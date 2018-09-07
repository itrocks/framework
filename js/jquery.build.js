(function($)
{

	/**
	 * this (global) static variable stores all wished build() callbacks
	 * key 1 : integer (priority) ; key 2 : arbitrary counter 0..n (push)
	 * always sorted by priority
	 */
	window.jquery_build_callback = {};

	//----------------------------------------------------------------------------------- keySortPush
	/**
	 * This function allow to push an element into an array, with real-time sort by key
	 *
	 * The array must already be sorted by key before calling keySortPush, or it will not work well
	 *
	 * @example
	 * keySortPush({1: 'one', 10: 'ten', 11: 'eleven'}, 5, 'five')
	 * => {1: 'one', 5: 'five', 10: 'ten', 11: 'eleven'}
	 * @param object
	 * @param key
	 * @param value
	 */
	var keySortPush = function(object, key, value)
	{
		var array = object;
		object = {};
		for (var array_key in array) if (array.hasOwnProperty(array_key)) {
			if ((key !== undefined) && (array_key > key)) {
				object[key] = value;
				key         = undefined;
			}
			object[array_key] = array[array_key];
		}
		if (key !== undefined) {
			object[key] = value;
		}
		return object;
	};

	//----------------------------------------------------------------------------------------- build
	/**
	 * Call build(callback) what callback functions you want to be called for future added dom elements
	 * call this.build() after you add dom elements (ie dynamic javascript add, ajax calls) to apply the same changes
	 *
	 * @param callback function the callback function
	 * @param priority boolean|integer optional : if boolean, set priority to 1000. default is true
	 * @return jQuery
	 */
	$.fn.build = function (callback, priority)
	{
		// use this.inside(selector) in callback to build the elements
		this.inside = function(selector, nop)
		{
			// accepts '.aclass, .another' : take each of them
			var i = selector.indexOf(',');
			if (i > -1) {
				var selectors = selector.split(',');
				var result = $();
				var obj = this;
				$.each(selectors, function(index, value) { result = result.add(obj.inside(value.trim())); });
				return result;
			}
			// accepts '.myclass .subelems' selectors : .myclass for this working
			if (nop === undefined) {
				nop = true;
				i = selector.indexOf(' ');
				var i2 = selector.indexOf('>');
				if ((i2 > -1) && ((i === -1) || i2 < i)) {
					i = i2;
				}
				if (i > -1) {
					return this.inside(selector.substr(0, i), nop).find(selector.substr(i));
				}
			}
			// filtered object itself, added to find into it's children
			return this.filter(selector).add(this.find(selector));
		};

		// add a callback function (sorted by priority)
		if (callback !== undefined) {
			if ((priority === undefined) || priority) {
				callback.call(this);
			}
			if ((priority === undefined) || (priority === false) || (priority === true)) {
				priority = 1000;
			}
			priority = (priority * 1000000) + Object.keys(window.jquery_build_callback).length;
			window.jquery_build_callback = keySortPush(window.jquery_build_callback, priority, callback);
		}

		// execute all callback functions
		else {
			var callbacks = window.jquery_build_callback;
			for (var key in callbacks) if (callbacks.hasOwnProperty(key)) {
				callbacks[key].call(this);
			}
		}

		delete this.inside;
		return this;
	};

})( jQuery );
