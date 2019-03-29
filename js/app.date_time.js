$(document).ready(function()
{

	//---------------------------------------------------------------------------- checkCompletedDate
	var checkCompletedDate = function($datetime)
	{
		if ($datetime.val() === '') {
			return true;
		}
		var formattedNow = $.datepicker.formatDate(
			$datetime.datepicker('option', 'dateFormat'),
			new Date()
		);

		// No completion needed
		if ($datetime.val().length >= formattedNow.length) {
			return checkDate($datetime);
		}
		else {
			var bufferVal = $datetime.val();
			$datetime.val(bufferVal + formattedNow.substr($datetime.val().length));
			//if  Completed date is not valid, fallback to input value
			return checkDate($datetime) || ($datetime.val(bufferVal) && false)
		}
	};

	//------------------------------------------------------------------------------------- checkDate
	var checkDate = function($datetime)
	{
		var format_date = $.datepicker.formatDate(
			$datetime.datepicker('option', 'dateFormat'),
			$datetime.datepicker('getDate')
		);
		return $datetime.val() === format_date;
	};

	//--------------------------------------------------------------- input.datetime datepicker/keyup
	$.datepicker.setDefaults($.datepicker.regional[window.app.language]);
	$('input.datetime').build(function()
	{
		this.datepicker({
			constrainInput: false,
			dateFormat: dateFormatToDatepicker(window.app.date_format),
			firstDay: 1,
			showOn: 'button',
			showOtherMonths: true,
			selectOtherMonths: true,
			showWeek: true
		});

		this.blur(function()
		{
			this.setCustomValidity(checkCompletedDate($(this)) ? '' : 'Invalid date');
		});

		this.keyup(function(event)
		{
			if (!event.ctrlKey) {
				if (event.keyCode === 38) {
					$(this).datepicker('hide');
				}
				if (event.keyCode === 40) {
					$(this).datepicker('show');
				}
			}
		});

		this.nextAll('button.ui-datepicker-trigger').attr('tabindex', -1);
	});

});
