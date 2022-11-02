$(document).ready(function()
{
	const class_selector = /^\.([\w-]+)$/
	const id_selector    = /^#([\w-]+)$/
	const tag_selector   = /^[\w-]+$/

	//-------------------------------------------------------------------------------------------- $$
	window.$$ = function(element, selector)
	{
		let found
		return ((element === document) && id_selector.test(selector))
			? ((found = element.getElementById(RegExp.$1)) ? [found] : [])
			: Array.prototype.slice.call(
				class_selector.test(selector)
				? element.getElementsByClassName(RegExp.$1)
				: (
					tag_selector.test(selector)
					? element.getElementsByTagName(selector)
					: element.querySelectorAll(selector)
				)
			)
	}

})
