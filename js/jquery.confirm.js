(function($)
{

	/**
	 * This plugin will display a confirmation dialog when clicking on confirmation buttons.
	 * If user confirms action, the normal action of the button processes.
	 *
	 * - Works with <a> tags.
	 * - Initialise this feature with a single $('body').confirm(); call.
	 */
	$.fn.confirm = function () {

		this.find('a[data-confirm]').bind("click", function(event) {
			var $link = $(this);

			if (!$link.attr('confirmed')) {
				event.stopImmediatePropagation();

				var message  = $link.attr('data-message');
				var ok_label = $link.attr('data-confirm');
				var ko_label = $link.attr('data-cancel');
				/**
				 * Add a "confirmed" flag and re-trigger click event to keep normal process.
				 */
				var callback = function() {
					setConfirmedFlag($link);
					$link[0].click();
				};

				openDialog(message, callback, null, ok_label, ko_label);

				return false;
			}

			removeConfirmedFlag($link);

			return event;
		});

		/**
		 * Display a confirm dialog with the given message and callbacks.
		 *
		 * @param message     {string}   The message to display.
		 * @param ok_callback {function} The callback called on confirmation.
		 * @param ko_callback {function} The callback called on cancellation (optional).
		 * @param ok_label    {string}   Label of the confirm button (optional).
		 * @param ko_label    {string}   Label of the cancel button (optional).
		 */
		var openDialog = function(message, ok_callback, ko_callback, ok_label, ko_label) {
			// Set default value of cancellation callback if not set.
			if (typeof ko_callback === 'undefined' || ko_callback === null) {
				ko_callback = function () {
					closeDialog();
				};
			}

			// Set default label for confirmation button if not set.
			if (typeof ok_label === 'undefined' || ok_label === null) {
				ok_label = 'Confirmer';
			}

			// Set default label for cancelation button if not set.
			if (typeof ko_label === 'undefined' || ko_label === null) {
				ko_label = 'Annuler';
			}

			// Close dialog when running confirmation callback.
			var callback = function() {
				closeDialog();
				ok_callback();
			};

			var ok_button = buildButton(ok_label, callback, 'output');
			var ko_button = buildButton(ko_label, ko_callback, 'delete');

			var html    = '<p>' + message + '</p>';
			var wrapper = $('<div>').append(html, $('<ul>', {
				class: 'general actions'
			}).append(ok_button, ko_button));

			display(wrapper);
		};

		/**
		 * Display the given markup in a dialog box.
		 *
		 * @param markup {object|string}
		 */
		var display = function(markup) {
			$('#messages').html(markup);
		};

		/**
		 * Close dialog box.
		 */
		var closeDialog = function() {
			$('#messages').html('');
		};

		/**
		 * Build a button object with the given parameters.
		 *
		 * @param label     {string}   Label of the button.
		 * @param callback  {function} The callback to call on click.
		 * @param css_class {string}   The CSS class to apply to the button.
		 * @returns {*|jQuery}
		 */
		var buildButton = function(label, callback, css_class) {
			return $('<li>', {
				class: css_class
			}).append($('<a>', {
				html: label
			}).click(callback));
		};

		/**
		 * Set "confirmed" flag to the given object.
		 *
		 * @param $object {object}
		 */
		var setConfirmedFlag = function($object) {
			$object.attr('confirmed', 'confirmed');
		};

		/**
		 * Remove "confirmed" flag to the given object.
		 *
		 * @param $object {object}
		 */
		var removeConfirmedFlag = function($object) {
			$object.removeAttr('confirmed');
		};
	}
})( jQuery );
