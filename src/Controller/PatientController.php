<?php


namespace L3_CSI_Covid19\Controller;


use Illuminate\Database\QueryException;
use L3_CSI_Covid19\Model\Departement;
use L3_CSI_Covid19\Model\Patient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $patients = Patient::Select("*")->get();
        $departements = Departement::Select("nodep","nomdep")->get();
        $this->render($response,'pages/patient.twig',['patients'=> $patients, 'departements' =>$departements, 'message']);
    }

    public function nouveauPatient(RequestInterface $request, ResponseInterface $response){
        $params = $request->getParams();
        $erreurs = [];

        $this->est_champ_null($params['num_secu']) || $erreurs['num_secu'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['nom']) || $erreurs['nom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['prenom']) || $erreurs['prenom'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['rue']) || $erreurs['rue'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['ville']) || $erreurs['ville'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['codePost']) || $erreurs['codePost'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['tel']) || $erreurs['tel'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['Etat_sante']) || $erreurs['Etat_sante'] = "Veuillez specifier ce champ";
        $this->est_champ_null($params['sexe']) || $erreurs['sexe'] = "Veuillez specifier ce champ";

        if (!empty($erreurs)){
            $this->afficher_message('Certains champs n\'ont pas été rempli correctement','echec');
            $this->afficher_message($erreurs,'erreurs');
            return $this->redirect($response,'patient');
        }

        //Validator::date('yyyy/mm/dd')->validate($params['date_naiss']) || $erreurs['date_naiss'] = "Veuillez specifier le champ";
        //Validator::date('dd/mm/yyyy h:m')->validate($params['dateSurveillance']) || $erreurs['dateSurveillance'] = "Veuillez specifier le champ";

        $patient = new Patient();
        $patient->num_secu = filter_var($params['num_secu'],FILTER_SANITIZE_STRING);
        $patient->nom = filter_var($params['nom'],FILTER_SANITIZE_STRING);
        $patient->prenom = filter_var($params['prenom'],FILTER_SANITIZE_STRING);
        $patient->ruep = filter_var($params['rue'],FILTER_SANITIZE_STRING);
        $patient->villep = filter_var($params['ville'],FILTER_SANITIZE_STRING);
        $patient->codepostp = filter_var($params['codePost'],FILTER_SANITIZE_STRING);
        $patient->date_naissance = filter_var($params['date_naiss'],FILTER_SANITIZE_STRING);
        $patient->num_tel = filter_var($params['tel'],FILTER_SANITIZE_STRING);
        $patient->debut_surveillance = filter_var($params['dateSurveillance'],FILTER_SANITIZE_STRING);
        $patient->etat_sante = filter_var($params['Etat_sante'],FILTER_SANITIZE_STRING);
        $patient->nodep = filter_var($params['departement'],FILTER_SANITIZE_STRING);
        $patient->sexe = filter_var($params['sexe'],FILTER_SANITIZE_STRING);

        try{
            $patient->save();
            $this->afficher_message('Le patient a bien été crée');
        }catch (QueryException $e){
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