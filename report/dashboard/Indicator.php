<?php
namespace ITRocks\Framework\Report\Dashboard;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\List_\Selection;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Report\Dashboard;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ReflectionException;

/**
 * The dashboard indicator
 *
 * @representative setting.code
 * @store_name dashboard_indicators
 */
class Indicator
{
	use Component;

	//----------------------------------------------------------------------------------------- COUNT
	const COUNT = '@count';

	//------------------------------------------------------------------------------------ $dashboard
	/**
	 * @composite
	 * @link Object
	 * @mandatory
	 * @var Dashboard
	 */
	public $dashboard;

	//----------------------------------------------------------------------------------------- $icon
	/**
	 * @link Object
	 * @var File|null
	 */
	public $icon = null;

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The indicator property path. Can also be '@count' for an objects counter
	 *
	 * @var string
	 */
	public $property_path = self::COUNT;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * An indicator is linked to a list setting : when you click it, you go there.
	 * The first way to create an indicator on the current home dashboard is to drag a custom list
	 * setting and drop it into the 'home-page' icon that appears.
	 *
	 * @link Object
	 * @mandatory
	 * @var Setting
	 */
	public $setting;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $setting   Setting|null
	 * @param $dashboard Dashboard|null
	 */
	public function __construct(Setting $setting = null, Dashboard $dashboard = null)
	{
		if (isset($setting)) {
			$this->setting = $setting;
		}
		if (isset($dashboard)) {
			$this->dashboard = $dashboard;
		}
		elseif (!isset($this->dashboard)) {
			$this->dashboard = Dashboard::current();
			if (!Dao::getObjectIdentifier($this->dashboard)) {
				$dao = Dao::current();
				if ($dao instanceof Identifier_Map) {
					$dao->setObjectIdentifier($this->dashboard, 1);
				}
				Dao::write($this->dashboard, Dao::add());
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->setting
			? $this->setting->value->name
			: Loc::tr('new $1', Loc::replace([1 => Loc::tr('indicator')]));
	}

	//-------------------------------------------------------------------------------- formattedValue
	/**
	 * Calculates and returns the formatted value of the indicator
	 *
	 * The value is formatted using locales, and appended with the property @unit, if it is the same
	 * for all read values
	 *
	 * @return string
	 */
	public function formattedValue() : string
	{
		return $this->value(true);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * A link to the target objects list
	 *
	 * @return string
	 */
	public function link() : string
	{
		return View::link(
			Names::classToSet($this->setting->value->class_name),
			Feature::F_LIST,
			null,
			['load_name' => $this->setting->value->name]
		);
	}
	
	//----------------------------------------------------------------------------------------- value
	/**
	 * Calculates and returns the value of the indicator
	 *
	 * @param $format boolean if true, the value is formatted using locales and property unit
	 * @return float|string
	 */
	public function value(bool $format = false) : float|string
	{
		/** @var $setting List_Setting\Set */
		$setting   = $this->setting->value;
		$selection = new Selection($setting->class_name);
		$selection->allFromListSettings($setting);

		$options        = [Dao::groupBy(lParse($this->property_path, DOT, 1, false) ?: [])];
		$property_paths = ($this->property_path === '@count')
			? Representative_Annotation::of($setting->getClass())->values()
			: [$this->property_path];
		$data = $selection->readDataSelect($property_paths, null, $options);

		if ($this->property_path === '@count') {
			return count($data->getRows());
		}

		try {
			$property = new Reflection_Property(Builder::className($setting->class_name), $this->property_path);
		}
		catch (ReflectionException) {
			$property = null;
		}
		/** @var $unit_annotation ?Constant_Or_Method_Annotation */
		$unit_annotation = ($format && $property && !str_contains($this->property_path, DOT))
			? $property->getAnnotation('unit')
			: null;
		$last_unit = null;
		$result    = .0;
		$unit      = null;
		foreach ($data->getRows() as $row) {
			if ($unit_annotation) {
				$unit = $unit_annotation->call($row->getObject());
				if (isset($last_unit) && ($unit !== $last_unit)) {
					$unit = $unit_annotation = null;
				}
			}
			$result += $row->getValue($this->property_path);
		}
		if ($format && $property) {
			$result = Loc::propertyToLocale($property, $result);
			if (isset($unit)) {
				$result .= SP . $unit;
			}
		}

		return $result;
	}

}
