$('document').ready(function()
{
	// Only if we have select_all, we can exclude a part of elements
	var excluded_selection = [];
	var select_all         = [];
	var selection          = [];

	$('.list.window').build(function()
	{
		if (!this.length) return;

		var addProperty = function($object, property_name, before_after, before_after_property_name)
		{
			var $window    = $object.closest('.list.window');
			var app        = window.app;
			var class_name = $window.data('class').repl(BS, SL);
			var uri        = app.uri_base + SL + class_name + SL + 'listSetting'
				+ '?add_property=' + property_name;
			if (before_after_property_name !== undefined) {
				uri += '&' + before_after + '=' + before_after_property_name;
			}
			uri += '&as_widget' + app.andSID();
			$.ajax({ url: uri, success: function()
			{
				var class_name   = $window.data('class').repl(BS, SL);
				var feature_name = $window.data('feature');
				var url          = app.uri_base + SL + class_name + SL + feature_name
					+ '?as_widget' + window.app.andSID();
				$.ajax({ url: url, success: function(data)
				{
					var $container = $window.parent();
					$container.html(data);
					$container.children().build();
				}});
			}});
		};

		//---------------------------------------------------------- .column_select li.basic.property
		if (this.closest('.list.window .column_select').length) {
			this.find('li.basic.property').click(function()
			{
				var $this = $(this);
				addProperty($this, $this.data('property'), 'before');
			});
		}

		this.inside('.list.window').each(function()
		{
			var $this = $(this);
			$this.id = $this.attr('id');

			//--------------------------------------------------------------- .list.window resetSelection
			var resetSelection = function()
			{
				excluded_selection = [];
				select_all         = [];
				selection          = [];
			};

			//------------------------------------------------------------------ .list.window updateCount
			var updateCount = function ()
			{
				var count_elements, select_all_content, selection_content, selection_exclude_content, text;
				if (select_all[$this.id]) {
					select_all_content        = 1;
					selection_content         = '';
					selection_exclude_content = excluded_selection[$this.id].join();
					count_elements  = ($this.find('.select_count>ul>li>.select_all').data('count'));
					count_elements -= excluded_selection[$this.id].length;
					text            = 'x' + count_elements;
				}
				else {
					selection_content         = selection[$this.id].join();
					select_all_content        = 0;
					selection_exclude_content = '';
					text                      = 'x' + selection[$this.id].length;
				}
				$this.find('.select_count>.objects').html(text);
				$this.find('input[name=excluded_selection]').val(selection_exclude_content);
				$this.find('input[name=select_all]').val(select_all_content);
				$this.find('input[name=selection]').val(selection_content);
			};

			//-------------------------------------------------------------- .column_select>a.popup click
			// column select popup
			$this.find('.column_select>a.popup').click(function(event)
			{
				var $this = $(this);
				var $div  = $this.closest('.column_select').find('#column_select');
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
				if (event.keyCode === 13) {
					resetSelection();
					$(this).closest('form').submit();
				}
			});

			//--------------------------------------------------------------------- .search select change
			$this.find('.search select').change(function()
			{
				resetSelection();
				$(this).closest('form').submit();
			});

			//------------------------------------------------------------- .search .reset.search a click
			$this.find('.search .reset.search a').click(resetSelection);

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
					var left  = $prev.offset().left + $prev.width();
					var right = $this.offset().left + $this.width();
					if ((draggable_left > left) && (draggable_left <= right)) {
						found   = (draggable_left <= ((left + right) / 2)) ? count : (count + 1);
						var old = $droppable.data('insert-after');
						if (found !== old) {
							if (old !== undefined) {
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
					var $this        = $(this);
					var insert_after = $this.data('insert-after');
					if (insert_after !== undefined) {
						var insert_before = insert_after + 1;
						var $th = $this.find('thead>tr:first>th:nth-child(' + insert_before + ')');
						var $draggable           = ui.draggable;
						var before_property_name = $th.data('property');
						var property_name        = $draggable.data('property');
						addProperty($this, property_name, 'before', before_property_name);
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

			var callback_uri = window.app.uri_base + '/{className}/listSetting?as_widget'
				+ window.app.andSID();

			var list_property_uri = window.app.uri_base
				+ '/ITRocks/Framework/Widget/List_Setting/Property/edit/{className}/{propertyPath}?as_widget'
				+ window.app.andSID();

			// list title (class name) double-click
			$this.find('h2>span').modifiable({
				ajax:    callback_uri + '&title={value}',
				aliases: { 'className': className },
				target:  '#messages',
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
				popup:     list_property_uri,
				target:    '#messages'
			});

			//--------------------------------------------------------------- input[type=checkbox] change
			var checkboxes_select = 'table>tbody>tr>td>input[type=checkbox]';
			var checkboxes        = $this.find(checkboxes_select);
			if ($this.id in selection) {
				checkboxes.each(function() {
					if (
						(select_all[$this.id] && ($.inArray(this.value, excluded_selection[$this.id]) === -1))
						|| $.inArray(this.value, selection[$this.id]) !== -1
					) {
						$(this).prop('checked', true);
					}
				});
			}
			else {
				excluded_selection[$this.id] = [];
				select_all[$this.id]         = false;
				selection[$this.id]          = [];
			}

			checkboxes.change(function()
			{
				if (select_all[$this.id]) {
					if (!this.checked && (excluded_selection[$this.id].indexOf(this.value) === -1)) {
						excluded_selection[$this.id].push(this.value);
					}
					if (this.checked && (excluded_selection[$this.id].indexOf(this.value) > -1)) {
						excluded_selection[$this.id]
							.splice(excluded_selection[$this.id].indexOf(this.value), 1);
					}
					$this.find(checkboxes_select + '[value=' + this.value + ']')
						.attr('checked', this.checked);
				}
				else {
					if (this.checked && (selection[$this.id].indexOf(this.value) === -1)) {
						selection[$this.id].push(this.value);
					}
					if (!this.checked && (selection[$this.id].indexOf(this.value) > -1)) {
						selection[$this.id].splice(selection[$this.id].indexOf(this.value), 1);
					}
					// Repercussion if with have multiple lines
					$this.find(checkboxes_select + '[value=' + this.value + ']')
						.attr('checked', this.checked);
				}
				updateCount();
			});

			updateCount();

			//------------------------------------------------------------------------------ selectAction
			/**
			 * Select / deselect buttons
			 *
			 * @param select boolean true to select, false to deselect
			 * @param type   string  @values all, matching, visible
 			 */
			var selectAction = function(select, type)
			{
				if (type === 'all') {
					// Re-initialize selection
					excluded_selection[$this.id] = [];
					select_all[$this.id]         = select;
					selection[$this.id]          = [];
					$this.find('table>tbody>tr>td>input[type=checkbox]').prop('checked', select);
				}
				else {
					$this.find('table>tbody>tr>td>input[type=checkbox]').each(function () {
						var checkbox = $(this);
						checkbox.prop('checked', select);
						checkbox.change();
					});
				}
				updateCount();
				return false;
			};

			//------------------------------------------------------------------- .select_count ... click
			$this.find('.select_count>.objects').click(function ()
			{
				return false;
			});

			$this.find('.select_count>ul>li>.deselect_all').click(function ()
			{
				return selectAction(false, 'all');
			});

			$this.find('.select_count>ul>li>.deselect_visible').click(function ()
			{
				return selectAction(false);
			});

			$this.find('.select_count>ul>li>.select_all').click(function ()
			{
				return selectAction(true, 'all');
			});

			$this.find('.select_count>ul>li>.select_visible').click(function ()
			{
				return selectAction(true);
			});

			$this.find('.selection.actions a.submit:not([target^="#"])').click(function(event)
			{
				var data = {
					excluded_selection: $this.find('input[name=excluded_selection]').val(),
					select_all:         $this.find('input[name=select_all]').val(),
					selection:          $this.find('input[name=selection]').val()
				};
				var form   = document.createElement('form');
				var target = $(this).attr('target');
				// remember to change me :
				form.action = event.target;
				form.method = 'post';
				form.target = target;
				for (var key in data) {
					var input   = document.createElement('input');
					input.name  = key;
					input.type  = 'hidden';
					input.value = data[key];
					form.appendChild(input);
				}
				// must add to body to submit with refresh page
				document.body.appendChild(form);
				form.submit();
				// clean html dom
				document.body.removeChild(form);
				return false;
			});

		});

	});
});
