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
			this.innerHTML = this.getAttribute('data-old-value')
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
			$(selected.closest('tr')).next('tr').get(0)
				?.querySelector('td:nth-of-type(' + ($(selected).prevAll('td').length + 1) + ')')
		)
	}

	const moveLeft = () =>
	{
		select($(selected).prev('td').get(0))
	}

	const moveRight = () =>
	{
		select($(selected).next('td').get(0))
	}

	const moveUp = () =>
	{
		select(
			$(selected.closest('tr')).prev('tr').get(0)
				?.querySelector('td:nth-of-type(' + ($(selected).prevAll('td').length + 1) + ')')
		)
	}

	const restoreOldValue = () =>
	{
		selected.innerHTML = old_value
	}

	const saveOldValue = () =>
	{
		if (!selected.hasAttribute('data-old-value')) {
			selected.setAttribute('data-old-value', selected.innerHTML)
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

	const unEdit = () =>
	{
		if (!isEditing()) return
		selected.removeAttribute('contentEditable')
		selected.removeEventListener('blur', blurEvent)
		selected.innerHTML = selected.innerText.trim()
		if (selected.getAttribute('data-old-value').trim() === selected.innerHTML.trim()) {
			selected.innerHTML = selected.getAttribute('data-old-value')
			selected.removeAttribute('data-old-value')
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
		exitEditMode(this)
	})

	//------------------------------------------------------------------------------------ body click
	$body.build('click', 'body', event =>
	{
		if (!edit_mode || !selected || (event.target.closest('tbody') === selected.closest('tbody'))) {
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
			default:
				if (event.key.length <= 2) {
					selected.innerHTML = ''
					edit()
				}
				break
		}

		if (prevent) return event.preventDefault()
		if (oldKeyDown) oldKeyDown.call(document, event)
	}

})
