$(document).ready(function()
{
	let $body = $('body')
	let article_header = 'article[data-class] > form > header, article[data-class] > header'

	//------------------------------------------------------------- article[data-class] > header > h2
	/**
	 * Show / hide 'load article' popup
	 */
	$body.build('click', [article_header, '> h2'], function()
	{
		let $this = $(this)
		if ($this.data('stop-click')) {
			$this.data('stop-click', '')
			return
		}
		let $select = $this.parent().find('ul.select')
		if ($select.is(':visible')) {
			$('body').click()
		}
		else {
			$select.css('left', $this.position().left.toString() + 'px')
			$select.css('min-width', $this.width().toString() + 'px')
			$select.fadeIn(200, function() {
				let clickEvent = function() {
					$('body').off('click', clickEvent)
					$select.fadeOut(200)
				}
				$('body').on('click', clickEvent)
			}).css('display', 'block')
		}
	})

	$body.build('call', [article_header, '.custom.select li[data-class][data-id]'], function()
	{
		this.draggable({
			appendTo:    'body',
			containment: 'body',
			cursorAt:    { left: -10, top: -10 },
			scroll:      false,

			drag: function(event, ui)
			{
				let $helper    = $(ui.helper)
				let $inside    = undefined
				$('article, nav#menu, #notifications > .drop-on > li').each(function() {
					let $element  = $(this)
					let offset    = $element.offset()
					offset.right  = offset.left + $element.width()
					offset.bottom = offset.top + $element.height()
					if (
						(event.pageX >= offset.left) && (event.pageX < offset.right)
						&& (event.pageY >= offset.top) && (event.pageY < offset.bottom)
					) {
						$inside = $element
						return false
					}
				})
				if (
					$inside
					&& (
						!$helper.data('$inside')
						|| ($helper.data('$inside').attr('class') !== $inside.attr('class'))
					)
				) {
					if ($helper.data('$inside')) {
						$helper.removeClass('inside-' + $helper.data('$inside').attr('class'))
					}
					$helper
						.addClass('inside')
						.addClass('inside-' + $inside.attr('class'))
						.removeClass('outside')
					$helper.data('$inside', $inside)
				}
				else if ($helper.data('$inside') && !$inside) {
					$helper
						.addClass('outside')
						.removeClass('inside')
						.removeClass('inside-' + $helper.data('$inside').attr('class'))
					$helper.removeData('$inside')
				}
				$helper.fadeIn(200);
			},

			start: function()
			{
				$(this).dropOn({
					id:    'custom',
					zones: [{ action: 'delete', text: tr('remove') + SP + tr('custom list') }]
				})
			},

			stop: function(event, ui)
			{
				$(this).dropOn('stop')
				let $inside = ui.helper.data('$inside')
				if ($inside && $inside.is('article')) {
					$(this).find('a').click()
				}
				$('body').click()
			},

			helper: function()
			{
				let $this = $(this)
				return $('<div class="custom select helper">')
					.css('z-index', zIndexInc())
					.text($this.text())
			}

		})
	})

	//------------------------------------------------------------------------------------ li.deleted
	let selector = '#responses > li.deleted[data-class="ITRocks\\\\Framework\\\\Setting"][data-id]'
	$body.build('call', selector, function()
	{
		let id = $(this).data('id')
		$('ul.custom.select > li[data-class="ITRocks\\\\Framework\\\\Setting"][data-id=' + id + ']')
			.remove()
	})

	//------------------------------------------------------------------- ul.custom.select mousewheel
	$body.build('mousewheel', 'ul.custom.select', function(event)
	{
		let $custom = $(this)
		$custom.scrollTop($custom.scrollTop() - (event.deltaFactor * event.deltaY))
	})

	//----------------------------------------------------------------------- ul.custom.select resize
	/**
	 * this: jQuery an unique 'ul.custom.select' element
	 */
	let resize = function()
	{
		let $custom = $(this)
		let $window = $(window)
		let top     = $custom.scrollTop()
		$custom.css('height', '')
		if ($custom.offset().top + $custom.height() > $window.height()) {
			$custom.css(
				'height',
				($window.height() - $custom.offset().top).toString() + 'px'
			)
			$custom.scrollTop(top)
		}
	}
	$body.build('each', 'ul.custom.select', resize)

	//--------------------------------------------------------------------------------- window resize
	$(window).resize(function()
	{
		$('ul.custom.select').each(function() {
			let $custom = $(this)
			resize.call($custom)
		})
	})

})
