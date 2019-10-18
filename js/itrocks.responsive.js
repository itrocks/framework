var phone_max_width = 469;

//----------------------------------------------------------------------------------------- isPhone
function isPhone()
{
	return document.body.clientWidth <= phone_max_width;
}

$(document).ready(function()
{

	//-------------------------------------------------------------------------------- main > article
	$('body').build('call', 'main > article', function()
	{
		if (isPhone()) {
			if (!$('body.min-left').length) {
				$('#menu .minimize').click();
			}
		}
	});

});
