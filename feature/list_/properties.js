$(document).ready(function()
{
	var $body = $('body');

	//----------------------------------------------------------------------------------- addProperty
	var addProperty = function($object, property_name, before_after, before_after_property_name)
	{
		var $window    = $object.closest('article.list');
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

	//------------------------------------------------------------------------------------------ drag
	/**
	 * when a property is dragged over the droppable object
	 */
	var drag = function(event, ui)
	{
		var $droppable     = $(this);
		var draggable_left = ui.offset.left + (ui.helper.width() / 2);
		var count          = 0;
		var found          = 0;
		var is_table       = $droppable.is('table');
		var $columns       = $droppable.find((is_table ? 'tr > *' : 'ol > li') + ':not(:first)');
		$columns.each(function() {
			count ++;
			var $this = $(this);
			var $prev = $this.prev(is_table ? 'td, th' : 'li');
			var left  = $prev.offset() ? ($prev.offset().left + $prev.width()) : 0;
			var right = $this.offset().left + $this.width();
			if ((draggable_left > left) && (draggable_left <= right)) {
				found   = (draggable_left <= ((left + right) / 2)) ? count : (count + 1);
				var old = $droppable.data('insert-after');
				if (found !== old) {
					var select;
					if (old !== undefined) {
						select = (is_table ? 'tr > *' : 'ol > li') + ':nth-child(' + old + ')';
						$droppable.find(select).removeClass('insert-right');
					}
					select = (is_table ? 'tr > *' : 'ol > li') + ':nth-child(' + found + ')';
					$droppable.find(select).addClass('insert-right');
					$droppable.data('insert-after', found);
				}
				return false;
			}
		});
	};

	//------------------------------------------------------------------------------------------- out
	/**
	 * when a property is not longer between two columns
	 */
	var out = function($this, event, ui)
	{
		$this.find('.insert-right').removeClass('insert-right');
		$this.removeData('insert-after');
		$this.removeData('drag-callback');
		ui.draggable.removeData('over-droppable');
	};

	//---------------------------------------------------------------------------------- article.list
	$body.build('call', 'article.list', function()
	{

		this.each(function()
		{
			var $this = $(this);
			var $list = $this.find('table.list, ul.list');

			//------------------------------------------------------------ .column_select > a.popup click
			// column select popup
			$this.find('.column_select > a.popup').click(function(event)
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

			//--------------------------------------------------------------------------- .list droppable
			$list.droppable(
			{
				accept:    '.property',
				tolerance: 'touch',

				drop: function(event, ui)
				{
					var $this        = $(this);
					var insert_after = $this.data('insert-after');
					var is_table     = $this.is('table');
					if (insert_after !== undefined) {
						var insert_before = insert_after + 1;
						var $th = $this.find(
							(is_table ? 'tr:first > th' : 'ol:first > li') + ':nth-child(' + insert_before + ')'
						);
						var $draggable           = ui.draggable;
						var before_property_name = $th.data('property');
						var property_name        = $draggable.data('property');
						addProperty($this, property_name, 'before', before_property_name);
					}
					out($this, event, ui);
					ui.helper.data('dropped', true);
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

		});
	});

	//-------------------------------------------------------- #column_select li.basic.property click
	$body.build('click', '.property-select .property', function()
	{
		var $this      = $(this);
		var data_class = $this.closest('.property-select').data('class');
		var selector   = 'article.list[data-class=' + data_class.repl(BS, BS + BS) + ']';
		var $list      = $(selector);
		if ($list.length) {
			addProperty($list, $this.data('property'), 'before');
		}
	});

});
