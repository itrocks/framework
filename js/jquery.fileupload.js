(function($)
{
	/**
	 * This plugin will display a confirmation dialog when clicking on confirmation buttons.
	 * If user confirms action, the normal action of the button processes.
	 *
	 * - Works with <a> tags.
	 * - Initialise this feature with a single $('body').confirm(); call.
	 */
	$.fn.fileUpload = function(options)
	{
		var $drop_zone = this;

		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			file_input_attribute: 'data-file-input',
			upload_url:           null
		}, options);

		//------------------------------------------------------------------------------ this dragenter
		$drop_zone.on('dragenter', function()
		{
			$(this).css('border', '1px solid red');
			return false;
		});

		//------------------------------------------------------------------------------ this dragleave
		$drop_zone.on('dragleave', function()
		{
			event.preventDefault();
			event.stopPropagation();
			$(this).css('border', '');
		});

		//------------------------------------------------------------------------------- this dragover
		$drop_zone.on('dragover', function(event)
		{
			event.preventDefault();
			event.stopPropagation();
		});

		//----------------------------------------------------------------------------------- this drop
		$drop_zone.on('drop', function(event)
		{
			var data_transfer = event.originalEvent.dataTransfer;
			var $drop_zone    = $(this);
			if (data_transfer && data_transfer.files.length) {
				var $file_input = $(
					'input#' + $drop_zone.attr(settings.file_input_attribute) + '[type=file]'
				);
				event.preventDefault();
				event.stopPropagation();
				if ($file_input.get(0)) {
					$file_input.get(0).files = data_transfer.files;
				}
				if (settings.upload_url) {
					// TODO ajax upload of the file
				}

				// TODO this is very specific to "PDF into the same zone"
				// TODO generalize the "direct display" and add settings
				// @see https://codepen.io/Shiyou/pen/JNLwVO
				var file_reader = new FileReader();
				file_reader.onload = function() {
					PDFJS.getDocument(new Uint8Array(this.result)).then(function(pdf) {
						pdf.getPage(1).then(function(page) {
							var viewport = page.getViewport(4.235);
							var $canvas  = $('<canvas>')
								.attr('height', $drop_zone.height() * 3)
								.attr('width',  $drop_zone.width()  * 3);
							var canvas  = $canvas.get(0);
							var context = canvas.getContext('2d');
							page
								.render({canvasContext: context, viewport: viewport})
								.promise
								.then(function() {
									$drop_zone.css('background-image', 'url("' + canvas.toDataURL() + '")');
									$canvas.remove();
								});
						});
					});
				};
				file_reader.readAsArrayBuffer(data_transfer.files[0]);

			}
			$drop_zone.css('border', '');
		});

		return this;
	};

})( jQuery );
