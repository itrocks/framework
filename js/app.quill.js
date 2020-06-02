$(document).ready(function()
{

	//-------------------------------------------------------------------------------------- toolbars
	var toolbars = {
		'simple': [
			[ 'bold', 'italic', 'underline' ],
			[{ color: [] }, { background: [] }],
			[ 'link', 'image' ],
			[{ header: [false, 1, 2, 3] }, { align: [] }],
			[
				{ list: 'bullet' }, { list: 'ordered' },
				{ indent: '-1' }, { indent: '+1' }
			]
		],
		'standard': [
			[ 'bold', 'italic', 'underline', 'strike' ],
			[{ color: [] }, { background: [] }],
			[ 'link', 'image', 'video', 'code', 'code-block' ],
			[{ header: [false, 1, 2, 3, 4, 5] }, { align: [] }],
			[
				{ list: 'bullet' }, { list: 'ordered' }, { list: 'check' },
				{ indent: '-1' }, { indent: '+1' }
			]
		]
	}

	//----------------------------------------------------------------------------------- quillModule
	/**
	 * @param $element jQuery textarea
	 * @return string
	 */
	var quillModule = function($element)
	{
		var classes = $element.attr('class').split(/\s+/);
		for (var i = 0; i < classes.length; i++) {
			if (classes[i].startsWith('quill-')) {
				return classes[i].substr(6);
			}
		}
		return 'standard';
	}

	//------------------------------------------------------------------------- textarea.quill-* each
	$('body').build('each', 'textarea[class*=quill-]', function()
	{
		var $this  = $(this);
		var $quill = $('<div>');
		$this.hide().after($quill);

		$quill.keyup(function() {
			// TODO later depending on data-store-format $this.text(JSON.stringify(quill.getContents()))
			$this.text($quill.find('.ql-editor').html());
		});

		var options = {
			modules: {
				imageResize: { modules: ['Resize', 'Toolbar'] },
				toolbar:     toolbars[quillModule($this)]
			},
			theme: 'snow'
		};
		var quill = new Quill($quill.get(0), options);
		if ($this.text().beginsWith('{')) {
			quill.setContents(JSON.parse($this.text()));
		}
		else {
			$quill.find('.ql-editor').html($this.text().repl("\n", '<br>')).keyup();
		}

		$quill = $quill.parent();
		var $container = $quill.children('.ql-container');
		var $toolbar   = $quill.children('.ql-toolbar');

		//-------------------------------------------------------------------------------------- scroll
		$this.scrollParent().scroll(function()
		{
			var $this = $(this);
			var top   = ($quill.offset().top - $this.offset().top);
			if (top < 0) {
				if (!$toolbar.attr('style')) {
					var container_shift = ($container.offset().top - $toolbar.offset().top);
					$quill.css('padding-top', container_shift.toString() + 'px');
					$toolbar.css({ position: 'fixed', top: $this.offset().top, 'z-index': 1 });
				}
				var width = $quill.width();
				if (width !== $toolbar.width()) {
					$toolbar.css({ width: width.toString() + 'px' });
				}
			}
			else if ($toolbar.attr('style')) {
				$quill.css('padding-top', '');
				$toolbar.attr('style', '');
			}
		});

	});

	//-------------------------------------------------------------------------------------- language
	var uri = app.project_uri + '/itrocks/framework/js/quill/quill-' + app.language + '.css';
	$('head').append($('<link rel="stylesheet" href="' + uri + '">'));

});
