<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Dao\Option;

/**
 * A built object ready for write
 */
class Built_Object
{

	//--------------------------------------------------------------------------------------- $object
	public object $object;

	//-------------------------------------------------------------------------------- $write_options
	/**
	 * You may want to set some write options here, like Dao::only(...)
	 * TODO LOW cannot be easily automated for data coming from forms : @before_write and #Setter may change other values
	 *
	 * @var Option[]
	 */
	public array $write_options = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object|null
	 * @param $write_options Option[]|null
	 */
	public function __construct(object $object = null, array $write_options = null)
	{
		if (isset($object))        $this->object        = $object;
		if (isset($write_options)) $this->write_options = $write_options;
	}

}
