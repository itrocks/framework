
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
			],
			[ 'table' ]
		],
		'standard': [
			[ 'bold', 'italic', 'underline', 'strike' ],
			[{ color: [] }, { background: [] }],
			[ 'link', 'image', 'video', 'code', 'code-block' ],
			[{ header: [false, 1, 2, 3, 4, 5] }, { align: [] }],
			[
				{ list: 'bullet' }, { list: 'ordered' }, { list: 'check' },
				{ indent: '-1' }, { indent: '+1' }
			],
			[ 'table' ]
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
		$this.parent().addClass('ql');

		$quill.keyup(function() {
			// TODO later depending on data-store-format $this.text(JSON.stringify(quill.getContents()))
			$this.text($quill.find('.ql-editor').html());
		});

		var options = {
			modules: { table: true, toolbar: toolbars[quillModule($this)] },
			theme: 'snow'
		};
		var quill = new Quill($quill.get(0), options);
		var table = quill.getModule('table');

		if ($this.text().beginsWith('{')) {
			quill.setContents(JSON.parse($this.text()));
		}
		else {
			$quill.find('.ql-editor').html($this.text().repl("\n", '<br>')).keyup();
		}

		$quill = $quill.parent();
		var $toolbar   = $quill.children('.ql-toolbar');
		var $container = $quill.children('.ql-container');
		var $editor    = $container.children('.ql-editor');

		// keyboard / click outside of the table tools remove them
		$(document).click  (function() { $('.ql-table-tools').remove(); });
		$(document).keydown(function() { $('.ql-table-tools').remove(); });

		//------------------------------------------------------------------------- $editor contextmenu
		/**
		 * Right-click into a table show contextual table buttons
		 */
		$editor.contextmenu(function(event)
		{
			var $cell = $(event.target).closest('td');
			if (!$cell.length) {
				return;
			}
			var $quill = $cell.closest('.ql-container').parent();
			var $table_tools = $quill.children('.ql-table-tools');
			if ($table_tools.length) {
				$table_tools.remove();
				return false;
			}

			// - create table tools button bar
			$table_tools = $('<ul class="ql-table-tools">');
			$table_tools.append('<li class="ql-table-delete"><button>delete table');
			$table_tools.append('<li class="ql-table-delete-column"><button>delete column');
			$table_tools.append('<li class="ql-table-delete-row"><button>delete row');
			$table_tools.append('<li class="ql-table-insert-column-left"><button>insert column left');
			$table_tools.append('<li class="ql-table-insert-column-right"><button>insert column right');
			$table_tools.append('<li class="ql-table-insert-row-above"><button>insert row above');
			$table_tools.append('<li class="ql-table-insert-row-below"><button>insert row below');
			$table_tools.appendTo($quill).build();

			// - button positions
			var $actions = $table_tools.children('li');
			var $row     = $cell.closest('tr');
			var $table   = $row.closest('table');
			(function() {
				var cell   = $cell.offset();
				var offset = $quill.offset();
				var table  = $table.offset();
				var left = function(left) { return (left - offset.left).toString() + 'px'; };
				var top  = function(top)  { return (top - offset.top).toString() + 'px'; };
				// delete table
				$($actions[0]).css({
					left: left(table.left + $table.width() - 32),
					top:  top(table.top + $table.height())
				});
				// delete column
				$($actions[1]).css(
					($row.prev().length || $row.next().length)
					? {
						left: left(cell.left + $cell.width() / 2 - 9),
						top:  top($row.prev().length ? (table.top - 24) : (table.top + $table.height()))
					}
					: { display: 'none' }
				);
				// delete row
				$($actions[2]).css(
					($cell.prev().length || $cell.next().length)
					? {
						left: left($cell.prev().length ? (table.left - 24) : (table.left + $table.width())),
						top:  top(cell.top + $cell.height() / 2 - 9)
					}
					: { display: 'none' }
				);
				// insert column left
				$($actions[3]).css({
					left: left(cell.left - 20),
					top:  top(cell.top + $cell.height() / 2 - 9)
				});
				// insert column right
				$($actions[4]).css({
					left: left(cell.left + $cell.width() + 8),
					top:  top(cell.top + $cell.height() / 2 - 9)
				});
				// insert row above
				$($actions[5]).css({
					left: left(cell.left + $cell.width() / 2 - 9),
					top:  top(cell.top - 20)
				});
				// insert row below
				$($actions[6]).css({
					left: left(cell.left + $cell.width() / 2 - 9),
					top:  top(cell.top + $cell.height() + 2)
				});
			})();

			// - link clicked buttons to quill actions on table
			$actions = $actions.children('button').attr('type', 'button');
			$($actions[0]).click(function() { table.deleteTable(); });
			$($actions[1]).click(function() { table.deleteColumn(); });
			$($actions[2]).click(function() { table.deleteRow(); });
			$($actions[3]).click(function() { table.insertColumnLeft(); });
			$($actions[4]).click(function() { table.insertColumnRight(); });
			$($actions[5]).click(function() { table.insertRowAbove(); });
			$($actions[6]).click(function() { table.insertRowBelow(); });

			// - delete buttons highlights column / row / table in red for deletion
			$($actions[0]).hover(
				function() { $table.css('background', '#fcc'); },
				function() { $table.css('background', ''); }
			);
			$($actions[1]).hover(
				function() {
					var $tds = $table.find('td:nth-child(' + ($cell.prevAll().length + 1) + ')');
					$tds.css('background', '#fcc');
				},
				function() {
					$table.find('td').css('background', '');
				}
			);
			$($actions[2]).hover(
				function() { $row.css('background', '#fcc'); },
				function() { $row.css('background', ''); }
			);

			// - disable the browsers context menu
			return false;
		});

		//----------------------------------------------------------------------- scrollParent() scroll
		/**
		 * On article scroll : toolbar always visible
		 */
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

		//----------------------------------------------------------------------------- .ql-table click
		/**
		 * On insert table : create a 4x4 table instead of 1x1
		 */
		$toolbar.find('.ql-table').click(function()
		{
			table.insertColumnRight();
			table.insertColumnRight();
			table.insertColumnRight();
			table.insertRowBelow();
			table.insertRowBelow();
			table.insertRowBelow();
		});

	});

	//-------------------------------------------------------------------------------------- language
	var uri = app.project_uri + '/itrocks/framework/js/quill/quill-' + app.language + '.css';
	$('head').append($('<link rel="stylesheet" href="' + uri + '">'));

});
