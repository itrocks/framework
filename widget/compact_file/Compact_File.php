<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Builder;
use ITRocks\Framework\View\Html\Builder\Property;

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
	public function buildHtml()
	{
		$parameters = array_merge($this->parameters, ['property' => $this->template->getObject()]);

		$feature       = $this->template->getFeature();
		$template_file = __DIR__ . SL . $feature . '.html';
		$template      = Builder::create(
			get_class($this->template),
			[$this->value, $template_file, $feature]
		);

		$template->properties_prefix = explode(DOT, reset($this->template->var_names));
		$template->setParameters($parameters);
		return $template->parse();
	}

}
