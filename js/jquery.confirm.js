(function($)
{
	/**
	 * This plugin will display a confirmation dialog when clicking on confirmation buttons.
	 * If user confirms action, the normal action of the button processes.
	 *
	 * - Works with <a> tags.
	 * - Initialise this feature with a single $('body').confirm(); call.
	 */
	$.fn.confirm = function()
	{

		//----------------------------------------------------------------------------------- buildButton
		/**
		 * Build a button object with the given parameters.
		 *
		 * @param label     {string}   Label of the button.
		 * @param callback  {function} The callback to call on click.
		 * @param css_class {string}   The CSS class to apply to the button.
		 * @returns {*|jQuery}
		 */
		const buildButton = function(label, callback, css_class)
		{
			const $link = $('<a>', {html: label}).on('click', callback)

			return $('<li>')
				.addClass(css_class)
				.append($link)
		}

		//----------------------------------------------------------------------------------- closeDialog
		/**
		 * Close dialog box.
		 */
		const closeDialog = function()
		{
			$('#responses').empty()
		}

		//--------------------------------------------------------------------------------------- display
		/**
		 * Display the given markup in a dialog box.
		 *
		 * @param markup {object|string}
		 */
		const display = function(markup)
		{
			$('#responses').html(markup)
		}

		//------------------------------------------------------------------------------------ openDialog
		/**
		 * Display a confirm dialog with the given message and callbacks.
		 *
		 * @param message     {string}   The message to display.
		 * @param ok_callback {function} The callback called on confirmation.
		 * @param ko_callback {function} The callback called on cancellation (optional).
		 * @param ok_label    {string}   Label of the confirm button.
		 * @param ko_label    {string}   Label of the cancel button.
		 */
		const openDialog = function(message, ok_callback, ko_callback, ok_label, ko_label)
		{
			// Set default value of cancellation callback if not set.
			if ((typeof ko_callback === 'undefined') || (ko_callback === null)) {
				ko_callback = function() {
					closeDialog()
				}
			}

			// Close dialog when running confirmation callback.
			const callback = function() {
				closeDialog()
				ok_callback()
			}

			const ok_button = buildButton(ok_label, callback, 'output')
			const ko_button = buildButton(ko_label, ko_callback, 'delete')

			const html    = '<p>' + message + '</p>'
			const buttons = $('<ul>')
				.addClass('general actions')
				.css('text-align', 'right')
				.append(ko_button, ok_button)

			const wrapper = $('<div>').append(html, buttons)

			display(wrapper)
		}

		//--------------------------------------------------------------------------- removeConfirmedFlag
		/**
		 * Remove "confirmed" flag to the given object.
		 *
		 * @param $object {object}
		 */
		const removeConfirmedFlag = function($object)
		{
			$object.removeAttr('confirmed')
		}

		//------------------------------------------------------------------------------ setConfirmedFlag
		/**
		 * Set "confirmed" flag to the given object.
		 *
		 * @param $object {object}
		 */
		const setConfirmedFlag = function($object)
		{
			$object.attr('confirmed', 'confirmed')
		}

		//------------------------------------------------------------------------------- a.confirm click
		/**
		 * Listener on click event.
		 *
		 * If link has flag "confirmed", just free the event & remove flag. Otherwise prevent event and
		 * display a confirm box built with data attributes of the link.
		 */
		this.find('a.confirm').bind('click', function(event)
		{
			const $link = $(this)

			if (!$link.attr('confirmed')) {
				event.stopImmediatePropagation()

				let   message  = $link.attr('data-confirm-message')
				const ok_label = $link.attr('data-confirm-ok')
				const ko_label = $link.attr('data-confirm-cancel')

				if (!message) {
					message = tr('|Do you confirm this action| : ') + $link.html()
				}

				/**
				 * Add a "confirmed" flag and re-trigger click event to keep normal process.
				 */
				const callback = function()
				{
					setConfirmedFlag($link)
					$link[0].click()
				}

				openDialog(message, callback, null, ok_label, ko_label)

				return false
			}

			removeConfirmedFlag($link)

			return event
		})

	}
})( jQuery )
