$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------- input[type=checkbox] change
	$body.build('change', 'input[type=checkbox]', function()
	{
		var $checkbox = $(this);
		var $input    = $checkbox.prev().filter('input[type=hidden]');
		if ($input.length) {
			var old_check = $input.val();
			var check     = $checkbox.is(':checked') ? $checkbox.val() : '0';
			var nullable  = String($checkbox.data('nullable'));
			if (nullable.length) {
				if (old_check === nullable) {
					check = '';
					$checkbox.attr('checked', false);
				}
			}
			$input.val(check).change();
		}
	});

	//---------------------------------------------------------- input[type=checkbox][readonly] click
	$body.build('click', 'input[type=checkbox][readonly]', function(event)
	{
		event.preventDefault();
	});

	$body.build('call', 'select:not([data-ordered=true])', $.fn.sortContent);

});
