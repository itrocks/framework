<?php
namespace ITRocks\Framework\Dao\File\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Reflection\Reflection_Property;
use ReflectionException;

/**
 * Parse post files list like $_FILES to get them into objects
 * or append them to a form array like $_POST
 *
 * TODO not done yet !
 */
class Post_Files
{

	//------------------------------------------------------------------------------- $for_class_name
	/**
	 * @var string
	 */
	public $for_class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $for_class_name string
	 */
	public function __construct($for_class_name = null)
	{
		if (isset($for_class_name)) {
			$this->for_class_name = $for_class_name;
		}
	}

	//---------------------------------------------------------------------------------- appendToForm
	/**
	 * @param $form  array
	 * @param $files array[]
	 * @return array
	 */
	public function appendToForm(array $form, array $files)
	{
		foreach ($files as $top => $element) {
			// element keys are standard post files keys : name, type, tmp_name, error, size
			if (is_array($element['name'])) {
				if (!isset($form[$top])) {
					$form[$top] = [];
				}
				$form[$top] = $this->appendToFormRecurse(
					$top, $form[$top], $element['name'], $element['tmp_name']
				);
			}
			elseif (!(empty($element['name']) || empty($element['tmp_name']))) {
				$file                      = $this->newFileObject($top);
				$file->name                = $element['name'];
				$file->temporary_file_name = $element['tmp_name'];
				$form[$top]                = $file;
			}
		}
		return $form;
	}

	//--------------------------------------------------------------------------- appendToFormRecurse
	/**
	 * @param $property_path    string
	 * @param $form             array
	 * @param $name_element     array
	 * @param $tmp_name_element array
	 * @return array
	 */
	private function appendToFormRecurse(
		$property_path, array $form, array $name_element, array $tmp_name_element
	) {
		foreach ($name_element as $key => $name_sub_element) {
			if (is_array($name_sub_element)) {
				if (!isset($form[$key])) {
					$form[$key] = [];
				}
				$form[$key] = $this->appendToFormRecurse(
					$property_path . DOT . $key, $form[$key], $name_sub_element, $tmp_name_element[$key]
				);
			}
			else {
				if ($this->for_class_name && !is_numeric($key)) {
					try {
						new Reflection_Property($this->for_class_name, $property_path . DOT . $key);
						$property_path .= DOT . $key;
					}
					catch(ReflectionException $exception) {
					}
				}
				$file                      = $this->newFileObject($property_path);
				$file->name                = $name_sub_element;
				$file->temporary_file_name = $tmp_name_element[$key];
				$form[$key]                = $file;
			}
		}
		return $form;
	}

	//--------------------------------------------------------------------------------- newFileObject
	/**
	 * Return a file object that complies the file type of the property into the reference class name
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property_path string
	 * @return File
	 */
	protected function newFileObject($property_path)
	{
		if ($this->for_class_name) {
			try {
				$property   = new Reflection_Property($this->for_class_name, $property_path);
				$file_class = $property->getType()->getElementTypeAsString();
			}
			catch (ReflectionException $exception) {
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection file class must be a valid class */
		/** @var $file File */
		$file = Builder::create($file_class ?? File::class);
		return $file;
	}

}
