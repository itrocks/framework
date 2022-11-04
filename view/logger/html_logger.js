$(document).ready(function()
{

	$('body').build('call', 'iframe[data-from]', function() {
		const $iframe = this
		const $from   = $('#' + $iframe.data('from'))
		if (!$from.length) {
			return
		}
		const document = $iframe.get(0).contentWindow.document
		document.open()
		// noinspection HtmlRequiredTitleElement Gets current head content, including title
		document.write(
			'<!DOCTYPE HTML>' + LF
			+ '<html lang=' + DQ + $('html').attr('lang') + DQ + '>'
			+ '<head>' + $('head').html() + '</head>'
			+ '<body><main>' + LF + LF
			+ $from.html()
			+ LF + LF + '</main></body></html>'
		)
		document.close()
		$from.remove()
	})

})
