$(document).ready(function()
{

	//------------------------------------------------------------------- input[type=checkbox] change
	$('input[type=checkbox]').build('change', function()
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
	$('input[type=checkbox][readonly]').build('click', function(event)
	{
		event.preventDefault();
	});

	$('select:not([data-ordered=true])').build($.fn.sortContent);

});
