$('document').ready(function()
{
	$('.property.edit.window').build(function()
	{
		if (!this.length) return;
		this.inside('.property.edit.window').each(function() {
			var $this = $(this);
			var will_close = false;
			var $input = $this.find('input');

			//-------------------------------------------------------------------------------- input blur
			$input.blur(function()
			{
				var $this = $(this);
				var $popup = $this.closest('.property.edit.window');
				will_close = true;
				setTimeout(function() {
					if (will_close) {
						$popup.data('done').call($popup.get(0));
					}
				}, 100);
			});

			//------------------------------------------------------------------------------- input focus
			$input.focus(function()
			{
				var $this = $(this);
				var $popup = $this.closest('.property.edit.window');

				if (!$popup.data('done')) {
					var $window = $popup.parent().data('xtarget.from').closest('.window');
					var app = window.app;
					var class_name = $window.data('class').repl(BS, SL);
					var uri = app.uri_base + SL + class_name + SL + 'outputSetting';

					$popup.attr('action', uri);

					$popup.data('done', function()
					{
						var $label = $window.find(
							'div[class]#' + $popup.find('input[name=property_path]').val() + '>label>a'
						);
						$label.html($popup.find('input[name=property_title]').val());
						$popup.submit();
						$popup.closest('.closeable-popup').fadeOut(200, function() { $(this).remove(); });
					});

				}
				will_close = false;
			});

			//---------------------------------------------------------------------- input keydown CR|ESC
			$input.keydown(function(event)
			{
				var $this = $(this);
				if (event.keyCode == 13 || event.keyCode == 27) {
					var $popup = $this.closest('.property.edit.window');
					if (event.keyCode == 13) {
						event.preventDefault();
						$popup.data('done').call($popup.get(0));
					}
					else if (event.keyCode == 27) {
						$popup.closest('.closeable-popup').fadeOut(200, function() { $(this).remove(); });
					}
				}
			});

		});
	});
});
