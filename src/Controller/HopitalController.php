<?php


namespace L3_CSI_Covid19\Controller;


use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HopitalController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function index(RequestInterface $request, ResponseInterface $response){
        $pdo = $this->get_PDO();
        $stmt = $pdo->prepare("SELECT * FROM hopital inner join departement d on hopital.nodep = d.nodep order by nomhop");
        $stmt->execute();
        $hopitaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        $hopitaux_plein = [];
        $hopitaux_presque_plein = [];
        $hopitaux_reste = [];
        foreach ($hopitaux as $hopital){
            $nb_place_hopital_tot = $hopital['nb_places'] + $hopital['nb_supplementaire'];
            $place_acutelles = $nb_place_hopital_tot - $hopital['nb_libres'];
            $hopital['places_actuelles'] = $place_acutelles;
            $hopital['places_tot'] = $nb_place_hopital_tot;
            if ($hopital['nb_libres']==0){
                $hopital['etat'] = 'plein';
                $hopitaux_plein[] = $hopital;
            }elseif ((($place_acutelles/$nb_place_hopital_tot)*100) >= 80){
                $hopital['etat'] = 'presque_plein';
                $hopitaux_presque_plein[] = $hopital;
            }else{
                $hopitaux_reste[] = $hopital;
            }
            $count++;
        }
        $hopitaux = array_merge($hopitaux_plein,$hopitaux_presque_plein,$hopitaux_reste);
        $this->render($response,'pages/list_hopital.twig',['hopitaux'=>$hopitaux]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel et code post => bon format
        $params['nb_places'] >= 0 || $erreurs['places'] = "Format incorrect";

        if (!empty($erreurs)){
            $this->afficher_message('Le nombre de places supplémentaires doit strictement positif','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'hopitaux');
        }

        $stmt_update = $pdo->prepare('UPDATE hopital SET nb_supplementaire = ? where nohopital = ?');
        $resultat = $stmt_update->execute([filter_var($params['nb_places'],FILTER_SANITIZE_STRING),$args['nohopital']]);
        if ($resultat) {
            $this->afficher_message('Le nombre de places supplémentaires à bien été effectué');
        }else{
            $this->afficher_message($stmt_update->errorInfo()[2], 'echec');
        }

        return $this->redirect($response,'hopitaux');
    }
}