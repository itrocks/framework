$(document).ready(function()
{
	// Only if we have select_all, we can exclude a part of elements
	var excluded_selection = [];
	var select_all         = [];
	var selection          = [];

	var selector_checkbox = 'th > input.selector[type=checkbox]';
	var checkboxes_select = 'th > input[type=checkbox]:not(.selector)';

	//------------------------------------------------------------------------------ unselectFromList
	window.unselectFromList = function(class_name, object_id)
	{
		var selection_id;
		for (selection_id in excluded_selection) if (excluded_selection.hasOwnProperty(selection_id)) {
			if (selection_id.startsWith(class_name + DOT)) {
				excluded_selection[selection_id] = excluded_selection[selection_id].withoutValue(object_id);
			}
		}
		for (selection_id in selection) if (selection.hasOwnProperty(selection_id)) {
			if (selection_id.startsWith(class_name + DOT)) {
				selection[selection_id] = selection[selection_id].withoutValue(object_id);
			}
		}
	};

	//-------------------------------------------------------------------------------- resetSelection
	var resetSelection = function(id)
	{
		excluded_selection[id] = [];
		select_all[id]         = false;
		selection[id]          = [];
	};

	//----------------------------------------------------------------------------------- updateCount
	var updateCount = function($article_list, $selector, $summary)
	{
		var count_elements, select_all_content, selection_content, selection_exclude_content, text;
		var $selection_checkbox = $article_list.find(selector_checkbox);
		var selection_checkbox  = $selection_checkbox[0];
		var title;
		var total = $selector.children('input[name=select_all]').data('count');
		if (select_all[$article_list.id]) {
			select_all_content         = 1;
			selection_content          = '';
			selection_exclude_content  = excluded_selection[$article_list.id].join();
			count_elements             = total;
			count_elements            -= excluded_selection[$article_list.id].length;
			text                       = count_elements;
			selection_checkbox.checked = true;
			title = tr('uncheck to deselect all lines');
		}
		else {
			selection_content          = selection[$article_list.id].join();
			select_all_content         = 0;
			selection_exclude_content  = '';
			text                       = selection[$article_list.id].length;
			selection_checkbox.checked = false;
			title = tr('check to select all $!1 lines').repl('$!1', total);
		}
		$summary.html($summary.data('text').replace('?', text));
		$selector.children('input[name=excluded_selection]').val(selection_exclude_content);
		$selector.children('input[name=select_all]')        .val(select_all_content);
		$selector.children('input[name=selection]')         .val(selection_content);
		selection_checkbox.indeterminate = (selection_exclude_content || selection_content);
		$selection_checkbox.parent().attr('title', title);
	};

	//---------------------------------------------------------------------------------- article.list
	$('body').build('each', 'article.list', function()
	{
		var $article  = $(this);
		var $table    = $article.find('> form > table');
		var $search   = $table.find('> thead > .search');
		var $selector = $table.find('> thead > .title > .visible.lines');
		var $summary  = $article.find('.summary .lines');
		$article.id   = $article.attr('id');
		$summary.data('text', $summary.html());

		//-------------------------------------------------------------- .search input|textarea keydown
		// reload list when #13 pressed into a search input
		$search.find('input, textarea').keydown(function(event)
		{
			if (event.keyCode === 13) {
				var $this = $(this);
				resetSelection($this.closest('article.list').attr('id'));
				$this.closest('form').submit();
			}
		});

		//----------------------------------------------------------------------- .search select change
		$search.find('select').change(function()
		{
			var $this = $(this);
			resetSelection($this.closest('article.list').attr('id'));
			$this.closest('form').submit();
		});

		//--------------------------------------------------------------- .search .reset.search a click
		$search.find('.reset > a').click(function()
		{
			resetSelection($(this).closest('article.list').attr('id'));
		});

		//------------------------------------------------------------------ input[type=checkbox] check
		var $checkboxes = $article.find(checkboxes_select);
		if ($article.id in selection) {
			$checkboxes.each(function() {
				if (
					(select_all[$article.id] && ($.inArray(this.value, excluded_selection[$article.id]) === -1))
					|| $.inArray(this.value, selection[$article.id]) !== -1
				) {
					$(this).prop('checked', true);
				}
			});
		}
		else {
			excluded_selection[$article.id] = [];
			select_all[$article.id]         = false;
			selection[$article.id]          = [];
		}

		//-------------------------------------------------------- input.selector[type=checkbox] change
		$article.find(selector_checkbox).change(function(event)
		{
			if (this.indeterminate) {
				this.indeterminate = false;
			}
			selectAction(this.checked, 'all', event);
		});

		//---------------------------------------------------------------------------- tbody > th click
		$article.find('th > input[type=checkbox]').parent().click(function(event)
		{
			if ($(event.target).is('input[type=checkbox]')) {
				return;
			}
			$(this).children('input[type=checkbox]').click();
		});

		//----------------------------------------------------------------- input[type=checkbox] change
		$checkboxes.change(function()
		{
			if (select_all[$article.id]) {
				if (!this.checked && (excluded_selection[$article.id].indexOf(this.value) === -1)) {
					excluded_selection[$article.id].push(this.value);
				}
				if (this.checked && (excluded_selection[$article.id].indexOf(this.value) > -1)) {
					excluded_selection[$article.id]
						.splice(excluded_selection[$article.id].indexOf(this.value), 1);
				}
				$article.find(checkboxes_select + '[value=' + this.value + ']')
					.attr('checked', this.checked);
			}
			else {
				if (this.checked && (selection[$article.id].indexOf(this.value) === -1)) {
					selection[$article.id].push(this.value);
				}
				if (!this.checked && (selection[$article.id].indexOf(this.value) > -1)) {
					selection[$article.id].splice(selection[$article.id].indexOf(this.value), 1);
				}
				// Repercussion if with have multiple lines
				$article.find(checkboxes_select + '[value=' + this.value + ']')
					.attr('checked', this.checked);
			}
			updateCount($article, $selector, $summary);
		});

		updateCount($article, $selector, $summary);

		//-------------------------------------------------------------------------------- selectAction
		/**
		 * Select / deselect buttons
		 *
		 * @param select boolean true to select, false to deselect
		 * @param type   string  @values all, matching, visible
		 * @param event  Event
		 */
		var selectAction = function(select, type, event)
		{
			if (type === 'all') {
				// Re-initialize selection
				excluded_selection[$article.id] = [];
				select_all[$article.id]         = select;
				selection[$article.id]          = [];
				$article.find('input[type=checkbox]').prop('checked', select);
			}
			else {
				$article.find('input[type=checkbox]').each(function() {
					var checkbox = $(this);
					checkbox.prop('checked', select);
					checkbox.change();
				});
			}
			updateCount($article, $selector, $summary);
			event.preventDefault();
		};

		//----------------------------------------------------------- .selection.actions a.submit click
		$article.find('.selection.actions a.submit:not([target^="#"])').click(function(event)
		{
			var data = {
				excluded_selection: $selector.children('input[name=excluded_selection]').val(),
				select_all:         $selector.children('input[name=select_all]').val(),
				selection:          $selector.children('input[name=selection]').val()
			};
			var form   = document.createElement('form');
			var target = $(this).attr('target');
			// remember to change me :
			form.action = event.target;
			form.method = 'post';
			form.target = target;
			for (var key in data) if (data.hasOwnProperty(key)) {
				var input   = document.createElement('input');
				input.name  = key;
				input.type  = 'hidden';
				input.value = data[key];
				form.appendChild(input);
			}
			// must add to body to submit with refresh page
			document.body.appendChild(form);
			form.submit();
			// clean html dom
			document.body.removeChild(form);
			return false;
		});
	});

});
