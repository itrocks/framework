(function($) {

	//-------------------------------------------------------------------------------------- tabber
	/**
	 * Very simple table system
	 *
	 * Accepts nested tabs
	 * Use URI target to directly open withed tab
	 */
	$.fn.tabber = function()
	{

		this.each(function()
		{
			var $tabber = $(this);
			var $tabs = $tabber.children("ul:first").addClass("ui-tabber-tabs").children("li");
			var $pages = $tabber.children("div");

			$tabber.addClass("ui-tabber");
			$tabs.addClass("ui-tabber-tab");
			$tabs.first().addClass("active");
			$pages.addClass("ui-tabber-page");
			$pages.first().addClass("active");
			$pages.each(function() { $(this).children("h2:first").css("display", "none"); });

			$tabs.children("a").click(function(event) {
				event.preventDefault();
				$this = $(this);
				$tabs.removeClass("active");
				$pages.removeClass("active");
				$this.closest(".ui-tabber-tab").addClass("active");
				$pages.filter($this.attr("href")).addClass("active");
			});
		});

		if (window.location && window.location.hash) {
			var $active_page = this.find(".ui-tabber-page" + window.location.hash);
			if ($active_page.length) {
				var $tabber = $active_page.closest(".ui-tabber");
				$tabber.find("a[href='" + window.location.hash + "']").click();
				var $page = $tabber.closest(".ui-tabber-page");
				while ($page.length) {
					$tabber = $page.closest(".ui-tabber");
					$tabber.find("a[href='#" + $page.attr("id") + "']").click();
					$page = $tabber.closest(".ui-tabber-page");
				}
			}
		}

	};

})( jQuery );
