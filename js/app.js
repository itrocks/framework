$(document).ready(function()
{
	var $body = $('body');
	window.zindex_counter = 0;

	$body.build('call', 'always', function()
	{
		this.autofocus();
		this.confirm();
		this.tooltip();

		// change all a / form target="#target" to ajax calls
		this.xtarget({
			auto_empty:      {'main': 'div#messages'},
			draggable_blank: 'header',
			history: {
				condition:        'h2',
				title:            'h2',
				without_get_vars: ['.*/list\\?.*']
			},
			url_append: 'as_widget'
		});
	});

	//---------------------------------------------------------------- div#messages draggable & click
	$body.build('call', 'div#messages', function()
	{
		this.draggable().click(function(event)
		{
			if (event.offsetX > (this.clientWidth - 10) && (event.offsetY < 10)) {
				$(this).empty();
			}
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
			$(this).closest('div.popup').css('z-index', ++window.zindex_counter);
		});
	});

	//----------------------------------------------------------------------------- nav#menu minimize
	$body.build('call', 'nav#menu', function()
	{
		this.minimize({ absolute_next: true });
	});

	//--------------------------------------------------------------------- build simple plugin calls
	$body.build('call', '.auto_height',                    $.fn.autoHeight);
	$body.build('call', '.auto_width:not(table):not(ul)',  $.fn.autoWidth);
	$body.build('call', 'input:visible, textarea:visible', $.fn.changeState);
	$body.build('call', '.list-filter',                    $.fn.listFilter);
	$body.build('call', '.tabber',                         $.fn.tabber);
	$body.build('call', '.vertical.scrollbar',             $.fn.verticalscrollbar);

});