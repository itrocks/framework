(function() {

	var cache = {};

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
		if (context === undefined) {
			context = '';
		}

		// numeric option : replaces the $1 element in the text
		if (!isNaN(options)) {
			text = text.replace('$1', options);
			if (options > 1) {
				context += '*';
			}
		}

		if (cache[context] === undefined) {
			cache[context] = {};
		}
		else if (cache[context][text] !== undefined) {
			if (callback === undefined) {
				return cache[context][text];
			}
			else {
				callback(cache[context][text]);
			}
		}

		// common call settings
		var call_settings = {
			data: { 'text': text, 'context': context },
			url:  window.app.uri_base + '/ITRocks/Framework/Locale/translate'
		};
		var result = undefined;

		// no callback => synchronous call
		if (callback === undefined) {
			call_settings['async']   = false;
			call_settings['error']   = function()     { result = text; };
			call_settings['success'] = function(data) { result = data; cache[context][text] = result; };
		}

		// callback is set => asynchronous call
		else {
			call_settings['error']   = function() { callback(text); };
			call_settings['success'] = function(result) {
				cache[context][text] = result;
				callback(result);
			};
		}

		// call
		$.post(call_settings);

		// no callback => return result
		if (callback === undefined) {
			return result;
		}
	};

})();
