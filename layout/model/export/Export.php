<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Files;

/**
 * Layout model export
 *
 * This exports multiple layout models (if one : an array of one) into a json structured file
 */
class Export
{

	//---------------------------------------------------------------------------------------- export
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $models Model[]
	 * @return string a json structure
	 */
	public function export(array $models) : string
	{
		// array_values ensure that keys are 0..n, and that the json structure will be an array
		$models = array_values($models);
		Dao::exhaust($models, false, true);
		foreach ($models as $model) {
			foreach ($model->pages as $page) {
				if ($page->background->content ?? false) {
					$page->background->content = base64_encode($page->background->content);
				}
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		$output = jsonEncode($models);
		if (json_last_error()) {
			trigger_error('JSON ' . json_last_error_msg(), E_USER_ERROR);
		}
		Files::downloadOutput(Loc::tr('print models') . '.json', 'application/json', strlen($output));
		return $output;
	}

}
