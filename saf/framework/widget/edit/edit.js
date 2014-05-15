$('document').ready(function()
{

	$('form').build(function()
	{
		//noinspection JSUnresolvedVariable
		var app = window.app;

		//--------------------------------------------------------------------- .autoheight, .autowidth
		this.in('.autoheight').autoheight();
		this.in('.autowidth').autowidth();

		//-------------------------------------------------------------------------------------- .minus
		this.in('.minus').click(function()
		{
			var $this = $(this);
			if ($this.closest('tbody').children().length > 1) {
				$this.closest('tr').remove();
			}
			else {
				var $new_row = $this.closest('table').data('saf_add').clone();
				$this.closest('tr').replaceWith($new_row);
				$new_row.build();
			}
		});

		//------------------------------------------------------------------------ input[type=checkbox]
		this.in('input[type=checkbox]').change(function()
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
				$input.val(check);
				$checkbox.val(check);
			}
		});

		//-------------------------------------------------------------- input[type=checkbox][readonly]
		this.in('input[type=checkbox][readonly]').click(function(event)
		{
			event.preventDefault();
		});

		//-------------------------------------------------------------------------------------- select
		this.in('select').sortcontent();

		//----------------------------------------------------------------- table.collection, table.map
		this.in('table.collection, table.map').each(function()
		{
			var $this = $(this);
			$this.data('saf_add', $this.children('tbody').children('tr.new').clone());
			$this.data('saf_add_indice', $this.children('tbody').children('tr').length - 1);
			if ($this.data('saf_add_indice')) {
				$this.children('tbody').children('tr.new').remove();
			}
		});

		//------------------------------------------------------- table.collection input,textarea focus
		this.in('input, textarea').focus(function()
		{
			var $tr = $(this).closest('tr');
			if ($tr.length && !$tr.next('tr').length) {
				var $collection = $tr.closest('table.collection, table.map');
				if ($collection.length) {
					var $table = $($collection[0]);
					var $new_row = $table.data('saf_add').clone();
					var indice = $table.children('tbody').children('tr').length;
					var old_indice = $table.data('saf_add_indice');
					$new_row.html(
						$new_row.html()
							.repl('][' + old_indice + ']', '][' + indice + ']')
							.repl('%5D%5B' + old_indice + '%5D', '%5D%5B' + indice + '%5D')
					);
					$table.children('tbody').append($new_row);
					$new_row.build();
				}
			}
		});

		//------------------------------------------------------------------- input.datetime datepicker
		this.in('input.datetime').datepicker({
			dateFormat:        dateFormatToDatepicker(app.date_format),
			showOn:            'button',
			showOtherMonths:   true,
			selectOtherMonths: true
		});

		this.in('input.datetime').blur(function()
		{
			$(this).datepicker('hide');
		});

		this.in('input.datetime').keyup(function(event)
		{
			if ((event.keyCode != 13) && (event.keyCode != 27)) {
				$(this).datepicker('show');
			}
		});

		//------------------------------------------------------------------------ input.combo comboUri
		var comboUri = function($element)
		{
			return window.app.uri_base + '/' + $element.data('combo-class') + '/json'
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
					var $filter_element = $($element.get(0).form).find('[name="' + filter[1] + '"]');
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
		this.in('input.combo').autocomplete(
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
				//noinspection JSUnresolvedVariable
				var $element = this.element;
				$.getJSON(
					comboUri($element),
					$.param(comboRequest($element, request)),
					function(data) { response(data); }
				);
			},

			select: function(event, ui)
			{
				var $this = $(this);
				//console.log('selected ' + ui.item.id + ': ' + ui.item.value);
				$this.prev().val(ui.item.id);
				$this.data('value', ui.item.value);
				if (!comboMatches($this)) {
					//console.log('> ' + $this.val() + ' does not match ' + $this.data('value'));
					comboForce($this);
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
				$(this).siblings('.edit').click();
			}
		})

		//----------------------------------------------------------------------------- input.combo ESC
		.keyup(function(event)
		{
			if (event.keyCode == 27) {
				$(this).removeData('value');
				$(this).prev().val('');
				$(this).val('');
			}
		});

		//--------------------------------------------------------------------------- input.combo~.edit
		/**
		 * On clicking on [+] or when ctrl+click on a combo input : open add/edit popup form
		 */
		this.in('input.combo~.edit').click(function()
		{
			var $this = $(this);
			var $input = $this.siblings('input.combo');
			if (!$this.data('link')) {
				$this.data('link', $this.attr('href'));
			}
			var href = $this.data('link');
			var id = $input.prev().val();
			$this.attr('href', id ? href.repl('/add', '/' + $input.prev().val() + '/edit') : href);
		});
		this.in('input.combo~.edit').attr('tabindex', -1);
		if (this.attr('id') && (this.attr('id').substr(0, 6) == 'window')) {
			this.in('.actions>.close>a')
				.attr('href', 'javascript:$(\'#' + this.attr('id') + '\').remove()')
				.attr('target', '');
			var $button = this.in('.actions>.write>a');
			if ($button.length) {
				$button.attr('href',
					$button.attr('href')
					+ (($button.attr('href').indexOf('?') > -1) ? '&' : '?')
					+ 'close=' + this.attr('id')
				);
			}
		}
		this.in('input.combo').each(function()
		{
			$(this).parent()
				.mouseenter(function() { $(this).children('.edit').show(); })
				.mouseleave(function() { $(this).children('.edit').hide(); });
		});

		//--------------------------------------------------------------------------- input.combo~.more
		this.in('input.combo~.more').click(function(event)
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
		this.in('input[data-conditions]').each(function() {
			var $this = $(this);
			var conditions = $this.data('conditions').replace(/\(.*\)/g);
			$.each(conditions.split(';'), function(condition_key, condition) {
				condition = condition.split('=');
				var $condition;
				if (will_change.hasOwnProperty(condition[0])) {
					$condition = will_change[condition[0]];
				}
				else {
					$condition = $($this.get(0).form).find('[name="' + condition[0] + '"]');
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
				if ($condition.data('condition-of') == undefined) $condition.data('condition-of', {});
				$condition.data('condition-of')[this_name] = $this;
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
								return !(found = (condition.element.val() == value));
							});
							return (show = found);
						});
						if (show) {
							$element.parent().find('input,button').show();
						}
						else {
							$element.parent().find('input,button').hide();
						}
					});
				});
			}
			$condition.change();
		});

		//------------------------------------------------------------------------- .vertical.scrollbar
		this.in('.vertical.scrollbar').verticalscrollbar();

	});

});
