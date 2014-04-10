<?php
namespace SAF\Framework\View\Html\Builder;

use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Takes a value that stores a file content and builds HTML code using their data
 */
class File
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var Dao\File
	 */
	protected $file;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $file     Dao\File
	 */
	public function __construct($property, Dao\File $file)
	{
		$this->file = $file;
		$this->property = $property;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		return $this->file->name;
	}

}
