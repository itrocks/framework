(function($)
{

	$.fn.minimize = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			duration: 500,
			minimized_class: "minimized",
			min_height: 24,
			min_width: 24
		}, options);

		//------------------------------------------------------------------------------------ maximize
		var maximize = function()
		{
			this.animate(
				{ height: this.data("height"), padding: this.data("padding"), width: this.data("width") },
				settings.duration,
				function()
				{
					$(this)
						.removeClass(settings.minimized_class)
						.css({ height: "", overflow: "", padding: "", width: "" })
						.children(".minimize").html("minimize");
				}
			);
		};

		//------------------------------------------------------------------------------------ minimize
		var minimize = function()
		{
			this.data("height", this.height() + "px");
			this.data("padding",  this.css("padding"));
			this.data("width",  this.width() + "px");
			this.css({ overflow: "hidden" });
			this.animate(
				{ height: settings.min_height + "px", padding: 0, width: settings.min_width + "px" },
				settings.duration,
				function() {
					$(this).addClass(settings.minimized_class).children(".minimize").html("maximize");
				}
			);
		};

		//----------------------------------------------------------------------------- .minimize click
		var $div = $('<div class="minimize">minimize</div>');
		$div.click(function()
		{
			var $parent = $(this).parent();
			if ($parent.hasClass(settings.minimized_class)) {
				$parent.maximize = maximize;
				$parent.maximize();
			}
			else {
				$parent.minimize = minimize;
				$parent.minimize();
			}
		});
		this.prepend($div);

		return this;
	}

})( jQuery );
