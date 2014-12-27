<?php
namespace SAF\Framework\Controller;

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
	const AS_WIDGET   = 'as_widget';
	const CONTAINER   = 'container';
	const IS_INCLUDED = 'is_included';

}
