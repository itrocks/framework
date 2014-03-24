<?php
namespace SAF\Framework;

/**
 * Custom settings controller
 */
abstract class Custom_Settings_Controller
{

	//--------------------------------------------------------------- applyParametersToCustomSettings
	/**
	 * @param $custom_settings   Custom_Settings
	 * @param $parameters        array
	 * @return Custom_Settings
	 */
	public static function applyParametersToCustomSettings(
		Custom_Settings &$custom_settings, $parameters
	) {
		$did_change = false;
		if (isset($parameters) && isset($parameters['delete_name'])) {
			$custom_settings->delete();
			$did_change = true;
		}
		elseif (isset($parameters['save_name'])) {
			$custom_settings->save($parameters['save_name']);
			$did_change = true;
		}
		elseif (isset($parameters['load_name'])) {
			// keep it last, as load name could be sent on every calls
			$custom_settings = call_user_func_array(
				[get_class($custom_settings), 'load'],
				[$custom_settings->class_name, $parameters['load_name']]
			);
			$custom_settings->cleanup();
			$did_change = true;
		}
		return $did_change ? $custom_settings : null;
	}

}
