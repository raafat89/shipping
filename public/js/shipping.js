$(document).ready(function () {
    $('input.check_lines').change(function () {
        $('input.to_send').prop('checked', $(this).prop("checked"));
    });
    $('input.to_send').change(function () {
        $('input.check_lines').prop('checked', false);
    });

    $('form input').keydown(function (e) {
        if (e.keyCode == 13) {
            var inputs = $(this).parents("form").eq(0).find(":input");
            if (inputs[inputs.index(this) + 1] != null) {
                inputs[inputs.index(this) + 1].focus();
            }
            e.preventDefault();
            return false;
        }
    });

    $('.track_no').keydown(function (e) {
        if (e.keyCode == 13) {
            $(this).val($(this).val());
        }
    });

    $(".row_0").highlightRow();
    $(".row_1").highlightRow();

    $(".date, #ship_date").datepicker();

    if ($("table.shipping_sort_table").length) {
        $(".shipping_sort_table").dataTable({
            "sDom": 'p<"top"f>',
            "bPaginate": false,
            "bLengthChange": false,
            "bInfo": false,
            "bAutoLength": false,
            "aaSorting": [[0, "asc"]]
        });
    }

    $("select#alpha_code").change(function () {
        var selectedCountry = $(this).children("option:selected").text();
        var carrier = selectedCountry.split("-");
        $("input#carrier").val(carrier[1]);
    });

    $('.save_change').change(function (e) {
        var id = $(this).attr('id');
        $("#" + id).css({border: '1px solid grey'}).animate({}, 500);
    });

    $('#XML, #XML_sep').click(function (e) {
        var error = 0;
        var pallets = parseInt($('input#number_of_pallets').val());
        $('.save_change').each(function () {
            if (!$(this).val()) {
                $(this).focus();
                $(this).css({border: '0 solid #f37736'}).animate({
                    borderWidth: 4
                }, 500);
                error++;
            }
        });
        if (pallets === 0 && !$('input#tracking_number').val())
            error--;
        if (error > 0)
            e.preventDefault();
    });

    $('.Qty').keyup(function (e) {
        var str_id = e.target.id.split("_");
        var id = str_id.pop();
        var lbl = Math.floor($('#Qty_' + id).val() / $('#QtyPei_' + id).val());
        var part = Math.ceil($('#Qty_' + id).val() - (lbl * $('#QtyPei_' + id).val()));
//        if (part>0 && lbl!=1){
//            lbl  = lbl -1;
//            part = Math.ceil($('#Qty_'+id).val()-(lbl*$('#QtyPei_'+id).val()));
//        }
        var sum = 0;
        var lbl_from = 1;
        var lbl_to = 0;
        var lbl_range_txt = '';
        if (lbl != 'Infinity' && !isNaN(lbl))
            $('#labels_' + id).html(lbl);
        else
            $('#labels_' + id).html('0');
        if (part != 0 && !isNaN(part))
            $('#part_labels_' + id).html('1(' + part + ')');
        else
            $('#part_labels_' + id).html('');
        $('.labels').each(function () {
            var l_id = $(this).attr('id');
            var str_p_id = $("#part_" + l_id).html();
            if ($(this).html() != 'Infinity' && !isNaN($(this).html()))
                sum += Number($(this).html());
            if (str_p_id.trim())
                sum += 1;

            lbl_to = lbl_to + Number($(this).html()) + ((str_p_id.trim()) ? 1 : 0);
            if ($(this).html() == 1 && str_p_id.trim() == "")
                lbl_range_txt = lbl_to + ((str_p_id.trim()) ? 1 : 0);
            else
                lbl_range_txt = lbl_from + '-' + (lbl_to);
            lbl_from = lbl_to + 1;
            $("#range_" + l_id).html(lbl_range_txt);
        });
        $('#gros_qty').html(sum);
        $('#landing_qty').val(sum);
        $('#XML').remove();
    });

    $('.update_line').click(function (e) {
        var line = $(this).attr('data-line');
        $("#line_row_" + line).find('.not_equal').removeClass("not_equal");
    });

    $('.lot_exp').change(function (e) {
        var line = $(this).attr('data-line');
        var order_no = $("input[name='order_no']").val();
        var exp_date = Date.parse($("#line_exp_" + line).val());
        var lot_no = $("#line_lot_" + line).val();
        var url = "/shipping/edititem/orderno/" + order_no + "/line/" + line + "/exp_date/" + exp_date + "/lot_no/" + lot_no;

        $("#line_url_" + line).attr('href', url);
        $("#line_row_" + line).css("background-color", "red");
    });

    $(".user_table").dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bInfo": false,
        "bAutoLength": false,
        "aaSorting": [[0, "asc"]]
    });

    $('#submitbtn').click(function () {
        $(this).css('display', 'none');
        $("div#btn_loader").html('<img src="/css/images/ajax-loader1.gif" />');
        var loads = 0;
        $("#viewimage").html('');
        $(".uploadform").ajaxForm({
            target: '#viewimage'
        }).submit();
        $(document).ajaxComplete(function (event, xhr, settings) {

            loads++;
            if (loads == 1) {
                $("#viewimage").html('<img src="/css/images/ajax-loader1.gif" />');
                $('#upload_input,#scan_input').hide();
                $('#print').html('');
            } else if (loads > 1) {
                //alert(xhr.responseText);
                $('#upload_input,#scan_input').show();
                //if($('#viewimage').text()=='')
                //$('#viewimage').html('<div style="font-size:24px; color:blue; margin:20px;"><b>Upload completed successfully!<b></div>');
                //alert("Upload completed successfully!");
                location.reload();
            }
        });
    });
});