<?php
namespace SAF\Framework\View\Html\Builder;

use SAF\Framework;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Takes a value that stores a file content and builds HTML code using their data
 */
class File
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var Framework\File
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
	 * @param $file     Framework\File
	 */
	public function __construct($property, Framework\File $file)
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
