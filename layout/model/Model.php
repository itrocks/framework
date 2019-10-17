<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\Traits\Has_Name;
use ReflectionException;

/**
 * A print model gives the way to print an object of a given class
 *
 * @business
 * @representative class_name, name
 */
abstract class Model
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @alias document
	 * @mandatory
	 * @user readonly
	 * @user_getter userGetClassName
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------------------- $pages
	/**
	 * @getter
	 * @link Collection
	 * @mandatory
	 * @user hide_edit, hide_output
	 * @var Page[]
	 */
	public $pages;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim($this->class_name . SP . $this->name);
	}

	//--------------------------------------------------------------------------------- classNamePath
	/**
	 * @return string
	 */
	public function classNamePath()
	{
		return (new String_Class($this->class_name))->path();
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 * @throws ReflectionException
	 */
	public function getClass()
	{
		return new Reflection_Class($this->class_name);
	}

	//-------------------------------------------------------------------------------------- getPages
	/**
	 * Sorted pages getter
	 *
	 * @noinspection PhpDocMissingThrowsInspection only valid classes : no exception
	 * @return Page[]
	 */
	public function getPages()
	{
		/** @noinspection PhpUnhandledExceptionInspection get_class of a valid object */
		$property   = new Reflection_Property($this, 'pages');
		$page_class = $property->getType()->getElementTypeAsString();
		/** @noinspection PhpUnhandledExceptionInspection Valid class used */
		$this->pages = Page::sort(Getter::getCollection($this->pages, $page_class, $this));
		return $this->pages;
	}

	//--------------------------------------------------------------------------------------- newPage
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $position string @values $pages::const
	 * @return Page
	 */
	public function newPage($position)
	{
		/** @noinspection PhpUnhandledExceptionInspection get_class of a valid object */
		$property    = new Reflection_Property($this, 'pages');
		$pages_class = $property->getType()->getElementTypeAsString();
		/** @noinspection PhpIncompatibleReturnTypeInspection pages class type must be valid */
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		return Builder::create($pages_class, [$position]);
	}

	//------------------------------------------------------------------------------ userGetClassName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string
	 */
	public function userGetClassName()
	{
		if ($this->class_name && class_exists($this->class_name)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			return Loc::tr(Display_Annotation::of(new Reflection_Class($this->class_name))->value);
		}
		return $this->class_name;
	}

}
