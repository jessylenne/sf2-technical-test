$(function() {
    // Page commentaires
    $('#repository').change(function() {
        var val = $(this).find('option:selected').data('for');

        if(val == 'all')
            return $('#comment_form_panel_footer').slideUp();

        $('#comment_form_panel_footer').slideDown();
        $('.repository_detail').slideUp();
        $('#repository_detail_'+val).slideDown();
    }).change();

    // Champs de recherche autocomplete
    $(".search-form input[type=text]").autocomplete({
        source: "/index.php?controller=comments&submitSearchAccount=1&",
        minLength: 4,
        select: function( event, ui ) {
            $(this).val(ui.item.value);
            window.location.href = "/index.php?controller=comments&user="+ui.item.value;
        }
    });
});
