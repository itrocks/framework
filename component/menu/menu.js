$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------------------- #menu .minimize
	$body.build('click', '#menu .minimize',function()
	{
		var $body  = $('body');
		var $input = $(this).parent().find('input');
		if ($body.hasClass('min-left')) {
			$body.removeClass('min-left');
			$input.keyup().focus();
		}
		else {
			$body.addClass('min-left');
			$input.keyup();
			$(this).blur();
		}
	});

	//------------------------------------------------------------------------------- #menu .selected
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

	//---------------------------------------------------------------------------------- #menu-filter
	$body.build('keyup', '#menu-filter', function()
	{
		var $input = $(this);
		var $menu  = $input.closest('#menu');
		var value  = $('body').hasClass('min-left') ? '' : $input.val();
		$menu.find('li:not(:visible)').show();
		if (!value.length) {
			return;
		}

		$menu.find('li > a').each(function() {
			var $a    = $(this);
			var $li   = $a.parent();
			var $h3_a = $li.parent().closest('li').find('> h3 > a');
			var is_visible = ($a.text().simple().indexOf(value.simple()) > -1)
				|| ($h3_a.text().simple().indexOf(value.simple()) > -1);
			if (!is_visible) {
				$li.hide();
			}
		});

		$menu.find('> ul > li').each(function() {
			var $li        = $(this);
			var is_visible = $li.find('> ul > li:visible').length;
			if (!is_visible) {
				$li.hide();
			}
		});
	});

});
