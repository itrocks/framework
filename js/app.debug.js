$(document).ready(function()
{
	return

	const DEBUG = ['fadeOut']

	if (DEBUG.indexOf('empty') > -1) {
		const $_empty = $.fn.empty
		$.fn.empty = function () {
			console.debug('$.empty()')
			console.trace()
			return $_empty.call(this)
		}
	}

	if (DEBUG.indexOf('fadeOut') > -1) {
		const $_fadeOut = $.fn.fadeOut
		$.fn.fadeOut = function (duration, complete) {
			console.debug('$.fadeOut()')
			console.trace()
			return $_fadeOut.call(this, duration, complete)
		}
	}

	if (DEBUG.indexOf('remove') > -1) {
		const $_remove = $.fn.remove
		$.fn.remove = function (selector) {
			console.debug('$.remove()')
			console.trace()
			return $_remove.call(this, selector)
		}
	}

})
