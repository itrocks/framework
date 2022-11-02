(function($)
{

	/**
	 * Allow your pages to contain implicit form submit calls, into a elements
	 *
	 * - Works with <a href="...?#formid"> / <a href="...?...&#formid"> links
	 * - Initialise this feature with a single $('body').aform(); call
	 *
	 * @example
	 * <a href="execute_page?#my_form">Submit my form</a>
	 * <form id="my_form">(...)</form>
	 */
	$.fn.aform = function()
	{

		//----------------------------------------- $('a[href*="?#"]').click / $('a[href*="&#"]').click
		/**
		 * <a> with href finishing with "?#form_id" or "&#form_id" are form submitters
		 */
		this.find('a[href*="?#"],a[href*="&#"],a[href*="?&"]').click(function(event)
		{
			const $this = $(this)
			const href = $this.attr('href')
			if (href.indexOf('#') >= 0) {
				event.preventDefault()
				const index = href.indexOf('#')
				const form_id = href.substring(index)
				const $form = $(form_id)
				$form.attr('action', href.substring(0, index).repl('?&', '?'))
				$form.submit()
			}
		})

		return this
	}

})( jQuery )
