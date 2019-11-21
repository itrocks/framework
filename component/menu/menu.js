$(document).ready(function()
{
	var $body = $('body');

	//--------------------------------------------------------------------------------- #menu animate
	var ignore = false;

	var animate = function(expand)
	{
		var $body   = $('body');
		var $button = $(this);
		var random  = Math.random().toString(36).substr(2, 9);
		var side    = (($body.hasClass('min-left') === expand) ? 'expand' : 'reduce');
		var image   = app.project_uri + '/itrocks/framework/skins/default/img/menu-24-' + side + '.svg'
			+ '?' + random;
		$('<img alt="image" src="' + image + '">').on('load', function() {
			$button.css('background-image', 'url(' + Q + image + Q + ')');
		});
	};

	$body.build('call', '#menu .minimize', function()
	{
		var $button = $(this);

		$button.mouseenter(function()
		{
			ignore ? (ignore = false) : animate.call(this, true);
		});

		$button.mouseout(function()
		{
			ignore ? (ignore = false) : animate.call(this, false);
		});
	});

	//------------------------------------------------------------------------------- #menu .minimize
	$body.build('click', '#menu .minimize',function()
	{
		var $button = $(this);
		var $body   = $('body');
		var $input  = $button.parent().find('input');
		if ($body.hasClass('min-left')) {
			$body.removeClass('min-left');
			$input.keyup().focus();
		}
		else {
			$body.addClass('min-left');
			$input.keyup();
			$button.blur();
		}
		$button.mouseenter();
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

	//------------------------------------------------------------------------------ #menu mousewheel
	$body.build('mousewheel', '#menu', function(event)
	{
		var $items = $(this).children('ul');
		$items.scrollTop($items.scrollTop() - (event.deltaFactor * event.deltaY));
	});

});
