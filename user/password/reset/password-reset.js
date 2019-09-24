$(document).ready(function() {

	//------------------------------------------------------------------- input[name^=password] keyup
	$('body').build('keyup', 'article.password.reset input', function()
	{
		var $form      = $(this).closest('form');
		var $login     = $form.find('input[name=login]');
		var $password  = $form.find('input[name=password]');
		var $password2 = $form.find('input[name=password2]');
		var $submit    = $form.find('input[type=submit]');
		if (
			($password.val() !== $password2.val())
			|| ($password.val().length < 4)
			|| !$login.val().trim().length
		) {
			$form.addClass('disabled');
			$submit.attr('disabled', true);
			$submit.parent().addClass('disabled');
		}
		else {
			$form.removeClass('disabled');
			$submit.removeAttr('disabled');
			$submit.parent().removeClass('disabled');
		}
	});

});
