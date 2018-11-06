<?php
namespace ITRocks\Framework\Locale\Translation\Data;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Translation\Data;
use ITRocks\Framework\View;

/**
 * Data translation form : translate one property value into all available languages in one form
 */
class Form_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'form';

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$object        = $parameters->getMainObject();
		$class_name    = Builder::current()->sourceClassName(get_class($object));
		$property_name = $parameters->getRawParameter('property') ?: $parameters->shiftUnnamed();

		$data_set = Dao::search(
			['class_name' => $class_name, 'property_name' => $property_name],
			Data::class,
			[Dao::key('language.code'), Dao::sort()]
		);
		foreach (Dao::readAll(Language::class, Dao::sort()) as $language) {
			if (!isset($data_set[$language->code])) {
				$data                      = new Data();
				$data->object              = $object;
				$data->language            = $language;
				$data->property_name       = $property_name;
				$data_set[$language->code] = $data;
			}
		}
		$parameters->unshift(new Set($object, $property_name, $data_set));
		return View::run($parameters->getObjects(), $form, $files, Data::class, static::FEATURE);
	}

}
