$(document).ready(function()
{

	//----------------------------------------------------------------------------------- autoAddLine
	var autoAddLine = function()
	{
		var $this = $(this);
		var $row  = $this.closest('tr, ul > li');
		if ($this.val() && ($this.val() !== '0') && $row.length && !$row.next('tr, li').length) {
			var $block = $row.closest('.collection, .map');
			if ($block.length) {
				// calculate depth in order to increment the right index
				var depth   = 0;
				var $parent = $block;
				while (($parent = $parent.parent().closest('.collection, .map')).length) {
					depth ++;
				}
				// calculate new row and indexes
				var $new_row = $block.data('itrocks_add').clone();
				$block.data('itrocks_last_index', $block.data('itrocks_last_index') + 1);
				var index     = $block.data('itrocks_last_index');
				var old_index = $block.data('itrocks_add_index');
				// increment indexes in new row html code
				var depthReplace = function(text, open, close, depth)
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
							var k = text.indexOf(close, i);
							var html_index = text.substring(i, k);
							if (Number(html_index) === old_index) {
								text = text.substr(0, i) + index + text.substr(k);
							}
						}
					}
					return text;
				};
				$new_row.html(
					depthReplace(depthReplace($new_row.html(), '%5B', '%5D', depth), '[', ']', depth)
				);
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
		}
	};

	//-------------------------------------------- table.collection, table.map, ul.collection, ul.map
	var block_selector = 'table.collection, table.map, ul.collection, ul.map';
	$(block_selector).build('each', function()
	{
		var $this = $(this);
		var table = $this.is('table');
		// prepare new row
		var $new  = $this.find(table ? '> tbody > tr.new' : '> li.new');
		$this.data('itrocks_add', $new.clone());
		// itrocks_add_index : the value of the index to be replaced into the model for new rows
		var index = $this.find(table ? '> tbody > tr' : '> li').length - 1;
		$this.data('itrocks_add_index', index);
		// itrocks_last_index : the last used index (lines count - 1)
		$this.data('itrocks_last_index', Math.max(0, $this.data('itrocks_add_index') - 1));
		if ($this.data('itrocks_add_index')) {
			if ($new.find('input:not([class=file]):not([type=hidden]), select, textarea').length) {
				$new.remove();
			}
		}
	});

	//---------------------------------------------------- input, select, textarea change/focus/keyup
	$(block_selector).find('input, select, textarea').build(function()
	{
		this.change(autoAddLine).focus(autoAddLine).keyup(autoAddLine);
	});

	//-------------------------------------------------------------------- li.multiple li.minus click
	/**
	 * Remove a line
	 */
	$('li.multiple li.minus').build('click', function()
	{
		var $this = $(this);
		// setTimeout allows other click events to .minus to execute before the row is removed
		setTimeout(function() {
			var $body = $this.closest('tbody, ul');
			if ($body.children().length > ($body.is('ul') ? 2 : 1)) {
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
	});

});
