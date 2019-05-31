$(document).ready(function()
{
	$('body').build('each', '.quill-standard', function()
	{
		var $this  = $(this);
		var $quill = $('<div>');
		$this.before($quill);
		$this.hide();

		$quill.keyup(function() {
			// TODO later depending on data-store-format $this.text(JSON.stringify(quill.getContents()))
			$this.text($quill.find('.ql-editor').html());
		});

		var options = {
			modules: {
				toolbar: [
					[{ header: [1, 2, 3, 4, 5, false]}, { align: []}],
					[
						{ list: 'bullet' }, { list: 'ordered' }, { list: 'check' },
						{ indent: '-1' }, { indent: '+1' }
					],
					[ 'bold', 'italic', 'underline', 'strike' ],
					[{ color: []}, {background: []}],
					[ 'link', 'image', 'video', 'code', 'code-block' ]
				]
			},
			theme: 'snow'
		};
		var quill = new Quill($quill.get(0), options);
		if ($this.text().beginsWith('{')) {
			quill.setContents(JSON.parse($this.text()));
		}
		else {
			$quill.find('.ql-editor').html($this.text().replace("\n", '<br>')).keyup();
		}
	});
});
