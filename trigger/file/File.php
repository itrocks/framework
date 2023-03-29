<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Trigger;

/**
 * File trigger
 *
 * TODO action_per_file : runs an action for each file, without verifying if already running
 *
 * @override actions @set_store_name file_trigger_actions @var File\Action[]
 * @property File\Action[] actions
 */
#[
	Display_Order('name', 'file_path', 'delete_flag_file', 'actions'),
	Store('file_triggers')
]
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
	public bool $delete_flag_file = false;

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public string $file_path;

	//------------------------------------------------------------------------------- $trigger_static
	/**
	 *
	 * false (default) : the action will be triggered each time the file modification date changes.
	 * true : the action will be triggered while the file is here, even if it is not changed.
	 *
	 * This affects action triggering only when $delete_flag_file is false.
	 *
	 * @var boolean
	 */
	public bool $trigger_static = false;

}
