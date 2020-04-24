$(".hopital").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".departement").click(function () {
    $(this).next().toggleClass('hidden');
});

$(".btn-swap").click(function () {
   $(this).parent().parent().parent().next().toggleClass('hidden');
});