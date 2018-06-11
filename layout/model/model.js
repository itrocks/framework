$(document).ready(function()
{

	//---------------------------------------------------------------------------------- dragCallback
	var dragCallback = function()
	{
		var $dragged = this;
		var text     = $dragged.text();
		// remove property.path from text
		if (text.indexOf(DOT) > -1) {
			$dragged.text(text.substr(text.lastIndexOf(DOT) + 1));
		}
	};

	//---------------------------------------------------------------------------------- dropCallback
	var dropCallback = function()
	{
		var $dropped = this;
		// remove title from dropped tools
		if (!$dropped.hasClass('property')) {
			$dropped.attr('title', '');
		}
	};

	//----------------------------------------- .model.edit.window .editor .designer documentDesigner
	$('.model.edit.window .editor').build(function()
	{
		var $model_window = $('.model.edit.window:has(.editor)');
		var $editor       = $model_window.find('.editor');
		var $designer     = $editor.find('.designer');
		if (!$editor.length) return;

		$designer.each(function() {
			$(this).documentDesigner({
				drag_callback: dragCallback,
				drop_callback: dropCallback,
				fields:        {element: '.property_tree .property, .editor .tool', name_data: 'property'},
				remove_class:  'tool',
				tool_handle:   '.handle',
				tools:         '.tools'
			})
				.width(840);
		});

		//--------------------------------------- $email_window > .general_actions > .write > a click
		/**
		 * Save email : build the standardized data before saving the form,
		 * as no data is stored into inputs
		 */
		$model_window.find('> .general.actions > .write > a').click(function(event)
		{
			$designer.each(function() {
				var $page = $(this);
				var $input = $page.parent().children('input[name^="pages["]');
				$input.val(JSON.stringify($page.documentDesigner('getData')));
			});
		});

	});

});
