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
use PHPUnit_Exception;

/**
 * Mysql select tests for all cases of joins
 */
class Select_Test extends Test
{

	//------------------------------------------------------------------------------------ everything
	/**
	 * Test searches on everything with a depth of 1
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
					&& !strpos($class->name, BS . 'Tests' . BS)
				) {
					$this->perClass($class, $depth);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- perClass
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 * @throws Exception
	 */
	private function perClass(Reflection_Class $class, $depth)
	{
		/** @var $dao Link */
		$dao        = Dao::current();
		$errors     = [];
		$properties = $this->propertyNames($class, $depth - 1);
		$builder    = new Select($class->name, $properties);
		$query      = 'EXPLAIN ' . $builder->buildQuery();
		if (!strpos($query, '.`id_`')) {
			try {
				Maintainer::get()->notice = false;
				$dao->setContext($builder->getJoins()->getClassNames());
				$dao->query($query);
			}
			catch (PHPUnit_Exception $exception) {
				throw $exception;
			}
			catch (Exception $exception) {
				if ($dao->getConnection()->last_errno == Dao\Mysql\Errors::ER_TOO_MANY_TABLES) {
					$errors = array_merge($errors,$this->perProperty($class, $depth));
				}
				else {
					$errors[] = $query;
				}
				flush();
				ob_flush();
			}
		}
		$this->assertEquals([], $errors, $class->name);
	}

	//----------------------------------------------------------------------------------- perProperty
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 * @return array
	 * @throws Exception
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
				catch (PHPUnit_Exception $exception) {
					throw $exception;
				}
				catch (Exception $exception) {
					$errors[] = $query;
				}
			}
		}

		return $errors;
	}

	//--------------------------------------------------------------------------------- propertyNames
	/**
	 * @param $class Reflection_Class
	 * @param $depth integer
	 * @return string[]
	 */
	private function propertyNames(Reflection_Class $class, $depth)
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
