$(document).ready(function()
{

	let $body = $('body')

	//-------------------------------------------------- article.dashboard .data .indicator draggable
	$body.build('call', 'article.dashboard .data .indicator', function()
	{
		$(this).draggable({
			appendTo:    'body',
			containment: 'body',
			cursorAt:    { left: -10, top: -10 },
			scroll:      false,

			start: function()
			{
				$(this).dropOn({
					class: 'ITRocks\\Framework\\Report\\Dashboard\\Indicator',
					id:    'dashboard',
					zones: ['delete', 'edit']
				})
			},

			stop: function()
			{
				$(this).dropOn('stop')
				$(this).css({ position: 'absolute' })
				setTimeout(() => { $(this).css({ left: '', position: '', top: '' }) }, 200)
			}

		})
	})

	//---------------------------------------------------------------------- enhance custom.js dropOn
	$body.dropOn('enhance', {
		custom: {
			action: 'append',
			class:  'dashboard',
			link:   'app://ITRocks/Framework/Report/Dashboard/append/(class)/(id)',
			text:   tr('to') + SP + tr('dashboard')
		}
	})

})
