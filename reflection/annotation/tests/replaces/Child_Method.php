<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

/**
 * Child method @replaces test
 */
class Child_Method extends Parent_Method
{

	//--------------------------------------------------------------------------- $replacement_object
	/**
	 * @replaces replaced_object
	 * @var Son
	 */
	public Son $replacement_object;

	//--------------------------------------------------------------------------- $replacement_string
	/**
	 * @replaces replaced_string
	 * @var string
	 */
	public string $replacement_string;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct()
	{
		// do not call parent::__construct : $replacement_object replaces $replaced_object and forces
		// the class Son, so the parent constructor which writes a Parent_Class object here would crash.
		$this->replacement_object = new Son();
		$this->replacement_object->replacement = 'to_replacement';
	}

}
