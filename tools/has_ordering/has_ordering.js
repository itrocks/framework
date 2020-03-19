$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------------------- refreshOrdering
	var refresh = function()
	{
		var ordering = 0;
		this.each(function() {
			ordering ++;
			$(this).find('li[data-property=ordering] input[name*="[ordering]"]').val(ordering);
		});
	};

	//------------------------------------------------------------------------ tr.new refreshOrdering
	$body.build('call', 'article > form ul.collection li[data-property=ordering]', function()
	{
		var $li = $(this);

		//----------------------------------------------------------------------------------- draggable
		$li.closest('li.data').draggable(
		{
			appendTo: function() { $(this).closest('ul.collection'); },
			handle:   'li[data-property=ordering]',

			//---------------------------------------------------------------------------- draggable drag
			drag: function(event)
			{
				var $moving      = $(this);
				var $collection  = $moving.closest('ul.collection');
				var $lines       = $collection.children('li:not(.header)');
				var mouse_y      = event.pageY;
				var after_moving = false;
				var shift        = $moving.data('shift');
				$collection.children('li.drop-after').removeClass('drop-after');
				if (mouse_y < $lines.offset().top) {
					$collection.children('li.header:last').addClass('drop-after');
					return;
				}
				$lines.each(function() {
					var $line  = $(this);
					if ($line.is($moving)) {
						after_moving = true;
						return; // continue
					}
					var top    = $line.offset().top;
					var $next  = $line.next().is($moving) ? $line.next().next() : $line.next();
					var bottom = $next.length ? $next.offset().top : (top + $line.height());
					if (mouse_y < top) {
						$line.css('top', (after_moving ? 0 : shift).toString() + 'px');
						return;
					}
					if (mouse_y > bottom) {
						$line.css('top', (after_moving ? -shift : 0).toString() + 'px');
						return;
					}
					var middle = (top + bottom) / 2;
					if (mouse_y < middle) {
						$line.css('top', (after_moving ? 0 : shift).toString() + 'px');
						var $previous = $line.prev().is($moving) ? $line.prev().prev() : $line.prev();
						$previous.addClass('drop-after');
					}
					else {
						$line.css('top', (after_moving ? -shift : 0).toString() + 'px');
						$line.addClass('drop-after');
					}
				});
				if (!$collection.children('li.drop-after').length) {
					$collection.children('li:last').addClass('drop-after');
				}
			},

			//------------------------------------------------------------------------------------- start
			start: function()
			{
				var $moving = $(this);
				var $next   = $moving.next();
				var before  = $moving.offset().top;
				var after   = $next.length ? $next.offset().top : (before + $moving.height());
				$moving.data('shift', after - before);
			},

			//---------------------------------------------------------------------------- draggable stop
			stop: function()
			{
				var $moving     = $(this);
				var $collection = $moving.closest('ul.collection');
				var $lines      = $collection.children('li');
				$moving.insertAfter($collection.children('li.drop-after'));
				$collection.children('li.drop-after').removeClass('drop-after');
				$lines.css({ left: '', top: '' });
				refresh.call($collection.children('li.data'));
			}
		});

		//---------------------------------------------------------------------- ul.collection sortable
		$li.closest('ul.collection').each(function()
		{
			var $collection = $(this);
			refresh.call($collection.children('li.data'));

			if (!$collection.data('sortable')) {
				$collection.droppable({ accept: 'li[data-property=ordering]', tolerance: 'touch' });
			}
		});
	});

});
