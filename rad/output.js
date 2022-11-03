$(document).ready(function()
{

	$('body').build('each', 'article.output > header > h2', function()
	{
		const className = function($this)
		{
			return $this.closest('article').data('class').repl(BS, SL)
		}
		const featureName = function($this)
		{
			return $this.closest('article').data('feature')
		}
		const callback_uri = window.app.uri_base + '/{className}/outputSetting'
			+ '?as_widget&feature={featureName}' + window.app.andSID()

		//------------------------------ article > header > h2, div[class][id] > label > a modifiable
		// output title is modifiable (dbl-click)
		$(this).modifiable({
			ajax:    callback_uri + '&title={value}',
			aliases: {className: className, featureName: featureName},
			target:  '#responses'
		})
	})

})

function radOutput()
{

	$('body').build('each', 'article.edit > .data, article.output > .data', function()
	{
		const $this = $(this)

		//------------------------------------------------------------------------------------- out
		// when a property is no longer highlighting columns / blocks separators
		const out = function($this, event, ui)
		{
			$this.find('.insert-bottom').removeClass('insert-bottom')
			$this.find('.insert-top').removeClass('insert-top')
			$this.removeData('drag-callback')
			ui.draggable.removeData('over-droppable')
		}

		//------------------------------------------------------------------------------------ drag
		const drag = function(event, ui)
		{
			const $droppable = $(this)
			const draggable_top = ui.offset.top + (ui.helper.height() / 2)
			let   $found
			$droppable.find('div[class][id]').each(function() {
				const $this = $(this)
				const $next = $this.next('div[class][id]')
				const top = $this.offset().top
				const bottom = $next.length ? $next.offset().top : (top + $this.height())
				if ((draggable_top > top) && (draggable_top <= bottom)) {
					$droppable.find('.insert-bottom').removeClass('insert-bottom')
					$droppable.find('.insert-top').removeClass('insert-top')
					$found = (draggable_top <= ((top + bottom) / 2)) ? $this : $next
					if ($found.length) {
						$found.children('label').addClass('insert-top')
					}
					else {
						$this.children('label').addClass('insert-bottom')
					}
					return false
				}
			})
		}

		//------------------------------ article.edit > fieldset, article.output > fieldset droppable
		$this.droppable({
			accept:    '.property',
			tolerance: 'touch',

			drop: function(event, ui)
			{
				const $this          = $(this)
				const $insert_top    = $this.find('.insert-top').closest('div[class][id]')
				const $insert_bottom = $this.find('.insert-bottom').closest('div[class][id]')
				if ($insert_top.length || $insert_bottom.length) {
					const $div         = ui.draggable.closest('div[class][id]')
					const $insert      = $insert_top.length ? $insert_top : $insert_bottom
					let   div_property = $div.attr('id')
					const $draggable = ui.draggable
					if (div_property === undefined) {
						div_property = $draggable.data('property')
					}
					const insert_property = $insert.attr('id')
					if (div_property !== insert_property) {
						const $fieldset  = $insert.closest('fieldset[class]')
						const $window    = $this.closest('article')
						const app        = window.app
						const class_name = $window.data('class').repl(BS, SL)
						const side       = $insert_top.length ? 'before' : 'after'
						let   tab        = $fieldset.attr('id')
						tab = tab ? tab.substring(3) : $fieldset.attr('class')
						const uri = app.uri_base + SL + class_name + SL + 'outputSetting'
							+ '?add_property=' + div_property
							+ '&' + side + '=' + insert_property
							+ '&tab=' + ((tab === 'out_of_tabs') ? '' : tab)
							+ '&feature=' + $window.data('feature')
							+ '&as_widget'
							+ app.andSID()

						$div.detach()
						if (side === 'before') {
							$div.insertBefore($insert_top)
						}
						else {
							$div.insertAfter($insert_bottom)
						}

						$.ajax({ url: uri, success: function() {
							const data_id      = $window.data('id')
							const class_name   = $window.data('class').repl(BS, SL)
							const feature_name = $window.data('feature')
							const url = app.uri_base + SL + class_name + SL + data_id + SL + feature_name
								+ '?as_widget' + window.app.andSID()
							$.ajax({ url: url, success: function(data) {
								const $container = $window.parent()
								$container.html(data)
								$container.children().build()
							}})
						}})

					}
				}
				out($this, event, ui)
			},

			out: function(event, ui)
			{
				out($(this), event, ui)
			},

			over: function(event, ui)
			{
				const $this = $(this)
				$this.data('drag-callback', drag)
				ui.draggable.data('over-droppable', $this)
			}

		})

		//------------------------------------------------------------------------------- modifiables
		// prepare modifiables

		const className = function($this)
		{
			return $this.closest('article').data('class').repl(BS, SL)
		}

		const featureName = function($this)
		{
			return $this.closest('article').data('feature')
		}

		const propertyPath = function($this)
		{
			return $this.closest('div[class][id]').attr('id')
		}

		const callback_uri = window.app.uri_base + '/{className}/outputSetting'
			+ '?as_widget&feature={featureName}' + window.app.andSID()

		const output_property_uri = window.app.uri_base
			+ '/ITRocks/Framework/Feature/Output_Setting/Property/edit/{className}/{featureName}/{propertyPath}?as_widget'
			+ window.app.andSID()

		//--------------------------------------------------------- div[class][id]>label>a modifiable
		// property label is modifiable
		$this.find('div[class][id]>label').modifiable({
			ajax:      callback_uri + '&property_path={propertyPath}&property_title={value}',
			ajax_form: 'form',
			aliases:   { className: className, featureName: featureName, propertyPath: propertyPath },
			popup:     output_property_uri,
			target:    '#responses'
		})

	})

}
