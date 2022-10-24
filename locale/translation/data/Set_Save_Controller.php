<?php
namespace ITRocks\Framework\Locale\Translation\Data;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Save;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\View_Exception;

/**
 * Data translation set save controller
 */
class Set_Save_Controller extends Save\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 * @throws View_Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		/** @var $data_set Set */
		$data_set = $parameters->getMainObject();
		$this->checkFormIntegrity($form, $files);

		$data_set->object        = Dao::read(reset($form), Names::pathToClass(key($form)));
		$data_set->property_name = $form['property_name'];
		/** @noinspection PhpExpressionResultUnusedInspection for calling @getter */
		$data_set->elements;

		foreach ($form['translation'] as $language_code => $translation) {
			if (isset($data_set->elements[$language_code])) {
				$data = $data_set->elements[$language_code];
				if ($data->translation !== $translation) {
					if ($translation) {
						$data->translation = $translation;
						Dao::write($data);
					}
					else {
						Dao::delete($data);
					}
				}
			}
		}

		$parameters                     = $parameters->getObjects();
		$parameters[Template::TEMPLATE] = self::SAVED;

		return View::run($parameters, $form, $files, $class_name, Feature::F_SAVE);
	}

}
