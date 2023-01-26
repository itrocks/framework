$(document).ready(() => {
	const $body = $('body')
	let   edit_mode = false
	let   old_value
	let   selected

	const blurEvent = () =>
	{
		unEdit()
	}

	const cancelEdit = action =>
	{
		$(action.closest('.actions')).nextAll('table.list').find('td[data-old-value]').each(function() {
			this.innerHTML = this['data-old-value']
			delete this['data-old-value']
			this.removeAttribute('data-old-value')
		})
	}

	const edit = () =>
	{
		if (isEditing()) return
		saveOldValue()
		selected.setAttribute('contentEditable', '')
		selected.addEventListener('blur', blurEvent)
		selected.focus()
	}

	const enterEditMode = action =>
	{
		const $actions = $(action.closest('.actions'))
		const url      = action.getAttribute('href').lLastParse(SL)
		const $close   = $(
			'<li class="close"><a href="' + url + '/close">'
			+ tr('Close')
			+ '</a></li>'
		)
		const $save = $(
			'<li class="save"><a href="' + url + '/listSave" target="#responses">'
			+ tr('Save')
			+ '</a></li>'
		)
		$actions.children().css('display', 'none')
		$actions.append($close).append($save)
		$close.build()
		$save.build()
		edit_mode = true
		select($actions.nextAll('table.list').find('tbody > tr:first-child > td:nth-of-type(2)').get(0))
	}

	const exitEditMode = action =>
	{
		const $actions = $(action.closest('.actions'))
		unselect()
		$actions.children('.close').remove()
		$actions.children('.save').remove()
		$actions.children().css('display', '')
		edit_mode = false
	}

	const isEditing = () =>
	{
		return selected.hasAttribute('contentEditable')
	}

	const isSelected = td =>
	{
		return selected === td
	}

	const moveDown = () =>
	{
		select(
			$(selected.closest('tr')).next('tr[data-id]').get(0)
				?.querySelector('td:nth-of-type(' + ($(selected).prevAll('td').length + 1) + ')')
		)
		show()
	}

	const moveEnd = last_line =>
	{
		if (last_line) {
			const $next_tr = $(selected).parent().nextAll('tr[data-id]:last')
			const $next_td = $next_tr.children('td:not(.trailing)')
			select($next_td.get($next_td.length - 1))
		}
		else {
			const $next_td = $(selected).nextAll('td:not(.trailing)')
			select($next_td.get($next_td.length - 1))
		}
		show()
	}

	const moveHome = first_line =>
	{
		select(
			first_line
				? $(selected).parent().prevAll('tr:first-of-type').children('td:first-of-type').get(0)
				: $(selected).prevAll('td:first-of-type').get(0)
		)
		show()
	}

	const moveLeft = () =>
	{
		select($(selected).prev('td').get(0))
		show()
	}

	const moveRight = () =>
	{
		select($(selected).next('td:not(.trailing)').get(0))
		show()
	}

	const moveUp = () =>
	{
		select(
			$(selected.closest('tr')).prev('tr[data-id]').get(0)
				?.querySelector('td:nth-of-type(' + ($(selected).prevAll('td').length + 1) + ')')
		)
		show()
	}

	const pageDown = () =>
	{
		const count = Math.round(
			selected.closest('tbody').getBoundingClientRect().height
			/ selected.getBoundingClientRect().height
		)
		for (let i = 0; i < count; i ++) {
			moveDown()
		}
	}

	const pageUp = () =>
	{
		const count = Math.round(
			selected.closest('tbody').getBoundingClientRect().height
			/ selected.getBoundingClientRect().height
		)
		for (let i = 0; i < count; i ++) {
			moveUp()
		}
	}

	const restoreOldValue = () =>
	{
		selected.innerHTML = old_value
	}

	const saveOldValue = () =>
	{
		if (!selected.hasAttribute('data-old-value')) {
			selected.setAttribute('data-old-value', selected.innerText.trim())
			selected['data-old-value'] = selected.innerHTML
		}
		old_value = selected.innerHTML
	}

	const select = td =>
	{
		if (isSelected(td) || !td) return
		unselect()
		if (!td || (td.tagName !== 'TD')) return
		selected = td
		selected.classList.add('selected')
	}

	const setDataPost = anchor =>
	{
		const $table     = $(anchor.closest('.actions')).nextAll('table.list')
		const post       = []
		const properties = []
		$table.find('thead > .title > th').each((column, th) => {
			const property = th.getAttribute('data-property')
			if (property) {
				properties[column] = property
			}
		})
		$table.find('td[data-old-value]').each(function() {
			const column = $(this).prevAll('td, th').length
			if (properties[column]) {
				post.push(
					this.parentNode.getAttribute('data-id')
					+ '_'
					+ encodeURIComponent(properties[column])
					+ '='
					+ encodeURIComponent(this.innerText.trim())
				)
			}
		})
		anchor.setAttribute('data-post', post.join('&'))
	}

	const show = () =>
	{
		const table         = selected.closest('table')
		const body          = table.querySelector('tbody')
		const body_rect     = body.getBoundingClientRect()
		const selected_rect = selected.getBoundingClientRect()
		const th_rect       = $(selected).prevAll('th').get(0).getBoundingClientRect()
		if (selected_rect.left < (body_rect.left + th_rect.width)) {
			body.scrollLeft = body.scrollLeft + selected_rect.left - body_rect.left - th_rect.width
			$(table).scrollBar('draw')
		}
		if (selected_rect.top < body_rect.top) {
			body.scrollTop = body.scrollTop + selected_rect.top - body_rect.top
			$(table).scrollBar('draw')
		}
		if ((selected_rect.left + selected_rect.width) > (body_rect.left + body_rect.width)) {
			body.scrollLeft = body.scrollLeft + (selected_rect.left + selected_rect.width - body_rect.left - body_rect.width)
			$(table).scrollBar('draw')
		}
		if ((selected_rect.top + selected_rect.height * 2) > (body_rect.top + body_rect.height)) {
			body.scrollTop = body.scrollTop + (selected_rect.top + selected_rect.height * 2 - body_rect.top - body_rect.height)
			$(table).scrollBar('draw')
		}
	}

	const unEdit = () =>
	{
		if (!isEditing()) return
		selected.removeAttribute('contentEditable')
		selected.removeEventListener('blur', blurEvent)
		if (selected.innerText.trim() === selected.getAttribute('data-old-value')) {
			selected.innerHTML = selected['data-old-value']
			selected.removeAttribute('data-old-value')
			delete selected['data-old-value']
		}
	}

	const unselect = () =>
	{
		if (!selected) return
		unEdit()
		selected.classList.remove('selected')
		selected = undefined
	}

	//--------------------------------------------------------------------------- list-edit > a click
	$body.build('click', 'article.list .general.actions .close > a', function(event)
	{
		event.preventDefault()
		event.stopImmediatePropagation()
		cancelEdit(this)
		exitEditMode(this)
	})

	//--------------------------------------------------------------------------- list-edit > a click
	$body.build('click', 'article.list .general.actions .list-edit > a', function(event)
	{
		event.preventDefault()
		event.stopImmediatePropagation()
		enterEditMode(this)
	})

	//--------------------------------------------------------------------------- list-edit > a click
	$body.build('click', 'article.list .general.actions .save > a', function()
	{
		setDataPost(this)
		exitEditMode(this)
	})

	//------------------------------------------------------------------------------------ body click
	$body.build('click', 'body', event =>
	{
		if (
			!edit_mode
			|| !selected
			|| (event.target.closest('tbody') === selected.closest('tbody'))
			|| event.target.closest('.scrollbar')
		) {
			return
		}
		unselect()
	})

	//---------------------------------------------------------------------- article.list tbody click
	$body.build('click', 'article.list tbody', event =>
	{
		if (!edit_mode) return
		const td = event.target.closest('td')
		if (!td) return
		// 2nd click : edit
		if (isSelected(td)) {
			return edit()
		}
		// 1st click : select
		select(td)
	})

	//------------------------------------------------------------------------------ document keydown
	const oldKeyDown = document.onkeydown
	document.onkeydown = (event) =>
	{
		if (!edit_mode || !selected) {
			if (oldKeyDown) oldKeyDown.call(document, event)
			return
		}
		let prevent = false

		if (event.key === 'Tab') {
			prevent = true
			event.shiftKey ? moveLeft() : moveRight()
		}

		else if (isEditing()) switch (event.key) {
			case 'Enter':
				prevent = true
				unEdit()
				moveDown()
				break
			case 'Escape':
				restoreOldValue()
				unEdit()
				break
			case 'F2':
				unEdit()
				break
		}

		else switch (event.key)
		{
			case 'ArrowDown':
				moveDown()
				break
			case 'ArrowLeft':
				moveLeft()
				break
			case 'ArrowRight':
				moveRight()
				break
			case 'ArrowUp':
				moveUp()
				break
			case 'End':
				moveEnd(event.ctrlKey)
				break
			case 'Enter':
				prevent = true
				edit()
				break
			case 'Escape':
				unselect()
				break
			case 'F2':
				edit()
				break
			case 'Home':
				moveHome(event.ctrlKey)
				break
			case 'PageDown':
				pageDown()
				break
			case 'PageUp':
				pageUp()
				break
			default:
				if (event.key.length <= 2) {
					if (!selected.hasAttribute('data-old-value')) {
						selected.setAttribute('data-old-value', selected.innerText.trim())
						selected['data-old-value'] = selected.innerHTML
					}
					selected.innerHTML = ''
					edit()
				}
				break
		}

		if (prevent) return event.preventDefault()
		if (oldKeyDown) oldKeyDown.call(document, event)
	}

})
