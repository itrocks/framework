<?php
namespace ITRocks\Framework\Layout\Print_Model\Filtered_By_Features;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option\Group_By;
use ITRocks\Framework\Dao\Option\Having;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\RAD\Feature\Status;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Your print model are filtered by features
 *
 * @filter filterByFeatures
 */
#[Extend(Print_Model::class)]
trait Has_Features
{

	//------------------------------------------------------------------------------------- $features
	/**
	 * @feature configureFilters Configure print model filters by feature @user
	 * @user invisible
	 * @var Feature[]
	 */
	public array $features;

	//------------------------------------------------------------------------------ filterByFeatures
	/**
	 * @noinspection PhpUnused @filter
	 * @param $options array
	 * @return array search criterion to filter the print model
	 */
	public static function filterByFeatures(array &$options) : array
	{
		if (!Group_By::in($options)) {
			$options[] = new Group_By();
		}
		$having = Having::in($options) ?: ($options[] = new Having());
		// those two 'having' conditions have an equivalent result, I kept the fastest and simplest one
		//$having->conditions['features'] = Func::haveAll(['status' => Status::INSTALLED]);
		$having->conditions[Func::groupConcat('features.status')] = [Func::isNull(), Status::INSTALLED];
		return [];
	}

}
