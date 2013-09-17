<?php
namespace SAF\Framework;

/**
 * Parse post files list like $_FILES to get them into objects
 * or append them to a form array like $_POST
 *
 * TODO not done yet !
 */
class File_Builder_Post_Files
{

	//---------------------------------------------------------------------------------- appendToForm
	/**
	 * @param $form  array
	 * @param $files array
	 * @return array
	 */
	public function appendToForm($form, $files)
	{
		if (is_array($files)) {
			foreach ($files as $top => $element) {
				// element keys are standard post files keys : name, type, tmp_name, error, size
				if (is_array($element["name"])) {
					if (!isset($form[$top])) {
						$form[$top] = array();
					}
					$form[$top] = $this->appendToFormRecurse(
						$form[$top], $element["name"], $element["tmp_name"]
					);
				}
				elseif (!(empty($element["name"]) || empty($element["tmp_name"]))) {
					$file = new File();
					$file->name = $element["name"];
					$file->temporary_file_name = $element["tmp_name"];
					$form[$top] = $file;
				}
			}
		}
		return $form;
	}

	//--------------------------------------------------------------------------- appendToFormRecurse
	/**
	 * @param $form             array
	 * @param $name_element     array
	 * @param $tmp_name_element array
	 * @return array
	 */
	private function appendToFormRecurse($form, $name_element, $tmp_name_element)
	{
		foreach ($name_element as $key => $name_sub_element) {
			if (is_array($name_sub_element)) {
				if (!isset($form[$key])) {
					$form[$key] = array();
				}
				$form[$key] = $this->appendToFormRecurse(
					$form[$key], $name_sub_element, $tmp_name_element[$key]
				);
			}
			else {
				$file = new File();
				$file->name = $name_sub_element;
				$file->temporary_file_name = $tmp_name_element[$key];
				$form[$key] = $file;
			}
		}
		return $form;
	}

}
