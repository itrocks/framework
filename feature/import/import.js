$(document).ready(function()
{
	//------------------------------------------------------------------------ article.import.preview
	$('body').build('each', 'article.import section.preview li.block', function()
	{
		var $this    = $(this);
		var $section = $this.find('> section:has(ul)');
		var $li      = $section.find('> ul > li');

		//------------------------------------------------------- li.draggable, .properties.droppable
		/**
		 * drag and drop property names inside imported class settings
		 */
		$li.draggable({
			containment: $this
		});
		$section.droppable({
			accept: $li,

			drop: function(event, ui)
			{
				// drop
				var $draggable = ui.draggable;
				var $droppable = $(this);
				$draggable.removeAttr('style').appendTo($droppable.find('ul'));
				// old section value
				var $input        = $draggable.closest('section').find('input');
				var property_name = $draggable.attr('class').lParse(SP);
				var new_val       = (',' + $input.val() + ',').repl(',' + property_name + ',', ',');
				$input.val((new_val === ',') ? '' : new_val.substr(1, new_val.length - 2));
				// new section value
				$input = $($droppable.closest('section').find('input'));
				$input.val($input.val() + ($input.val() ? ',' : '') + property_name);
			}
		});

		//----------------------------------------------------------------------------- select.change()
		// change color of 'if no value found'
		$this.find('select').change(function()
		{
			var $this = $(this);
			$this.attr('class', $this.find(':selected').attr('value'));
		}).change();

	});
});
