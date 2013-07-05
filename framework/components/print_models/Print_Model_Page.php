<?php
namespace SAF\Framework;

/**
 * A print model page : a model linked to a unique page background and design
 */
class Print_Model_Page
{
	use Component;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link Object
	 * @var Print_Model
	 */
	public $model;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @dao file
	 * @link Object
	 * @var File
	 */
	public $background;

	//--------------------------------------------------------------------------------------- $zoning
	/**
	 * @var string
	 * @max-length 1000000
	 */
	public $zoning;

}
