// JavaScript Document
$(document).ready(function ()
{
    $(".row_0").highlightRow();
    $(".row_1").highlightRow();

    //Container Type
    var cont_type = $("select#container_type").val();
    if (cont_type == 'Bottle')
        $("tr#bottle").slideDown();
    else if (cont_type == 'Box')
        $("tr#box").slideDown();
    else if (cont_type == 'Packet')
        $("tr#packet").slideDown();
    else
        $("tr#packet,tr#box,tr#bottle").hide();


//AJAX FUNCTIONS        
    $('select#container_type').live("change", function (e) {
        var type = 'changetype';
        var cont_type = $(this).val();
        $("tr#packet,tr#box,tr#bottle").hide();
        if (cont_type == 'Bottle')
            $("tr#bottle").slideDown();
        else if (cont_type == 'Box')
            $("tr#box").slideDown();
        else if (cont_type == 'Packet')
            $("tr#packet").slideDown();
    });

    var weight_val = $("select#captab_weight").val();
    if (weight_val == 1)
        $("tr#shell_weight_info").show();
    else if (weight_val == 0)
        $("tr#shell_weight_info").hide();
    $('select#captab_weight').live("change", function (e) {
        var weight_val = $(this).val();
        if (weight_val == 1)
            $("tr#shell_weight_info").fadeIn();
        else if (weight_val == 0)
            $("tr#shell_weight_info").fadeOut();
    });

    $(".row_0").highlightRow();
    $(".row_1").highlightRow();


    $(".variables_table").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bInfo": false,
        "bAutoLength": false,
        "aaSorting": [[0, "asc"]]
    });

    $(".variables_activate").confirmation("Are you sure you want to activate this item?");
    $(".variables_deactivate").confirmation("Are you sure you want to deactivate this item?");

});