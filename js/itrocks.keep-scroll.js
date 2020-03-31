$(document).ready(function() {

	/**
	 * Use :
	 *
	 * - Call this immediately before $target.write() :
	 *   var keep_scroll = new Keep_Scroll($target);
	 *   keep_scroll.keep();
	 *
	 * - Then call this immediately after $target.build() :
	 *   keep_scroll.serve();
	 *
	 * @constructor
	 */
	window.Keep_Scroll = function($target)
	{

		//---------------------------------------------------------------------------------- scroll_top
		this.scroll_top = [];

		//------------------------------------------------------------------------------------- $target
		this.$target = $target;

		//---------------------------------------------------------------------------------------- keep
		this.keep = function()
		{
			var self = this;
			this.$target.find('.keep-scroll').each(function () {
				self.scroll_top.push($(this).scrollTop());
			});
		};

		//--------------------------------------------------------------------------------------- serve
		this.serve = function()
		{
			var self = this;
			if (this.scroll_top.length) this.$target.find('.keep-scroll').each(function() {
				if (!self.scroll_top.length) {
					return false;
				}
				var scroll = self.scroll_top.shift();
				var $this = $(this);
				$this.scrollTop(scroll);
				// in cas of autofocus : force
				setTimeout(function() { $this.scrollTop(scroll); });
			});
		};

	};

});
