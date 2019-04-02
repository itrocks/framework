$(document).ready(function()
{
	var $body = $('body');

	$body.build('call', '.object', function()
	{
		this.draggable(
		{
			appendTo: 'body',
			containment: 'body',
			cursorAt: {left: 10, top: 10},
			delay: 500,
			scroll: false,

			helper: function () {
				var $this = $(this);
				var class_name = $this.data('class');
				var id = $this.data('id');
				var data_throw = $this.data('throw');
				if (class_name === undefined) {
					class_name = $this.closest('[data-class]').data('class');
				}
				if (id === undefined) {
					id = $this.closest('[data-id]').data('id');
				}
				if (data_throw === undefined) {
					data_throw = $this.closest('[data-throw]').data('throw');
				}
				var text = $this.find('h2').text();
				if (!text.length) {
					text = $this.text();
				}
				return $('<div>')
					.addClass('object')
					.attr('data-class', class_name)
					.attr('data-id', id)
					.attr('data-throw', data_throw)
					.html(text)
					.css('z-index', ++zindex_counter);
			}

		});
	});

	// trash is droppable
	$body.build('call', '#trashcan a', function()
	{
		var accept = '.column label, .list table th.property, .object, .objects, .throwable';
		this
			.data('accept', accept)
			.droppable(
			{
				accept:     accept,
				hoverClass: 'candrop',
				tolerance:  'touch',

				drop: function(event, ui)
				{
					var app = window.app;
					// calculate destination href
					var href = ui.helper.data('throw')
						? (app.uri_base + ui.helper.data('throw'))
						: (event.target.pathname + SL + 'drop');
					// after trash call is complete, the source window is reloaded to update displayed content
					var $window = ui.draggable.closest('article');
					if ($window.length) {
						var data_class = $window.data('class');
						if (data_class !== undefined) {
							$(event.target).data(
								'on-success', function() {
									if (
										($window.data('feature') !== 'output')
										&& ($window.data('feature') !== 'edit')
									) {
										var uri = SL + data_class.replace(BS, SL) + SL + $window.data('feature');
										$.ajax({
											url:     app.uri_base + uri + '?as_widget' + app.andSID(),
											success: function(data) {
												var $parent = $window.parent();
												$parent.html(data);
												$parent.children().build();
											}
										});
									}
									else {
										ui.draggable.closest('div[class][id]').remove();
									}
								}
							);
						}
					}
					href += SL + ui.helper.data('class').replace(BS, SL);
					if (ui.helper.data('id')) {
						href += SL + ui.helper.data('id');
					}
					if (ui.helper.data('feature')) {
						href += SL + ui.helper.data('feature');
					}
					if (ui.helper.data('property')) {
						var property = ui.helper.data('property');
						href += '/ITRocks/Framework/Property';
						if (property.indexOf('(') > -1) {
							href = app.askAnd(href, 'property_path=' + property);
						}
						else {
							href += SL + property;
						}
					}
					if (ui.helper.data('action')){
						href += '/ITRocks/Framework/Rad/' + ui.helper.data('action');
					}
					href += event.target.search + event.target.hash;
					redirectLight(href, '#messages');
				}
			});

	});

});
