$(document).ready(function()
{
	var $body = $('body');

	$body.build('click', '#menu .minimize',function()
	{
		var $body = $('body');
		$body.hasClass('min-left') ? $body.removeClass('min-left') : $body.addClass('min-left');
		$(this).blur();
	});

	$body.build('click', ['#menu ul > li', '> a, > h3 > a'], function()
	{
		var $anchor = $(this);
		var $nav    = $anchor.closest('nav');
		var $module = $anchor.closest('nav > ul > li');
		var $h3     = $module.children('h3');

		$nav.find('a, h3').removeClass('selected');
		$anchor.addClass('selected');
		$h3.addClass('selected');
	});

});
