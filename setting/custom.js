$('document').ready(function()
{
	$('article').build(function()
	{
		if (!this.length) return;

		var $input = this.inside('input.custom.name');
		$input.autoWidth();

		//------------------------------------------------------------------------------- h2 span click
		this.inside('h2>span').click(function()
		{
			var $this = $(this);
			if ($this.data('stop-click')) {
				$this.data('stop-click', '');
				return;
			}
			var $ul_custom_selection = $this.parent().find('ul.custom.selection');
			if ($ul_custom_selection.is(':visible')) {
				$('body').click();
			}
			else {
				$ul_custom_selection.fadeIn(200, function () {
					var click_event = function () {
						$('body').off('click', click_event);
						$ul_custom_selection.fadeOut(200);
					};
					$('body').on('click', click_event);
				});
			}
		});

		//---------------------------------------------------------------------- input.custom.name blur
		// Loose focus more than 100 ms (without coming back) : cancel
		$input.blur(function()
		{
			var input = this;
			input.is_inside = false;
			setTimeout(function() { if (!input.is_inside) input.close(); }, 100);
		});

		//--------------------------------------------------------------------- input.custom.name focus
		$input.focus(function()
		{
			this.is_inside = true;
		});

		//------------------------------------------------------------------- input.custom.name keydown
		// Press ENTER : save, press ESCAPE : cancel
		$input.keydown(function(event)
		{
			var $this = $(this);
			if (event.keyCode === $.ui.keyCode.ENTER) {
				var $a = $this.closest('h2').find('a.custom_save, .custom_save>a');
				if (!$this.closest('form').length) {
					var app = window.app;
					$a.removeClass('submit');
					$a.attr('href', app.askAnd($a.attr('href'), $this.attr('name') + '=' + $this.val()));
				}
				$a.click();
				event.preventDefault();
			}
			if (event.keyCode === $.ui.keyCode.ESCAPE) {
				this.close();
			}
		});

		//------------------------------------------------------------------- [a].custom_save[>a] click
		// click on save button opens the save form between calling save
		this.inside('a.custom_save, .custom_save>a').click(function(event)
		{
			var $this  = $(this);
			var $h2    = $this.closest('h2');
			var $input = $h2.children('input.custom.name');
			if (!$input.filter(':visible').length) {
				event.preventDefault();
				event.stopImmediatePropagation();
				$input
					.attr('name', 'save_name')
					.fadeIn(200)
					.keyup()
					.focus();
				$input.get(0).close = function()
				{
					var $this = $(this);
					$this.fadeOut(200);
					$this.removeAttr('name');
				};
			}
			else if (!$input.val()) {
				event.preventDefault();
				event.stopImmediatePropagation();
				alert('Veuillez saisir un nom puis valider, ou tapez echap pour annuler');
			}
		});

	});
});
