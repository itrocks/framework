<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Mysql\Index;
use ITRocks\Framework\Dao\Sql\Table;

/**
 * SQL create table queries builder
 */
class Create_Table
{

	//---------------------------------------------------------------------------------------- $table
	/**
	 * @var Table
	 */
	private $table;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $table Table
	 */
	public function __construct(Table $table)
	{
		$this->table = $table;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * To create a table with foreign keys, we need multiple queries.
	 * This method Returns all necessary queries : CREATE TABLE, then ALTER TABLE ... ADD CONSTRAINT.
	 *
	 * @param $skip_constraint boolean If true skip constraint creation
	 * @return string[]
	 */
	public function build($skip_constraint = false)
	{
		$columns = [];
		foreach ($this->table->getColumns() as $column) {
			$columns[] = $column->toSql();
		}
		$indexes = [];
		foreach ($this->table->getIndexes() as $index) {
			$indexes[$index->getName()] = $index->toSql();
		}
		$foreign_keys = [];
		if (!$skip_constraint) {
			foreach ($this->table->getForeignKeys() as $foreign_key) {
				$foreign_key_constraint                = join(DOT, $foreign_key->getFields());
				$foreign_keys[$foreign_key_constraint] = $foreign_key->toSql();
				if (!isset($indexes[$foreign_key_constraint])) {
					$indexes[$foreign_key_constraint] = Index::buildLink($foreign_key_constraint)->toSql();
				}
			}
		}
		$queries[] = 'CREATE TABLE' . ' IF NOT EXISTS ' . BQ . $this->table->getName() . BQ . ' ('
			. ($columns ? (LF . TAB) : '') . join(',' . LF . TAB, $columns)
			. ($indexes ? (',' . LF . TAB) : '') . join(',' . LF . TAB, $indexes)
			. LF . ') DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci';
		foreach ($foreign_keys as $foreign_key) {
			$queries[] = 'ALTER TABLE' . SP . BQ . $this->table->getName() . BQ . ' ADD ' . $foreign_key;
		}
		return $queries;
	}

}
