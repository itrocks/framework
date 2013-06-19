<?php
namespace SAF\Framework;

/**
 * The sort annotation for classes stores a list of column names for object collections sort
 *
 * This is used by Dao to get default sort orders when calling Dao::readAll() and Dao::search().
 * This work like Class_Representative_Annotation : default values are the complete properties list
 */
class Class_Sort_Annotation extends Class_Representative_Annotation
{

}
