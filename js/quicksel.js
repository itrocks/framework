$(document).ready(function()
{
	var class_selector = /^\.([\w-]+)$/;
	var id_selector    = /^#([\w-]+)$/;
	var tag_selector   = /^[\w-]+$/;

	//-------------------------------------------------------------------------------------------- $$
	window.$$ = function(element, selector)
	{
		var found;
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
			);
	}

});
