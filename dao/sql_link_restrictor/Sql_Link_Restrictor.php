<?php
namespace SAF\Framework\Dao;

use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Sql\Builder\Select;
use SAF\Framework\Sql\Builder\Where;

/**
 * This configuration plugin enables to restrict read data
 *
 * TODO this has not been tested nor used yet, please finish and test this !
 */
class Sql_Link_Restrictor implements Registerable
{

	//--------------------------------------------------------------------------------------- CURRENT
	/**
	 * Current callback restrict objects to their Class_Name::current() value
	 * (if no current value, restriction is full and nothing could be read)
	 */
	const CURRENT = 'current';

	//------------------------------------------------------------------------- $current_restrictions
	/**
	 * Stores current restrictions queries : set by restrict() and used by applyCurrentRestrictions()
	 *
	 * Each where starts with ' WHERE '
	 *
	 * @var string[]
	 */
	private $current_restrictions;

	//--------------------------------------------------------------------------------- $restrictions
	/**
	 * Associate classes names and multiple restriction callbacks
	 *
	 * This is given by configuration
	 *
	 * @var array
	 */
	private $restrictions = [];

	//--------------------------------------------------------------------------- $final_restrictions
	/**
	 * Associate classes names and multiple restriction callbacks
	 *
	 * This is an internal cache to avoid repetitive searches into parent classes
	 *
	 * @var array associate classes and callbacks
	 */
	private $final_restrictions = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a Sql_link restrictor object
	 *
	 * $restrictions are a list of class names as keys, associated to callback restriction functions
	 * Those callbacks must be static public methods accepting two arguments :
	 * - the class name which the callback is associated to into Sql_Link_Restrictor
	 * - a Sql_Joins object giving the restrictor full paths for searches and where it can add joins
	 *
	 * @param $restrictions array
	 */
	public function __construct($restrictions = null)
	{
		if (isset($restrictions)) $this->restrictions = $restrictions;
	}

	//---------------------------------------------------------------------- applyCurrentRestrictions
	/**
	 * Applies current restrictions set by last call to restrict() to a SQL 'WHERE' clause
	 *
	 * @param $where string empty or begins where ' WHERE '
	 * @return string full SQL 'WHERE' clause, including $where and added restrictions
	 */
	private function applyCurrentRestrictions($where)
	{
		$sql = join(') AND (', $this->current_restrictions);
		return $sql
			? (LF . 'WHERE ' . ($where ? '(' . substr($where, 7) . ') AND ' : '') . '(' . $sql . ')')
			: $where;
	}

	//------------------------------------------------------------------------------ applyRestriction
	/**
	 * Apply a restriction to the builder
	 *
	 * @param $builder     Select
	 * @param $class_name  string
	 * @param $restriction string|array a restriction callback
	 */
	private function applyRestriction(Select $builder, $class_name, $restriction)
	{
		if ($restriction == self::CURRENT) {
			$restriction = [$class_name, 'current'];
		}
		$where_array = call_user_func_array($restriction, [$class_name, $builder->getJoins()]);
		if ($where_array) {
			$where_builder = new Where(
				$builder->getJoins()->getStartingClassName(),
				$where_array,
				$builder->getSqlLink(),
				$builder->getJoins()
			);
			$this->current_restrictions[] = $where_builder->build();
		}
	}

	//---------------------------------------------------------------- beforeSqlSelectBuilderFinalize
	/**
	 * @param $where string where clause, including ' WHERE ' or empty if no filter on read
	 */
	public function beforeSqlSelectBuilderFinalize(&$where)
	{
		$where = $this->applyCurrentRestrictions($where);
	}

	//------------------------------------------------------------- beforeSqlSelectBuilderBuildTables
	/**
	 * @param $object Select
	 */
	public function beforeSqlSelectBuilderBuildTables(Select $object)
	{
		$this->restrict($object);
	}

	//------------------------------------------------------------------------------- getRestrictions
	/**
	 * Gets restrictions list for a given class name
	 *
	 * @param $class_name string
	 * @return array
	 */
	private function getRestrictions($class_name)
	{
		if (isset($this->final_restrictions[$class_name])) {
			$restrictions = $this->final_restrictions[$class_name];
		}
		else {
			$restrictions = [];
			foreach (classTree($class_name) as $tree_class_name) {
				if (isset($this->restrictions[$tree_class_name])) {
					$restrictions = array_merge($restrictions, $this->restrictions[$tree_class_name]);
				}
			}
			$this->final_restrictions[$class_name] = $restrictions;
		}
		return $restrictions;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers SQL link restrictor plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		/* @todo this method does not exist anymore into Sql_Select_Builder
		$aop->beforeMethod(
			['SAF\Framework\Sql_Select_Builder', 'buildTables'),
			[__CLASS__, 'beforeSqlSelectBuilderBuildTables')
		);
		*/
		$aop->beforeMethod([Select::class, 'finalize'], [$this, 'beforeSqlSelectBuilderFinalize']);
	}

	//-------------------------------------------------------------------------------------- restrict
	/**
	 * Sets current restriction using $builder's joins foreign classes
	 *
	 * If current restrictions exist before call of restrict(), they are reset by this call.
	 *
	 * @param $builder Select
	 */
	private function restrict(Select $builder)
	{
		$this->current_restrictions = [];
		if ($this->restrictions) {
			foreach ($builder->getJoins()->getJoins() as $join) {
				if (isset($join->foreign_class)) {
					$restrictions = $this->getRestrictions($join->foreign_class);
					foreach ($restrictions as $restriction) {
						$this->applyRestriction($builder, $join->foreign_class, $restriction);
					}
				}
			}
		}
	}

}
