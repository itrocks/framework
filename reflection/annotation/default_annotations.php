<?php
namespace ITRocks\Framework\Reflection\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Default_True_Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Type_Annotation;

//-------------------------------------------------------------------- Parser::$default_annotations
/**
 * Known annotations that do not need a specific class
 */
Parser::$default_annotations = [

	/**
	 * @after_build_array afterBuildArray
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is built from an array representation
	 * - These methods may accept the built array as reference arguments, if needed
	 */
	Parser::T_CLASS . '@after_build_array' => Annotation::class,

	/**
	 * @after_commit afterCommit
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the written object are committed (transaction end)
	 * using any (even non-transactional) data link
	 * - These methods may accept a Data_Link as first argument, if needed
	 * - These methods may accept a Dao\Option[] as second argument, if needed
	 */
	Parser::T_CLASS . '@after_commit' => Method_Annotation::class,

	/**
	 * @after_create afterCreate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is created using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_create' => Method_Annotation::class,

	/**
	 * @after_delete afterDelete
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is deleted using a data link
	 * - These methods may return false to stop calls of others after_delete methods
	 */
	Parser::T_CLASS . '@after_delete' => Method_Annotation::class,

	/**
	 * @after_read afterRead
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is read using a data link
	 * - These methods may accept the resulting object and Dao\Option[] as arguments, if needed
	 */
	Parser::T_CLASS . '@after_read' => Method_Annotation::class,

	/**
	 * @after_transform afterTransform
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is being created from a transformation
	 * - Arguments are [$source_object]
	 * - This is an alias to @after_transform_to
	 * - This is called after @after_transform_from and before @after_transform_to
	 */
	Parser::T_CLASS . '@after_transform' => Method_Annotation::class,

	/**
	 * @after_transform_to afterTransformFrom
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object has been transformed
	 * - Arguments are [$destination_object]
	 * - This is called before @after_transform and @after_transform_to
	 */
	Parser::T_CLASS . '@after_transform_from' => Method_Annotation::class,

	/**
	 * @after_transform_to afterTransformTo
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is being created from a transformation
	 * - Arguments are [$source_object]
	 * - This is an alias to @after_transform
	 * - This is called after @after_transform_from and @after_transform
	 */
	Parser::T_CLASS . '@after_transform_to' => Method_Annotation::class,

	/**
	 * @after_update afterUpdate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is updated using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_update' => Method_Annotation::class,

	/**
	 * @after_write afterWrite
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is written using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_write' => Method_Annotation::class,

	/**
	 * @before_build_array beforeBuildArray
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is built from an array representation
	 * - These methods may accept the build array as reference argument, if needed
	 */
	Parser::T_CLASS . '@before_build_array' => Annotation::class,

	/**
	 * @before_create beforeCreate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is created using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_create' => Method_Annotation::class,

	/**
	 * @before_delete beforeDelete
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is deleted using a data link
	 * - These methods may return false to cancel the deletion of the object
	 */
	Parser::T_CLASS . '@before_delete' => Method_Annotation::class,

	/**
	 * @before_update beforeUpdate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is updated using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_update' => Method_Annotation::class,

	/**
	 * @before_write beforeWrite
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is written using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_write' => Method_Annotation::class,

	/**
	 * @before_writes beforeWrites
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the objects is written using a data link
	 * by the write controller
	 * - These methods may accept a Dao\Option[] as first argument, if needed
	 * - These methods may return false to cancel the writing of the objects by the controller
	 */
	Parser::T_CLASS . '@before_writes' => Method_Annotation::class,

	/**
	 * @data_access_control dataAccessControl
	 * This is a Multiple_Annotation : several access control callbacks can be defined
	 */
	Parser::T_CLASS . '@data_access_control' => Method_Annotation::class,

	/**
	 * @default [[\Class\Namespace\]Class_Name::]methodName
	 * Identifies a method that gets the default value for properties which type is this class
	 * The Class will be sent as an argument to this callable
	 */
	Parser::T_CLASS . '@default' => Constant_Or_Method_Annotation::class,

	/**
	 * @default_class_feature add
	 */
	Parser::T_CLASS . '@default_class_feature' => Annotation::class,

	/**
	 * @default_feature output
	 * @deprecated replace by @default_object_feature
	 */
	Parser::T_CLASS . '@default_feature' => Annotation::class,

	/**
	 * @default_object_feature output
	 */
	Parser::T_CLASS . '@default_object_feature' => Annotation::class,

	/**
	 * @default_set_feature list
	 */
	Parser::T_CLASS . '@default_set_feature' => Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated class
	 */
	Parser::T_CLASS . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @duplicate duplicateMethod
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object has been duplicated
	 */
	Parser::T_CLASS . '@duplicate' => Method_Annotation::class,

	/**
	 * @feature_local_access Class_Path[/featureName]
	 * The installation of the features will allow this local access entry
	 */
	Parser::T_CLASS . '@feature_local_access' => Annotation::class,

	/**
	 * @maintain Class_Name
	 *
	 * Declares which class should be needed by the dataset maintainer instead of current class
	 * Used for alias classes
	 */
	Parser::T_CLASS . '@maintain' => Type_Annotation::class,

	/**
	 * @on_list onList
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call on data list Dao::select() call
	 */
	Parser::T_CLASS . '@on_list' => Method_Annotation::class,

	/**
	 * @test_condition testCondition
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call in order to decide if massive tests (e.g. testEverything)
	 * should be run for this class
	 */
	Parser::T_CLASS . '@test_condition' => Method_Annotation::class,

	/**
	 * @unique property1, property2, ...
	 * Identifies a list of property that are the unique tuple of data that identify a record.
	 * Used with @link classes to allow the same object multiple times with different link property
	 * values (ie a client can have the same contract several times, with different dates)
	 */
	Parser::T_CLASS . '@unique' => List_Annotation::class,

	/**
	 * @user_remove [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
	 * Associates a feature controller to call each time a sub / linked object is removed by the final
	 * user to an input form (target of a @link Collection or link @Map).
	 * a target selector can be used to define where the result is loaded (#responses as default)
	 */
	Parser::T_CLASS . '@user_remove' => Method_Target_Annotation::class,

	/**
	 * @advice
	 * This tells everybody the method is an AOP advice
	 */
	Parser::T_METHOD . '@advice' => Boolean_Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated method
	 */
	Parser::T_METHOD . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @return string
	 * Gets the type of the returned value (as a string) and the associated documentation
	 */
	Parser::T_METHOD . '@return' => Documented_Type_Annotation::class,

	/**
	 * @return_constant [false]
	 * The result of this function is constant (false to override a parent true value)
	 */
	Parser::T_METHOD . '@return_constant' => Boolean_Annotation::class,

	/**
	 * @after_add_element afterAddElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the Collection/Map property element is added
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Write event object as a unique argument
	 */
	Parser::T_PROPERTY . '@after_add_element' => Method_Annotation::class,

	/**
	 * @before_add_element beforeAddElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the Collection/Map property element is added
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Write event object as a unique argument
	 */
	Parser::T_PROPERTY . '@before_add_element' => Method_Annotation::class,

	/**
	 * @before_remove_element beforeRemoveElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the Collection/Map property element is removed
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Delete event object as a unique argument
	 */
	Parser::T_PROPERTY . '@before_remove_element' => Method_Annotation::class,

	/**
	 * @binary
	 * Set this boolean annotation to tell that binary data will be stored into the property value
	 * The property should be of type 'string'
	 */
	Parser::T_PROPERTY . '@binary' => Boolean_Annotation::class,

	/**
	 * @block The block display
	 * The Block annotation for a property enables grouping properties into common blocks
	 * TODO LOW explain why a property could have multiple blocks
	 */
	Parser::T_PROPERTY . '@block' => List_Annotation::class,

	/**
	 * @calculated
	 * The value of this property is calculated : it will not be stored into DAO data links
	 */
	Parser::T_PROPERTY . '@calculated' => Boolean_Annotation::class,

	/**
	 * @constraint initial|set_null
	 * set_null : Force foreign key constraint to 'ON DELETE : SET NULL, ON UPDATE : UPDATE'
	 * initial : cancels any parent forced constraint using this annotation
	 */
	Parser::T_PROPERTY . '@constraint' => Annotation::class,

	/**
	 * @dao ITRocks\Framework\Dao\Mysql\Link
	 * This annotation stores the name of the Dao that should always be used for a linked object,
	 * map or collection property. Use it in conjunction with @link and @var annotations.
	 */
	Parser::T_PROPERTY . '@dao' => Annotation::class,

	/**
	 * @data key=value[, key2=value2[, key3]]
	 * Allow to store data associated with the property, from the property to the view.
	 */
	Parser::T_PROPERTY . '@data' => List_Annotation::class,

	/**
	 * @delete_constraint cascade|initial|restrict|set_null
	 * Force foreign key constraint on delete
	 * initial : cancels any parent forced delete constraint using this annotation
	 */
	Parser::T_PROPERTY . '@delete_constraint' => Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated property
	 */
	Parser::T_PROPERTY . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @duplicate [true]
	 * Identifies a property which value should be duplicated by the duplicate feature (default yes)
	 */
	Parser::T_PROPERTY . '@duplicate' => Default_True_Boolean_Annotation::class,

	/**
	 * @editor editor_name
	 * Enables online text editor (ckeditor)
	 */
	Parser::T_PROPERTY . '@editor' => Annotation::class,

	/**
	 * @empty_check [false]
	 * This property value is checked into Empty_Object and Null_Object only if true (default)
	 */
	Parser::T_PROPERTY . '@empty_check' => Default_True_Boolean_Annotation::class,

	/**
	 * @expand
	 * This property will be expanded into list / print model / etc. property lists
	 */
	Parser::T_PROPERTY . '@expand' => Boolean_Annotation::class,

	/**
	 * @immutable [false]
	 * This property value is checked into Is_Immutable : property is not part of immutable if false
	 */
	Parser::T_PROPERTY . '@immutable' => Default_True_Boolean_Annotation::class,

	/**
	 * @force_validate [false]
	 * Force validation of a linked sub-object when the object is validated
	 */
	Parser::T_PROPERTY . '@force_validate' => Boolean_Annotation::class,

	/**
	 * @link_composite
	 * Identifies a property to link to the composite object for a @link Class
	 * This is useful to declare it explicitly only if the @link Class links two identical classes
	 */
	Parser::T_PROPERTY . '@link_composite' => Boolean_Annotation::class,

	/**
	 * @override [false]
	 * This tells that the property will not have autowidth on collection view
	 */
	Parser::T_PROPERTY . '@no_autowidth' => Boolean_Annotation::class,

	/**
	 * @ordered_values [false]
	 * This tells that the property values must not be ordered (default is true)
	 */
	Parser::T_PROPERTY . '@ordered_values' => Boolean_Annotation::class,

	/**
	 * @output serialized
	 * The serialized value of the property value will be displayed and edited
	 */
	Parser::T_PROPERTY . '@output' => Annotation::class,

	/**
	 * @override [false]
	 * This tells that the property overrides a parent public/protected property having the same name
	 */
	Parser::T_PROPERTY . '@override' => Boolean_Annotation::class,

	/**
	 * @parent_documents [false]
	 * @todo Should be into the bappli application, has this is non-standard and used here only
	 * Enable or disable parent documents hierarchy for this property
	 */
	Parser::T_PROPERTY . '@parent_documents' => Boolean_Annotation::class,

	/**
	 * @print_getter printGetPropertyName
	 * Use this value getter on print only
	 */
	Parser::T_PROPERTY . '@print_getter' => Method_Annotation::class,

	/**
	 * @read_only [false]
	 * Set this annotation to set the property in read-only mode : it can't be set
	 * Not to be confused with #User::READONLY, which enable the application to set a value
	 * into this property, but does not allow the final user to alter it through the HMI
	 */
	Parser::T_PROPERTY . '@read_only' => Boolean_Annotation::class,

	/**
	 * @replace_filter [false]
	 * The property must be used as a filter when replacing the object referenced by another object
	 */
	Parser::T_PROPERTY . '@replace_filter' => Boolean_Annotation::class,

	/**
	 * @replaces property_name
	 * This tells the framework the property replaces an existing parent property name, so the parent
	 * property and this property will point on the same reference and have a common value.
	 */
	Parser::T_PROPERTY . '@replaces' => Annotation::class,

	/**
	 * @search_range false
	 * Search range using "from-to" with - as separator is accepted by default. Set this to false
	 * in order to disable ie for properties which values often contain the - character.
	 */
	Parser::T_PROPERTY . '@search_range' => Annotation::class,

	/**
	 * @set_store_name store_name
	 * Defines the name of the automatically generated link table.
	 */
	Parser::T_PROPERTY . '@set_store_name' => Annotation::class,

	/**
	 * @show_seconds
	 * Tells that for a Date_Time we must show seconds to the user.
	 * If not (default), seconds are always hidden by Loc::dateToLocale()
	 */
	Parser::T_PROPERTY . '@show_seconds' => Boolean_Annotation::class,

	/**
	 * @show_time
	 * Tells that for a Date_Time how we must show time to the user.
	 * default / false is the same as 'auto' : time will be shown if not 00:00:00.
	 * Others values are 'always' and 'never', 'auto' can also be set.
	 */
	Parser::T_PROPERTY . '@show_time' => Annotation::class,

	/**
	 * @signed
	 * Tells that the numeric value can be negative.
	 */
	Parser::T_PROPERTY . '@signed' => Boolean_Annotation::class,

	/**
	 * @textile
	 * This tells that the property should be rendered using textile parsing
	 */
	Parser::T_PROPERTY . '@textile' => Boolean_Annotation::class,

	/**
	 * @transform [true]
	 * Identifies a property which value should be transferred to a transformed object (default yes)
	 */
	Parser::T_PROPERTY . '@transform' => Default_True_Boolean_Annotation::class,

	/**
	 * @translate [common]
	 * This property is translated : original in 'default language', use user language to translate
	 * If common : the common application translation classes / data is used
	 * If not common (true) : a specific Data_Translation class is used for data-type translations
	 */
	Parser::T_PROPERTY . '@translate' => Annotation::class,

	/**
	 * @unlocked
	 * This allows you to write objects with Dao::only('this_property'), even if the object is locked
	 */
	Parser::T_PROPERTY . '@unlocked' => Boolean_Annotation::class,

	/**
	 * @update_constraint cascade|initial|restrict|set_null
	 * Force foreign key constraint on update
	 * initial : cancels any parent forced update constraint using this annotation
	 */
	Parser::T_PROPERTY . '@update_constraint' => Annotation::class,

	/**
	 * @user_default [[\Class\Namespace\]Class_Name::]methodName
	 * Identifies a method that gets the default value for the property into forms only
	 * The Property will be sent as an argument to this callable
	 */
	Parser::T_PROPERTY . '@user_default' => Method_Annotation::class,

	/**
	 * @user_empty_value false
	 * For @ var string with @ values, set this false to disable the user choice of an empty value,
	 * but an empty value can still be stored
	 */
	Parser::T_PROPERTY . '@user_empty_value' => Default_True_Boolean_Annotation::class,

	/**
	 * @user_getter [Vendor\Module\Class_Name::]methodName
	 */
	Parser::T_PROPERTY . '@user_getter' => Annotation::class,

	/**
	 * @widget_class class_name[, ...]
	 */
	Parser::T_PROPERTY . '@widget_class' => List_Annotation::class,

];
