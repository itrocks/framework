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
	 * @mandatory
	 * @setter
	 * @user invisible
	 * @var string
	 */
	public $class_name = '';

	//------------------------------------------------------------------------------------- $document
	/**
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
	public function __toString() : string
	{
		return trim(
			($this->document ? Loc::tr($this->document->name) : $this->class_name) . SP . $this->name
		);
	}

	//--------------------------------------------------------------------------------- classNamePath
	/**
	 * @noinspection PhpUnused output.html
	 * @return string
	 */
	public function classNamePath() : string
	{
		return (new String_Class($this->class_name))->path();
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 * @throws ReflectionException
	 */
	public function getClass() : Reflection_Class
	{
		return new Reflection_Class($this->class_name);
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * @return string
	 */
	protected function getName() : string
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
	protected function getPages() : array
	{
		/** @noinspection PhpUnhandledExceptionInspection get_class of a valid object */
		$property   = new Reflection_Property($this, 'pages');
		$page_class = $property->getType()->getElementTypeAsString();
		/** @noinspection PhpParamsInspection valid params given to Page::sort() */
		$this->pages = Page::sort(Getter::getCollection($this->pages, $page_class, $this));
		return $this->pages;
	}

	//--------------------------------------------------------------------------------------- newPage
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $position string @values $pages::const
	 * @return Page
	 */
	public function newPage(string $position) : Page
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
	protected function setClassName(string $value)
	{
		if ($this->class_name = $value) {
			$this->document = Dao::searchOne(['class_name' => $this->class_name], Feature_Class::class);
			if (!$this->document) {
				$this->document = new Feature_Class($this->class_name);
				Dao::write($this->document);
			}
		}
	}

	//----------------------------------------------------------------------------------- setDocument
	/**
	 * @param $value Feature_Class|null
	 */
	protected function setDocument(Feature_Class $value = null)
	{
		if (($this->document = $value) && ($this->class_name !== $value->class_name)) {
			$this->class_name = $value->class_name;
		}
	}

}
