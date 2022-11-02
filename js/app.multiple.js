$(document).ready(function()
{
	const $body = $('body')

	//---------------------------------------------------------------------------------- depthReplace
	/**
	 * increment indexes in new row html code
	 */
	const depthReplace = function(text, old_index, new_index, open, close, depth)
	{
		let i
		let j = 0
		while ((i = text.indexOf('=' + DQ, j) + 1) > 0) {
			let in_depth = depth
			j = text.indexOf(DQ, i + 1)
			while (
				(i = text.indexOf(open, i) + open.length) && (i > (open.length - 1)) && (i < j)
				&& ((in_depth > 0) || (text[i] < '0') || (text[i] > '9'))
				) {
				if ((text[i] >= '0') && (text[i] <= '9')) {
					in_depth --
				}
			}
			if ((i > (open.length - 1)) && (i < j) && !in_depth) {
				const k          = text.indexOf(close, i)
				const html_index = text.substring(i, k)
				if (Number(html_index) === old_index) {
					text = text.substring(0, i) + new_index + text.substring(k)
				}
			}
		}
		return text
	}

	//--------------------------------------------------------------------------------------- addLine
	/**
	 * @param $block jQuery the .component-objects / .objects ul/ol/table container
	 * @return jQuery new row = added line element
	 */
	const addLine = function($block)
	{
		// calculate depth in order to increment the right index
		let depth   = -1
		let $parent = $block
		do {
			if ($parent.is('.component-objects, .objects')) {
				depth ++
			}
		}
		while (($parent = $parent.parent()).length)
		// calculate new row and indexes
		const $itrocks_add = $block.data('itrocks_add')
		if (!$itrocks_add) {
			return null
		}
		const $new_row = $itrocks_add.clone()
		$block.data('itrocks_last_index', $block.data('itrocks_last_index') + 1)
		const new_index = $block.data('itrocks_last_index')
		const old_index = $block.data('itrocks_add_index')
		let   html      = $new_row.html()
		if (html.indexOf('[') > -1) {
			html = depthReplace(html, old_index, new_index, '[', ']', depth)
		}
		if (html.indexOf('%5B') > 1) {
			html = depthReplace(html, old_index, new_index, '%5B', '%5D', depth)
		}
		$new_row.html(html)
		// append and build new row
		let $body = $block.children('tbody')
		if (!$body.length) {
			$body = $block
		}
		$body.append($new_row)
		$new_row.autofocus(false)
		$new_row.build()
		$new_row.autofocus(true)
		return $new_row
	}

	//----------------------------------------------------------------------------------- autoAddLine
	/**
	 * Decide if a line must be added (last line with non-default values)
	 */
	const autoAddLine = function()
	{
		const $this = $(this)
		if (
			($this.val().length && ($this.val() === $this.data('default-value')))
			|| $this.data('itrocks-no-add')
			|| $this.data('no-empty-check')
		) {
			return
		}
		const $row = $this.closest('tr, ul > li')
		if ($row.data('itrocks-no-add')) {
			return
		}
		if ($this.val() && ($this.val() !== '0') && $row.length && !$row.next('tr, li').length) {
			let $block = $row.closest('.component-objects, .objects')
			if (!$block.is('ul, ol, table')) {
				$block = $block.children('ul, ol, table')
			}
			if (!$block.data('itrocks_add')) {
				return
			}
			addLine($block)
		}
	}

	//------------------------------------------------ ol.auto_width, table.auto_width, ul.auto_width
	$body.build('each', '.component-objects, .objects', function()
	{
		let $this = $(this)
		if (!$this.is('ul, ol, table')) {
			$this = $this.children('ul, ol, table')
		}
		$this.data('addLine', () => addLine($this))
		const table = $this.is('table')
		// prepare new row
		const $new = $this.find(table ? '> tbody > tr.new' : '> li.new')
		if (!$new.length) {
			return
		}
		$new.removeClass('new')
		$this.data('itrocks_add', $new.clone())
		// itrocks_add_index : the value of the index to be replaced into the model for new rows
		const objects = !table && !$this.children('li.head').length
		const index   = $this.find(table ? '> tbody > tr' : '> li').length - ((objects || table) ? 1 : 2)
		$this.data('itrocks_add_index', index)
		// itrocks_last_index : the last used index (lines count - 1)
		$this.data('itrocks_last_index', index)
		if ($this.data('itrocks_add_index') > 0) {
			if ($new.find('input:not([class=file]):not([type=hidden]), select, textarea').length) {
				$new.remove()
				$this.data('itrocks_last_index', index - 1)
			}
		}
	})

	//---------------------------------------------------- input, select, textarea change/focus/keyup
	/**
	 * Automatically add a line
	 */
	$body.build('each', ['.component-objects, .objects', 'input, select, textarea'], function()
	{
		const $this = $(this)
		let   $block = $this.closest('.component-objects, .objects')
		if (!$block.is('ul, ol, table')) {
			$block = $block.children('ul, ol, table')
		}
		if ($block.data('no-add')) {
			return
		}
		$this.change(function() { if (!$(this).data('itrocks-no-add-change')) autoAddLine.call(this) })
		$this.focus (function() { if (!$(this).data('itrocks-no-add-focus'))  autoAddLine.call(this) })
		$this.keyup (function() { if (!$(this).data('itrocks-no-add-keyup'))  autoAddLine.call(this) })
	})

	//------------------- article > form > ul.data ol.properties > li.component-objects > label click
	/**
	 * Manually add a line
	 */
	$body.build('click', '.component-objects > label, .objects > label', function()
	{
		const $label = $(this)
		const $block = $label.next()
		if ($block.data('no-add')) {
			return
		}
		const $input = $label.parent()
			.find('input, select, textarea').filter('[type=file], :visible').last()
		if ($input.data('itrocks-no-add-click')) {
			return
		}
		addLine($block)
	})

	//-------------------------------------------------------------------- li.multiple li.minus click
	/**
	 * Remove a line
	 */
	$body.build('click', ['.component-objects, .objects', 'button.minus, li.minus'], function(event)
	{
		const $this = $(this)
		// setTimeout allows other click events to .minus to execute before the row is removed
		setTimeout(function() {
			const $body = $this.closest('tbody, ul')
			if ($body.children().length > ($body.is('ul:not(.map)') ? 2 : 1)) {
				$this.closest('tr, ul > li').remove()
			}
			else {
				const $table   = $this.closest('table, ul')
				const $new_row = $table.data('itrocks_add').clone()
				$this.closest('tr, ul > li').replaceWith($new_row)
				$new_row.build()
				$table.data('itrocks_last_index', $table.data('itrocks_last_index') + 1)
			}
		})
		event.preventDefault()
	})

})
