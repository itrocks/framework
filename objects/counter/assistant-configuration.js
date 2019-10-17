$(document).ready(function()
{

	//----------------------------------------------------------------- .apply-counter-form-assistant
	$('body').build('each', '.apply-counter-form-assistant', function()
	{
		var $this = $(this);
		$this.hide();
		var format     = $this.find('.format').text().trim();
		var last_value = $this.find('.last_value').text().trim();
		$('input[data-name=format]').val(format);
		$('input[data-name=last_value]').val(last_value);
	});

});
