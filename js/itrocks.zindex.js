(function()
{

	let z_index_counter = 10

	//-------------------------------------------------------------------------------- zIndexOptimize
	/**
	 * Optimize z-index attributes on page
	 *
	 * @example z-indexes 1, 2, 10, 35 will be replaced by 1, 2, 3, 4, z_index_counter will be 4
	 */
	const zIndexOptimize = function()
	{
		// jQuery $element[integer z_index_attribute][integer counter_0_n]
		const elements = []
		// integer z_index_attribute[integer counter_0_n]
		const indexes = []
		$('[style*=z-index]').each(function () {
			const $element = $(this)
			const index    = parseInt($element.css('z-index'))
			if (elements[index] === undefined) {
				elements[index] = []
			}
			elements[index].push($element)
			indexes.push(index)
		})
		indexes.sort()
		z_index_counter = 0
		for (let index of indexes) {
			index = indexes[index]
			z_index_counter ++
			if (index === z_index_counter) {
				continue
			}
			for (let $element of elements[index]) {
				$element.css('z-index', z_index_counter)
			}
		}
	}

	//---------------------------------------------------------------------------------------- zIndex
	window.zIndex = function()
	{
		return z_index_counter
	}

	//------------------------------------------------------------------------------------- zIndexInc
	/**
	 * Increments z-index counter and return the new value
	 *
	 * @returns integer
	 */
	window.zIndexInc = function()
	{
		z_index_counter ++
		if (z_index_counter > 64) {
			setTimeout(zIndexOptimize)
		}
		return z_index_counter
	}

})()
