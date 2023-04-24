<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ReflectionMethod;

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
	protected Identifier_Map $link;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var ?object
	 */
	public ?object $object;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var Option[]
	 */
	protected array $options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Write constructor
	 *
	 * @param $link    Identifier_Map
	 * @param $object  ?object
	 * @param $options Option[]
	 */
	public function __construct(Identifier_Map $link, ?object $object, array $options)
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
	protected function afterWrite(object $object, array &$options, string $after_write_annotation)
		: void
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		/** @var $after_writes Method_Annotation[] */
		$after_writes = $class->getAnnotations($after_write_annotation);
		if (in_array($after_write_annotation, [self::AFTER_CREATE, self::AFTER_UPDATE], true)) {
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
	public function beforeWrite(object $object, array &$options, string $before_write_annotation)
		: bool
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		/** @var $before_writes Method_Annotation[] */
		$before_writes = $class->getAnnotations($before_write_annotation);
		if (in_array($before_write_annotation, [self::BEFORE_CREATE, self::BEFORE_UPDATE], true)) {
			$before_writes = array_merge($before_writes, $class->getAnnotations(self::BEFORE_WRITE));
		}
		if (!$before_writes) {
			return true;
		}
		foreach ($options as $option) {
			if ($option instanceof Option\Only) {
				$only = $option;
				break;
			}
		}
		foreach ($before_writes as $before_write) {
			$arguments  = [];
			$callable   = is_string($before_write->value)
				? explode('::', $before_write->value)
				: $before_write->value;
			$method     = new ReflectionMethod($callable[0], $callable[1]);
			$parameters = $method->getParameters();
			if ($parameters) {
				if (is_a($parameters[0]->getType()->getName(), Data_Link::class, true)) {
					$arguments[] = $this->link;
					if (isset($parameters[1]) && ($parameters[1]->getType()->getName() === 'array')) {
						$arguments[] = &$options;
					}
				}
			}
			$response = $before_write->call($object, $arguments);
			if ($response === false) {
				return false;
			}
			elseif (is_array($response) && isset($only)) {
				$only->add($response);
			}
		}
		return true;
	}

	//------------------------------------------------------------------------- beforeWriteComponents
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object                  object
	 * @param $options                 Option[]
	 * @param $before_write_annotation string @values before_create, before_update, before_write,
	 *                                 before_writes
	 */
	public function beforeWriteComponents(
		object $object, array $options, string $before_write_annotation
	) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($object))->getProperties() as $property) {
			if (
				!$property->getAnnotation('component')->value
				&& !Link_Annotation::of($property)->isCollection()
			) {
				continue;
			}
			/** @var $value Component */
			if ($property->getType()->isMultiple()) {
				/** @noinspection PhpUnhandledExceptionInspection */
				foreach ($property->getValue($object) as $value) {
					$value->setComposite($object);
					$this->beforeWrite($value, $options, $before_write_annotation);
					$this->beforeWriteComponents($value, $options, $before_write_annotation);
				}
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection */
				$value = $property->getValue($object);
				$value->setComposite($object);
				$this->beforeWrite($value, $options, $before_write_annotation);
				$this->beforeWriteComponents($value, $options, $before_write_annotation);
			}
		}
	}

	//---------------------------------------------------------------------------- prepareAfterCommit
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object  object
	 * @param $options Option[]
	 */
	protected function prepareAfterCommit(object $object, array $options) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		/** @var $after_commits Method_Annotation[] */
		$after_commits = (new Reflection_Class($object))->getAnnotations('after_commit');
		foreach ($after_commits as $after_commit) {
			$this->link->after_commit[] = new After_Action($after_commit, $object, $options);
		}
	}

}
