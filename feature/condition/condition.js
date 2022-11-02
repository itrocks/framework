$(document).ready(function()
{

	//---------------------------------------------------------------------------- appendEmptyOperand
	/**
	 * Appends an empty operand if the current one is empty and is not followed by any other empty
	 *
	 * @param $this jQuery
	 */
	const appendEmptyOperand = function($this)
	{
		const is_empty  = $this.hasClass('empty')
		if (is_empty && !$this.next().length) {
			const $empty = $('<div class="empty operand">')
			$this.after($empty)
			$empty.build()
		}
	}

	//------------------------------------------------------------------------------ dropFunctionInto
	const dropFunctionInto = function($function, $into)
	{
		// TODO drop function
	}

	//------------------------------------------------------------------------------ dropPropertyInto
	const dropPropertyInto = function($property, $into)
	{
		const $operand = $('<div>')
			.addClass('property operand')
			.attr('data-property', $property.data('property'))
			.text($property.text())
		$into.replaceWith($operand)
		$operand.build()
	}

	//-------------------------------------------------------------------- .condition.editor .operand
	$('body').build('call', '.condition.editor .operand', function()
	{

		//-------------------------------------------------------- .condition.editor .operand droppable
		this.droppable(
		{
			accept:    '.function, .property, .value',
			greedy:    true,
			tolerance: 'pointer',

			//------------------------------------------------- .condition.editor .operand droppable drop
			drop: function(event, ui)
			{
				const $draggable = ui.draggable
				const $this      = $(this)
				appendEmptyOperand($this)
				$this.removeClass('replace')
				$draggable.data('property')
					? dropPropertyInto($draggable, $this)
					: dropFunctionInto($draggable, $this)
			},

			//-------------------------------------------------- .condition.editor .operand droppable out
			out: function()
			{
				$(this).removeClass('replace')
			},

			//------------------------------------------------- .condition.editor .operand droppable over
			over: function()
			{
				$(this).addClass('replace')
			}

		})

		//----------------------------------- .condition.editor fieldset ul.logical > li:before content
		/**
		 * Translated 'and' and 'or' css content
		 */
		if (!$('head > style[data-condition]').length) {
			$('<style>')
				.attr('data-condition', 'condition')
				.attr('type', 'text/css')
				.text('\
					.condition.editor fieldset ul.and > li:before {\
						content: ' + Q + tr('|and|', 'ITRocks\\Framework\\Feature\\Condition') + Q + ';\
					}\
					.condition.editor fieldset ul.or > li:before {\
						content: ' + Q + tr('|or|', 'ITRocks\\Framework\\Feature\\Condition') + Q + ';\
					}\
				')
				.appendTo('head')
		}

	})
})
