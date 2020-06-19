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

	//----------------------------------------------------------------------------------- ANNOTATIONS
	const AFTER_CREATE  = 'after_create';
	const AFTER_UPDATE  = 'after_update';
	const AFTER_WRITE   = 'after_write';
	const BEFORE_CREATE = 'before_create';
	const BEFORE_UPDATE = 'before_update';
	const BEFORE_WRITE  = 'before_write';

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
	 * @param $after_write_annotation string @values after_create, after_update, after_write
	 */
	protected function afterWrite($object, array &$options, $after_write_annotation)
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		/** @var $after_writes Method_Annotation[] */
		$after_writes = $class->getAnnotations($after_write_annotation);
		if (in_array($after_write_annotation, [self::AFTER_CREATE, self::AFTER_UPDATE])) {
			$after_writes = array_merge($after_writes, $class->getAnnotations(self::AFTER_WRITE));
		}
		Method_Annotation::callAll($after_writes, $object, [$this->link, &$options]);
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object                  object
	 * @param $options                 Option[]
	 * @param $before_write_annotation string @values before_create, before_update, before_write,
	 *                                 before_writes
	 * @return boolean
	 */
	public function beforeWrite($object, array &$options, $before_write_annotation)
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		/** @var $before_writes Method_Annotation[] */
		$before_writes = $class->getAnnotations($before_write_annotation);
		if (in_array($before_write_annotation, [self::BEFORE_CREATE, self::BEFORE_UPDATE])) {
			$before_writes = array_merge($before_writes, $class->getAnnotations(self::BEFORE_WRITE));
		}
		if ($before_writes) {
			foreach ($options as $option) {
				if ($option instanceof Option\Only) {
					$only = $option;
					break;
				}
			}
			foreach ($before_writes as $before_write) {
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
		/** @noinspection PhpUnhandledExceptionInspection object */
		/** @var $after_commits Method_Annotation[] */
		$after_commits = (new Reflection_Class($object))->getAnnotations('after_commit');
		foreach ($after_commits as $after_commit) {
			$this->link->after_commit[] = new After_Action($after_commit, $object, $options);
		}
	}

}
