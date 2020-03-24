$(document).ready(function()
{
	var $body = $('body');

	//--------------------------------------------------------------------------------------- onEvent
	var on_event_pool = [];
	var onEvent = function(event)
	{
		var $this  = $(this);
		var $form  = $this.closest('form');
		var target = '#responses';
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
					if (['{', '['].indexOf(data.substr(0, 1)) > -1) {
						$.each(JSON.parse(data), function(name, value) {
							if ((name.indexOf('#') > -1) || (name.indexOf('.') > -1)) {
								$(name).html(value).build();
							}
							else {
								setFieldValue($form, name, value);
							}
						});
					}
					else {
						var $target = $(target);
						if ($target.is('input')) {
							$target.attr('value', data).val(data);
						}
						else if ($target.is('select')) {
							$target.val(data);
						}
						else {
							$target.html(data).build();
						}
					}
				}
				setTimeout(function() { on_event_pool.splice(on_event_pool_index, 1); });
			});

		});
	};

	//----------------------------------------------------------------------------- prepareCollection
	/**
	 * Prepare a collection :
	 * - empty it if value is null
	 * - if field_name includes a reference to a non-existing line : add it
	 *
	 * @param $field jQuery object the field element, with an id.component-objects#field_name_root
	 * @param search the search selector for the targeted field
	 * @param value  the value, for testing purpose only (setFieldValue sets the value)
	 */
	var prepareCollection = function($field, search, value)
	{
		var $collection = $field.find('> div > ul.collection', '> div > ul.map');
		if (!$collection.length) {
			return;
		}
		if (value === null) {
			$collection.data('itrocks_add_index',  0);
			$collection.data('itrocks_last_index', -1);
			// TODO LOW call "click on minus", to allow on-remove
			$collection.find('> li.data').remove();
			return;
		}
		if (!$collection.find(search).length) {
			$collection.data('addLine').call();
		}
	};

	//--------------------------------------------------------------------------------- setFieldValue
	/**
	 * Use always this method to set a new value to a field
	 * For a simple field, value is a string (or similar)
	 * For a combo field, value is [id, string representation of value]
	 *
	 * @param $form      jQuery object for the targeted form
	 * @param field_name string
	 * @param value      Array|string
	 * @todo should be able to set value for any form field tag (like select)
	 */
	var setFieldValue = function($form, field_name, value)
	{
		var search = 'input[name=' + DQ + field_name + DQ + ']'
			+ ', input[data-name=' + DQ + field_name + DQ + ']';
		var $input       = $form.find(search);
		var do_change    = true;
		var string_value = null;
		// id
		if (!$input.length) {
			$input = $form.find(search.repl('=' + DQ, '="id_'));
		}
		// new element for collection / empty collection
		if (!$input.length) {
			$input = $form.find('li.component-objects#' + field_name.lParse('['));
			if ($input.length) {
				prepareCollection($input, search, value);
				if (value === null) {
					return;
				}
				$input = $form.find(search);
			}
		}
		// not found
		if (!$input.length) {
			return;
		}

		// simple value
		if (((typeof value) === 'object') && value && !value.hasOwnProperty('0')) {
			$.each(value, function(attribute, value) {
				var $what = $input.next('input').attr(attribute) ? $input.next() : $input;
				if (attribute.beginsWith('data-')) {
					$what.removeData(attribute.substr(5));
				}
				$what.attr(attribute, value);
				if (attribute === 'value') {
					$what.change();
				}
			});
			return;
		}

		// an array with the value (and id) and its string representation
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

		if (
			$input.val().length
			&& ($input.is(':focus') || $input.next().is(':focus'))
			&& ($input.is(':visible') || $input.next().is(':visible'))
			&& (!$input.is('[readonly]') && !$input.next().is('[readonly]'))
		) {
			do_change = false;
		}

		if (do_change) {
			$input.attr('value', value).val(value);
			if ((string_value !== null)) {
				$input.next().attr('value', string_value).val(string_value).change();
			}
			$input.change();
		}
	};

	//----------------------------------------------------------------- input[data-on-change] .change
	var selector = 'input[data-on-change], select[data-on-change], textarea[data-on-change]';
	$body.build('change', selector, function()
	{
		var $this = $(this);
		if (
			(($this.attr('type') === 'checkbox') || !$this.data('realtime-change'))
			&& !$this.is('input:focus, textarea:focus')
		) {
			onEvent.call(this, 'on-change');
		}
	});

	//---------------------------------------------------------------- input[realtime-change] .change
	selector = 'input[data-realtime-change], select[data-realtime-change],'
		+ ' textarea[data-realtime-change]';
	$body.build('keyup', selector, function()
	{
		if ($(this).data('on-change')) {
			onEvent.call(this, 'on-change');
		}
	});

	//--------------------------------------------------------- table[data-on-remove] td.minus .click
	$body.build('click', 'li.multiple li.minus', function()
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

});
