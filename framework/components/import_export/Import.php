<?php
namespace SAF\Framework;

/**
 * Standard import class for your objects data
 */
class Import
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @link Object
	 * @var Import_Export_Format
	 */
	public $format;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @link Object
	 * @var File
	 */
	public $file;

	//----------------------------------------------------------------------------------- $worksheets
	/**
	 * @var Import_Worksheet[]
	 */
	public $worksheets;

}
