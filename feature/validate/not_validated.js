$('document').ready(function()
{
	$('.invalid_properties').build(function()
	{
		this.each(function()
		{
			var $this = $(this);

			// remove existing highlighted inputs
			$('.bad').removeClass('bad');

			// highlight inputs with validation errors
			$this.find('.property').each(function () {
				var property_name = $(this).data('property');
				var $property = $('#' + property_name);
				$property.find('label + div').each(function () {
					$(this).find('input:not(:checkbox), select, textarea').filter(':visible').addClass(
						'bad'
					);
					$(this).children('span.set').closest('div').addClass('bad');
				});
				$('input[name="' + property_name + '[0]"]').next('input').addClass('bad');
				$('.bad').first().focus();
			});

			// in case of validation warning, the 'cancel' button closes the message box
			$('.invalid_properties_warning .cancel a').click(function (event)
			{
				$('#messages').empty();
				event.preventDefault();
				event.stopImmediatePropagation();
			});

		});
	});
});
