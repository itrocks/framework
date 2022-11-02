<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_\Controller;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tests\Test;

/**
 * Data list search parameters parser : database settings test
 *
 * @group functional
 */
class Database_Settings_Test extends Test
{

	//---------------------------------------------------------------------------------------- TESTED
	const TESTED = [Setting::class, Setting\User::class];

	//----------------------------------------------------------------------------------- $controller
	/**
	 * @var Controller
	 */
	private Controller $controller;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Database_Settings_Test constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->controller = new Controller();
	}

	//-------------------------------------------------------------------------------------- runClass
	/**
	 * @param $class_name string
	 */
	public function runClass(string $class_name)
	{
		$errors = [];
		// get all settings related to list
		$settings = Dao::search(
			['code' => Dao\Func::like('%.list%')], $class_name, [new Dao\Option\Sort('id')]
		);
		foreach ($settings as $setting) {
			$id           = Dao::getObjectIdentifier($setting);
			$list_setting = $setting->value;
			if (($list_setting instanceof List_Setting\Set) && count($list_setting->search)) {
				// be careful not to write $list_settings after this call. data may have been modified
				// this is a test script so we do ont want to modify anything in database
				$this->controller->applySearchParameters($list_setting);
				//write result
				if ($this->controller->getErrors()) {
					$errors["$class_name($id): $setting->code"] = $this->controller->getErrors();
				}
			}
		}
		if ($errors) {
			print_r($errors);
		}
		static::assertEquals([], $errors);
	}

	//------------------------------------------------------------------------------------------ test
	/**
	 * Read all List_Setting\Set of Settings and Setting\User, and test search expressions
	 */
	public function test()
	{
		$this->runClass(Setting::class);
		$this->runClass(Setting\User::class);
	}

}
