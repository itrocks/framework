$(document).ready(function()
{

	//------------------------------------------------------------------------ input[data-conditions]
	$('body').build('each', '[data-conditions]', function()
	{
		const $this       = $(this)
		const conditions  = $this.data('conditions').replace(/\(.*\)/g)
		const will_change = {}

		$.each(conditions.split(';'), (condition_key, condition) => {
			let index
			let operator = '='
			if ((index = condition.indexOf('>')) > -1) {
				operator = ((condition[index + 1] === '=') ? '>=' : '>')
			}
			else if ((index = condition.indexOf('<')) > -1) {
				operator = ((condition[index + 1] === '=') ? '<=' : '<')
			}
			condition = condition.split(operator).map(condition => condition.trim())
			let $condition
			if (will_change.hasOwnProperty(condition[0])) {
				$condition = will_change[condition[0]]
			}
			else {
				const $form = $this.closest('form')
				$condition = $form.find('[name="id_' + condition[0] + DQ + ']')
				$condition = $condition.length
					? $condition.next()
					: $form.find('[name=' + DQ + condition[0] + DQ + ']')
				will_change[condition[0]] = $condition
			}
			let condition_name = $condition.attr('name')
			if (!condition_name) {
				condition_name = $condition.prev().attr('name')
			}
			if ((typeof $this.data('conditions')) === 'string') {
				$this.data('conditions', {})
			}
			if (!$this.data('conditions').hasOwnProperty(condition_name)) {
				$this.data('conditions')[condition_name] = { element: $condition, values: {}}
			}
			$.each(condition[1].split(','), (value_key, value) => {
				if (operator !== '=') {
					value = (operator + value)
				}
				$this.data('conditions')[condition_name].values[value] = value
			})
			let this_name = $this.attr('name')
			if (!this_name) {
				this_name = $this.prev().attr('name')
			}
			if ($condition.data('condition-of')) {
				$condition.data('condition-of')[this_name] = $this
			}
			else {
				const condition_of = {}
				condition_of[this_name] = $this
				$condition.data('condition-of', condition_of)
			}
		})

		$.each(will_change, (condition_name, $condition) => {
			if ($condition.data('condition-change')) {
				$condition.change()
				return
			}
			$condition.data('condition-change', true)
			$condition.change(function()
			{
				const $this = $(this)
				$.each($this.data('condition-of'), (element_name, $element) => {
					let show = true
					$.each($element.data('conditions'), (condition_name, condition) => {
						let found = false
						$.each(condition.values, (value) => {
							const element_type = condition.element.attr('type')
							const element      = (element_type === 'radio')
								? condition.element.filter(':checked')
								: condition.element
							const element_value = (element_type === 'checkbox')
								? (element.is(':checked') ? '1' : '0')
								: element.val()
							if (value === '@empty') {
								found = !element_value.length
							}
							else if (value === '@set') {
								found = element_value.length
							}
							else if (value.startsWith('>')) {
								found = value.startsWith('>=')
									? (parseInt(element_value) >= parseInt(value.substring(2)))
									: (parseInt(element_value) > parseInt(value.substring(1)))
							}
							else if (value.startsWith('<')) {
								found = value.startsWith('<=')
									? (parseInt(element_value) <= parseInt(value.substring(2)))
									: (parseInt(element_value) < parseInt(value.substring(1)))
							}
							else {
								found = (element_value === value)
							}
							return !found
						})
						return (show = found)
					})
					let name = $element.attr('name')
					if (!name) {
						name = $element.data('name')
					}
					if (!name) {
						name = $element.prev().attr('name')
					}
					if (!name && $element.is('label')) {
						name = $element.closest('[id]').attr('id')
					}
					if (name.startsWith('id_')) {
						name = name.substring(3)
					}
					name = name.repl('[', '.').repl(']', '').repl('id_', '')
					let $field = $element.closest('[id="' + name + '"]')
					if (!$field.length) {
						$field = $element.closest('[data-name="' + name + '"]')
					}
					if (!$field.length) {
						$field = $element.parent().children()
					}
					const $input_parent = $field.is('input, select, textarea') ? $field.parent() : $field
					if (show) {
						// when shown, get the locally saved value back (undo restores last typed value)
						$input_parent.find('input, select, textarea').each(function() {
							const $this = $(this)
							// show can be called on already visible and valued element : ignore them
							if ($this.data('value')) {
								$this.val($this.data('value'))
							}
							$this.removeData('hidden')
							$this.removeData('value')
						})
						$field.show()
					}
					else {
						$field.hide()
						// when hidden, reset value to empty
						$input_parent.find('input, select, textarea').each(function() {
							const $this = $(this)
							if (!$this.data('hidden')) {
								$this.data('hidden', true)
								$this.data('value', $this.val())
							}
							// never empty values on required fields TODO should be managed in validator
							if (!$this.attr('required') && !$this.data('required')) {
								$this.val('')
							}
						})
					}
				})
			})
			$condition.change()
		})
	})

})
