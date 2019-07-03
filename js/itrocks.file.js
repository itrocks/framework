$(document).ready(function()
{

	var anti_recurse = false;
	$('body').build('click', 'article > form > ul.data li.file.object > div', function()
	{
		var $div = $(this);
		if (
			(document.mouse.x < ($div.offset().left + 22))
			&& (document.mouse.y < ($div.offset().top + 22))
		) {
			if (anti_recurse) return;
			anti_recurse = true;
			$div.children('input[type=file]').click();
			anti_recurse = false;
		}
	});

});
