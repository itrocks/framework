
//------------------------------------------------------------------------------- copyCssPropertyTo
copyCssPropertyTo = function(context, element)
{
	var tab = [
		'font-size',
		'font',
		'font-family',
		'font-size',
		'font-weight',
		'letter-spacing',
		'line-height',
		'border',
		'border-bottom-width',
		'border-left-width',
		'border-top-width',
		'border-right-width',
		'border-color',
		'margin',
		'margin-bottom',
		'margin-left',
		'margin-right',
		'margin-top',
		'text-rendering',
		'word-spacing',
		'word-wrap'
	];
	for (var i = 0; i < tab.length; i++) {
		element.css(tab[i], context.css(tab[i]));
	}
	element.css('width', context.css('width') ? context.css('width') : context.width() + 'px');
};

//-------------------------------------------------------------------------- dateFormatToDatepicker
dateFormatToDatepicker = function(text)
{
	return text.repl('d', 'dd').repl('m', 'mm').repl('Y', 'yy');
};

//------------------------------------------------------------------------------ getInputTextHeight
getInputTextHeight = function($context, additional_text)
{
	if (additional_text === undefined) {
		additional_text = '';
	}
	return getTextHeight($context, 0, additional_text);
};

//------------------------------------------------------------------------------- getInputTextWidth
getInputTextWidth = function($context, additional_text)
{
	if (additional_text === undefined) {
		additional_text = '';
	}
	return Math.max(40, getTextWidth($context, 0, additional_text));
};

//--------------------------------------------------------------------------- getTextContentAsArray
getTextContentAsArray = function($context, additional_text)
{
	if (additional_text === undefined) {
		additional_text = '';
	}
	var text = $context.is('div')
		? $context.html().repl('<br>', "\n").repl('<p>', "\n\n")
		: ($context.val() + additional_text);
	return text.repl('<', '&lt;').repl('>', '&gt;').split("\n");
};

//----------------------------------------------------------------------------------- getTextHeight
getTextHeight = function($context, extra_height, additional_text)
{
	if (additional_text === undefined) {
		additional_text = '';
	}
	var content = getTextContentAsArray($context, additional_text);
	// If the last element is empty, need put a character to prevent the browser ignores the character
	var $last_index = content.length -1;
	if (!content[$last_index]) {
		content[$last_index] = 'X';
	}
	var $height = $('<div class="hidden">').html(content.join('<br>'));
	$height.appendTo($context.parent());
	copyCssPropertyTo($context, $height);
	$height.css('position', 'absolute');
	var $width = getInputTextWidth($context, additional_text);
	$height.width($width);
	var height = $height.height();
	if (extra_height !== undefined) {
		height += extra_height;
	}
	$height.remove();
	return height;
};

//------------------------------------------------------------------------------------ getTextWidth
var get_text_width_cache = [];
getTextWidth = function($context, extra_width, additional_text)
{
	if (additional_text === undefined) {
		additional_text = '';
	}
	if (get_text_width_cache.length > 1000) {
		get_text_width_cache = [];
	}
	var width = get_text_width_cache[$context.val() + additional_text];
	if (width !== undefined) {
		return width;
	}
	else {
		var content = getTextContentAsArray($context, additional_text);
		var $width  = $('<span>');
		$width.append(content.join('<br>')).appendTo('body');
		copyCssPropertyTo($context, $width);
		$width.css('position', 'absolute');
		var $pos = $context.position();
		$width.css('top', $pos.top);
		$width.css('left', $pos.left);
		width = $width.width() + extra_width;
		var $parent  = $context.parent();
		var $margins = parseInt($parent.css('margin-right'))
			+ parseInt($parent.css('padding-right'))
			+ parseInt($context.css('margin-right'));
		var ending_right_parent = ($(window).width() - ($parent.offset().left + $parent.outerWidth()));
		ending_right_parent += $margins;
		var ending_right = $(window).width() - ($width.offset().left + $width.outerWidth())
			- extra_width;
		if (ending_right < ending_right_parent) {
			width = width - (ending_right_parent - ending_right);
		}
		$width.remove();
		get_text_width_cache[$context.val() + additional_text] = width;
		return width;
	}
};

//---------------------------------------------------------------------------------------- redirect
/**
 * Load an URI into target
 *
 * @param uri      string
 * @param target   string|object jquery set object (object) or selector (string)
 * @param after    string|object jquery set object (object) or selector (string)
 * @param callback call this function when target was loaded (parameter is $target)
 * @param history boolean
 */
redirect = function(uri, target, after, callback, history)
{
	//noinspection JSUnresolvedVariable
	var app = window.app;
	var more = (
		(typeof target !== 'object') && (target !== undefined) && (target !== '') && (target[0] === '#')
	) ? 'as_widget' : '';
	if (uri.substr(0, app.uri_base.length) !== app.uri_base) {
		uri = app.uri_base + uri;
	}
	if (!more) {
		window.location = app.addSID(uri);
	}
	else {
		var close_function;
		var $target = (target && (typeof target === 'object')) ? target : $(target);
		if (target && target.endsWith('main') && !$target.length) {
			$target = $(target.beginsWith('#') ? 'main' : '#main');
		}
		if (!$target.length) {
			var z_index = zIndexInc();
			var $after  = (after && (typeof after === 'object')) ? after : $(after);
			$target     = $('<div>')
				.addClass('popup')
				.attr('id', 'window' + ++window.id_index)
				.css('left',     $after.length ? $after.offset().left : 10)
				.css('position', 'absolute')
				.css('top',      $after.length ? ($after.offset().top + $after.outerHeight()) : 10)
				.css('z-index',  z_index)
				.appendTo('body');
			close_function = function(event)
			{
				event.preventDefault();
				event.stopImmediatePropagation();
				$(this).closest('div.popup').remove();
				if (uri.indexOf('fill_combo=') > -1) {
					var fill_combo = uri.rParse('fill_combo=').lParse('&');
					$('[name=' + DQ + fill_combo + DQ + ']').next().focus();
				}
			}
		}
		$.ajax({
			url:     app.addSID(app.askAnd(uri, more)),
			success: function(data) {
				var keep_scroll = new Keep_Scroll($target);
				keep_scroll.keep();
				$target.html(data);
				if (close_function) {
					// do it before build, in order to disable xtarget on .close button
					$target.find('.actions .close a').click(close_function);
					$target.find('a').each(function() {
						var $this = $(this);
						var href = $this.attr('href');
						if (!href.beginsWith('#')) {
							var close_link = app.askAnd(href, 'close=window' + window.id_index);
							$this.attr('href', close_link);
						}
					});
				}
				$target.build();
				keep_scroll.serve();
				if (!close_function) {
					var title = $target.find('h2').first().text();
					if (!title.length) {
						title = uri;
					}
					document.title = title;
					if ((history === undefined) || history) {
						window.history.pushState({reload: true}, title, uri);
					}
				}
				if ((callback !== undefined) && callback) {
					callback.call($target, $target);
				}
			}
		});
	}
};

//----------------------------------------------------------------------------------- redirectLight
/**
 * Load an URI into target
 * Light version, without history and can add condition.
 * Load only if target exist and condition is respected.
 * No history and no change title (used for refresh functions)
 *
 * @param uri       string        uri to load
 * @param target    string|object jquery set object or selector (string)
 * @param condition callable      callable function to check (not mandatory)
 */
redirectLight = function(uri, target, condition)
{
	//noinspection JSUnresolvedVariable
	var app  = window.app;
	var more = (
		(typeof target !== 'object') && (target !== undefined) && (target !== '') && (target[0] === '#')
	) ? (((uri.indexOf('?') > -1) ? '&' : '?') + 'as_widget') : '';
	if (uri.substr(0, app.uri_base.length) !== app.uri_base) {
		uri = app.uri_base + uri;
	}
	if (more) {
		var $target = (target && (typeof target === 'object')) ? target : $(target);
		if (target && target.endsWith('main') && !$target.length) {
			$target = $(target.beginsWith('#') ? 'main' : '#main');
		}
		$.ajax({
			url:     app.addSID(uri + more),
			success: function(data) {
				if (!condition || condition()) {
					var keep_scroll = new Keep_Scroll($target);
					keep_scroll.keep();
					$target.html(data).build();
					keep_scroll.serve();
				}
			}
		});
	}
};

//----------------------------------------------------------------------------------------- refresh
/**
 * Refresh a window embedding data-class, and optionally data-feature and/or data-id
 *
 * @example refresh('main'); refresh('#main'); refresh('#responses'); refresh($('main');
 * @param target string|jQuery
 */
refresh = function(target)
{
	var uri = refreshLink(target);
	if (!uri) {
		return;
	}
	$.ajax({
		url: app.askAnd(uri, 'as_widget'),
		success: function(data) {
			var $target     = refreshTarget(target);
			var keep_scroll = new Keep_Scroll($target);
			keep_scroll.keep();
			$target.html(data).build();
			keep_scroll.serve();
		}
	});
};

//------------------------------------------------------------------------------------- refreshLink
/**
 *
 * @param target
 * @return string
 */
refreshLink = function(target)
{
	var $target = refreshTarget(target);
	var $window = $target.children('[data-class]');
	if (!$window.length) {
		$window = $target.filter('[data-class]');
	}
	if (!$window.length) {
		return '';
	}
	var feature = $window.data('feature');
	var id      = $window.data('id');
	var uri     = SL + $window.data('class').repl(BS, SL);
	if (id) {
		uri += SL + id;
	}
	if (feature) {
		uri += SL + feature;
	}
	return app.addSID(app.uri_base + uri);
};

//----------------------------------------------------------------------------------- refreshTarget
/**
 * @param target string
 * @return jQuery
 */
refreshTarget = function(target)
{
	var $target = $(target);
	if (((typeof target) === 'string') && target.endsWith('main') && !$target.length) {
		$target = $(target.beginsWith('#') ? 'main' : '#main');
	}
	return $target;
};
