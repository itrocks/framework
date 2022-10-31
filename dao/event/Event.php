<?php
namespace ITRocks\Framework\Dao;

/**
 * Dao event
 */
abstract class Event
{

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Data_Link
	 */
	public Data_Link $link;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public object $object;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	public array $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link    Data_Link
	 * @param $object  object
	 * @param $options Option[]
	 */
	public function __construct(Data_Link $link, object $object, array &$options)
	{
		$this->link    =  $link;
		$this->object  =  $object;
		$this->options =& $options;
	}

}
