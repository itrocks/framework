<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tests\Test;

/**
 * Property annotations unit tests
 */
class Tests extends Test
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * A fictive local property, for unit tests use only
	 * We don't care any of its annotations or values
	 *
	 * @var string
	 */
	private /* @noinspection PhpUnusedPrivateFieldInspection */ $property;

	//-------------------------------------------------------------------------- testGetterAnnotation
	/**
	 * Test property @getter
	 */
	public function testGetterAnnotation()
	{
		$this->method('@getter');

		$property = new Reflection_Property(self::class, 'property');
		// @getter methodName
		$this->assume(
			'methodName',
			(new Getter_Annotation('testGetterAnnotation', $property))->value,
			'testGetterAnnotation'
		);
		// @getter Local_Class_Name::methodName
		$this->assume(
			'Local_Class_Name::methodName',
			(new Getter_Annotation('User_Annotation::has', $property))->value,
			User_Annotation::class . '::has'
		);
		// @getter Distant\Class\Full\Path::methodName
		$this->assume(
			'Distant\Class\Full\Path\Class_Name::methodName',
			(new Getter_Annotation(Annoted::class . '::has', $property))->value,
			Annoted::class . '::has'
		);
		// use Distant\Class\Full\Path\Class_Name
		// @getter Class_Name::methodName
		$this->assume(
			'use Class_Name::methodName',
			(new Getter_Annotation('Annoted::has', $property))->value,
			Annoted::class . '::has'
		);
	}

}
