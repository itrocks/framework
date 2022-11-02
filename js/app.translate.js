$(document).ready(function()
{

	//--------------------------------------------------------- input[data-translate=data] ctrl+click
	$('body').build('click', 'input[data-translate=data]', function(event)
	{
		if (event.ctrlKey || event.metaKey) {
			const $this         = $(this)
			const $form         = $this.closest('article')
			const class_path    = $form.data('class').repl(BS, SL)
			const id            = $form.data('id')
			const property_name = $this.attr('name')
			const uri           = '/ITRocks/Framework/Locale/Translation/Data/form/'
				+ class_path + SL + id + SL + property_name
			redirect(uri, '#popup', $this)
		}
	})

})
