$(document).ready(function()
{
	$('.quill-standard').build(function()
	{
		this.each(function() {
			var $this  = $(this);
			var $quill = $('<div>');
			if ($this.hasClass('auto_height')) {
				$quill.addClass('auto_height');
			}
			$this.before($quill);
			$this.hide();

			$quill.keyup(function() {
				// TODO later depending on data-store-format $this.text(JSON.stringify(quill.getContents()))
				$this.text($quill.find('.ql-editor').html());
			});

			var quill = new Quill($quill.get(0), { theme: 'snow' });
			if ($this.text().beginsWith('{')) {
				quill.setContents(JSON.parse($this.text()));
			}
			else {
				$quill.find('.ql-editor').html($this.text().replace("\n", '<br>')).keyup();
			}
		});
	});
});
