(function($)
{

	/**
	 * Allow your pages to contain implicit ajax calls, using the power of selector targets
	 *
	 * - Works with <a> and <form> links
	 * - Initialise this feature with a single $("body").xTarget(); call
	 *
	 * @example
	 * <div id="position"></div>
	 * <a href="linked_page" target="#position">click to load linked page content into position</a>
	 *
	 * @example
	 * <div id="position"></div>
	 * <form action="linked_page" target="#position">(...)</form>
	 */
	$.fn.xTarget = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			url_append: "",
			keep:       "popup",
			submit:     "submit",
			error:      undefined,
			success:    undefined
		}, options);

		//---------------------------------------------------------------------------------------- ajax
		var ajax =
		{
			//------------------------------------------------------------------------------- ajax.target
			target: undefined,

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
				var $target = $(xhr.from.target);
				if (!$target.length) {
					$target = $("<div>").attr("id", xhr.from.target.substr(1));
					var $from = $(xhr.from);
					if (settings["keep"] && $from.hasClass(settings["keep"])) {
						$target.addClass(settings["keep"]);
					}
					$target.insertAfter($from);
				}
				$target.html(data);
				if (settings["success"] != undefined) {
					settings["success"](data, status, xhr);
				}
				if ($target.build != undefined) {
					$target.build();
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
			var done = false;
			if ($this.hasClass(settings["submit"])) {
				var $parent_form = $this.closest("form");
				if ($parent_form.length) {
					$parent_form.ajaxSubmit($.extend(ajax, {
						url: urlAppend(this.href, this.search)
					}));
					$parent_form.data("jqxhr").from = this;
					done = true;
				}
			}
			if (!done) {
				var $xhr = $.ajax($.extend(ajax, {
					url: urlAppend(this.href, this.search)
				}));
				$xhr.from = this;
			}
		});

		//---------------------------------------------------------------- $('form[target^="#"]').click
		/**
		 * <form> with target "#*" are ajax calls
		 */
		this.find('form[target^="#"]').submit(function(event)
		{
			var $this = $(this);
			event.preventDefault();
			$this.ajaxSubmit($.extend(ajax, {
				url: urlAppend(this.action, this.search)
			}));
			$this.data("jqxhr").from = this;
		});

		return this;
	};

})( jQuery );
