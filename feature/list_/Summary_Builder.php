<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Sql\Link;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Date_Format;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Sql\Builder;
use ITRocks\Framework\Sql\Join\Joins;
use ITRocks\Framework\Sql\Value;
use ITRocks\Framework\Tools\Date_Time;
use ReflectionException;

/**
 * The Summary section of search filter on a List
 */
class Summary_Builder
{

	//------------------------------------------------------------------------------- translate flags
	/**
	 * COMPLETE_TRANSLATE: get both translation delimiters for translation text
	 * MAIN_TRANSLATE:     get main translation delimiter to surround text
	 * NO_TRANSLATE:       want no translation surrounding delimiter at all
	 * SUB_TRANSLATE:      get sub translation delimiter to surround a sub part of text
	 */
	const COMPLETE_TRANSLATE = self::MAIN_TRANSLATE | self::SUB_TRANSLATE;
	const MAIN_TRANSLATE     = 1;
	const NO_TRANSLATE       = 0;
	const SUB_TRANSLATE      = 2;

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * @var Joins
	 */
	private $joins;

	//------------------------------------------------------------------------------------- $sql_link
	/**
	 * Sql data link used for identifiers
	 *
	 * @var Link
	 */
	private $sql_link;

	//---------------------------------------------------------------------------------- $where_array
	/**
	 * Where array expression, keys are columns names
	 *
	 * @var array|Func\Where
	 */
	private $where_array;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct the Human readable summary section of a query
	 *
	 * Supported columns naming forms are :
	 * - column_name : column_name must correspond to a property of class,
	 * - column.foreign_column : column must be a property of class, foreign_column must be a property
	 *   of column's var class.
	 *
	 * @param $class_name  string base object class name
	 * @param $where_array array|Func\Where where array expression, keys are columns names
	 * @param $sql_link    Link
	 * @param $joins       Joins
	 */
	public function __construct(
		$class_name, $where_array = null, Link $sql_link = null, Joins $joins = null
	) {
		$this->joins       = $joins ? $joins : new Joins($class_name);
		$this->sql_link    = $sql_link ? $sql_link : Dao::current();
		$this->where_array = $where_array;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->build();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * Build Human readable summary
	 *
	 * @return string|string[] if array, this is several clauses for an union-or.
	 */
	public function build()
	{
		$where_array = $this->where_array;
		if (($where_array instanceof Func\Logical) && $where_array->isOr()) {
			$str = [];
			foreach ($where_array->arguments as $property_path => $argument) {
				$str[] = LF . Loc::tr('where')
					. SP . $this->buildPath($property_path, $argument, Loc::tr('and'));
			}
			return implode(', ', $str);
		}
		$str = is_null($this->where_array)
			? ''
			: $this->buildPath('id', $this->where_array, Loc::tr('and'));
		return $str ? (LF . $str) : $str;
	}

	//------------------------------------------------------------------------------------ buildArray
	/**
	 * Build SQL WHERE section for multiple where clauses
	 *
	 * @param $path   string Base property path for values (if keys are numeric or structure keywords)
	 * @param $array  array An array of where conditions
	 * @param $clause string For multiple where clauses, tell if they are linked with 'OR' or 'AND'
	 * @return string
	 */
	private function buildArray($path, array $array, $clause)
	{
		$sql        = '';
		$sql_close  = '';
		$sub_clause = $clause;
		$first      = true;
		foreach ($array as $key => $value) {
			if (!is_string($value) || strlen($value)) {
				if ($first) {
					$first = false;
				}
				else {
					$sql .= SP . Loc::tr(strtolower($clause)) . SP;
				}
				$key_clause = strtoupper($key);
				if (is_numeric($key) && ($value instanceof Logical)) {
					// if logical, simply build path as if key clause was 'AND' (the simplest)
					$key_clause = 'AND';
				}
				switch ($key_clause) {
					case 'NOT':
						$sql .= Loc::tr('not') . ' (' . $this->buildPath($path, $value, 'AND') . ')';
						break;
					case 'AND':
						$sql .= $this->buildPath($path, $value, $key_clause);
						break;
					case 'OR':
						$sql .= '(' . $this->buildPath($path, $value, $key_clause) . ')';
						break;
					default:
						if (is_numeric($key)) {
							if ((count($array) > 1) && !$sql) {
								$sql       = '(';
								$clause    = 'OR';
								$sql_close = ')';
							}
							$build = $this->buildPath($path, $value, $sub_clause);
						}
						else {
							$prefix        = '';
							$master_path   = (($i = strrpos($path, DOT)) !== false) ? substr($path, 0, $i) : '';
							$property_name = ($i !== false) ? substr($path, $i + 1) : $path;
							$properties    = $this->joins->getProperties($master_path);
							if (isset($properties[$property_name])) {
								$property = $properties[$property_name];
								if (Link_Annotation::of($property)->value) {
									$prefix = ($master_path ? ($master_path . DOT) : '')
										. Store_Name_Annotation::of($property)->value . DOT;
								}
							}
							$build = $this->buildPath($prefix . $key, $value, $sub_clause);
						}
						if (!empty($build)) {
							$sql .= $build;
						}
						elseif (!empty($sql)) {
							$sql = substr($sql, 0, -strlen(SP . Loc::tr(strtolower($sub_clause)) . SP));
						}
						else {
							$first = true;
						}
				}
			}
		}
		return $sql . $sql_close;
	}

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path           string
	 * @param $prefix         string
	 * @param $translate_flag integer flag for surrounding translation chars
	 * @return string
	 */
	public function buildColumn($path, $prefix = '', $translate_flag = self::COMPLETE_TRANSLATE)
	{
		list($translation_delimiter, $sub_translation_delimiter)
			= $this->getTranslationDelimiters($translate_flag);
		return $translation_delimiter . $sub_translation_delimiter . ($prefix ? $prefix . '.' : '')
			. $path . $sub_translation_delimiter . $translation_delimiter;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build SQL WHERE section for an object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path   string Base property path pointing to the object
	 * @param $object object The value is an object, which will be used for search
	 * @return string
	 */
	private function buildObject($path, $object)
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Link_Class($object);
		$id = $this->sql_link->getObjectIdentifier(
			$object,
			Class_\Link_Annotation::of($class)->value ? $class->getCompositeProperty()->name : null
		);
		if ($id) {
			// object is linked to stored data : search with object identifier
			return $this->buildValue($path, $id, ($path == 'id') ? '' : 'id_');
		}
		// object is a search object : each property is a search entry, and must join table
		$this->joins->add($path);
		$array = [];
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class = new Reflection_Class($object);
		foreach (
			Replaces_Annotations::removeReplacedProperties($class->accessProperties())
			as $property_name => $property
		) {
			if (isset($object->$property_name)) {
				$sub_path         = $property_name;
				$array[$sub_path] = $object->$property_name;
			}
		}
		$sql = $this->buildArray($path, $array, 'AND');
		if (!$sql) {
			$sql = 'FALSE';
		}
		return $sql;
	}

	//------------------------------------------------------------------------------------- buildPath
	/**
	 * Build Human readable search section for given path and value
	 *
	 * @param $path   string|integer Property path starting by a root class property (may be a numeric key, or a structure keyword)
	 * @param $value  mixed May be a value, or a structured array of multiple where clauses
	 * @param $clause string For multiple where clauses, tell if they are linked with OR or AND
	 * @return string
	 */
	private function buildPath($path, $value, $clause)
	{
		if ($value instanceof Func\Where) {
			list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
			if ($foreign_column == 'id') {
				$prefix = '';
			}
			else {
				$properties = $this->joins->getProperties($master_path);
				$property   = isset($properties[$foreign_column]) ? $properties[$foreign_column] : null;
				$id_links   = [Link_Annotation::COLLECTION, Link_Annotation::MAP, Link_Annotation::OBJECT];
				$prefix     = $property ? (Link_Annotation::of($property)->is($id_links) ? 'id_' : '') : '';
			}
			return $value->toHuman($this, $path, $prefix);
		}
		elseif ($value instanceof Date_Time) {
			// TODO a class annotation (@business? @string?) could help choose
			$value = $value->toISO(false);
		}
		switch (gettype($value)) {
			case 'NULL':   return $this->buildColumn($path) . SP . Loc::tr('is null');
			case 'array':  return $this->buildArray ($path, $value, $clause);
			case 'object': return $this->buildObject($path, $value);
			default:       return $this->buildValue ($path, $value);
		}
	}

	//----------------------------------------------------------------------------------- buildScalar
	/**
	 * Build a scalar value to be human readable
	 *
	 * @param $value          string
	 * @param $property_path  string
	 * @param $translate_flag integer flag for surrounding translation chars
	 * @return string
	 */
	public function buildScalar($value, $property_path, $translate_flag = self::COMPLETE_TRANSLATE)
	{
		static $pattern
			= '/([0-9%_]{4})-([0-9%_]{2})-([0-9%_]{2})(?:\s([0-9%_]{2}):([0-9%_]{2}):([0-9%_]{2}))?/x';
		$property = $this->getProperty($property_path);
		// check if we are on a enum field with @values list of values
		$values = ($property ? $property->getListAnnotation('values')->values() : []);
		if (count($values)) {
			list($translation_delimiter, $sub_translation_delimiter)
				= $this->getTranslationDelimiters($translate_flag);
			return DQ . $translation_delimiter . $sub_translation_delimiter
				. str_replace('_', SP, $value) . $sub_translation_delimiter . $translation_delimiter . DQ;
		}
		elseif (preg_match($pattern, $value)) {
			// in case of a date, we convert to locale with time
			$date_format            = Loc::date();
			$show_time              = $date_format->show_time;
			$date_format->show_time = Date_Format::TIME_ALWAYS;
			$date                   = Loc::dateToLocale($value);
			$date_format->show_time = $show_time;
			return $date;
		}
		elseif ($property && is_numeric($value)) {
			$type_string = $property->getType()->asString();
			if ($type_string == Type::BOOLEAN) {
				return ($value ? Loc::tr(YES) : Loc::tr(NO));
			}
			return $value;
		}
		else {
			return DQ . $value . DQ;
		}
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * Build SQL WHERE section for a unique value
	 *
	 * @param $path   string search property path
	 * @param $value  mixed search property value
	 * @param $prefix string Prefix for column name
	 * @return string
	 */
	private function buildValue($path, $value, $prefix = '')
	{
		$column  = $this->buildColumn($path, $prefix);
		$is_like = Value::isLike($value);
		return $column . SP . ($is_like ? Loc::tr('is like') : '=') . SP . $value;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * get the property of a path
	 *
	 * @param $path string
	 * @return ?Reflection_Property
	 */
	public function getProperty(string $path) : ?Reflection_Property
	{
		// old way to do. keep for backward compatibility
		// TODO check if we should keep or if it's buggy and so we could keep only new way to do
		list($master_path, $foreign_column) = Builder::splitPropertyPath($path);
		$properties = $this->joins->getProperties($master_path);
		$property   = isset($properties[$foreign_column]) ? $properties[$foreign_column] : null;
		// if null, new way to do
		if (is_null($property)) {
			// property path can be an Expressions::MARKER or 'representative' view field name
			try {
				$property = new Reflection_Property($this->joins->getClass(''), $path);
			}
			catch (ReflectionException) {
			}
		}
		return $property;
	}

	//---------------------------------------------------------------------- getTranslationDelimiters
	/**
	 * Returns the delimiters to build a translated string according to current locale and given
	 * option flag. @see self::const documentation for accepted flags
	 *
	 * @param $translate_flag integer flag for surrounding translation chars
	 * @return string[] [translation delimiter, sub translation delimiter] @example ['|', '¦']
	 */
	public function getTranslationDelimiters($translate_flag = self::COMPLETE_TRANSLATE)
	{
		if (Locale::current()) {
			$translation_delimiter     = (($translate_flag & self::MAIN_TRANSLATE) ? '|' : '');
			$sub_translation_delimiter = (($translate_flag & self::SUB_TRANSLATE)  ? '¦' : '');
		}
		else {
			$translation_delimiter = $sub_translation_delimiter = '';
		}
		return [$translation_delimiter, $sub_translation_delimiter];
	}

}
