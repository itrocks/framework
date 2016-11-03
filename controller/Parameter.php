<?php
namespace ITRocks\Framework\Controller;

/**
 * Controller parameter class
 *
 * Abstract as a Parameter is never stored as a Parameter object, but as an array element
 * associating parameter name with its value.
 * This class is used for constants only.
 */
class Parameter
{

	//-------------------------------------------------------------- Some general parameter constants

	//------------------------------------------------------------------------------------- AS_WIDGET
	const AS_WIDGET = 'as_widget';

	//------------------------------------------------------------------------------------- CONTAINER
	const CONTAINER = 'container';

	//-------------------------------------------------------------------------- EXPAND_PROPERTY_PATH
	const EXPAND_PROPERTY_PATH = 'expand_property_path';

	//----------------------------------------------------------------------------------- IS_INCLUDED
	const IS_INCLUDED = 'is_included';

	//----------------------------------------------------------------------------- PROPERTIES_FILTER
	const PROPERTIES_FILTER = 'properties_filter';

	//----------------------------------------------------------------------------- PROPERTIES_PREFIX
	const PROPERTIES_PREFIX = 'properties_prefix';

	//------------------------------------------------------------------------------ PROPERTIES_TITLE
	/**
	 * Note : properties titles must always be stored translated
	 */
	const PROPERTIES_TITLE = 'properties_title';

}
