$(document).ready(function()
{
	const $body = $('body')

	//--------------------------------------------------------- header > .top.bar > button.menu click
	/**
	 * Click on the "menu" button shows / hides the menu
	 */
	$body.build('click', 'header > .top.bar > button.menu', function()
	{
		const $menu = $('nav#menu')
		if ($menu.is(':visible')) {
			$menu.css('display', '')
		}
		else {
			$menu.css('display', 'block')
		}
	})

	//----------------------------------------------------------------------------- article hide menu
	/**
	 * Each time an article is loaded, ensure that the menu becomes invisible
	 */
	$body.build('each', 'article', function()
	{
		const $menu = $('nav#menu')
		if ($menu.is(':visible')) {
			$menu.css('display', '')
		}
	})

	//--------------------------------------------------------------------------------- window.resize
	/**
	 * hide responsive menu each time the window is resized,
	 * to avoid bugs when switching from "smartphone view with menu" to "PC view"
	 */
	$(window).resize(function()
	{
		const $menu = $('nav#menu')
		if ($menu.is(':visible')) {
			$menu.css('display', '')
		}
	})

})
