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
		if ($dropped.hasClass('tool')) {
			$dropped.attr('title', '');
		}
		if ($dropped.attr('data-field')) {
			if (!$dropped.attr('data-format')) {
				$dropped.attr('data-format', 'text');
			}
		}
		// remove property / tool classes
		$dropped.removeClass('property');
		$dropped.removeClass('tool');
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

	//-------------------------------------------------------------------------------------- register
	var register = function()
	{
		this.register({ attribute: 'title' });
	};

	//-------------------------------------------------------------------------------- selectCallback
	var selectCallback = function()
	{
		var $selected = this;
		var $title    = $selected.closest('.editor').find('.tools > h3');
		$title.text($selected.text());
		$title.attr('title', $selected.attr('title'));
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
				default:      { size: 4 },
				drag:         dragCallback,
				drop:         dropCallback,
				fields:       {element: '.property_tree .property, .editor .tool', name_data: 'property'},
				register:     register,
				remove_class: 'tool',
				select:       selectCallback,
				tool_handle:  '.handle',
				tools:        '.tools'
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
