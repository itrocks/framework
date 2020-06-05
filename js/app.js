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
			auto_empty: {'main': '#query, #responses', '#modal': 'ul#query', '#responses': '#query'},
			auto_empty_except: '.auto-redirect, .keep-response',
			draggable_blank: 'header',
			history: {
				condition: 'h2',
				title:     'h2',
				post:      ['/email$'],
				without_get_vars: ['/email\\?', '/list\\?', '/output\\?', '\\?save_name=']
			},
			url_append: 'as_widget'
		});
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
