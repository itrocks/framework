(function($)
{

	$.fn.modifiable = function(options)
	{

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			aliases: {},
			done:    undefined,
			target:  undefined
		}, options);

		//------------------------------------------------------------------------------------- click()
		this.click(function(event)
		{
console.log("click confirm=" + modifiable_confirm + " waiting=" + modifiable_waiting + " dblclick=" + modifiable_dblclick);
			if (!modifiable_confirm) {
				var clickable = this;
				event.preventDefault();
				event.stopImmediatePropagation();
				if (!modifiable_waiting) {
					modifiable_waiting = true;
					setTimeout(
						function()
						{
							if (modifiable_dblclick) {
								modifiable_dblclick = false;
							}
							else {
								modifiable_confirm = true;
								$(clickable).click();
								modifiable_confirm = false;
							}
							modifiable_waiting = false;
						},
						200
					);
				}
			}
		});

		//---------------------------------------------------------------------------------- dblclick()
		this.dblclick(function(event)
		{
console.log("dblclick");
			modifiable_dblclick = true;
			event.preventDefault();
			event.stopImmediatePropagation();
			var $this = $(this);
			var $input = $("<input>").val($this.html());
			var done = function() {
				var done = settings.done;
				if (typeof(done) == "string") {
					for(var alias in settings.aliases) if (settings.aliases.hasOwnProperty(alias)) {
						var value = settings.aliases[alias];
						if (typeof(value) == "function") {
							value = value($this);
						}
						done = done.replace("{" + alias + "}", encodeURI(value));
					}
					done = done.replace("{value}", encodeURI($input.val()));
					console.log(done);
					$.ajax({
						url: done,
						target: settings.target,
						success: function(data, status, xhr)
						{
							var destination = xhr.target;
							$(destination).html(data);
						}
					}).target = settings.target;
				}
				$input.parent().html($input.val());
			};
			$this.html($input);
			$input.autowidth();
			$input.keydown(function(event) { if (event.keyCode == 13) done(); });
			$input.blur(function() { done(); });
			$input.focus();
		});
	}

})( jQuery );

var modifiable_confirm  = false;
var modifiable_dblclick = false;
var modifiable_waiting  = false;
