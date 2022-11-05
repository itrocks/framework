<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Add;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\View\Html\Template;

/**
 * Layout model add controller : initialises pages
 */
class Add_Controller extends Add\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$this->tuneProperties($class_name);
		$parameters->set(Template::TEMPLATE, 'add');
		return parent::run($parameters, $form, $files, $class_name);
	}

	//-------------------------------------------------------------------------------- tuneProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 */
	protected function tuneProperties(string $class_name) : void
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$class      = new Reflection_Class($class_name);
		$properties = $class->getProperties();
		User_Annotation::of($properties['pages'])->add(User_Annotation::INVISIBLE);
		User_Annotation::of($properties['document'])->value = [];
	}

}
