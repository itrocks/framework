$(document).ready(function()
{
	var $body = $('body');

	//---------------------------------------------------------------------- input[type=file] .change
	$body.build('change', 'input[type=file]', function()
	{
		var $input   = $(this);
		var filename = $input.val();
		filename     = filename.rLastParse(filename.indexOf(BS) ? BS : SL, 1, true);
		$input.nextAll().remove();
		$input.after($('<span class="filename">').text(filename));
	});

	//------------------------------------------------------------------- li.file.object > div .click
	var files_selector = 'article > form > ul.data li.file.object > div, '
		+ 'article > form > ul.data ul.collection > li.data > ol > .file, '
		+ 'article > form > ul.data li[class~="file[]"] li[class~="file[]"] > div';
	$body.build('click', files_selector , function(event)
	{
		var $div    = $(this);
		var $target = $(event.target);
		if ($target.is($div)) {
			$div.children('input[type=file]').click();
		}
	});

});
