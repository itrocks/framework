<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\User_Setting;
use ITRocks\Framework\Tests\Testable;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

/**
 * Data list search parameters parser : database settings test
 */
class Test_Database_Settings extends Testable
{

	//------------------------------------------------------------------------- $data_list_controller
	/**
	 * @var $data_list_controller Data_List_Controller
	 */
	private $data_list_controller;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Test_Database_Settings constructor.
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
		$errors_count = 0;
		//get all settings related to data_list
		$settings = Dao::search(
			['code' => Dao\Func::like('%.data_list%')], $class_name, [new Dao\Option\Sort('id')]
		);
		foreach ($settings as $setting) {
			$id                = Dao::getObjectIdentifier($setting);
			$data_list_setting = $setting->value;
			if ($data_list_setting instanceof Data_List_Settings && count($data_list_setting->search)) {
				$search = $data_list_setting->search;
				// be careful not to write $data_list_settings after this call. data may have been modified
				// this is a test script so we do ont want to modify anything in database
				$this->data_list_controller->applySearchParameters($data_list_setting);
				//write result
				$errors = $this->data_list_controller->getErrors();
				if ($this->show == Testable::ALL || ($this->show == Testable::ERRORS && count($errors))) {
					$this->method("$class_name($id): $setting->code");
				}
				foreach($search as $property_path => $expression) {
					$this->tests_count++;
					if (isset($errors[$property_path])) {
						$errors_count++;
						$error = $errors[$property_path];
						if ($this->show === self::ERRORS) {
							$this->header .= '<li>' . htmlentities($property_path . ' : ' . $expression) . BR
								. '<span style="color:red;">BAD : '
								. ($error instanceof \Exception ? $error->getMessage() : $error)
								. '</span></li>';
							$this->flush();
						}
					}
				}
			}
		}
		$this->errors_count = $errors_count;
	}

	//------------------------------------------------------------------------------------ runTheTest
	/**
	 * Read all Data_List_Settings of Settings and User_Settings, and test search expressions
	 */
	public function runTheTest()
	{
		$this->runClass(Setting::class);
		$this->runClass(User_Setting::class);
	}

}
