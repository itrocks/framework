window.modifiable_confirm  = false;
window.modifiable_dblclick = false;
window.modifiable_waiting  = false;

(function($)
{

	$.fn.modifiable = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			ajax:      undefined,
			ajax_form: undefined,
			aliases:   {},
			class:     'editing',
			popup:     undefined,
			start:     undefined,
			stop:      undefined,
			target:    undefined
		}, options);

		//------------------------------------------------------------------------------------- click()
		this.click(function(event)
		{
			if (!window.modifiable_confirm) {
				var clickable = this;
				event.preventDefault();
				event.stopImmediatePropagation();
				if (!window.modifiable_waiting) {
					window.modifiable_waiting = true;
					setTimeout(
						function()
						{
							if (window.modifiable_dblclick) {
								window.modifiable_dblclick = false;
							}
							else {
								window.modifiable_confirm = true;
								$(clickable).click();
								window.modifiable_confirm = false;
							}
							window.modifiable_waiting = false;
						},
						200
					);
				}
			}
		});

		//---------------------------------------------------------------------------------- dblclick()
		this.dblclick(function(event)
		{
			window.modifiable_dblclick = true;
			event.preventDefault();
			event.stopImmediatePropagation();
			var $this = $(this);
			$this.addClass(settings.class);

			//------------------------------------------------------------------------------------ $input
			var $input = $('<input>').addClass('auto_width').val($this.html().trim());
			if ($this.data('old') === undefined) {
				var $popup;
				$this.data('old', $input.val());
				$this.html($input);
				$input.build().click(function(event) {
					event.preventDefault();
					event.stopImmediatePropagation();
				});

				//----------------------------------------------------------------------------- $input done
				var done = function()
				{
					var ajax = settings.ajax;
					if (typeof(ajax) === 'string') {
						for (var alias in settings.aliases) if (settings.aliases.hasOwnProperty(alias)) {
							var value = settings.aliases[alias];
							if (typeof(value) === 'function') {
								value = value($this);
							}
							ajax = ajax.replace('{' + alias + '}', encodeURI(value));
						}
						ajax = ajax.replace('{value}', encodeURI($input.val()));
						ajax = {
							url:    ajax,
							target: settings.target,
							success: function(data, status, xhr)
							{
								var destination = xhr.target;
								$(destination).html(data);
							}
						};
						ajax.target = settings.target;

						// ajax call : post form, use form plugin, or simple post
						if (settings.ajax_form !== undefined) {
							var $ajax_form = $popup.find(settings.ajax_form);
							if ($ajax_form.ajaxSubmit !== undefined) {
								$ajax_form.ajaxSubmit($.extend(
									ajax, { type: $ajax_form.attr('method') }
								));
							}
							else {
								$.ajax($.extend(
									ajax, { data: $ajax_form.serialize(), type: $ajax_form.attr('method') }
								));
							}
						}
						else {
							$.ajax(ajax);
						}

					}
					if (settings.stop) {
						var input = $input.get(0);
						settings.stop.call(input);
					}
					if ($popup) {
						$popup.fadeOut(100, function() { $(this).remove(); });
					}
					$input.parent().html($input.val()).removeClass(settings.class);
					$this.removeData('old');
				};

				//------------------------------------------------------------------ $input keydown=ESC/RET
				$input.keydown(function(event)
				{
					var $input = $(this);
					if (event.keyCode === 13) {
						if ($input.data('callback')) {
							$input.data('callback').call($input);
						}
						done();
					}
					if (event.keyCode === 27) {
						$input.val($input.parent().data('old'));
						done();
					}
				});

				//------------------------------------------------------------- $input, popup elements blur
				var blur = function()
				{
					setTimeout(function() {
						if (($popup === undefined) || (!$input.is(':focus') && !$popup.find(':focus').length)) {
							if ($input.data('callback')) {
								$input.data('callback').call($input);
							}
							done();
						}
					}, 100);
				};

				//---------------------------------------------------------------------------------- $popup
				if (settings.popup !== undefined) {
					var popup = settings.popup;
					for (var alias in settings.aliases) if (settings.aliases.hasOwnProperty(alias)) {
						var value = settings.aliases[alias];
						if (typeof(value) === 'function') {
							value = value($this);
						}
						popup = popup.replace('{' + alias + '}', encodeURI(value));
					}
					var left = $input.offset().left;
					var top = $input.offset().top + $input.height();
					$popup = $('<div>').addClass('popup').css({
						left:      left,
						position:  'absolute',
						top:       top,
						'z-index': zIndexInc()
					});
					$.ajax({
						url: popup,
						success: function(data)
						{
							$popup.html(data).build();
							$popup.appendTo('body');
							$popup.find('input, select, textarea').blur(blur);
							// press tab from title goes to output properties form instead of data form
							var tab_index = 1;
							$input.attr('tabindex', tab_index);
							$popup.find('input, select, textarea').each(function() {
								$(this).attr('tabindex', ++tab_index);
							});
						}
					});
				}

				$input.focus();
				$input.blur(blur);
				if (settings.start) {
					var input = $input.get(0);
					settings.start.call(input);
				}

			}
		});

		return this;
	};

})( jQuery );
