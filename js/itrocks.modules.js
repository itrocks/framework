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
		var path        = $article.data('class');
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
			$menu.find('li[data-class]').each(function () {
				var $item = $(this);
				var check = $item.data('class');
				var check_length;
				var check2 = check;
				while (check2 && !path.startsWith(check2) && !check2.startsWith(path)) {
					check2 = check2.lLastParse(SL, 1, false);
				}
				var path2 = path;
				while (path2 && !check.startsWith(path2) && !path2.startsWith(check)) {
					path2 = path2.lLastParse(SL, 1, false);
				}
				check = (path2.length < check2.length) ? check2 : path2;
				if (check && ((check_length = check.split(SL).length) > found_count)) {
					$found      = $item;
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
		var $item   = $found;
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
