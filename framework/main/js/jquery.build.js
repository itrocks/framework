(function($) {

	// this (global) static variable stores all wished build() callbacks
	jquery_build_callback = new Array();

	//--------------------------------------------------------------------------------------- build
	/**
	 * Call build(callback) what callback functions you want to be called for future added dom elements
	 * call this.build() after you add dom elements (ie dynamic javascript add, ajax calls) to apply the same changes
	 *
	 * @param callback function the callback function
	 * @param call_now boolean default is true
	 */
	$.fn.build = function(callback, call_now)
	{
		if (callback != undefined) {
			jquery_build_callback.push(callback);
			if ((call_now == undefined) || call_now) {
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
				delete this.tmpBuildCaller;
			}
		}
		else {
			for (var key in jquery_build_callback) {
				callback = jquery_build_callback[key];
				this.tmpBuildCaller = callback;
				this.tmpBuildCaller();
			}
			delete this.tmpBuildCaller;
		}
	}

})( jQuery );
