$(document).ready(function()
{
	const $body = $('body')

	//------------------------------------------------------------------------------- refreshOrdering
	const refresh = function()
	{
		let ordering = 0
		this.each(function() {
			ordering ++
			$(this).find('li[data-property=ordering] input[name*="[ordering]"]').attr('value', ordering)
		})
	}

	//------------------------------------------------------------------------ tr.new refreshOrdering
	$body.build('call', '.component-objects [data-property=ordering]', function()
	{
		const $property = this

		//----------------------------------------------------------------------------------- draggable
		$property.closest('.data').draggable(
		{
			appendTo: function() { $(this).closest('.collection'); },
			handle:   'li[data-property=ordering]',

			//---------------------------------------------------------------------------- draggable drag
			drag: function(event)
			{
				const $moving      = $(this)
				const $collection  = $moving.closest('ul, ol, table')
				const $lines       = $collection.find('> li:not(.head), > tbody > tr')
				const mouse_y      = event.pageY
				let   after_moving = false
				const shift        = $moving.data('shift')
				$collection.find('.drop-after').removeClass('drop-after')
				if (mouse_y < $lines.not($moving).offset().top) {
					$collection.find('> li.head:last, > thead > tr:last').addClass('drop-after')
					return
				}
				$lines.each(function() {
					const $line  = $(this)
					if ($line.is($moving)) {
						after_moving = true
						return; // continue
					}
					const top    = $line.offset().top
					const $next  = $line.next().is($moving) ? $line.next().next() : $line.next()
					const bottom = $next.length ? $next.offset().top : (top + $line.height())
					if (mouse_y < top) {
						$line.css('top', (after_moving ? 0 : shift).toString() + 'px')
						return
					}
					if (mouse_y > bottom) {
						$line.css('top', (after_moving ? -shift : 0).toString() + 'px')
						return
					}
					const middle = (top + bottom) / 2
					if (mouse_y < middle) {
						$line.css('top', (after_moving ? 0 : shift).toString() + 'px')
						const $previous = $line.prev().is($moving) ? $line.prev().prev() : $line.prev()
						$previous.addClass('drop-after')
					}
					else {
						$line.css('top', (after_moving ? -shift : 0).toString() + 'px')
						$line.addClass('drop-after')
					}
				})
				if (!$collection.find('.drop-after').length) {
					$collection.find('> li:last, > tbody > tr:last').addClass('drop-after')
				}
			},

			//------------------------------------------------------------------------------------- start
			start: function()
			{
				const $moving = $(this)
				const $next   = $moving.next()
				const before  = $moving.offset().top
				const after   = $next.length ? $next.offset().top : (before + $moving.height())
				$moving.data('shift', after - before)
			},

			//---------------------------------------------------------------------------- draggable stop
			stop: function()
			{
				const $moving     = $(this)
				const $collection = $moving.closest('ul, ol, table')
				const $lines      = $collection.find('> li, > * > tr')
				$moving.insertAfter($lines.filter('.drop-after'))
				$collection.find('.drop-after').removeClass('drop-after')
				$lines.css({ left: '', top: '' })
				refresh.call($collection.children('.data'))
			}
		})

		//---------------------------------------------------------------------- ul.collection sortable
		const $component_objects = $property.closest('.component-objects')
		$component_objects.children('ul, ol, table').each(function()
		{
			const $collection = $(this)
			refresh.call($collection.children('.data'))

			if (!$collection.data('sortable')) {
				$collection.droppable({ accept: '[data-property=ordering]', tolerance: 'touch' })
			}
		})
	})

})
