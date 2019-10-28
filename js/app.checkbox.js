$(document).ready(function()
{
	var $body = $('body');

	//--------------------------------------------------- input[id^=cb-][type=checkbox] => label[for]
	$body.build('each', 'input[id^=cb-][type=checkbox]', function()
	{
		var $checkbox = $(this);
		var $label    = $checkbox.prevAll('label');
		if (!$label.length) {
			$label = $checkbox.parent().prevAll('label');
		}
		if (!$label.length) {
			return;
		}
		$label.attr('for', $checkbox.attr('id'));
	});

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

	$body.build({
		callback: $.fn.sortContent,
		event:    'call',
		priority: 1,
		selector: 'select:not([data-ordered=true])'
	});

});
