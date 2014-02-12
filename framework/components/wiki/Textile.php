<?php
namespace SAF\Framework;

require_once __DIR__ . "/../../vendor/textile/classTextile.php";

/**
 * Textile review B-Appli style, in order to be more ergonomic
 *
 * Replace textile's '_' by '/' for italic
 * Replace textile's '+' by '_' for underlined
 */
class Textile extends \Textile
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
	 * @param string $doctype The output document type, either 'xhtml' or 'html5'
	 */
	public function __construct($doctype = 'html5')
	{
		parent::__construct($doctype);

		$this->span_tags = array(
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
		);
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param $text string
	 * @return string
	 */
	public static function parse($text)
	{
		return (new Textile)->textileThis($text);
	}

	//------------------------------------------------------------------------------------ parseSpans
	/**
	 * Replaces <one+two: by <span class="one two">
	 * Replaces :> by </span>
	 *
	 * This makes available all customizations of text formatting, linked to css stylesheets.
	 *
	 * @param $text
	 * @return string
	 */
	private function parseSpans($text)
	{
		$length = strlen($text);
		// replaces <one+two: by <span class="one two">
		$i = 0;
		while (($i = strpos($text, "<", $i)) !== false) {
			$j = ++$i;
			while (
				($j < $length)
				&& (strpos("abcdefghijklmnopqrstuvwxyz0123456789_+", $text[$j]) !== false)
			) {
				$j ++;
			}
			if (($j < $length) && ($text[$j] === ":")) {
				$text = substr($text, 0, $i)
					. "span class=\"" . str_replace("+", " ", substr($text, $i, $j - $i)) . "\">"
					. substr($text, $j + 1);
				$length += 13;
			}
		}
		// replaces :> by </span>
		return str_replace(":>", "</span>", $text);
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
		$pnct = ".,\"'?!;:‹›«»„“”‚‘’";
		$this->span_depth++;

		if ($this->span_depth <= $this->max_span_depth) {
			foreach ($span_tags as $f) {
				$f = preg_quote($f);
				$text = preg_replace_callback(
					"`
          (^|(?<=[\s>$pnct\(])|[{[]) # pre
          ($f)(?!$f)                 # tag
          ({$this->lc})              # atts - do not use horizontal alignment; it kills html tags within inline elements.
          (?::(\S+))?                # cite
          ([^\s$f]+|\S.*?[^\s$f\n])  # content
          ([$pnct]*)                 # end
          $f
          ($|[\[\]}<]|(?=[$pnct]{1,2}[^0-9]|\s|\)))  # tail
          `x" . $this->regex_snippets['mod'],
					array(&$this, "fSpan"),
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
	 * @param $lite     boolean|string Switch to lite mode
	 * @param $encode   string Encode input and return
	 * @param $no_image string Disables images
	 * @param $strict   boolean|string false to strip whitespace before parsing
	 * @param $rel      string Relationship attribute applied to generated links
	 * @return string Parsed $text
	 */
	public function textileThis(
		$text, $lite = '', $encode = '', $no_image = '', $strict = '', $rel = ''
	) {
		return $this->parseSpans(parent::textileThis($text, $lite, $encode, $no_image, $strict, $rel));
	}

}
