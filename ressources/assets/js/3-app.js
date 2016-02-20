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
});
