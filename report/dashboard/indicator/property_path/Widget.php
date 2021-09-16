<?php
namespace ITRocks\Framework\Report\Dashboard\Indicator\Property_Path;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Report\Dashboard\Indicator;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Dom\Div;
use ITRocks\Framework\View\Html\Template;
use ReflectionException;

/**
 * Indicator property path widget
 */
class Widget extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function buildHtml() : string
	{
		$feature = $this->template->getFeature();
		if ($feature !== Feature::F_EDIT) {
			try {
				$value = Names::propertyToDisplay($this->indicatorClass()->getProperty($this->value));
			}
			catch (ReflectionException) {
				$value = 'item count';
			}
			return new Div(Loc::tr($value));
		}
		$parameters = array_merge($this->parameters, [
			Parameter::AS_WIDGET   => true,
			Parameter::IS_INCLUDED => true,
			'values'               => $this->values()
		]);
		$template_file = __DIR__ . SL . 'edit.html';
		/** @noinspection PhpUnhandledExceptionInspection class */
		$template = Builder::create(Template::class, [$this->value, $template_file, $feature]);
		$template->setParameters($parameters);
		return $template->parse();
	}

	//------------------------------------------------------------------------------------- indicator
	/**
	 * @return Indicator
	 */
	public function indicator() : Indicator
	{
		$indicator = null;
		foreach ($this->template->objects as $object) {
			if ($object instanceof Indicator) {
				$indicator = $object;
				break;
			}
		}
		return $indicator;
	}

	//-------------------------------------------------------------------------------- indicatorClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function indicatorClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection must be valid*/
		return new Reflection_Class(Builder::className($this->indicator()->setting->value->class_name));
	}

	//---------------------------------------------------------------------------------------- values
	/**
	 * @return Value[]
	 */
	public function values() : array
	{
		$values = [new Value(Indicator::COUNT, 'item count', $this->value === Indicator::COUNT)];
		foreach ($this->indicatorClass()->getProperties() as $property) {
			$type = $property->getType();
			if ($type->isNumeric()) {
				$values[] = new Value(
					$property->path,
					Names::propertyToDisplay($property->path),
					$this->value === $property->path
				);
			}
		}
		return $values;
	}

}
