<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * File trigger
 *
 * TODO action_per_file : runs an action for each file, without verifying if already running
 *
 * @display_order name, file_path, delete_flag_file, actions
 * @override actions @set_store_name file_trigger_actions @var File\Action[]
 * @property File\Action[] actions
 * @store_name file_triggers
 */
class File extends Trigger
{

	//----------------------------------------------------------------------------- $delete_flag_file
	/**
	 * If true, the file if for triggering use only, contains no data, and can be deleted.
	 * The default use if "do not delete", as files may contain data that will be treated by the
	 * called actions.
	 *
	 * @var boolean
	 */
	public $delete_flag_file;

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public $file_path;

}
