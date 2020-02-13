(function($)
{

	/**
	 * inputTitleHelper plugin for jQuery
	 * (c) Baptiste Pillot http://www.pillot.fr
	 *
	 * For <input title="help text"> :
	 *
	 * when the input is empty and unfocused :
	 * - sets its value to 'help text'
	 * - adds 'helper' to its css classes for custom design
	 * - if input is a password, its type is set to text (when empty) for the help text to be displayed
	 *
	 * options are :
	 * help_class : name of the css to be used for custom design when the help text is displayed
	 */
	$.fn.inputTitleHelper = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			help_class: 'helper'
		}, options);

		this.each(function()
		{
			var $this = $(this);

			var $focus;

			//-------------------------------------------------------------------------------------- blur
			$this.blur(function()
			{
				var $this = $(this);
				if ($this.val() === '') {
					if ($this.attr('type') === 'password') {
						var $container = $('<p>');
						$container.append($this.clone());
						var $helper = $($container.html().repl('type="password" ', 'type="text"'));
						$helper.attr('name', '').data('password', true);
						$this.hide().after($helper);
						$this = $helper;
						$this.focus($focus);
					}
					$this.val($this.attr('title'));
					$this.addClass(settings.help_class);
				}
			});

			//------------------------------------------------------------------------------------- focus
			$this.focus($focus = function()
			{
				var $this = $(this);
				if ($this.val() === $this.attr('title')) {
					if ($this.data('password')) {
						$this = $this.prev();
						$this.next().remove();
						$this.show().focus();
					}
					$this.val('');
					$this.removeClass(settings.help_class);
				}
			});

			$this.blur();
		});

		return this;
	};

})( jQuery );
