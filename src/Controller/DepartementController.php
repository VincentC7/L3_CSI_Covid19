<?php


namespace L3_CSI_Covid19\Controller;


use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DepartementController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function index(RequestInterface $request, ResponseInterface $response){
        $pdo = $this->get_PDO();
        $stmt = $pdo->prepare("SELECT * FROM departement order by nodep");
        $stmt->execute();
        $departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $departemnts_sature = [];
        $departemnts_non_sature = [];
        foreach ($departements as $departement){
            $stmt = $pdo->prepare("Select count(*) from patient where nodep = ?");
            $stmt->execute([$departement['nodep']]);
            $nb_patient = $stmt->fetch()['count'];
            $departement['nb_patients'] = $nb_patient;
            $departement['sature'] = $nb_patient > $departement['seuil_contamine'];
            if ($nb_patient > $departement['seuil_contamine']){
                $departemnts_sature[] = $departement;
            }else{
                $departemnts_non_sature[] = $departement;
            }
        }
        $departements = array_merge($departemnts_sature,$departemnts_non_sature);
        $this->render($response,'pages/list_departements.twig', ['departements'=>$departements]);
    }

    public function view(RequestInterface $request, ResponseInterface $response, $args){
        $pdo = $this->get_PDO();
        $stmt = $pdo->prepare("Select * from patient where nodep = ? and fin_surveillance is null and num_secu not in (Select num_secup from hospitalise where fin_hospitalisation is null)");
        $stmt->execute([$args['nodep']]);
        $stmt_dep = $pdo->prepare("Select * from departement where nodep = ?");
        $stmt_dep->execute([$args['nodep']]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $departemnt = $stmt_dep->fetch();
        $bilan_dep = new Bilan($pdo);
        $this->render($response,'pages/departement.twig', ['patients'=>$patients, 'departement'=>$departemnt, 'bilan'=>$bilan_dep->get_statisiques($departemnt['nodep'])]);
    }


    public function update(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel et code post => bon format
        $params['seuil'] >= 0 || $erreurs['seuil'] = "Format incorrect";

        if (!empty($erreurs)){
            $this->afficher_message('Le seuil d\'un département doit être strictement positif','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'departements');
        }

        $stmt_update = $pdo->prepare('UPDATE departement SET seuil_contamine = ? where nodep = ?');
        $resultat = $stmt_update->execute([filter_var($params['seuil'],FILTER_SANITIZE_STRING),$args['departement']]);
        if ($resultat) {
            $this->afficher_message('Le seuil de places supplémentaires à bien été modifié');
        }else{
            $this->afficher_message('Une erreur est survenue dans la mise à jour', 'echec');
        }

        return $this->redirect($response,'departements');
    }
}