$(document).ready(function()
{

	const closeWindows = function(class_name, identifier)
	{
		let selector = 'article[data-class=' + class_name.repl(BS, BS + BS) + ']'
		if (identifier !== undefined) {
			selector  = selector.substring(7)
			selector += parseInt(identifier) ? ('[data-id=' + identifier + ']') : identifier
		}

		// close main window
		const $main        = $('main, #main')
		const $main_window = $main.children(selector)
		if ($main_window.length) {
			const $close_anchor = $main_window.find('.actions > .close > a')
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
			const $sources = [
				$(selector + ':not(.deleted)'),
				$(selector.repl('][data-id=', '] > [data-id='))
			]
			for (const $source of $sources) {
				const $replace = $source.closest('[data-delete-replace-by]')
				if ($replace.length) {
					const $element = $($replace.data('delete-replace-by'))
					$source.each(function() {
						const $source = $(this);
						if ($source.next().length) {
							$element.insertBefore($source.next());
						}
						else {
							$source.parent().append($element);
						}
						$element.build()
					})
				}
				$source.remove()
			}
		}
	}

	//--------------------------------------------------------------------- .confirmed.delete.message
	$('body').build('call', '#responses > .deleted[data-class]', function()
	{
		const $message       = this
		const class_name     = $message.data('class')
		const identifier     = $message.data('id')
		const set_class_name = $message.data('set')
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
			const id = $(this).data('id')
			unselectFromList(class_name, id)
		})
	})

})
