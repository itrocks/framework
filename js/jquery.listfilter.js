(function($)
{
	$.fn.listFilter = function()
	{

		//--------------------------------------------------------------------------------------- keyup
		this.keyup(function()
		{
			const $input = $(this)
			const text   = $input.val()
			$input.parent().find('li').each(function()
			{
				const $li = $(this)
				if ($li.text().toLowerCase().indexOf(text.toLowerCase()) > -1) {
					$li.show()
				}
				else {
					$li.hide()
				}
			})
		})

		const $parent = this.parent()

		//----------------------------------------------------------------------------- input.all click
		$parent.find('input.all').click(function()
		{
			const $input  = $(this)
			const $parent = $input.parent()
			$parent.find('input[type=checkbox]').attr('checked', true)
			$input.hide()
			$parent.find('input.none').show()
		})

		//---------------------------------------------------------------------------- input.none click
		$parent.find('input.none').click(function()
		{
			const $input  = $(this)
			const $parent = $input.parent()
			$parent.find('input[type=checkbox]').attr('checked', false)
			$input.hide()
			$parent.find('input.all').show()
		})

		$parent.find($parent.find('input[checked]').length ? 'input.none' : 'input.all').hide()

	}
})( jQuery )
