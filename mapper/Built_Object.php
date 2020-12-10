<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Dao\Option;

/**
 * A built object ready for write
 */
class Built_Object
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------- $write_options
	/**
	 * You may want to set some write options here, like Dao::only(...)
	 * TODO LOW cannot be easily automated for data coming from forms : @before_write and @setter may change other values
	 *
	 * @var Option[]
	 */
	public $write_options = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $write_options Option[]
	 */
	public function __construct($object = null, array $write_options = null)
	{
		if (isset($object))        $this->object        = $object;
		if (isset($write_options)) $this->write_options = $write_options;
	}

}
