<?php
namespace SAF\Framework;

/**
 * This checks any business object class, using typing and others annotations and management rules
 */
abstract class Checker
{

	//----------------------------------------------------------------------------------------- check
	/**
	 * @param $object object
	 * @return Check_Report
	 */
	public static function check($object)
	{
		$check_report = new Check_Report();
		$class = Reflection_Class::getInstanceOf($object);
		foreach ($class->accessProperties() as $property) {
			$check_report->add(self::checkProperty($property, $property->getValue($object)));
		}
		$class->accessPropertiesDone();
		return $check_report;
	}

	//--------------------------------------------------------------------------------- checkProperty
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return Check_Report_Line[]
	 */
	private static function checkProperty(Reflection_Property $property, $value)
	{
		$report_lines = array();
		if (is_null($value)) {
			if (!$property->getAnnotation("null")) {
				self::checkValue(
					$report_lines, false, $property, new Null_Annotation(false, $property), $value
				);
			}
		}
		else {
			foreach ($property->getAnnotations() as $annotation_name => $annotation) {
				switch ($annotation_name) {
					case "min_length":
						self::checkValue(
							$report_lines, strlen($value) >= $annotation->value, $property, $annotation, $value
						);
						break;
					case "max_length":
						self::checkValue(
							$report_lines, strlen($value) <= $annotation->value, $property, $annotation, $value
						);
						break;
					case "min_value":
						self::checkValue(
							$report_lines, $value >= $annotation->value, $property, $annotation, $value
						);
						break;
					case "max_value":
						self::checkValue(
							$report_lines, $value <= $annotation->value, $property, $annotation, $value
						);
						break;
					//case "precision":
					//case "signed":
					case "var":
						self::checkVar($report_lines, $property, $annotation, $value);
						break;
				}
			}
		}
		return $report_lines;
	}

	//-------------------------------------------------------------------------------------- checkVar
	/**
	 * @param $report_lines Check_Report_Line[]
	 * @param $property     Reflection_Property
	 * @param $annotation   Annotation
	 * @param $value        mixed
	 */
	private static function checkVar(
		&$report_lines, Reflection_Property $property, Annotation $annotation, $value
	) {
		switch ($annotation->value) {
			case "array":
				self::checkValue($report_lines, is_array($value), $property, $annotation, $value);
				break;
			case "boolean":
				self::checkValue($report_lines, is_bool($value), $property, $annotation, $value);
				break;
			case "float":
				self::checkValue($report_lines, is_float($value), $property, $annotation, $value);
				break;
			case "integer":
				self::checkValue($report_lines, is_integer($value), $property, $annotation, $value);
				break;
			case "string":
				self::checkValue($report_lines, is_string($value), $property, $annotation, $value);
				break;
			default:
				$type = new Type($annotation->value);
				if ($type->isClass()) {
					self::checkValue(
						$report_lines, is_a($value, $type->asString()), $property, $annotation, $value
					);
				}
		}
	}

	//------------------------------------------------------------------------------------ checkValue
	/**
	 * @param $report_lines Check_Report_Line[]
	 * @param $test         boolean
	 * @param $property     Reflection_Property
	 * @param $annotation   Annotation
	 * @param $value        mixed
	 */
	private static function checkValue(
		&$report_lines, $test, Reflection_Property $property, Annotation $annotation, $value
	) {
		if (!$test) {
			$report_line = new Annotation_Check_Report_Line($property, $annotation, $value);
			$report_lines[] = $report_line;
		}
	}

}
