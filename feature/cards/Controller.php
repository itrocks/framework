<?php
namespace ITRocks\Framework\Feature\Cards;

use ITRocks\Framework\Component\Button\Has_Selection_Buttons;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Columns_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Display_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Edit_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Groups_Annotation;
use ITRocks\Framework\Feature\Cards\Annotation\Card_Sums_Annotation;
use ITRocks\Framework\Feature\Cards\Property\Card;
use ITRocks\Framework\Feature\Cards\Property\Color;
use ITRocks\Framework\Feature\Cards\Property\Column;
use ITRocks\Framework\Feature\Cards\Property\Edit;
use ITRocks\Framework\Feature\Cards\Property\Group;
use ITRocks\Framework\Feature\Cards\Property\Sum;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Cards controller
 */
class Controller extends List_\Controller implements Has_Selection_Buttons
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = Feature::F_CARDS;

	//---------------------------------------------------------------------------------- $card_colors
	/**
	 * @var Color[]
	 */
	public array $card_colors;

	//------------------------------------------------------------------------------ $card_properties
	/**
	 * @var Card[]
	 */
	public array $card_properties;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public Reflection_Class $class;

	//---------------------------------------------------------------------------- $column_properties
	/**
	 * @var Column[]
	 */
	public array $column_properties;

	//------------------------------------------------------------------------------ $edit_properties
	/**
	 * @var Edit[]
	 */
	public array $edit_properties;

	//----------------------------------------------------------------------------- $group_properties
	/**
	 * @var Group[]
	 */
	public array $group_properties;

	//------------------------------------------------------------------------------- $sum_properties
	/**
	 * @var Sum[]
	 */
	public array $sum_properties;

	//----------------------------------------------------------------------------- getCardProperties
	/**
	 * Get properties rules for cards
	 */
	protected function getCardProperties() : void
	{
		$this->card_properties   = Card_Display_Annotation::of($this->class)->properties();
		$this->column_properties = Card_Columns_Annotation::of($this->class)->properties();
		$this->edit_properties   = Card_Edit_Annotation::of($this->class)->properties();
		$this->group_properties  = Card_Groups_Annotation::of($this->class)->properties();
		$this->sum_properties    = Card_Sums_Annotation::of($this->class)->properties();
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return array
	 */
	public function getViewParameters(Parameters $parameters, array $form, string $class_name)
	: array
	{
		/** @noinspection PhpUnhandledExceptionInspection Main set object is not read itself */
		$this->class = new Reflection_Class($parameters->getMainObject()->element_class_name);
		$parameters  = parent::getViewParameters($parameters, $form, $class_name);
		$this->getCardProperties();
		return $parameters;
	}

}
