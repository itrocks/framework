$(document).ready(function()
{

	var closeWindows = function(class_name, identifier)
	{
		var selector = 'article[data-class=' + class_name.repl(BS, BS + BS) + ']';
		if (identifier !== undefined) {
			if (parseInt(identifier)) {
				selector += '[data-id=' + identifier + ']';
			}
			else {
				selector += identifier;
			}
		}

		// close main window
		var $main = $('main, #main');
		var $main_window = $main.children(selector);
		if ($main_window.length) {
			var $close_anchor = $main_window.find('.actions > .close > a');
			if ($close_anchor.length) {
				$close_anchor.addClass('keep-response');
				$close_anchor.click();
			}
			else if (!parseInt(identifier)) {
				refresh($main);
			}
			else if (identifier !== undefined) {
				$main_window.remove();
			}
			else {
				refresh($main);
			}
		}

		// close popup windows
		$('div.popup > ' + selector).remove();
	};

	//--------------------------------------------------------------------- .confirmed.delete.message
	$('body').build('call', '#responses > .deleted[data-class]', function()
	{
		var $message       = this;
		var class_name     = $message.data('class');
		var identifier     = $message.data('id');
		var set_class_name = $message.data('set');
		if (!class_name) {
			return;
		}
		closeWindows(class_name, '.list');
		if (identifier) {
			closeWindows(class_name, identifier);
		}
		if (set_class_name) {
			closeWindows(set_class_name);
		}

		// unselect
		$message.find('ul.deleted > li[data-id]').each(function() {
			var id = $(this).data('id');
			unselectFromList(class_name, id);
		});
	});

});
