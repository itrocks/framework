<?php
namespace SAF\Framework;

/**
 * Takes a value that stores a file content and builds HTML code using their data
 */
class Html_Builder_File
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
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
	 * @param $file     File
	 */
	public function __construct($property, File $file)
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
