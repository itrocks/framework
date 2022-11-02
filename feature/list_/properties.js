$(document).ready(function()
{
	const $body = $('body')

	//----------------------------------------------------------------------------------- addProperty
	const addProperty = function($object, property_name, before_after, before_after_property_name)
	{
		const $article   = $object.closest('article.list')
		const app        = window.app
		const class_name = $article.data('class').repl(BS, SL)
		let   uri        = app.uri_base + SL + class_name + SL + 'listSetting'
			+ '?add_property=' + property_name
		if (before_after_property_name !== undefined) {
			uri += '&' + before_after + '=' + before_after_property_name
		}
		uri += '&as_widget' + app.andSID()
		$.ajax({ url: uri, success: function(answer)
		{
			const class_name   = $article.data('class').repl(BS, SL)
			const feature_name = $article.data('feature')
			const url          = app.uri_base + SL + class_name + SL + feature_name
				+ '?as_widget' + window.app.andSID()
			$.ajax({ url: url, success: function(article_container_content)
			{
				const $container = $article.parent()
				$container.html(article_container_content)
				$container.children().build()
			}})
			$('#responses').html(answer)
		}})
	}

	//------------------------------------------------------------------------------------------ drag
	/**
	 * when a property is dragged over the droppable object
	 */
	const drag = function(event, ui)
	{
		const $droppable     = $(this)
		const draggable_left = ui.offset.left + (ui.helper.width() / 2)
		let   count          = 0
		let   found          = 0
		const is_table       = $droppable.is('table')
		const $columns       = $droppable.find((is_table ? 'tr > *' : 'ol > li') + ':not(:first)')
		$columns.each(function() {
			count ++
			const $this = $(this)
			const $prev = $this.prev(is_table ? 'td, th' : 'li')
			const left  = $prev.offset() ? ($prev.offset().left + $prev.width()) : 0
			const right = $this.offset().left + $this.width()
			if ((draggable_left > left) && (draggable_left <= right)) {
				found     = (draggable_left <= ((left + right) / 2)) ? count : (count + 1)
				const old = $droppable.data('insert-after')
				if (found !== old) {
					let select
					if (old !== undefined) {
						select = (is_table ? 'tr > *' : 'ol > li') + ':nth-child(' + old + ')'
						$droppable.find(select).removeClass('insert-right')
					}
					select = (is_table ? 'tr > *' : 'ol > li') + ':nth-child(' + found + ')'
					$droppable.find(select).addClass('insert-right')
					$droppable.data('insert-after', found)
				}
				return false
			}
		})
	}

	//------------------------------------------------------------------------------------------- out
	/**
	 * when a property is not longer between two columns
	 */
	const out = function($this)
	{
		$this.find('.insert-right').removeClass('insert-right')
		$this.removeData('insert-after')
		$this.removeData('drag-callback')
	}

	//---------------------------------------------------------------------------------- article.list
	$body.build('call', 'article.list', function()
	{

		this.each(function()
		{
			const $this = $(this)
			const $list = $this.find('table.list, ul.list')

			//------------------------------------------------------------ .column_select > a.popup click
			// column select popup
			$this.find('.column_select > a.popup').click(function(event)
			{
				const $this = $(this)
				const $div  = $this.closest('.column_select').find('#column_select')
				if ($div.length) {
					if ($div.is(':visible')) {
						$div.hide()
					}
					else {
						$div.show()
						$div.find('input').first().focus()
					}
					event.preventDefault()
					event.stopImmediatePropagation()
				}
			})

			//--------------------------------------------------------------------------- .list droppable
			$list.droppable(
			{
				accept:    '.property',
				tolerance: 'touch',

				drop: function(event, ui)
				{
					const $this        = $(this)
					const insert_after = $this.data('insert-after')
					const is_table     = $this.is('table')
					if (insert_after !== undefined) {
						const insert_before = insert_after + 1
						const $th = $this.find(
							(is_table ? 'tr:first > th' : 'ol:first > li') + ':nth-child(' + insert_before + ')'
						)
						const $draggable           = ui.draggable
						const before_property_name = $th.data('property')
						const property_name        = $draggable.data('property')
						addProperty($this, property_name, 'before', before_property_name)
					}
					out($this, event, ui)
					ui.helper.data('dropped', true)
				},

				out: function(event, ui)
				{
					out($(this), event, ui)
					ui.draggable.removeData('over-droppable')
					ui.helper.addClass('outside').removeClass('inside')
				},

				over: function(event, ui)
				{
					const $this = $(this)
					$this.data('drag-callback', drag)
					ui.draggable.data('over-droppable', $this)
					ui.helper.addClass('inside').removeClass('outside')
				}
			})

		})
	})

	//-------------------------------------------------------- #column_select li.basic.property click
	$body.build('click', '.property-select .property', function()
	{
		const $this      = $(this)
		const data_class = $this.closest('.property-select').data('class')
		const selector   = 'article.list[data-class=' + data_class.repl(BS, BS + BS) + ']'
		const $list      = $(selector)
		if ($list.length) {
			addProperty($list, $this.data('property'), 'before')
		}
	})

})
