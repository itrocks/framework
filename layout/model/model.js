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
		// remove tool class
		if ($dropped.hasClass('tool')) {
			$dropped.removeClass('tool');
		}
	};

	//------------------------------------------------------------------------------- pageLayoutInput
	/**
	 * @param $page jQuery the page
	 * @returns jQuery the <input name="page[.][layout]"> of the page
	 */
	var pageLayoutInput = function($page)
	{
		return $page.parent().children('input[name^="pages["][name$="][layout]"]');
	};

	//---------------------------------------------- .model.window .editor .designer documentDesigner
	$('.model.window').build(function()
	{
		var $model_window = this.inside('.model.window:has(.editor)');
		var $editor       = $model_window.find('.editor');
		var $designer     = $editor.find('.designer');
		if (!$editor.length) return;

		$designer.each(function() {
			var $page  = $(this);
			var $input = pageLayoutInput($page);
			$page.documentDesigner({
				drag_callback: dragCallback,
				drop_callback: dropCallback,
				fields:        {element: '.property_tree .property, .editor .tool', name_data: 'property'},
				remove_class:  'tool',
				tool_handle:   '.handle',
				tools:         '.tools'
			})
				.width(840);
			if ($input.val()) {
				var json_data = $('<textarea>').html($input.val()).text();
				var data      = JSON.parse(json_data);
				$page.documentDesigner('setData', data);
			}
		});

		//--------------------------------------- $email_window > .general_actions > .write > a click
		/**
		 * Save email : build the standardized data before saving the form,
		 * as no data is stored into inputs
		 */
		$model_window.find('> .general.actions > .write > a').click(function()
		{
			$designer.each(function() {
				var $page  = $(this);
				var $input = pageLayoutInput($page);
				$input.val(JSON.stringify($page.documentDesigner('getData').fields));
			});
		});

	});

});
