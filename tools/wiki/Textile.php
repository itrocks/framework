<?php
namespace ITRocks\Framework\Tools\Wiki;

use Netcarver\Textile\Parser;

/**
 * Textile review B-Appli style, in order to be more ergonomic
 *
 * Replace textile's '_' by '/' for italic
 * Replace textile's '+' by '_' for underlined
 */
class Textile extends Parser
{

	//----------------------------------------------------------------------------------- $span_depth
	/**
	 * This overrides the Textile::$span_depth property, which declaration has been forgotten
	 *
	 * @var integer
	 */
	public $span_depth;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * This override replace '_' by '/' for em and '+' by '_' for ins
	 * Default is html5
	 *
	 * @param string $doc_type The output document type, either 'xhtml' or 'html5'
	 */
	public function __construct($doc_type = 'html5')
	{
		parent::__construct($doc_type);

		$this->span_tags = [
			'*'  => 'strong',
			'**' => 'b',
			'??' => 'cite',
			'/'  => 'em',
			'//' => 'i',
			'-'  => 'del',
			'%'  => 'span',
			'_'  => 'ins',
			'~'  => 'sub',
			'^'  => 'sup',
		];
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param $text string
	 * @return string
	 */
	public function parse($text)
	{
		$text   = strReplace(['@@@' => '@&at;', '@@' => '&at;'], $text);
		$result = parent::parse($text);
		return strReplace(['&at;' => '@', '&amp;at;' => '@', '|' => '||'], $result);
	}

	//------------------------------------------------------------------------------------ parseSpans
	/**
	 * Replaces <one+two: by <span class='one two'>
	 * Replaces :> by </span>
	 *
	 * This makes available all customizations of text formatting, linked to css stylesheets.
	 *
	 * @param $text string
	 * @return string
	 */
	private function parseSpans($text)
	{
		$length = strlen($text);
		// replaces <one+two: by <span class='one two'>
		$i = 0;
		while (($i = strpos($text, '<', $i)) !== false) {
			$j = ++$i;
			while (
				($j < $length)
				&& (strpos('abcdefghijklmnopqrstuvwxyz0123456789_+', $text[$j]) !== false)
			) {
				$j ++;
			}
			if (($j < $length) && ($text[$j] === ':')) {
				$text = substr($text, 0, $i)
					. 'span class="' . str_replace('+', SP, substr($text, $i, $j - $i)) . '">'
					. substr($text, $j + 1);
				$length += 13;
			}
		}
		// replaces :> by </span>
		return str_replace(':>', '</span>', $text);
	}

	//----------------------------------------------------------------------------------------- spans
	/**
	 * This override replaces '/' by '`' as REGEX separator
	 * Replaces textile spans with their equivalent HTML inline tags.
	 *
	 * @param  string $text The textile document to perform the replacements in.
	 * @return string       The textile document with spans replaced by their HTML inline equivalents
	 */
	protected function spans($text)
	{
		$span_tags = array_keys($this->span_tags);
		$pnct = '.,"\'?!;:‹›«»„“”‚‘’';
		$this->span_depth++;

		if ($this->span_depth <= $this->max_span_depth) {
			foreach ($span_tags as $tag) {
				$tag = preg_quote($tag);
				$text = preg_replace_callback(
					"`
					(?P<pre>^|(?<=[\s>$pnct\(])|[{[])
					(?P<tag>$tag)(?!$tag)
					(?P<atts>{$this->cls})
					(?!$tag)
					(?::(?P<cite>\S+[^$tag]{$this->regex_snippets['space']}))?
					(?P<content>[^{$this->regex_snippets['space']}$tag]+|\S.*?[^\s$tag\n])
					(?P<end>[$pnct]*)
					$tag
					(?P<tail>$|[\[\]}<]|(?=[$pnct]{1,2}[^0-9]|\s|\)))
					`x" . $this->regex_snippets['mod'],
					[&$this, 'fSpan'],
					$text
				);
			}
		}
		$this->span_depth--;
		return $text;
	}

	//----------------------------------------------------------------------------------- textileThis
	/**
	 * Parses the given Textile input in un-restricted mode.
	 *
	 * @param $text     string The Textile input to parse
	 * @param $lite     string|boolean Switch to lite mode
	 * @param $encode   string|boolean Encode input and return
	 * @param $no_image string|boolean Disables images
	 * @param $strict   string|boolean false to strip whitespace before parsing
	 * @param $rel      string|boolean Relationship attribute applied to generated links
	 * @return string Parsed $text
	 */
	public function textileThis(
		$text, $lite = false, $encode = false, $no_image = false, $strict = false, $rel = false
	) {
		return $this->parseSpans(parent::textileThis($text, $lite, $encode, $no_image, $strict, $rel));
	}

}
