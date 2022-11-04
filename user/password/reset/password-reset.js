$(document).ready(function() 
{

	//----------------------------------------------------------------------------------- complyError
	const complyError = function(password)
	{
		const contains_digit   = password.replace(/\D/g, '').length
		const contains_letter  = password.replace(/[^a-zA-Z]/g, '').length
		const contains_special = password.replace(/([0-9]|[A-Z]|[a-z]|[àáâãäåçèéêëìíîïðòóôõöùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ])/g, '').length
		const long_enough      = (password.length >= 10)
		return !(contains_digit && contains_letter && contains_special && long_enough)
	}

	//------------------------------------------------------------------- input[name^=password] keyup
	$('body').build('keyup', 'article.password.reset input', function()
	{
		const $form        = $(this).closest('form')
		const $login       = $form.find('li.login')
		const $message     = $form.children('p.message')
		const $password    = $form.find('li.password')
		const $password2   = $form.find('li.password2')
		const $submit      = $form.find('.actions > .reset')
		const login        = $login.find('input').val().trim()
		const password     = $password.find('input').val()
		const password2    = $password2.find('input').val()
		const comply_error = complyError(password)
		const login_error  = !login.length
		const match_error  = (password !== password2)

		comply_error ? $message.addClass('error')   : $message.removeClass('error')
		comply_error ? $password.addClass('error')  : $password.removeClass('error')
		match_error  ? $password2.addClass('error') : $password2.removeClass('error')

		if (comply_error || login_error || match_error) {
			$form.addClass('disabled')
			$submit.find('input').attr('disabled', true)
			$submit.addClass('disabled')
		}
		else {
			$form.removeClass('disabled')
			$submit.find('input').removeAttr('disabled')
			$submit.removeClass('disabled')
		}
	})

})
