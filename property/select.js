$(document).ready(function()
{
	var $body = $('body');

	$body.build('call', '.property-select ul.tree .property-select', function()
	{
		this.prepend($('<span>').addClass('joint'));
	});

	//--------------------------------------------------- .property_select > input[name=search] keyup
	// search
	$body.build('each', '.property-select > .search > input', function()
	{
		var last_search = '';
		var search_step = 0;

		$(this).keyup(function(event)
		{
			var $this = $(this);
			if (event.keyCode === $.ui.keyCode.ESCAPE) {
				$this.closest('#column_select.popup').fadeOut(200);
			}
			else {
				var new_search = $this.val();
				if ((last_search !== new_search) && !search_step) {
					last_search = new_search;
					search_step = 1;

					$.ajax(
						window.app.uri_base + '/ITRocks/Framework/Property/search'
						+ '/' + $this.closest('[data-class]').data('class').repl('/', '\\')
						+ '?search=' + encodeURI(new_search)
						+ '&as_widget' + window.app.andSID(),
						{
							success: function(data) {
								var $property_tree = $this.closest('section').find('> .tree');
								search_step = 2;
								$property_tree.html(data);
								$property_tree.build();
							}
						}
					);

					var retry = function() {
						if (search_step === 1) {
							setTimeout(retry, 200);
						}
						else {
							search_step = 0;
							if ($this.val() !== last_search) {
								$this.keyup();
							}
						}
					};

					setTimeout(retry, 500);
				}
			}
		});
	});

	//--------------------------------------------------------------- ul.property_tree > li > a click
	// create tree
	$body.build('click', '.property-select a.expand', function(event)
	{
		var $anchor      = $(this);
		var $li          = $anchor.closest('li.expandable');
		var $div         = $li.children('div');
		var $move_before = null;
		var $property    = $li.children('a.property');
		var title        = $property.attr('title');

		if ($div.children().length) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}

		if ($li.hasClass('expanded')) {
			// hide
			$li.removeClass('expanded');
			$div.slideUp(100);
			// reduce title
			if ($property.data('text')) {
				title = $property.data('text');
				$property.text(title);
				$property.removeData('text');
			}
			// move
			$li.siblings('.expandable').each(function() {
				var $next_li = $(this);
				if ($next_li.children('a.property').attr('title') > title) {
					$move_before = $next_li;
					return false;
				}
			});
			if (!$move_before) {
				$move_before = $li.siblings('.expanded').first();
			}
		}

		else {
			// show
			$li.addClass('expanded');
			$div.slideDown(100);
			// expand title
			title = title.split(DOT).reverse().join(' < ');
			$property.data('text', $property.text());
			$property.text(title);
			// move
			$li.siblings('.expanded').each(function() {
				var $next_li = $(this);
				if ($next_li.children('a.property').attr('title') > title) {
					$move_before = $next_li;
					return false;
				}
			});
			if (!$move_before) {
				$move_before = {length: 0};
			}
		}

		// effective move
		if ($move_before.length) {
			$li.insertBefore($move_before);
		}
		else {
			$li.appendTo($li.parent());
		}
	});

	//--------------------------------------------------------------------------- .property draggable
	$body.build('call', '.property', function()
	{
		var $properties = this.filter(':not(:has(ol,table,ul))')
		$properties.draggable({
			appendTo: 'body',
			cursorAt: { left: 10, top: 10 },
			scroll:   false,

			//---------------------------------------------------------------------------- draggable drag
			drag: function(event, ui)
			{
				var $this      = $(this);
				var $droppable = $this.data('over-droppable');
				if ($droppable !== undefined) {
					var callback  = $droppable.data('drag-callback');
					var droppable = $droppable.get(0);
					callback.call(droppable, event, ui);
				}
			},

			//-------------------------------------------------------------------------- draggable helper
			helper: function()
			{
				var $this = $(this);
				var property_name = $this.data('property')
					? $this.data('property')
					: $this.closest('[id]').attr('id');
				$this.closest('#column_select.popup').fadeOut(200);
				return $('<div>')
					.addClass('property')
					.css('white-space', 'nowrap')
					.css('z-index',     zIndexInc())
					.data('class',      $this.closest('article[data-class]').data('class'))
					.data('feature',    $this.closest('article[data-class]').data('feature'))
					.data('property',   property_name)
					.html($this.text());
			},

			//--------------------------------------------------------------------------- draggable start
			start: function()
			{
				var $this = $(this);
				if (!$this.hasClass('property')) {
					$this.addClass('property');
					$this.data('remove-property-class', true);
				}
			},

			//---------------------------------------------------------------------------- draggable stop
			stop: function(event, ui)
			{
				var $this = $(this);
				if ($this.data('will-remove-property')) {
					$this.removeClass('property');
					$this.removeData('remove-property-class');
				}
				if (!ui.helper.data('dropped')) {
					var drop_out_href = $this.data('drop-out-href');
					if (drop_out_href !== undefined) {
						var drop_out_target = $this.data('drop-out-target');
						if (drop_out_target === undefined) {
							drop_out_target = '#responses';
						}
						redirectLight(drop_out_href, drop_out_target);
					}
				}
			}

		});
	});

	//----------------------------------------------------------------- .property-select .auto-expand
	$body.build('call', '.property-select > ul.tree > li.auto-expand > a.expand', function()
	{
		var $anchors = this;
		setTimeout(function() { $anchors.click(); });
	});

	//-------------------------------------------------------------------------------- document click
	// hide popup select box when clicking outside of it
	$(document).click(function(event)
	{
		if (!event.pageX && !event.pageY) {
			return;
		}
		if (!$(event.target).closest('#column_select').length) {
			$('#column_select').fadeOut(200);
		}
	});

});
