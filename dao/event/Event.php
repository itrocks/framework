<?php
namespace SAF\Framework\Dao;

/**
 * Dao events
 */
abstract class Event
{

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Data_Link
	 */
	public $link;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	public $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param  $link    Data_Link
	 * @param  $options Option[]
	 */
	public function __construct(Data_Link $link, array &$options)
	{
		$this->link = $link;
		$this->options =& $options;
	}

}
