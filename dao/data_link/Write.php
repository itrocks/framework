<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ReflectionException;

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
	public $object;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object                 object
	 * @param $options                Option[]
	 * @param $after_write_annotation string @values after_create, after_write
	 * @throws ReflectionException
	 */
	protected function afterWrite($object, array &$options, $after_write_annotation)
	{
		$reflexion_class = (new Reflection_Class(get_class($object)));
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		/** @var $after_writes Method_Annotation[] */
		$after_writes = $reflexion_class->getAnnotations($after_write_annotation);
		if ($after_write_annotation === 'after_create') {
			$after_writes = array_merge($after_writes, $reflexion_class->getAnnotations('after_write'));
		}
		foreach ($after_writes as $after_write) {
			if ($after_write->call($object, [$this->link, &$options]) === false) {
				break;
			}
		}
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object                   object
	 * @param $options                  Option[]
	 * @param $before_write_annotation string @values before_create, before_write, before_writes
	 * @return boolean
	 */
	public function beforeWrite($object, array &$options, $before_write_annotation = 'before_write')
	{
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		$class = new Reflection_Class(get_class($object));
		/** @var $before_writes Method_Annotation[] */
		$before_writes = $class->getAnnotations($before_write_annotation);
		if ($before_write_annotation === 'before_create') {
			$before_writes = array_merge($before_writes, $class->getAnnotations('before_write'));
		}
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
					/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
					$before_write = new Method_Annotation(
						$before_write->value, new Reflection_Class(get_class($object)), $before_write_annotation
					);
					trigger_error(
						'Try executing @' . $before_write_annotation
						. SP . print_r($before_write, true), E_USER_WARNING
					);
				}
				$response = $before_write->call($object, [$this->link, &$options]);
				if ($response === false) {
					return false;
				}
				elseif (is_array($response) && isset($only)) {
					$only->add($response);
				}
			}
		}
		return true;
	}

	//---------------------------------------------------------------------------- prepareAfterCommit
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object  object
	 * @param $options Option[]
	 */
	protected function prepareAfterCommit($object, array $options)
	{
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		/** @var $after_commits Method_Annotation[] */
		$after_commits = (new Reflection_Class(get_class($object)))->getAnnotations('after_commit');
		foreach ($after_commits as $after_commit) {
			$this->link->after_commit[] = new After_Action($after_commit, $object, $options);
		}
	}

}
