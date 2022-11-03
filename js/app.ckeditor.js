$(document).ready(function()
{

	CKEDITOR.disableAutoInline = true

	//------------------------------------------------------------------------------- getEditorConfig
	/**
	 * @param type string
	 * @return {{customConfig: string}}
	 */
	const getEditorConfig = function(type)
	{
		const file_name = 'ckeditor-config_'+ type +'.js'
		let   config    = {
			customConfig: window.app.project_uri + SL + 'itrocks/framework/js' + SL + file_name
		}
		if (window.app.editorConfig) {
			config = $.extend({}, config, window.app.editorConfig)
		}
		return config
	}

	//------------------------------------------------------------------------------- setEditorConfig
	const setEditorConfig = function(context, type)
	{
		$('body').build('call', '.ckeditor-' + type, function() {
			this.ckeditor(getEditorConfig(type))
		})
	}

	setEditorConfig(this, 'full')
	setEditorConfig(this, 'standard')

})
