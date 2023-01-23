$(document).ready(() => {
	const $body = $('body')
	let   old_value
	let   selected

	const blurEvent = () =>
	{
		unEdit()
		console.log('blur')
	}

	const edit = () =>
	{
		if (isEditing()) return
		saveOldValue()
		selected.setAttribute('contentEditable', '')
		selected.addEventListener('blur', blurEvent)
		selected.focus()
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
		old_value = selected.innerHTML
	}

	const select = td =>
	{
		if (isSelected(td)) return
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
	}

	const unselect = () =>
	{
		if (!selected) return
		selected.classList.remove('selected')
		if (isEditing()) {
			unEdit()
		}
		selected = undefined
	}

	//------------------------------------------------------------------------------------ body click
	$body.build('click', 'body', event =>
	{
		if (!selected || (event.target.closest('tbody') === selected.closest('tbody'))) return
		unselect()
	})

	//---------------------------------------------------------------------- article.list tbody click
	$body.build('click', 'article.list tbody', event =>
	{
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
		if (!selected) return
		let prevent = false

		if (event.key === 'Tab') {
			prevent = true
			event.shiftKey ? moveLeft() : moveRight()
		}

		else if (isEditing()) switch (event.key) {
			case 'Enter':
				prevent = true
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
