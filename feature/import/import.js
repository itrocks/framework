$(document).ready(function()
{
	//------------------------------------------------------------------------ article.import.preview
	$('body').build('each', 'article.import.preview .block', function()
	{
		const $this    = $(this)
		const $section = $this.find('> section:has(ul)')
		const $li      = $section.find('> ul > li')

		//------------------------------------------------------- li.draggable, .properties.droppable
		/**
		 * drag and drop property names inside imported class settings
		 */
		$li.draggable({
			containment: $this
		})
		$section.droppable({
			accept: $li,

			drop: function(event, ui)
			{
				// drop
				const $draggable = ui.draggable
				const $droppable = $(this)
				// old section value
				let   $input        = $draggable.closest('section').find('input')
				const property_name = $draggable.attr('class').lParse(SP)
				const new_val       = (',' + $input.val() + ',').repl(',' + property_name + ',', ',')
				$input.val((new_val === ',') ? '' : new_val.substring(1, new_val.length - 1))
				// move property
				$draggable.appendTo($droppable.find('ul')).removeAttr('style')
				// new section value
				$input = $($droppable.closest('section').find('input'))
				$input.val($input.val() + ($input.val() ? ',' : '') + property_name)
			}
		})

		//----------------------------------------------------------------------------- select.change()
		// change color of 'if no value found'
		$this.find('select').change(function()
		{
			const $this = $(this)
			$this.attr('class', $this.find(':selected').attr('value'))
		}).change()

	})
})
