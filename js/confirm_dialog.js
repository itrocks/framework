/**
 * This object allows you to display confirmation dialog to user and customize callback on
 * confirmation and cancellation.
 *
 * @example
 * ConfirmDialog.open("Do you confirm deletion ?", function() {
 *     alert('Confirmation callback');
 * });
 * @example
 * ConfirmDialog.open("Do you confirm deletion ?", function() {
 *    alert('Confirmation callback');
 * }, function() {
 *    alert('Cancellation callback');
 * });
 */
var ConfirmDialog = {

	/**
	 * Display a confirm dialog with the given message and callbacks.
	 *
	 * @param message     {string}   The message to display.
	 * @param ok_callback {callback} The callback called on confirmation.
	 * @param ko_callback {callback} The callback called on cancellation (optional).
	 * @param ok_label    {string}   Label of the confirm button (optional).
	 * @param ko_label    {string}   Label of the cancel button (optional).
	 */
	open: function(message, ok_callback, ko_callback, ok_label, ko_label) {
		// Set default value of cancellation callback if not set.
		if (typeof ko_callback === 'undefined' || ko_callback === null) {
			ko_callback = function () {
				ConfirmDialog._closeDialog();
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
			ConfirmDialog._closeDialog();
			ok_callback();
		};

		var ok_button = ConfirmDialog._buildButton(ok_label, callback, 'output');
		var ko_button = ConfirmDialog._buildButton(ko_label, ko_callback, 'delete');

		var html    = '<p>' + message + '</p>';
		var wrapper = $('<div>').append(html, $('<ul>', {
			class: 'general actions'
		}).append(ok_button, ko_button));

		ConfirmDialog._display(wrapper);
	},

	/**
	 * Display the given markup in a dialog box.
	 *
	 * @param markup {object|string}
	 * @private
	 */
	_display: function(markup) {
		$('#messages').html(markup);
	},

	/**
	 * Close dialog box.
	 *
	 * @private
	 */
	_closeDialog: function() {
		$('#messages').html('');
	},

	/**
	 * Build a button object with the given parameters.
	 *
	 * @param label     {string}   Label of the button.
	 * @param callback  {callback} The callback to call on click.
	 * @param css_class {string}   The CSS class to apply to the button.
	 * @returns {*|jQuery}
	 * @private
	 */
	_buildButton: function(label, callback, css_class) {
		return $('<li>', {
			class: css_class
		}).append($('<a>', {
			html: label
		}).click(callback));
	}
};
