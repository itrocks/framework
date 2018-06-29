<?php
namespace ITRocks\Framework\Email\Tests;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Attachment;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Tests\Test;

/**
 * Email\Encoder tests
 */
class Encoder_Test extends Test
{

	//------------------------------------------------------------------------------- testParseImages
	public function testParseImages()
	{
		/** @var $email Email */
		$email = Builder::create(Email::class);
		$email->attachments = [
			'file1.txt' => new Attachment('file1.txt', 'first file'),
			'file2.txt' => new Attachment('file2.txt', 'second file')
		];
		$email->content = 'Contains an image :'
			. '<img src="itrocks/framework/skins/default/img/delete.png">';

		$assume  = file_get_contents(__DIR__ . '/testParseImage.eml');
		$encoded = str_replace(CR, '', (new Encoder($email))->encode());

		// use the same boundaries in $assume than into the encoded file
		$boundary_tag = 'boundary="=';
		$assume       = str_replace(
			'=' . mParse($assume, '--=', LF), '=' . mParse($encoded, '--=', LF), $assume
		);
		foreach (
			array_slice(
				array_combine(explode($boundary_tag, $assume), explode($boundary_tag, $encoded)), 1
			)
			as $assumed_boundary => $encoded_boundary
		) {
			$assumed_boundary = lParse($assumed_boundary, DQ . LF);
			$encoded_boundary = lParse($encoded_boundary, DQ . LF);
			$assume           = str_replace($assumed_boundary, $encoded_boundary, $assume);
		}

		// use the same embedded images identifiers in $assume than into the encoded file
		$image_tag = 'src=3D"cid:';
		foreach (
			array_slice(array_combine(explode($image_tag, $assume), explode($image_tag, $encoded)), 1)
			as $assumed_image => $encoded_image
		) {
			$assumed_image = lParse($assumed_image, DQ);
			$encoded_image = lParse($encoded_image, DQ);
			$assume        = str_replace($assumed_image, $encoded_image, $assume);
		}

		static::assertEquals(explode(LF, $assume), explode(LF, $encoded));
	}

}
