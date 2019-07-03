$(document).ready(function()
{
	var $body = $('body');

	var block_selector  = 'ul.collection, ul.map';
	var parent_selector = block_selector + ', ul.data';

	//---------------------------------------------------------------------------------- depthReplace
	/**
	 * increment indexes in new row html code
	 */
	var depthReplace = function(text, old_index, new_index, open, close, depth)
	{
		var i;
		var j = 0;
		while ((i = text.indexOf('=' + DQ, j) + 1) > 0) {
			var in_depth = depth;
			j = text.indexOf(DQ, i + 1);
			while (
				(i = text.indexOf(open, i) + open.length) && (i > (open.length - 1)) && (i < j)
				&& ((in_depth > 0) || (text[i] < '0') || (text[i] > '9'))
				) {
				if ((text[i] >= '0') && (text[i] <= '9')) {
					in_depth --;
				}
			}
			if ((i > (open.length - 1)) && (i < j) && !in_depth) {
				var k          = text.indexOf(close, i);
				var html_index = text.substring(i, k);
				if (Number(html_index) === old_index) {
					text = text.substr(0, i) + new_index + text.substr(k);
				}
			}
		}
		return text;
	};

	//----------------------------------------------------------------------------------- autoAddLine
	/**
	 * @param event object jQuery event
	 * @param force boolean @default false
	 */
	var autoAddLine = function(event, force)
	{
		var $this = $(this);
		var $row  = $this.closest('tr, ul > li');
		if (
			((force !== undefined) && force)
			|| ($this.val() && ($this.val() !== '0') && $row.length && !$row.next('tr, li').length)
		) {
			var $block = $row.closest(parent_selector);
			if (!$block.data('itrocks_add')) {
				return;
			}
			// calculate depth in order to increment the right index
			var depth   = -1;
			var $parent = $block;
			while (($parent = $parent.parent()).length) {
				if ($parent.is(parent_selector)) {
					depth ++;
				}
			}
			// calculate new row and indexes
			var $new_row = $block.data('itrocks_add').clone();
			$block.data('itrocks_last_index', $block.data('itrocks_last_index') + 1);
			var new_index = $block.data('itrocks_last_index');
			var old_index = $block.data('itrocks_add_index');
			var html      = $new_row.html();
			if (html.indexOf('[') > -1) {
				html = depthReplace(html, old_index, new_index, '[', ']', depth);
			}
			if (html.indexOf('%5B') > 1) {
				html = depthReplace(html, old_index, new_index, '%5B', '%5D', depth);
			}
			$new_row.html(html);
			// append and build new row
			var $body = $block.children('tbody');
			if (!$body.length) {
				$body = $block;
			}
			$body.append($new_row);
			$new_row.autofocus(false);
			$new_row.build();
			$new_row.autofocus(true);
		}
	};

	//--------------------------------------------------------------- table.auto_width, ul.auto_width
	$body.build('each', block_selector, function()
	{
		var $this = $(this);
		var table = $this.is('table');
		// prepare new row
		var $new = $this.find(table ? '> tbody > tr.new' : '> li.new');
		if (!$new.length) {
			return;
		}
		$new.removeClass('new');
		$this.data('itrocks_add', $new.clone());
		// itrocks_add_index : the value of the index to be replaced into the model for new rows
		var objects = !table && !$this.children('li.header').length;
		var index = $this.find(table ? '> tbody > tr' : '> li').length - ((objects || table) ? 1 : 2);
		$this.data('itrocks_add_index', index);
		// itrocks_last_index : the last used index (lines count - 1)
		$this.data('itrocks_last_index', Math.max(0, $this.data('itrocks_add_index') - 1));
		if ($this.data('itrocks_add_index') > 0) {
			if ($new.find('input:not([class=file]):not([type=hidden]), select, textarea').length) {
				$new.remove();
			}
		}
	});

	//---------------------------------------------------- input, select, textarea change/focus/keyup
	/**
	 * Automatically add a line
	 */
	$body.build('call', [block_selector, 'input, select, textarea'], function()
	{
		this.change(autoAddLine).focus(autoAddLine).keyup(autoAddLine);
	});

	//------------------- article > form > ul.data ol.properties > li.component-objects > label click
	/**
	 * Manually add a line
	 */
	var add_selector = 'article > form > ul.data ol.properties > li.component-objects > label';
	$body.build('click', add_selector, function(event)
	{
		var $input = $(this).parent().find('input, select, textarea').filter(':visible').last();
		if ($input.length) {
			autoAddLine.call($input, event, true);
		}
	});

	//-------------------------------------------------------------------- li.multiple li.minus click
	/**
	 * Remove a line
	 */
	$body.build('click', [block_selector, 'button.minus, li.minus'], function(event)
	{
		var $this = $(this);
		// setTimeout allows other click events to .minus to execute before the row is removed
		setTimeout(function() {
			var $body = $this.closest('tbody, ul');
			if ($body.children().length > ($body.is('ul:not(.map)') ? 2 : 1)) {
				$this.closest('tr, ul > li').remove();
			}
			else {
				var $table   = $this.closest('table, ul');
				var $new_row = $table.data('itrocks_add').clone();
				$this.closest('tr, ul > li').replaceWith($new_row);
				$new_row.build();
				$table.data('itrocks_last_index', $table.data('itrocks_last_index') + 1);
			}
		});
		event.preventDefault();
	});

});
