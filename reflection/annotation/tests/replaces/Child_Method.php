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
	 */
	public function __construct()
	{
		parent::__construct();
		$this->replacement_object = new Son();
		$this->replacement_object->replacement = 'to_replacement';
	}

}
