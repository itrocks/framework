(function($)
{

	/**
	 * Allow your pages to contain implicit ajax calls, using the power of selector targets
	 *
	 * - Works with <a> and <form> links
	 * - Initialise this feature with a single $("body").xtarget(); call
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
			url_append: "",
			keep:       "popup",
			submit:     "submit",
			error:      undefined,
			success:    undefined,
			draggable_blank: undefined
		}, options);

		//---------------------------------------------------------------------------------------- ajax
		var ajax =
		{

			//------------------------------------------------------------------------------- ajax.target
			target: undefined,

			//---------------------------------------------------------------------------------- complete
			complete: function(xhr)
			{
				clearTimeout(xhr.time_out);
				$("body").css({cursor: "auto"});
			},

			//-------------------------------------------------------------------------------- ajax.error
			error: function(xhr, status, error)
			{
				if (settings["error"] != undefined) {
					settings["error"](xhr, status, error);
				}
			},

			//------------------------------------------------------------------------------ ajax.success
			success: function(data, status, xhr)
			{
				var $from = $(xhr.from);
				var $target = $(xhr.from.target);
				var build_target = false;
				// popup a new element
				if (!$target.length) {
					var destination = xhr.from.target.substr(1);
					var $where = $from;
					if (destination == "_blank") {
						destination = "window" + ++window.zindex_counter;
						$where = $($("body").children(":last-child"));
					}
					$target = $("<section>").attr("id", destination);
					if (settings["keep"] && $from.hasClass(settings["keep"])) {
						$target.addClass(settings["keep"]);
					}
					$target.insertAfter($where);
					if ($where != $from) {
						$target.css("position", "absolute");
						$target.css("left", document.mouse.x);
						$target.css("top",  document.mouse.y);
						$target.css("z-index", window.zindex_counter);
						if (settings["draggable_blank"] != undefined) {
							$target.draggable({ handle: settings["draggable_blank"] });
						}
					}
					build_target = true;
				}
				// write result into destination element, and build jquery active contents
				$target.html(data);
				if ($target.build != undefined) {
					if (build_target) $target.build();
					else              $target.children().build();
				}
				// on.success callbacks
				var target = $target.get()[0];
				if (settings["success"] != undefined) {
					target.success = settings["success"];
					target.success(data, status, xhr);
					target.success = undefined;
				}
				var on_success = $from.data("on.success");
				if (on_success != undefined) {
					target.success = on_success;
					target.success(data, status, xhr);
					target.success = undefined;
				}
			}

		};

		//----------------------------------------------------------------------------------- urlAppend
		/**
		 * Append the url_append setting to the url
		 *
		 * @param url    string the url
		 * @param search string the "?var=value&var2=value2" part of the url, if set
		 * @return string
		 */
		var urlAppend = function (url, search)
		{
			if (settings.url_append) {
				url += (search ? "&" : "?") + settings.url_append;
			}
			return url;
		};

		//------------------------------------------------------------------- $('a[target^="#"]').click
		/**
		 * <a> with target "#*" are ajax calls
		 *
		 * If the a element is inside a form and the a class "submit" is set, the link submits the form with the a href attribute as action
		 */
		this.find('a[target^="#"]').click(function(event)
		{
			event.preventDefault();
			var $this = $(this);
			var xhr = undefined;
			if ($this.hasClass(settings["submit"])) {
				var $parent_form = $this.closest("form");
				if ($parent_form.length) {
					if ($parent_form.ajaxSubmit != undefined) {
						$parent_form.ajaxSubmit($.extend(ajax, {
							url: urlAppend(this.href, this.search)
						}));
						xhr = $parent_form.data("jqxhr");
					}
					else {
						xhr = $.ajax($.extend(ajax, {
							url:  urlAppend(this.href, this.search),
							data: $parent_form.serialize(),
							type: $parent_form.attr("method")
						}));
					}
				}
			}
			if (!xhr) {
				xhr = $.ajax($.extend(ajax, {
					url: urlAppend(this.href, this.search)
				}));
			}
			xhr.from    = this;
			xhr.mouse_x = (document.mouse == undefined) ? event.pageX : document.mouse.x;
			xhr.mouse_y = (document.mouse == undefined) ? event.pageY : document.mouse.y;
			xhr.time_out = setTimeout(function(){ $("body").css({cursor: "wait"}); }, 500);
		});

		//---------------------------------------------------------------- $('form[target^="#"]').click
		/**
		 * <form> with target "#*" are ajax calls
		 */
		this.find('form[target^="#"]').submit(function(event)
		{
			var $this = $(this);
			event.preventDefault();
			if ($this.ajaxSubmit != undefined) {
				$this.ajaxSubmit($.extend(ajax, {
					url: urlAppend(this.action, this.search)
				}));
				$this.data("jqxhr").from = this;
			}
			else {
				$.ajax($.extend(ajax, {
					url:  urlAppend(this.action, this.search),
					data: $this.serialize(),
					type: $this.attr("method")
				})).from = this;
			}
		});

		return this;
	};

})( jQuery );
