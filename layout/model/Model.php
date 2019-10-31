<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Feature_Class;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\Traits\Has_Name;
use ReflectionException;

/**
 * A print model gives the way to print an object of a given class
 *
 * @business
 * @override name @getter
 * @representative document.name, name
 */
abstract class Model
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @getter
	 * @mandatory
	 * @setter
	 * @user invisible
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------- $document
	/**
	 * @getter
	 * @link Object
	 * @setter
	 * @user readonly
	 * @var Feature_Class
	 */
	public $document;

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
		return trim(
			($this->document ? Loc::tr($this->document->name) : $this->class_name) . SP . $this->name
		);
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

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	protected function getClassName()
	{
		if (!$this->class_name) {
			$this->class_name = $this->document ? $this->document->class_name : null;
		}
		return $this->class_name;
	}

	//----------------------------------------------------------------------------------- getDocument
	/**
	 * @return Feature_Class
	 */
	protected function getDocument()
	{
		if (!$this->document && $this->class_name) {
			$this->document = Dao::searchOne(['class_name' => $this->class_name], Feature_Class::class);
			if (!$this->document) {
				$this->document = new Feature_Class($this->class_name);
				Dao::write($this->document);
			}
		}
		return $this->document;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	protected function getName()
	{
		if (!$this->name && $this->document) {
			$this->name = $this->document->name;
		}
		return Loc::tr($this->name);
	}

	//-------------------------------------------------------------------------------------- getPages
	/**
	 * Sorted pages getter
	 *
	 * @noinspection PhpDocMissingThrowsInspection only valid classes : no exception
	 * @return Page[]
	 */
	protected function getPages()
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

	//---------------------------------------------------------------------------------- setClassName
	/**
	 * @param $value string
	 */
	protected function setClassName($value)
	{
		if ($this->class_name = $value) {
			$this->document = null;
			$this->document;
		}
	}

	//----------------------------------------------------------------------------------- setDocument
	/**
	 * @param $value Feature_Class
	 */
	protected function setDocument(Feature_Class $value = null)
	{
		if ($this->document = $value) {
			$this->class_name = '';
			$this->class_name;
		}
	}

}
