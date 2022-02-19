$(document).ready(function()
{
	const $body = $('body')

	let ignore = false

	//--------------------------------------------------------------------------------- #menu animate
	const animate = function(expand)
	{
		const $body   = $('body')
		const $button = $(this)
		const random  = Math.random().toString(36).substr(2, 9)
		const side    = (($body.hasClass('min-left') === expand) ? 'expand' : 'reduce')
		const image = app.project_uri + '/itrocks/framework/skins/default/img/menu-24-' + side + '.svg'
			+ '?' + random
		$('<img alt="image" src="' + image + '">').on('load', function() {
			$button.css('background-image', 'url(' + Q + image + Q + ')')
		})
	}

	//-------------------------------------------------------------------------- #menu .minimize call
	$body.build('call', '#menu .minimize', function()
	{
		const $button = $(this)

		$button.mouseenter(function()
		{
			ignore ? (ignore = false) : animate.call(this, true)
		})

		$button.mouseout(function()
		{
			ignore ? (ignore = false) : animate.call(this, false)
		})
	})

	//------------------------------------------------------------------------- #menu .minimize click
	$body.build('click', '#menu .minimize',function()
	{
		const $button = $(this)
		const $body   = $('body')
		const $input  = $button.parent().find('input')
		if ($body.hasClass('min-left')) {
			$body.removeClass('min-left')
			$input.keyup().focus()
		}
		else {
			$body.addClass('min-left')
			$input.keyup()
			$button.blur()
		}
		$button.mouseenter()
	})

	//---------------------------------------------------------------------------------- #menu-filter
	$body.build('keyup', '#menu-filter', function()
	{
		const $input = $(this)
		const $menu  = $input.closest('#menu')
		const value  = $('body').hasClass('min-left') ? '' : $input.val()
		$menu.find('li:not(:visible)').show()
		if (!value.length) {
			return
		}
		const values = value.simple().split(',')
		for (const i in values) if (values.hasOwnProperty(i)) {
			values[i] = values[i].trim()
		}

		$menu.find('li > a').each(function() {
			const $a         = $(this)
			const $li        = $a.parent()
			const $h3_a      = $li.parent().closest('li').find('> h3 > a')
			let   is_visible = false
			const block_text = $h3_a.text().simple()
			const item_text  = $a.text().simple()
			for (const i in values) if (values.hasOwnProperty(i) && values[i].length) {
				const value = values[i]
				if ((item_text.indexOf(value) > -1) || (block_text.indexOf(value) > -1)) {
					is_visible = true
					break
				}
			}
			if (!is_visible) {
				$li.hide()
			}
		})

		$menu.find('> ul > li').each(function() {
			const $li        = $(this)
			const is_visible = $li.find('> ul > li:visible').length
			if (!is_visible) {
				$li.hide()
			}
		})
	})

	//------------------------------------------------------------------------------ #menu mousewheel
	$body.build('mousewheel', '#menu', function(event)
	{
		const $items = $(this).children('ul')
		// noinspection JSUnresolvedVariable event.deltaFactor exists
		$items.scrollTop($items.scrollTop() - (event.deltaFactor * event.deltaY))
	})

})
