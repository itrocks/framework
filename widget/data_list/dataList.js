$('document').ready(function()
{
	var selection = [];

	$('.list.window').build(function()
	{
		if (!this.length) return;

		this.inside('.list.window').each(function()
		{
			var $this = $(this);

			//-------------------------------------------------------------- .column_select>a.popup click
			// column select popup
			$this.find('.column_select>a.popup').click(function(event)
			{
				var $this = $(this);
				var $div = $this.closest('.column_select').find('#column_select');
				if ($div.length) {
					if ($div.is(':visible')) {
						$div.hide();
					}
					else {
						$div.show();
						$div.find('input').first().focus();
					}
					event.preventDefault();
					event.stopImmediatePropagation();
				}
			});

			//------------------------------------------------------------ .search input|textarea keydown
			// reload list when #13 pressed into a search input
			$this.find('.search').find('input, textarea').keydown(function(event)
			{
				if (event.keyCode == 13) {
					$(this).closest('form').submit();
				}
			});

			//--------------------------------------------------------------------- .search select change
			$this.find('.search').find('select').change(function()
			{
				$(this).closest('form').submit();
			});

			//-------------------------------------------------------------------------------------- drag
			// when a property is dragged over the droppable object
			var drag = function(event, ui)
			{
				var $droppable     = $(this);
				var draggable_left = ui.offset.left + (ui.helper.width() / 2);
				var count          = 0;
				var found          = 0;
				$droppable.find('thead>tr:first>th:not(:first)').each(function() {
					count ++;
					var $this = $(this);
					var $prev = $this.prev('th');
					var left = $prev.offset().left + $prev.width();
					var right = $this.offset().left + $this.width();
					if ((draggable_left > left) && (draggable_left <= right)) {
						found = (draggable_left <= ((left + right) / 2)) ? count : (count + 1);
						var old = $droppable.data('insert-after');
						if (found != old) {
							if (old != undefined) {
								$droppable.find('colgroup>col:nth-child(' + old + ')').removeClass('insert-right');
							}
							if (found > 1) {
								$droppable.find('colgroup>col:nth-child(' + found + ')').addClass('insert-right');
								$droppable.data('insert-after', found);
							}
						}
						return false;
					}
				});
			};

			//--------------------------------------------------------------------------------------- out
			// when a property is not longer between two columns
			var out = function($this, event, ui)
			{
				$this.find('.insert-right').removeClass('insert-right');
				$this.removeData('insert-after');
				$this.removeData('drag-callback');
				ui.draggable.removeData('over-droppable');
			};

			//--------------------------------------------------------------------------- table droppable
			$this.children('table').droppable({
				accept:    '.property',
				tolerance: 'touch',

				drop: function(event, ui)
				{
					var $this = $(this);
					var insert_after = $this.data('insert-after');
					if (insert_after != undefined) {
						var app = window.app;
						var $window = $this.closest('.list.window');
						var $th = $this.find('thead>tr:first>th:nth-child(' + insert_after + ')');
						var $draggable = ui.draggable;
						var property_name = $draggable.data('property');
						var after_property_name = $th.data('property');
						var class_name = $window.data('class').repl(BS, SL);
						var uri = app.uri_base + SL + class_name + SL + 'dataListSetting'
							+ '?add_property=' + property_name
							+ '&after=' + ((after_property_name != undefined) ? after_property_name : '')
							+ '&as_widget'
							+ app.andSID();

						$.ajax({ url: uri, success: function()
						{
							var class_name = $window.data('class').repl(BS, SL);
							var feature_name = $window.data('feature');
							var url = app.uri_base + SL + class_name + SL + feature_name
								+ '?as_widget' + window.app.andSID();
							$.ajax({ url: url, success: function(data)
							{
								var $container = $window.parent();
								$container.html(data);
								$container.children().build();
							}});
						}});

					}
					out($this, event, ui);
				},

				out: function(event, ui)
				{
					out($(this), event, ui);
				},

				over: function(event, ui)
				{
					var $this = $(this);
					$this.data('drag-callback', drag);
					ui.draggable.data('over-droppable', $this);
				}

			});

			//---------------------------------------- .window.title, table.list th.property a modifiable
			// modifiable list and columns titles
			var className = function($this)
			{
				return $this.closest('.list.window').data('class');
			};
			var propertyPath = function($this)
			{
				return $this.closest('th').data('property');
			};

			var callback_uri = window.app.uri_base + '/{className}/dataListSetting?as_widget'
				+ window.app.andSID();

			var data_list_property_uri = window.app.uri_base
				+ '/SAF/Framework/Widget/Data_List_Setting/Property/edit/{className}/{propertyPath}?as_widget'
				+ window.app.andSID();

			// list title (class name) double-click
			$this.find('h2>span').modifiable({
				ajax:      callback_uri + '&title={value}',
				aliases:   { 'className': className },
				target:    '#messages',
				start: function() {
					$(this).closest('h2').children('.custom.actions').css('display', 'none');
				},
				stop: function() {
					$(this).closest('h2').children('.custom.actions').css('display', '');
				}
			});

			// list column header (property path) double-click
			$this.find('table>thead>tr>th.property>a').modifiable({
				ajax:      callback_uri + '&property_path={propertyPath}&property_title={value}',
				ajax_form: 'form',
				aliases:   { 'className': className, 'propertyPath': propertyPath },
				popup:     data_list_property_uri,
				target:    '#messages'
			});

			//--------------------------------------------------------------- input[type=checkbox] change
			var checkboxes = $this.find('table>tbody>tr>td>input[type=checkbox]');
			if ($this.id in selection) {
				$this.find('input[name=selection]').val(selection[$this.id].join());
				var sel = selection[$this.id];
				checkboxes.each(function() {
					if ((sel == 'all') || (this.value in sel)) {
						this.checked = true;
					}
				});
			}
			else {
				selection[$this.id] = [];
			}
			checkboxes.change(function() {
				if (this.checked && (selection[$this.id].indexOf(this.value) == -1)) {
					selection[$this.id].push(this.value);
				}
				if (!this.checked && (selection[$this.id].indexOf(this.value) > -1)) {
					selection[$this.id].splice(selection[$this.id].indexOf(this.value), 1);
				}
				$this.find('input[name=selection]').val(selection[$this.id].join());
			});
		});

	});
});
