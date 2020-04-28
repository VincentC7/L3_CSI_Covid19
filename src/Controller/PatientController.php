<?php


namespace L3_CSI_Covid19\Controller;

use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Rules\Json;
use Respect\Validation\Validator;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function index(RequestInterface $request, ResponseInterface $response){
        $pdo = $this->get_PDO();
        $stmt_departements = $pdo->prepare("Select * from departement");
        $stmt_departements->execute();
        $departements = $stmt_departements->fetchAll(PDO::FETCH_ASSOC);
        $this->render($response,'pages/list_patients.twig',['departements' =>$departements]);
    }

    public function new(RequestInterface $request, ResponseInterface $response){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel, code post et num secu => bon format
        Validator::intVal()->length(5,5)->validate($params['codePost']) || $erreurs['codePost'] = "Format incorrect";
        (Validator::length(10,10)->validate($params['tel']) && is_numeric($params['tel'])) || $erreurs['tel'] = "Format incorrect";
        Validator::intVal()->length(15,15)->validate($params['num_secu']) || $erreurs['num_secu'] = "Format incorrect";

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
        //Affichage des erreurs s'il y en a
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
        //Vérification si la requette c'est correctement exécuté
        if ($resultat) {
            $this->afficher_message('Le patient a bien été crée');
        }else{
            $this->afficher_message('Les information du patient ne sont pas corrects', 'echec');
        }

        return $this->redirect($response,'patient');
    }

    public function view(RequestInterface $request, ResponseInterface $response, $args){
        $num = $args['numsecu'];
        $pdo = $this->get_PDO();
        $stmt_departements = $pdo->prepare("Select * from departement");
        $stmt_hospi = $pdo->prepare("Select nohospitalisation,debut_hospitalisation,fin_hospitalisation,nomhop from hospitalise inner join hopital on hopital.nohopital = hospitalise.nohopital where num_secup = ? order by nohospitalisation");
        $stmt = $pdo->prepare("Select * from patient where num_secu = ?");
        $stmt_departements->execute();
        $stmt_hospi->execute([$num]);
        $stmt->execute([$num]);

        $patient = $stmt->fetch();
        $departements = $stmt_departements->fetchAll(PDO::FETCH_ASSOC);
        $hospitalisations = $stmt_hospi->fetchAll(PDO::FETCH_ASSOC);
        $currentHop = false;
        foreach ($hospitalisations as $hospitalisation){
            if (is_null($hospitalisation['fin_hospitalisation'])) {
                $currentHop = true;
                break;
            }
        }
        $this->render($response,'pages/patient.twig', ['patient' =>  $patient, 'departements' =>$departements,'hospitalisations'=>$hospitalisations ,'currentHop' => $currentHop]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel, code post et num secu => bon format
        Validator::intVal()->length(5,5)->validate($params['codePost']) || $erreurs['codePost'] = "Format incorrect";
        (Validator::length(10,10)->validate($params['tel']) && is_numeric($params['tel'])) || $erreurs['tel'] = "Format incorrect";
        Validator::intVal()->length(15,15)->validate($params['num_secu']) || $erreurs['num_secu'] = "Format incorrect";

        $this->est_champ_null($params['num_secu']) || $erreurs['num_secu'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['nom']) || $erreurs['nom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['prenom']) || $erreurs['prenom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['rue']) || $erreurs['rue'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['ville']) || $erreurs['ville'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['codePost']) || $erreurs['codePost'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['tel']) || $erreurs['tel'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['sexe']) || $erreurs['sexe'] = "Veuillez specifier ce champ";

        //Verification de l'existance du patient
        if (!isset($erreurs['num_secu'])){
            $stmt = $pdo->prepare("SELECT nom FROM patient WHERE num_secu = ? ");
            $stmt->execute([$params['num_secu']]);
            $patient = $stmt->fetch()['nom'];
            !isset($patient['nom']) ||  $erreurs['num_secu'] = "Ce patient existe déjà";
        }
        //Affichage des erreurs s'il y en a
        if (!empty($erreurs)){
            $this->afficher_message('Certains champs n\'ont pas été rempli correctement','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        $stmt_update = $pdo->prepare('UPDATE patient SET num_secu = ?, nom=?, prenom=?,sexe=?,date_naissance=?,ruep = ?, villep = ?, codepostp = ?, num_tel = ?, fin_surveillance = ? where num_secu = ?');
        $resultat = $stmt_update->execute([$params['num_secu'],$params['nom'],$params['prenom'],$params['sexe'],$params['date_naiss'],$params['rue'],$params['ville'],$params['codePost'],$params['tel'],$params['fin_surveillance'],$args['numsecu']]);

        if ($resultat) {
            $this->afficher_message('Le patient a bien été modifié');
        }else{
            $this->afficher_message($stmt_update->errorInfo()[2], 'echec');
        }
        return $this->redirect($response,'voirPatient', ['numsecu'=>$params['num_secu']]);
    }

    public function update_etat_sante(RequestInterface $request, ResponseInterface $response, $args){
        //Récupération de l'acces base
        $pdo = $this->get_PDO();

        //Verification des champs
        $params = $request->getParams();
        $erreurs = [];

        //Vérification du num tel et code post => bon format
        $params['Etat_sante'] === "fièvre"
            || $params['Etat_sante'] === "aucun symptome"
            || $params['Etat_sante'] === "fièvre et pb respiratoires"
            || $params['Etat_sante'] === "aucun symptome"
            || $params['Etat_sante'] === "inconscient"
            || $params['Etat_sante'] === "décédé"
            || $erreurs['Etat_sante'] = "Veuillez specifier ce champ";

        if ($params['Etat_sante'] === "décédé" && !Validator::dateTime()->validate($params['fin_surveillance'])){
            $this->afficher_message('Si le patient est décédé indiquez une date de fin surveillance','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        if (Validator::dateTime()->validate($params['fin_surveillance']) && ($params['Etat_sante']=="fièvre et pb respiratoires" || $params['Etat_sante']=="inconscient" || $params['Etat_sante']=="fièvre")){
            $this->afficher_message('Vous ne pouvez pas mettre fin à la surveillance si le patient présente des symptomes','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        if (!empty($erreurs)){
            $this->afficher_message('Certains champs n\'ont pas été rempli correctement','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
        }

        if (Validator::dateTime()->validate($params['fin_surveillance'])){
            $stmt_update = $pdo->prepare('UPDATE patient SET etat_sante = ?, fin_surveillance = ? where num_secu = ?');
            $resultat = $stmt_update->execute([$params['Etat_sante'],$params['fin_surveillance'],$args['numsecu']]);
        }else{
            $stmt_update = $pdo->prepare('UPDATE patient SET etat_sante = ? where num_secu = ?');
            $resultat = $stmt_update->execute([$params['Etat_sante'], $args['numsecu']]);
        }

        if ($resultat) {
            $this->afficher_message("L'état de santé du patient a bien été modifié");
        }else{
            $this->afficher_message($stmt_update->errorInfo()[2], 'echec');
        }
        return $this->redirect($response,'voirPatient', ['numsecu'=>$args['numsecu']]);
    }

    public function rechercher(RequestInterface $request, ResponseInterface $response, $args){
        $pdo = $this->get_PDO();
        $params = $request->getParams();
        $recherche = $params['recherche'];
        $type_recherche = $params['types_recherche'];
        if ($type_recherche == 0){
            $stmt = $pdo->prepare("select * from patient where upper(nom) LIKE upper(?) or lower(prenom) LIKE lower(?) limit 100");
            $stmt->execute(['%'.$recherche.'%','%'.$recherche.'%']);
        }else{
            $stmt = $pdo->prepare("select * from patient where num_secu LIKE ? limit 100");
            $stmt->execute(['%'.$recherche.'%']);
        }
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count=0;
        foreach ($patients as $patient){
            $patients[$count]['hospitalise'] = false;
            $stmt = $pdo->prepare("Select debut_hospitalisation, fin_hospitalisation, nomhop from hospitalise INNER JOIN hopital ON hospitalise.nohopital = hopital.nohopital where num_secup = ? ");
            $stmt->execute([$patient['num_secu']]);
            $hospitalisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($hospitalisations as $hospitalisation){
                if (is_null($hospitalisation['fin_hospitalisation'])){
                    $patients[$count]['hospitalise'] = $hospitalisation['nomhop'];
                }
            }
            $count++;
        }
        return json_encode($patients);
    }

    private function est_champ_null($var){
        return Validator::notEmpty()->validate($var);
    }
}