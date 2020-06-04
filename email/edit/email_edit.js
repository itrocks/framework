$(document).ready(function()
{

	var $body = $('body');

	//----------------------------------------------------------------------- article.email.edit each
	$body.build('each', 'article.email.edit', function()
	{
		var $article = $(this);
		var hideRecipients = function(property_name)
		{
			var $recipient = $article.find('li#' + property_name);
			if ($recipient.find('input.id').val()) {
				return;
			}
			// hide recipient
			$recipient.hide();
			// create 'expand' button
			var $to        = $article.find('li#to');
			var $collapsed = $to.find('.collapsed');
			if (!$collapsed.length) {
				$collapsed = $('<ul class="collapsed">');
				$to.children('div').append($collapsed);
			}
			var $button = $('<button>').attr('type', 'button').text($recipient.find('label').text());
			var $li     = $('<li>').addClass(property_name).append($button).appendTo($collapsed);
			// click 'expand' button : show recipient and hide button
			$button.click(function() {
				$recipient.show();
				$li.hide();
			});
		}
		hideRecipients('copy_to');
		hideRecipients('blind_copy_to');
	});

	//---------------------------------- article.email.edit copy_to, blind_copy_to button.minus click
	var minus_selector = ['article.email.edit', 'li#copy_to, li#blind_copy_to', 'button.minus'];
	$body.build('click', minus_selector, function()
	{
		var $minus     = $(this);
		var $recipient = $minus.closest('li[id]');
		if (($recipient.find('button.minus').length > 1) || $recipient.find('input.id').val()) {
			return;
		}
		var property_name = $recipient.attr('id');
		var $collapsed    = $minus.closest('.properties').find('li#to .collapsed')
		var $li           = $collapsed.find('.' + property_name);
		$recipient.hide();
		$li.show();
	})

});
