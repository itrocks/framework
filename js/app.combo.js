$(document).ready(function()
{
	var $body = $('body');

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
			$element.prev().val(id);
			$element.prev().change();
		}
		if ($element.val() !== value) {
			$element.val(value);
			$element.change();
		}
	};

	//---------------------------------------------------------------------- input.combo autocomplete
	$body.build('call', 'input.combo', function()
	{
		this.autocomplete({
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
					$id.val(id);
				}
				// mouse click : copy the full value to the input
				if (!event.keyCode) {
					$value.val(ui.item.value);
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
	$body.build('click', 'input.combo~.more', function(event)
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

});
