$(document).ready(function()
{

	$('body').build('click', ['nav#menu ul > li', '> a, > h3 > a'], function()
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
