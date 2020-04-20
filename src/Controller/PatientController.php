<?php


namespace L3_CSI_Covid19\Controller;

use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $pdo = $this->get_PDO();
        $stmt_patients = $pdo->prepare("Select * from patient");
        $stmt_departements = $pdo->prepare("Select * from departement");
        $stmt_patients->execute();
        $stmt_departements->execute();
        $patients = $stmt_patients->fetchAll(PDO::FETCH_ASSOC);
        $departements = $stmt_departements->fetchAll(PDO::FETCH_ASSOC);
        $this->render($response,'pages/patient.twig',['patients'=> $patients, 'departements' =>$departements, 'message']);
    }

    public function nouveauPatient(RequestInterface $request, ResponseInterface $response){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];


        //Vérification du num tel, code post et num secu => bon format
        Validator::intVal()->length(5,5)->validate($params['codePost']) || $erreurs['codePost'] = "Format incorrect";;
        Validator::intVal()->length(10,10)->validate($params['tel']) || $erreurs['tel'] = "Format incorrect";;
        Validator::intVal()->length(15,15)->validate($params['num_secu']) || $erreurs['num_secu'] = "Format incorrect";;

        $this->est_champ_null($params['num_secu']) || $erreurs['num_secu'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['nom']) || $erreurs['nom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['prenom']) || $erreurs['prenom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['rue']) || $erreurs['rue'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['ville']) || $erreurs['ville'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['codePost']) || $erreurs['codePost'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['tel']) || $erreurs['tel'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['Etat_sante']) || $erreurs['Etat_sante'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['sexe']) || $erreurs['sexe'] = "Veuillez specifier ce champ";

        //Verification de l'existance du patient
        if (!isset($erreurs['num_secu'])){
            $stmt = $pdo->prepare("SELECT nom FROM patient WHERE num_secu = ? ");
            $stmt->execute([$params['num_secu']]);
            !isset($stmt->fetch()['nom']) ||  $erreurs['num_secu'] = "Ce patient existe déjà";
        }


        if (!empty($erreurs)){
            $this->afficher_message('Certains champs n\'ont pas été rempli correctement','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'patient');
        }


        $stmt = $pdo->prepare("INSERT INTO patient (num_secu, nom, prenom, sexe, date_naissance, num_tel, ruep, villep, codepostp, debut_surveillance, fin_surveillance, etat_sante, nodep) VALUES (?,?,?,?,?,?,?,?,?,?,NULL,?,?)");
        $num_secu = filter_var($params['num_secu'],FILTER_SANITIZE_STRING);
        $nom = filter_var($params['nom'],FILTER_SANITIZE_STRING);
        $prenom = filter_var($params['prenom'],FILTER_SANITIZE_STRING);
        $ruep = filter_var($params['rue'],FILTER_SANITIZE_STRING);
        $villep = filter_var($params['ville'],FILTER_SANITIZE_STRING);
        $codepostp = filter_var($params['codePost'],FILTER_SANITIZE_STRING);
        $date_naissance = filter_var($params['date_naiss'],FILTER_SANITIZE_STRING);
        $num_tel = filter_var($params['tel'],FILTER_SANITIZE_STRING);
        $debut_surveillance = filter_var($params['dateSurveillance'],FILTER_SANITIZE_STRING);
        $etat_sante = filter_var($params['Etat_sante'],FILTER_SANITIZE_STRING);
        $nodep = filter_var($params['departement'],FILTER_SANITIZE_STRING);
        $sexe = filter_var($params['sexe'],FILTER_SANITIZE_STRING);

        $resultat = $stmt->execute([$num_secu,$nom,$prenom,$sexe,$date_naissance,$num_tel,$ruep,$villep,$codepostp,$debut_surveillance,$etat_sante,$nodep]);
        if ($resultat) {
            $this->afficher_message('Le patient a bien été crée');
        }else{
            $this->afficher_message('Les information du patient ne sont pas corrects', 'echec');
        }

        return $this->redirect($response,'patient');
    }

    public function modifierPatient(RequestInterface $request, ResponseInterface $response){
        echo "à faire";
    }

    private function est_champ_null($var){
        return Validator::notEmpty()->validate($var);
    }
}