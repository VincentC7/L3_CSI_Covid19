drop table Hospitalise;
drop table Patient;
drop table Hopital;
drop table Departement;


create table Departement
(
    noDep           VARCHAR(3)
        constraint pk_departement PRIMARY KEY,
    nomDep          varchar(32) not null,
    seuil_contamine integer     not null
        constraint domain_departement_seuil_contamine CHECK (seuil_contamine >= 0)
);

create table Patient
(
    num_secu           char(15)    not null
        constraint pk_patient PRIMARY KEY,
    nom                varchar(30) not null,
    prenom             varchar(30) not null,
    sexe               char(1)     null
        constraint domain_patient_sexe CHECK (sexe in ('m', 'f', 'M', 'F')),
    date_naissance     date        not null,
    num_tel            char(10)    not null,
    rueP               varchar(64) not null,
    villeP             varchar(32) not null,
    codePostP          char(5)     not null,
    debut_surveillance timestamp   not null
        constraint domain_patient_date CHECK (debut_surveillance > date_naissance),
    fin_surveillance   timestamp default null,
    etat_sante         varchar(33) not null
        constraint domain_patient_etat CHECK (etat_sante in
                                              ('aucun symptome', 'fièvre', 'fièvre et problèmes respiratoires',
                                               'inconscient', 'décédé')),
    noDep              VARCHAR(3)  not null
        constraint FK_Patient_noDep REFERENCES Departement (noDep)
);


create table Hopital
(
    noHopital         serial
        constraint pk_hopital PRIMARY KEY,
    nomHop            varchar(32) not null,
    villeH            varchar(32) not null,
    rueH              varchar(32) not null,
    code_PostH        char(5)     not null,
    nb_places         integer     not null
        constraint domain_hopital_nb_place CHECK (nb_places >= 0),
    nb_supplementaire integer default 0
        constraint domain_hopital_nb_supplementaire CHECK (nb_supplementaire >= 0),
    nb_libres         integer     not null
        constraint domain_hopital_nb_libre CHECK (nb_libres >= 0 AND nb_libres <= (nb_supplementaire + nb_places)),
    noDep             VARCHAR(3)  not null
        constraint FK_Hopital_noDep REFERENCES Departement (noDep)
);

create table Hospitalise
(
    noHospitalisation     serial
        constraint pk_hospitalisation PRIMARY KEY,
    debut_hospitalisation timestamp not null,
    fin_hospitalisation   timestamp default null,
    noHopital             integer   not null
        constraint FK_Hospitalise_noHopital REFERENCES Hopital (noHopital),
    num_secuP             char(15)  not null
        constraint FK_Hospitalise_num_secuP REFERENCES Patient (num_secu)
);



insert into Departement (noDep, nomDep, seuil_contamine)
values ('1', 'Ain', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('2', 'Aisne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('3', 'Allier', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('4', 'Alpes-de-Haute-Provence', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('5', 'Hautes-Alpes', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('6', 'Alpes-Maritimes', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('7', 'Ardèche', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('8', 'Ardennes', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('9', 'Ariège', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('10', 'Aube', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('11', 'Aude', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('12', 'Aveyron', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('13', 'Bouches-du-Rhône', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('14', 'Calvados', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('15', 'Cantal', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('16', 'Charente', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('17', 'Charente-Maritime', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('18', 'Cher', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('19', 'Corrèze', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('2A', 'Corse-du-Sud', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('2B', 'Haute-Corse', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('21', 'Côte-d''Or', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('22', 'Côtes d''Armor', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('23', 'Creuse', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('24', 'Dordogne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('25', 'Doubs', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('26', 'Drôme', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('27', 'Eure', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('28', 'Eure-et-Loir', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('29', 'Finistère', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('30', 'Gard', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('31', 'Haute-Garonne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('32', 'Gers', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('33', 'Gironde', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('34', 'Hérault', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('35', 'Ille-et-Vilaine', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('36', 'Indre', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('37', 'Indre-et-Loire', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('38', 'Isère', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('39', 'Jura', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('40', 'Landes', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('41', 'Loir-et-Cher', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('42', 'Loire', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('43', 'Haute-Loire', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('44', 'Loire-Atlantique', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('45', 'Loiret', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('46', 'Lot', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('47', 'Lot-et-Garonne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('48', 'Lozère', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('49', 'Maine-et-Loire', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('50', 'Manche', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('51', 'Marne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('52', 'Haute-Marne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('53', 'Mayenne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('54', 'Meurthe-et-Moselle', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('55', 'Meuse', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('56', 'Morbihan', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('57', 'Moselle', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('58', 'Nièvre', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('59', 'Nord', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('60', 'Oise', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('61', 'Orne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('62', 'Pas-de-Calais', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('63', 'Puy-de-Dôme', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('64', 'Pyrénées-Atlantiques', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('65', 'Hautes-Pyrénées', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('66', 'Pyrénées-Orientales', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('67', 'Bas-Rhin', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('68', 'Haut-Rhin', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('69', 'Rhône', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('70', 'Haute-Saône', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('71', 'Saône-et-Loire', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('72', 'Sarthe', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('73', 'Savoie', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('74', 'Haute-Savoie', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('75', 'Paris', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('76', 'Seine-Maritime', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('77', 'Seine-et-Marne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('78', 'Yvelines', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('79', 'Deux-Sèvres', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('80', 'Somme', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('81', 'Tarn', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('82', 'Tarn-et-Garonne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('83', 'Var', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('84', 'Vaucluse', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('85', 'Vendée', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('86', 'Vienne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('87', 'Haute-Vienne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('88', 'Vosges', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('89', 'Yonne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('90', 'Territoire de Belfort', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('91', 'Essonne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('92', 'Hauts-de-Seine', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('93', 'Seine-St-Denis', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('94', 'Val-de-Marne', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('95', 'Val-D''Oise', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('971', 'Guadeloupe', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('972', 'Martinique', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('973', 'Guyane', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('974', 'La Réunion', 5);
insert into Departement (noDep, nomDep, seuil_contamine)
values ('976', 'Mayotte', 5);



insert into patient
values ('197015434588921', 'MELZ', 'Kenny', 'M', '1997-01-02', '0767893456', '34 rue de la république ', 'Nancy',
        '54000', '17-03-2020 00:00:00', NULL, 'fièvre', 54);
insert into patient
values ('197125456732167', 'COSSIN', 'Alain', 'M', '1997-12-01', '0609879837', '109 avenue charles', 'Toul', '54200',
        '17-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('199071065428912', 'CHOQUERT', 'Vincent', 'M', '1999-07-27', '0645329087', '4 impasse foch', 'Troyes', '10300',
        '19-03-2020 00:00:00', NULL, 'fièvre', 10);
insert into patient
values ('299075476831598', 'BEIRAO', 'Camille', 'F', '1999-07-22', '0678542398', '76 rue des triangles', 'Nancy',
        '54000', '19-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('256091386415378', 'BOUCHE', 'Valentine', 'F', '1956-09-29', '0716549857', '21 impasse réseau', 'Marseille',
        '13000', '20-03-2020 00:00:00', NULL, 'fièvre', 13);
insert into patient
values ('289021376489312', 'KOCH', 'Anne-Sophie', 'F', '1989-02-16', '0363961634', '107 avenue Foch', 'Marseille',
        '13000', '21-03-2020 00:00:00', NULL, 'fièvre', 13);
insert into patient
values ('103075478656432', 'GABORIT', 'Florian', 'M', '2003-07-01', '0678527963', '31 rue des tilleuls', 'Nancy',
        '54000', '21-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('186081045321789', 'ZAHM', 'Florian', 'M', '1986-08-13', '0692349086', '23 rue jules vierne', 'Sainte Savine',
        '10300', '21-03-2020 00:00:00', NULL, 'fièvre', 10);
insert into patient
values ('154105798761276', 'PIERRAT', 'Charly', 'M', '1954-10-21', '0612345679', '5 rue edouard branly', 'Metz',
        '57000', '22-03-2020 00:00:00', NULL, 'inconscient', 57);
insert into patient
values ('154086765743219', 'DUPONT', 'Félix', 'M', '1954-08-03', '0967860942', '90 avenue des brasseurs', 'Strasbourg',
        '67000', '23-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 67);
insert into patient
values ('232078359817652', 'SOQUET', 'Chloé', 'F', '1932-07-02', '0654897529', '57 rue de la soif', 'Toulon', '83000',
        '25-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 83);
insert into patient
values ('176025484534513', 'POLLET', 'Antoine', 'M', '1976-02-16', '0676468965', '1 rue des montagnes', 'Tomblaine',
        '54510', '25-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('165092978565432', 'BONCI', 'Jérémy', 'M', '1965-09-13', '0632675498', '37 avenue saint jean', 'Brest ',
        '29200', '26-03-2020 00:00:00', NULL, 'fièvre', 29);
insert into patient
values ('259084498756423', 'LOUIS', 'Cécile', 'F', '1959-08-17', '0753798523', '45 rue des 4 églises', 'Nantes',
        '44000', '26-03-2020 00:00:00', NULL, 'inconscient', 44);
insert into patient
values ('267013576816267', 'CALLONEGO', 'Colleen', 'F', '1967-01-27', '0787895654', '98 avenue du général', 'Rennes',
        '35000', '26-03-2020 00:00:00', NULL, 'fièvre', 35);
insert into patient
values ('287127598817656', 'DANI', 'Oumayma', 'F', '1987-12-12', '0654546878', '3 impasse des tuiles', 'Paris', '75000',
        '27-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 75);
insert into patient
values ('289109398714590', 'RACHEDI', 'Souha', 'F', '1989-10-10', '0623245785', '5 rue faubourg', 'Saint Denis',
        '93200', '27-03-2020 00:00:00', NULL, 'fièvre', 93);
insert into patient
values ('274017598010287', 'CHOLLEY', 'Wendie', 'F', '1974-01-13', '0613156837', '6 rue tesla', 'Paris', '75000',
        '27-03-2020 00:00:00', NULL, 'inconscient', 75);
insert into patient
values ('179067567514578', 'PRUGNE', 'Robin', 'M', '1979-06-25', '0698780347', '8 rue Edison', 'Paris', '75000',
        '28-03-2020 00:00:00', NULL, 'fièvre', 75);
insert into patient
values ('188067567545383', 'CRINON', 'Nicolas', 'M', '1988-06-14', '0754073849', '32 avenue Descartes', 'Paris',
        '75000', '29-03-2020 00:00:00', NULL, 'fièvre', 75);
insert into patient
values ('190105178865437', 'ARNOULD', 'Maxime', 'M', '1990-10-12', '0726490617', '21 rue Newton', 'Reims', '51100',
        '29-03-2020 00:00:00', NULL, 'fièvre', 51);
insert into patient
values ('178015776614282', 'CHABBERT', 'Benjamin', 'M', '1978-01-14', '0709831579', '20 Avenue Tyson', 'Metz', '57000',
        '30-03-2020 00:00:00', NULL, 'fièvre', 57);
insert into patient
values ('169015497716291', 'DA SILVA', 'Alexandre', 'M', '1969-01-15', '0325769856', '60 rue Plank', 'Tomblaine',
        '54510', '01-04-2020 00:00:00', NULL, 'fièvre', 54);
insert into patient
values ('183055493210784', 'JACQUET', 'Alexi', 'M', '1983-05-17', '0798564329', '7 impasse Vernes', 'Nancy', '54000',
        '03-04-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('178015465532170', 'SOHBI', 'Elias', 'M', '1978-01-15', '0923764509', '5 rue Chloroquine', 'Laxou', '54520',
        '06-04-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('171095134476518', 'JAMAN', 'Gael', 'M', '1971-09-14', '0386574319', '3 rue Corona', 'Reims', '51100',
        '06-04-2020 00:00:00', NULL, 'inconscient', 51);
insert into patient
values ('246055496714372', 'AIT HSAINE', 'Myriam', 'F', '1946-05-04', '0795485135', '2 impasse du dromadaire', 'Toul',
        '54200', '08-03-2020 00:00:00', NULL, 'fièvre et problèmes respiratoires', 54);
insert into patient
values ('167085139982447', 'KANE', 'Boubou', 'M', '1967-08-09', '0694387650', '8 rue Sherlock', 'Reims', '51100',
        '10-04-2020 00:00:00', NULL, 'fièvre', 51);
