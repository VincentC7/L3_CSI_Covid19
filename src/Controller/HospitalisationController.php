<?php


namespace L3_CSI_Covid19\Controller;


use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class HospitalisationController extends Controller {

    public function index(RequestInterface $request, ResponseInterface $response, $args){
        $pdo = $this->get_PDO();
        $stmt_hopital = $pdo->prepare("Select * from hopital where nb_libres > 0 and nodep = ?");
        $stmt_patient = $pdo->prepare("Select * from patient where num_secu = ?");
        $stmt_patient->execute([$args['numsecu']]);
        $patient = $stmt_patient->fetch();
        $stmt_hopital->execute([$patient['nodep']]);
        $hopitaux = $stmt_hopital->fetchAll(PDO::FETCH_ASSOC);
        $this->render($response,'pages/hospitalisation.twig', ['hopitaux'=> $hopitaux, 'patient'=>$patient]);
    }

    public function new(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel et code post => bon format
        Validator::dateTime()->validate($params['debut_hosp']) || $erreurs['debut_hosp'] = "Format incorrect";
        if (!empty($erreurs)){
            $this->afficher_message('L\'hospitalisation n\'a pas été enregistrée, format de la date invalide (jj/mm/aaaa hh:min)','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        $stmt_insert = $pdo->prepare('INSERT INTO hospitalise (debut_hospitalisation, fin_hospitalisation, nohopital, num_secup) VALUES (?,null,?,?)');
        $resultat = $stmt_insert->execute([$params['debut_hosp'],$args['nohopital'],$args['numsecu']]);
        if ($resultat) {
            $this->afficher_message('Le patient a bien été hospitalisé');
        }else{
            $this->afficher_message('L\'hospitalisaiton du patient à échoué veuillez réesayer', 'echec');
        }
        return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel et code post => bon format
        Validator::dateTime()->validate($params['fin_hosp']) || $erreurs['fin_hosp'] = "Format incorrect";
        if (!empty($erreurs)){
            $this->afficher_message('L\'hospitalisation n\'a pas été intérompue, format de la date invalide (jj/mm/aaaa hh:min)','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        $stmt_insert = $pdo->prepare('UPDATE hospitalise set fin_hospitalisation = ? where nohospitalisation = ?');
        $resultat = $stmt_insert->execute([$params['fin_hosp'],$args['noHosp']]);
        if ($resultat) {
            $this->afficher_message('L\'hospitalisation à bien été arrêtée');
        }else{
            $this->afficher_message($stmt_insert->errorInfo()[2], 'echec');
        }
        return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
    }

    public function demandetransfer (RequestInterface $request, ResponseInterface $response, $args){
        $pdo = $this->get_PDO();
        $stmt_hopitaux = $pdo->prepare("Select * from hopital where nb_libres > 0 and nodep = ? and nohopital != ?");
        $stmt_patient = $pdo->prepare("Select * from patient where num_secu = ?");
        $stmt_hopital_courant = $pdo->prepare("Select * from hopital where nohopital = ?");

        $stmt_patient->execute([$args['numsecu']]);
        $patient = $stmt_patient->fetch();
        $stmt_hopital_courant->execute([$args['nohopital']]);
        $hopital = $stmt_hopital_courant->fetch();
        $stmt_hopitaux->execute([$patient['nodep'],$hopital['nohopital']]);

        $hopitaux = $stmt_hopitaux->fetchAll(PDO::FETCH_ASSOC);
        $this->render($response,'pages/hospitalisation.twig', ['hopitaux'=> $hopitaux, 'patient'=>$patient, 'transfer'=>true]);
    }

    public function transferer(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];
        $stmt_hosp = $pdo->prepare("Select nohopital,nohospitalisation from hospitalise where num_secup = ?");
        $stmt_hosp->execute([$args['numsecu']]);
        $hospitalisation_courante = $stmt_hosp->fetch();

        //Vérification du num tel et code post => bon format
        Validator::dateTime()->validate($params['debut_hosp']) || $erreurs['debut_hosp'] = "Format incorrect";

        if (!empty($erreurs)){
            $this->afficher_message('Le transfère  n\'a été effectué, format de la date invalide (jj/mm/aaaa hh:min)','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'transferpatient', ['numsecu'=>$args['numsecu'],'nohopital'=>$hospitalisation_courante['nohopital']]);
        }

        $stmt_transfer = $pdo->prepare('select proc_trf_patient(?,?,?)');
        $resultat = $stmt_transfer->execute([$hospitalisation_courante['nohospitalisation'],$args['newHopital'],$params['debut_hosp']]);
        if ($resultat) {
            $this->afficher_message('L\'hospitalisation à bien été arrêtée');
        }else{
            $this->afficher_message($stmt_transfer->errorInfo()[2], 'echec');
            return $this->redirect($response,'transferpatient', ['numsecu'=>$args['numsecu'],'nohopital'=>$hospitalisation_courante['nohopital']]);
        }
        return $this->redirect($response,'voirHopital', ['nohopital'=>$args['newHopital']]);
    }

}