$(document).ready(function()
{

	//--------------------------------------------------------- input[class~=id][name] previous_value
	/**
	 * TODO isn't it dead code ? previous-value is not used anywhere
	 */
	$('input[class~=id][name]').build('each', function()
	{
		var $this = $(this);
		var $next = $this.next('input');
		if ($next.length && $this.val()) {
			$this.data('previous-value', [$this.val(), $next.val()]);
		}
	});

});
