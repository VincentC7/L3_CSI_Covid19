<?php


namespace L3_CSI_Covid19\Controller;


use PDO;

class Bilan
{
    private $pdo;

    /**
     * Bilan constructor.
     * @param $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Fonction donnant regrouppant toutes les statistiques pour former le bilan national ou départementral
     * @param null $dpt département (si dep == nul on recherche les statisitques nationales
     * @return mixed
     */
    public function get_statisiques($dpt = null)
    {
        $data['sexe'] = [];
        $data['age'] = [];
        $data['etat_sante'] = [];
        $data['statut'] = [];

        //Statut
        $data['statut']['mort'] = 0;
        $data['statut']['gueri'] = 0;
        $data['statut']['confine'] = 0;
        $data['statut']['hospitalise'] = 0;

        //Sexe
        $data['sexe']['F'] = 0;
        $data['sexe']['M'] = 0;

        //Age
        $data['age']['moins_15'] = 0;
        $data['age']['_15_44'] = 0;
        $data['age']['_45_64'] = 0;
        $data['age']['_65_74'] = 0;
        $data['age']['plus_75'] = 0;

        //Etat_Sante
        $data['etat_sante']['aucun_symp'] = 0;
        $data['etat_sante']['fievre'] = 0;
        $data['etat_sante']['fievre_pb_resp'] = 0;
        $data['etat_sante']['inconscient'] = 0;


        if (!isset($dpt)) {
            $stmt_patient_surv = $this->pdo->prepare("select sexe, date_naissance,etat_sante from patient where fin_surveillance is null");
            $stmt_patient_surv->execute();
            $data = $this->set_statut($data);
        } else {
            $stmt_patient_surv = $this->pdo->prepare("select sexe, date_naissance,etat_sante from patient where nodep = ? and fin_surveillance is null");
            $stmt_patient_surv->execute(array($dpt));
            $data = $this->set_statut($data, $dpt);
        }

        while ($patient = $stmt_patient_surv->fetch()) {
            $data['sexe'][$patient['sexe']]++;
            $data = $this->set_stats_age($data, $patient['date_naissance']);
            $data = $this->set_etat_sante($data, $patient['etat_sante']);
        }

        return $data;
    }

    /**
     * Fonction qui met a jour les statistiques globales en fonction de la date de naissance
     * @param $data
     * @param $dateNaiss
     * @return mixed
     */
    private function set_stats_age($data, $dateNaiss)
    {
        $age = self::getAge($dateNaiss);
        if ($age < 15) {
            $data['age']['moins_15']++;
        } elseif ($age < 45) {
            $data['age']['_15_44']++;
        } elseif ($age < 65) {
            $data['age']['_45_64']++;
        } elseif ($age < 75) {
            $data['age']['_65_74']++;
        } else {
            $data['age']['plus_75']++;
        }
        return $data;
    }

    /**
     * Calcul l'age d'un patient
     * @param $date_naissance
     * @return false|int|mixed|string
     */
    private static function getAge($date_naissance)
    {
        $date = explode('-', $date_naissance);
        $age = date('Y') - $date[0];
        if (date('md') < date('md', strtotime($date_naissance))) {
            return $age - 1;
        }
        return $age;
    }

    /**
     * Fonction qui met a jour les statistiques globales en fonction de l'état santé donnée
     * @param $data
     * @param $etat_sante 'aucun symptome', 'fièvre', 'fièvre et pb respiratoires', 'inconscient'
     * @return mixed
     */
    private function set_etat_sante($data, $etat_sante)
    {
        if ($etat_sante === 'aucun symptome') {
            $data['etat_sante']['aucun_symp']++;
        } elseif ($etat_sante === 'fièvre') {
            $data['etat_sante']['fievre']++;
        } elseif ($etat_sante === 'fièvre et pb respiratoires') {
            $data['etat_sante']['fievre_pb_resp']++;
        } else {
            $data['etat_sante']['inconscient']++;
        }
        return $data;
    }

    private function set_statut($data, $dpt = null)
    {
        if (!isset($dpt)) {
            $stmt_patient_hosp = $this->pdo->prepare("select count(*) from patient inner join hospitalise h on patient.num_secu = h.num_secup where fin_hospitalisation is null");
            $stmt_patient_hosp->execute();

            $stmt_patient_conf = $this->pdo->prepare("select count(*) from patient where fin_surveillance is null and num_secu not in (select num_secup from hospitalise where fin_surveillance is null)");
            $stmt_patient_conf->execute();

            $stmt_patient_mort = $this->pdo->prepare("select count(*) from patient where etat_sante = 'décédé'");
            $stmt_patient_mort->execute();

            $stmt_patient_gueris = $this->pdo->prepare("select count(*) from patient where fin_surveillance is not null and etat_sante = 'aucun symptome'");
            $stmt_patient_gueris->execute();
        } else {
            $stmt_patient_hosp = $this->pdo->prepare("select count(*) from patient inner join hospitalise h on patient.num_secu = h.num_secup where fin_hospitalisation is null and nodep = ?");
            $stmt_patient_hosp->execute(array($dpt));

            $stmt_patient_conf = $this->pdo->prepare("select count(*) from patient where fin_surveillance is null and nodep = ? and num_secu not in (select num_secup from hospitalise where fin_surveillance is null and nodep = ?)");
            $stmt_patient_conf->execute(array($dpt, $dpt));

            $stmt_patient_mort = $this->pdo->prepare("select count(*) from patient where etat_sante = 'décédé' and nodep = ?");
            $stmt_patient_mort->execute(array($dpt));

            $stmt_patient_gueris = $this->pdo->prepare("select count(*) from patient where fin_surveillance is not null and nodep = ?and etat_sante = 'aucun symptome'");
            $stmt_patient_gueris->execute(array($dpt));
        }
        $morts = $stmt_patient_mort->fetch(PDO::FETCH_ASSOC);
        $gueris = $stmt_patient_gueris->fetch(PDO::FETCH_ASSOC);
        $hosp = $stmt_patient_hosp->fetch(PDO::FETCH_ASSOC);
        $confine = $stmt_patient_conf->fetch(PDO::FETCH_ASSOC);


        $data['statut']['mort'] += $morts['count'];
        $data['statut']['gueri'] += $gueris['count'];
        $data['statut']['confine'] += $confine['count'];
        $data['statut']['hospitalise'] += $hosp['count'];
        return $data;
    }

    public function getGlobalStat($intervalle, $periode)
    {
        if (!isset($intervalle)) {
            $stmt_patient_surv = $this->pdo->prepare("select fin_surveillance, etat_sante from patient where fin_surveillance is not null and fin_surveillance < ? and fin_surveillance > ? order by fin_surveillance asc");
            $stmt_patient_surv->execute($intervalle);
        } else {
            $stmt_patient_surv = $this->pdo->prepare("select fin_surveillance, etat_sante from patient where fin_surveillance is not null order by fin_surveillance asc");
            $stmt_patient_surv->execute();
        }

        $final_status = Array();
        $index = 0;
        $previousDateDiff = null;

        while ($patient = $stmt_patient_surv->fetch()) {
            if (!isset($intervalle)) {
                $intervalle = Array();
                $intervalle[0] = $patient['fin_surveillance'];
            }

            $currentDateDiff = $this->getDateDiff($intervalle[0], $patient['fin_surveillance'], $periode);

            if ($previousDateDiff != null && $currentDateDiff != $previousDateDiff)
                $index++;

            $final_status[$index][0] = $currentDateDiff;

            if ($patient['etat_sante'] == "décédé") {
                $final_status[$index][1]++;
            } else {
                $final_status[$index][2]++;
            }

            $previousDateDiff = $currentDateDiff;
        }
    }

    public function get_bilan_epidemie($format){
        $bilan=[];
        $stmt = $this->pdo->prepare("select count(to_char(debut_surveillance,'$format')),to_char(debut_surveillance,'$format') 
                                                                from patient 
                                                                group by to_char(debut_surveillance,'$format'),fin_surveillance
                                                                having fin_surveillance is null
                                                                order by to_char(debut_surveillance,'$format')");
        $stmt->execute();
        $bilan['suspect']['dates'];
        $bilan['suspect']['nb'];
        $sum=0;
        while ($data = $stmt->fetch()) {
            $bilan['suspect']['dates'][] = $data[1];
            $sum+=$data[0];
            $bilan['suspect']['nb'][] = $sum;
        }
        $stmt = $this->pdo->prepare("select count(to_char(fin_surveillance,'$format')),to_char(fin_surveillance,'$format') 
                                                                from patient 
                                                                group by to_char(fin_surveillance,'$format'),etat_sante having etat_sante='décédé'");
        $stmt->execute();
        $bilan['mort']['dates'];
        $bilan['mort']['nb'];
        $sum=0;
        while ($data = $stmt->fetch()) {
            $bilan['mort']['dates'][] = $data[1];
            $sum+=$data[0];
            $bilan['mort']['nb'][] = $sum;
        }
        $stmt = $this->pdo->prepare("select count(to_char(fin_surveillance,'$format')),to_char(fin_surveillance,'$format')
                                                                from patient 
                                                                group by to_char(fin_surveillance,'$format'),etat_sante having etat_sante='aucun symptome'");
        $stmt->execute();
        $stmt->execute();
        $bilan['gueris']['dates'];
        $bilan['gueris']['nb'];
        $sum=0;
        while ($data = $stmt->fetch()) {
            $bilan['gueris']['dates'][] = $data[1];
            $sum+=$data[0];
            $bilan['gueris']['nb'][] = $sum;
        }
        return $bilan;
    }

    private static function getDateDiff($date_1, $date_2, $format)
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($format);
    }

}