<?php
namespace SAF\Framework\Widget\Delete_And_Replace;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Type;
use SAF\Framework\View\Html\Default_View;
use SAF\Framework\View\Html\View;
use SAF\Framework\Widget\Edit\Html_Builder_Type;

/**
 * The default delete-and-replace view
 *
 * Needed as we must generate a Combo for the replacement object selection, with a specific filter
 */
class Html_Delete_And_Replace_View implements View
{

	//-------------------------------------------------------------------------------------- getCombo
	/**
	 * @param $object object
	 * @return string HTML combo-box with filters
	 */
	protected function getCombo($object)
	{
		$class_name = get_class($object);
		$edit = new Html_Builder_Type(
			'replace_with',
			new Type($class_name),
			Builder::create($class_name)
		);
		return $edit->buildObject(null, $this->getFilters($object));
	}

	//------------------------------------------------------------------------------------ getFilters
	/**
	 * @param $object object
	 * @return string[] combo filters
	 */
	protected function getFilters($object)
	{
		$filters = ['id' => '!' . Dao::getObjectIdentifier($object)];
		foreach (
			(new Reflection_Class(get_class($object)))->getProperties([T_EXTENDS, T_USE]) as $property
		) {
			if ($property->getAnnotation('replace_filter')->value) {
				$filters[$property->name] = Dao::getObjectIdentifier($object, $property->name);
			}
		}
		return $filters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		// the view when the replacement has been done
		if (isset($parameters['done'])) {
			return (new Default_View())->run(
				$parameters, $form, $files, $class_name, $feature_name . '_done'
			);
		}
		// the view when the replacement was executed but returned errors
		elseif (isset($parameters['error'])) {
			return (new Default_View())->run(
				$parameters, $form, $files, $class_name, $feature_name . '_error'
			);
		}
		// the view that enables the user to select a replacement object
		else {
			$parameters['combo'] = $this->getCombo(reset($parameters));
			return (new Default_View())->run($parameters, $form, $files, $class_name, $feature_name);
		}
	}

}
