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
        $departemnts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->render($response,'pages/list_departements.twig', ['departements'=>$departemnts]);
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
            $this->afficher_message('Le seuil de places supplémentaires à bien été effectué');
        }else{
            $this->afficher_message('Une erreur est survenue dans la mise à jour', 'echec');
        }

        return $this->redirect($response,'departements');
    }
}