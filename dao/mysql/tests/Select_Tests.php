<?php
namespace SAF\Framework\Dao\Mysql\Tests;

use Exception;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\PHP\Dependency;
use SAF\Framework\Reflection\Annotation\Property\Store_Annotation;
use SAF\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use SAF\Framework\Reflection\Link_Class;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Sql\Builder\Select;
use SAF\Framework\Tests\Test;

/**
 * Mysql select tests for all cases of joins
 */
class Select_Tests extends Test
{

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
				['type' => Dependency::T_DECLARATION, 'declaration' => [Dependency::T_CLASS]],
				Dependency::class
			);
			foreach ($dependencies as $dependency) {
				$class = new Reflection_Class($dependency->class_name);
				echo '- ' . $class->name . BR;
				if (!$class->isAbstract() && $class->getAnnotation('business')->value) {
					$properties = $this->propertyNames($class, $depth - 1);
					$builder    = new Select($class->name, $properties);
					$query      = 'EXPLAIN ' . $builder->buildQuery();
					try {
						$dao->query($query);
						$this->assume($class->name, 'works', 'works');
					}
					catch (Exception $exception) {
						$this->assume(
							$class->name, $dao->getConnection()->last_error . PRE . $query . _PRE, 'works', false
						);
						flush(); ob_flush();
					}
				}
			}
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
			echo '~ ' . $property->name;
			$type  = $property->getType();
			$class = ($type->isClass() && ($type->getElementTypeAsString() !== 'object'))
				? $type->asReflectionClass()
				: null;
			if (
				$property->isStatic()
				|| (
					$property->getAnnotation(Store_Annotation::ANNOTATION)->value === Store_Annotation::FALSE
				)
				|| (
					$class
					&& (
						$class->isAbstract() || $class->isInterface() || ($class->name === 'object')
					)
				)
			) {
				echo ' UNSET';
				unset($properties[$property->name]);
			}
			echo BR;
		}
		if ($depth) {
			foreach ($properties as $property) {
				$type = $property->getType();
				if ($type->isClass()) {
					$sub_class = new Reflection_Class($type->getElementTypeAsString());
					foreach ($this->propertyNames($sub_class, $depth - 1) as $sub_property_name) {
						$properties[$property->name . DOT . $sub_property_name] = true;
					}
				}
			}
		}
		echo 'OK' . BR;
		return array_keys($properties);
	}

	//-------------------------------------------------------------------------- testEverythingDepth1
	/**
	 * Test searches of everything that has depth 1
	 */
	public function testEverythingDepth1()
	{
		$this->everything(1);
	}

}
