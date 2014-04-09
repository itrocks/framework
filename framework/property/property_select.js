$('document').ready(function()
{

	$('body').build(function()
	{
		// sort and decoration
		this.in('ul.property_tree').sortcontent();
		this.in('.property_select').prepend($('<span>').addClass('joint'));

		// create tree
		this.in('ul.property_tree>li a').click(function(event)
		{
			var $this = $(this);
			var $li = $(this).closest('li');
			if ($li.children('section').length) {
				if ($li.children('section:visible').length) {
					$this.removeClass('expanded');
					$li.children('section:visible').hide();
				}
				else {
					$this.addClass('expanded');
					$li.children('section:not(:visible)').show();
				}
				event.stopImmediatePropagation();
				event.preventDefault();
			}
			else {
				$this.addClass('expanded');
			}
		});

		// draggable items
		this.in('.property, fieldset>div[id]>label').draggable({
			appendTo:    'body',
			containment: 'body',
			cursorAt:    { left: 10, top: 10 },
			delay:       500,
			scroll:      false,

			helper: function()
			{
				var $this = $(this);
				return $('<div>')
					.addClass('property')
					.attr('id', $this.closest('.window').attr('id') + '/Property/' + $this.attr('id'))
					.css('z-index', ++zindex_counter)
					.html($this.text());
			},

			drag: function(event, ui)
			{
				var $droppable = $(this).data('over-droppable');
				if ($droppable != undefined) {
					var draggable_left = ui.offset.left;
					var count = 0;
					var found = 0;
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
									$droppable.find('colgroup>col:nth-child(' + old + ')').removeClass('insert_after');
								}
								if (found > 1) {
									$droppable.find('colgroup>col:nth-child(' + found + ')').addClass('insert_after');
									$droppable.data('insert-after', found);
								}
							}
						}
					});
				}
			}

		});

	});

});
