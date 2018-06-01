<?php
namespace ITRocks\Framework\Widget\Write;

use Exception;
use ITRocks\Framework\Controller\Default_Class_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File\Builder\Post_Files;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Built_Object;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\View_Exception;

/**
 * The default write controller will be called if no other write controller is defined
 */
class Write_Controller implements Default_Class_Controller
{

	//-------------------------------------------------------------------- write controller constant
	const ERROR      = 'error';
	const FILL_COMBO = 'fill_combo';
	const REDIRECT   = 'redirect_after_write';
	const WRITTEN    = 'written';

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters  Parameters
	 * @param $class_name  string
	 * @param $write_error boolean
	 * @return array
	 */
	protected function getViewParameters(Parameters $parameters, $class_name, $write_error)
	{
		$parameters->getMainObject($class_name);
		$parameters                     = $parameters->getObjects();
		$parameters[Template::TEMPLATE] = $write_error ? self::ERROR : self::WRITTEN;
		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for default 'write-typed' controller
	 *
	 * Save data from the posted form into the first parameter object using standard method.
	 * Create a new instance of this object if no identifier was given.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return string
	 * @throws Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		if (!$form && !$files) {
			$max_size = max(ini_get('post_max_size'), ini_get('upload_max_filesize'));
			throw new View_Exception(
				'<div class="error">'
				. Loc::tr('Unable to write your data : you probably sent too much big files') . BR
				. Loc::tr(
					'The maximum allowed size for files / sent data is :max_size',
					Loc::replace(['max_size' => $max_size])
				)
				. '</div>'
			);
		}

		$object     = $parameters->getMainObject($class_name);
		$new_object = !Dao::getObjectIdentifier($object);

		Dao::begin();
		try {
			$builder = new Post_Files(get_class($object));
			$form    = $builder->appendToForm($form, $files);
			$builder = new Object_Builder_Array();
			$builder->null_if_empty_sub_objects = true;
			$builder->build($form, $object);
			$write_objects = [];
			foreach ($builder->getBuiltObjects() as $built_object) {
				$write_object = $built_object->object;
				if (($write_object == $object) || Dao::getObjectIdentifier($write_object)) {
					$write_objects[] = $built_object;
				}
			}
			$write_error = $this->write($write_objects);
			$write_error ? Dao::rollback() : Dao::commit();
		}
		catch (Exception $exception) {
			Dao::rollback();
			throw $exception;
		}

		$parameters = $this->getViewParameters($parameters, $class_name, $write_error);
		$parameters['new_object'] = $new_object;
		return View::run($parameters, $form, $files, $class_name, Feature::F_WRITE);
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @param $write_objects Built_Object[]
	 * @return boolean true when write error, false if writes were made without error
	 */
	protected function write(array $write_objects)
	{
		$write = Dao::current()->getWrite();
		foreach ($write_objects as $write_object) {
			if (!$write->beforeWrite(
				$write_object->object, $write_object->write_options, 'before_writes'
			)) {
				return false;
			}
		}
		foreach ($write_objects as $write_object) {
			if (!Dao::write($write_object->object, $write_object->write_options)) {
				return true;
			}
		}
		return false;
	}

}
