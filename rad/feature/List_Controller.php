<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\No_General_Buttons;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\List_Data;

/**
 * RAD feature list controller
 */
class List_Controller extends List_\Controller
{
	use No_General_Buttons;

	//--------------------------------------------------------------------------- getSelectionButtons
	/**
	 * @param $class_name    string class name
	 * @param $parameters    string[] parameters
	 * @param $list_settings Setting\Custom\Set|List_Setting\Set
	 * @return Button[]
	 */
	public function getSelectionButtons(
		/** @noinspection PhpUnusedParameterInspection @implements */
		$class_name, array $parameters, Setting\Custom\Set $list_settings = null
	) {
		$buttons = parent::getSelectionButtons($class_name, $parameters, $list_settings);
		return isset($buttons[Feature::F_EXPORT])
			? [Feature::F_EXPORT => $buttons[Feature::F_EXPORT]]
			: [];
	}

	//-------------------------------------------------------------------------------- readDataSelect
	/**
	 * @param $class_name      string Class name for the read object
	 * @param $properties_path string[] the list of the columns names : only those properties
	 *                         will be read. There are 'column.sub_column' to get values from linked
	 *                         objects from the same data source
	 * @param $search          object|array source object for filter, set properties will be used for
	 *                         search. Can be an array associating properties names to matching
	 *                         search value too.
	 * @param $options         Option[] some options for advanced search
	 * @return List_Data A list of read records. Each record values (may be objects) are
	 *         stored in the same order than columns.
	 */
	public function readDataSelect($class_name, array $properties_path, $search, array $options)
	{
		//$filter = ['title' => Func::notOp('bridge_feature')];
		//$search = $search ? Func::andOp([$filter, $search]) : $filter;
		return parent::readDataSelect($class_name, $properties_path, $search, $options);
	}

}
