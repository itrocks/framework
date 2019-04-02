$(document).ready(function()
{
	var $body = $('body');

	//------------------------------------------------------------------------------- refreshOrdering
	var refreshOrdering = function()
	{
		var ordering = 0;
		$(this).find('>tr>td.ordering>input.customized.integer[name*="[ordering]"][type="hidden"]')
			.each(function() {
				$(this).val(++ordering);
			});
	};

	//----------------------- article > form input.customized.integer[name*="[ordering]"]:not([type])
	$body.build(
		'call', 'article > form input.customized.integer[name*="[ordering]"]:not([type])',
		function()
		{
			this.each(function()
			{
				var $input  = $(this);
				var $parent = $input.parent();
				if ($parent.is('td')) {
					var position = $parent.prevAll().length + 1;
					$input.closest('table').find('>thead>tr>th:nth-child(' + position + ')')
						.addClass('no-autowidth');
					$parent.addClass('ordering');
					$input.attr('type', 'hidden');
				}
			});

			var $tbody = this.parent().filter('td').closest('tbody');
			$tbody.sortable({
				handle: 'td.ordering',
				stop:   function() { refreshOrdering.call(this); }
			});
		}
	);

	//------------------------------------------------------------------------ tr.new refreshOrdering
	$body.build('each', 'tr.new', function()
	{
		var $tr = $(this);
		if ($tr.children('td.ordering').length) {
			refreshOrdering.call($tr.closest('tbody'));
		}
	});

});
