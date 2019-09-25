$(document).ready(function()
{

	//------------------------------------------------------------------------------------- className
	var className = function($this)
	{
		return $this.closest('article.list').data('class');
	};

	//---------------------------------------------------------------------------------- propertyPath
	var propertyPath = function($this)
	{
		return $this.closest('li').data('property');
	};

	//---------------------------------------------------------------------------------- article.list
	$('body').build('each', 'article.list', function()
	{
		var $this = $(this);

		var callback_uri = window.app.uri_base + '/{className}/listSetting?as_widget'
			+ window.app.andSID();

		var list_property_uri = window.app.uri_base
			+ '/ITRocks/Framework/Feature/List_Setting/Property/edit/{className}/{propertyPath}?as_widget'
			+ window.app.andSID();

		//--------------------------------------- (article.list h2, ul.list li.property a) modifiable
		// list title (class name) double-click
		$this.find('> form > header > h2').modifiable({
			ajax:    callback_uri + '&title={value}',
			aliases: { 'className': className },
			target:  '#responses',
			start: function() {
				$(this).closest('article.list').find('> div.custom > ul.actions').css('display', 'none');
			},
			stop: function() {
				$(this).closest('article.list').find('> div.custom > ul.actions').css('display', '');
			}
		});

		// list column header (property path) double-click
		$this.find('> form > ul.list > li:first > ol > li.property > a').modifiable({
			ajax:      callback_uri + '&property_path={propertyPath}&property_title={value}',
			ajax_form: 'form',
			aliases:   { 'className': className, 'propertyPath': propertyPath },
			popup:     list_property_uri,
			target:    '#responses'
		});
	});

});
