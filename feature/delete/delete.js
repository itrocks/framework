$(document).ready(function()
{

	let closeWindows = function(class_name, identifier)
	{
		let selector = 'article[data-class=' + class_name.repl(BS, BS + BS) + ']'
		if (identifier !== undefined) {
			selector =  selector.substr(7)
			selector += parseInt(identifier) ? ('[data-id=' + identifier + ']') : identifier
		}

		// close main window
		let $main = $('main, #main')
		let $main_window = $main.children(selector)
		if ($main_window.length) {
			let $close_anchor = $main_window.find('.actions > .close > a')
			if ($close_anchor.length) {
				$close_anchor.addClass('keep-response')
				$close_anchor.click()
			}
			else if (!parseInt(identifier)) {
				refresh($main)
			}
			else if (identifier !== undefined) {
				$main_window.remove()
			}
			else {
				refresh($main)
			}
		}

		// close all articles, popup windows, items matching data-class and data-id
		if (selector.includes('[data-id=')) {
			$(selector + ':not(.deleted)').remove()
			$(selector.repl('][data-id=', '] > [data-id=')).remove()
		}
	}

	//--------------------------------------------------------------------- .confirmed.delete.message
	$('body').build('call', '#responses > .deleted[data-class]', function()
	{
		let $message       = this
		let class_name     = $message.data('class')
		let identifier     = $message.data('id')
		let set_class_name = $message.data('set')
		if (!class_name) {
			return
		}
		closeWindows(class_name, '.list')
		if (identifier) {
			closeWindows(class_name, identifier)
		}
		if (set_class_name) {
			closeWindows(set_class_name)
		}

		// unselect
		$message.find('ul.deleted > li[data-id]').each(function() {
			let id = $(this).data('id')
			unselectFromList(class_name, id)
		})
	})

})
