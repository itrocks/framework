(function($) {

	//--------------------------------------------------------------------------------- selected_tabs
	if (window.selected_tabs === undefined) {
		window.selected_tabs = {};
	}

	//---------------------------------------------------------------------------------------- tabber
	/**
	 * Very simple tabs system
	 *
	 * Accepts nested tabs
	 * Use URI target to directly open withed tab
	 * You can call tabber('add', $add_tab, $add_page) to add a new tab
	 */
	$.fn.tabber = function(action, $add_tab, $add_page)
	{

		var $tabber = this;
		var settings;

		//---------------------------------------------------------------------------------- tabber.add
		if ((action === 'add') && ($add_tab !== undefined) && ($add_page !== undefined)) {

			$tabber = $add_tab.closest('.ui-tabber');
			$add_tab.children('a').click($tabber.data('click_handler'));
			$tabber.data('tabs',  $tabber.children('ul:first').children('li'));
			$tabber.data('pages', $tabber.children(':not(ul:first)'));

		}

		//-------------------------------------------------------------------------------- tabber.click
		else if ((action === 'click') && ($add_tab !== undefined)) {

			$tabber.children('.ui-tabber-tabs').find('a[href="' + $add_tab + '"]').click();

		}

		//------------------------------------------------------------------------------ tabber.refresh
		else if (action === 'refresh') {

			$tabber.data('tabs',  $tabber.children('ul:first').children('li'));
			$tabber.data('pages', $tabber.children(':not(ul:first)'));

		}

		//------------------------------------------------------------------------------ tabber (apply)
		else {

			//---------------------------------------------------------------------------------- settings
			settings = $.extend({
				window_identifier: 'data-class',
				window_selector:   'article'
			}, action);

			//---------------------------------------------------------------------------------- each tab
			this.each(function() {
				var $tabber = $(this);
				var $tabs = $tabber.children('ul:first').addClass('ui-tabber-tabs').children('li');
				var $pages = $tabber.children(':not(ul:first)');

				$tabber.addClass('ui-tabber');
				$tabs.addClass('ui-tabber-tab');
				$tabs.first().addClass('active');
				$pages.addClass('ui-tabber-page');
				$pages.first().addClass('active');
				$pages.each(function() { $(this).children(':first').css('display', 'none'); });

				var click_handler;
				$tabs.children('a').click(click_handler = function(event)
				{
					event.preventDefault();
					var $this   = $(this);
					var $tabber = $this.closest('.ui-tabber');
					var $tabs   = $tabber.data('tabs');
					var $pages  = $tabber.data('pages');
					$tabs.removeClass('active');
					$pages.removeClass('active');
					$this.closest('.ui-tabber-tab').addClass('active');
					$pages.filter($this.attr('href')).addClass('active');
					if ($pages.filter($this.attr('href')).autofocus !== undefined) {
						$pages.filter($this.attr('href')).autofocus();
					}
					var find_edit = window.location.pathname + '/edit';
					var $window   = $this.closest(settings.window_selector);
					$window.find('a[href="' + find_edit + '"]').each(function() {
						$(this).attr('href', find_edit + '#' + $this.prop('href').rParse('#'));
					});
					window.history.pushState({reload: true}, document.title, $this.prop('href'));
					selected_tabs[$window.attr(settings.window_identifier)] = $this.prop('href').rParse('#');
				});

				$tabber.data('click_handler', click_handler);
				$tabber.data('tabs',          $tabs);
				$tabber.data('pages',         $pages);

				var $window = $tabber.closest(settings.window_selector);
				if (!window.location.hash && ($window.attr(settings.window_identifier) in selected_tabs)) {
					var selected_tab = selected_tabs[$window.attr(settings.window_identifier)];
					window.location  = window.location + '#' + selected_tab;
				}
			});

			if (window.location && window.location.hash) {
				var $active_page = this.find('.ui-tabber-page' + window.location.hash);
				if ($active_page.length) {
					$tabber = $active_page.closest('.ui-tabber');
					$tabber.find('a[href="' + window.location.hash + '"]').click();
					var $page = $tabber.closest('.ui-tabber-page');
					while ($page.length) {
						$tabber = $page.closest('.ui-tabber');
						$tabber.find('a[href="#' + $page.attr('id') + '"]').click();
						$page = $tabber.closest('.ui-tabber-page');
					}
				}
			}

			return this;
		}

	};

})( jQuery );
