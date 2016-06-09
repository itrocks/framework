
function PDF_Templates(id_print_model)
{
	var active_zone;
	var active_ruler;
	var current_zone;
	var current_ruler;
	var previous_zone;
	var toggle_data_view = 0;
	var table_html = '<table class="table_zone" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;height:100%;"><tr><td><div class="td_div"></div></td></tr></table>';
	var pdf_tpl = this;
	var zone_move = 1;

	var zindex = 10;

	var font_descender = '12px';

	var zone_drag_option = function(lock, snap) {
		var options = {
			disabled: lock,
			containment: "parent",
			cursor: "move",
			opacity: 0.5,
			snap: (snap == 'snap') ? ".zone, .table_zone, .x_ruler_line, .y_ruler_line" : false,
			snapMode: "both",
			snapTolerance: 10
		};
		return options;
	}

	/**
	 * Zone Object
	 */
	function zone (id, value, type) {

		this.counter = 0;
		this.node = document.createElement("div");

		if (type == 'table_zone') {
			this.node.className = 'table_zone_dragger';
		} else {
			this.node.className = 'zone';
		}

		var line_height = Math.max($('#props_fontSize').val(), $('#props_lineHeight').val() );

		this.node.style.fontFamily = $('#props_fontFamily').val();
		this.node.style.fontSize = $('#props_fontSize').val() + "pt";
		this.node.style.fontWeight = ($('#props_fontWeight').is(':checked')) ? "bold" : "";
		this.node.style.fontStyle = ($('#props_fontStyle').is(':checked')) ? "italic" : "";
		this.node.style.textDecoration = ($('#props_textDecoration').is(':checked')) ? "underline" : "";
		//this.node.style.lineHeight = $('#props_lineHeight').val() + "pt";
		this.node.style.lineHeight = line_height + "pt";
		this.node.style.color = hex2rgb($('#props_color').val());
		this.node.style.backgroundColor = hex2rgb($('#props_backgroundColor').val());
		this.node.style.borderColor = hex2rgb($('#props_borderColor').val());
		this.node.style.textAlign = $('input[name=props_textAlign]:checked').val();

		this.node.style.verticalAlign = $('input[name=props_textValign]:checked').val();


		this.node.style.position = 'absolute';

		this.node.lock = ($('#props_lock').is(':checked')) ? "lock" : "";
		this.node.transparent = ($('#props_transparent').is(':checked')) ? "transparent" : "";
		this.node.snap = ($('#props_snap').is(':checked')) ? "snap" : "";
		this.node.border = ($('#props_border').is(':checked')) ? "1" : "";
		this.node.vSepBorder = ($('#props_vSepBorder').is(':checked')) ? "1" : "";
		this.node.hSepBorder = ($('#props_hSepBorder').is(':checked')) ? "1" : "";
		this.node.vSepColor = $('#props_vSepColor').val();
		this.node.hSepColor = $('#props_hSepColor').val();

		this.node.path = id.path;
		this.node.title = id.path;

		$(this.node).attr('alt', value);

		linespace = $('#props_lineSpace').val();

		$(this.node).data('id', id);

		$(this.node).data('linespace', linespace);

		if (type == 'table_zone') {
			$(this.node).append(table_html);
			//this.node.innerHTML = table_html;
			this.node.style.height = 150 + "px";

			$(this.node).data('overzone', 1);

			$(this.node).data('baseline', 0);

		} else {
			this.node.style.height = 25 + "px";
			if (toggle_data_view == 0) {

				if (id.name.indexOf('<div class="tcell">') != -1) {
					this.node.innerHTML = id.name;
				} else {
					this.node.innerHTML = '<div class="ttable"><div class="tcell">' + id.name + '</div></div>';
				}

			} else {

				if (value.indexOf('<div class="tcell">') != -1) {
					this.node.innerHTML = value;
				} else {
					this.node.innerHTML = '<div class="ttable"><div class="tcell">' + value + '</div></div>';
				}

			}

			$(this.node).data('overzone', 0);

			$(this.node).data('baseline', $('input[name=props_baseline]:checked').val());

		}

		zone_obj = this;
	}
	zone.prototype.getID = function() {

		// @todo : remplacer par la methode getNextZoneId ...

		var id = 'element_' + this.counter++;
		while(document.getElementById(id)) id = 'element_' + this.counter++;
		return id;
	};

	/**
	 * zone.addToPage
	 */
	zone.prototype.addToPage = function(page) {

		this.node.id = this.id = this.getID();
		//this.node.id = this.getID();
		this.node.tabIndex = this.counter;

		pdf_tpl.zoneSetActive(this.node);

		$(this.node).on('mousedown',
			function(ev) {
				pdf_tpl.zoneMouseDown($(this)[0]);
				pdf_tpl.zoneSetActive($(this)[0]);
			}
		);

		$(this.node)
			.draggable(zone_drag_option(this.node.lock, this.node.snap))
			.resizable({
				disabled: this.node.lock,
				minWidth: 10,
				minHeight: 10,
				handles: "all",
				autoHide: true,
				containment: "parent"
			})
		;
		if (this.node.border == 1) {
			$(this.node).css("border-style", "solid");
		} else {
			$(this.node).css("border-style", "dashed");
		}
		$(page).append(this.node);
		// jquery.build plugin compatibility
		if ($(this.node).build != undefined) $(this.node).build();
	};

	/**
	 * zoneMouseDown
	 */
	this.zoneMouseDown = function(el) {

		if ($(el).hasClass("zone") || $(el).hasClass("table_zone_dragger")) {
			// la zone selectionnée apparait toujours au dessus des autres
			var zzz = pdf_tpl.findHightestZindex();
			zzz = zzz  + 1;
			$(el).css("z-index", zzz);
		}

		el.focus();
		$('#props_fontFamily').val(el.style.fontFamily);
		$('#props_fontSize').val(parseInt(el.style.fontSize));
		$('#props_fontWeight').attr('checked', (el.style.fontWeight == "bold") ? true : false);
		$('#props_fontStyle').attr('checked', (el.style.fontStyle == "italic") ? true : false);
		$('#props_textDecoration').attr('checked', (el.style.textDecoration == "underline") ? true : false);
		$('#props_lineHeight').val(parseInt(el.style.lineHeight));

		$("input[name=props_textAlign][value=" + el.style.textAlign + "]").attr('checked', 'checked');

		var text_valign;
		if ($(el).children('.ttable').children('.tcell').css("vertical-align") == 'baseline') {
			text_valign = 'top';
		} else {
			text_valign = $(el).children('.ttable').children('.tcell').css("vertical-align");
		}
		$("input[name=props_textValign][value=" + text_valign + "]").attr('checked', 'checked');

		//el.style.verticalAlign
		//$(el).children('.tcell').css('');
		//$(el).children('.tcell').css("vertical-align");

		$('#props_lock').attr('checked', (el.lock == "lock") ? true : false);
		$('#props_transparent').attr('checked', (el.transparent == "transparent") ? true : false);
		$('#props_snap').attr('checked', (el.snap == "snap") ? true : false);
		$('#props_border').attr('checked', (el.border == 1) ? true : false);
		$('#props_vSepBorder').attr('checked', (el.vSepBorder == 1) ? true : false);
		$('#props_hSepBorder').attr('checked', (el.hSepBorder == 1) ? true : false);
		$('#props_color')[0].color.fromString(rgb2hex(el.style.color));
		$('#props_backgroundColor')[0].color.fromString(rgb2hex(el.style.backgroundColor));
		$('#props_borderColor')[0].color.fromString(rgb2hex(el.style.borderColor));
		$('#props_vSepColor')[0].color.fromString(el.vSepColor);
		$('#props_hSepColor')[0].color.fromString(el.hSepColor);
		$('#props_overzone').attr('checked', ($(el).data('overzone') == "1") ? true : false);
		$('#props_lineSpace').val(parseFloat($(el).data('linespace')));

		$('#props_baseline').attr('checked', ($(el).data('baseline') == "1") ? true : false);

		pdf_tpl.zoneSetActive(el);
	}

	/**
	 * zoneSetActive
	 */
	this.zoneSetActive = function(z) {
		$(".zone, .table_zone_dragger").css('opacity', 0.5);
		$(z).css('opacity', 1);
		current_zone = z;
		previous_zone = z;
		active_zone = 1;
	}

	/**
	 * window.onclick
	 */
	window.addEventListener('click',
		function(e) {
			if(current_zone) {
				if (e.target.className.indexOf("pdf_page") != -1) {
					$(".zone, .table_zone_dragger").css('opacity', 0.5);
					current_zone = null;
					active_zone = null;
					active_ruler = null;
				}
			}
			if (current_ruler) {
				if (e.target.className.indexOf("pdf_page") != -1) {
					current_ruler.children("div").css("backgroundColor", "lightgray");
					current_ruler.children("div").css("boxShadow", "0px 0px 0px #ffffff");
					current_ruler = null;
					active_zone = null;
					active_ruler = null;
				}
			}
		}, false
	);

	/**
	 * window.onkeydown
	 */
	window.onkeydown = function(e) {
		// suppression d'une zone dans la page
		if (e.keyCode == 46) {
			if (current_zone) {
				if (!$("input").is(":focus") && !$("textarea").is(":focus")) {
					// empeche la suppression de zone si le curseur est dans un champ et qu'on supprime des caracteres
					$(current_zone).remove();
				}
			}
			else if (current_ruler) {
				$(current_ruler).remove();
			}
		}
	}

	/**
	 * updateProps
	 * (quand on change les styles et propriété d'une zone dans l'interface, l'appliquer sur la zone)
	 */
	function updateProps() {
		prop = this.name.substring(6);
		(this.checked == false && this.type == "checkbox") ? v = "" : v = this.value;
		if (prop == "fontSize") {

			if(v >= $('#props_lineHeight').val()) {
				$('#props_lineHeight').val(v);

				$(current_zone).css("line-height", v + "pt");
			}

			v += "pt";
		} else if (prop == "lineHeight") {
			v += "pt";
		} else if (prop.indexOf("color") != -1 || prop.indexOf("Color") != -1) {
			v = "#" + v;
		}
		if (current_zone) {

			if (prop == "textValign") {
				$(current_zone).children('.ttable').children('.tcell').css("vertical-align", v);
			}

			else if (prop == "overzone") {
				$(current_zone).data('overzone', v);
			}
			else if (prop == "baseline") {
				$(current_zone).data('baseline', v);

			}
			else if (prop == "lineSpace") {
				$(current_zone).data('linespace', v);
			}

			else if (prop == "lock") {
				current_zone[prop] = v;
				toggleDragResize(v);
			} else if (prop == "snap") {
				current_zone[prop] = v;
				toggleSnap(v);
			} else if (prop == "border") {
				current_zone[prop] = v;
				if (v == 1) {
					$(current_zone).css("border-style", "solid");
				} else {
					$(current_zone).css("border-style", "dashed");
				}
			} else if (prop == "vSepBorder" || prop == "hSepBorder") {
				current_zone[prop] = v;
			} else if (prop.indexOf("SepColor") != -1) {
				current_zone[prop] = v;
			} else if (prop == "transparent") {
				current_zone[prop] = v;
				toggleTransparency(v);
			} else {
				current_zone.style[prop] = v;
			}

			setBaselineOffset($(current_zone));

		}
	};

	/**
	 * setBaselineOffset
	 */
	function setBaselineOffset(zone) {
		var fontsBaselineRatio = {
			"Arial" : 10/68,
			"Courier" : 10/45,
			"Times" : 10/68,
		};
		fontSizeInPx = $(zone).css('font-size');
		fontSizeInPx = fontSizeInPx.replace("px", "");
		var baseline_offset = fontsBaselineRatio[$(zone).css("font-family")] * fontSizeInPx;
		if ($(zone).data('baseline') == 1) {
			$(zone).children('.ttable').css('marginTop', baseline_offset + 'px');
		} else {
			$(zone).children('.ttable').css('marginTop', '0px');
		}
	}

	/**
	 * toggleDragResize
	 */
	function toggleDragResize() {
		if (current_zone.lock == "lock") {
			$(current_zone).draggable('disable').resizable('disable');
		} else {
			$(current_zone).draggable('enable').resizable('enable');
		}
	}

	/**
	 * toggleSnap
	 */
	function toggleSnap() {
		if (current_zone.snap == "snap") {
			$(current_zone).draggable("option", "snap", ".zone, .x_ruler_line, .y_ruler_line");
		} else {
			$(current_zone).draggable("option", "snap", false);
		}
	}

	/**
	 * toggleTransparency
	 */
	function toggleTransparency(t) {
		var bgc = current_zone.style.backgroundColor;
		//bgcolor = bgcolor.substring(0,(bgcolor.length-1));
		//bgcolor = bgcolor.replace("rgb","rgba");
		//bgcolor = bgcolor + ', 0.5)'
		var rgb = String(bgc).match(/\d+(\.\d+)?%?/g);
		var r = rgb[0];
		var g = rgb[1];
		var b = rgb[2];
		if (current_zone.transparent == "transparent") {
			//$(current_zone).css('backgroundColor', 'rgba('+r+', '+g+', '+b+', 0)');
			current_zone.style.backgroundColor = 'rgba('+r+', '+g+', '+b+', 0)';
		} else {
			//$(current_zone).css('backgroundColor', 'rgba('+r+', '+g+', '+b+', 1)');
			current_zone.style.backgroundColor = 'rgba('+r+', '+g+', '+b+', 1)';
		}
	}

	/**
	 * addFieldOnTemplate
	 */
	$('.pdf_templates_fields,.property').draggable({
		snap: ".zone, .table_zone, .x_ruler_line, .y_ruler_line",
		snapMode: "both",
		snapTolerance: 10,
		opacity: 0.5,
		cursorAt: { top: 0, left: 0 },
		helper: function(  )
		{
			var value = $(this).attr("alt");
			id = {};
			id.path = $(this).attr("title");
			id.name =  $(this).attr("id");

			z = new zone(id, value);
			z.node.path = this.id;
			z.node.style.marginLeft = "2px";
			z.node.style.marginTop = "2px";

			return $(z.node);
		}
	});

	$('.pdf_page')
		.droppable({
			greedy: false,
			tolerance: "pointer",
			accept: '.pdf_templates_fields,.property',
			drop: function(event, ui) {
				// empeche l'ajout d'une zone dans la page si le drop se fait au dessus d'une cellule de table zone.


				// @@@
				//id_zone = $(ui.helper).attr('title');
				//zone_content = $(ui.helper).html();

				id = {};
				id.path = $(ui.helper).attr('title');
				id.name = $(ui.helper).html();

				if ($(event.originalEvent.target).hasClass('td_div')) {

					//$(event.originalEvent.target).html(id_zone);
					$(event.originalEvent.target).html(id.name);

					//$(event.originalEvent.target).attr('title', id_zone);
					$(event.originalEvent.target).attr('title', id.path);

				} else {
					drag = ui.helper.offset();
					drop = $(this).offset();
					var active_page_tab = $("#pages_tabs").tabs("option", "active");
					if ($(ui.draggable).hasClass('contains_lines')) {
						type = 'table_zone';
					} else {
						type = 'zone';
					}
					var value = ui.draggable.attr("alt");

					z = new zone(id, value, type);
					z.node.style.left = (drag.left - drop.left -1) + 'px';
					z.node.style.top = (drag.top - drop.top -1) + 'px';
					if (active_page_tab == 0) {
						z.addToPage($('#pdf_page1'));
					} else if (active_page_tab == 1) {
						z.addToPage($('#pdf_page0'));
					} else if (active_page_tab == 2) {
						z.addToPage($('#pdf_pagex'));
					}
				}
			}
		})
	;

	$('#fields_tabs').tabs();

	$('#pages_tabs').tabs({
		select: function(event, ui) {
			if (ui.index == 0) {
				$('#copyzone_to0').hide();
				$('#copyzone_to1').show();
				$('#copyzone_tox').show();
			} else if (ui.index == 1) {
				$('#copyzone_to0').show();
				$('#copyzone_to1').hide();
				$('#copyzone_tox').show();
			} else if (ui.index == 2) {
				$('#copyzone_to0').show();
				$('#copyzone_to1').show();
				$('#copyzone_tox').hide();
			}
		},
		create: function() {
			$('#copyzone_to0').hide();
			$('#copyzone_to1').show();
			$('#copyzone_tox').show();
		}
	});

	$('#copyzone_to0').on('click', function() {
		pdf_tpl.copyZoneToPage($('#pdf_page0'));
	});

	$('#copyzone_to1').on('click', function() {
		pdf_tpl.copyZoneToPage($('#pdf_page1'));
	});

	$('#copyzone_tox').on('click', function() {
		pdf_tpl.copyZoneToPage($('#pdf_pagex'));
	});

	/**
	 * copyZoneToPage
	 */
	this.copyZoneToPage = function(page) {

		copy_zone = $(current_zone).clone(false);

		// pas trouvé le moyen de conserver ces données lors du clonage, donc copie manuelle ...
		$(copy_zone)[0].border = $(current_zone)[0].border;
		$(copy_zone)[0].hSepBorder = $(current_zone)[0].hSepBorder;
		$(copy_zone)[0].hSepColor = $(current_zone)[0].hSepColor;
		$(copy_zone)[0].lock = $(current_zone)[0].lock;
		$(copy_zone)[0].path = $(current_zone)[0].path;
		$(copy_zone)[0].snap = $(current_zone)[0].snap;
		$(copy_zone)[0].transparent = $(current_zone)[0].transparent;
		$(copy_zone)[0].vSepBorder = $(current_zone)[0].vSepBorder;
		$(copy_zone)[0].vSepColor = $(current_zone)[0].vSepColor;

		opacity = 0.5;
		color = rgb2hex($(copy_zone)[0].style.backgroundColor);
		$(copy_zone)[0].style.backgroundColor = hex2rgb(color, opacity);
		color = rgb2hex($(copy_zone)[0].style.color);
		$(copy_zone)[0].style.color = hex2rgb(color, opacity);
		color = rgb2hex($(copy_zone)[0].style.borderColor);
		$(copy_zone)[0].style.borderColor = hex2rgb(color, opacity);

		options_r = $(current_zone).resizable("option");
		options_d = $(current_zone).draggable("option");

		// @todo : ici patch pour les options du containment, mais serai plus interessant de faire un objet pour les option
		// du drag et resize pour les zones de maniere globale.

		options_d.containment = 'parent';

		$(copy_zone).attr('id', 'element_' + pdf_tpl.getNextZoneId());
		$(copy_zone).appendTo(page);

		$(copy_zone).resizable().resizable('destroy').resizable(options_r);
		$(copy_zone).draggable().draggable('destroy').draggable(options_d);

		$(copy_zone).on('mousedown',
			function(ev) {
				pdf_tpl.zoneMouseDown($(this)[0]);
				//pdf_tpl.zoneSetActive($(this)[0]);
			}
		);

		//$(".table_zone").colResizable({disable:true});
		//$(".table_zone").colResizable({disable:false});

	};

	/**
	 * getNextZoneId
	 */
	this.getNextZoneId = function() {
		var max = 0;
		$("[id^=element_]").each(function() {
			num = parseInt(this.id.split("_")[1],10);
			if (num > max) {
				max = num;
			}
		});
		return max + 1;
	}

	$('.zone_props').on('change', updateProps);
	$('#button_save_templates').on('click', save_templates);
	$('#button_print_preview').on('click', print_preview);

	/**
	 * print_preview
	 */
	function print_preview() {
		data = pdf_tpl.elementsToJSON();
		ajaxCall('?c=Pdf_Templates&m=printForm&a[id]=' + id_print_model + SID, 'app_messages', data, 'POST');
	}

	/**
	 * convert zones and rulers to JSON
	 */
	this.elementsToJSON = function()
	{
		var active_tab_index = $("#pages_tabs").tabs( "option", "active" );

		$("#pages_tabs").tabs( "option", "active", 0 );

		zones0 = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_page0 > .zone")));
		table_zones0 = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_page0 > .table_zone_dragger")));
		rulers0 = pdf_tpl.jsonToStringEncode(getRulersByPage($("#pdf_page0 > .x_ruler, #pdf_page0 > .y_ruler")));

		$("#pages_tabs").tabs( "option", "active", 1 );
		zones1 = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_page1 > .zone")));
		table_zones1 = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_page1 > .table_zone_dragger")));
		rulers1 = pdf_tpl.jsonToStringEncode(getRulersByPage($("#pdf_page1 > .x_ruler, #pdf_page0 > .y_ruler")));

		$("#pages_tabs").tabs( "option", "active", 2 );
		zonesx = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_pagex > .zone")));
		table_zonesx = pdf_tpl.jsonToStringEncode(getZonesByPage($("#pdf_pagex > .table_zone_dragger")));
		rulersx = pdf_tpl.jsonToStringEncode(getRulersByPage($("#pdf_pagex > .x_ruler, #pdf_page0 > .y_ruler")));

		// retour à l'onglet de départ
		$("#pages_tabs").tabs( "option", "active", active_tab_index );
		data = 'zones[zones0]=' + zones0 + '&zones[zones1]=' + zones1 + '&zones[zonesx]=' + zonesx;
		data += '&rulers[rulers0]=' + rulers0 + '&rulers[rulers1]=' + rulers1 + '&rulers[rulersx]=' + rulersx;
		data += '&table_zones[table_zones0]=' + table_zones0 + '&table_zones[table_zones1]=' + table_zones1 + '&table_zones[table_zonesx]=' + table_zonesx;

		return data;
	}

	/**
	 *
	 */
	this.jsonToStringEncode = function(elements) {
		s = JSON.stringify(elements);
		//s = htmlspecialcharsForPDF_Templates(s);
		return encodeURIComponent(s);
	}

	/**
	 * save_templates
	 */
	function save_templates() {
		ignore_ajax_tag_change['app_body'] = 1;
		data = pdf_tpl.elementsToJSON();
		ajaxCall('?c=Pdf_Templates&m=saveZones&a[id]=' + id_print_model + SID, 'app_messages', data, 'POST');
	}

	/**
	 * getRulersByPage
	 */
	function getRulersByPage(rulersByPage) {
		var rulers = [];
		rulersByPage.each(function () {
			var ruler = {};
			ruler.type = $(this).attr('class').split(' ')[0];
			if ($(this).hasClass("x_ruler")) {
				ruler.position = $(this).css("left");
			} else if ($(this).hasClass("y_ruler")) {
				ruler.position = $(this).css("top");
			}
			rulers.push(ruler);
		});
		return rulers;
	}

	/**
	 * getZonesByPage
	 */
	function getZonesByPage(zonesByPage) {
		var zones = {};
		zonesByPage.each(function (i, el) {

			zones[this.id] = {};

			zones[this.id]['id'] = $(this).attr('id');

			zones[this.id]['name'] = $(this).attr("title");

			//console.log();

			// <div\sclass=\\"tcell\\">(.*)</div>
			//console.log($(el).data('id').name);
			//var pattern = new RegExp("<div\\sclass=\"tcell\">(.*)</div></div>");
			//var name_in_tcell_tag = pattern.exec($(el).data('id').name);
			//if (name_in_tcell_tag != null) {
			//	name_in_tcell_tag = name_in_tcell_tag[1];
			//	}
			//console.log(name_in_tcell_tag);
			//if (name_in_tcell_tag) {
			//console.log("test1");
			//		zones[this.id]['name'] = name_in_tcell_tag;
			//	} else {
			//console.log("test2");
			//		zones[this.id]['name'] = $(el).data('id').name;
			//	}
			//console.log(name_in_tcell_tag);
			//console.log($(el).data('id').name);

			zones[this.id]['alt'] = $(this).attr("alt");

			//zones[this.id]['path'] = $(this).attr("title");
			zones[this.id]['path'] = $(el).data('id').path;

			zones[this.id]['data'] = {};
			zones[this.id]['data']['console'] = $(el).data("console");

			if ($(el).hasClass('table_zone_dragger')) {
				var cells = $(el).children('table').find('.td_div');
				var table_cells = {};
				cells.each(function (idx, elmt) {
					if (idx >= 0) {
						table_cells[idx] = {};
						table_cells[idx]['width'] = $(elmt).width() + 'px';
						table_cells[idx]['title'] = $(elmt).attr('title');
					}
				});
				zones[this.id]['cells'] = table_cells;
			}
			var styles = $(this).attr('style').split(';'),

			//console.log(styles);

				i = styles.length,
				s = {style: {}},
				style, k, v;
			while (i--) {
				style = styles[i].split(':');



				k = $.trim(style[0]);
				v = $.trim(style[1]);
				if (k.length > 0 && v.length > 0)
				{
					s.style[k] = v;
				}
			}
			zones[this.id]['style'] = s.style;

			// largeur de la zone affichée par defaut si la zone n'est pas redimensionnée
			//if (!zones[this.id]['style']['width']) {
			//	zones[this.id]['style']['width'] = $(this).width() + 'px';
			//}

			zones[this.id]['style']['width'] = $(this).width() + 'px';
			zones[this.id]['style']['vertical-align'] = $(this).children('.ttable').children('.tcell').css('verticalAlign');
			zones[this.id]['attr'] = {};
			zones[this.id]['attr']['snap'] = $(this)[0]["snap"];
			zones[this.id]['attr']['lock'] = $(this)[0]["lock"];
			zones[this.id]['attr']['transparent'] = $(this)[0]["transparent"];
			zones[this.id]['attr']['border'] = $(this)[0]["border"];
			zones[this.id]['attr']['vSepBorder'] = $(this)[0]["vSepBorder"];
			zones[this.id]['attr']['hSepBorder'] = $(this)[0]["hSepBorder"];
			zones[this.id]['attr']['vSepColor'] = hex2rgb($(this)[0]["vSepColor"]);
			zones[this.id]['attr']['hSepColor'] = hex2rgb($(this)[0]["hSepColor"]);
			zones[this.id]['attr']['overzone'] = ($(el).data('overzone')) ? ($(el).data('overzone')) : '0';
			zones[this.id]['attr']['baseline'] = ($(el).data('baseline')) ? ($(el).data('baseline')) : '0';
			zones[this.id]['attr']['linespace'] = ($(el).data('linespace')) ? ($(el).data('linespace')) : '0';
		});
		return zones;
	}

	/**
	 * activeRulerDrag
	 */
	this.activeRulerDrag = function() {
		$('.x_ruler').draggable({
			axis : 'x',
			containment : 'parent',
			cursor : "w-resize",
			snap: '.zone, .table_zone',
			snapMode: "both",
			snapTolerance: 10
		});
		$('.y_ruler').draggable({
			axis : 'y',
			containment : 'parent',
			cursor : "n-resize",
			snap: '.zone, .table_zone',
			snapMode: "both",
			snapTolerance: 10
		});
	}

	/**
	 * loadZones
	 */
	this.loadZones = function(z, page) {
		var zones = JSON.parse(z);
		$.each(zones, function(index, element) {

			if (element.cells) {
				type = 'table_zone';
				classname = 'table_zone_dragger';
				content = table_html;
			} else {
				type = 'zone';
				classname = 'zone';
				content = element.name;
			}

			style = element.style;

			id = {};
			id.name = element.name;
			id.path = element.path;

			zo = new zone(id, element.alt, type);
			zo.node = document.createElement("div");
			zo.node.className = classname;
			//zo.node.id = element.id;
			zo.node.path = element.path;
			zo.node.title = element.path;
			$(zo.node).attr('alt', element.alt);

			$(zo.node).data('console', element.data.console);
			$(zo.node).data('id', id);
			$(zo.node).data('overzone', element.attr.overzone);
			$(zo.node).data('baseline', element.attr.baseline);
			$(zo.node).data('linespace', element.attr.linespace);

			zo.node.style.width = style.width;
			zo.node.style.height = style.height;
			zo.node.style.left = style.left;
			zo.node.style.top = style.top;
			zo.node.style.fontFamily = style["font-family"];
			zo.node.style.fontSize = style["font-size"];
			zo.node.style.fontWeight = style["font-weight"];
			zo.node.style.fontStyle = style["font-style"];
			zo.node.style.textDecoration = style["text-decoration"];
			zo.node.style.lineHeight = style["line-height"];
			zo.node.style.color = style.color;
			zo.node.style.backgroundColor = style["background-color"];
			zo.node.style.borderColor = style["border-color"];
			zo.node.style.textAlign = style["text-align"];
			zo.node.style.position = 'absolute';
			zo.node.lock = element.attr.lock;
			zo.node.transparent = element.attr.transparent;
			zo.node.snap = element.attr.snap;
			zo.node.vSepColor = rgb2hex(element.attr.vSepColor);
			zo.node.hSepColor = rgb2hex(element.attr.hSepColor);
			zo.node.border = element.attr.border;
			zo.node.vSepBorder = element.attr.vSepBorder;
			zo.node.hSepBorder = element.attr.hSepBorder;

			// astuce pour récuperer le outerHtml ...
			rows = $('<div>').append($(content).clone());
			$(rows).find('td:first').remove();
			if (element.cells) {
				$.each(element.cells, function(cell_idx, cell) {
					if (cell.title) {
						cell_title = 'title="' + cell.title + '"';
					} else {
						cell_title = '';
					}
					$(rows).find('tr:last').append("<td style=\"width:"+cell.width+";\"><div class=\"td_div\" " + cell_title + ">" + cell.title + "</div></td>");
					//$(this).closest('.table_zone_dragger').css("width", $(this).closest('.table_zone').width());
				});
				zo.node.innerHTML = rows.html();
			} else {

				//zo.node.innerHTML = content;

				zo.node.innerHTML = '<div class="ttable"><div class="tcell">' + content + '</div></div>';

			}

			$(zo.node).children('.ttable').children('.tcell').css("vertical-align", style["vertical-align"]);


			setBaselineOffset($(zo.node));

			zo.addToPage(page);
		});

		$(".table_zone").colResizable({disable:true});
		$(".table_zone").colResizable({disable:false});
	}

	$('.add_ruler').on('mouseup', function() {
		if (this.id == 'vertical_ruler') {
			var $ruler = $('<div class="x_ruler"><div class="x_ruler_line"></div></div>');
		} else if (this.id == 'horizontal_ruler') {
			var $ruler = $('<div class="y_ruler"><div class="y_ruler_line"></div></div>');
		}
		$ruler.on('mousedown', function() {
			if (current_ruler) {
				current_ruler.children("div").css("backgroundColor", "lightgray");
				current_ruler.children("div").css("boxShadow", "0px 0px 0px #ffffff");
			}
			current_ruler = $(this);
			active_ruler = 1;
			current_ruler.children("div").css("backgroundColor", "gray");
			current_ruler.children("div").css("boxShadow", "0px 0px 2px yellow");
			current_zone = null;
		});

		$ruler.css("z-index", 999999);

		$ruler0 = $ruler.clone(true);
		$ruler1 = $ruler.clone(true);
		$rulerx = $ruler.clone(true);
		$('#pdf_page0').append($ruler0);
		$('#pdf_page1').append($ruler1);
		$('#pdf_pagex').append($rulerx);
		pdf_tpl.activeRulerDrag();
	});

	/**
	 * loadRulers
	 */
	this.loadRulers = function(r, page) {
		var rulers = JSON.parse(r);
		$.each(rulers, function(index, element) {
			var $ruler = $('<div class="' + element.type + '"><div class="' + element.type + '_line"></div></div>');
			if (element.type == 'x_ruler') {
				$ruler.css("left", element.position);
			} else if (element.type == 'y_ruler') {
				$ruler.css("top", element.position);
			}
			$ruler.on('mousedown', function() {
				if (current_ruler) {
					current_ruler.children("div").css("backgroundColor", "lightgray");
					current_ruler.children("div").css("boxShadow", "0px 0px 0px #ffffff");
				}
				current_ruler = $(this);
				active_ruler = 1;
				current_ruler.children("div").css("backgroundColor", "gray");
				current_ruler.children("div").css("boxShadow", "0px 0px 2px yellow");
				current_zone = null;
			});

			$ruler.css("z-index", 999999);

			$(page).append($ruler);
		});
	}

	$(".table_zone_dragger")
		.resizable({
			//ghost: true
			handles: "all",
			containment : 'parent'

		})


		//.draggable(zone_drag_option(this.node.lock, this.node.snap));


		.draggable({
			//disabled: this.node.lock,
			containment : 'parent',
			cursor: "move",
			opacity: 0.5,
			snap: '.zone, .table_zone_dragger, .x_ruler_line, .y_ruler_line',
			snapMode: "both",
			snapTolerance: 10
		});


	$(".table_zone").colResizable();

	$('#pages_tabs').on('mouseenter', '.td_div', function() {
		var button_rem = '';
		if($(this).closest('.table_zone').find('td').length > 1) {
			button_rem = '<img src="' + window.app.project_uri + '/bappli/skins/bappli/print_model/delete.png" class="table_zone_rem" />';
		}
		var buttons =
			'<div class="table_zone_buttons">' +
				'<img src="' + window.app.project_uri + '/bappli/skins/bappli/print_model/add.png" class="table_zone_add" />' +
				button_rem +
				'</div>';
		$(this).css("backgroundColor", "rgba(0, 255, 0, 0.1)");
		$(this).append(buttons);
	});
	$('#pages_tabs').on('mouseleave', '.td_div', function() {
		$('.table_zone_buttons').remove();
		$(this).css("backgroundColor", "transparent");
	});
	$('#pages_tabs').on('click', '.table_zone_add', function() {
		$(".table_zone").colResizable({disable:true});
		$(this).closest('td').after("<td style=\"width:100px;\"><div class=\"td_div\"></div></td>");
		$(".table_zone").colResizable({disable:false});
		$(this).closest('.table_zone_dragger').css("width", $(this).closest('.table_zone').width());
	});
	$('#pages_tabs').on('click', '.table_zone_rem', function() {
		$(".table_zone").colResizable({disable:true});
		$(this).closest('td').remove();
		$(".table_zone").colResizable({disable:false});
		$(this).closest('.table_zone_dragger').css("width", $(this).closest('.table_zone').width());
	});

	// @TODO : ranger cette fonction ailleurs ...
	$.fn.insertAtCaret = function (myValue) {
		return this.each(function(){
			//IE support
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			}
			//MOZILLA / NETSCAPE support
			else if (this.selectionStart || this.selectionStart == '0') {
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)+ myValue+ this.value.substring(endPos,this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	};

	$('#console').on("keyup", function() {

		//$(current_zone).text($(this).val());
		//$(current_zone).path = $(this).val();

		$(current_zone).data("console", $(this).val());

		// permet de reactiver le resize apres le changement de contenu, sinon resize inoperant ...

		options = $(current_zone).resizable("option");
		$(current_zone).resizable("destroy").resizable(options);

	});

// TODO : voir si ici on a toujours un probleme de drop multiple
	$("#console").droppable({
		accept: ".pdf_templates_fields",
		drop: function(ev, ui) {
			$(this).insertAtCaret( /*' + ' + */ ui.draggable.prop('title') /*+ ' + '*/ );
		}
	});

	// @todo : convertir la fonction ( ... this.node.addEventListener('mousedown', ... ) en jquery ici ...
	$('#pages_tabs').on('click', '.zone', function() {

		if ($(this).data("console")) {
			$('#console').val($(this).data("console"));
		} else {
			$('#console').val($(this)[0].path);
		}

	});

	$('#toggle_data_path_view').on("mousedown", function() {
		toggle_data_view = !toggle_data_view;

		if (toggle_data_view == true) {
			// on affiche les donn�e
			$('.zone').each(function(index, element) {

				// permet de reactiver le resize apres le changement de contenu, sinon resize inoperant ...
				options = $(element).resizable("option");

				//$(this).text($(this).attr("alt"));
				$(this).text($(this).attr("alt"));

				$(element).resizable("destroy").resizable(options);
			});
		} else {

			// on affiche les donnée
			$('.zone').each(function(index, element) {
				// permet de reactiver le resize apres le changement de contenu, sinon resize inoperant ...
				options = $(element).resizable("option");

				//$(this).text($(this).attr("title"));

				var pattern = new RegExp("<div\\sclass=\"tcell\">(.*)</div></div>");
				var name_in_tcell_tag = pattern.exec($(this).data("id").name);
				if (name_in_tcell_tag != null) {
					name_in_tcell_tag = name_in_tcell_tag[1];
				}
				if (name_in_tcell_tag) {
					$(this).text(name_in_tcell_tag);
				} else {
					$(this).text($(this).data("id").name);
				}





				$(element).resizable("destroy").resizable(options);
			});
		}
	});

	/**
	 * déplacement des zones / rulers avec les flêches du clavier
	 */
	$(document).keydown(function(e) {
		// si le focus est sur un champ, on désactive la fonction de déplacement ou suppression de zone
		// car les touches fleche et suppr y sont utilisés
		if (!$("input").is(":focus") && !$("textarea").is(":focus")) {
			zone_move = 1;
		} else {
			zone_move = 0;
		}
		if (current_zone && active_zone == 1) {
			current = current_zone;
		} else if (current_ruler && active_ruler == 1)  {
			current = current_ruler;
		} else {
			current = null;
		}
		if (current && zone_move == 1) {
			if (e.keyCode == 37) {
				x_pos = parseInt($(current).css('left')) - 1;
				if (x_pos >= 0) {
					$(current).css('left', x_pos + "px");
				}
				return false;
			}
			if (e.keyCode == 38) {
				y_pos = parseInt($(current).css('top')) - 1;
				if (y_pos >= 0) {
					$(current).css('top', y_pos + "px");
				}
				return false;
			}
			if (e.keyCode == 39) {
				x_pos = parseInt($(current).css('left')) + 1;
				x_pos_w = x_pos + $(current).width() + 1;
				top_right_corner = $(current).parent().width();
				if (x_pos_w < top_right_corner) {
					$(current).css('left', x_pos + "px");
				}
				return false;
			}
			if (e.keyCode == 40) {
				y_pos = parseInt($(current).css('top')) + 1;
				y_pos_h = y_pos + $(current).height() + 1;
				bottom_right_corner = $(current).parent().height();
				if (y_pos_h < bottom_right_corner) {
					$(current).css('top', y_pos + "px");
				}
				return false;
			}
		}
	});

	/**
	 * recherche d'un mot clé dans le treeview
	 */
	$("#filter_fields").change(function() {
		var tval = $(this).val();
		if (tval) {
			$("#treeview_fields li").hide().filter(":contains('" + tval + "')").find('#treeview_fields li').andSelf().show();
		} else {
			$("#treeview_fields li").show();
		}
	});

	/**
	 * recherche du z-index le plus elevé dans toutes les zones
	 */
	this.findHightestZindex = function() {
		var index_highest = 0;
		$(".zone, .table_zone_dragger").each(function() {
			var index_current = parseInt($(this).css("zIndex"), 10);
			if(index_current > index_highest) {
				index_highest = index_current;
			}
		});
		return index_highest;
	}

	/**
	 * permet de basculer automatiquement d'une console taille réduite à une console taille augmentée.
	 */
	$("#toggle_console").toggle(
		function() {
			$("#console").stop().animate({
				width: "1000px",
				height: "600px"
			}, 400);
			$("#toggle_console").attr("src", window.app.project_uri + "/bappli/skins/bappli/print_model/arrow_in.png");
		},
		function() {
			$("#console").stop().animate({
				width: "100%",
				height: "200px"
			}, 400);
			$("#toggle_console").attr("src", window.app.project_uri + "/bappli/skins/bappli/print_model/arrow_out.png");
		}
	);

}
