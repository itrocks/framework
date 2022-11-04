$(document).ready(function()
{
	$('body').build(
		'dblclick', 'article input[data-sensitive], article textarea[data-sensitive]',
		function()
		{
			const $this   = $(this)
			const $window = $this.closest('article[data-class]')
			const uri     = '/ITRocks/Framework/User/password'
				+ SL + $window.data('class').repl(BS, SL)
				+ SL + $window.data('id')
				+ SL + $window.data('feature')
				+ '?as_widget'

			$('article.user.password').parent().remove()

			redirect(
				uri,
				'#popup',
				$this,
				function($target) {
					$target.draggable({
						handle: 'h2',
						stop: function() {
							$(this).find('h2').data('stop-click', true)
						}
					})
				}
			)
		}
	)
})
