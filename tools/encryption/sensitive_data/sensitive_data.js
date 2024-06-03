$(document).ready(function()
{
	const $body = $('body')

	function getCookie(name)
	{
		const value = '; ' + document.cookie
		const parts = value.split('; ' + name + '=')
		if (parts.length === 2) return parts.pop().split(';').shift()
	}

	$body.build('each', 'ul.general.actions > .sensitive_data > a', function()
	{
		const $anchor = $(this)

		$anchor.click(function(event)
		{
			event.preventDefault()
			event.stopImmediatePropagation()
			const $parent = $anchor.parent()
			let   $input  = $parent.find('input')
			const found   = $input.length
			if (!found) {
				$input = $parent.closest('form').length
					? $('<input data-action="' + $anchor.attr('href') + '" data-target="' + $anchor.attr('target') + '" name="sensitive_password" placeholder="' + tr('cipher key') + '" type="password">')
					: $('<form action="' + $anchor.attr('href') + '" method="post" target="' + $anchor.attr('target') + '"><input name="sensitive_password" placeholder="' + tr('cipher key') + '" type="password"></form>')
			}
			$parent.prepend($input)
			if (!found) {
				$input.build()
			}
			$input.focus()

			const submit = function($input)
			{
				const $form = $input.closest('form')
				if ($input.data('action')) {
					$form.attr('action', $input.data('action'))
				}
				if ($input.data('target')) {
					$form.attr('target', $input.data('target'))
				}
				$form.submit()
			}

			$input.keydown(function(event)
			{
				if (event.keyCode !== 13) {
					return
				}
				event.preventDefault()
				event.stopImmediatePropagation()
				submit($(this))
			})

			if (getCookie('sensitive_password')) {
				$anchor.css('display', 'inline-block')
				$input.addClass('hidden')
				$input.val('')
				submit($input)
			}
		})
	})

	$body.build('dblclick', 'article li.sensitive > div', function(event)
	{
		const target = event.target
		if ((['input', 'textarea'].includes(target.type)) && !target.readOnly) {
			return
		}
		$(this).closest('article').find('ul.general.actions > .sensitive_data > a').click()
	})

})
