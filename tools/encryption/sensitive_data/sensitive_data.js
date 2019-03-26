$(document).ready(function()
{
	$('article[data-class]').build(function()
	{
		var $sensitive_data = this.inside('input[data-sensitive], textarea[data-sensitive]');

		//---------------------------------------------------------------------- [data-sensitive] click
		$sensitive_data.dblclick(function()
		{
			var $this   = $(this);
			var $window = $this.closest('article[data-class]');
			var uri     = '/ITRocks/Framework/User/password'
				+ SL + $window.data('class').repl(BS, SL)
				+ SL + $window.data('id')
				+ SL + $window.data('feature')
				+ '?as_widget';
			$('article.user.password').parent().remove();
			redirect(
				uri,
				'#popup',
				$this,
				function($target) {
					$target.autofocus();
					$target.draggable({
						handle: 'h2',
						stop: function() {
							$(this).find('h2>span').data('stop-click', true);
						}
					});
				}
			);
		});

	});
});
