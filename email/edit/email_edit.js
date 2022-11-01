$(document).ready(function()
{

	const $body = $('body')

	//----------------------------------------------------------------------- article.email.edit each
	$body.build('each', 'article.email.edit', function()
	{
		const $article       = $(this)
		const hideRecipients = (property_name) =>
		{
			const $recipient = $article.find('li#' + property_name)
			if ($recipient.find('input.id').val()) {
				return
			}
			// hide recipient
			$recipient.hide()
			// create 'expand' button
			const $to        = $article.find('li#to')
			let   $collapsed = $to.find('.collapsed')
			if (!$collapsed.length) {
				$collapsed = $('<ul class="collapsed">')
				$to.children('div').append($collapsed)
			}
			const $button = $('<button>').attr('type', 'button').text($recipient.find('label').text())
			const $li     = $('<li>').addClass(property_name).append($button).appendTo($collapsed)
			// click 'expand' button : show recipient and hide button
			$button.click(() => {
				$recipient.show()
				$li.hide()
			})
		}
		hideRecipients('copy_to')
		hideRecipients('blind_copy_to')
	})

	//---------------------------------- article.email.edit copy_to, blind_copy_to button.minus click
	const minus_selector = ['article.email.edit', 'li#copy_to, li#blind_copy_to', 'button.minus']
	$body.build('click', minus_selector, function()
	{
		const $minus     = $(this)
		const $recipient = $minus.closest('li[id]')
		if (($recipient.find('button.minus').length > 1) || $recipient.find('input.id').val()) {
			return
		}
		const property_name = $recipient.attr('id')
		const $collapsed    = $minus.closest('.properties').find('li#to .collapsed')
		const $li           = $collapsed.find('.' + property_name)
		$recipient.hide()
		$li.show()
	})

})
