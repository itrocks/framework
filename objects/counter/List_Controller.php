<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Count;
use ITRocks\Framework\Feature\List_\Controller;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Tools\List_Data;

/**
 * Counter list controller
 */
class List_Controller extends Controller
{

	//-------------------------------------------------------------------------------------- readData

	/**
	 * @param $class_name    string
	 * @param $list_settings List_Setting\Set
	 * @param $search        array search-compatible search array
	 * @param $options       Option[]
	 * @return List_Data
	 */
	public function readData(
		$class_name, List_Setting\Set $list_settings, array $search, array $options = []
	) : List_Data
	{
		if (isset($search['identifier'])) {
			$search_identifier = $search['identifier'];
			unset($search['identifier']);
		}
		$data = parent::readData($class_name, $list_settings, $search, $options);
		// 'manual' filter by identifier
		if (isset($search_identifier)) {
			$count = Count::in($options);
			if ($search_identifier instanceof Comparison) {
				if (in_array($search_identifier->sign, [Comparison::EQUAL, Comparison::LIKE])) {
					$search_identifier = $search_identifier->than_value;
				}
			}
			$search_identifier = str_replace(
				['?',  '_',  '*',  '%'],
				['.?', '.?', '.*', '.*'],
				$search_identifier
			);
			$search_pattern = SL . $search_identifier . SL . 'i';
			foreach ($data->getRows() as $key => $data_row) {
				$value = $data_row->getValue('identifier');
				if (!preg_match($search_pattern, $value)) {
					$data->remove($key);
					if ($count) {
						$count->count --;
					}
				}
			}
		}
		return $data;
	}

}
