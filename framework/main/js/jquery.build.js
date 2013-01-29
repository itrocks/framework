(function($) {

	// this (global) static variable stores all wished build() callbacks
	window.jquery_build_callback = [];

	//--------------------------------------------------------------------------------------- build
	/**
	 * Call build(callback) what callback functions you want to be called for future added dom elements
	 * call this.build() after you add dom elements (ie dynamic javascript add, ajax calls) to apply the same changes
	 *
	 * @param callback function the callback function
	 * @param call_now boolean optional default true
	 */
	$.fn.build = function (callback, call_now)
	{
		call_now = call_now || true;
		if (callback != undefined) {
			window.jquery_build_callback.push(callback);
			if ((call_now == undefined) || call_now) {
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
				delete this.tmpBuildCaller;
			}
		}
		else {
			for (var key in jquery_build_callback) {
				callback = window.jquery_build_callback[key];
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
			}
			delete this.tmpBuildCaller;
		}
	}

})( jQuery );
