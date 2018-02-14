$('document').ready(function()
{
	$('.condition.editor').build(function()
	{
		if (!this.length || !this.closest('.condition.editor').length) return;
		var $editor = this;

		//-------------------------------------------------------------------------- appendEmptyOperand
		/**
		 * Appends an empty operand if the current one is empty and is not followed by any other empty
		 *
		 * @param $this jQuery
		 */
		var appendEmptyOperand = function($this)
		{
			var is_empty  = $this.hasClass('empty');
			if (is_empty && !$this.next().length) {
				var $empty = $('<div class="empty operand">');
				$this.after($empty);
				$empty.build();
			}
		};

		//---------------------------------------------------------------------------- dropFunctionInto
		var dropFunctionInto = function($function, $into)
		{
			// TODO drop function
		};

		//---------------------------------------------------------------------------- dropPropertyInto
		var dropPropertyInto = function($property, $into)
		{
			var $operand = $('<div>')
				.addClass('property operand')
				.attr('data-property', $property.data('property'))
				.text($property.text());
			$into.replaceWith($operand);
			$operand.build();
		};

		//-------------------------------------------------------------------------- .operand droppable
		$editor.inside('.operand').droppable(
		{
			accept:    '.function, .property, .value',
			greedy:    true,
			tolerance: 'pointer',

			//------------------------------------------------------------------- .operand droppable drop
			drop: function(event, ui)
			{
				var $draggable = ui.draggable;
				var $this      = $(this);
				appendEmptyOperand($this);
				$this.removeClass('replace');
				$draggable.data('property')
					? dropPropertyInto($draggable, $this)
					: dropFunctionInto($draggable, $this);
			},

			//-------------------------------------------------------------------- .operand droppable out
			out: function()
			{
				$(this).removeClass('replace');
			},

			//------------------------------------------------------------------- .operand droppable over
			over: function()
			{
				$(this).addClass('replace');
			}

		});

		//----------------------------------- .condition.editor fieldset ul.logical > li:before content
		/**
		 * Translated 'and' and 'or' css content
		 */
		if (!$('header > style[data-condition]').length) {
			$('<style>')
				.attr('data-condition', 'condition')
				.attr('type', 'text/css')
				.text('\
					.condition.editor fieldset ul.and > li:before {\
						content: ' + Q + tr('|and|', 'ITRocks\\Framework\\Widget\\Condition') + Q + ';\
					}\
					.condition.editor fieldset ul.or > li:before {\
						content: ' + Q + tr('|or|', 'ITRocks\\Framework\\Widget\\Condition') + Q + ';\
					}\
				')
				.appendTo('header');
		}

	});
});
