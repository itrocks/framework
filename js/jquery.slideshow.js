(function($)
{

	$.fn.slideShow = function(options)
	{
		if (this.length < 2) return this

		//------------------------------------------------------------------------------------ settings
		const settings = $.extend({
			frame_delay:             5000,
			hover_stop:              true,
			manual_transition_speed:  200,
			next:                    undefined,
			previous:                undefined,
			transition_speed:        1000
		}, options)

		const elements = this
		let   position = 0
		let   hover    = 0

		// hide all elements
		for (let i = 1; i < elements.length; i++) {
			$(elements[i]).hide()
		}

		//------------------------------------------------------------ previous/next : string to jquery
		let parent, previous, next
		let truc = 0
		if (settings.previous !== undefined) {
			if (typeof settings.previous === 'string') {
				parent = elements.parent()
				previous = parent.children(settings.previous)
				while (parent.length && !previous.length && truc++ < 100) {
					parent = parent.parent()
					previous = parent.children(settings.previous)
				}
				settings.previous = previous
			}
		}
		if (settings.next !== undefined) {
			if (typeof settings.next === 'string') {
				parent = elements.parent()
				next = parent.children(settings.next)
				while (parent.length && !next.length && truc++ < 100) {
					parent = parent.parent()
					next = parent.children(settings.next)
				}
				settings.next = next
			}
		}

		//---------------------------------------------------------------------------- elements.hover()
		elements.hover(() => hover++, () => hover--)
		if (settings.previous !== undefined) {
			settings.previous.hover(() => hover++, () => hover--)
		}
		if (settings.next !== undefined) {
			settings.next.hover(() => hover++, () => hover--)
		}

		//----------------------------------------------------------------------- previous/next.click()
		if (settings.previous !== undefined) {
			settings.previous.click(function() {
				$(elements[position]).fadeOut(settings.manual_transition_speed)
				position --
				if (position < 0) {
					position = elements.length - 1
				}
				$(elements[position]).fadeIn(settings.manual_transition_speed)
			})
		}
		if (settings.next !== undefined) {
			settings.next.click(function() {
				$(elements[position]).fadeOut(settings.manual_transition_speed)
				position ++
				if (position >= elements.length) {
					position = 0
				}
				$(elements[position]).fadeIn(settings.manual_transition_speed)
			})
		}

		//----------------------------------------------------------------------------------- slideShow
		setInterval(function()
		{
			if (!hover || !settings.hover_stop) {
				$(elements[position]).fadeOut(settings.transition_speed)
				position ++
				if (position >= elements.length) {
					position = 0
				}
				$(elements[position]).fadeIn(settings.transition_speed)
			}
		}, settings.frame_delay)

		return this
	}

})( jQuery )
