(function($)
{
	$.fn.listFilter = function()
	{

		//--------------------------------------------------------------------------------------- keyup
		this.keyup(function()
		{
			var $input = $(this);
			var text   = $input.val();
			$input.parent().find('li').each(function()
			{
				var $li = $(this);
				if ($li.text().toLowerCase().indexOf(text.toLowerCase()) > -1) {
					$li.show();
				}
				else {
					$li.hide();
				}
			})
		});

		var $parent = this.parent();

		//----------------------------------------------------------------------------- input.all click
		$parent.find('input.all').click(function()
		{
			var $input  = $(this);
			var $parent = $input.parent();
			$parent.find('input[type=checkbox]').attr('checked', true);
			$input.hide();
			$parent.find('input.none').show();
		});

		//---------------------------------------------------------------------------- input.none click
		$parent.find('input.none').click(function()
		{
			var $input  = $(this);
			var $parent = $input.parent();
			$parent.find('input[type=checkbox]').attr('checked', false);
			$input.hide();
			$parent.find('input.all').show();
		});

		$parent.find($parent.find('input[checked]').length ? 'input.none' : 'input.all').hide();

	};
})( jQuery );
