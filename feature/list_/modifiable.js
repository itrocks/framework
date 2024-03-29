$(document).ready(function()
{

	//------------------------------------------------------------------------------------- className
	const className = function($this)
	{
		return $this.closest('article.list').data('class').repl(BS, SL)
	}

	//---------------------------------------------------------------------------------- propertyPath
	const propertyPath = function($this)
	{
		return $this.closest('[data-property]').data('property')
	}

	//---------------------------------------------------------------------------------- article.list
	$('body').build('each', 'article.list', function()
	{
		const $this = $(this)

		const callback_uri = window.app.uri_base + '/{className}/listSetting?as_widget'
			+ window.app.andSID()

		const list_property_uri = window.app.uri_base
			+ '/ITRocks/Framework/Feature/List_Setting/Property/edit/{className}/{propertyPath}?as_widget'
			+ window.app.andSID()

		//--------------------------------------- (article.list h2, ul.list li.property a) modifiable
		// list title (class name) double-click
		$this.find('> form > header > h2').modifiable({
			ajax:    callback_uri + '&title={value}',
			aliases: { 'className': className },
			target:  '#responses',
			start: function() {
				$(this).closest('article.list').find('> div.custom > ul.actions').css('display', 'none')
			},
			stop: function() {
				$(this).closest('article.list').find('> div.custom > ul.actions').css('display', '')
			}
		})

		// list column header (property path) double-click
		$this.find('> form > table.list > thead > tr.title > th.property > a').modifiable({
			ajax:      callback_uri + '&property_path={propertyPath}&property_title={value}',
			ajax_form: 'form',
			aliases:   { 'className': className, 'propertyPath': propertyPath },
			popup:     list_property_uri,
			target:    '#responses'
		})
	})

})
