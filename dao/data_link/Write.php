<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Parent class for all data links Write class
 */
abstract class Write
{

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Identifier_Map
	 */
	protected $link;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	protected $object;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	protected $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Write constructor
	 *
	 * @param $link    Identifier_Map
	 * @param $object  object
	 * @param $options Option[]
	 */
	public function __construct(Identifier_Map $link, $object, array $options)
	{
		$this->link    = $link;
		$this->object  = $object;
		$this->options = $options;
	}

	//------------------------------------------------------------------------------------ afterWrite
	/**
	 * @param $object  object
	 * @param $options Option[]
	 */
	protected function afterWrite($object, array &$options)
	{
		/** @var $after_writes Method_Annotation[] */
		$after_writes = (new Reflection_Class(get_class($object)))->getAnnotations('after_write');
		foreach ($after_writes as $after_write) {
			if ($after_write->call($object, [$this->link, &$options]) === false) {
				break;
			}
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @param $object  object
	 * @param $options Option[]
	 * @return boolean
	 */
	protected function beforeWrite($object, array &$options)
	{
		/** @var $before_writes Method_Annotation[] */
		$before_writes = (new Reflection_Class(get_class($object)))->getAnnotations('before_write');
		if ($before_writes) {
			foreach ($options as $option) {
				if ($option instanceof Option\Only) {
					$only = $option;
					break;
				}
			}
			foreach ($before_writes as $before_write) {
				// TODO This is here for in-prod diagnostic. Please remove when done.
				if (!($before_write instanceof Method_Annotation)) {
					trigger_error(
						'Method_Annotation awaited ' . print_r($before_write, true) . LF
						. 'on object ' . print_r($object, true),
						E_USER_WARNING
					);
					$before_write = new Method_Annotation(
						$before_write->value, new Reflection_Class(get_class($object)), 'before_write'
					);
					trigger_error(
						'Try executing before_write ' . print_r($before_write, true), E_USER_WARNING
					);
				}
				$response = $before_write->call($object, [$this->link, &$options]);
				if ($response === false) {
					return false;
				}
				elseif (is_array($response) && isset($only)) {
					$only->properties = array_merge($response, $only->properties);
				}
			}
		}
		return true;
	}

}
