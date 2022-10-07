<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Checker\Report;
use ITRocks\Framework\Checker\Report\Line;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Null_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * This checks any business object class, using typing and others annotations and management rules
 *
 * @deprecated see Validator
 */
abstract class Checker
{

	//----------------------------------------------------------------------------------------- check
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return Report
	 */
	public static function check($object)
	{
		$check_report = new Report();
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($object))->getProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection $property from $object */
			$check_report->add(self::checkProperty($property, $property->getValue($object)));
		}
		return $check_report;
	}

	//--------------------------------------------------------------------------------- checkProperty
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return Line[]
	 */
	private static function checkProperty(Reflection_Property $property, $value)
	{
		$report_lines = [];
		if (is_null($value)) {
			if (!$property->getAnnotation('null')) {
				self::checkValue(
					$report_lines, false, $property, new Null_Annotation(false, $property), $value
				);
			}
		}
		else {
			foreach ($property->getAnnotations() as $annotation_name => $annotation) {
				switch ($annotation_name) {
					case 'min_length':
						self::checkValue(
							$report_lines, strlen($value) >= $annotation->value, $property, $annotation, $value
						);
						break;
					case 'max_length':
						self::checkValue(
							$report_lines, strlen($value) <= $annotation->value, $property, $annotation, $value
						);
						break;
					case 'min_value':
						self::checkValue(
							$report_lines, $value >= $annotation->value, $property, $annotation, $value
						);
						break;
					case 'max_value':
						self::checkValue(
							$report_lines, $value <= $annotation->value, $property, $annotation, $value
						);
						break;
					//case 'precision':
					//case 'signed':
					case 'var':
						self::checkVar($report_lines, $property, $annotation, $value);
						break;
				}
			}
		}
		return $report_lines;
	}

	//------------------------------------------------------------------------------------ checkValue
	/**
	 * @param $report_lines Line[]
	 * @param $test         boolean
	 * @param $property     Reflection_Property
	 * @param $annotation   Annotation
	 * @param $value        mixed
	 */
	private static function checkValue(
		array &$report_lines, $test, Reflection_Property $property, Annotation $annotation, $value
	) {
		if (!$test) {
			$report_line = new Line\Annotation($property, $annotation, $value);
			$report_lines[] = $report_line;
		}
	}

	//-------------------------------------------------------------------------------------- checkVar
	/**
	 * @param $report_lines Line[]
	 * @param $property     Reflection_Property
	 * @param $annotation   Annotation
	 * @param $value        mixed
	 */
	private static function checkVar(
		array &$report_lines, Reflection_Property $property, Annotation $annotation, $value
	) {
		switch ($annotation->value) {
			case 'array':
				self::checkValue($report_lines, is_array($value), $property, $annotation, $value);
				break;
			case 'boolean':
				self::checkValue($report_lines, is_bool($value), $property, $annotation, $value);
				break;
			case 'float':
				self::checkValue($report_lines, is_float($value), $property, $annotation, $value);
				break;
			case 'integer':
				self::checkValue($report_lines, is_integer($value), $property, $annotation, $value);
				break;
			case 'string':
				self::checkValue($report_lines, is_string($value), $property, $annotation, $value);
				break;
			default:
				$type = new Type($annotation->value);
				if ($type->isClass()) {
					self::checkValue(
						$report_lines, is_a($value, $type->asString(), true), $property, $annotation, $value
					);
				}
		}
	}

}
