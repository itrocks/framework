<?php
namespace ITRocks\Framework\Dao\Mysql\Tests;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Console;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Dao\Mysql\Maintainer;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tests\Test;

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
	 * @see testEverything
	 * @throws Exception
	 */
	public function everythingProvider()
	{
		upgradeTimeLimit(0);
		$classes = [];
		$dao     = Dao::current();
		if ($dao instanceof Link) {
			/** @var $dependencies Dependency[] */
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
					&& $class->getAnnotation('business')->value
					&& !strpos($class->name, BS . 'Sub0')
					&& !strpos($class->name, BS . 'Tests' . BS)
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
	 * @param $class Reflection_Class
	 * @param $depth integer
	 * @return string[]
	 * @throws Exception
	 */
	private function propertyNames(Reflection_Class $class, $depth = 1)
	{
		$properties = Class_\Link_Annotation::of($class)->value
			? (new Link_Class($class->name))->getLocalProperties()
			: $class->getProperties([T_EXTENDS, T_USE]);
		/** @var $properties Reflection_Property[] */
		$properties = Replaces_Annotations::removeReplacedProperties($properties);
		foreach ($properties as $property) {
			$type  = $property->getType();
			$class = ($type->isClass() & !in_array($type->getElementTypeAsString(), ['object', 'static']))
				? $type->asReflectionClass()
				: null;
			if (
				$property->isStatic()
				|| (
					$property->getAnnotation(Store_Annotation::ANNOTATION)->value === Store_Annotation::FALSE
				)
				|| ($class && ($class->isAbstract() || ($class->name === 'object')))
			) {
				unset($properties[$property->name]);
			}
		}
		if ($depth) {
			foreach ($properties as $property) {
				$type = $property->getType();
				if (
					$type->isClass()
					&& Link_Annotation::of($property)->value
					&& !$property->getAnnotation(Store_Annotation::ANNOTATION)->value
				) {
					$sub_class = new Reflection_Class($type->getElementTypeAsString());
					foreach ($this->propertyNames($sub_class, $depth - 1) as $sub_property_name) {
						$properties[$property->name . DOT . $sub_property_name] = true;
					}
				}
			}
		}
		return array_keys($properties);
	}

	//-------------------------------------------------------------------------------- testEverything
	/**
	 * Tests Explain on every object (split to property for large object)
	 *
	 * @dataProvider everythingProvider
	 * @param        $query   string
	 * @param        $builder Select
	 */
	public function testEverything($query, Select $builder)
	{
		/** @var $dao Link */
		$dao                      = Dao::current();
		Maintainer::get()->notice = false;
		$dao->setContext($builder->getJoins()->getClassNames());
		$dao->query($query);

		// Failure will be SQL Exception
		static::assertTrue(true);
	}

}
