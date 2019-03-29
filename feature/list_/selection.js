$(document).ready(function()
{
	// Only if we have select_all, we can exclude a part of elements
	var excluded_selection = [];
	var select_all         = [];
	var selection          = [];

	//-------------------------------------------------------------------------------- resetSelection
	var resetSelection = function()
	{
		excluded_selection = [];
		select_all         = [];
		selection          = [];
	};

	//----------------------------------------------------------------------------------- updateCount
	var updateCount = function($article_list, $selector)
	{
		var count_elements, select_all_content, selection_content, selection_exclude_content, text;
		if (select_all[$article_list.id]) {
			select_all_content        = 1;
			selection_content         = '';
			selection_exclude_content = excluded_selection[$article_list.id].join();
			count_elements  = $selector.find('> ul > li.select_all').data('count');
			count_elements -= excluded_selection[$article_list.id].length;
			text            = 'x' + count_elements;
		}
		else {
			selection_content         = selection[$article_list.id].join();
			select_all_content        = 0;
			selection_exclude_content = '';
			text                      = 'x' + selection[$article_list.id].length;
		}
		$selector.children('a').html(text);
		$selector.children('input[name=excluded_selection]').val(selection_exclude_content);
		$selector.children('input[name=select_all]')        .val(select_all_content);
		$selector.children('input[name=selection]')         .val(selection_content);
	};

	//---------------------------------------------------------------------------------- article.list
	$('article.list').build(function()
	{

		this.each(function()
		{
			var $this     = $(this);
			var $list     = $this.find('ul.list');
			var $search   = $list.children('.search');
			var $selector = $this.find('ul.footer > .selector');
			$this.id = $this.attr('id');

			//------------------------------------------------------------ .search input|textarea keydown
			// reload list when #13 pressed into a search input
			$search.find('input, textarea').keydown(function(event)
			{
				if (event.keyCode === 13) {
					resetSelection();
					$(this).closest('form').submit();
				}
			});

			//--------------------------------------------------------------------- .search select change
			$search.find('select').change(function()
			{
				resetSelection();
				$(this).closest('form').submit();
			});

			//------------------------------------------------------------- .search .reset.search a click
			$search.find('.reset > a').click(resetSelection);

			//--------------------------------------------------------------- input[type=checkbox] change
			var checkboxes_select = 'input[type=checkbox]';
			var $checkboxes       = $this.find(checkboxes_select);
			if ($this.id in selection) {
				$checkboxes.each(function() {
					if (
						(select_all[$this.id] && ($.inArray(this.value, excluded_selection[$this.id]) === -1))
						|| $.inArray(this.value, selection[$this.id]) !== -1
					) {
						$(this).prop('checked', true);
					}
				});
			}
			else {
				excluded_selection[$this.id] = [];
				select_all[$this.id]         = false;
				selection[$this.id]          = [];
			}

			$checkboxes.change(function()
			{
				if (select_all[$this.id]) {
					if (!this.checked && (excluded_selection[$this.id].indexOf(this.value) === -1)) {
						excluded_selection[$this.id].push(this.value);
					}
					if (this.checked && (excluded_selection[$this.id].indexOf(this.value) > -1)) {
						excluded_selection[$this.id]
							.splice(excluded_selection[$this.id].indexOf(this.value), 1);
					}
					$this.find(checkboxes_select + '[value=' + this.value + ']')
						.attr('checked', this.checked);
				}
				else {
					if (this.checked && (selection[$this.id].indexOf(this.value) === -1)) {
						selection[$this.id].push(this.value);
					}
					if (!this.checked && (selection[$this.id].indexOf(this.value) > -1)) {
						selection[$this.id].splice(selection[$this.id].indexOf(this.value), 1);
					}
					// Repercussion if with have multiple lines
					$this.find(checkboxes_select + '[value=' + this.value + ']')
						.attr('checked', this.checked);
				}
				updateCount($this, $selector);
			});

			updateCount($this, $selector);

			//------------------------------------------------------------------------------ selectAction
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
					excluded_selection[$this.id] = [];
					select_all[$this.id]         = select;
					selection[$this.id]          = [];
					$this.find('input[type=checkbox]').prop('checked', select);
				}
				else {
					$this.find('input[type=checkbox]').each(function() {
						var checkbox = $(this);
						checkbox.prop('checked', select);
						checkbox.change();
					});
				}
				updateCount($this, $selector);
				event.preventDefault();
			};

			//------------------------------------------------------------------- .select_count ... click
			$selector.find('> a.objects').click(function(event)
			{
				event.preventDefault();
			});

			$selector.find('li.deselect_all > a').click(function(event)
			{
				selectAction(false, 'all', event);
			});

			$selector.find('li.deselect_visible > a').click(function(event)
			{
				selectAction(false, null, event);
			});

			$selector.find('li.select_all > a').click(function(event)
			{
				selectAction(true, 'all', event);
			});

			$selector.find('li.select_visible > a').click(function(event)
			{
				selectAction(true, null, event);
			});

			$this.find('.selection.actions a.submit:not([target^="#"])').click(function(event)
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

});
