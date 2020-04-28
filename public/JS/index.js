$(".hopital").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".departement").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".btn-swap").click(function () {
   $(this).parent().parent().parent().next().toggleClass('hidden');
});

$(".btn-modif-patient").click(function () {
    $('.form-modif-patient').toggleClass('hidden')
});

$(".btn-modif-etat-patient").click(function () {
    $('.form-modif-etat-patient').toggleClass('hidden')
});



var type_recherche_patient = 0;
$('#rechercher-patient').keyup(function () {
    var search = $(this).val();
    search=search.toLowerCase();
    $.each($('#liste-patients').children(), function () {
        if (type_recherche_patient === 0){
            var nom = $(this).children().children("div:first").text();
            var prenom = $(this).children().children("div:nth-child(2)").text();
            prenom = prenom.toLowerCase();
            nom = nom.toLowerCase();
            if (nom.includes(search) || prenom.includes(search)){
                $(this).removeClass('hidden');
            }else {
                $(this).addClass('hidden');
            }
        }else{
            var num_secu = $(this).children().children("div:first").data("num_secu");
            num_secu+='';
            if (num_secu.includes(search)){
                $(this).removeClass('hidden');
            }else {
                $(this).addClass('hidden');
            }
        }
    })
});

$('#select-recherche-patient').change(function () {
    type_recherche_patient = $(this).val();
});

var type_recherche_hopital = 0;
$('#rechercher-hopital').keyup(function () {
    var search = $(this).val();
    search=search.toLowerCase();
    $.each($('#list-hopital').children(), function () {
        var champ;
        if (type_recherche_hopital === 0){
            champ = $(this).children().children('div:first').children('div:first').text();
        }else{
            champ = $(this).children().children('div:first').children('div:first').data("nodep") +'';
        }
        champ = champ.toLowerCase();
        if (champ.includes(search)){
            $(this).removeClass('hidden');
        }else {
            $(this).addClass('hidden');
        }
    })
});

$('#select-recherche-hopital').change(function () {
    type_recherche_hopital = $(this).val();
});



var type_recherche_departement = 0;
$('#rechercher-departement').keyup(function () {
    var search = $(this).val();
    search=search.toLowerCase();
    $.each($('#list-departement').children(), function () {
        var champ;
        if (type_recherche_hopital === 0){
            champ = $(this).children().children('div:first').children('div:first').text();
        }else{
            champ = $(this).children().children('div:first').children('div:first').data("nodep") +'';
        }
        champ = champ.toLowerCase();
        if (champ.includes(search)){
            $(this).removeClass('hidden');
        }else {
            $(this).addClass('hidden');
        }
    })
});

$('#select-recherche-departement').change(function () {
    type_recherche_departement = $(this).val();
});

