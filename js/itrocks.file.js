$(document).ready(function()
{
	const $body = $('body')

	//---------------------------------------------------------------------- input[type=file] .change
	$body.build('change', 'input[type=file]', function()
	{
		const $input   = $(this)
		let   filename = $input.val()
		filename       = filename.rLastParse(filename.indexOf(BS) ? BS : SL, 1, true)
		$input.nextAll().remove()
		$input.after($('<span class="filename">').text(filename))
	})

	//------------------------------------------------------------------- li.file.object > div .click
	const files_selector = 'article > form li.file.object > div, '
		+ 'article > form .component-objects .file, '
		+ 'article > form li[class~="attachment[]"] li[class~="attachment[]"] > div, '
		+ 'article > form li[class~="file[]"] li[class~="file[]"] > div'
	$body.build('click', files_selector , function(event)
	{
		const $div    = $(this)
		const $target = $(event.target)
		if ($target.is($div)) {
			$div.children('input[type=file]').click()
		}
	})

})
