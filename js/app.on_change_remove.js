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

	//----------------------------------------------------------------- input[data-on-change] .change
	var selector = 'input[data-on-change], select[data-on-change], textarea[data-on-change]';
	$body.build('change', selector, function()
	{
		onEvent.call(this, 'on-change');
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
