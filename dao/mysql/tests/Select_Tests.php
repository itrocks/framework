<?php
namespace ITRocks\Framework\Dao\Mysql\Tests;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Console;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\PHP\Dependency;
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
 */
class Select_Tests extends Test
{

	//-------------------------------------------------------------------------------------- perClass
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 */
	private function perClass(Reflection_Class $class, $depth)
	{
		/** @var $dao Link */
		$dao        = Dao::current();
		$properties = $this->propertyNames($class, $depth - 1);
		$builder    = new Select($class->name, $properties);
		$query      = 'EXPLAIN ' . $builder->buildQuery();
		if (!strpos($query, '.id_ ')) {
			try {
				$dao->setContext();
				$dao->query($query);
				$this->assume($class->name, 'works', 'works');
			}
			catch (Exception $exception) {
				if (beginsWith($dao->getConnection()->last_error, ('Too many tables'))) {
					$this->perProperty($class, $depth);
				}
				else {
					$this->assume(
						$class->name, $dao->getConnection()->last_error . PRE . $query . _PRE, 'works',
						false
					);
				}
				flush(); ob_flush();
			}
		}
	}

	//------------------------------------------------------------------------------------ everything
	/**
	 * Test searches on eveything with a depth of 1
	 *
	 * @param $depth integer
	 */
	private function everything($depth)
	{
		$dao = Dao::current();
		if ($dao instanceof Link) {
			/** @var $dependencies Dependency[] */
			$dependencies = Dao::search(
				[
					'class_name'  => Func::notOp(Console::class),
					'declaration' => [Dependency::T_CLASS],
					'file_name'   => Func::notOp('%/cache/compiled/%'),
					'type'        => Dependency::T_DECLARATION
				],
				Dependency::class
			);
			foreach ($dependencies as $dependency) {
				$class = new Reflection_Class(Builder::className($dependency->class_name));
				if (
					!$class->isAbstract()
					&& $class->getAnnotation('business')->value
					&& !strpos($class->name, BS . 'Tests' . BS)
				) {
					$this->perClass($class, $depth);
				}
				/*
				elseif (
					!$class->isAbstract()
					&& !$class->getAnnotation('business')->value
					&& !strpos($class->name, BS . 'Tests' . BS)
					&& $dao->getConnection()->exists(Dao::storeNameOf($class->name))
				) {
					echo '! PLEASE CHECK IF ' . $class->name . ' SHOULD BE @business' . BR;
				}
				*/
			}
		}
	}

	//----------------------------------------------------------------------------------- perProperty
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 */
	private function perProperty(Reflection_Class $class, $depth)
	{
		/** @var $dao Link */
		$dao        = Dao::current();
		$errors     = [];
		$properties = $this->propertyNames($class, $depth - 1);
		foreach ($properties as $property) {
			$builder = new Select($class->name, [$property]);
			$query   = 'EXPLAIN ' . $builder->buildQuery();
			if (!strpos($query, '.id_ ')) {
				try {
					$dao->setContext();
					$dao->query($query);
				}
				catch (Exception $exception) {
					$errors[] = $query;
				}
			}
		}
		if ($errors) {
			$this->assume(
				$class->name,
				$dao->getConnection()->last_error . PRE . print_r($errors, true) . _PRE,
				'works',
				false
			);
		}
		else {
			$this->assume($class->name, 'works', 'works');
		}
	}

	//--------------------------------------------------------------------------------- propertyNames
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 * @return string[]
	 */
	private function propertyNames(Reflection_Class $class, $depth)
	{
		$properties = $class->getAnnotation('link')->value
			? (new Link_Class($class->name))->getLocalProperties()
			: $class->getProperties([T_EXTENDS, T_USE]);
		$properties = Replaces_Annotations::removeReplacedProperties($properties);
		foreach ($properties as $property) {
			/** @var $property Reflection_Property */
			$type  = $property->getType();
			$class = ($type->isClass() && ($type->getElementTypeAsString() !== 'object'))
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
					&& $property->getAnnotation(Link_Annotation::ANNOTATION)->value
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

	//-------------------------------------------------------------------------- testEverythingDepth2
	/**
	 * Test searches of everything that has depth 2
	 */
	public function testEverythingDepth2()
	{
		$this->method(__METHOD__);
		$this->everything(2);
	}

}
