drop table Hospitalise;
drop table Patient;
drop table Hopital;
drop table Departement;

--Script de création de la base de données du COVID_19

create table Departement(
                            noDep varchar constraint pk_departement PRIMARY KEY,
                            nomDep varchar(32) not null,
                            seuil_contamine integer not null constraint domain_departement_seuil_contamine CHECK (seuil_contamine >= 0)
);

create table Patient(
                        num_secu char(15) not null constraint pk_patient PRIMARY KEY,
                        nom varchar(30) not null,
                        prenom varchar(30) not null,
                        sexe char(1) null constraint domain_patient_sexe CHECK (sexe in ('m','f','M','F')),
                        date_naissance date not null,
                        num_tel char(10) not null,
                        rueP varchar(64) not null,
                        villeP varchar(32) not null,
                        codePostP char(5) not null,
                        debut_surveillance timestamp not null constraint domain_patient_date CHECK (debut_surveillance > date_naissance),
                        fin_surveillance timestamp default null,
                        etat_sante varchar(32) not null constraint domain_patient_etat CHECK (etat_sante in ('aucun symptome', 'fièvre', 'fièvre et pb respiratoires', 'inconscient', 'décédé')),
                        noDep varchar not null constraint FK_Patient_noDep REFERENCES Departement(noDep)
);


create table Hopital(
                        noHopital serial constraint pk_hopital PRIMARY KEY,
                        nomHop varchar(32) not null,
                        villeH varchar(32) not null,
                        rueH varchar(32) not null,
                        codePostH char(5) not null,
                        nb_places integer not null constraint domain_hopital_nb_place CHECK (nb_places >= 0),
                        nb_supplementaire integer default 0 constraint domain_hopital_nb_supplementaire CHECK (nb_supplementaire >= 0),
                        nb_libres integer not null constraint domain_hopital_nb_libre CHECK (nb_libres >= 0 AND nb_libres <= (nb_supplementaire + nb_places)),
                        noDep varchar not null constraint FK_Hopital_noDep REFERENCES Departement(noDep)
);

create table Hospitalise(
                            noHospitalisation serial constraint pk_hospitalisation PRIMARY KEY,
                            debut_hospitalisation timestamp not null,
                            fin_hospitalisation timestamp default null,
                            noHopital integer not null constraint FK_Hospitalise_noHopital REFERENCES Hopital(noHopital),
                            num_secuP char(15) not null constraint FK_Hospitalise_num_secuP REFERENCES Patient(num_secu)
);

--check si la date de début est < à la date de fin
create or replace function f_check_date_deb_sup_fin(date_debut timestamp, date_fin timestamp) returns void as $$
declare
begin
    if (date_debut > date_fin) then
        raise exception 'La date de début ne peut pas être supérieur à la date de fin ! ';
    end if;
end;
$$ language plpgsql;


--fonction lors de update dans patient
create or replace function proc_upd_patient() returns trigger as $proc_upd_patient$
declare
    noHosp integer;
    fin_hosp timestamp;
begin

    if (old.etat_sante = 'décédé' and new.etat_sante != old.etat_sante) then
        raise exception 'le patient a été réssusciter ?';
    end if;

    if (not new.fin_surveillance is null or ((new.etat_sante in ('aucun symptome', 'décédé')) and new.etat_sante != old.etat_sante)) then
        if (new.etat_sante not in ('aucun symptome', 'décédé')) then
            raise exception 'il ne peut pas y avoir de fin d hospitalisation si le patient n est pas soit mort soit guérie';
        elseif (new.fin_surveillance is null and new.etat_sante in ('décédé')) then
            raise exception 'si le patient est décédé, une date de fin_surveillance doit être renseignée';
        end if;

        perform f_check_date_deb_sup_fin(old.debut_surveillance, new.fin_surveillance);
        select noHospitalisation into noHosp from hospitalise where Hospitalise.num_secuP = old.num_secu and fin_hospitalisation is null;
        raise notice '%', noHosp;

        if(noHosp is not null) then
            raise notice 'papapap';
            update Hospitalise set fin_hospitalisation = new.fin_surveillance where Hospitalise.num_secuP = old.num_secu and noHospitalisation = noHosp;
        end if;

    end if;

    return new;
end;
$proc_upd_patient$ language plpgsql;

create trigger trig_upd_patient before update on patient for each row execute procedure
    proc_upd_patient();



--fonction pour checker si la chaine est constituee uniquement de chiffres
create or replace function check_full_num(strpass TEXT, name TEXT) returns void as $$
declare
    i integer;
begin
    i := 1;

    while i <= char_length(strpass) loop
            if(substr(strpass, i, 1) not in ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')) then
                raise exception 'la colonne % ne contient pas que des chiffres: %.', name, strpass;
            end if;
            i := i+1;
        end loop;
end;
$$ language plpgsql;



--fonction a executer a l insertion d un patient
create or replace function proc_insert_patient() returns trigger as $proc_insert_patient$
begin
    perform check_full_num(new.num_secu, 'num_secu');
    perform check_full_num(new.num_tel, 'num_tel');
    perform check_full_num(new.codePostP, 'codePostP');

    return new;
end;
$proc_insert_patient$ language plpgsql;

create trigger trig_insert_patient before insert on patient for each row execute procedure
    proc_insert_patient();


--fonction a executer a l insertion d un hopital
create or replace function proc_insert_hopital() returns trigger as $proc_insert_hopital$
begin
    perform check_full_num(new.codePostH, 'codePostH');

    --places libres = places de base + places supplémentaires
    new.nb_libres := new.nb_places + new.nb_supplementaire;

    return new;
end;
$proc_insert_hopital$ language plpgsql;

create trigger trig_insert_hopital before insert on hopital for each row execute procedure
    proc_insert_hopital();


--procédure lors d'une hospitalisation
create or replace function proc_insert_hospitalise() returns trigger as $proc_insert_hospitalise$
declare
    nb_free_pl integer;
    nomH varchar;
    noHosp integer;
begin
    select noHospitalisation into noHosp from Hospitalise where num_secuP = new.num_secuP and fin_hospitalisation is null;
    if(noHosp is not null) then
        raise exception 'une hospitalisation de ce patient est déjà en cours';
    end if;

    select nb_libres, nomHop into nb_free_pl, nomH FROM hopital WHERE Hopital.noHopital = new.noHopital;

    if (nb_free_pl = 0) then
        raise exception 'plus de places dans cet hopital: %', nomH;
    end if;
    update Hopital set nb_libres = nb_libres - 1 where noHopital = new.noHopital;
    return new;
end;
$proc_insert_hospitalise$ language plpgsql;

create trigger trig_insert_hospitalise before insert on hospitalise for each row execute procedure
    proc_insert_hospitalise();



 --fonction lors de update d'un patient hospitalise
create or replace function proc_upd_hospitalise() returns trigger as $proc_upd_hospitalise$
begin
  if(old.fin_hospitalisation is not null) then
    raise exception 'la date de fin d hospitalisation ne peut pas être modifiée';
  end if;
	if (not new.fin_hospitalisation is null) then
		perform f_check_date_deb_sup_fin(old.debut_hospitalisation, new.fin_hospitalisation);
    update Hopital set nb_libres = nb_libres + 1 where Hopital.noHopital = old.noHopital;
	end if;

	return new;
end;
$proc_upd_hospitalise$ language plpgsql;

create trigger trig_upd_hospitalise before update on hospitalise for each row execute procedure
proc_upd_hospitalise();



--fonction lors de l'augmentation de places supplémentaires
create or replace function proc_upd_hopital() returns trigger as $proc_upd_hopital$
declare
    diff integer;
begin
    if(new.nb_supplementaire != old.nb_supplementaire) then
        diff := new.nb_supplementaire - old.nb_supplementaire;

        --check si c'est possible de décrémenter les places supplémentaires
        if(diff < 0 and (new.nb_libres + diff < 0)) then
            raise exception 'Impossible de retirer des places supplémentaires car le nombre de place libre et limité';
        end if;

        new.nb_libres := new.nb_libres + diff;
    end if;
    return new;
end;
$proc_upd_hopital$ language plpgsql;

create trigger trig_upd_hopital before update on hopital for each row execute procedure
    proc_upd_hopital();



--Fonction appelé toutes les 24h, mettant les patient dont l'état n'a pas empiré depuis 14 semaines à l'état géris
--La fonction va parcourir l'ensemble des patient serveillés qui n'ont aucun symptomes
-- Si leur état est stable de depuis plus de 14 jours on les concidères étant guéris
-- état stable = pas d'hospitalisation dans les 14 jours de surveillances
create or replace function check_daily_etat_patient() returns void as $check_dayli_etat_patient$
declare
    rec_patient RECORD;
    c_patient cursor for select num_secu,debut_surveillance from patient where fin_surveillance is null and etat_sante = 'aucun symptome';
    v_hosp hospitalise.fin_hospitalisation%TYPE;
    v_diff integer;
begin
    OPEN c_patient;
    loop
        fetch c_patient into rec_patient;
        exit when not found;

        select fin_hospitalisation into v_hosp from hospitalise where num_secup = rec_patient.num_secu order by fin_hospitalisation DESC;
        if (v_hosp is not null ) then
            SELECT DATE_PART('day', now() -  v_hosp) into v_diff;
        else
            SELECT DATE_PART('day', now() -  rec_patient.debut_surveillance) into v_diff;
        end if;

        if (v_diff > 14) then
            update patient set fin_surveillance = now() where num_secu = rec_patient.num_secu;
        end if;

    end loop;
    close c_patient;
end;
$check_dayli_etat_patient$ language plpgsql;


--transfert d'un patient
create function proc_trf_patient(noHospi integer, newH integer, dateFin timestamp) returns void as $$
declare
    numS TEXT;
begin
    select num_secuP into numS from Hospitalise where noHospitalisation = noHospi;

    update Hospitalise set fin_hospitalisation = dateFin where noHospitalisation = noHospi;
    insert into hospitalise (debut_hospitalisation, noHopital, num_secuP) values (dateFin, newH, num_secuP);
end;
$$ language plpgsql;



--Insertion des données


--Département
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'1'	,	'Ain'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'2'	,	'Aisne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'3'	,	'Allier'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'4'	,	'Alpes-de-Haute-Provence'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'5'	,	'Hautes-Alpes'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'6'	,	'Alpes-Maritimes'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'7'	,	'Ardèche'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'8'	,	'Ardennes'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'9'	,	'Ariège'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'10'	,	'Aube'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'11'	,	'Aude'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'12'	,	'Aveyron'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'13'	,	'Bouches-du-Rhône'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'14'	,	'Calvados'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'15'	,	'Cantal'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'16'	,	'Charente'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'17'	,	'Charente-Maritime'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'18'	,	'Cher'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'19'	,	'Corrèze'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'2A'	,	'Corse-du-Sud'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'2B'	,	'Haute-Corse'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'21'	,	'Côte-d''Or'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'22'	,	'Côtes d''Armor'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'23'	,	'Creuse'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'24'	,	'Dordogne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'25'	,	'Doubs'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'26'	,	'Drôme'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'27'	,	'Eure'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'28'	,	'Eure-et-Loir'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'29'	,	'Finistère'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'30'	,	'Gard'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'31'	,	'Haute-Garonne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'32'	,	'Gers'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'33'	,	'Gironde'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'34'	,	'Hérault'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'35'	,	'Ille-et-Vilaine'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'36'	,	'Indre'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'37'	,	'Indre-et-Loire'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'38'	,	'Isère'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'39'	,	'Jura'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'40'	,	'Landes'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'41'	,	'Loir-et-Cher'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'42'	,	'Loire'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'43'	,	'Haute-Loire'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'44'	,	'Loire-Atlantique'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'45'	,	'Loiret'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'46'	,	'Lot'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'47'	,	'Lot-et-Garonne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'48'	,	'Lozère'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'49'	,	'Maine-et-Loire'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'50'	,	'Manche'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'51'	,	'Marne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'52'	,	'Haute-Marne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'53'	,	'Mayenne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'54'	,	'Meurthe-et-Moselle'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'55'	,	'Meuse'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'56'	,	'Morbihan'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'57'	,	'Moselle'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'58'	,	'Nièvre'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'59'	,	'Nord'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'60'	,	'Oise'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'61'	,	'Orne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'62'	,	'Pas-de-Calais'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'63'	,	'Puy-de-Dôme'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'64'	,	'Pyrénées-Atlantiques'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'65'	,	'Hautes-Pyrénées'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'66'	,	'Pyrénées-Orientales'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'67'	,	'Bas-Rhin'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'68'	,	'Haut-Rhin'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'69'	,	'Rhône'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'70'	,	'Haute-Saône'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'71'	,	'Saône-et-Loire'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'72'	,	'Sarthe'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'73'	,	'Savoie'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'74'	,	'Haute-Savoie'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'75'	,	'Paris'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'76'	,	'Seine-Maritime'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'77'	,	'Seine-et-Marne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'78'	,	'Yvelines'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'79'	,	'Deux-Sèvres'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'80'	,	'Somme'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'81'	,	'Tarn'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'82'	,	'Tarn-et-Garonne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'83'	,	'Var'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'84'	,	'Vaucluse'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'85'	,	'Vendée'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'86'	,	'Vienne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'87'	,	'Haute-Vienne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'88'	,	'Vosges'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'89'	,	'Yonne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'90'	,	'Territoire de Belfort'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'91'	,	'Essonne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'92'	,	'Hauts-de-Seine'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'93'	,	'Seine-St-Denis'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'94'	,	'Val-de-Marne'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'95'	,	'Val-D"Oise'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'971'	,	'Guadeloupe'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'972'	,	'Martinique'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'973'	,	'Guyane'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'974'	,	'La Réunion'	,	5	);
insert into 	Departement	(noDep,nomDep,seuil_contamine)	values(	'976'	,	'Mayotte'	,	5	);

--Hopital
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital Central'	,	'Nancy'	,	'2 rue du centre'	,	'54000'	,	5	,	3	,	'54'	);
insert into 	hopital	(nomHop,villeH,rueH,codepostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital de Laxou'	,	'Laxou'	,	'150 rue de la libération'	,	'54200'	,	5	,	3	,	'54'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital Troyen'	,	'Troyes'	,	'3 rue des libertes'	,	'10000'	,	5	,	3	,	'10'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital bonne mère'	,	'Marseille'	,	'13 rue des villes'	,	'13000'	,	5	,	3	,	'13'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital neuf'	,	'Metz'	,	'5 avenue des tirs'	,	'57000'	,	5	,	3	,	'57'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital du sergent'	,	'Strasbourg'	,	'87 rue Lodges'	,	'67000'	,	5	,	3	,	'67'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital du sud '	,	'Toulon'	,	'12 rue des reves'	,	'83000'	,	5	,	3	,	'83'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital de l"ouest'	,	'Brest'	,	'56 avenue du général'	,	'29200'	,	5	,	3	,	'29'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital du capitaine'	,	'Nantes'	,	'176 avenue des preuves'	,	'44000'	,	5	,	3	,	'44'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital du soleil'	,	'Sainte-Savine'	,	'87 rue de l"observatoire'	,	'10300'	,	5	,	3	,	'10'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital parisien'	,	'Paris'	,	'16 rue de l"espoir'	,	'75000'	,	5	,	3	,	'75'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital perdu'	,	'Saint-Denis'	,	'9 rue bienvenu'	,	'93200'	,	5	,	3	,	'93'	);
insert into 	hopital	(nomHop,villeH,rueH,codePostH,nb_places,nb_Supplementaire,noDep)	values(	'Hopital de l est'	,	'Reims'	,	'59 avenue de la vengeance'	,	'51100'	,	5	,	3	,	'51'	);


--Patient
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'197015434588921'	,	'MELZ'	,	'Kenny'	,	'M'	,	'1997-01-02'	,	'0767893456'	,	'34 rue de la république '	,	'Nancy'	,	'54000'	,	'17-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'197125456732167'	,	'COSSIN'	,	'Alain'	,	'M'	,	'1997-12-01'	,	'0609879837'	,	'109 avenue charles'	,	'Toul'	,	'54200'	,	'17-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'199071065428912'	,	'CHOQUERT'	,	'Vincent'	,	'M'	,	'1999-07-27'	,	'0645329087'	,	'4 impasse foch'	,	'Troyes'	,	'10300'	,	'19-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	10	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'299075476831598'	,	'BEIRAO'	,	'Camille'	,	'F'	,	'1999-07-22'	,	'0678542398'	,	'76 rue des triangles'	,	'Nancy'	,	'54000'	,	'19-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'256091386415378'	,	'BOUCHE'	,	'Valentine'	,	'F'	,	'1956-09-29'	,	'0716549857'	,	'21 impasse réseau'	,	'Marseille'	,	'13000'	,	'20-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	13	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'289021376489312'	,	'KOCH'	,	'Anne-Sophie'	,	'F'	,	'1989-02-16'	,	'0363961634'	,	'107 avenue Foch'	,	'Marseille'	,	'13000'	,	'21-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	13	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'103075478656432'	,	'GABORIT'	,	'Florian'	,	'M'	,	'2003-07-01'	,	'0678527963'	,	'31 rue des tilleuls'	,	'Nancy'	,	'54000'	,	'21-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'186081045321789'	,	'ZAHM'	,	'Florian'	,	'M'	,	'1986-08-13'	,	'0692349086'	,	'23 rue jules vierne'	,	'Sainte Savine'	,	'10300'	,	'21-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	10	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'154105798761276'	,	'PIERRAT'	,	'Charly'	,	'M'	,	'1954-10-21'	,	'0612345679'	,	'5 rue edouard branly'	,	'Metz'	,	'57000'	,	'22-03-2020 00:00:00'	,	NULL	,	'inconscient'	,	57	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'154086765743219'	,	'DUPONT'	,	'Félix'	,	'M'	,	'1954-08-03'	,	'0967860942'	,	'90 avenue des brasseurs'	,	'Strasbourg'	,	'67000'	,	'23-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	67	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'232078359817652'	,	'SOQUET'	,	'Chloé'	,	'F'	,	'1932-07-02'	,	'0654897529'	,	'57 rue de la soif'	,	'Toulon'	,	'83000'	,	'25-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	83	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'176025484534513'	,	'POLLET'	,	'Antoine'	,	'M'	,	'1976-02-16'	,	'0676468965'	,	'1 rue des montagnes'	,	'Tomblaine'	,	'54510'	,	'25-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'165092978565432'	,	'BONCI'	,	'Jérémy'	,	'M'	,	'1965-09-13'	,	'0632675498'	,	'37 avenue saint jean'	,	'Brest '	,	'29200'	,	'26-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	29	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'259084498756423'	,	'LOUIS'	,	'Cécile'	,	'F'	,	'1959-08-17'	,	'0753798523'	,	'45 rue des 4 églises'	,	'Nantes'	,	'44000'	,	'26-03-2020 00:00:00'	,	NULL	,	'inconscient'	,	44	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'267013576816267'	,	'CALLONEGO'	,	'Colleen'	,	'F'	,	'1967-01-27'	,	'0787895654'	,	'98 avenue du général'	,	'Rennes'	,	'35000'	,	'26-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	35	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'287127598817656'	,	'DANI'	,	'Oumayma'	,	'F'	,	'1987-12-12'	,	'0654546878'	,	'3 impasse des tuiles'	,	'Paris'	,	'75000'	,	'27-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	75	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'289109398714590'	,	'RACHEDI'	,	'Souha'	,	'F'	,	'1989-10-10'	,	'0623245785'	,	'5 rue faubourg'	,	'Saint Denis'	,	'93200'	,	'27-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	93	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'274017598010287'	,	'CHOLLEY'	,	'Wendie'	,	'F'	,	'1974-01-13'	,	'0613156837'	,	'6 rue tesla'	,	'Paris'	,	'75000'	,	'27-03-2020 00:00:00'	,	NULL	,	'inconscient'	,	75	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'179067567514578'	,	'PRUGNE'	,	'Robin'	,	'M'	,	'1979-06-25'	,	'0698780347'	,	'8 rue Edison'	,	'Paris'	,	'75000'	,	'28-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	75	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'188067567545383'	,	'CRINON'	,	'Nicolas'	,	'M'	,	'1988-06-14'	,	'0754073849'	,	'32 avenue Descartes'	,	'Paris'	,	'75000'	,	'29-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	75	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'190105178865437'	,	'ARNOULD'	,	'Maxime'	,	'M'	,	'1990-10-12'	,	'0726490617'	,	'21 rue Newton'	,	'Reims'	,	'51100'	,	'29-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	51	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'178015776614282'	,	'CHABBERT'	,	'Benjamin'	,	'M'	,	'1978-01-14'	,	'0709831579'	,	'20 Avenue Tyson'	,	'Metz'	,	'57000'	,	'30-03-2020 00:00:00'	,	NULL	,	'fièvre'	,	57	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'169015497716291'	,	'DA SILVA'	,	'Alexandre'	,	'M'	,	'1969-01-15'	,	'0325769856'	,	'60 rue Plank'	,	'Tomblaine'	,	'54510'	,	'01-04-2020 00:00:00'	,	NULL	,	'fièvre'	,	54	);
insert into 	patient (num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'183055493210784'	,	'JACQUET'	,	'Alexi'	,	'M'	,	'1983-05-17'	,	'0798564329'	,	'7 impasse Vernes'	,	'Nancy'	,	'54000'	,	'03-04-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'178015465532170'	,	'SOHBI'	,	'Elias'	,	'M'	,	'1978-01-15'	,	'0923764509'	,	'5 rue Chloroquine'	,	'Laxou'	,	'54520'	,	'06-04-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'171095134476518'	,	'JAMAN'	,	'Gael'	,	'M'	,	'1971-09-14'	,	'0386574319'	,	'3 rue Corona'	,	'Reims'	,	'51100'	,	'06-04-2020 00:00:00'	,	NULL	,	'inconscient'	,	51	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'246055496714372'	,	'AIT HSAINE'	,	'Myriam'	,	'F'	,	'1946-05-04'	,	'0795485135'	,	'2 impasse du dromadaire'	,	'Toul'	,	'54200'	,	'08-03-2020 00:00:00'	,	NULL	,	'fièvre et pb respiratoires'	,	54	);
insert into 	patient	(num_secu, nom,prenom, sexe, date_naissance, num_tel, rueP, villeP, codePostP, debut_surveillance, fin_surveillance, etat_sante,noDep)	values(	'167085139982447'	,	'KANE'	,	'Boubou'	,	'M'	,	'1967-08-09'	,	'0694387650'	,	'8 rue Sherlock'	,	'Reims'	,	'51100'	,	'10-04-2020 00:00:00'	,	NULL	,	'fièvre'	,	51	);

--Hopistalisé
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'17/03/2020 06:00:00'	,	2	,	'197125456732167'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'24/03/2020 08:00:00'	,	1	,	'299075476831598'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'23/03/2020 07:00:00'	,	1	,	'103075478656432'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'30/03/2020 00:00:00'	,	5	,	'154105798761276'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'27/03/2020 00:00:00'	,	6	,	'154086765743219'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'25/03/2020 00:00:00'	,	7	,	'232078359817652'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'30/03/2020 00:00:00'	,	1	,	'176025484534513'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'01/04/2020 00:00:00'	,	9	,	'259084498756423'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'29/03/2020 00:00:00'	,	11	,	'287127598817656'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'29/03/2020 00:00:00'	,	11	,	'274017598010287'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'03/04/2020 00:00:00'	,	1	,	'183055493210784'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'10/04/2020 00:00:00'	,	2	,	'178015465532170'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'06/04/2020 00:00:00'	,	13	,	'171095134476518'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values(	'12/03/2020 00:00:00'	,	2	,	'246055496714372'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values('10-04-2020 00:00:00'	,	1	,	'167085139982447'	);
insert into 	hospitalise	(debut_hospitalisation, noHopital, num_secuP)	values('17-03-2020 00:00:00'	,	1	,	'197015434588921'	);
