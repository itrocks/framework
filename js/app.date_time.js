$(document).ready(function()
{

	//----------------------------------------------------------------------------------- padWithZero
	/**
	 *
	 * @param value     string A date or time like this : '2803', '28/3', '9:28'
	 * @param separator string @values '/', ':'
	 * @return string will return this : '2803', '2803', '0928'
	 */
	const padWithZero = (value, separator) =>
	{
		if (value === undefined) {
			return ''
		}
		const split = value.split(separator)
		if (split[0] && (split[0].length < 2)) split[0] = split[0].padStart(2, '0')
		if (split[1] && (split[1].length < 2)) split[1] = split[1].padStart(2, '0')
		if (split[2] && (split[2].length < 2)) split[2] = split[2].padStart(2, '0')
		return split.join('')
	}

	//---------------------------------------------------------------------------------- reformatDate
	const reformatDate = ($datetime) =>
	{
		let datetime = $datetime.val()
		if (datetime === '') {
			return
		}
		let date, time
		[date, time] = datetime.split(SP, 2)
		date = padWithZero(date, '/')
		time = padWithZero(time, ':')
		const now = $.datepicker
			.formatDate($datetime.datepicker('option', 'dateFormat'), new Date())
			.replace(/\D/g, '')
			.lParse(SP)
		if (date.length < now.length) {
			date = (date.length === 6)
				? (date.substring(0, 4) + now.substring(4, 6) + date.substring(4))
				: (date + now.substring(date.length))
		}
		if (time.length && (time.length < 4)) {
			time += '0000'.substring(time.length)
		}
		datetime = date.substring(0, 2) + SL + date.substring(2, 4) + SL + date.substring(4, 8)
		if (time.length) {
			datetime += SP + time.substring(0, 2)
			if (time.length > 2) {
				datetime += ':' + time.substring(2, 4)
				if (time.length > 4) {
					datetime += ':' + time.substring(4)
				}
			}
		}
		$datetime.val(datetime)
	}

	//--------------------------------------------------------------- input.datetime datepicker/keyup
	$.datepicker.setDefaults($.datepicker.regional[window.app.language])
	$('body').build('call', 'input.datetime', function()
	{
		// if comes from a cloned datepicker, reinitialize it to avoid bugs and id mismatches
		if (this.hasClass('hasDatepicker')) {
			this.nextAll('button.ui-datepicker-trigger[type=button]').remove()
			this.removeClass('hasDatepicker')
			this.removeData()
			this.removeAttr('data-kpxc-id')
			this.removeAttr('id')
		}

		this.datepicker({
			constrainInput:    false,
			dateFormat:        dateFormatToDatepicker(window.app.date_format),
			firstDay:          1,
			showOn:            'button',
			showOtherMonths:   true,
			selectOtherMonths: true,
			showWeek:          true
		})

		this.blur(function()
		{
			reformatDate($(this))
		})

		this.keyup(function(event)
		{
			if (!event.ctrlKey) {
				if (event.keyCode === 38) {
					$(this).datepicker('hide')
				}
				if (event.keyCode === 40) {
					$(this).datepicker('show')
				}
			}
		})

		this.nextAll('button.ui-datepicker-trigger').attr('tabindex', -1)
	})

})
