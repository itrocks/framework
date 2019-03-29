$('document').ready(function()
{
	//--------------------------------------------------------- a, button, input[type=submit] disable
	/**
	 * Disable click on .disabled links
	 */
	$('a, button, input[type=submit]').build({ event: 'click', priority: 10, callback: function(event)
	{
		if ($(this).hasClass('disabled')) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
	}});

	//------------------------------------------------------------------------------- div.popup close
	/**
	 * Set popup windows close action to javascript remove popup instead of calling a link
	 */
	$('div.popup .general.actions').build(function()
	{
		this.find('.close > a').click(function()
		{
			var $this = $(this);
			$this.removeAttr('href').removeAttr('target');
			setTimeout(function() { $this.closest('.popup').remove(); });
		});

		this.find('a[href]:not([href*="close="])').each(function()
		{
			var $this = $(this);
			var href  = $this.attr('href');
			if (!href.beginsWith('#')) {
				var close_link = app.askAnd(href, 'close=window' + window.zindex_counter);
				$this.attr('href', close_link);
			}
		});
	});

	//------------------------------------------------------------------------ section#messages close
	/**
	 * #messages close action empties #messages instead of calling a link
	 */
	$('div#messages .actions .close a').build('click', function(event)
	{
		$(this).closest('div#messages').empty();
		event.preventDefault();
		event.stopImmediatePropagation();
	});

	//-------------------------------------------------------------------- li.multiple li.minus click
	/**
	 * Remove a line
	 */
	$('li.multiple li.minus').build('click', function()
	{
		var $this = $(this);
		// setTimeout allows other click events to .minus to execute before the row is removed
		setTimeout(function() {
			var $body = $this.closest('tbody, ul');
			if ($body.children().length > ($body.is('ul') ? 2 : 1)) {
				$this.closest('tr, ul > li').remove();
			}
			else {
				var $table   = $this.closest('table, ul');
				var $new_row = $table.data('itrocks_add').clone();
				$this.closest('tr, ul > li').replaceWith($new_row);
				$new_row.build();
				$table.data('itrocks_last_index', $table.data('itrocks_last_index') + 1);
			}
		});
	});

	//------------------------------------------------------------------- input[type=checkbox] change
	$('input[type=checkbox]').build('change', function()
	{
		var $checkbox = $(this);
		var $input    = $checkbox.prev().filter('input[type=hidden]');
		if ($input.length) {
			var old_check = $input.val();
			var check     = $checkbox.is(':checked') ? $checkbox.val() : '0';
			var nullable  = String($checkbox.data('nullable'));
			if (nullable.length) {
				if (old_check === nullable) {
					check = '';
					$checkbox.attr('checked', false);
				}
			}
			$input.val(check).change();
		}
	});

	//---------------------------------------------------------- input[type=checkbox][readonly] click
	$('input[type=checkbox][readonly]').build('click', function(event)
	{
		event.preventDefault();
	});

	$('select:not([data-ordered=true])').build($.fn.sortContent);

	//-------------------------------------------- table.collection, table.map, ul.collection, ul.map
	var block_selector = 'table.collection, table.map, ul.collection, ul.map';
	$(block_selector).build('each', function()
	{
		var $this = $(this);
		var table = $this.is('table');
		// prepare new row
		var $new  = $this.find(table ? '> tbody > tr.new' : '> li.new');
		$this.data('itrocks_add', $new.clone());
		// itrocks_add_index : the value of the index to be replaced into the model for new rows
		var index = $this.find(table ? '> tbody > tr' : '> li').length - 1;
		$this.data('itrocks_add_index', index);
		// itrocks_last_index : the last used index (lines count - 1)
		$this.data('itrocks_last_index', Math.max(0, $this.data('itrocks_add_index') - 1));
		if ($this.data('itrocks_add_index')) {
			if ($new.find('input:not([class=file]):not([type=hidden]), select, textarea').length) {
				$new.remove();
			}
		}
	});

	//----------------------------------------------------------------------------------- autoAddLine
	var autoAddLine = function()
	{
		var $this = $(this);
		var $row  = $this.closest('tr, ul > li');
		if ($this.val() && ($this.val() !== '0') && $row.length && !$row.next('tr, li').length) {
			var $block = $row.closest('.collection, .map');
			if ($block.length) {
				// calculate depth in order to increment the right index
				var depth   = 0;
				var $parent = $block;
				while (($parent = $parent.parent().closest('.collection, .map')).length) {
					depth ++;
				}
				// calculate new row and indexes
				var $new_row = $block.data('itrocks_add').clone();
				$block.data('itrocks_last_index', $block.data('itrocks_last_index') + 1);
				var index     = $block.data('itrocks_last_index');
				var old_index = $block.data('itrocks_add_index');
				// increment indexes in new row html code
				var depthReplace = function(text, open, close, depth)
				{
					var i;
					var j = 0;
					while ((i = text.indexOf('=' + DQ, j) + 1) > 0) {
						var in_depth = depth;
						j = text.indexOf(DQ, i + 1);
						while (
							(i = text.indexOf(open, i) + open.length) && (i > (open.length - 1)) && (i < j)
							&& ((in_depth > 0) || (text[i] < '0') || (text[i] > '9'))
						) {
							if ((text[i] >= '0') && (text[i] <= '9')) {
								in_depth --;
							}
						}
						if ((i > (open.length - 1)) && (i < j) && !in_depth) {
							var k = text.indexOf(close, i);
							var html_index = text.substring(i, k);
							if (Number(html_index) === old_index) {
								text = text.substr(0, i) + index + text.substr(k);
							}
						}
					}
					return text;
				};
				$new_row.html(
					depthReplace(depthReplace($new_row.html(), '%5B', '%5D', depth), '[', ']', depth)
				);
				// append and build new row
				var $body = $block.children('tbody');
				if (!$body.length) {
					$body = $block;
				}
				$body.append($new_row);
				$new_row.autofocus(false);
				$new_row.build();
				$new_row.autofocus(true);
			}
		}
	};

	//---------------------------------------------------- input, select, textarea change/focus/keyup
	$(block_selector).find('input, select, textarea').build(function()
	{
		this.change(autoAddLine).focus(autoAddLine).keyup(autoAddLine);
	});

	//---------------------------------------------------------------------------- checkCompletedDate
	var checkCompletedDate = function($datetime)
	{
		if ($datetime.val() === '') {
			return true;
		}
		var formattedNow = $.datepicker.formatDate(
			$datetime.datepicker('option', 'dateFormat'),
			new Date()
		);

		// No completion needed
		if ($datetime.val().length >= formattedNow.length) {
			return checkDate($datetime);
		}
		else {
			var bufferVal = $datetime.val();
			$datetime.val(bufferVal + formattedNow.substr($datetime.val().length));
			//if  Completed date is not valid, fallback to input value
			return checkDate($datetime) || ($datetime.val(bufferVal) && false)
		}
	};

	//------------------------------------------------------------------------------------- checkDate
	var checkDate = function ($datetime)
	{
		var format_date = $.datepicker.formatDate(
			$datetime.datepicker('option', 'dateFormat'),
			$datetime.datepicker('getDate')
		);
		return $datetime.val() === format_date;
	};

	//--------------------------------------------------------------- input.datetime datepicker/keyup
	$.datepicker.setDefaults($.datepicker.regional[window.app.language]);
	$('input.datetime').build(function()
	{
		this.datepicker({
			constrainInput: false,
			dateFormat: dateFormatToDatepicker(window.app.date_format),
			firstDay: 1,
			showOn: 'button',
			showOtherMonths: true,
			selectOtherMonths: true,
			showWeek: true
		});

		this.blur(function()
		{
			this.setCustomValidity(checkCompletedDate($(this)) ? '' : 'Invalid date');
		});

		this.keyup(function(event)
		{
			if (!event.ctrlKey) {
				if (event.keyCode === 38) {
					$(this).datepicker('hide');
				}
				if (event.keyCode === 40) {
					$(this).datepicker('show');
				}
			}
		});

		this.nextAll('button.ui-datepicker-trigger').attr('tabindex', -1);
	});

	//------------------------------------------------------------------------ input.combo comboValue
	/**
	 * Sets the value of the element
	 *
	 * @param $element jQuery
	 * @param id       integer
	 * @param value    string
	 */
	var comboValue = function($element, id, value)
	{
		if (id) {
			$element.data('combo-value', id);
		}
		else {
			$element.removeData('combo-value');
		}
		if ($element.prev().val() !== id) {
			$element.prev().val(id);
			$element.prev().change();
		}
		if ($element.val() !== value) {
			$element.val(value);
			$element.change();
		}
	};

	//-------------------------------------------------------------------------- input.combo comboUri
	var comboUri = function($element)
	{
		return window.app.uri_base + SL + $element.data('combo-set-class') + SL + 'json'
	};

	//---------------------------------------------------------------------- input.combo comboRequest
	var comboRequest = function($element, request)
	{
		if (request === undefined) {
			request = [];
		}
		if (!request['first']) {
			request['limit'] = 100;
		}
		if (!window.app.use_cookies) request['PHPSESSID'] = window.app.PHPSESSID;
		var filters = $element.data('combo-filters');
		if (filters !== undefined) {
			filters = filters.split(',');
			for (var key in filters) if (filters.hasOwnProperty(key)) {
				var filter      = filters[key].split('=');
				var is_constant = filter[1].match(/$[0-9]+^/)
					|| (
						((filter[1].substr(0, 1) === DQ) || (filter[1].substr(0, 1) === Q))
						&& (filter[1].substr(0, 1) === filter[1].substr(-1))
					);
				var $filter_element = is_constant
					? { length: 0 }
					: $element.closest('form').find('[name=' + DQ + filter[1] + DQ + ']');
				if ($filter_element.length) {
					request['filters[' + filter[0] + ']'] = $filter_element.val();
				}
				else {
					request['filters[' + filter[0] + ']']
						= is_constant ? filter[1].substr(1, filter[1].length - 2) : filter[1];
				}
			}
		}
		return request;
	};

	//---------------------------------------------------------------------- input.combo comboMatches
	/**
	 * Returns true if the typed value is the same as the data value read from the server
	 * Returns false if do not match or if there is no data value
	 *
	 * @param $element string
	 * @returns boolean
	 */
	var comboMatches = function($element)
	{
		if ($element.data('combo-value')) {
			var val = $element.val().toLowerCase();
			var dat = $element.data('combo-value').toLowerCase();
			return (!val.length) || (dat.indexOf(val) !== -1);
		}
		else {
			return false;
		}
	};

	//------------------------------------------------------------------------ input.combo comboForce
	var comboForce = function($element)
	{
		if ($element.val().length) {
			// window.running_combo enable third-parties to wait for this to be complete (eg clicks)
			window.running_combo = true;
			var request = $.param(comboRequest($element, { term: $element.val(), first: true }));
			$.getJSON(comboUri($element), request)
				.done(function(data) {
					comboValue($element, data.id, data.value);
				})
				.always(function() {
					window.running_combo = undefined;
				});
		}
		else {
			comboValue($element, null, '');
		}
	};

	//--------------------------------------------------------------------------------- setFieldValue
	/**
	 * Use always this method to set a new value to a field
	 * For a simple field, value is a string (or similar)
	 * For a combo field, value is [id, string representation of value]
	 *
	 * @param $form jQuery object for the targeted form
	 * @param field_name string
	 * @param value Array|string
	 * @todo should be able to set value for any form field tag (like select)
	 */
	var setFieldValue = function($form, field_name, value)
	{
		var search = 'input[name=' + DQ + field_name + DQ + ']'
			+ ', input[data-name=' + DQ + field_name + DQ + ']';
		var $input       = $form.find(search);
		var do_change    = true;
		var string_value = null;

		// case we receive an array with the value (and id) and its string representation
		if (Array.isArray(value)) {
			string_value = ((value.length > 1) ? value[1] : '');
			value        = (value.length       ? value[0] : '');
		}

		// todo comment what is this case about (value starting with ':') ?
		if (((typeof value) === 'string') && (value.substr(0, 1) === ':')) {
			if (!$input.val().length) {
				value = value.substr(1);
			}
			else {
				do_change = false;
			}
		}

		if (do_change) {
			$input.val(value);
			if ((string_value !== null)) {
				$input.next().val(string_value);
			}
			$input.change();
		}
	};

	//---------------------------------------------------------------------- input.combo autocomplete
	$('input.combo').build(function()
	{
		this.autocomplete(
		{
			autoFocus: true,
			delay:     100,
			minLength: 1,

			close: function()
			{
				var $this = $(this);
				setTimeout(function() { $this.removeData('visible'); }, 100);
			},

			open: function()
			{
				var $this = $(this);
				$this.data('visible', true);
			},

			source: function(request, response)
			{
				var $element = this.element;
				window.running_combo = true;
				// set data to lower case for /MAJ combo in term
				var data = $.param(comboRequest($element, request)).toLocaleLowerCase();
				$.getJSON(comboUri($element), data)
					.done(response)
					.always(function() {
						window.running_combo = undefined;
					});
			},

			select: function(event, ui)
			{
				var $caption = $(this);
				var $value   = $caption.prev().filter('input[type=hidden]');
				var has_id   = true;
				if (!$value.length) {
					has_id = false;
					$value = $caption;
				}

				var previous_caption = $caption.val();
				var previous_value   = $value.val();
				if (has_id) {
					var val = ui.item.id;
					if (ui.item.class_name !== undefined) {
						val = ui.item.class_name + ':' + val;
					}
					$value.val(val);
				}
				// mouse click : copy the full value to the input
				if (!event.keyCode) {
					$caption.val(ui.item.value);
				}
				$caption.data('combo-value', ui.item.value);
				if (!comboMatches($caption)) {
					comboForce($caption);
				}
				if (previous_caption !== $caption.val()) {
					$caption.change();
				}
				if (previous_value !== $value.val()) {
					$value.change();
				}
			}
		});

		//--------------------------------------------------------------------------- input.combo focus
		this.focus(function()
		{
			var $this = $(this);
			$this.data('combo-value', $this.val());
		});

		//---------------------------------------------------------------------------- input.combo blur
		this.blur(function()
		{
			var $this = $(this);
			if (!$this.val().length) {
				comboValue($this, null, '');
			}
			else {
				if (comboMatches($this)) {
					$this.val($this.data('combo-value'));
				}
				else {
					comboForce($this);
				}
			}
			$this.removeData('combo-value');
		});

		//---------------------------------------------------------------------- input.combo ctrl+click
		this.click(function(event)
		{
			if (event.ctrlKey || event.metaKey || event.shiftKey) {
				var $this = $(this);
				var id    = $this.prev().val();
				var uri   = $this.data('combo-class');
				if ((uri === undefined) || !uri) {
					return;
				}
				if (id.indexOf(':') > -1) {
					uri = id.lParse(':');
					id  = id.rParse(':');
				}
				uri = uri.repl(BS, SL);
				if (id) {
					uri += SL + id;
				}
				if (event.ctrlKey || event.metaKey) {
					uri += '/edit';
				}
				var target = $this.data('target');
				if (!target) {
					target = ((event.ctrlKey || event.metaKey) && event.shiftKey) ? '#main' : '#popup';
				}
				var $target = $(target);
				if (target.endsWith('main') && !$target.length) {
					$target = $(target.beginsWith('#') ? 'main' : '#main');
				}
				var target_exists = $target.length;
				redirect(
					app.uri_base + SL + uri + '?fill_combo=' + $this.prev().attr('name'),
					target,
					$this,
					function($target) {
						if (target_exists) {
							return;
						}
						$target.draggable({
							handle: 'h2',
							stop: function() {
								$(this).find('h2').data('stop-click', true);
							}
						});
					}
				);
			}
		});

		//------------------------------------------------------------------------- input.combo keydown
		this.keydown(function(event)
		{
			var $this = $(this);
			// down : open even if value is empty
			if (event.keyCode === 40) {
				if ($this.autocomplete('option', 'minLength')) {
					$this.autocomplete('option', 'minLength', 0).autocomplete('search', '');
				}
			}
		});

		//--------------------------------------------------------------------------- input.combo keyup
		this.keyup(function(event)
		{
			var $this = $(this);
			// backspace | delete : close if value is empty
			if (((event.keyCode === 8) || (event.keyCode === 46)) && !$this.val().length) {
				$this.autocomplete('option', 'minLength', 1).autocomplete('close');
				var $value         = $this.prev().filter('input[type=hidden]');
				var previous_value = $value.val();
				comboValue($this, null, '');
				if ((previous_value !== undefined) && previous_value.length) {
					$value.change();
				}
			}
		});
	});

	//----------------------------------------------------------------------- input.combo~.more click
	$('input.combo~.more').build('click', function(event)
	{
		event.preventDefault();
		var $this = $(this).prevAll('input.combo');
		if (!$this.data('visible')) {
			if ($this.autocomplete('option', 'minLength')) {
				$this.autocomplete('option', 'minLength', 0);
			}
			$this.autocomplete('search', '').focus();
		}
	});

	//------------------------------------------------------------------------ input[data-conditions]
	var will_change = {};
	var selector    = 'input[data-conditions], select[data-conditions], textarea[data-conditions]';
	$(selector).build('each', function()
	{
		var $this      = $(this);
		var conditions = $this.data('conditions').replace(/\(.*\)/g);
		$.each(conditions.split(';'), function(condition_key, condition) {
			condition = condition.split('=');
			var $condition;
			if (will_change.hasOwnProperty(condition[0])) {
				$condition = will_change[condition[0]];
			}
			else {
				$condition = $this.closest('form').find('[name="id_' + condition[0] + DQ + ']');
				if ($condition.length) {
					$condition = $condition.next();
				}
				else {
					$condition = $this.closest('form').find('[name=' + DQ + condition[0] + DQ + ']');
				}
				will_change[condition[0]] = $condition;
			}
			var condition_name = $condition.attr('name');
			if (!condition_name) {
				condition_name = $condition.prev().attr('name');
			}
			if ((typeof $this.data('conditions')) === 'string') {
				$this.data('conditions', {});
			}
			if (!$this.data('conditions').hasOwnProperty(condition_name)) {
				$this.data('conditions')[condition_name] = { element: $condition, values: {}};
			}
			$.each(condition[1].split(','), function(value_key, value) {
				$this.data('conditions')[condition_name].values[value] = value;
			});
			var this_name = $this.attr('name');
			if (!this_name) {
				this_name = $this.prev().attr('name');
			}
			if ($condition.data('condition-of')) {
				$condition.data('condition-of')[this_name] = $this;
			}
			else {
				var condition_of = {};
				condition_of[this_name] = $this;
				$condition.data('condition-of', condition_of);
			}
		});
	});

	$.each(will_change, function(condition_name, $condition) {
		if (!$condition.data('condition-change')) {
			$condition.data('condition-change', true);
			$condition.change(function()
			{
				var $this = $(this);
				$.each($this.data('condition-of'), function(element_name, $element) {
					var show = true;
					$.each($element.data('conditions'), function(condition_name, condition) {
						var found = false;
						$.each(condition.values, function(value) {
							var element_type = condition.element.attr('type');
							var element      = (element_type === 'radio')
								? condition.element.filter(':checked')
								: condition.element;
							var element_value = (element_type === 'checkbox')
								? (element.is(':checked') ? '1' : '0')
								: element.val();
							if (value === '@empty') {
								found = !element_value.length;
							}
							else if (value === '@set') {
								found = element_value.length;
							}
							else {
								found = (element_value === value);
							}
							return !found;
						});
						return (show = found);
					});
					var name = $element.attr('name');
					if (!name) {
						name = $element.data('name');
					}
					if (!name) {
						name = $element.prev().attr('name');
					}
					if (!name && $element.is('label')) {
						name = $element.closest('[id]').attr('id');
					}
					if (name.beginsWith('id_')) {
						name = name.substr(3);
					}
					name = name.replace('[', '.').replace(']', '').replace('id_', '');
					var $field = $element.closest('[id="' + name + '"]');
					if (!$field.length) {
						$field = $element.closest('[data-name="' + name + '"]');
					}
					if (!$field.length) {
						$field = $element.parent().children();
					}
					var $input_parent = $field.is('input, select, textarea') ? $field.parent() : $field;
					if (show) {
						// when shown, get the locally saved value back (undo restores last typed value)
						$input_parent.find('input, select, textarea').each(function() {
							var $this = $(this);
							// show can be called on already visible and valued element : ignore them
							if ($this.data('value')) {
								$this.val($this.data('value'));
							}
							$this.data('value', '');
						});
						$field.show();
					}
					else {
						$field.hide();
						// when hidden, reset value to empty
						$input_parent.find('input, select, textarea').each(function() {
							var $this = $(this);
							$this.data('value', $this.val());
							// never empty values on required fields TODO should be managed in validator
							if (!$this.attr('required') && !$this.data('required')) {
								$this.val('');
							}
						});
					}
				});
			});
		}
		$condition.change();
	});

	//--------------------------------------------------------- input[data-translate=data] ctrl+click
	$('input[data-translate=data]').build('click', function(event)
	{
		if (event.ctrlKey || event.metaKey) {
			var $this         = $(this);
			var $form         = $this.closest('article');
			var class_path    = $form.data('class').repl(BS, SL);
			var id            = $form.data('id');
			var property_name = $this.attr('name');
			var uri           = '/ITRocks/Framework/Locale/Translation/Data/form/';
			uri += class_path + SL + id + SL + property_name;
			redirect(uri, '#popup', $this);
		}
	});

	//--------------------------------------------------------- input[class~=id][name] previous_value
	$('input[class~=id][name]').build('each', function()
	{
		var $this = $(this);
		var $next = $this.next('input');
		if ($next.length && $this.val()) {
			$this.data('previous-value', [$this.val(), $next.val()]);
		}
	});

	//--------------------------------------------------------------------------------------- onEvent
	var on_event_pool = [];
	var onEvent = function(event)
	{
		var $this  = $(this);
		var $form  = $this.closest('form');
		var target = '#messages';
		var uri    = $this.data(event);
		$.each(uri.split(','), function(key, uri) {
			if (uri.indexOf(SP) > -1) {
				target = uri.rParse(SP);
				uri    = uri.lParse(SP);
			}

			// on-event-pool avoid calling several times the same handler on several changed inputs
			var on_event_pool_index = on_event_pool.indexOf(uri);
			if (on_event_pool_index >= 0) return;
			on_event_pool.push(uri);

			uri = window.app.uri_base + SL + uri;
			if ($this.prop('name')) {
				uri += SL + $this.prop('name');
			}
			uri += '?as_widget';

			$.post(uri, $form.formSerialize(), function(data)
			{
				if (data) {
					if (data.substr(0, 1) === '{') {
						$.each(JSON.parse(data), function(name, value) {
							if ((name.indexOf('#')) > -1 || (name.indexOf('.') > -1)) {
								$(name).html(value).build();
							}
							else {
								setFieldValue($form, name, value);
							}
						});
					}
					else {
						$(target).html(data).build();
					}
				}
				setTimeout(function() { on_event_pool.splice(on_event_pool_index, 1); });
			});

		});
	};

	//----------------------------------------------------------------- input[data-on-change] .change
	selector = 'input[data-on-change], select[data-on-change], textarea[data-on-change]';
	$(selector).build('change', function()
	{
		onEvent.call(this, 'on-change');
	});

	//--------------------------------------------------------- table[data-on-remove] td.minus .click
	$('li.multiple li.minus').build('click', function()
	{
		var $this    = $(this);
		var selector = 'table[data-on-remove], ul[data-on-remove]';
		var $block   = $this.closest(selector);
		if (!$block.length) {
			return;
		}
		// do not execute before the row has been removed : the event happens AFTER removal
		var call = function() {
			if ($this.closest(selector).length) {
				setTimeout(call);
			}
			else {
				onEvent.call($block, 'on-remove');
			}
		};
		call();
	});

	//--------------------------------------------------------------------------- .vertical.scrollbar
	$('.vertical.scrollbar').build($.fn.verticalscrollbar);

	CKEDITOR.disableAutoInline = true;

	//------------------------------------------------------------------------------- getEditorConfig
	/**
	 * @param type string
	 * @returns {{customConfig: string}}
	 */
	var getEditorConfig = function(type)
	{
		var file_name = 'ckeditor-config_'+ type +'.js';
		var config    = {
			customConfig: window.app.project_uri + SL + 'itrocks/framework/js' + SL + file_name
		};
		if (window.app.editorConfig) {
			config = $.extend({}, config, window.app.editorConfig);
		}
		return config;
	};

	//------------------------------------------------------------------------------- setEditorConfig
	var setEditorConfig = function(context, type)
	{
		var $ckeditor = $('.ckeditor-' + type);
		$ckeditor.build(function() {
			this.ckeditor(getEditorConfig(type));
		});
	};

	setEditorConfig(this, 'full');
	setEditorConfig(this, 'standard');

});
