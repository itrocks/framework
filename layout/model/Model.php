<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Feature_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\Traits\Has_Name;
use ReflectionException;

/**
 * A print model gives the way to print an object of a given class
 */
#[Override('name', new Getter('getName')), Representative('document.name', 'name'), Store]
abstract class Model
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	#[Mandatory, Setter, User(User::INVISIBLE)]
	public string $class_name = '';

	//------------------------------------------------------------------------------------- $document
	#[Setter, User(User::READONLY)]
	public ?Feature_Class $document;

	//---------------------------------------------------------------------------------------- $pages
	/** @var Page[] */
	#[Component, Getter, Mandatory, User(User::HIDE_EDIT, User::HIDE_OUTPUT)]
	public array $pages;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$document_name = $this->document
			? Loc::tr($this->document->name)
			: Names::classToDisplay($this->class_name);
		return trim($document_name . SP . $this->name);
	}

	//--------------------------------------------------------------------------------- classNamePath
	/** @noinspection PhpUnused output.html */
	public function classNamePath() : string
	{
		return (new String_Class($this->class_name))->path();
	}

	//-------------------------------------------------------------------------------------- getClass
	/** @throws ReflectionException */
	public function getClass() : Reflection_Class
	{
		return new Reflection_Class($this->class_name);
	}

	//--------------------------------------------------------------------------------------- getName
	protected function getName() : string
	{
		if (!$this->name && $this->document) {
			$this->name = $this->document->name;
		}
		return $this->name ? Loc::tr($this->name) : '';
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
		if (isset($this->pages)) {
			return $this->pages;
		}
		/** @noinspection PhpUnhandledExceptionInspection get_class of a valid object */
		$property    = new Reflection_Property($this, 'pages');
		$page_class  = $property->getType()->getElementTypeAsString();
		/** @noinspection PhpParamsInspection valid params given to Page::sort() */
		$this->pages = Page::sort(Mapper\Getter::getCollection($pages, $page_class, $this, 'pages'));
		return $this->pages;
	}

	//--------------------------------------------------------------------------------------- newPage
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $position string @values $pages::const
	 */
	public function newPage(string $position) : Page
	{
		/** @noinspection PhpUnhandledExceptionInspection get_class of a valid object */
		$property    = new Reflection_Property($this, 'pages');
		$pages_class = $property->getType()->getElementTypeAsString();
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		return Builder::create($pages_class, [$position]);
	}

	//---------------------------------------------------------------------------------- setClassName
	/** @noinspection PhpUnused #Setter */
	protected function setClassName(string $value) : void
	{
		if (!($this->class_name = $value)) {
			return;
		}
		$this->document = Dao::searchOne(['class_name' => $this->class_name], Feature_Class::class);
		if ($this->document) {
			return;
		}
		$this->document = new Feature_Class($this->class_name);
		Dao::write($this->document);
	}

	//----------------------------------------------------------------------------------- setDocument
	/** @noinspection PhpUnused #Setter */
	protected function setDocument(Feature_Class $value = null) : void
	{
		if (($this->document = $value) && ($this->class_name !== $value->class_name)) {
			$this->class_name = $value->class_name;
		}
	}

}
