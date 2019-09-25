$(document).ready(function()
{
	var $body = $('body');
	var article_header = 'article[data-class] > form > header';

	//------------------------------------------------------------- article[data-class] > header > h2
	/**
	 * Show / hide 'load article' popup
	 */
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
			$select.css(
				'left', ($this.offset().left) - parseInt($this.parent().offset().left).toString() + 'px'
			);
			$select.css('min-width', $this.width().toString() + 'px');
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
	/**
	 * When a title is modified : save it as a 'custom article'
	 */
	$body.build('each', [article_header, '> h2.editing > input'], function()
	{
		$(this).data('callback', function() {
			var $input = this;
			if ($input.val().trim()) {
				$input.attr('name', 'save_name');
				$input.closest('form').submit();
			}
		});
	});

});
