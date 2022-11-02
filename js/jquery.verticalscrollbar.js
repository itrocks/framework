(function($)
{

	$.fn.verticalscrollbar = function()
	{

		//------------------------------------------------------------------------- .vertical.scrollbar
		// vertical scrollbar
		this.each(function()
		{
			const $this     = $(this)
			const $up       = $this.children('.up')
			const $position = $this.children('.position')
			const $down     = $this.children('.down')

			const data_start   = $this.data('start') - 1
			const data_length  = $this.data('length')
			const data_total   = $this.data('total')

			const tr_height        = $this.closest('tr').height() - 1
			const scrollbar_height = Math.max((tr_height * data_length), $this.height())
			const position_height  = Math.max(Math.round((data_length * scrollbar_height) / data_total), 15)
			const scroll_height    = scrollbar_height - position_height
			let   position_start   = (data_total - data_length > 0)
				? Math.round((data_start * scroll_height) / (data_total - data_length))
				: 0

			if ((position_start + position_height) > scrollbar_height) {
				position_start = position_height - scrollbar_height
			}
			const end_height = scrollbar_height - position_start - position_height

			$up.height(position_start)
			$position.height(position_height)
			$down.height(end_height)
		})

		this.find('.position').each(function()
		{
			const $this = $(this)
			$this.draggable({
				containment: $this.parent(),
				opacity: .5,

				stop: function()
				{
					const $this      = $(this)
					const $scrollbar = $this.parent()

					const old_start   = $scrollbar.data('start')
					const data_length = $scrollbar.data('length')
					const data_total  = $scrollbar.data('total')

					const scrollbar_height = $scrollbar.innerHeight() - 1
					const position_height  = $scrollbar.children('.position').outerHeight() - 1
					const scroll_height    = scrollbar_height - position_height

					const position_start
						= $scrollbar.children('.up').height() + parseInt($this.css('top').repl('px', ''))
					const new_start
						= Math.round(((position_start * (data_total - data_length)) / scroll_height)) + 1

					if (new_start !== old_start) {
						$this.attr('href', $this.attr('href').repl('=' + old_start, '=' + new_start))
						$this.click()
					}
				}

			})
		})

		return this
	}

})( jQuery )
