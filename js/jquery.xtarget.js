
var more_request_headers = {};

//---------------------------------------------------------------------------------- requestHeaders
/**
 * Add 'beforeSend: requestHeaders' to your ajax calls to add information about the client
 *
 * @param request
 */
var requestHeaders = function(request)
{
	request.setRequestHeader('screen-height', screen.height);
	request.setRequestHeader('screen-width',  screen.width);
	request.setRequestHeader('window-height', $(window).height());
	request.setRequestHeader('window-width',  $(window).width());
	for (var header in more_request_headers) if (more_request_headers.hasOwnProperty(header)) {
		request.setRequestHeader(header, more_request_headers[header]);
	}
	more_request_headers = {};
};

(function($)
{

	//------------------------------------------------------------------------------------- writeHtml
	/**
	 * Write multi-target HTML data into multiple targets
	 *
	 * @example
	 *   writeHtml('Some text <!--target #another-id-->and other<!--end--> continues', $('#main'))
	 * equiv :
	 *   $('#another-id').html('and other');
	 *   $('#main').html('Some text  continues');
	 * @example
	 *   writeHtml('<!--target #another-id-->and other<!--end-->', $('#main'))
	 * equiv :
	 *   $('#another-id').html('and other');
	 *   $('#main').html('');
	 * @param data         string
	 * @param $main_target jQuery
	 * @return jQuery[] targets
	 */
	var writeHtml = function(data, $main_target)
	{
		var $targets = $main_target;
		var target_position = 0;
		while ((target_position = data.indexOf('<!--target ', target_position)) > -1) {
			var target_data_position = target_position + 11;
			var target_end_position  = data.indexOf('-->', target_data_position);
			var target               = data.substring(target_data_position, target_end_position);
			var $target              = $(target);
			target_data_position     = target_end_position + 3;
			target_end_position      = data.indexOf('<!--end-->', target_data_position);
			var target_data          = data.substring(target_data_position, target_end_position);
			target_end_position     += 10;
			$target.html(target_data);
			data     = (data.substr(0, target_position) + data.substr(target_end_position)).trim();
			$targets = $targets.add($target);
		}
		$main_target.html(data);
		return $targets.add($main_target);
	};

	/**
	 * Allow your pages to contain implicit ajax calls, using the power of selector targets
	 *
	 * - Works with <a> and <form> links
	 * - Initialise this feature with a single $('body').xtarget(); call
	 *
	 * @example
	 * <div id="position"></div>
	 * <a href="linked_page" target="#position">click to load linked page content into position</a>
	 *
	 * @example
	 * <div id="position"></div>
	 * <form action="linked_page" target="#position">(...)</form>
	 *
	 * TODO HIGHEST Not SOLID anymore, because of form validity checks : remove them from here ! do compatibility like with build() !
	 */
	$.fn.xtarget = function(options)
	{

		var last_history_entry;

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			auto_empty:        {}, // { 'target-selector': 'zone(s)-to-empty-selector' }
			auto_empty_except: undefined, // string : if set, selector for exception original link
			closeable_popup:   'popup',
			draggable_blank:   undefined,
			error:             undefined,
			history:           false, // { condition, popup, post, title }
			keep:              'popup',
			popup_element:     'div',
			show:              undefined,
			submit:            'submit',
			success:           undefined,
			track:             true,
			url_append:        '',
			xtarget_from:      'xtarget.from'
		}, options);

		//---------------------------------------------------------------------------------------- ajax
		var ajax = {

			//------------------------------------------------------------------------------- ajax.target
			target: undefined,

			//----------------------------------------------------------------------------- ajax.complete
			complete: function(xhr)
			{
				clearTimeout(xhr.time_out);
				$('body').css({ cursor: 'auto' });
			},

			//-------------------------------------------------------------------------------- ajax.error
			error: function(xhr, status, error)
			{
				if (settings.error !== undefined) {
					settings.error(xhr, status, error);
				}
			},

			//-------------------------------------------------------------------------- ajax.pushHistory
			/**
			 * @param xhr     object
			 * @param $target jQuery
			 */
			pushHistory: function(xhr, $target)
			{
				if ((settings.history.popup !== undefined) || !$target.hasClass('popup')) {
					var type = xhr.ajax.type;
					if (type === undefined) type = xhr.call_type;
					if (type === undefined) type = 'get';
					if (
						(settings.history.condition !== undefined)
						&& $target.find(settings.history.condition).length
						&& (settings.history.post || (type !== 'post'))
					) {
						var title;
						if ((settings.history.title !== undefined) && settings.history.title) {
							title = $target.find(settings.history.title).first().text();
							if (!title.length) {
								title = xhr.from.href;
							}
						}
						else {
							title = xhr.from.href;
						}
						document.title = title;

						var history_entry = xhr.from.href;
						if (history_entry !== undefined) {
							var history_push = true;
							for (var without_get_var in settings.history.without_get_vars) {
								if (settings.history.without_get_vars.hasOwnProperty(without_get_var)) {
									if (history_entry.match(settings.history.without_get_vars[without_get_var])) {
										history_push = false;
										break;
									}
								}
							}
							if (history_push && (history_entry !== last_history_entry)) {
								last_history_entry = xhr.from.href;
								window.history.pushState({reload: true}, title, history_entry);
							}
						}

					}
				}
			},

			//-------------------------------------------------------------------------------- ajax.popup
			/**
			 * @param $where jQuery
			 * @param id     string
			 * @return jQuery
			 */
			popup: function($where, id)
			{
				var $body = $('body');
				var $from = $where;
				var left  = $where.offset().left + 3;
				var top   = $where.offset().top + $where.height() + 2;
				if (id.substr(0, 1) === '_') {
					$where = $($body.children(':last-child'));
				}
				if (id === '_blank') {
					id = 'window' + ++window.id_index;
				}
				var $target = $('<' + settings.popup_element + '>')
					.addClass(settings.closeable_popup)
					.attr('id', id);
				if (settings.keep && $where.hasClass(settings.keep)) {
					$target.addClass(settings.keep);
				}
				$target.data(settings.xtarget_from, $from);
				$target.insertAfter($where.hasClass('popup') ? $body : $where);
				if (($where !== $from) || $where.hasClass('popup')) {
					$target.addClass('popup');
					if ($where.hasClass('right')) {
						$target.css('right', $(window).width() - (left + $where.width()));
					}
					else {
						$target.css('left', left);
					}
					$target.css('position', 'absolute');
					$target.css('top',      top);
					$target.css('z-index',  zIndex());
					if (settings.draggable_blank !== undefined) {
						if (settings.draggable_blank === true) {
							$target.draggable();
						}
						else {
							$target.draggable({ handle: settings.draggable_blank });
						}
					}
				}
				setTimeout(function() {
					var offset  = $target.offset();
					var $window = $(window);
					if ((offset.left + $target.outerWidth()) > $window.outerWidth()) {
						offset.left = Math.max(0, $window.outerWidth() - $target.outerWidth());
						$target.css({ left: '', right: 0 });
					}
					if ((offset.top + $target.outerHeight()) > $window.outerHeight()) {
						offset.top = Math.max(0, $window.outerHeight() - $target.outerHeight());
						$target.css('top', offset.top);
					}
					if (window.scrollbar !== undefined) {
						if (offset.left < window.scrollbar.left()) {
							$target.css({ left: window.scrollbar.left(), right: '' });
						}
						if (offset.top < window.scrollbar.top()) {
							$target.css('top', window.scrollbar.top());
						}
					}
				});
				return $target;
			},

			//------------------------------------------------------------------------------ ajax.success
			success: function(data, status, xhr)
			{
				var target       = xhr.from.target;
				var focus        = (xhr.from.href ? xhr.from.href : xhr.from.action).rParse('#');
				var $from        = $(xhr.from);
				var $target      = $(target);
				var build_target = false;
				if (target.endsWith('main') && !$target.length) {
					$target = $(target.beginsWith('#') ? 'main' : '#main');
				}
				// popup a new element
				if ($target.is('.' + settings.closeable_popup)) {
					$target.remove();
					$target = $(xhr.from.target);
				}
				if (!$target.length) {
					$target      = this.popup($from, xhr.from.target.substr(1));
					build_target = true;
				}
				$target = writeHtml(data, $target);
				if (settings.show && $target.filter(':not(:visible)').length) {
					$target.filter(':not(:visible)').show();
				}
				// auto empty
				if ((settings.auto_empty !== undefined) && !xhr.auto_empty_except) {
					for (var key in settings.auto_empty) if (settings.auto_empty.hasOwnProperty(key)) {
						var empty_target = settings.auto_empty[key];
						if (
							($target.filter(key).length || $(target).is(key))
							&& !$target.filter(empty_target).length
						) {
							$(empty_target).empty();
						}
					}
				}
				// track window position to focus, if set
				var $focus = focus.length ? $('#' + focus) : null;
				if ($focus && $focus.length) {
					if (
						($focus.offset().left < window.scrollbar.left())
						|| ($focus.offset().left > (window.scrollbar.left() + window.innerWidth))
					) {
						window.scrollbar.left($focus.offset().left);
					}
					if (
						($focus.offset().top < window.scrollbar.top())
						|| ($focus.offset().top > (window.scrollbar.top() + window.innerHeight))
					) {
						window.scrollbar.top($focus.offset().top);
					}
				}
				// track window position to target
				else if (
					settings.track && xhr.from.target.beginsWith('#') && (window.scrollbar !== undefined)
				) {
					$target.each(function() {
						var $target = $(this);
						if ($target.offset().left < window.scrollbar.left()) {
							window.scrollbar.left($target.offset().left);
						}
						if ($target.offset().top < window.scrollbar.top()) {
							window.scrollbar.top($target.offset().top);
						}
					});
				}
				// change browser's URL and title, push URL into history
				if (settings.history !== undefined) {
					this.pushHistory(xhr, $target);
				}
				// If build plugin is active : build loaded DOM
				if ($target.build !== undefined) {
					if (build_target) {
						$target.build();
					}
					else {
						$target.children().build();
					}
				}
				// on success callbacks
				target = $target.last()[0];
				if (settings.success !== undefined) {
					settings.success.call(target, data, status, xhr);
				}
				var on_success = $from.data('on-success');
				if (on_success !== undefined) {
					on_success.call(target, data, status, xhr);
				}
			}
		};

		//----------------------------------------------------------------------- hasFormReportValidity
		/**
		 * Returns true if browser manage checkValidity
		 *
		 * @return boolean
		 */
		var hasFormCheckValidity = function()
		{
			return (typeof document.createElement('form').checkValidity) === 'function';
		};

		//----------------------------------------------------------------------- hasFormReportValidity
		/**
		 * Returns true if browser manage reportValidity
		 *
		 * @return boolean
		 */
		var hasFormReportValidity = function()
		{
			return (typeof document.createElement('form').reportValidity) === 'function';
		};

		//------------------------------------------------------------------------------ reportValidity
		/**
		 * Reports the validity of a form
		 *
		 * @param form HTMLFormElement
		 * @return boolean
		 */
		var reportValidity = function(form)
		{
			if (hasFormReportValidity()) {
				return form.reportValidity();
			}
			else if (hasFormCheckValidity()) {
				if (form.checkValidity()) {
					return true;
				}
				alert('Invalid data input');
				return false;
			}
			// No check method, fallback to default behaviour
			return true;
		};

		//----------------------------------------------------------------------------------- urlAppend
		/**
		 * Append the url_append setting to the url
		 *
		 * @param url    string the url
		 * @param search string the '?var=value&var2=value2' part of the url, if set
		 * @return string
		 */
		var urlAppend = function(url, search)
		{
			if (settings.url_append) {
				url = url.lParse('#')
					+ (search ? '&' : '?')
					+ settings.url_append
					+ ((url.indexOf('#') >= 0) ? '#' : '') + url.rParse('#');
			}
			return url;
		};

		//------------------------------------------------------------------- $('a[target^='#']').click
		/**
		 * <a> with target '#*' are ajax calls
		 *
		 * If the a element is inside a form and the a class 'submit' is set, the link submits the form with the a href attribute as action
		 */
		this.find('a[target^="#"]').add(this.filter('a[target^="#"]')).click(function(event)
		{
			if (event.which !== 2) {
				var anchor        = this;
				var is_javascript = (anchor.href.substr(0, 11) === 'javascript:');
				event.preventDefault();
				var executeClick = function() {
					// ensures that pending messages (blur on a combo) resolve before click
					if (window.running_combo !== undefined) {
						setTimeout(executeClick);
						return;
					}
					if (is_javascript) {
						eval(anchor.href.substr(11));
						return;
					}
					var $anchor = $(anchor);
					var jax;
					var target = $anchor.attr('target');
					var xhr    = undefined;
					if (target.beginsWith('#')) {
						var $target;
						if ((target === '#main') && !($target = $(target)).length) {
							$target = $('main');
						}
						more_request_headers['target-height'] = $target.height();
						more_request_headers['target-width']  = $target.width();
					}
					if ($anchor.hasClass(settings.submit)) {
						var $parent_form = $anchor.closest('form');
						if ($parent_form.length) {
							/*
							TODO: 97556 Rework required properties first
							if (!reportValidity($parent_form[0])) {
								return;
							}
							*/
							if ($parent_form.ajaxSubmit !== undefined) {
								$parent_form.ajaxSubmit(jax = $.extend(ajax, {
									type: $parent_form.attr('type'),
									url:  urlAppend(anchor.href, anchor.search)
								}));
								xhr = $parent_form.data('jqxhr');
							}
							else {
								xhr = $.ajax(jax = $.extend(ajax, {
									beforeSend: requestHeaders,
									data: $parent_form.serialize(),
									type: $parent_form.attr('method'),
									url:  urlAppend(anchor.href, anchor.search)
								}));
							}
							xhr.call_type = $parent_form.attr('method');
						}
					}
					else if ($anchor.data('post')) {
						xhr = $.ajax(jax = $.extend(ajax, {
							beforeSend: requestHeaders,
							data: $anchor.data('post'),
							type: 'post',
							url:  urlAppend(anchor.href, anchor.search)
						}));
					}
					if (!xhr) {
						xhr = $.ajax(jax = $.extend(ajax, {
							beforeSend: requestHeaders,
							url: urlAppend(anchor.href, anchor.search)
						}));
					}
					xhr.ajax     = jax;
					xhr.from     = anchor;
					xhr.mouse_x  = (document.mouse === undefined) ? event.pageX : document.mouse.x;
					xhr.mouse_y  = (document.mouse === undefined) ? event.pageY : document.mouse.y;
					xhr.time_out = setTimeout(function() { $('body').css({ cursor: 'wait' }); }, 500);
					xhr.auto_empty_except = (settings.auto_empty_except !== undefined)
						&& $anchor.is(settings.auto_empty_except);
				};
				executeClick();
				if (is_javascript) {
					return false;
				}
			}
		});

		//---------------------------------------------------------------- $('form[target^='#']').click
		/**
		 * <form> with target '#*' are ajax calls
		 */
		this.find('form[target^="#"]').add(this.filter('form[target^="#"]')).submit(function(event)
		{
			var form  = this;
			var $form = $(form);
			var jax;
			var xhr;
			event.preventDefault();
			var executeClick = function() {
				// ensures that pending messages (blur on a combo) resolve before click
				if (window.running_combo !== undefined) {
					setTimeout(executeClick);
					return;
				}
				if ($form.ajaxSubmit !== undefined) {
					$form.ajaxSubmit(jax = $.extend(ajax, {
						type: $form.attr('type'),
						url:  urlAppend(form.action, form.action.indexOf('?') > -1)
					}));
					xhr = $form.data('jqxhr');
				}
				else {
					xhr = $.ajax(jax = $.extend(ajax, {
						beforeSend: requestHeaders,
						data: $form.serialize(),
						type: $form.attr('method'),
						url:  urlAppend(form.action, form.action.indexOf('?') > -1)
					}));
				}
				xhr.ajax     = jax;
				xhr.from     = form;
				xhr.time_out = setTimeout(function() { $('body').css({ cursor: 'wait' }); }, 500);
			};
			executeClick();
		});

		//--------------------------------------------------------------------------- window onpopstate
		if ((settings.history !== undefined) && (settings.history.condition !== undefined)) {
			$(window).bind('popstate', function(event)
			{
				if (
					(event.originalEvent.state !== undefined)
					&& event.originalEvent.state
					&& (event.originalEvent.state.reload !== undefined)
					&& event.originalEvent.state.reload
				) {
					document.location.reload();
				}
			});
		}

		return this;
	};

})( jQuery );
