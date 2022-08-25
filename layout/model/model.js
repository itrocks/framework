$(document).ready(function()
{
	const $body = $('body')

	//-------------------------------------------------------------------------------- dragCallback
	const dragCallback = function()
	{
		const $dragged = this
		const text     = $dragged.text()
		// remove property.path from text
		if ($dragged.hasClass('property') && (text.indexOf(DOT) > -1)) {
			$dragged.text(text.substring(text.lastIndexOf(DOT) + 1))
		}
	}

	//-------------------------------------------------------------------------------- dropCallback
	const dropCallback = function()
	{
		const $dropped = this
		// default format to text for field
		if ($dropped.attr('data-field')) {
			if (!$dropped.attr('data-format')) {
				$dropped.attr('data-format', 'text')
			}
		}
		// remove title from dropped tools
		else {
			$dropped.attr('title', '')
		}
		// remove property / tool classes
		$dropped.removeClass('property')
		$dropped.removeClass('tool')
		$dropped.build()
	}

	//----------------------------------------------------------------------------- pageLayoutInput
	/**
	 * @param $page jQuery the page
	 * @returns jQuery the <input name="page[layout][.]"> of the page
	 */
	const pageLayoutInput = function($page)
	{
		return $page.children('input[name^="pages[layout]["][name$="]"]')
	}

	//------------------------------------------------------------------------------------ register
	/**
	 * @param $tools   jQuery
	 * @param settings object
	 */
	const register = function($tools, settings)
	{
		this.register({ attribute: 'title' })
		this.register($tools.find('#align'), 'style', 'text-align', null, settings.default.align)
	}

	//------------------------------------------------------------------------------ selectCallback
	const selectCallback = function()
	{
		const $selected  = this
		const $editor    = $selected.closest('.layout-model')
		const $tools     = $editor.find('.selected.tools')
		const $free_text = $tools.find('#free-text')
		const old_text   = $free_text.val()
		// draw field
		if ($selected.hasClass('line') || $selected.hasClass('rectangle')) {
			$free_text.val('')
			$tools.hide()
		}
		// text field
		else if ($selected.hasClass('field')) {
			$free_text.closest('li').show()
			$free_text.val($selected.text())
			$free_text.autoHeight()
			if (
				// if changed from a $selected to another (approximately) : keep the focus
				(old_text !== $selected.text())
				// if clicked the same $selected : switch between focused / not focused
				|| (!$free_text.is(':focus') && !$free_text.data('focus'))
			) {
				$free_text.focus()
			}
		}
		else {
			$free_text.closest('li').hide()
			$free_text.val('')
		}
		// title
		const $title = $tools.children('h5')
		let   title  = $selected.text()
		if (title.length > 30) {
			title = '...' + title.substring(title.length - 30)
		}
		$title.text(title)
		$title.attr('title', $selected.attr('title'))
	}

	//----------------------------------------------- article.layout-model .designer documentDesigner
	$body.build({ priority: 500, selector: 'article.layout-model', callback: function()
	{
		const $editor       = this
		const $model_window = this.closest('article.layout-model')
		const $designer     = $editor.find('.designer')
		const $free_text    = $model_window.find('#free-text')
		const $size         = $model_window.find('#size')

		setTimeout(function() { $designer.each(function()
		{
			const $designer = $(this)
			const $page     = $designer.closest('.page')
			const $input    = pageLayoutInput($page)
			const fields    = [
				'article.layout-model.edit',
				'.buttons.toolbox .add.tools li, .page > .snap.tool, .property-select .property'
			]

			let $elements = $page.find('[data-style]')
			if ($page.data('style')) {
				$elements = $elements.add($page)
			}
			$elements.each(function() {
				const $element = $(this)
				let   style    = $element.attr('style')
				style = (style === undefined) ? '' : (style + SP)
				$element.attr('style', style + $element.data('style'))
				$element.removeAttr('data-style')
				$element.removeData('style')
			})
			const css_height = $designer.css('height').repl('px', '')
			const css_width  = $designer.css('width').repl('px', '')
			const height = $designer.data('height') ? $designer.data('height') : css_height
			const size   = $designer.data('size')   ? $designer.data('size')   : 10
			const width  = $designer.data('width')  ? $designer.data('width')  : css_width

			$designer.documentDesigner({
				default: {
					align: 'left',
					size:  size
				},
				drag:   dragCallback,
				drop:   dropCallback,
				fields: {
					element:   fields,
					name_data: 'property'
				},
				ratio: {
					height: height,
					width:  width
				},
				register:     register,
				remove_class: 'tool',
				select:       selectCallback,
				tool_handle:  '.handle',
				tools:        '.selected.tools'
			})
				.width(css_width)

			if ($input.val()) {
				const json_data = $('<textarea>').html($input.val()).text()
				const data      = JSON.parse(json_data.toString())
				$designer.documentDesigner('setData', data)
			}
			$designer.fileUpload()

		})})

		//------------------------------------------------------------------ $editor .field:contains(#)
		/**
		 * This is a patch because the template engine does not support {text} typing
		 */
		$editor.find('.field:contains(#)').each(function() {
			const $field = $(this)
			if ($field.text().startsWith('#') && ($field.text().length > 3)) {
				$field.text($field.text().replace(/#([.\w]+)/g, '{$1}'))
			}
		})

		//--------------------------------------------------------------- $model_window #free-text blur
		/**
		 * Mark #free-text as focused until the end of the current events execution loop
		 *
		 * This allows to know that the focused element was #free-text when dropCallback() is called
		 */
		$free_text.blur(function()
		{
			const $free_text = $(this)
			$free_text.data('focus', true)
			setTimeout(function() { $free_text.data('focus', false) }, 0)
		})

		//----------------------------------------------------------------- $model_window #size keydown
		$size.keydown(function(event)
		{
			const $size = $(this)

			const DOWN = 40
			const UP   = 38

			const distance = event.ctrlKey ? 1 : .2

			// up / down arrows increment / decrement the size from .1
			if (event.keyCode === DOWN) {
				$size.val(Math.round(10 * (parseFloat($size.val()) - distance)) / 10)
				event.preventDefault()
				event.stopPropagation()
			}
			if (event.keyCode === UP) {
				$size.val(Math.round(10 * (parseFloat($size.val()) + distance)) / 10)
				event.preventDefault()
				event.stopPropagation()
			}
		})

		//------------------------------------------------------------------- $model_window #size keyup
		$size.keyup(function()
		{
			// once size changes, resize the zone into the designer / html-links
			$(this).change()
		})

	}})

	//------------------------------------------ article.layout-model .general.actions > .write click
	/**
	 * Save layout model : build the standardized data before saving the form,
	 * as no data is stored into inputs
	 */
	$body.build({
		event:    'click',
		priority: 10,
		selector: 'article.layout-model .general.actions > .save > a',
		callback: function()
		{
			const $designer = $(this).closest('article.layout-model').find('.designer')
			const $active   = $designer.closest('.active.page')
			const $pages    = $designer.closest('.page')
			$pages.addClass('active')
			$designer.each(function() {
				const $designer = $(this)
				const $input    = pageLayoutInput($designer.closest('.page'))
				$input.val(JSON.stringify($designer.documentDesigner('getData').fields))
			})
			$pages.removeClass('active')
			$active.addClass('active')
		}
	})

	//------------------------------------------------------- article.layout-model input#align change
	$body.build('change', 'article.layout-model input#align', function()
	{
		const $this  = $(this)
		const $align = $this.closest('article.layout-model').find('li.align')
		const value  = $this.val()
		$align.removeClass('selected')
		$align.filter(DOT + value).addClass('selected')
	})

	//------------------------------------------------------ article.layout-model input#format change
	$body.build('change', 'article.layout-model input#format', function()
	{
		const $this  = $(this)
		const $align = $this.closest('article.layout-model').find('li.format')
		const value  = $this.val()
		$align.removeClass('selected')
		$align.filter(DOT + value).addClass('selected')
	})

	//----------------------------------------------------------- article.layout-model li.align click
	$body.build('click', 'article.layout-model li.align', function()
	{
		const $this = $(this)
		let   value = 'left'
		if      ($this.is('.center')) value = 'center'
		else if ($this.is('.left'))   value = 'left'
		else if ($this.is('.right'))  value = 'right'
		$this.closest('form').find('input#align').val(value).change()
	})

	//------------------------------------------------------------ article.layout-model li.bold click
	$body.build('click', 'article.layout-model li.bold', function()
	{
		const $this = $(this)
		const $bold = $this.closest('form').find('input#bold')
		let   value = $bold.val()
		value = (value === '') ? 'bold' : ''
		$bold.val(value).change()
	})

	//------------------------------------------------------ article.layout-model li.font-color click
	$body.build('click', 'article.layout-model li.font-color', function()
	{
		const $this = $(this)
		let   color = $this.closest('article').find('.designer .selected').css('color')
		color = color.substring(color.indexOf('(') + 1, color.indexOf(')'))
		color = color.repl(' ', '').split(',')
		color = '#'
			+ parseInt(color[0]).toString(16)
			+ parseInt(color[1]).toString(16)
			+ parseInt(color[2]).toString(16)
		if ($this.data('colpick')) {
			$this.colpickSetColor(color, true)
			return
		}
		const change = (hsb, hex) => {
			$this.closest('article').find('.designer .selected').css({ 'color': '#' + hex })
		}
		const submit = function() {
			this.selector.closest('.colpick').hide()
		}
		$this.colpick({ color: color, onChange: change, onSubmit: submit })
			.data('colpick', true)
			.click()
	})

	//---------------------------------------------------------- article.layout-model li.format click
	$body.build('click', 'article.layout-model li.format', function()
	{
		const $this = $(this)
		let   value = 'text'
		if      ($this.is('.image'))   value = 'image'
		else if ($this.is('.text'))    value = 'text'
		else if ($this.is('.text-cr')) value = 'text-cr'
		$this.closest('form').find('input#format').val(value).change()
	})

	//------------------------------------------------------------ article.layout-model li.size click
	$body.build('click', 'article.layout-model li.size', function()
	{
		const $this = $(this)
		const $size = $this.closest('form').find('input#size')
		let   value = parseFloat($size.val())
		if      ($this.is('.bigger'))  value += ((value >= 6) ? 1 : .2)
		else if ($this.is('.smaller')) value -= ((value <= 6) ? .2 : 1)
		value = (Math.round(Math.max(1, value) * 10) / 10)
		$size.val(value).change()
	})

	//------------------------------------------------------------------------------------ .free-text
	$body.build('call', 'article.layout-model .designer .free-text.field', function()
	{
		this.attr('contenteditable', true)
		this.css('white-space', 'pre-wrap')
		this.keydown(function(event) {
			if (event.keyCode === 13) {
				document.execCommand('insertHTML', false, '\n')
				return false
			}
		})
	})

	//------------------------------------------------------- article.layout-model li.copy-page click
	$body.build('click', 'article.layout-model li.copy-page', function()
	{
		const $editor      = $(this).closest('article.layout-model')
		const $active_page = $editor.find('.active.page')
		$editor.data('$copy_page', $active_page)
	})

	//------------------------------------------------------ article.layout-model li.paste-page click
	$body.build('click', 'article.layout-model li.paste-page', function()
	{
		const $editor      = $(this).closest('article.layout-model')
		const $source_page = $editor.data('$copy_page')

		if (!$source_page) {
			confirm(tr('Unable to paste') + ' : ' + tr('You must copy a page first'))
			return
		}
		if (!confirm(
			tr('Warning') + ' : '
			+ tr('this action will erase all your drawing elements in the current page')
		)) {
			return
		}
		const $active_page     = $editor.find('.active.page')
		const $source_designer = $source_page.find('.designer')
		const $target_designer = $active_page.find('.designer')
		$target_designer.empty().append($source_designer.children().clone())
	})

	//------------------------------------------------------ article.layout-model li.empty-page click
	$body.build('click', 'article.layout-model li.empty-page', function()
	{
		const $editor = $(this).closest('article.layout-model')

		const emptyPage = function($page)
		{
			const empty_page_layout = ''
				+ '<div class="horizontal snap line ui-draggable" '
				+ 'style="top: 40px;font-size:40px;text-align:left;" data-format="text" title="">\n'
				+ '\t<div class="handle ui-draggable-handle"></div>\n'
				+ '</div>\n'
				+ '<div class="horizontal snap line ui-draggable" '
				+ 'style="top: 1148px; font-size: 40px; text-align: left;" data-format="text" title="">\n'
				+ '\t<div class="handle ui-draggable-handle"></div>\n'
				+ '</div>\n'
				+ '<div class="vertical snap line ui-draggable" '
				+ 'style="left: 40px; font-size: 40px; text-align: left;" data-format="text" title="">\n'
				+ '\t<div class="handle ui-draggable-handle"></div>\n'
				+ '</div>\n'
				+ '<div class="vertical snap line ui-draggable" '
				+ 'style="left: 800px; font-size: 40px; text-align: left;" data-format="text" title="">\n'
				+ '\t<div class="handle ui-draggable-handle"></div>\n'
				+ '</div>'
			$page.find('.designer')
				.css('background-image', '')
				.html(empty_page_layout)
		}

		const $active_page = $editor.find('.active.page')
		if (confirm(
			tr('Warning') + ' : '
			+ tr('this action will erase all your drawing elements in the current page')
		)) {
			emptyPage($active_page)
		}
	})

})

//----------------------------------------------------------------------------------- window scroll
$(window).scroll(function()
{

	const $toolbox = $('article.layout-model.edit > form .toolbox')
	if (!$toolbox.length) return
	const $pages = $toolbox.next('.pages')
	const $stay_top = $('article.layout-model.edit > form .fixed.stay-top')
	// reset position
	if (!$stay_top.length && $toolbox.hasClass('stay-top')) {
		$pages.attr('style', '')
		$toolbox.attr('style', '')
		$toolbox.removeClass('fixed stay-top')
	}
	// fixed position
	if ($stay_top.length) {
		const $parent = $toolbox.parent()
		if (!$toolbox.hasClass('stay-top')) {
			$pages.css('margin-left', $pages.offset().left - $pages.parent().offset().left)
			$toolbox.addClass('fixed stay-top')
			$toolbox.css('position', 'fixed')
		}
		$toolbox.css('left',
			$parent.offset().left + parseInt($parent.css('padding-left')) - window.scrollbar.left()
		)
		$toolbox.css('top', Math.max(
			$pages.offset().top - window.scrollbar.top(),
			$stay_top.height()
				+ parseInt($stay_top.css('top'))
				+ parseInt($stay_top.css('border-bottom-width'))
		))
	}

})
