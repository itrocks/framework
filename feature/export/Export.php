<?php
namespace ITRocks\Framework\Feature\Export;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Option\Translate;
use ITRocks\Framework\Feature\List_\Selection;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Integrated_Properties;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Encryption;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Names;

/**
 * Export feature
 */
class Export
{

	//------------------------------------------------------------------------------- $all_properties
	public bool $all_properties = false;

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//------------------------------------------------------------------------------------ $selection
	public Selection $selection;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocSignatureInspection $object object|Parameters is informative for doc
	 * @param $class_name string
	 * @param $object     object|Parameters object or Parameters with a main object
	 * @param $form       array
	 */
	public function __construct(string $class_name, object $object, array $form)
	{
		$this->class_name = $class_name;
		$this->selection  = new Selection($object, $form);
	}

	//--------------------------------------------------------------------------------- allProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Interfaces\Reflection_Property[] The key is the property path
	 */
	protected function allProperties() : array
	{
		$already    = [];
		$class_name = Builder::className(Names::setToClass($this->class_name));
		$properties = [];
		foreach ((new Integrated_Properties)->expandUsingClassName($class_name) as $property) {
			$keep_property   = $property;
			$export_property = true;
			while (true) {
				$link_annotation  = Link_Annotation::of($property);
				$store_annotation = Store::of($property);
				if (
					$property->isStatic()
					|| $link_annotation->isCollection()
					|| $link_annotation->isMap()
					|| $store_annotation->isFalse()
					|| $store_annotation->isGz()
					|| User::of($property)->has(User::INVISIBLE)
				) {
					$already[$property->path] = $export_property = false;
					break;
				}
				if (str_contains($property->path, DOT)) {
					/** @noinspection PhpUnhandledExceptionInspection valid */
					$property = new Reflection_Property($class_name, lLastParse($property->path, DOT));
					if (isset($already[$property->path])) {
						$export_property = $already[$property->path];
						break;
					}
				}
				else {
					break;
				}
			}
			if ($export_property) {
				$property = $keep_property;
				$position = 0;
				while ($position = strpos($property->path, DOT, $position + 1)) {
					$already[substr($keep_property->path, 0, $position)] = true;
				}
				$properties[$property->path] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------------------- export
	public function export() : string
	{
		$selection_properties = $this->all_properties ? $this->allProperties() : [];
		$property_names       = array_keys($selection_properties) ?: null;
		$data                 = $this->selection->readDataSelect($property_names, null, new Translate());
		$properties           = $data->getProperties();

		// create temporary file
		$application   = Session::current()->get(Application::class);
		$tmp           = $application->getTemporaryFilesPath();
		$short_class   = Names::classToProperty($this->class_name);
		$csv_file_name = tempnam($tmp, $short_class . '_') . '.csv';
		$file          = fopen($csv_file_name, 'w');

		// format columns
		$bool_val = [0 => Loc::tr('no'), 1 => Loc::tr('yes')];
		foreach ($properties as $property_path => $property) {
			if ($property instanceof Reflection_Property) {
				if ($property->getType()->isBoolean()) {
					$booleans[$property_path] = true;
				}
				if ($property->getType()->isDateTime()) {
					$date_times[$property_path] = true;
				}
				if ($property->getListAnnotation('values')->values()) {
					$translate[$property_path] = true;
				}
				if (Encrypt_Annotation::of($property)->value === Encryption::SENSITIVE_DATA) {
					if (Sensitive_Data::isPasswordGlobalAndValid()) {
						$sensitive_data[$property_path] = true;
					}
					else {
						unset($properties[$property_path]);
						$data->removeProperty($property_path);
					}
				}
			}
		}

		// write first line (properties path)
		$row = [];
		if ($selection_properties) {
			foreach ($properties as $property) {
				$row[] = Loc::tr($property->path);
			}
		}
		else {
			$selection_properties = $this->selection->getListSettings()->properties;
			foreach ($selection_properties as $property) {
				if (isset($properties[$property->path])) {
					$row[] = $property->title();
				}
			}
		}
		fputcsv($file, $row);

		// format columns
		$bool_val = [0 => Loc::tr('no'), 1 => Loc::tr('yes')];
		foreach ($properties as $property_path => $property) {
			if ($property instanceof Reflection_Property) {
				if ($property->getType()->isBoolean()) {
					$booleans[$property_path] = true;
				}
				if ($property->getType()->isDateTime()) {
					$date_times[$property_path] = true;
				}
				if (Values::of($property)?->values) {
					$translate[$property_path] = true;
				}
			}
		}

		// write data
		$min_max_dates = [Date_Time::min()->toISO(false), Date_Time::max()->toISO(false)];
		foreach ($data->getRows() as $row) {
			$write = [];
			foreach ($row->getValues() as $property_path => $value) {
				if (isset($date_times[$property_path])) {
					if (in_array($value, $min_max_dates)) {
						$value = null;
					}
					elseif (str_ends_with($value, '00:00:00')) {
						$value = substr($value, 8, 2) . SL . substr($value, 5, 2) . SL . substr($value, 0, 4);
					}
					else {
						$value = substr($value, 8, 2) . SL . substr($value, 5, 2) . SL . substr($value, 0, 4)
						. SP . substr($value, 11, 2) . ':' . substr($value, 14, 2) . ':' . substr($value, 17, 2);
					}
				}
				elseif (isset($booleans[$property_path])) {
					$value = $value ? $bool_val[1] : $bool_val[0];
				}
				elseif (isset($translate[$property_path])) {
					$value = Loc::tr($value);
				}
				if (isset($sensitive_data[$property_path])) {
					$value = (new Sensitive_Data)->decrypt(strval($value), $properties[$property_path]);
				}
				$write[] = str_replace(DQ, Q . Q, strval($value));
			}
			fputcsv($file, $write);
		}

		// done
		fclose($file);

		// simple convert to XLSX using gnumeric
		$xlsx_file_name = str_replace('.csv', '.xlsx', $csv_file_name);
		exec(
			'ssconvert --import-encoding=UTF8'
			. SP . DQ . $csv_file_name . DQ
			. SP . DQ . $xlsx_file_name . DQ
			. SP . '2>&1 &'
		);

		// download
		$output = file_get_contents($xlsx_file_name);
		unlink($xlsx_file_name);
		unlink($csv_file_name);
		Files::downloadOutput(($short_class . '.xlsx'), 'xlsx', strlen($output));

		return $output;
	}

}
