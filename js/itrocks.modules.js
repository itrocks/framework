$(document).ready(function()
{

	$('body').build('each', 'article[data-class]', function()
	{
		var $article    = $(this);
		var $body       = $('body');
		var $found      = null;
		var $menu       = $('#menu');
		var found       = '';
		var found_count = 0;
		var path        = app.uri_base + SL + $article.data('class').repl(BS, SL);
		// copy the article class and data-class into the body
		var classes        = $article.attr('class');
		var data_class     = $article.data('class');
		var is_parent_main = $article.parent('main').length;
		if (is_parent_main) {
			if ($body.hasClass('min-left')) {
				classes += ' min-left';
			}
			$body.attr('class', classes);
			$body.attr('data-class', data_class);
		}
		// find the link into the menu which path matches the article data-class path best
		do {
			$menu.find('li > a[href]').each(function () {
				var $a    = $(this);
				var check = $a.attr('href');
				var check_length;
				while (check && !path.startsWith(check)) {
					check = check.lLastParse(SL, 1, false);
				}
				if (check && ((check_length = check.split(SL).length) > found_count)) {
					$found      = $a;
					found       = check;
					found_count = check_length;
				}
			});
			if (found_count) {
				break;
			}
			path = path.lLastParse(SL, '', false);
		}
		while (path);
		if (!$found) {
			return;
		}
		// set article module
		var $item   = $found.parent();
		var $module = $item.parent().parent();
		var module  = $module.attr('id');
		$article.attr('data-module', module);
		if (!is_parent_main) {
			return;
		}
		// set body module and class, select menu module and item
		$body.attr('data-module', module);
		$menu.find('.selected').removeClass('selected');
		$item.addClass('selected');
		$module.addClass('selected');
		// set current favorite class, feature, module, href, text
		var $favorites_current = $('#favorites > .current');
		if ($favorites_current.data('setCurrent')) {
			$favorites_current.data('setCurrent').call($favorites_current, $article, module);
		}
		else {
			setTimeout(function() {
				$favorites_current.data('setCurrent').call($favorites_current, $article, module);
			});
		}
	});

});
