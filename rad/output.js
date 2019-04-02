
function radOutput()
{

	$('body').build('each', 'article.edit > fieldset, article.output > fieldset', function()
	{
		var $this = $(this);

		//------------------------------------------------------------------------------------- out
		// when a property is no longer highlighting columns / blocks separators
		var out = function($this, event, ui)
		{
			$this.find('.insert-bottom').removeClass('insert-bottom');
			$this.find('.insert-top').removeClass('insert-top');
			$this.removeData('drag-callback');
			ui.draggable.removeData('over-droppable');
		};

		//------------------------------------------------------------------------------------ drag
		var drag = function(event, ui)
		{
			var $droppable = $(this);
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

		//------------------------------ article.edit > fieldset, article.output > fieldset droppable
		$this.droppable({
			accept:    '.property',
			tolerance: 'touch',

			drop: function(event, ui)
			{
				var $this          = $(this);
				var $insert_top    = $this.find('.insert-top').closest('div[class][id]');
				var $insert_bottom = $this.find('.insert-bottom').closest('div[class][id]');
				if ($insert_top.length || $insert_bottom.length) {
					var $div         = ui.draggable.closest('div[class][id]');
					var $insert      = $insert_top.length ? $insert_top : $insert_bottom;
					var div_property = $div.attr('id');
					var $draggable = ui.draggable;
					if (div_property === undefined) {
						div_property = $draggable.data('property');
					}
					var insert_property = $insert.attr('id');
					if (div_property !== insert_property) {
						var $fieldset  = $insert.closest('fieldset[class]');
						var $window    = $this.closest('article');
						var app        = window.app;
						var class_name = $window.data('class').repl(BS, SL);
						var side       = $insert_top.length ? 'before' : 'after';
						var tab        = $fieldset.attr('id');
						tab = tab ? tab.substr(3) : $fieldset.attr('class');
						var uri = app.uri_base + SL + class_name + SL + 'outputSetting'
							+ '?add_property=' + div_property
							+ '&' + side + '=' + insert_property
							+ '&tab=' + ((tab === 'out_of_tabs') ? '' : tab)
							+ '&feature=' + $window.data('feature')
							+ '&as_widget'
							+ app.andSID();

						$div.detach();
						if (side === 'before') {
							$div.insertBefore($insert_top);
						}
						else {
							$div.insertAfter($insert_bottom);
						}

						$.ajax({ url: uri, success: function() {
							var data_id      = $window.data('id');
							var class_name   = $window.data('class').repl(BS, SL);
							var feature_name = $window.data('feature');
							var url = app.uri_base + SL + class_name + SL + data_id + SL + feature_name
								+ '?as_widget' + window.app.andSID();
							$.ajax({ url: url, success: function(data) {
								var $container = $window.parent();
								$container.html(data);
								$container.children().build();
							}});
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

		//------------------------------------------------------------------------------- modifiables
		// prepare modifiables

		var className = function($this)
		{
			return $this.closest('article').data('class').repl(BS, SL);
		};

		var featureName = function($this)
		{
			return $this.closest('article').data('feature');
		};

		var propertyPath = function($this)
		{
			return $this.closest('div[class][id]').attr('id');
		};

		var callback_uri = window.app.uri_base + '/{className}/outputSetting'
			+ '?as_widget&feature={featureName}' + window.app.andSID();

		var output_edit_uri = window.app.uri_base
			+ '/ITRocks/Framework/Feature/Output_Setting/edit/{className}/{featureName}?as_widget'
			+ window.app.andSID();

		var output_property_uri = window.app.uri_base
			+ '/ITRocks/Framework/Feature/Output_Setting/Property/edit/{className}/{featureName}/{propertyPath}?as_widget'
			+ window.app.andSID();

		//------------------------------ article > header > h2, div[class][id] > label > a modifiable
		// output title is modifiable (dbl-click)
		$this.parent().find('header > h2').modifiable({
			ajax:      callback_uri + '&title={value}',
			ajax_form: 'form',
			aliases:   {className: className, featureName: featureName},
			popup:     output_edit_uri,
			target:    '#messages',
			start: function() {
				$(this).closest('h2').children('.custom.actions').css('display', 'none');
			},
			stop: function() {
				$(this).closest('h2').children('.custom.actions').css('display', '');
			}
		});

		//--------------------------------------------------------- div[class][id]>label>a modifiable
		// property label is modifiable
		$this.find('div[class][id]>label').modifiable({
			ajax:      callback_uri + '&property_path={propertyPath}&property_title={value}',
			ajax_form: 'form',
			aliases:   { className: className, featureName: featureName, propertyPath: propertyPath },
			popup:     output_property_uri,
			target:    '#messages'
		});

	});

}
