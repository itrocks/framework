<?php
namespace ITRocks\Framework\Debug;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Traits\Date_Logged;

/**
 * Tell if a feature is dead or alive
 * This is a dead-code detector
 *
 * @set Dead_Or_Alive
 */
class Dead_Or_Alive
{
	use Date_Logged;

	//-------------------------------------------------------------------------------------- $counter
	/**
	 * @var integer
	 */
	public int $counter = 0;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var string
	 */
	public string $file;

	//----------------------------------------------------------------------------------- $identifier
	/**
	 * @var string
	 */
	public string $identifier;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public int $line;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $identifier string|null
	 */
	public function __construct(string $identifier = null)
	{
		if (isset($identifier)) {
			$this->identifier = $identifier;
			$this->matchCallStack();
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->file . ':' . $this->line . ':' . $this->identifier;
	}

	//--------------------------------------------------------------------------------------- isAlive
	/**
	 * Increment a Dead_Or_Alive object matching $identifier
	 *
	 * @param $identifier string
	 */
	public static function isAlive(string $identifier)
	{
		$search             = Search_Object::create(static::class);
		$search->identifier = $identifier;
		$search->matchCallStack(['file']);
		Dao::begin();
		$doa = Dao::searchOne($search);
		if (!$doa) {
			$doa = new Dead_Or_Alive($identifier);
		}
		$doa->matchCallStack();
		$doa->counter ++;
		Dao::write($doa);
		Dao::commit();
	}

	//-------------------------------------------------------------------------------- matchCallStack
	/**
	 * @param $property_names string[]|null
	 */
	private function matchCallStack(array $property_names = null)
	{
		$call_stack = new Call_Stack();
		$call_stack->shift();
		$line = $call_stack->lines()[0];
		if (!$property_names || in_array('file', $property_names)) $this->file = $line->file;
		if (!$property_names || in_array('line', $property_names)) $this->line = $line->line;
	}

}
