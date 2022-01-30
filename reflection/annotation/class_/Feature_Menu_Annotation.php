<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ReflectionException;

/**
 * The installation of the features will install this menu entry
 *
 * [/Called/Class/Path[/feature]] [:] Block caption [>|/|, item caption]
 */
class Feature_Menu_Annotation extends Annotation
	implements Class_Context_Annotation, Do_Not_Inherit
{
	use Template\Feature_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_menu';

	//-------------------------------------------------------------------------------- $block_caption
	/**
	 * @var string
	 */
	public string $block_caption;

	//--------------------------------------------------------------------------------- $item_caption
	/**
	 * @var string
	 */
	public string $item_caption;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 * @param $class Reflection_Class
	 * @throws ReflectionException
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		if (static::$context) {
			$class = static::$context;
		}
		$value = str_replace(':', SP, $value);
		if (substr($value, 0, 1) !== SL) {
			$value = View::link(Names::classToSet($class->getName()), Feature::F_LIST) . SP . $value;
			$this->item_caption = ucfirst(Displays_Annotation::of($class));
		}
		else {
			$class_name         = Names::uriToClass(lParse($value, SP));
			$this->item_caption = ucfirst(
				Displays_Annotation::of(new Reflection\Reflection_Class($class_name))
			);
		}
		list($link, $value) = explode(SP, $value, 2);
		parent::__construct($link);
		$value = trim($value);
		if (strpos($value, ',')) {
			$value = str_replace(',', '>', $value);
		}
		if (strpos($value, '/')) {
			$value = str_replace('/', '>', $value);
		}
		if (strpos($value, '>')) {
			list($this->block_caption, $this->item_caption) = explode('>', $value, 2);
			$this->block_caption = trim($this->block_caption);
			$this->item_caption  = trim($this->item_caption);
		}
		elseif ($value) {
			$this->block_caption = $value;
		}
	}

}
