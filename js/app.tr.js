(function() {

	const cache = {}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translate text using options
	 *
	 * - if options is a single numeric value :
	 *   this value will replace $1 into the text
	 *   AND this value decides if we look for the singular / plural translation
	 *   while adding '*' to the context if plural
	 *
	 * - asynchronous mode only if callback is set
	 *
	 * @param text     string
	 * @param context  string the translation context (eg class name)
	 * @param options  mixed translation options
	 * @param callback callable the function to call when the translation has been read (asynchronous)
	 */
	window.tr = function(text, context, options, callback)
	{
		let result

		if (context === undefined) {
			context = ''
		}

		// numeric option : replaces the $1 element in the text
		if (!isNaN(options)) {
			if (options > 1) {
				context += '*'
			}
		}

		if (cache[context] === undefined) {
			cache[context] = {}
		}
		else if (cache[context][text] !== undefined) {
			if (callback === undefined) {
				result = cache[context][text]
				if (!isNaN(options)) {
					result = result.repl('$1', options)
				}
				return result
			}
			else {
				callback(cache[context][text])
			}
		}

		// common call settings
		const call_settings = {
			data: { 'text': text, 'context': context },
			url:  window.app.uri_base + '/ITRocks/Framework/Locale/translate'
		}

		// no callback => synchronous call
		if (callback === undefined) {
			call_settings['async']   = false
			call_settings['error']   = ()     => { result = text }
			call_settings['success'] = (data) => { result = data; cache[context][text] = result }
		}

		// callback is set => asynchronous call
		else {
			call_settings['error']   = () => callback(text)
			call_settings['success'] = (result) => {
				cache[context][text] = result
				if (!isNaN(options)) {
					result = result.repl('$1', options)
				}
				callback(result)
			}
		}

		// call
		$.post(call_settings)

		// no callback => return result
		if (callback === undefined) {
			if (!isNaN(options)) {
				result = result.repl('$1', options)
			}
			return result
		}
	}

})()
