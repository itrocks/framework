$(window).on('load', function() {

	//------------------------------------------------------------------------------ $(window).resize
	$(window).resize(function()
	{
		var $windows = $(window).height() - $('.application.top').height();
		var $center  = $('.application.center');
		var $content = parseInt($center.css('margin-top'))
			+ parseInt($center.css('margin-bottom'))
			+ parseInt($center.css('padding-top'))
			+ parseInt($center.css('padding-bottom'));

		$center.children().each(function() {
			var $this = $(this);
			$content += $this.height();
			$content += parseInt($this.css('margin-top'))
				+ parseInt($this.css('margin-bottom'))
				+ parseInt($this.css('padding-top'))
				+ parseInt($this.css('padding-bottom'));
			$content += parseInt($this.css('border-left-width')) * 2;
		});
		$center.height((($windows > $content) ? $windows : $content));
	});

	$(window).resize();

});
