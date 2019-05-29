<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
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
	 * @mandatory
	 * @user readonly
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
		return Builder::create($pages_class, [$position]);
	}

}
