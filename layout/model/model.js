$(document).ready(function()
{

	//-------------------------------------------------------------------------------- dragCallback
	var dragCallback = function()
	{
		var $dragged = this;
		var text     = $dragged.text();
		// remove property.path from text
		if ($dragged.hasClass('property') && (text.indexOf(DOT) > -1)) {
			$dragged.text(text.substr(text.lastIndexOf(DOT) + 1));
		}
	};

	//-------------------------------------------------------------------------------- dropCallback
	var dropCallback = function()
	{
		var $dropped = this;
		// default format to text for field
		if ($dropped.attr('data-field')) {
			if (!$dropped.attr('data-format')) {
				$dropped.attr('data-format', 'text');
			}
		}
		// remove title from dropped tools
		else {
			$dropped.attr('title', '');
		}
		// remove property / tool classes
		$dropped.removeClass('property');
		$dropped.removeClass('tool');
	};

	//----------------------------------------------------------------------------- pageLayoutInput
	/**
	 * @param $page jQuery the page
	 * @returns jQuery the <input name="page[layout][.]"> of the page
	 */
	var pageLayoutInput = function($page)
	{
		return $page.parent().children('input[name^="pages[layout]["][name$="]"]');
	};

	//------------------------------------------------------------------------------------ register
	/**
	 * @param $tools   jQuery
	 * @param settings object
	 */
	var register = function($tools, settings)
	{
		this.register({ attribute: 'title' });
		this.register($tools.find('#align'), 'style', 'text-align', null, settings.default.align);
	};

	//------------------------------------------------------------------------------ selectCallback
	var selectCallback = function()
	{
		var $selected  = this;
		var $editor    = $selected.closest('.editor');
		var $tools     = $editor.find('.selected.tools');
		var $free_text = $tools.find('#free-text');
		var old_text   = $free_text.val();
		// draw field
		if ($selected.hasClass('line') || $selected.hasClass('rectangle')) {
			$free_text.val('');
			$tools.hide();
		}
		// text field
		else if ($selected.hasClass('field')) {
			$free_text.closest('li').show();
			$free_text.val($selected.text());
			$free_text.autoHeight();
			if (
				// if changed from a $selected to another (approximatively) : keep the focus
				(old_text !== $selected.text())
				// if clicked the same $selectded : switch between focused / not focused
				|| (!$free_text.is(':focus') && !$free_text.data('focus'))
			) {
				$free_text.focus();
			}
		}
		else {
			$free_text.closest('li').hide();
			$free_text.val('');
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
	$('body').build({ priority: 500, selector: 'article.model .editor', callback: function()
	{
		var $editor       = this;
		var $model_window = this.closest('article.model');
		var $designer     = $editor.find('.designer');
		var $free_text    = $model_window.find('#free-text');
		var $size         = $model_window.find('#size');

		setTimeout(function() { $designer.each(function() {
			var $page  = $(this);
			var $input = pageLayoutInput($page);
			$page.documentDesigner({
				default: { align: 'left', size: 4 },
				drag:    dragCallback,
				drop:    dropCallback,
				fields:  {
					element:   '.property_tree .property, .editor .tool, .toolbox .add.tools li>span',
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
			$page.fileUpload();
		})});

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

		//----------------------------------------------------------------- $model_window #size keydown
		$size.keydown(function(event)
		{
			var $size = $(this);

			var DOWN = 40;
			var UP   = 38;

			var distance = event.ctrlKey ? 1 : .2;

			// up / down arrows increment / decrement the size from .1
			if (event.keyCode === DOWN) {
				$size.val(Math.round(10 * (parseFloat($size.val()) - distance)) / 10);
				event.preventDefault();
				event.stopPropagation();
			}
			if (event.keyCode === UP) {
				$size.val(Math.round(10 * (parseFloat($size.val()) + distance)) / 10);
				event.preventDefault();
				event.stopPropagation();
			}
		});

		//------------------------------------------------------------------- $model_window #size keyup
		$size.keyup(function()
		{
			// once size changes, resize the zone into the designer / html-links
			$(this).change();
		});

		//----------------------------------- article.model .editor .general.actions > .write > a click
		/**
		 * Save layout model : build the standardized data before saving the form,
		 * as no data is stored into inputs
		 */
		this.find('.general.actions > .write > a').click(function()
		{
			var $designer = $(this).closest('article.model').find('.editor .designer');
			var $active   = $designer.closest('.active.page');
			var $pages    = $designer.closest('.page');
			$pages.addClass('active');
			$designer.each(function() {
				var $page  = $(this);
				var $input = pageLayoutInput($page);
				$input.val(JSON.stringify($page.documentDesigner('getData').fields));
			});
			$pages.removeClass('active');
			$active.addClass('active');
		});

	}});

});

//----------------------------------------------------------------------------------- window scroll
$(window).scroll(function()
{

	var $toolbox = $('article.model.edit > .editor > .toolbox');
	if (!$toolbox.length) return;
	var $fieldset = $toolbox.next('fieldset');
	var $stay_top = $('article.model.edit > .fixed.stay-top');
	// reset position
	if (!$stay_top.length && $toolbox.hasClass('stay-top')) {
		$fieldset.attr('style', '');
		$toolbox.attr('style', '');
		$toolbox.removeClass('fixed stay-top');
	}
	// fixed position
	if ($stay_top.length) {
		var $parent = $toolbox.parent();
		if (!$toolbox.hasClass('stay-top')) {
			$fieldset.css('margin-left', $fieldset.offset().left - $fieldset.parent().offset().left);
			$toolbox.addClass('fixed stay-top');
			$toolbox.css('position', 'fixed');
		}
		$toolbox.css('left',
			$parent.offset().left + parseInt($parent.css('padding-left')) - window.scrollbar.left()
		);
		$toolbox.css('top', Math.max(
			$fieldset.offset().top - window.scrollbar.top(),
			$stay_top.height()
				+ parseInt($stay_top.css('top'))
				+ parseInt($stay_top.css('border-bottom-width'))
		));
	}

});
