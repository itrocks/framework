(function($)
{

	/**
	 * this (global) static variable stores all wished build() callbacks
	 * key 1 : integer (priority) ; key 2 : arbitrary counter 0..n (push)
	 * always sorted by priority
	 */
	let jquery_build_callback = {}

	//-------------------------------------------------------------------------------------- Callback
	/**
	 * Callback class constructor
	 *
	 * @constructor
	 * @param event      string
	 * @param selector   string|string[]
	 * @param callback   function
	 * @param priority   number
	 * @param always     boolean
	 * @param parameters array|mixed
	 */
	const Callback = function(event, selector, callback, priority, always, parameters)
	{
		this.callback   = callback
		this.event      = event
		this.parameters = (parameters === undefined)
			? null
			: (((typeof parameters) === 'array') ? parameters : [parameters])
		this.priority   = priority
		this.selectors  = {}

		const object    = this
		const selectors = chainedSelectors(selector)
		$.each(selectors, function(key, part) {
			part = part.trim()
			object.selectors[part] = always
				? 'always'
				: part.repl(' ', '>').split('>').pop().trim()
		})
	}

	//------------------------------------------------------------------------------- Callback.callIt
	/**
	 * @param $context jQuery
	 */
	Callback.prototype.callIt = function($context)
	{
		const $elements = this.matchSelector($context)
		if ($elements.length) {
			if (this.event === 'call') {
				this.callback.apply($elements, this.parameters)
			}
			else if (this.event === 'each') {
				$elements.each(this.callback)
			}
			else {
				$elements.on(this.event, this.callback)
			}
		}
	}

	//------------------------------------------------------------------------ Callback.matchSelector
	/**
	 * @param $context jQuery context to test elements into
	 * @return jQuery the elements that match the selector (or an empty jQuery set object, length = 0)
	 */
	Callback.prototype.matchSelector = function($context)
	{
		let $result = $()
		$.each(this.selectors, function(selector, end_selector) {
			if (end_selector === 'always') {
				if ((selector === 'body') || (selector === 'always') || $context.closest(selector).length) {
					$result = $result.add($context)
				}
				return;
			}
			let $elements = $context.find(end_selector).filter(selector)
			if ($elements.length) {
				$result = $result.add($elements)
			}
			$elements = $context.filter(selector)
			if ($elements.length) {
				$result = $result.add($elements)
			}
		})
		return $result
	}

	//------------------------------------------------------------------------------- contextSelector
	/**
	 * Change ['tag', 'subTag1, subTag2', '> tag3'] to ['tag subTag1 > tag3', 'tag subTag2 > tag3']
	 * selectors
	 *
	 * @param selector string|string[]
	 * @return array
	 */
	window.chainedSelectors = function(selector)
	{
		const selectors = Array.isArray(selector) ? selector : [selector]
		let   parts     = ['']
		for (selector of selectors) {
			const add_parts = selector.split(',')
			const new_parts = []
			const old_parts = parts
			for (const add_part of add_parts) {
				for (const old_part of old_parts) {
					new_parts.push((old_part + ' ' + add_part).trim().repl('  ', ' '))
				}
			}
			parts = new_parts
		}
		return parts
	}

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
	const keySortPush = function(object, key, value)
	{
		const array = object
		object = {}
		for (const array_key in array) if (array.hasOwnProperty(array_key)) {
			if ((key !== undefined) && (array_key > key)) {
				object[key] = value
				key         = undefined
			}
			object[array_key] = array[array_key]
		}
		if (key !== undefined) {
			object[key] = value
		}
		return object
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Declare a new callback function to be called when DOM elements are added
	 * Then call build() on the head newly added DOM element, each time you add some, to call them
	 *
	 * @param event      string event name : jQuery event and 'call', 'each' special events
	 *                   or : an object with {event:, priority:, selector:, callback: }
	 * @param selector   string|string[]
	 * @param callback   function
	 * @param parameters object
	 * @return jQuery this
	 */
	$.fn.build = function(event, selector, callback, parameters)
	{
		const $context = this

		// execute all callback functions
		if (event === undefined) {
			if ($context.length) {
				for (const callback of Object.values(jquery_build_callback)) {
					callback.callIt($context)
				}
			}
			return this
		}

		// add a callback function
		let always   = false
		let priority = 1000
		if (callback === undefined) {
			if (event.always !== undefined) {
				always = event.always
			}
			if (event.callback !== undefined) {
				callback = event.callback
			}
			if (
				(event.priority !== undefined) && (event.priority !== false) && (event.priority !== true)
			) {
				priority = event.priority
			}
			if (event.selector !== undefined) {
				selector = event.selector
			}
			event = (event.event === undefined) ? 'call' : event.event
		}
		if (selector === undefined) {
			selector = 'always'
		}
		if (((typeof parameters) === 'object') && parameters.priority) {
			priority = parameters.priority
			delete parameters.priority
			if (!Object.keys(parameters).length) {
				parameters = undefined
			}
		}
		priority = (priority * 1000000) + Object.keys(jquery_build_callback).length
		callback = new Callback(event, selector, callback, priority, always, parameters)
		jquery_build_callback = keySortPush(jquery_build_callback, priority, callback)
		if ($context.length) {
			callback.callIt($context)
		}

		return this
	}

})( jQuery )
