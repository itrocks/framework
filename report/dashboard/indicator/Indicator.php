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
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Unit;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Widget;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Report\Dashboard;
use ITRocks\Framework\Report\Dashboard\Indicator\Property_Path;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ReflectionException;

/**
 * The dashboard indicator
 *
 * @feature
 * @feature move
 */
#[Representative('setting.code'), Store('dashboard_indicators')]
class Indicator
{
	use Component;

	//----------------------------------------------------------------------------------------- COUNT
	const COUNT = '@count';

	//------------------------------------------------------------------------------------ GRID_WIDTH
	const GRID_WIDTH = 6;

	//------------------------------------------------------------------------------------ $dashboard
	#[Composite]
	public Dashboard $dashboard;

	//--------------------------------------------------------------------------------------- $grid_x
	/** horizontal coordinate on the dashboard grid, from 0 to 5 */
	#[User(User::INVISIBLE)]
	public int $grid_x;

	//--------------------------------------------------------------------------------------- $grid_y
	/** vertical coordinate on the dashboard grid, from 0 to n */
	#[User(User::INVISIBLE)]
	public int $grid_y;

	//----------------------------------------------------------------------------------------- $icon
	public ?File $icon = null;

	//-------------------------------------------------------------------------------- $property_path
	/** The indicator property path. Can also be '@count' for an objects counter */
	#[Widget(Property_Path\Widget::class)]
	public string $property_path = self::COUNT;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * An indicator is linked to a list setting : when you click it, you go there.
	 * The first way to create an indicator on the current home dashboard is to drag a custom list
	 * setting and drop it into the 'home-page' icon that appears.
	 */
	public Setting $setting;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Setting $setting = null, Dashboard $dashboard = null)
	{
		if (isset($setting)) {
			$this->setting = $setting;
		}
		if (isset($dashboard)) {
			$this->dashboard = $dashboard;
		}
		elseif (isset($this->dashboard)) {
			return;
		}
		$this->dashboard = Dashboard::current();
		if (Dao::getObjectIdentifier($this->dashboard)) {
			return;
		}
		$dao = Dao::current();
		if ($dao instanceof Identifier_Map) {
			$dao->setObjectIdentifier($this->dashboard, 1);
		}
		Dao::write($this->dashboard, Dao::add());
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->setting->value->name;
	}

	//-------------------------------------------------------------------------------- formattedValue
	/**
	 * Calculates and returns the formatted value of the indicator
	 *
	 * The value is formatted using locales, and appended with the property #Unit, if it is the same
	 * for all read values
	 */
	public function formattedValue() : string
	{
		return $this->value(true);
	}

	//------------------------------------------------------------------------------------------ link
	/** A link to the target objects list */
	public function link() : string
	{
		return View::link(
			Names::classToSet($this->setting->value->class_name),
			Feature::F_LIST,
			null,
			['load_name' => $this->setting->value->name]
		);
	}

	//---------------------------------------------------------------------------------------- moveTo
	/**
	 * Move the indicator to this destination on the grid
	 * - If already contains an indicator : exchange places
	 */
	public function moveTo(int $grid_x, int $grid_y) : void
	{
		$grid = $this->dashboard->fullHeightGrid();
		if ($swap_indicator = $grid[$grid_y][$grid_x]) {
			$swap_indicator->grid_x = $this->grid_x;
			$swap_indicator->grid_y = $this->grid_y;
			Dao::write($swap_indicator, Dao::only('grid_x', 'grid_y'));
		}
		$this->grid_x = $grid_x;
		$this->grid_y = $grid_y;
		Dao::write($this, Dao::only('grid_x', 'grid_y'));
	}

	//----------------------------------------------------------------------------------- placeOnGrid
	/** Places the current indicator into the dashboard grid, at the first available place */
	public function placeOnGrid() : void
	{
		$grid = $this->dashboard->grid();
		foreach ($grid as $grid_y => $row) {
			foreach ($row as $grid_x => $indicator) {
				if (!$indicator) {
					$this->grid_x = $grid_x;
					$this->grid_y = $grid_y;
					return;
				}
			}
		}
		$this->grid_x = 0;
		$this->grid_y = count($grid);
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
			? Representative::of($setting->getClass())->values
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
		$unit_attribute = ($format && $property && !str_contains($this->property_path, DOT))
			? Unit::of($property)
			: null;
		$last_unit = null;
		$result    = .0;
		$unit      = null;
		foreach ($data->getRows() as $row) {
			if ($unit_attribute) {
				$unit = $unit_attribute->call($row->getObject());
				if (isset($last_unit) && ($unit !== $last_unit)) {
					$unit = $unit_attribute = null;
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
