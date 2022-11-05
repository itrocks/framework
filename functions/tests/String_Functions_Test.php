<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Unit tests for the string_functions.php functions library
 */
class String_Functions_Test extends Test
{

	//------------------------------------------------------------------------------- accentsProvider
	/**
	 * Provides test data for testRemoveAccents().
	 *
	 * @return string[][]
	 */
	public function accentsProvider() : array
	{
		return [
			['À', 'A'],
			['Á', 'A'],
			['Â', 'A'],
			['Ã', 'A'],
			['Ä', 'A'],
			['Å', 'A'],
			['Ç', 'C'],
			['È', 'E'],
			['É', 'E'],
			['Ê', 'E'],
			['Ë', 'E'],
			['Ì', 'I'],
			['Í', 'I'],
			['Î', 'I'],
			['Ï', 'I'],
			['Ò', 'O'],
			['Ó', 'O'],
			['Ô', 'O'],
			['Õ', 'O'],
			['Ö', 'O'],
			['Ù', 'U'],
			['Ú', 'U'],
			['Û', 'U'],
			['Ü', 'U'],
			['Ý', 'Y'],
			['Ÿ', 'Y'],
			['à', 'a'],
			['á', 'a'],
			['â', 'a'],
			['ã', 'a'],
			['ä', 'a'],
			['å', 'a'],
			['ç', 'c'],
			['è', 'e'],
			['é', 'e'],
			['ê', 'e'],
			['ë', 'e'],
			['ì', 'i'],
			['í', 'i'],
			['î', 'i'],
			['ï', 'i'],
			['ð', 'o'],
			['ò', 'o'],
			['ó', 'o'],
			['ô', 'o'],
			['õ', 'o'],
			['ö', 'o'],
			['ù', 'u'],
			['ú', 'u'],
			['û', 'u'],
			['ü', 'u'],
			['ý', 'y'],
			['ÿ', 'y'],
			['&', 'and'],
			['foo', 'foo'],
			['étoile', 'etoile'],
		];
	}

	//----------------------------------------------------------------------- testBase64EncodeUrlSafe
	public function testBase64EncodeUrlSafe() : void
	{
		static::assertEquals(
			'VGVzdCBlbmNvZGFnZSBhdmVjIGRlcyArLCAvIGV0ID0.',
			base64_encode_url_safe('Test encodage avec des +, / et =')
		);
	}

	//----------------------------------------------------------------------------- testRemoveAccents
	/**
	 * Test function removeAccents() with several case.
	 *
	 * @dataProvider accentsProvider
	 * @param $string   string The string to replace accents in
	 * @param $expected string The expected result for the given string
	 */
	public function testRemoveAccents(string $string, string $expected) : void
	{
		static::assertEquals($expected, removeAccents($string));
	}

	//-------------------------------------------------------------------------------- testStrReplace
	public function testStrReplace() : void
	{
		$subject = 'This is a text where some things wanna be searched and replaced';
		$replace = [
			'This' => 'These',
			'is'   => 'are',
			' a '  => ' some ',
			'text' => 'texts',
			'ed'   => 'ED'
		];
		$result = 'These are some texts where some things wanna be searchED and replacED';
		static::assertEquals($result, strReplace($replace, $subject));
	}

}
