<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Template;

/**
 * Compact (single icon) widget for File
 */
class Compact_File extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function buildHtml() : string
	{
		$parameters = array_merge($this->parameters, ['property' => $this->property]);

		$feature = ($this->template->getFeature() === Feature::F_OUTPUT)
			? Feature::F_OUTPUT
			: Feature::F_EDIT;
		$template_file = __DIR__ . SL . $feature . '.html';
		/** @noinspection PhpUnhandledExceptionInspection object class */
		/** @var $template Template */
		$template = Builder::create(
			get_class($this->template),
			[$this->value, $template_file, $feature]
		);

		$template->properties_prefix = $this->template->properties_prefix;
		$template->setParameters($parameters);
		return $template->parse();
	}

}
