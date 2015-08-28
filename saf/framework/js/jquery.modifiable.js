window.modifiable_confirm  = false;
window.modifiable_dblclick = false;
window.modifiable_waiting  = false;

(function($)
{

	$.fn.modifiable = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			ajax:    undefined,
			aliases: {},
			start:   undefined,
			stop:    undefined,
			target:  undefined
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
			var $input = $('<input>').val($this.html().trim());
			if ($this.data('old') == undefined) {
				$this.data('old', $input.val());

				var done = function () {
					var ajax = settings.ajax;
					if (typeof(ajax) == 'string') {
						for (var alias in settings.aliases) if (settings.aliases.hasOwnProperty(alias)) {
							var value = settings.aliases[alias];
							if (typeof(value) == 'function') {
								value = value($this);
							}
							ajax = ajax.replace('{' + alias + '}', encodeURI(value));
						}
						ajax = ajax.replace('{value}', encodeURI($input.val()));
						$.ajax({
							url:    ajax,
							target: settings.target,
							success: function (data, status, xhr)
							{
								var destination = xhr.target;
								$(destination).html(data);
							}
						}).target = settings.target;
					}
					if (settings.stop) {
						var input = $input.get(0);
						input.stop = settings.stop;
						input.stop();
						input.stop = undefined;
					}
					$input.parent().html($input.val());
					$this.removeData('old');
				};

				$this.html($input);
				$input.autowidth();
				$input.keydown(function (event) {
					if (event.keyCode == 13) {
						done();
					}
					if (event.keyCode == 27) {
						var $this = $(this);
						$this.val($this.parent().data('old'));
						done();
					}
				});
				$input.blur(function () { done(); });
				$input.focus();
				if (settings.start) {
					var input = $input.get(0);
					input.start = settings.start;
					input.start();
					input.start = undefined;
				}
			}
		});

		return this;
	};

})( jQuery );
