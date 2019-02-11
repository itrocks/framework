$('document').ready(function()
{

	$('.confirmed.delete.message').build(function()
	{
		var $message = this.inside('.confirmed.delete.message');
		$message = $message.length ? $message : this;
		if (!$message.closest('.confirmed.delete.message').length) {
			return;
		}
		$message = $message.closest('.confirmed.delete.message');

		var class_name = $message.data('class');
		var identifier = $message.data('id');
		if (!class_name || !identifier) {
			return;
		}
		var selector = '[data-class=' + class_name.repl(BS, BS + BS) + '][data-id=' + identifier + ']';

		// close main window
		var $main_window = $('#main').children(selector);
		if ($main_window.length) {
			var $close_anchor = $main_window.find('.actions > .close > a');
			if ($close_anchor.length) {
				$close_anchor.click();
			}
			else {
				$main_window.remove();
			}
		}

		// close popup windows
		$('.closeable-popup > .window' + selector).remove();
	});

});
