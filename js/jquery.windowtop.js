$(document).ready(function()
{

	//------------------------------------------------------------------------------------- windowFix
	var windowFix = function($window, $top)
	{
		var $element      = $top;
		var $next_element = $element;
		var margin_top    = parseInt($top.css('margin-top'));
		var top           = 0;
		do {
			$element      = $next_element;
			$next_element = $element.next();
			var height = $element.height()
				+ parseInt($element.css('border-top-width'))
				+ parseInt($element.css('border-bottom-width'))
				+ Math.max(
					parseInt($element.css('margin-bottom')),
					parseInt($next_element.css('margin-top'))
				);
			var style = $element.attr('style');
			$element
				.addClass('fixed')
				.data('stay-top-style', (style === undefined) ? '' : style)
				.css({
					height:    $element.height(),
					position:  'fixed',
					top:       top,
					width:     $element.width(),

					'margin-bottom': 0,
					'margin-top':    0,
					'z-index':       window.zindex_counter + 2000
				})
				.data('stay-top', top);
			top += height;
		}
		while ($next_element.length && $next_element.is('.general.actions, .global-settings'));
		$top.after($('<div>').addClass('fixed stay-top').css({
			background:      'white',
			height:          top - $top.height(),
			position:        'fixed',
			top:             $top.height(),
			width:           $top.width(),
			'border-bottom': '1px solid darkgray',
			'z-index':       window.zindex_counter + 1000
		}).data('stay-top', $top.height()));
		$element.after($('<div>').addClass('stay-top').css({ height: top + margin_top }));
		$top
			.css({ 'border-top-left-radius': 0, 'border-top-right-radius': 0 })
			.data('stay-top-bottom', top);
	};

	//----------------------------------------------------------------------------------- windowPlace
	var windowPlace = function($window, $top)
	{
		$window.children('.fixed').each(function() {
			$(this).css(
				'left',
				$window.offset().left + parseInt($window.css('padding-left')) - window.scrollbar.left()
			);
		});

		var max_top = $window.offset().top + $window.height()
			+ parseInt($window.css('border-top-width'))
			+ parseInt($window.css('border-bottom-width'))
			+ parseInt($window.css('padding-bottom'))
			- window.scrollbar.top();
		top = $top.data('stay-top-bottom');

		if (top > max_top) {
			var diff = top - max_top;
			$top.data('stay-top-diff', diff);
			$window.children('.fixed').each(function() {
				var $element = $(this);
				$element.css('top', $element.data('stay-top') - diff);
			});
		}
		else if ($top.data('stay-top-diff')) {
			$top.removeData('stay-top-diff');
			$window.children('.fixed').each(function() {
				var $element = $(this);
				$element.css('top', $element.data('stay-top'));
			});
		}
	};

	//--------------------------------------------------------------------------------- windowUnFix
	var windowUnFix = function($window, $top)
	{
		$window.children('.stay-top').remove();
		var $fixed = $window.children('.fixed');
		$fixed.each(function() {
			var $element = $(this);
			$element.attr('style', $element.data('stay-top-style'));
		});
		$fixed.removeClass('fixed')
			.removeData('stay-top-style')
			.removeData('stay-top');
		$top.removeData('stay-top-bottom');
	};

	//------------------------------------------------------------------------------------- windowTop
	var windowTop = function()
	{
		$('article > header > h2:first-child').each(function()
		{
			var $top    = $(this);
			var $window = $top.parent();
			if ($window.offset().top < window.scrollbar.top()) {
				if (!$top.hasClass('fixed')) {
					windowFix($window, $top);
				}
				windowPlace($window, $top);
			}
			else if ($top.hasClass('fixed')) {
				windowUnFix($window, $top);
			}
		});

	};

	//--------------------------------------------------------------------- $(window) resize + scroll
	$(window).resize(windowTop).scroll(windowTop);

});
