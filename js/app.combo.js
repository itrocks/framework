$(document).ready(function()
{
	var $body = $('body');
	var DEBUG = false;

	//------------------------------------------------------------------------ input.combo comboForce
	var comboForce = function($element)
	{
		if ($element.val().length) {
			// window.running_combo enable third-parties to wait for this to be complete (eg clicks)
			window.running_combo = true;
			var request = $.param(comboRequest($element, { term: $element.val(), first: true }));
			$.getJSON(comboUri($element), request)
				.done(function(data) {
					comboValue(
						$element,
						data.id,
						data.value ? data.value : ($element.attr('name') ? $element.val() : ''),
						data.class_name
					);
					$element.prev().data('object', data);
				})
				.always(function() {
					window.running_combo = undefined;
				});
		}
		else {
			comboValue($element, null, '');
		}
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
				if (!is_constant) {
					var combo_name = $element.prev().attr('name');
					if (combo_name.indexOf(']') && $element.closest('.component-objects').length) {
						var property_name = 'id_' + $element.closest('li[data-property]').data('property');
						filter[1] = combo_name.replace(property_name, filter[1]);
					}
					var $container = $element.closest('form');
					if (!$container.length) {
						$container = $element.closest('article');
					}
				}
				var $filter_element = is_constant
					? { length: 0 }
					: $container.find('[name=' + DQ + filter[1] + DQ + ']');
				if ($filter_element.length) {
					request['filters[' + filter[0] + ']'] = $filter_element.val();
				}
				else {
					request['filters[' + filter[0] + ']']
						= is_constant ? filter[1].substr(1, filter[1].length - 2) : filter[1];
				}
			}
		}
		if ($element.data('combo-object')) {
			request['property_names'] = $element.data('combo-object').split(';');
		}
		return request;
	};

	//-------------------------------------------------------------------------- input.combo comboUri
	var comboUri = function($element)
	{
		return window.app.uri_base + SL + $element.data('combo-set-class') + SL + 'json'
	};

	//------------------------------------------------------------------------ input.combo comboValue
	/**
	 * Sets the value of the element
	 *
	 * @param $element   jQuery
	 * @param id         integer
	 * @param value      string
	 * @param class_name string
	 */
	var comboValue = function($element, id, value, class_name)
	{
		if (id && (class_name !== undefined)) {
			id = class_name + ':' + id;
		}
		if (id) {
			$element.data('combo-value', value);
		}
		else {
			$element.removeData('combo-value');
		}
		if ($element.prev().val() !== id) {
			if (DEBUG) console.log('comboValue.id: ', $element.prev(), 'value =', id);
			$element.prev().val(id).change();
		}
		if ($element.val() !== value) {
			if (DEBUG) console.log('comboValue.val: ', $element, 'value =', value);
			$element.val(value).change();
		}
	};

	//---------------------------------------------------------------------- input.combo autocomplete
	$body.build('call', 'input.combo', function()
	{
		this.autocomplete({
			delay:     100,
			minLength: 1,

			close: function(event)
			{
				var $this = $(this);
				if (DEBUG) console.log('close');
				$this.removeData('visible');
				// echap : reset original value
				if (event.keyCode === 27) {
					if (DEBUG) console.log('- reset');
					origin = $this.data('origin');
					$this.prev().val(origin.id).change();
					$this.val(origin.value).change();
				}
				// else : validate original value
				else {
					$this.data('origin', {id: $this.prev().val(), value: $this.val()});
				}
				$this.data('lock-open', true);
				setTimeout(function() { $this.removeData('lock-open'); }, 100);
			},

			open: function()
			{
				if (DEBUG) console.log('open');
				var $this = $(this);
				if (!$this.data('origin')) {
					$this.data('origin', {id: $this.prev().val(), value: $this.val()});
				}
				$this.data('visible', true);
				var $select = $('.ui-autocomplete:visible');
				if ($select.length) {
					var height = $select.height();
					var bottom = $select.offset().top + height;
					if (bottom > window.innerHeight) {
						$select.css('top', $this.offset().top - height);
					}
				}
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
				var $value = $(this);
				var $id    = $value.prev().filter('input[type=hidden]');
				var has_id = true;
				if (!$id.length) {
					has_id = false;
					$id    = $value;
				}

				var previous_id    = $id.val();
				var previous_value = $value.val();
				if (has_id) {
					var id = ui.item.id;
					if (ui.item.class_name !== undefined) {
						id = ui.item.class_name + ':' + id;
					}
					if (DEBUG) console.log('select.id:', $id, 'value =', id);
					$id.val(id).change();
				}
				// mouse click : copy the full value to the input
				if (!event.keyCode) {
					if (DEBUG) console.log('select.val:', $value, 'value =', ui.item.value);
					$value.val(ui.item.value).change();
				}
				$value.data('combo-value', ui.item.value);
				if (!comboMatches($value)) {
					comboForce($value);
				}
				if (previous_value !== $value.val()) {
					$value.change();
				}
				if (previous_id !== $id.val()) {
					$id.change();
				}
				if ($value.data('combo-object')) {
					$id.data('object', ui.item);
				}
			}
		});

		//--------------------------------------------------------------------------- input.combo focus
		this.focus(function()
		{
			var $this = $(this);
			$this.data('combo-value', $this.val());
			if (!$this.data('origin')) {
				$this.data('origin', {id: $this.prev().val(), value: $this.val()});
			}
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
					var combo_value = $this.data('combo-value');
					if (DEBUG) console.log('blur:', $this, combo_value);
					$this.val(combo_value).change();
				}
				else {
					comboForce($this);
				}
			}
			$this.removeData('combo-value');
			$this.removeData('origin');
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
					$target = $(target.startsWith('#') ? 'main' : '#main');
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
							handle: 'header',
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
			if ((event.keyCode === 40) && !$this.data('visible')) {
				if (DEBUG) console.log('keydown.search');
				var min_length = $this.autocomplete('option', 'minLength');
				var value = $this.val();
				$this.val('');
				$this.autocomplete('option', 'minLength', 0);
				$this.autocomplete('search', '');
				$this.autocomplete('option', 'minLength', min_length);
				$this.val(value);
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
	$body.build('click', 'input.combo~.more', function(event)
	{
		event.preventDefault();
		var $this = $(this).prevAll('input.combo');
		if ($this.data('lock-open')) {
			return;
		}
		if (DEBUG) console.log('click.search');
		var min_length = $this.autocomplete('option', 'minLength');
		$this.autocomplete('option', 'minLength', 0);
		$this.autocomplete('search', '').focus();
		$this.autocomplete('option', 'minLength', min_length);
	});

});
