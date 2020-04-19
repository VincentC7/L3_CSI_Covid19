<?php


namespace L3_CSI_Covid19\Controller;


use L3_CSI_Covid19\Model\Departement;
use L3_CSI_Covid19\Model\Patient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

class PatientController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $patients = Patient::Select("*")->get();
        $departements = Departement::Select("nodep","nomdep")->get();
        $this->render($response,'pages/patient.twig',['patients'=> $patients, 'departements' =>$departements]);
    }

    public function nouveauPatient(RequestInterface $request, ResponseInterface $response){
        $patient = new Patient();
        $params = $request->getParams();

        $patient->num_secu = filter_var($params['num_secu'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->nom = filter_var($params['nom'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->prenom = filter_var($params['prenom'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->ruep = filter_var($params['rue'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->villep = filter_var($params['ville'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->codepostp = filter_var($params['codePost'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->date_naissance = filter_var($params['date_naiss'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->num_tel = filter_var($params['tel'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->debut_surveillance = filter_var($params['dateSurveillance'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->etat_sante = filter_var($params['Etat_sante'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->nodep = filter_var($params['departement'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->sexe = filter_var($params['sexe'],FILTER_SANITIZE_SPECIAL_CHARS);
        $patient->save();

        $patients = Patient::Select("*")->get();
        $departements = Departement::Select("nodep","nomdep")->get();
        $this->render($response,'pages/patient.twig',['patients'=> $patients, 'departements' =>$departements]);
    }

    public function modifierPatient(RequestInterface $request, ResponseInterface $response){
        echo "à faire";
    }
}