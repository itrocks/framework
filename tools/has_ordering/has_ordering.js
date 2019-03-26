$(document).ready(function() {

	//------------------------------------------------------------------------------- refreshOrdering
	var refreshOrdering = function()
	{
		var ordering = 0;
		$(this).find('>tr>td.ordering>input.customized.integer[name*="[ordering]"][type="hidden"]')
			.each(function() {
				$(this).val(++ordering);
			});
	};

	//-------------------------------------------------------------- form.window input ordering build
	$('article > form').build(function() {
		var $this = this;
		if (!$this.closest('article > form').length) return;

		var selector = 'input.customized.integer[name*="[ordering]"]:not([type])';
		var $ordering = $this.find(selector);
		$ordering.each(function()
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

		var $tbody = $ordering.parent().filter('td').closest('tbody');

		$tbody.sortable({
			handle: 'td.ordering',
			stop:   function() { refreshOrdering.call(this); }
		});
	});

	//----------------------------------------------------------------------- tr.new refresh ordering
	$('tr.new').build(function() {
		var $tr = $(this).closest('tr.new');
		if (!$tr.length) return;
		$tr.each(function() {
			if ($(this).children('td.ordering').length) {
				refreshOrdering.call($(this).closest('tbody'));
			}
		});
	});

});
