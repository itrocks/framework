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
		if (!$editor.length) return;

		$editor.find('.designer')
			.documentDesigner({
				drag_callback: dragCallback,
				drop_callback: dropCallback,
				fields:        { element: '.property_tree .property, .editor .tool', name_data: 'property' },
				remove_class:  'tool',
				tool_handle:   '.handle',
				tools:         '.tools'
			})
			/*
			.documentDesigner('setData', [
				{"field":"two","height":"","left":"275","top":"47","width":"","title":"deux","text":"deux","font_size":"16","format":"text"},
				{"field":"sub.four","height":"","left":"552","top":"291","width":"","title":"sub-property.quatre","text":"quatre","font_size":"40","format":"text"},
				{"class":"horizontal line","height":"","left":"564","top":"77","width":"100","title":"Horizontal line","text":"Horizontal line","font_size":"16","format":"text"},
				{"class":"vertical line","height":"","left":"666","top":"113","width":"0","title":"Vertical line","text":"Vertical line","font_size":"16","format":"text"},
				{"class":"horizontal snap line","height":"","left":"","top":"246","width":"","title":"Horizontal snap line","text":"Horizontal snap line","font_size":"16","format":"text"},
				{"class":"vertical snap line","height":"","left":"378","top":"","width":"","title":"Vertical snap line","text":"Vertical snap line","font_size":"16","format":"text"},
				{"field":"sub.three","height":"","left":"462","top":"144","width":"","title":"sub-property.trois","text":"trois","font_size":"30","format":"text"},
				{"class":"free-text","height":"160","left":"78","top":"188","width":"228","title":"barcode","text":"barcode","font_size":"30","format":"code128"},
				{"class":"vertical line","height":"100","left":"186","top":"54","width":"0","title":"Vertical line","text":"Vertical line","font_size":"1","format":"text"}
			])
			*/
			.width(840);

		//--------------------------------------- $email_window > .general_actions > .write > a click
		/**
		 * Save email : build the standardized data before saving the form,
		 * as no data is stored into inputs
		 */
		$model_window.find('> .general.actions > .write > a').click(function(event)
		{
		});

	});

});
