$(document).ready(function()
{
	const $body = $('body')

	// Only if we have select_all, we can exclude a part of elements
	const excluded_selection = []
	const select_all         = []
	const selection          = []

	const selector_checkbox = 'th > input.selector[type=checkbox]'
	const checkboxes_select = 'th > input[type=checkbox]:not(.selector)'

	//------------------------------------------------------------------------------ unselectFromList
	window.unselectFromList = function(class_name, object_id)
	{
		let selection_id
		for (selection_id in excluded_selection) if (excluded_selection.hasOwnProperty(selection_id)) {
			if (selection_id.startsWith(class_name + DOT)) {
				excluded_selection[selection_id] = excluded_selection[selection_id].withoutValue(object_id)
			}
		}
		for (selection_id in selection) if (selection.hasOwnProperty(selection_id)) {
			if (selection_id.startsWith(class_name + DOT)) {
				selection[selection_id] = selection[selection_id].withoutValue(object_id)
			}
		}
	}

	//-------------------------------------------------------------------------------- resetSelection
	const resetSelection = function(id)
	{
		excluded_selection[id] = []
		select_all[id]         = false
		selection[id]          = []
	}

	//----------------------------------------------------------------------------------- updateCount
	const updateCount = function($article_list, $selector, $summary)
	{
		let   count_elements, select_all_content, selection_content, selection_exclude_content, title
		const $selection_checkbox = $article_list.find(selector_checkbox)
		const article_id          = $article_list.data('selection-id')
		const selection_checkbox  = $selection_checkbox[0]
		const total               = $selector.children('input[name=select_all]').data('count')
		if (select_all[article_id]) {
			count_elements             = (total - excluded_selection[article_id].length)
			select_all_content         = 1
			selection_checkbox.checked = !!count_elements
			selection_content          = ''
			selection_exclude_content  = excluded_selection[article_id].join()
			title                      = tr('uncheck to deselect all lines')
		}
		else {
			count_elements             = selection[article_id].length
			select_all_content         = 0
			selection_content          = selection[article_id].join()
			selection_exclude_content  = ''
			selection_checkbox.checked = (count_elements && (count_elements === total))
			title                      = tr('check to select all $!1 lines').repl('$!1', total)
		}
		$summary.html($summary.data('text').repl('?', count_elements))
		$selector.children('input[name=excluded_selection]').val(selection_exclude_content)
		$selector.children('input[name=select_all]')        .val(select_all_content)
		$selector.children('input[name=selection]')         .val(selection_content)
		$selection_checkbox.parent().attr('title', title)
		selection_checkbox.indeterminate = count_elements && (count_elements < total)
	}

	//---------------------------------------------------------------------------------- article.list
	$body.build('each', 'article.list', function()
	{
		const $article   = $(this)
		const $table     = $article.find('> form > table')
		const $search    = $table.find('> thead > .search')
		const $selector  = $table.find('> thead > .title > .visible.lines')
		const $summary   = $article.find('.summary .lines')
		const article_id = $article.attr('id')
		$article.data('selection-id', article_id)
		$summary.data('text',         $summary.html())

		//-------------------------------------------------------------- .search input|textarea keydown
		// reload list when #13 pressed into a search input
		$search.find('input, textarea').keydown(function(event)
		{
			if (event.keyCode === 13) {
				const $this = $(this)
				resetSelection($this.closest('article.list').attr('id'))
				$this.closest('form').submit()
			}
		})

		//----------------------------------------------------------------------- .search select change
		$search.find('select').change(function()
		{
			const $this = $(this)
			resetSelection($this.closest('article.list').attr('id'))
			$this.closest('form').submit()
		})

		//--------------------------------------------------------------- .search .reset.search a click
		$search.find('.reset > a').click(function()
		{
			resetSelection($(this).closest('article.list').attr('id'))
		})

		//------------------------------------------------------------------ input[type=checkbox] check
		if (!(article_id in selection)) {
			excluded_selection[article_id] = []
			select_all[article_id]         = false
			selection[article_id]          = []
		}

		//-------------------------------------------------------- input.selector[type=checkbox] change
		$article.find(selector_checkbox).change(function(event)
		{
			if (this.indeterminate) {
				this.indeterminate = false
			}
			selectAction(this.checked, 'all', event)
		})

		updateCount($article, $selector, $summary)

		//-------------------------------------------------------------------------------- selectAction
		/**
		 * Select / deselect buttons
		 *
		 * @param select boolean true to select, false to deselect
		 * @param type   string  @values all, matching, visible
		 * @param event  Event
		 */
		const selectAction = function(select, type, event)
		{
			if (type === 'all') {
				// Re-initialize selection
				excluded_selection[article_id] = []
				select_all[article_id]         = select
				selection[article_id]          = []
				const $checkboxes = $article.find('input[type=checkbox]')
				$checkboxes.prop('checked', select)
				select
					? $checkboxes.closest('tr').addClass('selected')
					: $checkboxes.closest('tr').removeClass('selected')
			}
			else {
				$article.find('input[type=checkbox]').each(function() {
					const checkbox = $(this)
					checkbox.prop('checked', select)
					checkbox.change()
				})
			}
			updateCount($article, $selector, $summary)
			event.preventDefault()
		}

		//----------------------------------------------------------- .selection.actions a.submit click
		$article.find('.selection.actions a.submit:not([target^="#"])').click(function(event)
		{
			const data = {
				excluded_selection: $selector.children('input[name=excluded_selection]').val(),
				select_all:         $selector.children('input[name=select_all]').val(),
				selection:          $selector.children('input[name=selection]').val()
			}
			const form   = document.createElement('form')
			const target = $(this).attr('target')
			// remember to change me :
			form.action = event.target
			form.method = 'post'
			form.target = target
			for (const key in data) if (data.hasOwnProperty(key)) {
				const input   = document.createElement('input')
				input.name  = key
				input.type  = 'hidden'
				input.value = data[key]
				form.appendChild(input)
			}
			// must add to body to submit with refresh page
			document.body.appendChild(form)
			form.submit()
			// clean html dom
			document.body.removeChild(form)
			return false
		})

	})

	//------------------------------------------------------------------------------ tbody > th click
	$body.build('call', 'article.list th > input[type=checkbox]', function()
	{
		this.parent().click(function(event) {
			if ($(event.target).is('input[type=checkbox]')) {
				return
			}
			$(this).children('input[type=checkbox]').click().focus()
		})
	})

	//------------------------------------------------------------------- input[type=checkbox] change
	$body.build('change', 'article.list ' + checkboxes_select, function()
	{
		const $checkbox  = $(this)
		const $article   = $checkbox.closest('article.list')
		const $selector  = $article.find('table > thead > .title > .visible.lines')
		const $summary   = $article.find('.summary .lines')
		const article_id = $article.data('selection-id')
			this.checked
			? $checkbox.closest('tr').addClass('selected')
			: $checkbox.closest('tr').removeClass('selected')
		if (select_all[article_id]) {
			if (!this.checked && (excluded_selection[article_id].indexOf(this.value) === -1)) {
				excluded_selection[article_id].push(this.value)
			}
			if (this.checked && (excluded_selection[article_id].indexOf(this.value) > -1)) {
				excluded_selection[article_id]
					.splice(excluded_selection[article_id].indexOf(this.value), 1)
			}
			$article.find(checkboxes_select + '[value=' + this.value + ']')
				.attr('checked', this.checked)
		}
		else {
			if (this.checked && (selection[article_id].indexOf(this.value) === -1)) {
				selection[article_id].push(this.value)
			}
			if (!this.checked && (selection[article_id].indexOf(this.value) > -1)) {
				selection[article_id].splice(selection[article_id].indexOf(this.value), 1)
			}
			// Repercussion if with have multiple lines
			$article.find(checkboxes_select + '[value=' + this.value + ']')
				.attr('checked', this.checked)
		}
		updateCount($article, $selector, $summary)
	})

	//------------------------------------ article.list th > input[type=checkbox]:not(.selector) call
	$body.build('call', 'article.list ' + checkboxes_select, function()
	{
		const $checkboxes = this
		const $article    = $checkboxes.closest('article')
		const article_id  = $article.data('selection-id')
		if (!(article_id in selection)) {
			return
		}
		$checkboxes.each(function() {
			if (
				(select_all[article_id] && ($.inArray(this.value, excluded_selection[article_id]) === -1))
				|| $.inArray(this.value, selection[article_id]) !== -1
			) {
				const $checkbox = $(this)
				$checkbox.prop('checked', true)
				$checkbox.closest('tr').addClass('selected')
			}
		})
	})

})
