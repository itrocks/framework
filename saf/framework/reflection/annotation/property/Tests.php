<?php
namespace SAF\Framework\Reflection\Annotation\Property;

use SAF\Framework\Mapper\Getter;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Interfaces;
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
	 * Annotations set here are used only for the test that uses @link
	 *
	 * @default getDefaultPropertyValue
	 * @link Collection
	 * @var Tests[]
	 */
	private /* @noinspection PhpUnusedPrivateFieldInspection */ $property;

	//----------------------------------------------------------------------- getDefaultPropertyValue
	/**
	 * Get the default property value, for test of @default annotation
	 *
	 * @param $property Interfaces\Reflection_Property
	 * @return string
	 */
	/* @noinspection PhpMissingDocCommentInspection */
	public static function getDefaultPropertyValue(Interfaces\Reflection_Property $property)
	{
		return 'default value for ' . $property->getName();
	}

	//------------------------------------------------------------------------- testDefaultAnnotation
	public function testDefaultAnnotation()
	{
		$this->method('@default');
		$property = new Reflection_Property(self::class, 'property');

		// @default getDefaultPropertyValue
		$this->assume(
			'methodName',
			$property->getDefaultValue(),
			'default value for property'
		);
	}

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
			__CLASS__ . '::testGetterAnnotation'
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
		// default value for getter when there is a @link annotation
		$this->assume(
			'default value when @link',
			$property->getAnnotation('getter')->value,
			Getter::class . '::getCollection'
		);
	}

}
