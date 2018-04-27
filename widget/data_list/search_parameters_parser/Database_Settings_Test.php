<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\User_Setting;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Data list search parameters parser : database settings test
 *
 * @group functional
 */
class Database_Settings_Test extends Test
{

	//---------------------------------------------------------------------------------------- TESTED
	const TESTED = [Setting::class, User_Setting::class];

	//------------------------------------------------------------------------- $data_list_controller
	/**
	 * @var $data_list_controller Data_List_Controller
	 */
	private $data_list_controller;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Database_Settings_Test constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->data_list_controller = new Data_List_Controller();
	}

	//-------------------------------------------------------------------------------------- runClass
	/**
	 * @param $class_name string
	 */
	public function runClass($class_name)
	{
		$errors = [];
		//get all settings related to data_list
		$settings = Dao::search(
			['code' => Dao\Func::like('%.data_list%')], $class_name, [new Dao\Option\Sort('id')]
		);
		foreach ($settings as $setting) {
			$id                = Dao::getObjectIdentifier($setting);
			$data_list_setting = $setting->value;
			if ($data_list_setting instanceof Data_List_Settings && count($data_list_setting->search)) {
				// be careful not to write $data_list_settings after this call. data may have been modified
				// this is a test script so we do ont want to modify anything in database
				$this->data_list_controller->applySearchParameters($data_list_setting);
				//write result
				if ($this->data_list_controller->getErrors()) {
					$errors["$class_name($id): $setting->code"] = $this->data_list_controller->getErrors();
				}
			}
		}
		$this->assertEquals([], $errors);
	}

	//------------------------------------------------------------------------------------------ test
	/**
	 * Read all Data_List_Settings of Settings and User_Settings, and test search expressions
	 */
	public function test()
	{
		$this->runClass(Setting::class);
		$this->runClass(User_Setting::class);
	}

}
