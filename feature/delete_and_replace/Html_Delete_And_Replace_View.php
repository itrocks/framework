<?php
namespace ITRocks\Framework\Feature\Delete_And_Replace;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\Edit\Html_Builder_Type;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\View\Html\Default_View;
use ITRocks\Framework\View\Html\View;

/**
 * The default delete-and-replace view
 *
 * Needed as we must generate a Combo for the replacement object selection, with a specific filter
 */
class Html_Delete_And_Replace_View implements View
{

	//-------------------------------------------------------------------------------------- getCombo
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return string HTML combo-box with filters
	 */
	protected function getCombo(object $object) : string
	{
		$class_name = get_class($object);
		/** @noinspection PhpUnhandledExceptionInspection $class_name from existing object is valid */
		$edit = new Html_Builder_Type(
			'replace_with',
			new Type($class_name),
			Builder::create($class_name)
		);
		return $edit->buildObject($this->getFilters($object));
	}

	//------------------------------------------------------------------------------------ getFilters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return string[] combo filters
	 */
	protected function getFilters(object $object) : array
	{
		$filters = ['id' => Func::notEqual(Dao::getObjectIdentifier($object))];
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach (
			(new Reflection_Class($object))->getProperties([T_EXTENDS, T_USE]) as $property
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
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return ?string
	 */
	public function run(
		array $parameters, array $form, array $files, string $class_name, string $feature_name
	) : ?string
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
