(function()
{

	var z_index_counter = 10;

	//-------------------------------------------------------------------------------- zIndexOptimize
	/**
	 * Optimize z-index attributes on page
	 *
	 * @example z-indexes 1, 2, 10, 35 will be replaced by 1, 2, 3, 4, z_index_counter will be 4
	 */
	var zIndexOptimize = function()
	{
		// jQuery $element[integer z_index_attribute][integer counter_0_n]
		var elements = [];
		// integer z_index_attribute[integer counter_0_n]
		var indexes = [];
		$('[style*=z-index]').each(function () {
			var $element = $(this);
			var index    = parseInt($element.css('z-index'));
			if (elements[index] === undefined) {
				elements[index] = [];
			}
			elements[index].push($element);
			indexes.push(index);
		});
		indexes.sort();
		z_index_counter = 0;
		for (var index in indexes) if (indexes.hasOwnProperty(index)) {
			index = indexes[index];
			z_index_counter ++;
			if (index === z_index_counter) {
				continue;
			}
			for (var $element in elements[index]) if (elements[index].hasOwnProperty($element)) {
				$element = elements[index][$element];
				$element.css('z-index', z_index_counter);
			}
		}
	};

	//---------------------------------------------------------------------------------------- zIndex
	window.zIndex = function()
	{
		return z_index_counter;
	};

	//------------------------------------------------------------------------------------- zIndexInc
	/**
	 * Increments z-index counter and return the new value
	 *
	 * @returns integer
	 */
	window.zIndexInc = function()
	{
		z_index_counter ++;
		if (z_index_counter > 64) {
			setTimeout(zIndexOptimize);
		}
		return z_index_counter;
	};

})();
