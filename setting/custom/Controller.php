<?php
namespace ITRocks\Framework\Setting\Custom;

use ITRocks\Framework\Controller\Feature;

/**
 * Custom settings controller commons
 */
abstract class Controller
{

	//--------------------------------------------------------------- applyParametersToCustomSettings
	/**
	 * @param $custom_settings Set
	 * @param $parameters      array
	 * @return ?Set
	 */
	public static function applyParametersToCustomSettings(Set &$custom_settings, array $parameters)
		: ?Set
	{
		$did_change = false;
		if (isset($parameters['delete_name'])) {
			$custom_settings->delete();
			$did_change = true;
		}
		// keep it last, as load name could be sent on every calls
		if (isset($parameters['load_name'])) {
			$feature = $parameters[Feature::FEATURE] ?? null;
			/** @see Set::load */
			$custom_settings = call_user_func_array(
				[get_class($custom_settings), 'load'],
				[$custom_settings->getSourceClassName(), $feature, $parameters['load_name']]
			);
			$custom_settings->cleanup();
			$did_change = true;
		}
		return $did_change ? $custom_settings : null;
	}

}
