$('document').ready(function()
{

	$('body').build(function()
	{
		if (!this.length) return;

		// sort and decoration
		this.inside('ul.property_tree').sortContent('.separator');
		this.inside('.property_select').prepend($('<span>').addClass('joint'));

		//------------------------------------------------- .property_select > input[name=search] keyup
		// search
		this.inside('.property_select > input[name=search]').each(function()
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
								+ '/' + $this.closest('[data-class]').data('class').replace('/', '\\')
								+ '?search=' + encodeURI(new_search)
								+ '&as_widget' + window.app.andSID(),
							{
								success: function(data) {
									var $property_tree = $this.parent().children('.property_tree');
									search_step        = 2;
									$property_tree.html(data);
									$property_tree.build();
								}
							}
						);

						var retry = function()
						{
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

		//------------------------------------------------------------- ul.property_tree > li > a click
		// create tree
		this.inside('ul.property_tree > li > a').click(function(event)
		{
			var $this = $(this);
			var $li   = $this.closest('li');
			if ($li.children('section').length) {
				if ($li.children('section:visible').length) {
					$this.removeClass('expanded');
					$li.children('section:visible').hide();
				}
				else {
					$this.addClass('expanded');
					$li.children('section:not(:visible)').show();
				}
				event.preventDefault();
				event.stopImmediatePropagation();
			}
			else {
				$this.addClass('expanded');
			}
		});

		//-------------------------------------------- .property, .fieldset > div[id] > label draggable
		// draggable items
		this.inside('.property, fieldset > div[id] > label').draggable(
		{
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
					.css('z-index',     ++zindex_counter)
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
			stop: function()
			{
				var $this = $(this);
				if ($this.data('will-remove-property')) {
					$this.removeClass('property');
					$this.removeData('remove-property-class');
				}
			}

		});

	});

	//-------------------------------------------------------------------------------- document click
	// hide popup select box when clicking outside of it
	$(document).click(function(event)
	{
		//noinspection JSJQueryEfficiency well, why ?
		var $column_select = $('#column_select.popup > .property_select');
		if ($column_select.length) {
			var offset = $column_select.offset();
			if (!(
				(event.pageX > offset.left) && (event.pageX < (offset.left + $column_select.width()))
				&& (event.pageY > offset.top) && (event.pageY < (offset.top + $column_select.height()))
			)) {
				$column_select.parent().fadeOut(200);
			}
		}
	});

});
