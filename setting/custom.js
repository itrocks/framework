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

	$body.build('call', [article_header, '.custom.select li[data-class][data-id]'], function()
	{
		this.draggable({
			appendTo: 'body',
			containment: 'body',
			cursorAt: { left: 10, top: 10 },
			scroll: false,
			drag: function(event, ui) {
				var $helper = $(ui.helper);
				var $inside = undefined;
				$('article').add('nav#menu').each(function() {
					var $element  = $(this);
					var offset    = $element.offset();
					offset.right  = offset.left + $element.width();
					offset.bottom = offset.top + $element.height();
					if (
						(event.pageX >= offset.left) && (event.pageX < offset.right)
						&& (event.pageY >= offset.top) && (event.pageY < offset.bottom)
					) {
						$inside = $element;
						return false;
					}
				});
				$inside
					? $helper.addClass('inside').removeClass('outside')
					: $helper.addClass('outside').removeClass('inside');
				$helper.data('inside', $inside);
			},
			stop: function(event, ui) {
				var $li = $(this);
				var $helper = $(ui.helper);
				if ($helper.hasClass('outside')) {
					var href = app.uri_base
						+ SL + $li.data('class').repl(BS, SL)
						+ SL + $li.data('id')
						+ SL + 'delete'
						+ '?confirm=1';
					$li.remove();
					redirectLight(href, '#responses');
				}
				else if ($helper.data('inside').is('article')) {
					$li.find('a').click();
				}
				$('body').click();
			},
			helper: function() {
				var $this = $(this);
				return $('<div class="custom select helper">')
					.css('z-index', zIndexInc())
					.text($this.text());
			}
		});
	});

});
