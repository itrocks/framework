$('document').ready(function()
{
	$('.edit.window, .output.window').build(function()
	{
		if (!this.length) return;
		this.inside('.edit.window>fieldset, .output.window>fieldset').each(function()
		{
			var $this = $(this);

			//--------------------------------------------------------------------------------------- out
			// when a property is no longer highlighting columns / blocks separators
			var out = function($this, event, ui)
			{
				$this.find('.insert-bottom').removeClass('insert-bottom');
				$this.find('.insert-top').removeClass('insert-top');
				$this.removeData('drag-callback');
				ui.draggable.removeData('over-droppable');
			};

			//-------------------------------------------------------------------------------------- drag
			var drag = function(event, ui)
			{
				var $droppable    = $(this);
				var draggable_top = ui.offset.top + (ui.helper.height() / 2);
				var $found;
				$droppable.find('div[class][id]').each(function() {
					var $this = $(this);
					var $next = $this.next('div[class][id]');
					var top = $this.offset().top;
					var bottom = $next.length ? $next.offset().top : (top + $this.height());
					if ((draggable_top > top) && (draggable_top <= bottom)) {
						$droppable.find('.insert-bottom').removeClass('insert-bottom');
						$droppable.find('.insert-top').removeClass('insert-top');
						$found = (draggable_top <= ((top + bottom) / 2)) ? $this : $next;
						if ($found.length) {
							$found.children('label').addClass('insert-top');
						}
						else {
							$this.children('label').addClass('insert-bottom');
						}
						return false;
					}
				});
			};

			//---------------------------------- .edit.window>fieldset, .output.window>fieldset droppable
			$this.droppable({
				accept:     '.property',
				tolerance:  'touch',

				drop: function(event, ui)
				{
					var $this          = $(this);
					var $insert_top    = $this.find('.insert-top').closest('div[class][id]');
					var $insert_bottom = $this.find('.insert-bottom').closest('div[class][id]');
					if ($insert_top.length || $insert_bottom.length) {
						var $div            = ui.draggable.closest('div[class][id]');
						var $insert         = $insert_top.length ? $insert_top : $insert_bottom;
						var div_property    = $div.attr('id');
						var insert_property = $insert.attr('id');
						if (div_property != insert_property) {
							var $fieldset  = $insert.closest('fieldset[class]');
							var $window    = $this.closest('.window');
							var app        = window.app;
							var class_name = $window.data('class').repl(BS, SL);
							var side       = $insert_top.length ? 'before' : 'after';
							var tab        = $fieldset.attr('id');
							tab = tab ? tab.substr(3) : $fieldset.attr('class');
							var uri = app.uri_base + SL + class_name + SL + 'outputSetting'
								+ '?add_property=' + div_property
								+ '&' + side + '=' + insert_property
								+ '&tab=' + tab
								+ '&as_widget'
								+ app.andSID();

							$div.detach();
							if (side == 'before') {
								$div.insertBefore($insert_top);
							}
							else {
								$div.insertAfter($insert_bottom);
							}

							console.log(uri);
							$.ajax({ url: uri, success: function(data) {
								$('#messages').html(data);
							}});

						}
					}
					out($this, event, ui);
				},

				out: function(event, ui)
				{
					out($(this), event, ui);
				},

				over: function(event, ui)
	      {
		      var $this = $(this);
		      $this.data('drag-callback', drag);
		      ui.draggable.data('over-droppable', $this);
	      }

			});

			//--------------------------------------- .windows h2>span, div[class][id]>label>a modifiable
			var className = function($this)
			{
				return $this.closest('.window').data('class');
			};
			var propertyPath = function($this)
			{
				return $this.closest('div[class][id]').attr('id');
			};
			var uri = window.app.uri_base + '/{className}/outputSetting'
				+ window.app.askSIDand() + 'as_widget';

			// output title (class name) double-click
			$this.parent().find('h2>span').modifiable({
				ajax:    uri + '&title={value}',
				aliases: { 'className': className },
				start: function() {
					$(this).closest('h2').children('.custom.actions').css('display', 'none');
				},
				stop: function() {
					$(this).closest('h2').children('.custom.actions').css('display', '');
				},
				target: '#messages'
			});

			// property label
			$this.find('div[class][id]>label>a').modifiable({
				ajax:    uri + '&property_path={propertyPath}&property_title={value}',
				aliases: { 'className': className, 'propertyPath': propertyPath },
				target:  '#messages'
			});

		});
	});
});
