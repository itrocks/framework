<?php
namespace SAF\Framework\Objects;

/**
 * The Counter class manages business-side counters : ie invoices numbers, etc.
 *
 * It deals with application-side locking in order that the next number has no jumps nor replicates
 */
class Counter
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @var string
	 */
	public $format;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	public $identifier;

	//----------------------------------------------------------------------------------- $last_value
	/**
   * @var integer
   */
	public $last_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier string
	 */
	public function __construct($identifier = null)
	{
		if (isset($identifier)) {
			$this->identifier = $identifier;
		}
	}

	//------------------------------------------------------------------------------------------ next
	/**
	 * Returns the next value for the counter (with format)
	 *
	 * @return string
	 */
	public function next()
	{
	}

}
