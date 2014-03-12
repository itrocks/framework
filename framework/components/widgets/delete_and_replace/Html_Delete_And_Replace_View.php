<?php
namespace SAF\Framework;

/**
 * The default delete-and-replace view
 *
 * Needed as we must generate a Combo for the replacement object selection, with a specific filter
 */
class Html_Delete_And_Replace_View implements Html_View
{

	//-------------------------------------------------------------------------------------- getCombo
	/**
	 * @param $object object
	 * @return string HTML combo-box with filters
	 */
	protected function getCombo($object)
	{
		$class_name = get_class($object);
		$edit = new Html_Builder_Type_Edit(
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
		return ['id' => '!' . Dao::getObjectIdentifier($object)];
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
			return (new Html_Default_View())->run(
				$parameters, $form, $files, $class_name, $feature_name . '_done'
			);
		}
		// the view when the replacement was executed but returned errors
		elseif (isset($parameters['error'])) {
			return (new Html_Default_View())->run(
				$parameters, $form, $files, $class_name, $feature_name . '_error'
			);
		}
		// the view that enables the user to select a replacement object
		else {
			$parameters['combo'] = $this->getCombo(reset($parameters));
			return (new Html_Default_View())->run($parameters, $form, $files, $class_name, $feature_name);
		}
	}

}
