$('document').ready(function()
{
	var $article_header = $('article[data-class] > header');

	//------------------------------------------------------------- article[data-class] > header > h2
	$article_header.children('h2').build(function()
	{
		this.click(function() {
			var $this = $(this);
			if ($this.data('stop-click')) {
				$this.data('stop-click', '');
				return;
			}
			var $select = $this.parent().find('> ul.select');
			if ($select.is(':visible')) {
				$('body').click();
			}
			else {
				$select.fadeIn(200, function () {
					var click_event = function () {
						$('body').off('click', click_event);
						$select.fadeOut(200);
					};
					$('body').on('click', click_event);
				});
			}
		});
	});

	//------------------------------------------------ article[data-class] > header input#custom_name
	$article_header.find('input#custom_name').build(function()
	{
		this.autoWidth();

		// Loose focus more than 200 ms (without coming back) : cancel
		this.blur(function()
		{
			var input = this;
			input.is_inside = false;
			setTimeout(function() { if (!input.is_inside) input.close(); }, 200);
		});

		// Came back inside (cancel blur)
		this.focus(function()
		{
			this.is_inside = true;
		});

		// Press ENTER : save, press ESCAPE : cancel
		this.keydown(function(event)
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
	});

	//------------------------ article[data-class] > header > .custom > .actions > li.custom_save > a
	$article_header.find('> .custom > .actions > li.custom_save > a').build(function()
	{
		// click on save button opens the save form between calling save
		this.click(function(event)
		{
			var $this  = $(this);
			var $input = $this.closest('.custom').find('input#custom_name');
			if (!$input.filter(':visible').length) {
				event.preventDefault();
				event.stopImmediatePropagation();
				$input.attr('name', 'save_name').fadeIn(200).keyup().focus();
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
				alert(tr('Please input a name then valid, or enter escape to cancel'));
			}
		});
	});

});
