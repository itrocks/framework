$(document).ready(function() {

	//----------------------------------------------------------------------------------- complyError
	var complyError = function(password)
	{
		var contains_digit   = password.replace(/\D/g, '').length;
		var contains_special = password.replace(/([0-9]|[A-Z]|[a-z]|[àáâãäåçèéêëìíîïðòóôõöùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÒÓÔÕÖÙÚÛÜÝŸ])/g, '').length;
		var long_enough      = (password.length >= 10);
		return !(contains_digit && contains_special && long_enough);
	};

	//------------------------------------------------------------------- input[name^=password] keyup
	$('body').build('keyup', 'article.password.reset input', function()
	{
		var $form        = $(this).closest('form');
		var $login       = $form.find('li.login');
		var $password    = $form.find('li.password');
		var $password2   = $form.find('li.password2');
		var $submit      = $form.find('.actions > .reset');
		var login        = $login.find('input').val().trim();
		var password     = $password.find('input').val();
		var password2    = $password2.find('input').val();
		var comply_error = complyError(password);
		var login_error  = !login.length;
		var match_error  = (password !== password2);

		comply_error ? $password.addClass('error')  : $password.removeClass('error');
		match_error  ? $password2.addClass('error') : $password2.removeClass('error');

		if (comply_error || login_error || match_error) {
			$form.addClass('disabled');
			$submit.find('input').attr('disabled', true);
			$submit.addClass('disabled');
		}
		else {
			$form.removeClass('disabled');
			$submit.find('input').removeAttr('disabled');
			$submit.removeClass('disabled');
		}
	});

});
