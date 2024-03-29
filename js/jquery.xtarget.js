
//---------------------------------------------------------------------------- more_request_headers
let more_request_headers = {}

//---------------------------------------------------------------------------------- requestHeaders
/**
 * Add 'beforeSend: requestHeaders' to your ajax calls to add information about the client
 *
 * @param request
 */
const requestHeaders = function(request)
{
	request.setRequestHeader('screen-height', screen.height)
	request.setRequestHeader('screen-width',  screen.width)
	request.setRequestHeader('window-height', $(window).height())
	request.setRequestHeader('window-width',  $(window).width())
	for (const header in more_request_headers) if (more_request_headers.hasOwnProperty(header)) {
		request.setRequestHeader(header, more_request_headers[header])
	}
	more_request_headers = {}
}

//---------------------------------------------------------------------------- requestTargetHeaders
const requestTargetHeaders = function($element)
{
	let target = $element.attr('target')
	if (target.startsWith('#')) {
		let $target = $(target)
		if ((target === '#main') && !$target.length) {
			$target = $('main')
		}
		if ($target.length) {
			more_request_headers['target-height'] = $target.height()
			more_request_headers['target-width']  = $target.width()
		}
	}
};

(function($)
{

	//--------------------------------------------------------------------------------------- xtarget
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

		let last_history_entry

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			auto_empty:        {}, // { 'target-selector': 'zone(s)-to-empty-selector' }
			auto_empty_except: undefined, // string : if set, selector for exception original link
			closeable_popup:   'popup',
			draggable_blank:   undefined,
			error:             undefined,
			history:           false, // { condition, popup, post, title }
			keep:              'popup',
			popup_element:     'div',
			post:              undefined,
			show:              undefined,
			submit:            'submit',
			success:           undefined,
			track:             true,
			url_append:        '',
			xtarget_from:      'xtarget.from'
		}, options)

		//---------------------------------------------------------------------------------------- ajax
		const ajax = {

			//------------------------------------------------------------------------------- ajax.target
			target: undefined,

			//----------------------------------------------------------------------------- ajax.complete
			complete: function(xhr)
			{
				clearTimeout(xhr.time_out)
				$('body').css({ cursor: 'auto' })
			},

			//-------------------------------------------------------------------------------- ajax.error
			error: function(xhr, status, error)
			{
				if (settings.error !== undefined) {
					settings.error(xhr, status, error)
				}
			},

			//-------------------------------------------------------------------------- ajax.pushHistory
			/**
			 * @param xhr     object
			 * @param $target jQuery
			 */
			pushHistory: function(xhr, $target)
			{
				// no history for popups
				if ((settings.history.popup === undefined) && $target.hasClass('popup')) {
					return
				}
				// query type : get / post ?
				let type = xhr.ajax.type
				if (type === undefined) type = xhr.call_type
				if (type === undefined) type = 'get'
				// no history when no condition, or when post queries are all filtered
				if (
					(settings.history.condition === undefined)
					|| !$target.find(settings.history.condition).length
					|| ((type === 'post') && !settings.history.post)
				) {
					return
				}
				// no history when do not match post conditions
				let history_entry = xhr.from.href
				if ((type === 'post') && Array.isArray(settings.history.post)) {
					let match_post = false
					for (const post_filter of settings.history.post) {
						if (history_entry.match(post_filter)) {
							match_post = true
							break
						}
					}
					if (!match_post) {
						return
					}
				}
				// set document title
				let title
				if ((settings.history.title !== undefined) && settings.history.title) {
					title = $target.find(settings.history.title).first().text()
					if (!title.length) {
						title = xhr.from.href
					}
				}
				else {
					title = xhr.from.href
				}
				document.title = title
				// no history when the history entry is the same as the previous one was
				if ((history_entry === undefined) || (history_entry === last_history_entry)) {
					return
				}
				// no history when do not match get conditions
				for (const without_get_var of settings.history.without_get_vars) {
					if (history_entry.match(without_get_var)) {
						return
					}
				}
				// history
				last_history_entry = xhr.from.href
				if (settings.url_append) {
					history_entry = history_entry
						.repl('?' + settings.url_append, '')
						.repl('&' + settings.url_append)
				}
				try {
					window.history.pushState({reload: true}, title, history_entry)
				}
				// known cases :
				// - cross-domain : a page loaded from another domain
				catch (exception) {
					// no way : forget about it
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
				const $body = $('body')
				const $from = $where
				const left  = $where.offset().left
				const top   = $where.offset().top + $where.outerHeight()
				if (id.startsWith('_')) {
					$where = $($body.children(':last-child'))
				}
				if (id === '_blank') {
					id = 'window' + ++window.id_index
				}
				const $target = $('<' + settings.popup_element + '>')
					.addClass(settings.closeable_popup)
					.attr('id', id)
				if (settings.keep && $where.hasClass(settings.keep)) {
					$target.addClass(settings.keep)
				}
				$target.data(settings.xtarget_from, $from)
				$where.hasClass('popup')
					? $body.append($target)
					: $where.after($target)
				if (($where !== $from) || $where.hasClass('popup')) {
					$target.addClass('popup')
					if ($where.hasClass('right')) {
						$target.css('right', $(window).width() - (left + $where.width()))
					}
					else {
						$target.css('left', left)
					}
					$target.css('position', 'absolute')
					$target.css('top',      top)
					$target.css('z-index',  zIndex())
					if (settings.draggable_blank !== undefined) {
						if (settings.draggable_blank === true) {
							$target.draggable()
						}
						else {
							$target.draggable({ handle: settings.draggable_blank })
						}
					}
				}
				setTimeout(function() {
					const offset  = $target.offset()
					const $window = $(window)
					if ((offset.left + $target.outerWidth()) > $window.outerWidth()) {
						offset.left = Math.max(0, $window.outerWidth() - $target.outerWidth())
						$target.css({ left: '', right: 0 })
					}
					if ((offset.top + $target.outerHeight()) > $window.outerHeight()) {
						offset.top = Math.max(0, $window.outerHeight() - $target.outerHeight())
						$target.css('top', offset.top)
					}
					if (window.scrollbar !== undefined) {
						if (offset.left < window.scrollbar.left()) {
							$target.css({ left: window.scrollbar.left(), right: '' })
						}
						if (offset.top < window.scrollbar.top()) {
							$target.css('top', window.scrollbar.top())
						}
					}
				})
				return $target
			},

			//------------------------------------------------------------------------------ ajax.success
			success: function(data, status, xhr)
			{
				let   target       = xhr.from.target
				const focus        = (xhr.from.href ? xhr.from.href : xhr.from.action).rParse('#')
				const $from        = $(xhr.from)
				let   $target      = $(target)
				let   build_target = false
				if (target.endsWith('main') && !$target.length) {
					$target = $(target.startsWith('#') ? 'main' : '#main')
				}
				// popup a new element
				if ($target.is('.' + settings.closeable_popup)) {
					$target.remove()
					$target = $(xhr.from.target)
				}
				if (!$target.length) {
					$target      = this.popup($from, xhr.from.target.substring(1))
					build_target = true
				}
				const keep_scroll = new Keep_Scroll($target)
				keep_scroll.keep()
				$target = $target.htmlTarget(data)
				$target.each(function() {
					const $target = $(this)
					if ($target.find('form, .form').length && $target.closest('form').length) {
						$target.css({ right: 0, top: $target.offset().top + 'px' })
						$target.insertAfter($target.closest('form'))
					}
				})
				if (settings.show && $target.filter(':not(:visible)').length) {
					$target.filter(':not(:visible)').show()
				}
				// auto empty
				if ((settings.auto_empty !== undefined) && !xhr.auto_empty_except) {
					for (const key in settings.auto_empty) if (settings.auto_empty.hasOwnProperty(key)) {
						const empty_target = settings.auto_empty[key]
						if (
							($target.filter(key).length || $(target).is(key))
							&& !$target.filter(empty_target).length
						) {
							$(empty_target).empty()
						}
					}
				}
				// track window position to focus, if set
				const $focus = focus.length ? $('#' + focus) : null
				if ($focus && $focus.length) {
					if (
						($focus.offset().left < window.scrollbar.left())
						|| ($focus.offset().left > (window.scrollbar.left() + window.innerWidth))
					) {
						window.scrollbar.left($focus.offset().left)
					}
					if (
						($focus.offset().top < window.scrollbar.top())
						|| ($focus.offset().top > (window.scrollbar.top() + window.innerHeight))
					) {
						window.scrollbar.top($focus.offset().top)
					}
				}
				// track window position to target
				else if (
					settings.track && xhr.from.target.startsWith('#') && (window.scrollbar !== undefined)
				) {
					$target.each(function() {
						const $target = $(this)
						if ($target.offset().left < window.scrollbar.left()) {
							window.scrollbar.left($target.offset().left)
						}
						if ($target.offset().top < window.scrollbar.top()) {
							window.scrollbar.top($target.offset().top)
						}
					})
				}
				// change browser's URL and title, push URL into history
				if (settings.history !== undefined) {
					this.pushHistory(xhr, $target)
				}
				// If build plugin is active : build loaded DOM
				if ($target.build !== undefined) {
					build_target
						? $target.build()
						: $target.children().build()
				}
				keep_scroll.serve()
				// on success callbacks
				target = $target.last()[0]
				if (settings.success !== undefined) {
					settings.success.call(target, data, status, xhr)
				}
				const on_success = $from.data('on-success')
				if (on_success !== undefined) {
					on_success.call(target, data, status, xhr)
				}
				if ($from.hasClass('disabled')) {
					$from.removeClass('disabled progress')
				}
			}
		}

		//----------------------------------------------------------------------- hasFormReportValidity
		/**
		 * Returns true if browser manage checkValidity
		 *
		 * @return boolean
		 */
		const hasFormCheckValidity = function()
		{
			return (typeof document.createElement('form').checkValidity) === 'function'
		}

		//----------------------------------------------------------------------- hasFormReportValidity
		/**
		 * Returns true if browser manage reportValidity
		 *
		 * @return boolean
		 */
		const hasFormReportValidity = function()
		{
			return (typeof document.createElement('form').reportValidity) === 'function'
		}

		//------------------------------------------------------------------------------ reportValidity
		/**
		 * Reports the validity of a form
		 *
		 * @param form HTMLFormElement
		 * @return boolean
		 */
		const reportValidity = function(form)
		{
			if (hasFormReportValidity()) {
				return form.reportValidity()
			}
			else if (hasFormCheckValidity()) {
				if (form.checkValidity()) {
					return true
				}
				alert('Invalid data input')
				return false
			}
			// No check method, fallback to default behaviour
			return true
		}

		//----------------------------------------------------------------------------------- urlAppend
		/**
		 * Append the url_append setting to the url
		 *
		 * @param url    string the url
		 * @param search string the '?var=value&var2=value2' part of the url, if set
		 * @return string
		 */
		const urlAppend = function(url, search)
		{
			if (settings.url_append) {
				url = url.lParse('#')
					+ (search ? '&' : '?')
					+ settings.url_append
					+ ((url.indexOf('#') >= 0) ? '#' : '') + url.rParse('#')
			}
			return url
		}

		//------------------------------------------------------------------- $('a[target^='#']').click
		/**
		 * <a> with target '#*' are ajax calls
		 *
		 * If the anchor is inside a form and the anchor class 'submit' is set, the link submits the
		 * form with the anchor href attribute as action
		 */
		this.find('a[target^="#"]').add(this.filter('a[target^="#"]')).click(function(event)
		{
			if (event.ctrlKey) {
				return
			}
			if (event.which !== 2) {
				const anchor = this
				if (!anchor.href) {
					return
				}
				const is_javascript = anchor.href.startsWith('javascript:')
				event.preventDefault()
				const executeClick = function() {
					// ensures that pending messages (blur on a combo) resolve before click
					if (window.running_combo !== undefined) {
						setTimeout(executeClick)
						return
					}
					if (is_javascript) {
						eval(anchor.href.substring(11))
						return
					}
					const $anchor = $(anchor)
					let   jax
					let   xhr = undefined
					requestTargetHeaders($anchor)
					if ($anchor.hasClass(settings.submit) || $anchor.parent().hasClass(settings.submit)) {
						$anchor.addClass('disabled progress')
						const $parent_form = $anchor.closest('form')
						if ($parent_form.length) {
							/*
							TODO: 97556 Rework required properties first
							if (!reportValidity($parent_form[0])) {
								return
							}
							*/
							if ($parent_form.ajaxSubmit !== undefined) {
								$parent_form.ajaxSubmit(jax = $.extend(ajax, {
									beforeSend: requestHeaders,
									type:       $parent_form.attr('type'),
									url:        urlAppend(anchor.href, anchor.search)
								}))
								xhr = $parent_form.data('jqxhr')
							}
							else {
								xhr = $.ajax(jax = $.extend(ajax, {
									beforeSend: requestHeaders,
									data:       $parent_form.serialize(),
									type:       $parent_form.attr('method'),
									url:        urlAppend(anchor.href, anchor.search)
								}))
							}
							xhr.call_type = $parent_form.attr('method')
						}
					}
					else if ($anchor.data('post')) {
						xhr = $.ajax(jax = $.extend(ajax, {
							beforeSend: requestHeaders,
							data:       $anchor.data('post'),
							type:       'post',
							url:        urlAppend(anchor.href, anchor.search)
						}))
					}
					if (!xhr) {
						xhr = $.ajax(jax = $.extend(ajax, {
							beforeSend: requestHeaders,
							url:        urlAppend(anchor.href, anchor.search)
						}))
					}
					xhr.ajax     = jax
					xhr.from     = anchor
					xhr.mouse_x  = (document.mouse === undefined) ? event.pageX : document.mouse.x
					xhr.mouse_y  = (document.mouse === undefined) ? event.pageY : document.mouse.y
					xhr.time_out = setTimeout(() => $('body').css({ cursor: 'wait' }), 500)
					xhr.auto_empty_except = (settings.auto_empty_except !== undefined)
						&& $anchor.is(settings.auto_empty_except)
				}
				executeClick()
				if (is_javascript) {
					return false
				}
			}
		})

		//---------------------------------------------------------------- $('form[target^='#']').click
		/**
		 * <form> with target '#*' are ajax calls
		 */
		this.find('form[target^="#"]').add(this.filter('form[target^="#"]')).submit(function(event)
		{
			const form  = this
			const $form = $(form)
			let   jax
			let   xhr
			event.preventDefault()
			const executeClick = function() {
				// ensures that pending messages (blur on a combo) resolve before click
				if (window.running_combo !== undefined) {
					setTimeout(executeClick)
					return
				}
				requestTargetHeaders($form)
				if ($form.ajaxSubmit !== undefined) {
					$form.ajaxSubmit(jax = $.extend(ajax, {
						beforeSend: requestHeaders,
						type:       $form.attr('type'),
						url:        urlAppend(form.action, form.action.indexOf('?') > -1)
					}))
					xhr = $form.data('jqxhr')
				}
				else {
					xhr = $.ajax(jax = $.extend(ajax, {
						beforeSend: requestHeaders,
						data:       $form.serialize(),
						type:       $form.attr('method'),
						url:        urlAppend(form.action, form.action.indexOf('?') > -1)
					}))
				}
				xhr.ajax     = jax
				xhr.from     = form
				xhr.time_out = setTimeout(() => $('body').css({ cursor: 'wait' }), 500)
			}
			executeClick()
		})

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
					document.location.reload()
				}
			})
		}

		return this
	}

})( jQuery )
