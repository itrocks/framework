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
	 */
	$.fn.xtarget = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			auto_empty:      {}, // { 'target-selector': 'zone(s)-to-empty-selector' }
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
				$('body').css({cursor: 'auto'});
			},

			//-------------------------------------------------------------------------------- ajax.error
			error: function(xhr, status, error)
			{
				if (settings.error != undefined) {
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
				if ((settings.history.popup != undefined) || !$target.hasClass('popup')) {
					if (
						(settings.history.condition != undefined)
						&& $target.find(settings.history.condition).length
						&& (
							(settings.history.post != undefined)
							|| (xhr.ajax.type == undefined) || (xhr.ajax.type.toLowerCase() != 'post')
							|| (xhr.ajax.data == undefined) || !xhr.ajax.data.length
						)
					) {
						var title;
						if ((settings.history.title != undefined) && settings.history.title) {
							title = $target.find(settings.history.title).first().text();
							if (!title.length) {
								title = xhr.from.href;
							}
						}
						else {
							title = xhr.from.href;
						}
						document.title = title;
						window.history.pushState({ reload: true }, title, xhr.from.href);
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
				if (id.substr(0, 1) == '_') {
					$where = $($('body').children(':last-child'));
				}
				if (id == '_blank') {
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
				if ($where != $from) {
					$target.addClass('popup');
					$target.css('position', 'absolute');
					$target.css('left', left);
					$target.css('top',  top);
					$target.css('z-index', window.zindex_counter);
					if (settings.draggable_blank != undefined) {
						if (settings.draggable_blank === true) {
							$target.draggable();
						}
						else {
							$target.draggable({ handle: settings.draggable_blank });
						}
					}
				}
				return $target;
			},

			//------------------------------------------------------------------------------ ajax.success
			success: function(data, status, xhr)
			{
				var $from = $(xhr.from);
				var $target = $(xhr.from.target);
				var build_target = false;
				// popup a new element
				if ($target.is('.' + settings.closeable_popup)) {
					$target.remove();
					$target = $(xhr.from.target);
				}
				if (!$target.length) {
					$target = this.popup($from, xhr.from.target.substr(1));
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
						if ($target.is(key)) {
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
				if (settings.history != undefined) {
					this.pushHistory(xhr, $target);
				}
				// If build plugin is active : build loaded DOM
				if ($target.build != undefined) {
					if (build_target) $target.build();
					else              $target.children().build();
				}
				// on success callbacks
				var target = $target.get()[0];
				if (settings.success != undefined) {
					settings.success.call(target, data, status, xhr);
				}
				var on_success = $from.data('on-success');
				if (on_success != undefined) {
					on_success.call(target, data, status, xhr);
				}
			}
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
				url += (search ? '&' : '?') + settings.url_append;
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
			if (event.which != 2) {
				event.preventDefault();
				if (this.href.substr(0, 11) == 'javascript:') {
					eval(this.href.substr(11));
					return false;
				}
				else {
					var $this = $(this);
					var xhr = undefined;
					var jax;
					if ($this.hasClass(settings.submit)) {
						var $parent_form = $this.closest('form');
						if ($parent_form.length) {
							if ($parent_form.ajaxSubmit != undefined) {
								$parent_form.ajaxSubmit(jax = $.extend(ajax, {
									url:  urlAppend(this.href, this.search),
									type: $parent_form.attr('type')
								}));
								xhr = $parent_form.data('jqxhr');
							}
							else {
								xhr = $.ajax(jax = $.extend(ajax, {
									url:  urlAppend(this.href, this.search),
									data: $parent_form.serialize(),
									type: $parent_form.attr('method')
								}));
							}
						}
					}
					if (!xhr) {
						xhr = $.ajax(jax = $.extend(ajax, {
							url: urlAppend(this.href, this.search)
						}));
					}
					xhr.ajax     = jax;
					xhr.from     = this;
					xhr.mouse_x  = (document.mouse == undefined) ? event.pageX : document.mouse.x;
					xhr.mouse_y  = (document.mouse == undefined) ? event.pageY : document.mouse.y;
					xhr.time_out = setTimeout(function(){ $('body').css({cursor: 'wait'}); }, 500);
				}
			}
		});

		//---------------------------------------------------------------- $('form[target^='#']').click
		/**
		 * <form> with target '#*' are ajax calls
		 */
		this.find('form[target^="#"]').add(this.filter('form[target^="#"]')).submit(function(event)
		{
			var jax;
			var $this = $(this);
			var xhr;
			event.preventDefault();
			if ($this.ajaxSubmit != undefined) {
				$this.ajaxSubmit(jax = $.extend(ajax, {
					url:  urlAppend(this.action, this.action.indexOf('?') > -1),
					type: $this.attr('type')
				}));
				xhr = $this.data('jqxhr');
			}
			else {
				xhr = $.ajax(jax = $.extend(ajax, {
					url:  urlAppend(this.action, this.action.indexOf('?') > -1),
					data: $this.serialize(),
					type: $this.attr('method')
				}));
			}
			xhr.ajax = jax;
			xhr.from = this;
		});

		//--------------------------------------------------------------------------- window onpopstate
		if ((settings.history != undefined) && (settings.history.condition != undefined)) {
			$(window).bind('popstate', function(event)
			{
				if (
					(event.originalEvent.state != undefined)
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
