$(document).ready(function()
{

	$('body').build({
		callback: $.fn.sortContent,
		event:    'call',
		priority: 1,
		selector: 'select:not([data-ordered=true])'
	});

});
