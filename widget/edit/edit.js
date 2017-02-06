$('document').ready(function()
{
	$('form').build(function()
	{
		if (!this.length) return;
		//noinspection JSUnresolvedVariable
		var app = window.app;

		//------------------------------------------------------------------- .auto_height, .auto_width
		this.inside('.auto_height').autoHeight();
		this.inside('.auto_width').autoWidth();

		//--------------------------------------------------------------------------------- close popup
		if (this.is('.popup') || this.closest('.popup').length) {
			var $popup = this.is('.popup') ? this : this.closest('.popup');
			$popup.find('.general.actions .close a').click(function() {
				$(this).removeAttr('href').removeAttr('target');
			});
			$popup.find('.general.actions a').click(function() {
				var $this = $(this);
				setTimeout(function() { $this.closest('.popup').remove(); }, 1);
			});
		}

		//-------------------------------------------------------------------------------------- .minus
		this.inside('.minus').click(function()
		{
			var $this = $(this);
			if ($this.closest('tbody').children().length > 1) {
				$this.closest('tr').remove();
			}
			else {
				var $table = $this.closest('table');
				var $new_row = $table.data('saf_add').clone();
				$this.closest('tr').replaceWith($new_row);
				$new_row.build();
				$table.data('saf_last_index', $table.data('saf_last_index') + 1);
			}
		});

		//------------------------------------------------------------------------ input[type=checkbox]
		this.inside('input[type=checkbox]').change(function()
		{
			var $checkbox = $(this);
			var $input = $checkbox.prev().filter('input[type=hidden]');
			if ($input.length) {
				var old_check = $input.val();
				var check = $checkbox.is(':checked') ? '1' : '0';
				var nullable = String($checkbox.data('nullable'));
				if (nullable.length) {
					if (old_check == nullable) {
						check = '';
						$checkbox.attr('checked', false);
					}
				}
				$input.val(check).change();
				$checkbox.val(check);
			}
		});

		//-------------------------------------------------------------- input[type=checkbox][readonly]
		this.inside('input[type=checkbox][readonly]').click(function(event)
		{
			event.preventDefault();
		});

		//-------------------------------------------------------------------------------------- select
		this.inside('select').sortcontent();

		//----------------------------------------------------------------- table.collection, table.map
		this.inside('table.collection, table.map').each(function()
		{
			var $this = $(this);
			$this.data('saf_add', $this.children('tbody').children('tr.new').clone());
			// saf_add_index : the value of the index to be replaced into the model for new rows
			$this.data('saf_add_index', $this.children('tbody').children('tr').length - 1);
			// saf_last_index : the last used index (lines count - 1)
			$this.data('saf_last_index', Math.max(0, $this.data('saf_add_index') - 1));
			if ($this.data('saf_add_index')) {
				$this.children('tbody').children('tr.new').remove();
			}
		});

		//------------------------------------------------------- table.collection input,textarea focus
		var autoAddLine = function()
		{
			var $this = $(this);
			var $tr = $this.closest('tr');
			if ($this.val() && $tr.length && !$tr.next('tr').length) {
				var $collection = $tr.closest('table.collection, table.map');
				if ($collection.length) {
					// calculate depth in order to increment the right index
					var depth = 0;
					var $parent = $collection;
					while (($parent = $parent.parent().closest('table.collection, table.map')).length) {
						depth ++;
					}
					// calculate new row and indexes
					var $table = $($collection[0]);
					var $new_row = $table.data('saf_add').clone();
					$table.data('saf_last_index', $table.data('saf_last_index') + 1);
					var index = $table.data('saf_last_index');
					var old_index = $table.data('saf_add_index');
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
								if (html_index == old_index) {
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
					$table.children('tbody').append($new_row);
					$new_row.build();
				}
			}
		};
		this.inside('input, textarea').focus(autoAddLine).keyup(autoAddLine);

		//------------------------------------------------------------------- input.datetime datePicker
		this.inside('input.datetime').datepicker({
			dateFormat:        dateFormatToDatepicker(app.date_format),
			showOn:            'button',
			showOtherMonths:   true,
			selectOtherMonths: true
		});

		this.inside('input.datetime').blur(function()
		{
			$(this).datepicker('hide');
		});

		this.inside('input.datetime').keyup(function(event)
		{
			if ((event.keyCode != 13) && (event.keyCode != 27)) {
				$(this).datepicker('show');
			}
		});

		//------------------------------------------------------------------------ input.combo comboUri
		var comboUri = function($element)
		{
			return window.app.uri_base + SL + $element.data('combo-class') + SL + 'json'
		};

		//-------------------------------------------------------------------- input.combo comboRequest
		var comboRequest = function($element, request)
		{
			if (request == undefined) {
				request = [];
			}
			if (!request['first']) {
				request['limit'] = 100;
			}
			if (!window.app.use_cookies) request['PHPSESSID'] = window.app.PHPSESSID;
			var filters = $element.data('combo-filters');
			if (filters != undefined) {
				filters = filters.split(',');
				for (var key in filters) if (filters.hasOwnProperty(key)) {
					var filter = filters[key].split('=');
					var $filter_element = $($element.get(0).form).find('[name=' + DQ + filter[1] + DQ + ']');
					if ($filter_element.length) {
						request['filters[' + filter[0] + ']'] = $filter_element.val();
					}
					else {
						request['filters[' + filter[0] + ']'] = filter[1];
					}
				}
			}
			return request;
		};

		//-------------------------------------------------------------------- input.combo comboMatches
		/**
		 * Returns true if the typed value is the same as the data value read from the server
		 * Returns false if do not match or if there is no data value
		 *
		 * @param $element string
		 * @returns boolean
		 */
		var comboMatches = function($element)
		{
			if ($element.data('value')) {
				var val = $element.val().toLowerCase();
				var dat = $element.data('value').toLowerCase();
				return (!val.length) || (dat.indexOf(val) != -1);
			}
			else {
				return false;
			}
		};

		//---------------------------------------------------------------------- input.combo comboForce
		var comboForce = function($element)
		{
			if ($element.val().length) {
				$.getJSON(
					comboUri($element),
					$.param(comboRequest($element, { term: $element.val(), first: true })),
					function(data) {
						if (data.id) {
							//console.log('> found ' + data.id + ': ' + data.value);
							$element.data('value', data.value);
							$element.prev().val(data.id);
							$element.val(data.value);
						}
						else {
							//console.log('> not found');
							$element.prev().val('');
							$element.val('');
							$element.removeData('value');
						}
					}
				);
			}
			else {
				//console.log('> empty value');
				$element.prev().val('');
				$element.val('');
				$element.removeData('value');
			}
		};

		//-------------------------------------------------------------------- input.combo autocomplete
		this.inside('input.combo').autocomplete(
		{
			autoFocus: true,
			delay: 100,
			minLength: 0,

			close: function(event)
			{
				$(event.target).keyup();
			},

			source: function(request, response)
			{
				var $element = this.element;
				$.getJSON(
					comboUri($element),
					$.param(comboRequest($element, request)),
					function(data) { response(data); }
				);
			},

			select: function(event, ui)
			{
				// caption of the combo
				var $caption = $(this);
				// does value is an id managed with a previous input, or same string as caption?
				var has_id = true;
				var $value = $caption.prev();
				if (!$value.length) {
					// no id ! the value is the caption !
					$value = $caption;
					has_id = false;
				}
				var previous_value = $value.val();

				//console.log('selected ' + ui.item.id + ': ' + ui.item.value);
				if (has_id) {
					$value.val(ui.item.id);
				}
				if (!event.keyCode) {
 					// when mouse is clicked, then the value changes, sure !
					$caption.val(ui.item.value);
				}
				$caption.data('value', ui.item.value);

				if (!comboMatches($caption)) {
					//console.log('> ' + $caption.val() + ' does not match ' + $caption.data('value'));
					comboForce($caption);
				}
				if (previous_value != $value.val()) {
					$value.change();
				}
			}
		})

		//---------------------------------------------------------------------------- input.combo focus
		.focus(function()
		{
			var $this = $(this);
			$this.data('value', $this.val());
		})

		//---------------------------------------------------------------------------- input.combo blur
		.blur(function()
		{
			var $this = $(this);
			if (comboMatches($this)) {
				//console.log($this.val() + ' matches ' + $this.data('value'));
				$this.val($this.data('value'));
			}
			else {
				//console.log('blur : ' + $this.val() + ' does not match ' + $this.data('value'));
				comboForce($this);
			}
			$this.removeData('value');
		})

		//---------------------------------------------------------------------- input.combo ctrl+click
		.click(function(event)
		{
			if (event.ctrlKey) {
				var $this = $(this);
				var id   = $this.prev().val();
				var path = $this.data('edit-class').repl('\\', '/');
				var uri  = SL + path + SL + id + SL + 'edit';
				redirect(uri, '#main');
			}
		})

		//----------------------------------------------------------------------------- input.combo ESC
		.keyup(function(event)
		{
			if (event.keyCode == 27) {
				$(this).removeData('value');
				$(this).prev().val('');
				$(this).val('');
				$(this).prev().change();
			}
		});

		//--------------------------------------------------------------------- input.combo~.more click
		this.inside('input.combo~.more').click(function(event)
		{
			event.preventDefault();
			var $combo = $($(this).siblings('input.combo'));
			if (!$combo.autocomplete('widget').is(':visible')) {
				$combo.focus();
				$combo.autocomplete('search', '');
			}
		});

		//---------------------------------------------------------------------- input[data-conditions]
		var will_change = {};
		this.inside('[data-conditions]').each(function()
		{
			var $this = $(this);
			var conditions = $this.data('conditions').replace(/\(.*\)/g);
			$.each(conditions.split(';'), function(condition_key, condition) {
				condition = condition.split('=');
				var $condition;
				if (will_change.hasOwnProperty(condition[0])) {
					$condition = will_change[condition[0]];
				}
				else {
					$condition = $($this.get(0).form).find('[name=' + DQ + condition[0] + DQ + ']');
					will_change[condition[0]] = $condition;
				}
				var condition_name = $condition.attr('name');
				if (!condition_name) condition_name = $condition.prev().attr('name');
				if (typeof $this.data('conditions') == 'string') $this.data('conditions', {});
				if (!$this.data('conditions').hasOwnProperty(condition_name)) {
					$this.data('conditions')[condition_name] = { element: $condition, values: {}};
				}
				$.each(condition[1].split(','), function(value_key, value) {
					$this.data('conditions')[condition_name].values[value] = value;
				});
				var this_name = $this.attr('name');
				if (!this_name) this_name = $this.prev().attr('name');
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

		$.each(will_change, function(condition_name, $condition)
		{
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
								var element_value = (condition.element.attr('type') === 'checkbox')
									? (condition.element.is(':checked') ? 1 : 0) : condition.element.val();
								return !(found = (element_value == value));
							});
							return (show = found);
						});
						var name = $element.attr('name');
						if (!name)                  name = $element.prev().attr('name');
						if (name.beginsWith('id_')) name = name.substr(3);
						var $field = $element.closest('#' + name);
						if (!$field.length) {
							$field = $element.parent().children();
						}
						show ? $field.show() : $field.hide();
					});
				});
			}
			$condition.change();
		});

		//--------------------------------------------------------------- input[data-on-change] .change
		this.inside('input[data-on-change]').change(function()
		{
			var $this = $(this);
			var $form = $this.closest('form');
			var uri = $this.data('on-change');
			$.each(uri.split(','), function(key, uri) {
				uri = window.app.uri_base + SL + uri + SL + $this.prop('name') + '?as_widget';

				$.post(uri, $form.formSerialize(), function(data)
				{
					if (data) {
						if (data.substr(0, 1) == '{') {
							$.each(JSON.parse(data), function(name, value) {
								var $input = $form.find('input[name=' + DQ + name + DQ + ']');
								if (((typeof value) == 'string') && (value.substr(0, 1) == ':')) {
									if ($input.val() == false) {
										// false ie '0', ' or 0
										$input.val(value.substr(1));
										$input.change();
									}
								}
								else {
									$input.val(value);
									$input.change();
								}
							});
						}
						else {
							$('#messages').html(data).build();
						}
					}
				});

			});
		});

		//------------------------------------------------------------------------- .vertical.scrollbar
		this.inside('.vertical.scrollbar').verticalscrollbar();

		//----------------------------------------------------------------------------- .ckeditor-full
		var $ckeditor_full = this.inside('.ckeditor-full');
		if ($ckeditor_full.length) $ckeditor_full.ckeditor({
			customConfig: window.app.project_uri + SL + 'itrocks/framework/js/ckeditor-config_full.js'
		});

		//------------------------------------------------------------------------ .ckeditor-standard
		var $ckeditor_standard = this.inside('.ckeditor-standard');
		if ($ckeditor_standard.length) $ckeditor_standard.ckeditor({
			customConfig: window.app.project_uri + SL + 'itrocks/framework/js/ckeditor-config_standard.js'
		});

	});
});
