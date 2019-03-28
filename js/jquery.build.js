(function($)
{

	/**
	 * this (global) static variable stores all wished build() callbacks
	 * key 1 : integer (priority) ; key 2 : arbitrary counter 0..n (push)
	 * always sorted by priority
	 */
	window.jquery_build_callback = {};

	//-------------------------------------------------------------------------------------- Callback
	/**
	 * Callback class constructor
	 *
	 * @constructor
	 * @param $context jQuery
	 * @param callback function
	 * @param priority number
	 * @param always   boolean
	 */
	var Callback = function($context, callback, priority, always)
	{
		this.callback  = callback;
		this.priority  = priority;
		this.selectors = {};

		var object    = this;
		var selectors = contextSelectors($context);
		$.each(selectors, function(key, part) {
			part = part.trim();
			object.selectors[part] = always
				? '@always'
				: part.replace(' ', '>').split('>').pop().trim();
		});
	};

	//------------------------------------------------------------------------------- Callback.callIt
	/**
	 * @param $context jQuery
	 */
	Callback.prototype.callIt = function($context)
	{
		var $elements = this.matchSelector($context);
		if ($elements.length) {
			$elements.inside = inside;
			this.callback.call($elements);
			delete $elements.inside;
		}
	};

	//------------------------------------------------------------------------ Callback.matchSelector
	/**
	 * @param $context jQuery context to test elements into
	 * @return jQuery the elements that match the selector (or an empty jQuery set object, length = 0)
	 */
	Callback.prototype.matchSelector = function($context)
	{
		var $result = $();
		$.each(this.selectors, function(selector, end_selector) {
			if (end_selector === '@always') {
				if ((selector === 'body') || $context.closest(selector).length) {
					$result = $result.add($context);
				}
			}
			else {
				var $elements = $context.find(end_selector).filter(selector);
				if ($elements.length) {
					$result = $result.add($elements);
				}
				$elements = $context.filter(selector);
				if ($elements.length) {
					$result = $result.add($elements);
				}

			}
		});
		return $result;
	};

	//------------------------------------------------------------------------------- contextSelector
	/**
	 * The jQuery.selector property contains a bad value when it has prevObjects : we must rebuild
	 * it correctly.
	 *
	 * @param $context jQuery
	 * @return array
	 */
	var contextSelectors = function($context)
	{
		var parts = [''];

		do {
			var new_parts = [];
			var selector  = $context.selector;
			if ($context.prevObject) {
				selector = selector.substr($context.prevObject.selector.length);
			}
			var selectors = selector.split(',');
			for (var part in selectors) if (selectors.hasOwnProperty(part)) {
				part = selectors[part];
				for (var child_part in parts) if (parts.hasOwnProperty(child_part)) {
					child_part = parts[child_part];
					new_parts.push(part + child_part);
				}
			}
			parts    = new_parts;
			$context = $context.prevObject;
		} while ($context);

		return parts;
	};

	//---------------------------------------------------------------------------------------- inside
	var inside = function(selector, nop)
	{
		// accepts '.a_class, .another' : take each of them
		var i = selector.indexOf(',');
		if (i > -1) {
			var selectors = selector.split(',');
			var result    = $();
			var obj       = this;
			$.each(selectors, function(index, value) {
				result = result.add(obj.inside(value.trim()));
			});
			return result;
		}
		// accepts '.my_class .sub_elements' selectors : .my_class for this working
		if (nop === undefined) {
			nop = true;
			i   = selector.indexOf(' ');
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
	 * Declare a new callback function to be called when DOM elements are added
	 * Then call build() on the head newly added DOM element, each time you add some, to call them
	 *
	 * @param callback function|object the callback function or { always, callback, priority }
	 * @return jQuery this
	 */
	$.fn.build = function(callback)
	{
		var $context = this;

		// add a callback function (sorted by priority)
		if (callback !== undefined) {
			var always   = (callback.always   === undefined) ? false     : callback.always;
			var priority = (callback.priority === undefined) ? undefined : callback.priority;
			if (callback.callback !== undefined) {
				callback = callback.callback;
			}
			var call_it = false;
			if ((priority === undefined) || priority) {
				call_it = true;
			}
			if ((priority === undefined) || (priority === false) || (priority === true)) {
				priority = 1000;
			}
			priority = (priority * 1000000) + Object.keys(window.jquery_build_callback).length;
			console.log($context);
			callback = new Callback($context, callback, priority, always);
			window.jquery_build_callback = keySortPush(window.jquery_build_callback, priority, callback);
			if (call_it && $context.length) {
				callback.callIt($context);
			}
		}

		// execute all callback functions
		else if ($context.length) {
			var callbacks = window.jquery_build_callback;
			for (var key in callbacks) if (callbacks.hasOwnProperty(key)) {
				callbacks[key].callIt($context);
			}
		}

		return this;
	};

})( jQuery );
