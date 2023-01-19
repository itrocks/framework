<?php
namespace ITRocks\Framework\Dao\File\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Reflection\Reflection_Property;
use ReflectionException;

/**
 * Parse post files list like $_FILES to get them into objects
 * or append them to a form array like $_POST
 */
class Post_Files
{

	//------------------------------------------------------------------------------- $for_class_name
	/**
	 * @var string
	 */
	public string $for_class_name = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $for_class_name string|null
	 */
	public function __construct(string $for_class_name = null)
	{
		if (isset($for_class_name)) {
			$this->for_class_name = $for_class_name;
		}
	}

	//---------------------------------------------------------------------------------- appendToForm
	/**
	 * @param $form            array
	 * @param $files           array[]
	 * @param $result_as_files boolean if true, result will be File[]
	 * @return array[]|File[]
	 */
	public function appendToForm(array $form, array $files, bool $result_as_files = false) : array
	{
		foreach ($files as $top => $element) {
			// element keys are standard post files keys : name, type, tmp_name, error, size
			if (is_array($element['name'])) {
				if (!isset($form[$top])) {
					$form[$top] = [];
				}
				$form[$top] = $this->appendToFormRecurse(
					$top, $form[$top], $element['name'], $element['tmp_name'], $result_as_files
				);
			}
			elseif (!(empty($element['name']) || empty($element['tmp_name']))) {
				if ($result_as_files) {
					$file                      = $this->newFileObject($top);
					$file->name                = $element['name'];
					$file->temporary_file_name = $element['tmp_name'];
					$form[$top]                = $file;
				}
				else {
					$form[$top] = [
						'name'                => $element['name'],
						'temporary_file_name' => $element['tmp_name']
					];
				}
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
	 * @param $result_as_files  boolean
	 * @return array[]|File[]
	 */
	private function appendToFormRecurse(
		string $property_path, array $form, array $name_element, array $tmp_name_element,
		bool $result_as_files
	) : array
	{
		foreach ($name_element as $key => $name_sub_element) {
			if (is_array($name_sub_element)) {
				if (!isset($form[$key])) {
					$form[$key] = [];
				}
				$form[$key] = $this->appendToFormRecurse(
					$property_path . DOT . $key,
					$form[$key],
					$name_sub_element,
					$tmp_name_element[$key],
					$result_as_files
				);
			}
			else {
				if ($this->for_class_name && !is_numeric($key)) {
					try {
						new Reflection_Property($this->for_class_name, $property_path . DOT . $key);
						$property_path .= DOT . $key;
					}
					catch(ReflectionException) {
					}
				}
				if ($result_as_files) {
					$file                      = $this->newFileObject($property_path);
					$file->name                = $name_sub_element;
					$file->temporary_file_name = $tmp_name_element[$key];
					$form[$key]                = $file;
				}
				else {
					$form[$key] = [
						'name'                => $name_sub_element,
						'temporary_file_name' => $tmp_name_element[$key]
					];
				}
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
	protected function newFileObject(string $property_path) : File
	{
		if ($this->for_class_name) {
			try {
				$property   = new Reflection_Property($this->for_class_name, $property_path);
				$file_class = $property->getType()->getElementTypeAsString();
			}
			catch (ReflectionException) {
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection file class must be a valid class */
		/** @var $file File */
		$file = Builder::create($file_class ?? File::class);
		return $file;
	}

}
