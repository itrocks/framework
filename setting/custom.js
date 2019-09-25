$(document).ready(function()
{
	var $body = $('body');
	var article_header = 'article[data-class] > form > header';

	//------------------------------------------------------------- article[data-class] > header > h2
	$body.build('click', [article_header, '> h2'], function()
	{
		var $this = $(this);
		if ($this.data('stop-click')) {
			$this.data('stop-click', '');
			return;
		}
		var $select = $this.parent().find('ul.select');
		if ($select.is(':visible')) {
			$('body').click();
		}
		else {
			$select.fadeIn(200, function() {
				var clickEvent = function() {
					$('body').off('click', clickEvent);
					$select.fadeOut(200);
				};
				$('body').on('click', clickEvent);
			}).css('display', 'block');
		}
	});

	//------------------------------------------------ article[data-class] > header input#custom_name
	$body.build('call', [article_header, 'input#custom_name'], function()
	{
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
				var $a = $this.closest('header').find('> .custom > .actions > .custom_save > a');
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
	/**
	 * Click on save button opens the save form between calling save
	 */
	$body.build(
		'click', [article_header, '> .custom > .actions > li.custom_save > a'],
		function(event)
		{
			var $this  = $(this);
			var $input = $this.closest('.custom').find('> .name > input#custom_name');
			if (!$input.filter(':visible').length) {
				event.preventDefault();
				event.stopImmediatePropagation();
				$input.attr('name', 'save_name');
				$input.closest('.name').fadeIn(200);
				setTimeout(function() { $input.keyup().focus(); }, 200);
				$input.get(0).close = function()
				{
					var $this = $(this);
					$this.closest('.name').fadeOut(200);
					$this.removeAttr('name');
				};
			}
			else if (!$input.val()) {
				event.preventDefault();
				event.stopImmediatePropagation();
				alert(tr('Please input a name then valid, or enter escape to cancel'));
			}
		}
	);

});
