(function ($)
{
    // this is the default function for loading a link into a div
    function loadLinkToDiv(target_link, target_div)
    {
        $.get(target_link, function (data) {
            $(target_div).empty().append(data);

            tinyMCE.init({
                //Configure the text editor
                mode: "textareas",
                theme: "advanced",
                plugins: "safari,spellchecker,pagebreak,style,layer,table,advhr,inlinepopups,media,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
                theme_advanced_toolbar_location: "top",
                //We only want to show a few formatting objects in row one, row 2 and 3 are left empty
                theme_advanced_buttons1: "print,|,bold,italic,underline,|,justifyleft,justifycenter,justifyfull,|,bullist,numlist,|,indent,outdent,hr,|,sub,sup,charmap",
                theme_advanced_buttons2: "tablecontrols",
                theme_advanced_buttons3: ""
            });
        });
    }

    $.fn.requiredFields = function (fields)
    {
        var clicked = false;
        $(this).live('submit', function (e)
        {
            if (clicked) {
                e.preventDefault();
                alert("Please only press submit once.");
                return false;
            }

            clicked = true;
            var message = "";
            var error = false;
            for (var i = 0; i < fields.length; ++i) {
                if ($("#" + fields[i]).val() == "" ||
                        (tinyMCE.get(fields[i]) != null && tinyMCE.get(fields[i]).getContent() == "")) {
                    error = true;
                    message += fields[i] + " is a required field and can't be empty.\n";
                }
            }

            if (error) {
                e.preventDefault();
                clicked = false;
                alert(message);
                return false;
            }
        });
    };

    $.fn.convertSelectToText = function (parameters)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();
            $(parameters['select']).parent().empty().append(
                    "<input type='text' name='" + parameters['name'] + "' size='" + parameters['size'] + "' id='" + parameters['id'] + "' class='" + parameters['class'] + "' value=''></input>"
                    );
            return false;
        });
    };

    $.fn.altConvertSelectToText = function (parameters)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();
            var tokens = $(this).attr('id').split("_");
            var field_name = tokens[0] + "_" + parameters['select'];
            if (tokens.length > 1) {
                field_name += "_" + tokens[1];
            }

            $("#" + field_name).parent().empty().append(
                    "<input type='text' name='" + field_name + "' size='" + parameters['size'] + "' id='" + field_name + "' class='" + parameters['class'] + "' value=''></input>"
                    );
            return false;
        });
    };

    // open a link into designated div
    $.fn.openInDiv = function (parameters)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();

            var target_link = $(this).attr('href') + parameters['SUFFIX'];
            $(parameters['DIV']).toggle();
            $(parameters['LOADING']).toggle();

            $.get(target_link, function (data) {
                $(parameters['DIV']).empty().append(data);
                $(parameters['LOADING']).toggle();
                $(parameters['DIV']).toggle();
            });

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // accordian effetc
    $.fn.accordian = function (prefix)
    {
        var current_id = 0;

        $(this).live('click', function (e)
        {
            e.preventDefault();

            if ($(this).attr('id') != current_id) {
                var new_id = $(this).attr('id');
                $("#" + prefix + current_id).toggle();
                $("#" + prefix + new_id).toggle();
                current_id = new_id;
            } else {
                $("#" + prefix + current_id).toggle();
                current_id = 0;
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // make a section of the site visible after a radio button is pressed
    $.fn.makeSectionVisible = function (section_name, flag)
    {
        $(this).live('change', function (e)
        {
            if (flag && $(section_name).is(":hidden")) {
                $(section_name).toggle();
            } else if (!flag && !$(section_name).is(":hidden")) {
                $(section_name).toggle();
            }
        });
    };

    // this will create a dictionary array for the auto complete feature
    $.fn.loadAutoComplete = function (address, field)
    {
        var dict_keys = new Array();
        function buildDictionary() {
            $.get(address, function (xml) {
                $(xml).find("customer").each(function () {
                    dict_keys.push($(this).find('customer_no').text());
                });

                $(field).autocomplete({
                    source: dict_keys
                });
            });
        }

        buildDictionary();
    };

    // this will create a dictionary array for the auto complete feature
    $.fn.altLoadAutoComplete = function (parameters)
    {
        var dict_keys = new Array();
        var field;
        if (parameters['field'] === undefined) {
            field = "#" + $(this).attr('id');
        } else {
            field = parameters['field'];
        }

        function buildDictionary() {
            $.get(parameters['address'], function (xml) {
                $(xml).find(parameters['container']).each(function () {
                    dict_keys.push($(this).find(parameters['value']).text());
                });
                $(field).autocomplete({
                    source: dict_keys
                });
            });
        }

        buildDictionary();
    };

    // display a div when an input field is selected
    $.fn.displayOnSelect = function (div)
    {
        $(this).live('focus', function (e)
        {
            if ($(div).is(":hidden")) {
                $(div).css({
                    position: "absolute",
                    top: "50%",
                    left: "50%",
                    marginLeft: "-95px",
                    marginTop: "-95px",
                    zIndex: "1000"
                });
                $(div).toggle();
            }
        });

        $(this).live('focusout', function (e)
        {
            if (!$(div).is(":hidden")) {
                $(div).toggle();
            }
        });
    };

    // this will prompt the user with a confirmation screen before continuing with the selected action
    $.fn.confirmation = function (message)
    {
        $(this).live('click', function (e)
        {
            if (e.button == 0) {
                var answer = confirm(message);
                return answer;
            } else {
                e.preventDefault();
                return false;
            }
        });
        $(this).live('submit', function (e)
        {
            if (e.button == 0) {
                var answer = confirm(message);
                return answer;
            } else {
                e.preventDefault();
                return false;
            }
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will put a yellow border around any row that is the focus of the users mouse
    $.fn.highlightRow = function ()
    {
        var current_class = 0;

        return this.each(function ()
        {
            $(this).hover(function (e)
            {
                current_class = $(this).attr('class');
                var current_id = "#" + $(this).attr('id');

                $(current_id).removeClass(current_class);
                $(current_id).addClass("highlight");
            }, function (e)
            {
                var current_id = "#" + $(this).attr('id');

                $(current_id).removeClass("highlight");
                $(current_id).addClass(current_class);
            });
        });
    };

    // this will put a yellow border around any row that is the focus of the users mouse
    $.fn.clickRollDown = function (activate_class)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();
            var rollover_id = $(this).attr('id');
            var class_value = activate_class + rollover_id;
            $(class_value).slideToggle();

            return false;
        });
    };

    // this will preform a selected action when an item is selected
    $.fn.selectRedirect = function ()
    {
        return this.each(function ()
        {
            $(this).change(function (e)
            {
                var selected_link = $(this).val();
                if (selected_link != "#") {
                    $(location).attr('href', selected_link);
                }
            });
        });
    };

    $.fn.retractableAction = function (parameters)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();

            var selected_id = $(this).attr('id');
            $(parameters['prefix'] + selected_id + parameters['suffix']).slideToggle();

            return false;
        });
    };

    // this will create an expanding and retracting effect on lists
    $.fn.accordianAction = function (expand_list)
    {
        var current_id = -1;

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0) {
                if (current_id != $(this).attr('id')) {
                    $(expand_list + "_" + current_id).slideToggle();
                    current_id = $(this).attr('id');
                    $(expand_list + "_" + current_id).slideToggle();
                } else {
                    $(expand_list + "_" + current_id).slideToggle();
                    current_id = -1;
                }
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will load the selected link inside the given div
    $.fn.assignmentActionInDiv = function (definitions)
    {
        var locked = false;

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0 && !locked) {
                var current_link = $(this).attr('href');
                var current_class = $(this).attr('class');
                var current_id = $(this).attr('id');

                if (current_class == definitions['add_class']) {
                    $(definitions['out_row'] + current_id).toggle();
                    $(definitions['in_row'] + current_id).toggle();
                } else if (current_class == definitions['remove_class']) {
                    $(definitions['in_row'] + current_id).toggle();
                    $(definitions['out_row'] + current_id).toggle();
                }

                $("*").css("cursor", "progress");
                locked = true;
                $.get(current_link, function (data) {
                    if (data != "success") {
                        alert("Your request failed to process. Sorry for the inconvenience.");
                    }
                    locked = false;
                    $("*").css("cursor", "auto");
                });
            } else if (locked) {
                alert("Please wait while the last action finishes processing. Thanks for your patience.");
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will load the selected link inside the given div
    $.fn.removeDivAndLoadLink = function (definitions)
    {
        var locked = false;

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0 && !locked) {
                var current_link = $(this).attr('href');
                var current_class = $(this).attr('class');
                var current_id = $(this).attr('id');

                $(definitions['container'] + current_id).toggle();

                $("*").css("cursor", "progress");
                locked = true;
                $.get(current_link, function (data) {
                    if (data != "success") {
                        alert("Your request failed to process. Sorry for the inconvenience.");
                    }
                    locked = false;
                    $("*").css("cursor", "auto");
                });
            } else if (locked) {
                alert("Please wait while the last action finishes processing. Thanks for your patience.");
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will preform the selected action and rotate the image classes
    $.fn.flipSwitchAction = function (definitions)
    {
        var locked = false;

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0 && !locked) {
                var current_link = $(this).attr('href');
                var current_id = $(this).children().attr('id');
                var current_class = $(this).children().attr('class');

                if (current_class == definitions['on_class_off']) {
                    var yes_button = definitions['on_id'] + current_id.substr(definitions['on_substr_len']);
                    var no_button = definitions['off_id'] + current_id.substr(definitions['on_substr_len']);

                    $(yes_button).removeClass(definitions['on_class_off']);
                    $(yes_button).addClass(definitions['on_class_on']);
                    $(no_button).removeClass(definitions['off_class_on']);
                    $(no_button).addClass(definitions['off_class_off']);
                } else if (current_class == definitions['off_class_off']) {
                    var yes_button = definitions['on_id'] + current_id.substr(definitions['off_substr_len']);
                    var no_button = definitions['off_id'] + current_id.substr(definitions['off_substr_len']);

                    $(no_button).removeClass(definitions['off_class_off']);
                    $(no_button).addClass(definitions['off_class_on']);
                    $(yes_button).removeClass(definitions['on_class_on']);
                    $(yes_button).addClass(definitions['on_class_off']);
                }

                $("*").css("cursor", "progress");
                locked = true;
                $.get(current_link, function (data) {
                    if (data != "success") {
                        alert("Your request failed to process. Sorry for the inconvenience.");
                    }
                    locked = false;
                    $("*").css("cursor", "auto");
                });
            } else if (locked) {
                alert("Please wait while the last action finishes processing. Thanks for your patience.");
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will display the selected tab and hide the rest
    $.fn.divTabs = function ()
    {
        var initial_state = $("#initial").attr('value');
        if (initial_state == "") {
            initial_state = "tab01";
        }
        var current_link = "#" + initial_state + "_link";
        var current_tab = "#" + initial_state + "_tab";

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0 && "#" + $(this).attr('id') + "_tab" != current_tab) {
                var temp = "#" + $(this).attr('id');
                $(current_tab).toggle();
                $(temp + "_tab").toggle();
                $(current_link).removeClass("inactive");
                $(current_link).addClass("active");
                $(temp + "_link").removeClass("active");
                $(temp + "_link").addClass("inactive");

                current_link = temp + "_link";
                current_tab = temp + "_tab";
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    $.fn.autoLoadIntoDiv = function ()
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();
            var keys = $(this).attr('id').split("_");
            var check_value = $("#cl_" + keys[1]).html().replace(/(\r\n|\n|\r)/gm, "");
            check_value = check_value.replace(/\s+/g, "");
            if (check_value == "") {
                $("#cl_" + keys[1] + "_loading").toggle();
                var cur_link = $(this).attr('href');
                cur_link = cur_link + "/jq/1";

                $.get(cur_link, function (data) {
                    $("#cl_" + keys[1] + "_loading").toggle();
                    $("#cl_" + keys[1]).empty().append(data);
                });
            } else {
                $("#cl_" + keys[1]).toggle();
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will prompt the user with a confirmation screen before continuing with the selected action
    $.fn.expandAndLoadDiv = function (div, loading)
    {
        var lock = 0;
        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (lock == 0 && $(div).is(":hidden")) {
                lock = 1;
                $(loading).toggle();

                var cur_link = $(this).attr('href');
                cur_link = cur_link + "/jq/1";

                $.get(cur_link, function (data) {
                    $(loading).toggle();
                    $(div).empty().append(data);
                    $(div).toggle();
                });
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will prompt the user with a confirmation screen before continuing with the selected action
    $.fn.loadIntoDiv = function (div, loading)
    {
        $(this).live('click', function (e)
        {
            e.preventDefault();
            $(div).toggle();
            $(loading).toggle();

            var cur_link = $(this).attr('href');
            cur_link = cur_link + "/jq/1";
            $.get(cur_link, function (data) {
                $(loading).toggle();
                $(div).empty().append(data);
                $(div).toggle();
            });

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    // this will preform a selected action when an item is selected
    $.fn.selectRedirectIntoDiv = function (div, loading)
    {
        $(this).live('change', function (e)
        {
            var selected_link = $(this).val();
            selected_link = selected_link + "/jq/1";
            if (selected_link != "#") {
                $(div).toggle();
                $(loading).toggle();

                $.get(selected_link, function (data) {
                    $(loading).toggle();
                    $(div).empty().append(data);
                    $(div).toggle();
                });
            }
        });
    };

    // this will redirect the form submission into the appropriate div
    $.fn.formLoadIntoDiv = function (div, loading)
    {
        var lock = 0;
        $(this).live('submit', function (e)
        {
            e.preventDefault();
            if (lock == 0) {
                lock = 1;
                var cur_link = $(this).attr('action');
                var form_data = $(this).serializeArray();

                $.each(form_data, function (i, field) {
                    var field_value = field.value.replace(/ /g, "_@_");
                    if (field_value == "#") {
                        return;
                    }
                    field_value = field_value.replace(/\//g, "_*_");
                    field_value = field_value.replace(/\"/g, "_-_");
                    cur_link = cur_link + "/" + field.name + "/" + field_value;
                });
                cur_link = cur_link + "/jq/1";

                $(div).toggle();
                $(loading).toggle();
                $.get(cur_link, function (data) {
                    $(loading).toggle();
                    $(div).empty().append(data);
                    $(div).toggle();
                    lock = 0;
                });
            }

            return false;
        });
    };

    // this will display the selected tab and hide the rest
    $.fn.loadTabsIntoDiv = function (div, loading, sortable)
    {
        var lock = 0;
        var initial_state = $("#initial").attr('value');
        var current_tab = "#" + initial_state + "_tab";

        $(current_tab).removeClass("active");
        $(current_tab).addClass("inactive");

        $(this).live('click', function (e)
        {
            e.preventDefault();
            if (e.button == 0 && "#" + $(this).attr('id') + "_tab" != current_tab && lock == 0) {
                lock = 1;

                $(current_tab).removeClass("inactive");
                $(current_tab).addClass("active");

                current_tab = "#" + $(this).attr('id') + "_tab";
                current_link = $(this).attr('href') + "/jq/1";
                $(current_tab).removeClass("active");
                $(current_tab).addClass("inactive");

                $(div).toggle();
                $(loading).toggle();
                $.get(current_link, function (data) {
                    $(loading).toggle();
                    $(div).empty().append(data);

                    if (sortable != "") {
                        $(sortable).dataTable({
                            "bPaginate": false,
                            "bLengthChange": false,
                            "bInfo": false,
                            "bAutoLength": false,
                            "aaSorting": [[0, "asc"]]
                        });
                    }

                    $(div).toggle();
                    lock = 0;
                });
            }

            return false;
        });

        $(this).live('dblclick', function (e)
        {
            e.preventDefault();
            return false;
        });
    };

    $.fn.reloadBinNumContainers = function (parameters)
    {
        $(this).live('change', function (e) {
            var value = $(this).val();
            var target_link = parameters['link'] + value;

            $.get(target_link, function (data) {
                $(parameters['div']).children().remove();
                $(data).find('position').each(function () {
                    var position_id = $(this).find('position_id').text();
                    var position_label = $(this).find('position_label').text();
                    $(parameters['div']).append(
                            $('<option></option>').val(position_id).html(position_label)
                            );
                });
            });
        });
    };

    $.fn.reloadBinContainers = function (parameters)
    {
        $(this).live('change', function (e) {
            var value = $(this).val();
            var target_link = parameters['link'] + value;

            $.get(target_link, function (data) {
                $(parameters['div']).children().remove();
                $(data).find('container').each(function () {
                    var container_id = $(this).find('container_id').text();
                    var container_label = $(this).find('container_label').text();
                    $(parameters['div']).append(
                            $('<option></option>').val(container_id).html(container_label)
                            );
                });
            });
        });
    };

    $.fn.loadExpiry = function (parameters)
    {
        $(this).live('change', function (e) {
            var value = $(this).val();
            var target_link = parameters['link'] + value;
            $.get(target_link, function (data) {
                $(parameters['div']).children().remove();
                $(data).find('lot').each(function () {
                    var expiry = $(this).find('expiry_date').text();
                    $(parameters['div']).val(expiry);
                });
            });
        });
    };

    $.fn.loadExpiryAlt2 = function (parameters)
    {
        $(this).live('change', function (e) {
            var id_value = $(this).attr('id');
            var list = id_value.split(parameters['token_char']);
            var prefix = list[parameters['prefix_pos']];
            if (parameters['suffix_pos'] != -1) {
                var suffix = list[parameters['suffix_pos']];
            } else {
                var suffix = "";
            }

            var field_name = parameters['prefix'] + prefix + parameters['rebuild_char'] + parameters['middle'];
            if (suffix != "") {
                field_name += parameters['rebuild_char'] + suffix;
            }

            var value = $(this).val();
            var target_link = parameters['link'] + value;

            $.get(target_link, function (data) {
                $(parameters['div']).children().remove();
                $(data).find('lot').each(function () {
                    var expiry = $(this).find('expiry_date').text();
                    $(field_name).val(expiry);
                });
            });
        });
    };

    $.fn.reloadItemLots = function (parameters)
    {
        $(this).live('change', function (e) {
            var value = $(this).val();
            var target_link = parameters['link'] + value;
            $.get(target_link, function (data) {
                $(parameters['div']).children().remove();
                $(data).find('lot').each(function () {
                    var lot_number = $(this).find('lot_no').text();
                    $(parameters['div']).append(
                            $('<option></option>').val(lot_number).html(lot_number)
                            );
                });
            });
        });
    };

    $.fn.reloadItemLotsAlt2 = function (parameters)
    {
        $(this).live('change', function (e) {
            var id_value = $(this).attr('id');
            var list = id_value.split(parameters['token_char']);
            var prefix = list[parameters['prefix_pos']];
            if (parameters['suffix_pos'] != -1) {
                var suffix = list[parameters['suffix_pos']];
            } else {
                var suffix = "";
            }

            var select_name = parameters['prefix'] + prefix + parameters['rebuild_char'] + parameters['middle'];
            if (suffix != "") {
                select_name += parameters['rebuild_char'] + suffix;
            }

            var value = $(this).val();
            var target_link = parameters['link'] + value;

            $.get(target_link, function (data) {
                $(select_name).children().remove();
                $(data).find('lot').each(function () {
                    var itemkey = $(this).find('lot_no').text();
                    $(select_name).append(
                            $('<option></option>').val(itemkey).html(itemkey)
                            );
                });
            });
        });
    };

    $.fn.reloadItemLotsAlt = function (parameters)
    {
        $(this).live('change', function (e) {
            var id_value = $(this).attr('id');
            var list = id_value.split(parameters['token_char']);
            var prefix = list[parameters['prefix_pos']];
            var suffix = list[parameters['suffix_pos']];
            var select_name = parameters['prefix'] + prefix + parameters['rebuild_char'] + parameters['middle'] + parameters['rebuild_char'] + suffix;

            var value = $(this).val();
            var target_link = "/calls/itemlots/item/" + value;

            $.get(target_link, function (data) {
                $(select_name).children().remove();
                $(data).find('lot').each(function () {
                    var itemkey = $(this).find('lot_no').text();
                    $(select_name).append(
                            $('<option></option>').val(itemkey).html(itemkey)
                            );
                });
            });
        });
    };

    $.fn.reloadSelectOptions = function (select_name)
    {
        $(this).live('change', function (e) {
            var value = $(this).val();
            var target_link = "/calls/getcount/id/" + value;

            $.get(target_link, function (data) {
                $(select_name).children().remove();
                $(data).find('option').each(function () {
                    var sortorder = $(this).find('sortorder').text();
                    $(select_name).append(
                            $('<option></option>').val(sortorder).html(sortorder)
                            );
                });
            });
        });
    };

    $.fn.expandAction = function (parameters)
    {
        $(parameters['container_class']).live('click', function (e) {
            e.preventDefault();
            var tokens = $(this).attr('id').split("_");
            $("#" + parameters['buttons_prefix'] + "_" + tokens[1]).toggle();
            return false;
        });
    };

    $.fn.clickExpandAction = function (parameters)
    {
        $(parameters['container_class']).live('click', function (e) {
            e.preventDefault();
            var tokens = $(this).attr('id').split("_");
            $("#" + parameters['buttons_prefix'] + "_" + tokens[1]).toggle();
            $(this).hide();
            return false;
        });
    };

    $.fn.hoverExpandAction = function (parameters)
    {
        $(parameters['container_class']).live('mouseover', function (e) {
            var tokens = $(this).attr('id').split("_");
            $("#" + parameters['buttons_prefix'] + "_" + tokens[1]).toggle();
            $("#" + parameters['hide_prefix'] + "_" + tokens[1]).hide();
        })

        $(parameters['container_class']).live('mouseout', function (e) {
            var tokens = $(this).attr('id').split("_");
            $("#" + parameters['buttons_prefix'] + "_" + tokens[1]).toggle();
            $("#" + parameters['hide_prefix'] + "_" + tokens[1]).hide();
        });
    };

    $.fn.tooltipAction = function (tooltip_name)
    {
        var mouseX;
        var mouseY;

        $(document).mousemove(function (e) {
            mouseX = e.pageX;
            mouseY = e.pageY;
        });

        $(this).live('mouseenter', function (e) {
            var value = $(this).attr('id');
            $(tooltip_name + value).toggle();
            $(tooltip_name + value).css({'position': 'absolute', 'top': mouseY, 'left': mouseX});
        });
        $(this).live('mouseleave', function (e) {
            var value = $(this).attr('id');
            $(tooltip_name + value).toggle();
        });
    };

    $.fn.alttooltipAction = function (tooltip_name)
    {
        var mouseX;
        var mouseY;

        $(document).mousemove(function (e) {
            mouseX = e.pageX;
            mouseY = e.pageY;
        });

        $(this).live('click', function (e) {
            e.preventDefault();
            var value = $(this).attr('id');
            var horizontal = 0;
            $(tooltip_name + value).toggle();
            if ($(window).width() < (parseInt(mouseX) + parseInt($(tooltip_name + value).width()))) {
                horizontal = mouseX - $(tooltip_name + value).width();
            } else {
                horizontal = mouseX;
            }
            $(tooltip_name + value).css({'position': 'absolute', 'top': mouseY, 'left': horizontal});
        });
    };

    $.fn.fillField = function (field_name)
    {
        $(this).live('click', function (e) {
            e.preventDefault();
            var value = $(this).attr('id');
            $(field_name).val(value);
            return false;
        });
    };

    $.fn.appendToDiv = function (parameters)
    {
        $(this).live('click', function (e) {
            e.preventDefault();

            var code = $(parameters['field']).val();
            $(parameters['field']).val("");
            $.get((parameters['link'] + code), function (data) {
                $(parameters['div']).append(data);
            });

            return false;
        });
    };

    $.fn.addRows = function (parameters)
    {
        if (parameters['counter'] != null) {
            var counter = parameters['counter'];
        } else {
            var counter = 1;
        }

        $(this).live('click', function (e) {
            e.preventDefault();

            var link_value = $(this).attr('href');
            var div_value = $(this).attr('class');

            $.get((link_value + counter), function (data) {
                if (parameters['div'] != null) {
                    $(parameters['div'] + " tbody").append(data);
                } else {
                    $(parameters['prefix'] + div_value + parameters['suffix']).append(data);
                }
            });
            ++counter;

            return false;
        });
    };

    $.fn.appendToDivOnClick = function (div)
    {
        $(this).live('click', function (e) {
            e.preventDefault();
            var cur_keys = $(div).attr('id').split("_");
            var bin_id = (cur_keys[0] * 1);
            var end_point = (cur_keys[1] * 1);
            var new_id = bin_id + "_" + (end_point + 1);
            var link_value = $(this).attr('href');
            var final_link = link_value + "/id/" + bin_id + "/count/" + end_point;
            $.get((final_link), function (data) {
                $(div).append(data);
            });
            $(div).attr('id', new_id);
            return false;
        });
    };

    $.fn.makeVisible = function (parameters)
    {
        var status = false;
        $(this).live('change', function (e) {
            var value = $(this).val();
            if (value == parameters['trigger'] && !status) {
                status = true;
                $(parameters['target']).toggle();
                $(parameters['target']).val(parameters['message']);
            } else if (value != parameters['trigger'] && status) {
                status = false;
                $(parameters['target']).toggle();
            }
        });

        $(parameters['target']).live('focus', function (e) {
            if ($(parameters['target']).val() == parameters['message']) {
                $(parameters['target']).val("");
            }
        });
    };

    // limit the number of times a button can be pressed
    $.fn.submitOnce = function ()
    {
        var counter = 0;
        $(this).submit(function (e) {
            if (counter > 0) {
                //e.preventDefault();
                //alert("Please be patient, you only need to press submit once.");
                //return false;
            } else if (counter > 5) {
                //e.preventDefault();
                //alert("You've now pressed submit more than 5 times... Please be patient.");
                //return false;
            }
            ++counter;
        });
    };

    // limit the number of times a button can be pressed
    $.fn.clickOnce = function ()
    {
        var counter = 0;
        $(this).live('click', function (e) {
            if (counter > 0) {
                e.preventDefault();
                //alert("Please be patient, you only need to press submit once.");
                return false;
            } else if (counter > 5) {
                e.preventDefault();
                //alert("You've now pressed submit more than 5 times... Please be patient.");
                return false;
            }
            ++counter;
        });
    };

    $.fn.timerAction = function (parameters)
    {
        var total = 0;
        var minutes = 0;
        var seconds = 0;
        var miliseconds = 0;

        var output = false;

        var timer_int = setInterval(function () {
            if (output) {
                ++total;
                miliseconds = total % 100;
                seconds = (Math.floor(total / 100)) % 60;
                minutes = Math.floor(total / 6000);

                miliseconds = (miliseconds > 0) ? miliseconds : "00";
                miliseconds = (miliseconds > 9 || miliseconds == "00") ? miliseconds : "0" + miliseconds;
                seconds = (seconds > 0) ? seconds : "00";
                seconds = (seconds > 9 || seconds == "00") ? seconds : "0" + seconds;

                $(parameters['field']).val(minutes + ":" + seconds + ":" + miliseconds);
            }
        }, 10);

        $(parameters['start']).live('click', function (e) {
            e.preventDefault();
            if (!output) {
                output = true;
            }
            return false;
        });

        $(parameters['stop']).live('click', function (e) {
            e.preventDefault();
            if (output) {
                output = false;
            }
            return false;
        });

        $(parameters['reset']).live('click', function (e) {
            e.preventDefault();
            minutes = 0;
            seconds = 0;
            miliseconds = 0;
            total = 0;

            $(parameters['field']).val("0:00:00");

            return false;
        });
    };

    // this will load the selected link inside the given div
    $.fn.liveTimeout = function (seconds)
    {
        var ts_before_alert;
        var ts_after_alert;
        var lock = false;
        var status = true;
        var total_elapsed = 0;
        var total_inactivity = 0;

        var warning_time = seconds - 60;
        var logout_time = seconds;
        var keepalive_time = seconds + 60;

        var session_timer = setInterval(function () {
            if (status) {
                ++total_elapsed;
                ++total_inactivity;
                if (total_inactivity >= warning_time) {
                    warningAction();
                }
                if (total_inactivity >= logout_time) {
                    logoutAction();
                }
                if (total_elapsed >= keepalive_time) {
                    keepaliveAction();
                }
            }
        }, 1000);

        function warningAction() {
            if (!lock) {
                $("#logout-warning").remove();
                var message = "<div id='logout-warning'><table><tr><td class='icon'><img src='/images/warning.png' /></td><td class='message'>Due to inactivity you will be logged out soon. Please use your mouse or keyboard to indicate activity.</td></tr></table></div>";
                $('body').append(message);
                $('body').scrollTop(0);

                lock = true;
                ts_before_alert = new Date();
                alert("You are about to be logged out.");

                ts_after_alert = new Date();
                var sec_diff = Math.floor(Math.abs(ts_after_alert - ts_before_alert) / 1000);
                if (sec_diff >= 30) {
                    logoutAction();
                } else {
                    resetAction();
                    lock = false;
                }
            }

            return;
        }

        function logoutAction() {
            $("#logout-warning").remove();
            var message = "<div id='logout-warning'><table><tr><td class='icon'><img src='/images/warning.png' /></td><td class='message'>Due to inactivity you have been logged out.</td></tr></table></div>";
            $('body').append(message);
            $('body').scrollTop(0);

            status = false;
            $.get("/kill", function (data) {});

            return;
        }

        function keepaliveAction() {
            total_elapsed = 0;
            $.get("/keepalive", function (data) {});

            return;
        }

        function resetAction() {
            if (status) {
                total_inactivity = 0;
                $("#logout-warning").remove();
            }

            return;
        }

        $('body').live('mousemove', function (e) {
            resetAction();
        });
        $('body').live('keypress', function (e) {
            resetAction();
        });
    };


    // display content on hover
    $.fn.displayOnHover = function (parameters)
    {
        // ensure all the hidden lists are hidden at screen load
        $("." + parameters['hidden-class']).each(function (element, i) {
            $(this).hide();
        });

        $(parameters['target']).live("mouseenter", function (e) {
            var keys = $(this).attr("id").split("_");
            if ($("#" + parameters['hidden-prefix'] + "_" + keys[1]).is(":hidden")) {
                if (parameters['hide-link']) {
                    $("#" + parameters['link-prefix'] + "_" + keys[1]).hide();
                }
                $("#" + parameters['hidden-prefix'] + "_" + keys[1]).show();
            }
        });

        $(parameters['target']).live("mouseleave", function (e) {
            var keys = $(this).attr("id").split("_");
            $("#" + parameters['hidden-prefix'] + "_" + keys[1]).hide();
        });

        $("." + parameters['link-class']).live("click", function (e) {
            var keys = $(this).attr("id").split("_");
            $("#" + parameters['hidden-prefix'] + "_" + keys[1]).toggle();
            if (parameters['disable-click'] == true) {
                e.preventDefault();
            }
        });
    }; // end displayOnHover function


    // ** LOCK DIV IN PLACE ON SCROLL ********************************************* //
    $.fn.lockObjectToWindow = function (definitions)
    {
        if ($(definitions['object']).length <= 0) {
            return;
        }
        var object_left = $(definitions['object']).offset().left;
        var scrollMoved = function () {
            if (object_left == null || object_left <= 50) {
                object_left = $(definitions['object']).offset().left;
            }
            var top_limit = $(window).scrollTop();
            var object_anchor = $(definitions['anchor']).offset().top;
            var object = $(definitions['object']);
            if (top_limit > object_anchor + definitions['buffer']) {
                object.css({
                    position: "fixed",
                    top: "0px",
                    left: object_left + "px"
                });
            } else if (top_limit <= object_anchor) {
                object.css({
                    position: "relative",
                    top: "",
                    left: ""
                });
            }
        };

        $(window).scroll(scrollMoved);
    }; // end lockScrollToWindow function


    // allow overflow on hover
    $.fn.overflowOnHover = function (parameters)
    {
        $("." + parameters['inactive']).live("mouseenter", function (e) {
            var div_id = $(this).attr('id');
            $("#" + div_id).attr('class', parameters['active']);
        });

        $("." + parameters['active']).live("mouseleave", function (e) {
            var div_id = $(this).attr('id');
            $("#" + div_id).attr('class', parameters['inactive']);
        });

        $("." + parameters['link']).live("click", function (e) {
            e.preventDefault();
            var tokens = $(this).attr('id').split("_");
            if ($("#" + parameters['top_prefix'] + "_" + tokens[1]).attr('class') == parameters['inactive']) {
                $("#" + parameters['top_prefix'] + "_" + tokens[1]).attr('class', parameters['active']);
            } else {
                $("#" + parameters['top_prefix'] + "_" + tokens[1]).attr('class', parameters['inactive']);
            }
        });

    }; // end overflowOnHover function

})(jQuery);

$(document).ready(function () {
    $("form.show-loader").live("submit", function (e) {
        var btn = $(this).find("input[type=submit]:focus");
        if (btn.attr('rel') !== 'no_loader') {
            jAlert('<img src="/images/ajax-loader.gif"/>', 'Please Wait..');
            $("#popup_content").css('background-image', 'none');
            $("#popup_content").html('<div style="text-align:center"><img src="/images/ajax-loader.gif"/></div>');
        }
    });

    $(".bread_crumb").jBreadCrumb({easing: 'swing'});

    $().liveTimeout((25 * 60));

    $("form").submitOnce();

    $().displayOnHover({
        'target': '.admin-page_group_item',
        'parent': '.admin-page_group_item_list',
        'container': '.admin-page_group_item',
        'link-class': 'admin-nav_main_link',
        'hidden-class': 'admin-page_item_list',
        'hidden-prefix': 'api',
        'link-prefix': 'apgl',
        'disable-click': true,
        'hide-link': false
    });

    $('a.pdf_popup').live('click', function (e) {
        $("#dialog").dialog({
            minWidth: 800,
            minHeight: 700
        });
        $(".ui-dialog-title").html($(this).attr('title'));
        $("#frame").attr("src", $(this).attr('href'));
        return false;
    }); //View PDF Files In A PopUp Window

    $('.add_to_fav').live('click', function (e) {
        var uri = $(this).attr('data-uri');
        $.ajax({
            url: "/page/addtofav",
            type: "POST",
            datatype: "html",
            data: {'uri': uri},
            success: function (result) {
                if (result != 0) {
                    var html = '<tr>';
                    var i = 0;
                    $.each(result, function (key, value) {
                        if (i == 5) {
                            html += '</tr><tr>'
                        }
                        html += '<td id="fav_id_' + value['id'] + '">';
                        html += '<a class="first" href="http://' + value['host'] + value['path'] + '">' + value['page_name'] + '</a>';
                        html += '<a class="last delete_fav" data-favid="' + value['id'] + '" data-uri="' + value['path'] + '">X</a>';
                        html += '</td>';
                        i++;
                    });
                    html += '</tr>';
                    $('.user_favs').empty().append(html);
                    $('.add_to_fav').remove();
                }
            }
        });
    });

    $('.delete_fav').live('click', function (e) {
        var fav_id = $(this).attr('data-favid');
        var uri = $(this).attr('data-uri');
        $.ajax({
            url: "/page/deletefav",
            type: "POST",
            datatype: "html",
            data: {'fav_id': fav_id},
            success: function (result) {
                if (result === 1) {
                    $('#fav_id_' + fav_id).remove();
                    if (window.location.pathname === uri) {
                        var html = '<td class="right add_to_fav" data-uri="' + uri + '"><img src="/images/icons/famfam/award_star_add.png" title="Add To Favorites" /></td>';
                        $(html).insertAfter(".favorites .left");
                    }
                }
            }
        });
    });
});