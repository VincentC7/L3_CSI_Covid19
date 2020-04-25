$(".hopital").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".departement").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".btn-swap").click(function () {
   $(this).parent().parent().parent().next().toggleClass('hidden');
});

var type_recherche = 0;
$('#rechercher-patient').keyup(function () {
    var search = $(this).val();
    search=search.toLowerCase();
    $.each($('#liste-patients').children(), function () {
        if (type_recherche === 0){
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
   type_recherche = $(this).val();
});