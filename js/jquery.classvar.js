(function($)
{

	/**
	 * Read the value of a variable stored into the class attribute of the dom element
	 *
	 * Will return an undefined value if there is no stored class variable with this name
	 *
	 * @examples
	 *   <input id="sample" class="count:10">
	 *   console.log($('#sample').classVar('count')); // will display : '10'
	 *   $('#sample'].classVar('new', 1);             // will add a 'new:1' class
	 * @param var_name string
	 * @param set_value string
	 * @return string|undefined
	 */
	$.fn.classVar = function(var_name, set_value)
	{
		var_name     += ':'
		const length  = var_name.length
		const classes = this.attr('class').split(' ')
		for (const i in classes) if (classes.hasOwnProperty(i)) {
			if (classes[i].substring(0, length) === var_name) {
				if (set_value !== undefined) {
					const replace = (i > 0) ? ' ' + classes[i] : (
						(i < classes.length - 1) ? classes[i] + ' ' : classes[i]
					)
					this.attr('class', this.attr('class').repl(replace, ''))
				}
				else {
					return classes[i].substring(length)
				}
			}
		}
		if (set_value !== undefined) {
			if ((this.attr('class') !== undefined) && (this.attr('class') !== '')) {
				this.attr('class', this.attr('class') + ' ' + var_name + set_value)
			}
			else {
				this.attr('class', var_name + set_value)
			}
			return this
		}

		return undefined
	}

})( jQuery )
