$(document).ready(function()
{

	//--------------------------------------------------------- input[class~=id][name] previous_value
	/**
	 * TODO isn't it dead code ? previous-value is not used anywhere
	 */
	$('body').build('each', 'input[class~=id][name]', function()
	{
		const $this = $(this)
		const $next = $this.next('input')
		if ($next.length && $this.val()) {
			$this.data('previous-value', [$this.val(), $next.val()])
		}
	})

})
