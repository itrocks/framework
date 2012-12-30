(function($) {

	//------------------------------------------------------------------------------------ classVar
	/**
	 * Read the value of a variable stored into the class attribute of the dom element
	 *
	 * Will return an undefined value if there is no stored class variable with this name
	 *
	 * @example
	 *   <input id="sample" class="count:10">
	 *   console.log($("#sample").classVar("count"));
	 *   will display : "10" 
	 * @param string var_name
	 * @return string
	 */
	$.fn.classVar = function(var_name)
	{
		var_name += ":";
		length = var_name.length;
		var classes = this.attr("class").split(" ");
		for (var i in classes) {
			if (classes[i].substr(0, length) == var_name) {
				return classes[i].substr(length);
			}
		}
	}

})( jQuery );
