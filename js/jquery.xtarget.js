(function($)
{

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
			auto_empty:      {}, // { 'target-selector': 'zone(s)-to-empty-selector' }
			auto_redirect:   '.auto-redirect',
			closeable_popup: 'closeable-popup',
			draggable_blank: undefined,
			error:           undefined,
			history:         false, // { condition, popup, post, title }
			keep:            'popup',
			popup_element:   'div',
			show:            undefined,
			submit:          'submit',
			success:         undefined,
			track:           true,
			url_append:      '',
			xtarget_from:    'xtarget.from'
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
					if (
						(settings.history.condition !== undefined)
						&& $target.find(settings.history.condition).length
						&& (
							(settings.history.post !== undefined)
							|| (xhr.ajax.type === undefined) || (xhr.ajax.type.toLowerCase() !== 'post')
							|| (xhr.ajax.data === undefined) || !xhr.ajax.data.length
						)
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
				var $from = $where;
				var left  = $where.offset().left + 3;
				var top   = $where.offset().top + $where.height() + 2;
				if (id.substr(0, 1) === '_') {
					$where = $($('body').children(':last-child'));
				}
				if (id === '_blank') {
					id = 'window' + ++window.zindex_counter;
				}
				var $target = $('<' + settings.popup_element + '>')
					.addClass(settings.closeable_popup)
					.attr('id', id);
				if (settings.keep && $where.hasClass(settings.keep)) {
					$target.addClass(settings.keep);
				}
				$target.data(settings.xtarget_from, $from);
				$target.insertAfter($where);
				if ($where !== $from) {
					$target.addClass('popup');
					$target.css('left',     left);
					$target.css('position', 'absolute');
					$target.css('top',      top);
					$target.css('z-index',  window.zindex_counter);
					if (settings.draggable_blank !== undefined) {
						if (settings.draggable_blank === true) {
							$target.draggable();
						}
						else {
							$target.draggable({ handle: settings.draggable_blank });
						}
					}
				}
				setTimeout(function () {
					var offset  = $target.offset();
					var $window = $(window);
					if ((offset.left + $target.outerWidth()) > $window.outerWidth()) {
						offset.left = Math.max(0, $window.outerWidth() - $target.outerWidth());
						$target.css('left', offset.left);
					}
					if ((offset.top + $target.outerHeight()) > $window.outerHeight()) {
						offset.top = Math.max(0, $window.outerHeight() - $target.outerHeight());
						$target.css('top', offset.top);
					}
					if (window.scrollbar !== undefined) {
						if (offset.left < window.scrollbar.left()) {
							$target.css('left', window.scrollbar.left());
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
				// write result into destination element, and build jquery active contents
				$target.html(data);
				if (settings.show && $target.is(':not(:visible)')) {
					$target.show();
				}
				// auto empty
				if (settings.auto_empty !== undefined) {
					for (var key in settings.auto_empty) if (settings.auto_empty.hasOwnProperty(key)) {
						if ($target.is(key) || $(target).is(key)) {
							$(settings.auto_empty[key]).empty();
						}
					}
				}
				// track window position to target
				if (settings.track && xhr.from.target.beginsWith('#') && (window.scrollbar !== undefined)) {
					if ($target.offset().left < window.scrollbar.left()) {
						window.scrollbar.left($target.offset().left);
					}
					if ($target.offset().top < window.scrollbar.top()) {
						window.scrollbar.top($target.offset().top);
					}
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
				target = $target.get()[0];
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
		var hasFormCheckValidity = function ()
		{
			return (typeof document.createElement('form').checkValidity) === 'function';
		};

		//----------------------------------------------------------------------- hasFormReportValidity
		/**
		 * Returns true if browser manage reportValidity
		 *
		 * @return boolean
		 */
		var hasFormReportValidity = function ()
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
		var reportValidity = function (form)
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
		var urlAppend = function (url, search)
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
					var xhr     = undefined;
					var jax;
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
									data: $parent_form.serialize(),
									type: $parent_form.attr('method'),
									url:  urlAppend(anchor.href, anchor.search)
								}));
							}
						}
					}
					else if ($anchor.data('post')) {
						xhr = $.ajax(jax = $.extend(ajax, {
							data: $anchor.data('post'),
							type: 'post',
							url:  urlAppend(anchor.href, anchor.search)
						}));
					}
					if (!xhr) {
						xhr = $.ajax(jax = $.extend(ajax, {
							url: urlAppend(anchor.href, anchor.search)
						}));
					}
					xhr.ajax     = jax;
					xhr.from     = anchor;
					xhr.mouse_x  = (document.mouse === undefined) ? event.pageX : document.mouse.x;
					xhr.mouse_y  = (document.mouse === undefined) ? event.pageY : document.mouse.y;
					xhr.time_out = setTimeout(function() { $('body').css({ cursor: 'wait' }); }, 500);
				};
				executeClick();
				if (is_javascript) {
					return false;
				}
			}
		})
			.filter(settings.auto_redirect).click();

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
		})
			.filter(settings.auto_redirect).submit();

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
