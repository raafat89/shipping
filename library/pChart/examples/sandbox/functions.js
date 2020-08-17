/*
functions.js - Sandbox JS

Version     : 1.2.3
Made by     : Jean-Damien POGOLOTTI
Maintained By : Momchil Bozhinov
Last Update : 05/09/19

This file can be distributed under the license you can find at:

http://www.pchart.net/license

You can find the whole class documentation on the pChart web site.
*/

function Do(Action)
{
	$("#result_area").html("<img src='graphix/wait.gif' /><br />Saving configuration");

	var Input = {};

	$("select option:selected").each(function(idx, v) {
		Input[v.parentElement.id] = v.value;
	});

	$("input:checkbox, input:radio").each(function(idx, v) {
		Input[v.id] = v.checked;
	});

	$("input:text").each(function(idx, v) {
		Input[v.id] = v.value;
	});

	Input["data1"] = [];
	Input["data2"] = [];
	Input["data3"] = [];
	Input["absissa"] = [];

	for(i=0;i<8;i++)
	{
		Input["data1"].push(Input["d_serie1_data"+i]);
		Input["data2"].push(Input["d_serie2_data"+i]);
		Input["data3"].push(Input["d_serie3_data"+i]);
		Input["absissa"].push(Input["d_absissa_data"+i]);
	}

	switch(true) {
		case Input["t_axis0"]:
			t_axis = 0;
			break;
		case Input["t_axis1"]:
			t_axis = 1;
			break;
		case Input["t_axis2"]:
			t_axis = 2;
			break;
	}

	Input["t_axis"] = t_axis;

	$.ajax({
		type: "POST",
		data: {Data : JSON.stringify(Input), Action : Action},
		url: "render.php",
		success: function (result) {
			if (Action == "Code"){
				$("#result_area").html("<pre name='code'>"+result+"</pre>");
			} else if (Action == "Render") {
				$("#result_area").html("<img src=\"data:image/png;base64, "+result+"\" />");
			}
		},
		error: function() {
             $("#result_area").html("Post failed!");
        }
	});
}

function toggleAuto()
{
	Automatic = document.getElementById("g_autopos").checked;
}

function doLayout()
{
	if ( !Automatic ) { return; }

	g_width  = document.getElementById("g_width").value;
	g_height = document.getElementById("g_height").value;

	document.getElementById("g_title_x").value = g_width/2;

	checkEnabledAxis();
}

function getSelected(ID)
{
	e = document.getElementById(ID);
	return e.options[e.selectedIndex].value;
}

function randomize()
{
	for(i=0;i<8;i++)
	{
		document.getElementById("d_serie1_data"+i).value = Math.ceil(Math.random()*100-50);
		document.getElementById("d_serie2_data"+i).value = Math.ceil(Math.random()*100-50);
		document.getElementById("d_serie3_data"+i).value = Math.ceil(Math.random()*100-50);
	}
}

function checkChartSettings()
{
	ChartFamily = getSelected("c_family");

	disableItem("c_plot_size");
	disableItem("c_border_size");
	disableCheck("c_border_enabled");
	disableRadio("c_bar_classic");
	disableRadio("c_bar_rounded");
	disableRadio("c_bar_gradient");
	disableCheck("c_around_zero1");
	disableItem("c_transparency");
	disableCheck("c_forced_transparency");
	disableCheck("c_around_zero2");

	switch( ChartFamily ) {
		case "plot":
			enableItem("c_plot_size");
			enableItem("c_border_size");
			enableCheck("c_border_enabled");
			checkPlotBorder();
			break;
		case "bar":
		case "sbar":
			enableRadio("c_bar_classic");
			enableRadio("c_bar_rounded");
			enableRadio("c_bar_gradient");
			enableCheck("c_around_zero1");
			break;
		case "fspline":
		case "area":
		case "sarea":
		case "fstep":
			enableItem("c_transparency");
			enableCheck("c_forced_transparency");
			enableCheck("c_around_zero2");
			checkAreaChart();
			break;
	}

	if ( Automatic )
	{
		if ( ChartFamily == "sbar" || ChartFamily == "sarea" ){
			document.getElementById("s_mode").value = "690203";
		}
	} else {
		document.getElementById("s_mode").value = "690201";
	}
}

function checkLegend()
{
	if ( getSelected("l_position") == "Manual" ) {
		enableItem("l_x");
		enableItem("l_y");
	} else {
		disableItem("l_x");
		disableItem("l_y");
	}
}

function checkPlotBorder()
{
	if ( document.getElementById("c_border_enabled").checked ) {
		enableItem("c_border_size");
	} else {
		disableItem("c_border_size");
	}
}

function checkAreaChart()
{
	if ( document.getElementById("c_forced_transparency").checked ) {
		enableItem("c_transparency");
	} else {
		disableItem("c_transparency");
	}
}

function toggleSubTicks()
{
	if ( !document.getElementById("s_subticks_enabled").checked ) {
		disableItem("s_subticks_color");
		disableItem("s_subticks_alpha");
	} else {
		enableItem("s_subticks_color");
		enableItem("s_subticks_alpha");
	}
}

function toggleAutoMargins()
{
	if ( document.getElementById("s_automargin_enabled").checked ) {
		disableItem("s_x_margin");
		disableItem("s_y_margin");
	} else {
		enableItem("s_x_margin");
		enableItem("s_y_margin");
	}
}

function checkEnabledAxis()
{
	Serie1Enabled = document.getElementById("d_serie1_enabled").checked;
	Serie2Enabled = document.getElementById("d_serie2_enabled").checked;
	Serie3Enabled = document.getElementById("d_serie3_enabled").checked;
	Serie1Binding = getSelected("d_serie1_axis");
	Serie2Binding = getSelected("d_serie2_axis");
	Serie3Binding = getSelected("d_serie3_axis");

	Series = 0;
	if ( Serie1Enabled ) Series++;
	if ( Serie2Enabled ) Series++;
	if ( Serie3Enabled ) Series++;
	
	for( i = 0; i < 3; i++ )
	{
		if ( (Serie1Binding != i || !Serie1Enabled) && (Serie2Binding != i || !Serie2Enabled) && (Serie3Binding != i || !Serie3Enabled) )
		{
			disableItem("d_axis" + i + "_name");
			disableItem("d_axis" + i + "_unit");
			disableItem("d_axis" + i + "_position");
			disableItem("d_axis" + i + "_format");
		}
		else
		{
			enableItem("d_axis" + i + "_name");
			enableItem("d_axis" + i + "_unit");
			enableItem("d_axis" + i + "_position");
			enableItem("d_axis" + i + "_format");
		}
	}

	if ( Automatic )
	{
		sl_enabled  = document.getElementById("sl_enabled").checked;
		g_width     = document.getElementById("g_width").value;
		g_height    = document.getElementById("g_height").value;

		leftSeries = 0;
		rightSeries = 0;

		for ( i = 0; i < 3; i++ )
		{
			if (!document.getElementById("d_axis" + i + "_position").disabled){
				if (getSelected("d_axis" + i + "_position") == "AXIS_POSITION_LEFT"){
					leftSeries++;
				} else {
					rightSeries++;
				}
			}
		}

		if ( getSelected("s_direction") == "690101" )
		{
			leftOffset = (leftSeries == 0 ? 20 : 10);
			rightOffset = (rightSeries == 0 ? 25 : 15);

			leftMargin = leftOffset + 40 * leftSeries;
			width = g_width - leftMargin - 40 * rightSeries - rightOffset;

			BottomOffset = (sl_enabled ? Series*15 : 0);

			document.getElementById("s_x").value = leftMargin;
			document.getElementById("s_y").value = 50;
			document.getElementById("s_width").value = width;
			document.getElementById("s_height").value = g_height - 50 - 40 - BottomOffset;
		}
		else
		{
			topOffset = (leftSeries == 0 ? 40 : 40);
			bottomOffset = (rightSeries == 0 ? 25 : 15);

			topMargin = topOffset + 30 * leftSeries;
			height = g_height - topMargin - 30 * rightSeries - bottomOffset;

			RightOffset = (sl_enabled ? Series*15 : 0);

			document.getElementById("s_x").value = 70;
			document.getElementById("s_y").value = topMargin;
			document.getElementById("s_width").value = g_width - 70 - 40 - RightOffset;
			document.getElementById("s_height").value = height;
		}
	}
}

function disableItem(ID)
{
	document.getElementById(ID).style.backgroundColor = "#E0E0E0";
	document.getElementById(ID).style.color = "#A0A0A0";
	document.getElementById(ID).disabled = true;
}

function disableCheck(ID)
{
	document.getElementById(ID).style.color = "#A0A0A0";
	document.getElementById(ID).disabled = true;
}

function disableRadio(ID)
{
	document.getElementById(ID).disabled = true;
}

function enableItem(ID)
{
	document.getElementById(ID).style.backgroundColor = "#FFFFFF";
	document.getElementById(ID).style.color = "#707070";
	document.getElementById(ID).disabled = false;
}

function enableCheck(ID)
{
	document.getElementById(ID).style.color = "#707070";
	document.getElementById(ID).disabled = false;
}

function enableRadio(ID)
{
	document.getElementById(ID).disabled = false;
}
