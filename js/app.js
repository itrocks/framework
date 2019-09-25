$(document).ready(function()
{
	var $body = $('body');

	window.id_index = 0;

	$body.build('call', 'always', function()
	{
		this.autofocus();
		this.confirm();
		this.tooltip();

		// change all a / form target="#target" to ajax calls
		this.xtarget({
			auto_empty:      {'main': 'section#responses'},
			draggable_blank: 'header',
			history: {
				condition:        'h2',
				title:            'h2',
				without_get_vars: ['.*/list\\?.*', '.*/output\\?.*', '.*\\?save_name=']
			},
			url_append: 'as_widget'
		});
	});

	//----------------------------------------------------------- body[class] = main > article[class]
	$body.build('call', 'main > article', function()
	{
		var classes = this.attr('class');
		var $body   = $('body');
		classes ? $body.attr('class', classes) : $body.removeAttr('class');
	});

	//----------------------------------------------------------------------- .ui-draggable mousedown
	/**
	 * draggable objects brought to front on mousedown
	 */
	$body.build('call', 'div.popup > article > header', function()
	{
		this.mousedown(function()
		{
			$(this).closest('div.popup').css('z-index', zIndexInc());
		});
	});

	//--------------------------------------------------------------------- build simple plugin calls
	// must declare autoWidth before autoHeight to avoid height bugs
	$body.build('call', '.auto_width:not(table):not(ul)',  $.fn.autoWidth);
	$body.build('call', '.auto_height',                    $.fn.autoHeight);
	$body.build('call', 'input:visible, textarea:visible', $.fn.changeState);
	$body.build('call', '.list-filter',                    $.fn.listFilter);
	$body.build('call', '.tabber',                         $.fn.tabber);
	$body.build('call', '.vertical.scrollbar',             $.fn.verticalscrollbar);

});
