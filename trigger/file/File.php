<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Trigger;

/**
 * File trigger
 *
 * @override actions @set_store_name file_trigger_actions @var File\Action[]
 * @property File\Action[] actions
 * @store_name file_triggers
 */
class File extends Trigger
{

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public $file_path;

}
