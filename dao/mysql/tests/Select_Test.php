<?php
namespace ITRocks\Framework\Dao\Mysql\Tests;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Console;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao\Mysql\Maintainer;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tests\Test;
use ReflectionException;

/**
 * Mysql select tests for all cases of joins
 *
 * @group functional
 */
class Select_Test extends Test
{

	//------------------------------------------------------------------------- MAX_TABLE_PER_REQUEST
	const MAX_TABLE_PER_REQUEST = 60;

	//---------------------------------------------------------------------------- everythingProvider
	/**
	 * Gets all object business object
	 *
	 * @return array[] test_name => [ string $query, Select $builder ]
	 * @see    testEverything
	 * @throws ReflectionException
	 */
	public function everythingProvider() : array
	{
		upgradeTimeLimit(0);
		$classes = [];
		$dao     = Dao::current();
		if ($dao instanceof Mysql\Link) {
			$dependencies = Dao::search(
				[
					'class_name'  => Func::notOp(Console::class),
					'declaration' => [Dependency::T_CLASS],
					'file_name'   => Func::notOp('%/' . Compiler::getCacheDir() . '/%'),
					'type'        => Dependency::T_DECLARATION
				],
				Dependency::class
			);
			foreach ($dependencies as $dependency) {
				$class = new Reflection_Class(Builder::className($dependency->class_name));
				if (
					!$class->isAbstract()
					&& ($class->getAnnotation('business')->value || $class->getAnnotation('stored')->value)
					&& !str_contains($class->name, BS . 'Sub0')
					&& !str_contains($class->name, BS . 'Tests' . BS)
					&& $this->testConditions($class)
				) {
					$properties = $this->propertyNames($class);
					$builder    = new Select($class->name, $properties);
					$query      = 'EXPLAIN ' . $builder->buildQuery();
					if (substr_count($query, 'JOIN') <= static::MAX_TABLE_PER_REQUEST) {
						$classes[$class->name] = [$query, $builder];
					}
					else {
						foreach ($properties as $property) {
							$name           = $class->name . '::' . $property;
							$builder        = new Select($class->name, [$property]);
							$classes[$name] = ['EXPLAIN ' . $builder->buildQuery(), $builder];
						}
					}
				}
			}
		}
		return $classes;
	}

	//--------------------------------------------------------------------------------- propertyNames
	/**
	 * @param  $class Reflection_Class
	 * @param  $depth integer
	 * @return string[]
	 * @throws ReflectionException
	 */
	private function propertyNames(Reflection_Class $class, int $depth = 1) : array
	{
		$properties = Class_\Link_Annotation::of($class)->value
			? (new Link_Class($class->name))->getLocalProperties()
			: $class->getProperties([T_EXTENDS, T_USE]);
		/** @var $properties Reflection_Property[] */
		$properties = Replaces_Annotations::removeReplacedProperties($properties);
		foreach ($properties as $property) {
			$type        = $property->getType();
			$type_string = $type->getElementTypeAsString();
			$class       = ($type->isClass() && !in_array($type_string, ['object', 'static'], true))
				? $type->asReflectionClass()
				: null;
			if (
				$property->isStatic()
				|| ($type_string === 'object')
				|| ($class && $class->isAbstract())
				|| Store_Annotation::of($property)->isFalse()
			) {
				unset($properties[$property->name]);
			}
		}
		if ($depth) {
			foreach ($properties as $property) {
				$type        = $property->getType();
				$type_string = $type->getElementTypeAsString();
				if (
					$type->isClass()
					&& ($type_string !== 'object')
					&& Link_Annotation::of($property)->value
					&& !$property->getAnnotation(Store_Annotation::ANNOTATION)->value
				) {
					$sub_class = new Reflection_Class($type_string);
					foreach ($this->propertyNames($sub_class, $depth - 1) as $sub_property_name) {
						$properties[$property->name . DOT . $sub_property_name] = true;
					}
				}
			}
		}
		return array_keys($properties);
	}

	//-------------------------------------------------------------------------------- testConditions
	/**
	 * @param $class Reflection_Class
	 * @return boolean
	 */
	protected function testConditions(Reflection_Class $class) : bool
	{
		/** @var $annotation Method_Annotation */
		foreach ($class->getAnnotations('test_condition') as $annotation) {
			if (!$annotation->call(null)) {
				return false;
			}
		}
		return true;
	}

	//-------------------------------------------------------------------------------- testEverything
	/**
	 * Tests Explain on every object (split to property for large object)
	 *
	 * @dataProvider everythingProvider
	 * @param        $query   string
	 * @param        $builder Select
	 */
	public function testEverything(string $query, Select $builder) : void
	{
		/** @var $dao Mysql\Link */
		$dao                      = Dao::current();
		Maintainer::get()->notice = '';
		$mysqli                   = $dao->getConnection();
		$mysqli->contexts[]       = $builder->getJoins()->getClassNames();
		$dao->query($query);
		array_pop($mysqli->contexts);

		// Failure will be SQL Exception
		static::assertTrue(true);
	}

}
