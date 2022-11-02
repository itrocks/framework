$(document).ready(function()
{

	//----------------------------------------------------------------------- #modal .actions a click
	$('body').build('call', '#modal .actions', function()
	{
		const $actions = $(this)

		$actions.find('a').click(function()
		{
			const $this = $(this)
			setTimeout(() => $this.closest('#modal').empty())
		})

		$actions.find('> .close > a').click(function(event)
		{
			const $this = $(this)
			setTimeout(() => $this.closest('#modal').empty())
			event.preventDefault()
			event.stopImmediatePropagation()
		})
	})

	//----------------------------------------------------------------------------------- modalWindow
	window.modalWindow = function(title, text, choices, callback)
	{
		let $modal = $('#modal')
		if (!$modal.length) {
			$modal = $('<div id="modal">').appendTo($('body'))
		}
		const $article = $(
			'<article>'
			+ '<header><h2>' + title + '</h2></header>'
			+ '<div><p>' + text + '</p></div>'
			+ '</article>'
		)
		const $actions = $('<ul>').addClass('actions')
		for (const choice in choices) if (choices.hasOwnProperty(choice)) {
			$actions.append(
				$('<li>').addClass(choice).append($('<a>').text(choices[choice]).data('choice', choice))
			)
		}
		$article.append($actions)
		$modal.empty().append($article).build()
		if (callback) {
			$article.find('.actions a').click(function() {
				callback.call(this, $(this).data('choice'))
			})
		}
	}

})
