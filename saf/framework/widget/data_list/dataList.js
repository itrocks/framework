$('document').ready(function()
{
	var selection = [];

	$('.list.window').build(function()
	{

		this.in('.list.window').each(function()
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
					event.stopImmediatePropagation();
					event.preventDefault();
				}
			});

			//--------------------------------------------------- .search input, .search textarea keydown
			// reload list when #13 pressed into a search input
			$this.find('.search').find('input, textarea').keydown(function(event)
			{
				if (event.keyCode == 13) {
					$(this).closest('form').submit();
				}
			});

			//--------------------------------------------------------------------------- table droppable
			// when a property is dropped between two columns
			var complete = function($this, event, ui)
			{
				var insert_after = $this.data('insert-after');
				if (insert_after != undefined) {
					$this.find('colgroup>col:nth-child(' + insert_after + ')').removeClass('insert_after');
					$this.removeData('insert-after');
				}
				ui.draggable.removeData('over-droppable');
			};

			$this.children('table').droppable({
				accept:    '.property',
				tolerance: 'touch',

				drop: function(event, ui)
				{
					var $this = $(this);
					var insert_after = $this.data('insert-after');
					if (insert_after != undefined) {
						//noinspection JSUnresolvedVariable
						var app = window.app;
						var $window = $this.closest('.list.window');
						var $th = $this.find('thead>tr:first>th:nth-child(' + insert_after + ')');
						var $draggable = ui.draggable;
						var property_name = $draggable.data('property');
						var after_property_name = $th.data('property');
						var class_name = $window.data('class').replace('\\', '/');
						var url = app.uri_base + '/' + class_name + '/dataListSetting'
							+ '?add_property=' + property_name
							+ '&after=' + ((after_property_name != undefined) ? after_property_name : '')
							+ '&as_widget=1'
							+ app.andSID();
						complete($this, event, ui);

						$.ajax({ url: url, success: function()
						{
							var $class_name = $window.data('class').replace('\\', '/');
							var $feature_name = $window.data('feature');
							var url = app.uri_base + '/' + $class_name + '/' + $feature_name
								+ window.app.askSIDand() + 'as_widget=1';
							$.ajax({ url: url, success: function(data)
							{
								var $container = $window.parent();
								$container.html(data);
								$container.children().build();
							}});
						}});

					}
				},

				over: function(event, ui)
				{
					ui.draggable.data('over-droppable', $(this));
				},

				out: function(event, ui)
				{
					complete($(this), event, ui);
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
			var uri = window.app.uri_base + '/{className}/dataListSetting'
				+ window.app.askSIDand() + 'as_widget=1';
			// list title (class name) double-click
			$this.children('h2').modifiable({
				done: uri + '&title={value}',
				aliases: { 'className': className },
				target: '#messages'
			});
			// list column header (property path) double-click
			$this.find('table>thead>tr>th.property>a').modifiable({
				done: uri + '&property_path={propertyPath}&property_title={value}',
				aliases: { 'className': className, 'propertyPath': propertyPath },
				target: '#messages'
			});

			//--------------------------------------------------------------- input[type=checkbox] change
			var checkboxes = $this.find('table>tbody>tr>td>input[type=checkbox]');
			if ($this.id in selection) {
				$this.find('input[name=selection]').val(selection[$this.id].join());
				var sel = selection[$this.id];
				checkboxes.each(function() {
					if ((sel == 'all') || (this.value in sel)) {
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
