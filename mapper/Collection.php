<?php
namespace SAF\Framework\Mapper;

/**
 * A collection is an array of objects that are a component of the container object
 *
 * This means that each object of a collection should not exist without its container object.
 * They may be linked to the same datalink, and must have the same class.
 */
class Collection extends Map
{

}
