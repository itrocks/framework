
$(document).ready(function()
{

	//-------------------------------------------------------------------------------------- toolbars
	const toolbars = {
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
	const quillModule = function($element)
	{
		const classes = $element.attr('class').split(/\s+/)
		for (let i = 0; i < classes.length; i++) {
			if (classes[i].startsWith('quill-')) {
				return classes[i].substr(6)
			}
		}
		return 'standard'
	}

	//------------------------------------------------------------------------- textarea.quill-* each
	$('body').build('each', 'textarea[class*=quill-]', function()
	{
		const $this  = $(this)
		let   $quill = $('<div>')
		$this.hide().after($quill)
		$this.parent().addClass('ql')

		const options = {
			modules: { table: true, toolbar: toolbars[quillModule($this)] },
			theme: 'snow'
		}
		const quill = new Quill($quill.get(0), options)
		const table = quill.getModule('table')

		quill.on('text-change', () => {
			$this.text($quill.find('.ql-editor').html())
		})

		if ($this.text().startsWith('{')) {
			quill.setContents(JSON.parse($this.text()))
		}
		else if ($this.text().trimStart().startsWith('<')) {
			$quill.find('.ql-editor').html($this.text().repl(LF, '').repl(TAB, '').repl(BR, LF)).keyup()
		}
		else {
			$quill.find('.ql-editor').html($this.text().repl(LF, BR)).keyup()
		}

		$quill = $quill.parent()
		const $toolbar   = $quill.children('.ql-toolbar')
		const $container = $quill.children('.ql-container')
		const $editor    = $container.children('.ql-editor')

		// keyboard / click outside of the table tools remove them
		$(document).click  (() => { $('.ql-table-tools').remove() })
		$(document).keydown(() => { $('.ql-table-tools').remove() })

		//------------------------------------------------------------------------- $editor contextmenu
		/**
		 * Right-click into a table show contextual table buttons
		 */
		$editor.contextmenu(function(event)
		{
			const $cell = $(event.target).closest('td')
			if (!$cell.length) {
				return
			}
			const $quill = $cell.closest('.ql-container').parent()
			let   $table_tools = $quill.children('.ql-table-tools')
			if ($table_tools.length) {
				$table_tools.remove()
				return false
			}

			// - create table tools button bar
			$table_tools = $('<ul class="ql-table-tools">')
			$table_tools.append('<li class="ql-table-delete"><button>delete table')
			$table_tools.append('<li class="ql-table-delete-column"><button>delete column')
			$table_tools.append('<li class="ql-table-delete-row"><button>delete row')
			$table_tools.append('<li class="ql-table-insert-column-left"><button>insert column left')
			$table_tools.append('<li class="ql-table-insert-column-right"><button>insert column right')
			$table_tools.append('<li class="ql-table-insert-row-above"><button>insert row above')
			$table_tools.append('<li class="ql-table-insert-row-below"><button>insert row below')
			$table_tools.appendTo($quill).build()

			// - button positions
			let   $actions = $table_tools.children('li')
			const $row     = $cell.closest('tr')
			const $table   = $row.closest('table');
			(() => {
				const cell   = $cell.offset()
				const offset = $quill.offset()
				const table  = $table.offset()
				const left = (left) => { return (left - offset.left).toString() + 'px' }
				const top  = (top)  => { return (top - offset.top).toString() + 'px' }
				// delete table
				$($actions[0]).css({
					left: left(table.left + $table.width() - 32),
					top:  top(table.top + $table.height())
				})
				// delete column
				$($actions[1]).css(
					($row.prev().length || $row.next().length)
					? {
						left: left(cell.left + $cell.width() / 2 - 9),
						top:  top($row.prev().length ? (table.top - 24) : (table.top + $table.height()))
					}
					: { display: 'none' }
				)
				// delete row
				$($actions[2]).css(
					($cell.prev().length || $cell.next().length)
					? {
						left: left($cell.prev().length ? (table.left - 24) : (table.left + $table.width())),
						top:  top(cell.top + $cell.height() / 2 - 9)
					}
					: { display: 'none' }
				)
				// insert column left
				$($actions[3]).css({
					left: left(cell.left - 20),
					top:  top(cell.top + $cell.height() / 2 - 9)
				})
				// insert column right
				$($actions[4]).css({
					left: left(cell.left + $cell.width() + 8),
					top:  top(cell.top + $cell.height() / 2 - 9)
				})
				// insert row above
				$($actions[5]).css({
					left: left(cell.left + $cell.width() / 2 - 9),
					top:  top(cell.top - 20)
				})
				// insert row below
				$($actions[6]).css({
					left: left(cell.left + $cell.width() / 2 - 9),
					top:  top(cell.top + $cell.height() + 2)
				})
			})()

			// - link clicked buttons to quill actions on table
			$actions = $actions.children('button').attr('type', 'button')
			$($actions[0]).click(() => { table.deleteTable() })
			$($actions[1]).click(() => { table.deleteColumn() })
			$($actions[2]).click(() => { table.deleteRow() })
			$($actions[3]).click(() => { table.insertColumnLeft() })
			$($actions[4]).click(() => { table.insertColumnRight() })
			$($actions[5]).click(() => { table.insertRowAbove() })
			$($actions[6]).click(() => { table.insertRowBelow() })

			// - delete buttons highlights column / row / table in red for deletion
			$($actions[0]).hover(
				() => { $table.css('background', '#fcc') },
				() => { $table.css('background', '') }
			)
			$($actions[1]).hover(
				() => {
					const $tds = $table.find('td:nth-child(' + ($cell.prevAll().length + 1) + ')')
					$tds.css('background', '#fcc')
				},
				() => {
					$table.find('td').css('background', '')
				}
			)
			$($actions[2]).hover(
				() => { $row.css('background', '#fcc') },
				() => { $row.css('background', '') }
			)

			// - disable the browsers context menu
			return false
		})

		//----------------------------------------------------------------------- scrollParent() scroll
		/**
		 * On article scroll : toolbar always visible
		 */
		$this.scrollParent().scroll(function()
		{
			const $this = $(this)
			const top   = ($quill.offset().top - $this.offset().top)
			if (top < 0) {
				if (!$toolbar.attr('style')) {
					const container_shift = ($container.offset().top - $toolbar.offset().top)
					$quill.css('padding-top', container_shift.toString() + 'px')
					$toolbar.css({ position: 'fixed', top: $this.offset().top, 'z-index': 1 })
				}
				const width = $quill.width()
				if (width !== $toolbar.width()) {
					$toolbar.css({ width: width.toString() + 'px' })
				}
			}
			else if ($toolbar.attr('style')) {
				$quill.css('padding-top', '')
				$toolbar.attr('style', '')
			}
		})

		//----------------------------------------------------------------------------- .ql-table click
		/**
		 * On insert table : create a 4x4 table instead of 1x1
		 */
		$toolbar.find('.ql-table').click(function()
		{
			table.insertColumnRight()
			table.insertColumnRight()
			table.insertColumnRight()
			table.insertRowBelow()
			table.insertRowBelow()
			table.insertRowBelow()
		})

	})

	//-------------------------------------------------------------------------------------- language
	const uri = app.project_uri + '/itrocks/framework/js/quill/quill-' + app.language + '.css'
	$('head').append($('<link rel="stylesheet" href="' + uri + '">'))

})
