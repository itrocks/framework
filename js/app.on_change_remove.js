$(document).ready(() =>
{
	const $body = $('body')

	//--------------------------------------------------------------------------------------- onEvent
	const on_event_pool = []
	const onEvent = function(event)
	{
		const $this  = $(this)
		const $form  = $this.closest('form')
		let   target = '#responses'
		const uri    = $this.data(event)
		$.each(uri.split(','), (key, uri) => {
			if (uri.indexOf(SP) > -1) {
				target = uri.rParse(SP)
				uri    = uri.lParse(SP)
			}

			// on-event-pool avoid calling several times the same handler on several changed inputs
			const on_event_pool_index = on_event_pool.indexOf(uri)
			if (on_event_pool_index >= 0) return
			on_event_pool.push(uri)

			uri = window.app.uri_base + SL + uri
			if ($this.prop('name')) {
				uri += SL + $this.prop('name')
			}
			uri += '?as_widget'

			$.post(uri, $form.formSerialize(), (data) =>
			{
				if (data) {
					if (['{', '['].indexOf(data.substr(0, 1)) > -1) {
						$.each(JSON.parse(data), (name, value) => {
							if ((name.indexOf('#') > -1) || (name.indexOf('.') > -1)) {
								$(name).html(value).build()
							}
							else {
								setFieldValue($form, name, value)
							}
						})
					}
					else {
						const $target = $(target)
						if ($target.is('input')) {
							$target.attr('value', data).val(data)
						}
						else if ($target.is('select')) {
							$target.val(data)
						}
						else {
							$target.html(data).build()
						}
					}
				}
				setTimeout(() => { on_event_pool.splice(on_event_pool_index, 1) })
			})

		})
	}

	//----------------------------------------------------------------------------- prepareCollection
	/**
	 * Prepare a collection :
	 * - empty it if value is null
	 * - if field_name includes a reference to a non-existing line : add it
	 *
	 * @param $field jQuery object the field element, with an id.component-objects#field_name_root
	 * @param search string the search selector for the targeted field. If empty : empty collection
	 * @return jQuery if set, caller can add a line to this collection if he wants
	 */
	const prepareCollection = ($field, search) =>
	{
		const $collection = $field.is('ul, ol, table') ? $field : $field.children('ul, ol, table')
		if (!$collection.length) {
			return null
		}
		if (!search) {
			$collection.data('itrocks_last_index', -1)
			// TODO LOW call "click on minus", to allow on-remove
			$collection.find('> li.data').remove()
			return null
		}
		if (!$collection.find(search).length) {
			return $collection.data('addLine').call()
		}
	}

	//--------------------------------------------------------------------------------- setFieldValue
	/**
	 * Use always this method to set a new value to a field
	 * For a simple field, value is a string (or similar)
	 * For a combo field, value is [id, string representation of value]
	 *
	 * @param $form      jQuery object for the targeted form
	 * @param field_name string
	 * @param value      Array|string
	 * @todo should be able to set value for any form field tag (like select)
	 */
	const setFieldValue = ($form, field_name, value) =>
	{
		if (field_name.substr(0, 1) === '?') {
			modalWindow(value.title, value.text, value.choices, (choice) =>
			{
				if (choice === 'confirm') {
					setFieldValue($form, field_name.substr(1), value.value)
				}
			})
			return
		}
		const search = 'input[name=' + DQ + field_name + DQ + ']'
			+ ', input[data-name=' + DQ + field_name + DQ + ']'
			+ ', select[name=' + DQ + field_name + DQ + ']'
			+ ', select[data-name=' + DQ + field_name + DQ + ']'
			+ ', textarea[name=' + DQ + field_name + DQ + ']'
			+ ', textarea[data-name=' + DQ + field_name + DQ + ']'
		let $input       = $form.find(search)
		let do_change    = true
		let string_value = null
		// id
		if (!$input.length) {
			$input = $form.find(search.repl('=' + DQ, '="id_'))
		}
		// new element for collection / empty collection
		if (!$input.length) {
			$input = $form.find('li.component-objects#' + field_name)
			if ($input.length) {
				prepareCollection($input)
				return
			}
			if (value === null) {
				return
			}
			$input = $form.find('li.component-objects#' + field_name.lParse('['))
			if (!$input.length) {
				return
			}
			const $added_line = prepareCollection($input, search)
			$input = $form.find(search)
			if ($added_line && !$input.length) {
				let $collection = $added_line.closest('.component-objects, .objects')
				if (!$collection.is('ul, ol, table')) {
					$collection = $collection.children('ul, ol, table')
				}
				$added_line.remove()
				$collection.data('itrocks_last_index', $collection.data('itrocks_last_index') - 1)
			}
		}
		// not found
		if (!$input.length) {
			return
		}

		// simple value
		if (((typeof value) === 'object') && value && !value.hasOwnProperty('0')) {
			$.each(value, (attribute, value) => {
				const $what = $input.next('input').attr(attribute) ? $input.next() : $input
				if (attribute.startsWith('data-')) {
					$what.removeData(attribute.substr(5))
				}
				$what.attr(attribute, value)
				if (attribute === 'value') {
					$what.change()
				}
			})
			return
		}

		// an array with the value (and id) and its string representation
		if (Array.isArray(value)) {
			string_value = ((value.length > 1) ? value[1] : '')
			value        = (value.length       ? value[0] : '')
		}

		// todo comment what is this case about (value starting with ':') ?
		if (((typeof value) === 'string') && (value.substr(0, 1) === ':')) {
			if (!$input.val().length) {
				value = value.substr(1)
			}
			else {
				do_change = false
			}
		}

		if (
			($input.val().length || $input.text().trim().length)
			&& ($input.is(':focus') || $input.next().is(':focus'))
			&& ($input.is(':visible') || $input.next().is(':visible'))
			&& (!$input.is('[readonly]') && !$input.next().is('[readonly]'))
		) {
			do_change = false
		}

		if (do_change) {
			if ($input.is('textarea')) {
				$input.text(value)
			}
			else {
				$input.attr('value', value).val(value)
			}
			if ((string_value !== null)) {
				$input.next().attr('value', string_value).val(string_value).change()
			}
			else if ($input.is('[readonly]') && $input.next().is('input[readonly]')) {
				$input.next().val(tr(value))
			}

			$input.change()
		}
	}

	//----------------------------------------------------------------- input[data-on-change] .change
	const selector1 = 'input[data-on-change], select[data-on-change], textarea[data-on-change]'
	$body.build('change', selector1, function()
	{
		const $this = $(this)
		if (
			(($this.attr('type') === 'checkbox') || !$this.data('realtime-change'))
			&& !$this.is('input:focus, textarea:focus')
		) {
			// ensure that it will be called after the blur() event (datetime compatibility)
			setTimeout(() => { onEvent.call(this, 'on-change') })
		}
	})

	//---------------------------------------------------------------- input[realtime-change] .change
	const selector2 = 'input[data-realtime-change], select[data-realtime-change],'
		+ ' textarea[data-realtime-change]'
	$body.build('keyup', selector2, function()
	{
		if ($(this).data('on-change')) {
			onEvent.call(this, 'on-change')
		}
	})

	//--------------------------------------------------------- table[data-on-remove] td.minus .click
	$body.build('click', '.component-objects .minus', function()
	{
		const $this    = $(this)
		const selector = 'ul[data-on-remove], ol[data-on-remove], table[data-on-remove]'
		const $block   = $this.closest(selector)
		if (!$block.length) {
			return
		}
		// do not execute before the row has been removed : the event happens AFTER removal
		const call = () => {
			$this.closest(selector).length
				? setTimeout(call)
				: onEvent.call($block, 'on-remove')
		}
		call()
	})

})
