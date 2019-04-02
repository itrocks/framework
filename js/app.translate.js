$(document).ready(function()
{

	//--------------------------------------------------------- input[data-translate=data] ctrl+click
	$('body').build('click', 'input[data-translate=data]', function(event)
	{
		if (event.ctrlKey || event.metaKey) {
			var $this         = $(this);
			var $form         = $this.closest('article');
			var class_path    = $form.data('class').repl(BS, SL);
			var id            = $form.data('id');
			var property_name = $this.attr('name');
			var uri           = '/ITRocks/Framework/Locale/Translation/Data/form/';
			uri += class_path + SL + id + SL + property_name;
			redirect(uri, '#popup', $this);
		}
	});

});
