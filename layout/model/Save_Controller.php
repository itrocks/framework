<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File\Builder\Post_Files;
use ITRocks\Framework\Feature\Save;
use ITRocks\Framework\Mapper\Built_Object;

/**
 * Layout model save controller
 */
class Save_Controller extends Save\Controller
{

	//---------------------------------------------------------------------------------- buildObjects
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @param $form   array
	 * @param $files  array
	 * @return Built_Object[]
	 */
	protected function buildObjects(object $object, array $form, array $files) : array
	{
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		$built_objects = parent::buildObjects($object, $form, $files);
		$builder       = new Post_Files(get_class($object));
		$form          = $builder->appendToForm($form, $files);
		// this patch because we must save the new background as a new file
		// (existing file should not be replaced, and is not written if an id is already set)
		if (isset($form['pages'])) {
			foreach ($form['pages']['background'] as $background_key => $background_data) {
				if (!$background_data) {
					continue;
				}
				Dao::disconnect($object->pages[$background_key]->background);
			}
		}
		return $built_objects;
	}

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
		unset($form['search']);
		return parent::run($parameters, $form, $files, $class_name);
	}

}
