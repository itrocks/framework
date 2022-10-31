<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;

/**
 * After commit structure
 *
 * - At the same time than @after_write the written data information are kept for the next
 * transaction commit time
 * - When a transaction ends with a commit, all these kept events are fired
 */
class After_Action
{

	//----------------------------------------------------------------------------------- $annotation
	/**
	 * @var Method_Annotation
	 */
	private Method_Annotation $annotation;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private object $object;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	private array $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $annotation Method_Annotation
	 * @param $object     object
	 * @param $options    Option[]
	 */
	public function __construct(Method_Annotation $annotation, object $object, array $options)
	{
		$this->annotation = $annotation;
		$this->object     = $object;
		$this->options    = $options;
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * Call the @after_commit annotation method
	 *
	 * @param $link Data_Link
	 * @return mixed
	 */
	public function call(Data_Link $link) : mixed
	{
		return $this->annotation->call($this->object, [$link, $this->options]);
	}

	//--------------------------------------------------------------------------------------- callAll
	/**
	 * @param $actions static[]
	 * @param $link    Data_Link
	 * @return boolean false if calls chain was interrupted, true if every call were ok
	 */
	public static function callAll(array $actions, Data_Link $link) : bool
	{
		foreach ($actions as $action) {
			if ($action->call($link) === false) {
				return false;
			}
		}
		return true;
	}

}
