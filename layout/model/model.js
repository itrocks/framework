$(document).ready(function()
{

	//---------------------------------------------------------------------------------- dragCallback
	var dragCallback = function()
	{
		var $dragged = this;
		var text     = $dragged.text();
		// remove property.path from text
		if ($dragged.hasClass('property') && (text.indexOf(DOT) > -1)) {
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
	 * @returns jQuery the <input name="page[layout][.]"> of the page
	 */
	var pageLayoutInput = function($page)
	{
		return $page.parent().children('input[name^="pages[layout]["][name$="]"]');
	};

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $tools   jQuery
	 * @param settings object
	 */
	var register = function($tools, settings)
	{
		this.register({ attribute: 'title' });
		this.register($tools.find('#align'), 'style', 'text-align', null, settings.default.align);
	};

	//-------------------------------------------------------------------------------- selectCallback
	var selectCallback = function()
	{
		var $selected  = this;
		var $editor    = $selected.closest('.editor');
		var $tools     = $editor.find('.selected.tools');
		var $free_text = $tools.find('#free-text');
		// field
		if ($selected.hasClass('field')) {
			$free_text.closest('li').show();
			$free_text.text($selected.text()).change();
			if (!$free_text.is(':focus') && !$free_text.data('focus')) {
				$free_text.focus();
			}
		}
		else {
			$free_text.closest('li').hide();
			$free_text.text('');
		}
		// title
		var $title = $tools.children('h3');
		var title  = $selected.text();
		if (title.length > 30) {
			title = '...' + title.substr(title.length - 30);
		}
		$title.text(title);
		$title.attr('title', $selected.attr('title'));
	};

	//---------------------------------------------- .model.window .editor .designer documentDesigner
	$('.model.window').build(function()
	{
		var $model_window = this.inside('.model.window:has(.editor)');
		var $editor       = $model_window.find('.editor');
		if (!$editor.length) return;

		var $designer  = $editor.find('.designer');
		var $free_text = $model_window.find('#free-text');

		$designer.each(function() {
			var $page  = $(this);
			var $input = pageLayoutInput($page);
			$page.documentDesigner({
				default: { align: 'left', size: 4 },
				drag:    dragCallback,
				drop:    dropCallback,
				fields:  {
					element: '.property_tree .property, .editor .tool, .toolbox .add.tools li>span',
					name_data: 'property'
				},
				register:     register,
				remove_class: 'tool',
				select:       selectCallback,
				tool_handle:  '.handle',
				tools:        '.selected.tools'
			})
				.width(840);
			if ($input.val()) {
				var json_data = $('<textarea>').html($input.val()).text();
				var data      = JSON.parse(json_data.toString());
				$page.documentDesigner('setData', data);
			}
		});

		//------------------------------------------------------------------ $editor .field:contains(#)
		/**
		 * This is a patch because the template engine does not support {text} typing
		 */
		$editor.find('.field:contains(#)').each(function() {
			var $field = $(this);
			if ($field.text().beginsWith('#')) {
				$field.text('{' + $field.text().substr(1) + '}');
			}
		});

		//--------------------------------------------------------------- $model_window #free-text blur
		/**
		 * Mark #free-text as focused until the end of the current events execution loop
		 *
		 * This allow to know that the focused element was #free-text when dropCallback() is called
		 */
		$free_text.blur(function()
		{
			var $free_text = $(this);
			$free_text.data('focus', true);
			setTimeout(function() { $free_text.data('focus', false); }, 0);
		});

		//-------------------------------------------------------------- $model_window #free-text keyup
		$free_text.keyup(function()
		{
			var $free_text = $(this);
			var $selected  = $designer.data('selected');
			if ($selected.length) {
				$selected.text($free_text.val());
			}
		});

		//----------------------------------------- $model_window > .general_actions > .write > a click
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
