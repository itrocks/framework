(function($)
{
	let enhances = {}

	/**
	 * Associates a drop-on zone to a draggable object
	 *
	 * This zone will appear when the drag starts, and disappear when dropped
	 *
	 * If dropped into an action of the drop-on zone : this action will be executed with the object
	 * information associated to the dragged object.
	 *
	 * - into the draggable.start event, call $(this).dropOn({ ... })
	 * - into the draggable.stop event, call $(this).dropOn('stop')
	 * - from another module, if your dropOn has an id, call $('body').dropOn('enhance', { id: ... })
	 *   to enhance a drop-on without writing module-specific code into it's original module
	 *
	 * @param options    object|string
	 * @param parameters object|undefined
	 */
	$.fn.dropOn = function(options, parameters)
	{
		let stop = function()
		{
			$('#notifications .drop_on').animate({top: '-64px'}, 200)
			setTimeout(() => { $('ul.drop_on').remove() }, 200)
		}

		if (typeof(options) === 'string') {
			switch (options) {
				case 'enhance':
					for (let [id, zones] of Object.entries(parameters)) {
						if (typeof(zones) === 'object') {
							zones = [zones]
						}
						enhances[id] = enhances[id]
							? enhances[id].concat(zones)
							: zones
					}
					break
				case 'stop':
					stop()
					break
				default:
					throw new Error('Unknown method $.dropOn.' + options + '()')
			}
			return this
		}

		let $draggable = this
		let settings   = $.extend({
			class:   undefined,
			enhance: undefined,
			id:      undefined,
			target:  undefined,
			zones:   []
		}, options)
		this.settings = settings

		if (enhances[settings.id]) {
			settings.zones = settings.zones.concat(enhances[settings.id])
		}

		if ($draggable.data('class')) {
			settings.class = $draggable.data('class')
		}
		if (settings.class.startsWith(BS)) {
			settings.class = settings.class.substr(1)
		}
		if ($draggable.data('id')) {
			settings.id = $draggable.data('id')
		}

		let $drop_on = $('<ul class="drop_on"></ul>').css('top', '-64px')
		for (let zone of settings.zones) {
			if (typeof(zone) === 'string') {
				zone = { action: zone }
			}
			if (zone.action) {
				if (!zone.class)  zone.class  = zone.action
				if (!zone.target) zone.target = ['delete'].includes(zone.action) ? '#responses' : '#main'
				if (!zone.text)   zone.text   = tr(zone.action)
				if (!zone.link) {
					zone.link = 'app://(class)/(id)/' + zone.action
						+ ((zone.action === 'delete') ? '?confirm=true' : '')
				}
			}
			let target = zone.target
			if (!target) target = $draggable.data('target')
			if (!target) target = settings.target
			if (target)  target = ' target=' + DQ + target + DQ
			let link = zone.link
				.repl('app://',   app.uri_base + SL)
				.repl('(class)', settings.class.repl(BS, SL))
				.repl('(id)',    settings.id)
			$drop_on.append(
				'<li class=' + DQ + zone.class + DQ + '>'
				+ '<a href=' + DQ + link + DQ + target + '>'
				+ zone.text
				+ '</a>'
				+ '</li>'
			)
		}
		$('#notifications').append($drop_on)
		$drop_on.animate({ top: '3px' }, 200)
		$('#notifications .drop_on').build()

		$drop_on.find('li').droppable({
			accepts:   $draggable,
			tolerance: 'pointer',
			drop:      function() { $(this).find('a').click() }
		})

		return this
	}
})( jQuery )
