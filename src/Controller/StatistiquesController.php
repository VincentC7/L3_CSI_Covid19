<?php


namespace L3_CSI_Covid19\Controller;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PDO;

use L3_CSI_Covid19\Model\Patient;

class StatistiquesController extends Controller {

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function home(RequestInterface $request, ResponseInterface $response){
        $pdo = $this->container->get('pdo');
        $this->render($response,'pages/stats.twig');

        $this->getStatPerGender($pdo, 54);
        $this->getStatPerStatus($pdo, 54);
    }

    public function getStatPerGender($pdo, $dpt){
        if(!isset($dpt)){
            $stmt_patient_surv = $pdo->prepare("select sexe, date_naissance from patient where fin_surveillance is null");
            $stmt_patient_surv->execute();
        }
        else{
            $stmt_patient_surv = $pdo->prepare("select sexe, date_naissance from patient where fin_surveillance is null and nodep = ?");
            $stmt_patient_surv->execute(array($dpt));
            echo "département: ".$dpt."<br>";
        }


        $data = Array();
        $data[0] = 0;
        $data[1] = 0;

        $sumAge = Array();
        $sumAge[0] = 0;
        $sumAge[1] = 0;

        while($patient = $stmt_patient_surv->fetch()){
            if($patient['sexe'] == 'M'){
                $data[0]++;
                $sumAge[0] += $this->getAge($patient['date_naissance']);
            }
            else{
                $data[1]++;
                $sumAge[1] += $this->getAge($patient['date_naissance']);
            }
        }
        $this->tmpAffGender($data, $sumAge);
    }

    public static function tmpAffGender($data, $sumAge){
        echo "Hommes: ".$data[0]."<br>";
        if($data[0] != 0)
            echo "moyenne d'age: ".intval($sumAge[0] / $data[0])."<br>";
        echo "<br>";
        echo "Femmes: ".$data[1]."<br>";
        if($data[1] != 0)
            echo "moyenne d'age: ".intval($sumAge[1] / $data[1])."<br>";
        echo "<br>";
    }

    public function getStatPerStatus($pdo, $dpt){
      if(!isset($dpt)){
          $stmt_patient_surv = $pdo->prepare("select etat_sante, date_naissance from patient where fin_surveillance is null");
          $stmt_patient_surv->execute();
      }
      else{
          $stmt_patient_surv = $pdo->prepare("select etat_sante, date_naissance from patient where fin_surveillance is null and nodep = ?");
          $stmt_patient_surv->execute(array($dpt));
          echo "département: ".$dpt."<br>";
      }

        $etat = Array();
        $etat[0] = "aucun symptome";
        $etat[1] = "fièvre";
        $etat[2] = "fièvre et pb respiratoires";
        $etat[3] = "inconscient";

        $data = Array();
        $data[0] = 0;
        $data[1] = 0;
        $data[2] = 0;
        $data[3] = 0;

        $sumAge = Array();
        $sumAge[0] = 0;
        $sumAge[1] = 0;
        $sumAge[2] = 0;
        $sumAge[3] = 0;

        while($patient = $stmt_patient_surv->fetch()){
            $data[array_search($patient['etat_sante'], $etat)]++;
            $sumAge[array_search($patient['etat_sante'], $etat)] += $this->getAge($patient['date_naissance']);
        }

        $this->tmpAffStatus($data, $etat, $sumAge);
    }

    public static function tmpAffStatus($data, $etat, $sumAge){
        for($i = 0; $i < count($etat); $i++){
            echo $etat[$i].": ".$data[$i]."<br>";
            if($data[$i] != 0)
                echo "moyenne d'age: ".intval($sumAge[$i] / $data[$i])."<br>";
        }
    }







    public function global_stat($pdo){
        $stmt_patient_surv = $pdo->prepare("select prenom, sexe, date_naissance, etat_sante from patient where fin_surveillance is null");
        $stmt_patient_surv->execute();

      /*  $stmt_patient_home = $pdo->prepare("select num_secu from patient where not exists (select num_secuP from hospitalise where patient.num_secu = hospitalise.num_secuP)");
        $stmt_patient_home->execute();*/
        $data_array = Array();

        $sexe = Array();
        $sexe[0] = "Masculin";
        $sexe[1] = "Féminin";

        $tranches = Array();
        $tranches[0] = "0-30";
        $tranches[1] = "30-70";
        $tranches[2] = "70+";

        $etat = Array();
        $etat[0] = "aucun symptome";
        $etat[1] = "fièvre";
        $etat[2] = "fièvre et pb respiratoires";
        $etat[3] = "inconscient";

        for($i = 0; $i < count($tranches); $i++){
            for($j = 0; $j < 2; $j++){
                for($k = 0; $k < count($etat); $k++){
                    $data_array[$i][$j][$k] = 0;
                }
            }
        }

        while($patient = $stmt_patient_surv->fetch()){
            $ageP = $this->getAge($patient["date_naissance"]);
            $sexeP = $patient["sexe"];
            $etat_santeP = $patient["etat_sante"];

            $data_array[$this->getTrancheIndex($tranches, $ageP)][$this->getIndex($sexe, $sexeP)][$this->getIndex($etat, $etat_santeP)]++;
        }
        $this->tmpaffdata($data_array, $tranches, $etat, $sexe);
    }

    public static function getAge($date_naissance){
        $date = explode('-', $date_naissance);

        $age = date('Y') - $date[0];
        if (date('md') < date('md', strtotime($date_naissance))) {
            return $age - 1;
        }
        return $age;
    }

    public static function getTrancheIndex($tranches, $age){
        $find = false;
        $i = 0;

        while($i < (count($tranches) - 1) && !$find){
              $tmp = explode('-', $tranches[$i]);
              $max = $tmp[1];

              if($age < $max)
                  $find = true;
              $i++;
        }

        if(!$find)
            return $i;
        return $i - 1;
    }

    public static function getIndex($tab, $var){
        return array_search($var, $tab);
    }

    public static function tmpaffdata($data_array, $tranches, $etat, $sexe){
        for($i = 0; $i < count($tranches); $i++){
            echo "tranche: ".$tranches[$i]."<br>";

            for($j = 0; $j < 2; $j++){
                echo "_sexe: ".$sexe[$j]."<br>";

                for($k = 0; $k < count($etat); $k++){
                    echo "__".$etat[$k]." : ".$data_array[$i][$j][$k]."<br>";
                }
                echo "<br>";
            }
            echo "<br>";
        }
    }
}
