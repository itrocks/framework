$(document).ready(function()
{

	let $body = $('body')

	//--------------------------------------------------------------------- dropOn.enhance(custom.js)
	$body.dropOn('enhance', {
		custom: {
			action: 'append',
			class:  'dashboard',
			link:   'app://ITRocks/Framework/Report/Dashboard/append/(class)/(id)',
			text:   tr('to') + SP + tr('dashboard')
		}
	})

	//-------------------------------------------------- article.dashboard .data .indicator draggable
	$body.build('call', 'article.dashboard .data .indicator', function()
	{
		$(this).draggable({
			appendTo:    'body',
			containment: 'body',
			cursorAt:    { left: -10, top: -10 },
			helper:      'clone',
			scroll:      false,

			start: function(event, ui)
			{
				const $draggable = $(this)
				const $anchor    = $draggable.find('a')
				$anchor.data('href', $anchor.attr('href')).removeAttr('href')
				$draggable.dropOn({
					class: 'ITRocks\\Framework\\Report\\Dashboard\\Indicator',
					id:    'dashboard',
					zones: ['delete', 'edit']
				})
				ui.helper.addClass('dashboard')
			},

			stop: function()
			{
				const $draggable = $(this)
				$draggable.dropOn('stop')
				setTimeout(() => {
					const $anchor = $draggable.find('a')
					$anchor.attr('href', $anchor.data('href')).removeData('href')
				})
			}
		})
	})

	$body.build('call', ['article.dashboard .data', '.free, .indicator'], function()
	{
		$(this).droppable({
			accepts:   '.indicator',
			tolerance: 'pointer',

			drop: function(event, ui)
			{
				const $source = ui.draggable
				const $target = $(this)
				const grid_x  = $target.prevAll('li').length
				const grid_y  = $target.parent().closest('li').prevAll('li').length
				redirectLight(
					['', $source.data('class').repl(BS, SL), $source.data('id'), 'move'].join(SL)
						+ '?x=' + grid_x + '&y=' + grid_y,
					'#responses'
				)
			}
		})
	})

})
