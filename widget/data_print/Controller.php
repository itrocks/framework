<?php
namespace ITRocks\Framework\Widget\Data_Print;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Widget\Data_List\Selection;

/**
 * Print controller
 */
class Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$selection = new Selection($class_name);
		$selection->setFormData($form);
		$objects = $selection->readObjects();

		/** @noinspection PhpUnhandledExceptionInspection Object should always be found */
		$layout_model = $parameters->getObject(Model::class);

		$structure = null;
		foreach ($objects as $object) {
			$generator = new Generator($layout_model);
			$structure = $generator->generate($object);

			// TODO remove this debug file
			$buffer = [];
			foreach ($structure->pages as $page) {
				$buffer[] = LF . '########## PAGE ' . $page->number . LF;
				foreach ($page->elements as $element) {
					$buf = '- ' . get_class($element) . ' : ' . $element->left . ', ' . $element->top . ' : ' . $element->width . ', ' . $element->height;
					if ($element instanceof Text) {
						$buf .= ' = ' . $element->text;
					}
					if ($element instanceof Group) {
						foreach ($element->elements as $sub_element) {
							$buffer[] = $buf . LF;
							$buf = '  - ' . get_class($sub_element) . ' : ' . $sub_element->left . ', ' . $sub_element->top . ' : ' . $sub_element->width . ', ' . $sub_element->height;
							if ($sub_element instanceof Text) {
								$buf .= ' = ' . $sub_element->text;
							}
						}
					}
					$buffer[] = $buf . LF;
				}
			}
			file_put_contents('/tmp/structure.txt', join(LF, $buffer));

		}

		return 'generated';
	}

}
