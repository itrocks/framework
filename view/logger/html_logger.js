$(document).ready(function()
{

	$('body').build('call', 'iframe[data-from]', function() {
		var $iframe = this;
		var $from   = $('#' + $iframe.data('from'));
		if (!$from.length) {
			return;
		}
		var document = $iframe.get(0).contentWindow.document;
		document.open();
		document.write(
			'<!DOCTYPE HTML>' + LF
			+ '<html lang=' + DQ + $('html').attr('lang') + DQ + '>'
			+ '<head>' + $('head').html() + '</head>'
			+ '<body><main>' + LF + LF
			+ $from.html()
			+ LF + LF + '</main></body></html>'
		);
		document.close();
		$from.remove();
	});

});
