(function($)
{

	// this (global) static variable stores all wished build() callbacks
	window.jquery_build_callback = [];

	//----------------------------------------------------------------------------------------- build
	/**
	 * Call build(callback) what callback functions you want to be called for future added dom elements
	 * call this.build() after you add dom elements (ie dynamic javascript add, ajax calls) to apply the same changes
	 *
	 * @param callback function the callback function
	 * @param call_now boolean optional default true
	 * @return jQuery
	 */
	$.fn.build = function (callback, call_now)
	{
		// use this.in(selector) in callback to build the elements
		this.in = function(selector, nop)
		{
			// accepts ".aclass, .another" : take each of them
			var i = selector.indexOf(",");
			if (i > -1) {
				var selectors = selector.split(",");
				var result = $();
				var obj = this;
				$.each(selectors, function(index, value) { result = result.add(obj.in(value.trim())); });
				return result;
			}
			// accepts ".myclass .subelems" selectors : .myclass for this working
			if (nop == undefined) {
				nop = true;
				i = selector.indexOf(" ");
				if (i > -1) {
					return this.in(selector.substr(0, i), nop).find(selector.substr(i + 1));
				}
			}
			// filtered object itself, added to find into it's children
			return this.filter(selector).add(this.find(selector));
		};
		//
		if (callback != undefined) {
			// add a callback function
			window.jquery_build_callback.push(callback);
			if ((call_now == undefined) || call_now) {
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
				delete this.tmpBuildCaller;
			}
		}
		else {
			// execute all callback functions
			for (var key in jquery_build_callback) if (jquery_build_callback.hasOwnProperty(key)) {
				callback = window.jquery_build_callback[key];
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
			}
			delete this.tmpBuildCaller;
		}
		delete this.in;
		return this;
	}

})( jQuery );
