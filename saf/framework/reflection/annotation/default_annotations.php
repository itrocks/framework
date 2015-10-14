<?php
namespace SAF\Framework\Reflection\Annotation;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Default_True_Boolean_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use SAF\Framework\Reflection\Annotation\Template\List_Annotation;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;

//-------------------------------------------------------------------- Parser::$default_annotations
/**
 * Known annotations that do not need a specific class
 */
Parser::$default_annotations = array(

	// @after_read afterRead
	// This is a Multiple_Annotation
	// Declare one or several methods to call after the object is read using a data link
	// - These methods may accept the resulting object and Dao\Option[] as arguments, if needed
	__NAMESPACE__ . '\Class_\After_Read_Annotation' => Method_Annotation::class,

	// @after_transform afterTransform
	// This is a Multiple_Annotation
	// Declare one or several methods to call after the object is being created from a transformation
	// - These methods may accept the source object, if needed
	__NAMESPACE__ . '\Class_\After_Transform_Annotation' => Method_Annotation::class,

	// @after_write afterWrite
	// This is a Multiple_Annotation
	// Declare one or several methods to call after the object is written using a data link
	// - These methods may accept a Dao\Option[] as first argument, if needed
	__NAMESPACE__ . '\Class_\After_Write_Annotation' => Method_Annotation::class,

	// @before_build_array beforeBuildArray
	// This is a Multiple_Annotation
	// Declare one or several methods to call before the object is built from an array representation
	// - These methods may accept an array as first reference argument, if needed
	__NAMESPACE__ . '\Class_\Before_Build_Array_Annotation' => Annotation::class,

	// @before_delete beforeDelete
	// This is a Multiple_Annotation
	// Declare one or several methods to call before the object is deleted using a data link
	// - These methods may return false to cancel the deletion of the object
	__NAMESPACE__ . '\Class_\Before_Delete_Annotation' => Method_Annotation::class,

	// @before_write beforeWrite
	// This is a Multiple_Annotation
	// Declare one or several methods to call before the object is written using a data link
	// - These methods may accept a Dao\Option[] as first argument, if needed
	// - These methods may return false to cancel the writing of the object
	__NAMESPACE__ . '\Class_\Before_Write_Annotation' => Method_Annotation::class,

	// @business
	// This defines a class or a trait used to describe business objects
	__NAMESPACE__ . '\Class_\Business_Annotation' => Boolean_Annotation::class,

	// @default_feature output
	__NAMESPACE__ . '\Class_\Default_Feature_Annotation' => Annotation::class,

	// @deprecated [false]
	// Identifies a deprecated class
	__NAMESPACE__ . '\Class_\Deprecated_Annotation' => Boolean_Annotation::class,

	// @duplicate duplicateMethod
	// This is a Multiple_Annotation
	// Declare one or several methods to call after the object has been duplicated
	__NAMESPACE__ . '\Class_\Duplicate_Annotation' => Method_Annotation::class,

	// @on_data_list onDataList
	// This is a Multiple_Annotation
	// Declare one or several methods to call on data list Dao::select() call
	__NAMESPACE__ . '\Class_\On_Data_List_Annotation' => Method_Annotation::class,

	// @stored [false]
	// Identifies a class that may be stored using data links
	// When this annotation is set, this enables simplified / implicit use of @link
	// ie "@link Object" and @link DateTime" will be implicit (you won't need it)
	// ie "@link All", "@link Collection", "@link Map"
	//   will be replaced by "@var Object[] All", "@var Object[] Collection" and "@var Object[] Map"
	__NAMESPACE__ . '\Class_\Stored_Annotation' => Boolean_Annotation::class,

	// @advice
	// This tells everybody the method is an AOP advice
	__NAMESPACE__ . '\Method\Advice_Annotation' => Boolean_Annotation::class,

	// @deprecated [false]
	// Identifies a deprecated method
	__NAMESPACE__ . '\Method\Deprecated_Annotation' => Boolean_Annotation::class,

	// @return string
	// Gets the type of the returned value (as a string) and the associated documentation
	__NAMESPACE__ . '\Method\Return_Annotation' => Documented_Type_Annotation::class,

	// @binary
	// Set this boolean annotation to tell that binary data will be stored into the property value
	// The property should be of type 'string'
	__NAMESPACE__ . '\Property\Binary_Annotation' => Boolean_Annotation::class,

	// @block The block display
	// The Block annotation for a property enables grouping properties into common blocks
	// TODO LOW explain why a property could have multiple blocks
	__NAMESPACE__ . '\Property\Block_Annotation' => List_Annotation::class,

	// @calculated
	// The value of this property is calculated
	__NAMESPACE__ . '\Property\Calculated_Annotation' => Boolean_Annotation::class,

	// @component
	// The object referenced by the property is a component of the main object.
	// It should not exist without it's container.
	// TODO not sure this is used anymore
	__NAMESPACE__ . '\Property\Component_Annotation' => Boolean_Annotation::class,

	// @composite
	// Identifies a property to link to the composite object.
	// To be used into a component class only, when multiple properties link to composite class(es)
	__NAMESPACE__ . '\Property\Composite_Annotation' => Boolean_Annotation::class,

	// @conditions property_name, another_property
	// Conditions annotation declares other property names that are used to know if the property can
	// have a value
	__NAMESPACE__ . '\Property\Conditions_Annotation' => List_Annotation::class,

	// @dao SAF\Framework\Dao\Mysql\Link
	// This annotation stores the name of the Dao that should always used for a linked object,
	// map or collection property. Use it in conjunction with @link and @var annotations.
	__NAMESPACE__ . '\Property\Dao_Annotation' => Annotation::class,

	// @default [[\Class\Namespace\]Class_Name::]methodName
	// Identifies a method that gets the default value for the property
	// The Property will be sent as an argument to this callable
	__NAMESPACE__ . '\Property\Default_Annotation' => Method_Annotation::class,

	// @deprecated [false]
	// Identifies a deprecated property
	__NAMESPACE__ . '\Property\Deprecated_Annotation' => Boolean_Annotation::class,

	// @filters property_name, another_property
	// Declares other property names that are used to filter possible values of the property
	__NAMESPACE__ . '\Property\Filters_Annotation' => List_Annotation::class,

	// @length 5
	// Tells what is the wished count of characters for the value of the property
	__NAMESPACE__ . '\Property\Length_Annotation' => Annotation::class,

	// @mandatory [false]
	// Set this annotation to tell the data controllers that the property value is mandatory
	__NAMESPACE__ . '\Property\Mandatory_Annotation' => Boolean_Annotation::class,

	// @max_length 100
	// Tells what maximal count of characters can have the value of the property
	__NAMESPACE__ . '\Property\Max_Length_Annotation' => Annotation::class,

	// @max_value 40
	// Tells what is the maximal allowed value for the property
	__NAMESPACE__ . '\Property\Max_Value_Annotation' => Annotation::class,

	// @min_length 10
	// Tells what is the minimal count of characters for the value of the property
	__NAMESPACE__ . '\Property\Min_Length_Annotation' => Annotation::class,

	// @min_value -5
	// Tells what is the minimal allowed value for the property
	__NAMESPACE__ . '\Property\Min_Value_Annotation' => Annotation::class,

	// @multiline [false]
	// This tells that the string property can store multiple lines of text (default is false)
	__NAMESPACE__ . '\Property\Multiline_Annotation' => Boolean_Annotation::class,

	// @output serialized
	// The serialized value of the property value will be displayed and edited
	__NAMESPACE__ . '\Property\Output_Annotation' => Annotation::class,

	// @override [false]
	// This tells that the property overrides a parent public/protected property having the same name
	__NAMESPACE__ . '\Property\Override_Annotation' => Boolean_Annotation::class,

	// @precision 4
	// Tells how many decimals are stored/displayed on a float number, ie 0.5513 in ok in this case
	__NAMESPACE__ . '\Property\Precision_Annotation' => Annotation::class,

	// @read_only [false]
	// Set this annotation to set the property in read-only mode : it can't be set
	__NAMESPACE__ . '\Property\Read_Only_Annotation' => Boolean_Annotation::class,

	// @replace_filter [false]
	// The property must be used as a filter when replacing the object referenced by another object
	__NAMESPACE__ . '\Property\Replace_Filter_Annotation' => Boolean_Annotation::class,

	// @replaces property_name
	// This tells the framework the property replaces an existing parent property name, so the parent
	// property and this property will point on the same reference and have a common value
	__NAMESPACE__ . '\Property\Replaces_Annotation' => Annotation::class,

	// @search_range false
	// Search range using "from-to" with - as separator is accepted by default. Set this to false to
	// disable ie for properties which values often contain the - character.
	__NAMESPACE__ . '\Property\Search_Range_Annotation' => Default_True_Boolean_Annotation::class,

	// @setter [[[\Vendor\Module\]Class_Name::]methodName]
	// This is a Multiple_Annotation
	// Tells a method name that is the setter for that property.
	// The setter will be called each time the program changes the value of the property.
	__NAMESPACE__ . '\Property\Setter_Annotation' => Method_Annotation::class,

	// @signed
	// Tells that the numeric value can be negative.
	__NAMESPACE__ . '\Property\Signed_Annotation' => Boolean_Annotation::class,

	// @store serialized
	// The serialized value of the object is stored, instead of generating an objects table
	__NAMESPACE__ . '\Property\Store_Annotation' => Annotation::class,

	// @textile
	// This tells that the property should be rendered using textile parsing
	__NAMESPACE__ . '\Property\Textile_Annotation' => Boolean_Annotation::class,

	// @user_change [[\Vendor\Module\]Class_Name::]featureName]
	// associates a feature controller to call each time a property value is changed by the final user
	// to an input form
	__NAMESPACE__ . '\Property\User_Change_Annotation' => Method_Annotation::class,

	// @user_default [[\Class\Namespace\]Class_Name::]methodName
	// Identifies a method that gets the default value for the property into forms only
	// The Property will be sent as an argument to this callable
	__NAMESPACE__ . '\Property\User_Default_Annotation' => Method_Annotation::class,

	// @user_getter [Vendor\Module\Class_Name::]methodName
	__NAMESPACE__ . '\Property\User_Getter_Annotation' => Annotation::class,

);
