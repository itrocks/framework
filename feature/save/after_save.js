$(document).ready(function()
{
	const $body = $('body')

	//--------------------------------------------------------------------------- a[after-save] click
	$body.build('each', 'a.after-save', function()
	{
		const $anchor = $(this)
		const $window    = $('main > article')
		const class_name = $anchor.data('class')
		const id         = $anchor.data('id')
		const new_object = $anchor.data('new-object')
		if (
			($window.data('class') === class_name)
			&& (
				(id && ($window.data('id') === id))
				|| (new_object && !$window.data('id'))
			)
		) {
			redirect($anchor.attr('href'), $anchor.attr('target'))
		}
	})

	//--------------------------------------------------------- button[after-save] close / fill combo
	$body.build('each', 'button.after-save', function()
	{
		const $button = $(this)

		if ($button.data('close')) {
			const close = $button.data('close')
			$('#' + close).remove()
		}

		if ($button.data('fill-combo')) {
			const fill_combo  = $button.data('fill-combo')
			const id          = $button.data('id')
			const value       = $button.data('value')
			const $fill_combo = $('[name=' + DQ + fill_combo + DQ + ']')
			if ($fill_combo.length) {
				$fill_combo.val(id)
				$fill_combo.next().val(value).focus().keyup()
			}
		}
	})

})
