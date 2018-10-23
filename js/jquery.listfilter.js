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

	};
})( jQuery );
