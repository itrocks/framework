$(window).on('load', function() {

	//------------------------------------------------------------------------------ $(window).resize
	$(window).resize(function()
	{
		const $windows = $(window).height() - $('.application.top').height()
		const $center  = $('.application.center')
		let   $content = parseInt($center.css('margin-top'))
			+ parseInt($center.css('margin-bottom'))
			+ parseInt($center.css('padding-top'))
			+ parseInt($center.css('padding-bottom'))

		$center.children().each(function() {
			const $this = $(this)
			$content += $this.height()
			$content += parseInt($this.css('margin-top'))
				+ parseInt($this.css('margin-bottom'))
				+ parseInt($this.css('padding-top'))
				+ parseInt($this.css('padding-bottom'))
			$content += parseInt($this.css('border-left-width')) * 2
		})
		$center.height((($windows > $content) ? $windows : $content))
	})

	$(window).resize()

})
