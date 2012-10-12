<?php
/*
 * CPHP is more free software. It is licensed under the WTFPL, which
 * allows you to do pretty much anything with it, without having to
 * ask permission. Commercial use is allowed, and no attribution is
 * required. We do politely request that you share your modifications
 * to benefit other developers, but you are under no enforced
 * obligation to do so :)
 * 
 * Please read the accompanying LICENSE document for the full WTFPL
 * licensing text.
 */

if($_CPHP !== true) { die(); }

abstract class CPHPDatabaseRecordClass extends CPHPBaseClass
{
	public $fill_query = "";
	public $verify_query = "";
	public $table_name = "";
	public $query_cache = 60;
	public $id_field = "Id";
	public $autoloading = true;
	
	public $prototype = array();
	public $prototype_render = array();	
	public $prototype_export = array();
	public $uData = array();
	
	public $sId = 0;
	
	public function __construct($uDataSource, $defaultable = null)
	{
		global $cphp_config;
		
		if(!isset($cphp_config->class_map))
		{
			die("No class map was specified. Refer to the CPHP manual for instructions.");
		}
		
		$this->ConstructDataset($uDataSource, $defaultable);
		$this->EventConstructed();
	}
	
	public function __get($name)
	{
		if($name[0] == "s" || $name[0] == "u")
		{
			$actual_name = substr($name, 1);
			
			$found = false;
			
			foreach($this->prototype as $type => $dataset)
			{
				if(isset($dataset[$actual_name]))
				{
					$found = true;
					$found_type = $type;
					$found_field = $dataset[$actual_name];
				}
			}
			
			if($found === false)
			{
				$classname = get_class($this);
				throw new PrototypeException("The {$actual_name} variable was not found in the prototype of the {$classname} class.");
			}
			
			$this->SetField($found_type, $actual_name, $found_field);
			
			return $this->$name;
		}
	}
	
	public function RefreshData()
	{
		$this->PurgeCache();
		$this->ConstructDataset($this->sId);
		
		if($this->autoloading === true)
		{
			$this->PurgeVariables();
		}
	}
	
	public function PurgeVariables()
	{
		foreach($this->prototype as $type => $dataset)
		{
			foreach($dataset as $field)
			{
				$variable_name_safe = "s" . $field;
				$variable_name_unsafe = "u" . $field;
				unset($this->$variable_name_safe);
				unset($this->$variable_name_unsafe);
			}
		}
	}
	
	public function ConstructDataset($uDataSource, $defaultable = null)
	{
		global $database;
		
		$bind_datasets = true;
		
		if(is_numeric($uDataSource))
		{
			if($uDataSource != 0)
			{
				if(!empty($this->fill_query))
				{
					$this->sId = (is_numeric($uDataSource)) ? $uDataSource : 0;
					
					if(strpos($this->fill_query, " :") === false)
					{
						/* Use mysql_* to fetch the object from the database. */
						$query = sprintf($this->fill_query, $uDataSource);
						if($result = mysql_query_cached($query, $this->query_cache))
						{
							$uDataSource = $result->data[0];
						}
						else
						{
							$classname = get_class($this);
							throw new NotFoundException("Could not locate {$classname} {$uDataSource} in database.", 0, null, "");
						}
					}
					else
					{
						/* Use PDO to fetch the object from the database. */
						if($result = $database->CachedQuery($this->fill_query, array(":Id" => $this->sId), $this->query_cache))
						{
							$uDataSource = $result->data[0];
						}
						else
						{
							$classname = get_class($this);
							throw new NotFoundException("Could not locate {$classname} {$uDataSource} in database.", 0, null, "");
						}
					}
				}
				else
				{
					$classname = get_class($this);
					throw new PrototypeException("No fill query defined for {$classname} class.");
				}
			}
			else
			{
				$bind_datasets = false;
				$this->FillDefaults();
			}
		}
		elseif(is_object($uDataSource))
		{
			if(isset($uDataSource->data[0]))
			{
				$uDataSource = $uDataSource->data[0];
			}
			else
			{
				throw new NotFoundException("No result set present in object.");
			}
		}
		elseif(is_array($uDataSource))
		{
			if(isset($uDataSource[0]))
			{
				$uDataSource = $uDataSource[0];
			}
		}
		else
		{
			$classname = get_class($this);
			throw new ConstructorException("Invalid type passed on to constructor for object of type {$classname}.");
		}
		
		if($bind_datasets === true)
		{
			$this->sId = (is_numeric($uDataSource[$this->id_field])) ? $uDataSource[$this->id_field] : 0;
			
			$this->uData = $uDataSource;
			
			if($this->autoloading === false)
			{
				foreach($this->prototype as $type => $dataset)
				{
					$this->BindDataset($type, $dataset, $defaultable);
				}
			}
			
			$this->sFound = true;
		}
		else
		{
			$this->sFound = false;
		}
	}
	
	public function BindDataset($type, $dataset, $defaultable)
	{
		global $cphp_config;
		
		if(is_array($dataset))
		{
			foreach($dataset as $variable_name => $column_name) 
			{
				$this->SetField($type, $variable_name, $column_name);
			}
		}
		else
		{
			$classname = get_class($this);
			throw new Exception("Invalid dataset passed on to {$classname}.BindDataset."); 
		}
	}
	
	public function SetField($type, $variable_name, $column_name)
	{
		global $cphp_config;
		
		if(!isset($this->uData[$column_name]))
		{
			throw new Exception("The column name {$column_name} was not found in the resultset - ensure the prototype corresponds to the table schema.");
		}
		
		$original_value = $this->uData[$column_name];
		
		switch($type)
		{
			case "string":
				$value = htmlspecialchars(stripslashes($original_value));
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "html":
				$value = filter_html(stripslashes($original_value));
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "simplehtml":
				$value = filter_html_strict(stripslashes($original_value));
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "nl2br":
				$value = nl2br(htmlspecialchars(stripslashes($original_value)), false);
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "numeric":
				$value = (is_numeric($original_value)) ? $original_value : 0;
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "timestamp":
				$value = unix_from_mysql($original_value);
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "boolean":
				$value = (empty($original_value)) ? false : true;
				$variable_type = CPHP_VARIABLE_SAFE;
				break;
			case "none":
				$value = $original_value;
				$variable_type = CPHP_VARIABLE_UNSAFE;
				break;
			default:
				$found = false;
				foreach(get_object_vars($cphp_config->class_map) as $class_type => $class_name)
				{
					if($type == $class_type)
					{
						try
						{
							$value = new $class_name($original_value);
						}
						catch (NotFoundException $e)
						{
							$e->field = $variable_name;
							throw $e;
						}
						$variable_type = CPHP_VARIABLE_SAFE;
						$found = true;
					}
				}
				
				if($found == false)
				{
					$classname = get_class($this);
					throw new Exception("Cannot determine type of dataset ({$type}) passed on to {$classname}.BindDataset."); 
					break;
				}
		}
		
		if($variable_type == CPHP_VARIABLE_SAFE)
		{
			$variable_name_safe = "s" . $variable_name;
			$this->$variable_name_safe = $value;
		}
		
		$variable_name_unsafe = "u" . $variable_name;
		$this->$variable_name_unsafe = $original_value;
	}
	
	public function FillDefaults()
	{
		foreach($this->prototype as $type => $dataset)
		{
			switch($type)
			{
				case "string":
				case "simplehtml":
				case "html":
				case "nl2br":
				case "none":
					$safe_default_value = "";
					$unsafe_default_value = "";
					break;
				case "numeric":
					$safe_default_value = 0;
					$unsafe_default_value = "0";
					break;
				case "boolean":
					$safe_default_value = false;
					$unsafe_default_value = "0";
					break;
				case "timestamp":
					$safe_default_value = 0;
					$unsafe_default_value = "1970-01-01 12:00:00";
					break;
				default:
					continue 2;
			}
			
			foreach($dataset as $property)
			{
				$safe_variable_name = "s" . $property;
				$this->$safe_variable_name = $safe_default_value;
				
				$unsafe_variable_name = "u" . $property;
				$this->$unsafe_variable_name = $unsafe_default_value;
			}
		}
	}
	
	public function DoRenderInternalTemplate()
	{
		if(!empty($this->render_template))
		{
			$strings = array();
			foreach($this->prototype_render as $template_var => $object_var)
			{
				$variable_name = "s" . $object_var;
				$strings[$template_var] = $this->$variable_name;
			}
			return $this->DoRenderTemplate($this->render_template, $strings);
		}
		else
		{
			$classname = get_class($this);
			throw new Exception("Cannot render template: no template defined for {$classname} class.");
		}
	}
	
	public function InsertIntoDatabase()
	{
		global $cphp_config, $database;
		
		if(!empty($this->verify_query))
		{
			if($this->sId == 0)
			{
				$insert_mode = CPHP_INSERTMODE_INSERT;
			}
			else
			{
				/* Temporary implementation to make old style queries play nice with PDO code. */
				if(strpos($this->verify_query, ":Id") !== false)
				{
					$this->verify_query = str_replace(":Id", "'%d'", $this->verify_query);
				}
				
				$query = sprintf($this->verify_query, $this->sId);
				if($result = mysql_query_cached($query, 0))
				{
					$insert_mode = CPHP_INSERTMODE_UPDATE;
				}
				else
				{
					$insert_mode = CPHP_INSERTMODE_INSERT;
				}
			}
			
			$element_list = array();
			
			foreach($this->prototype as $type_key => $type_value)
			{
				foreach($type_value as $element_key => $element_value)
				{
					switch($type_key)
					{
						case "none":
						case "numeric":
						case "boolean":
						case "timestamp":
						case "string":
							$element_list[$element_value] = array(
								'key'	=> $element_key,
								'type'	=> $type_key
							);
							break;
						default:
							break;
					}
				}
			}
			
			$sKeyList = array();
			$sValueList = array();
			
			foreach($element_list as $sKey => $value)
			{				
				$variable_name_safe = "s" . $value['key'];
				$variable_name_unsafe = "u" . $value['key'];
				
				if(isset($this->$variable_name_safe) || isset($this->$variable_name_unsafe))
				{
					switch($value['type'])
					{
						case "none":
							$sFinalValue = mysql_real_escape_string($this->$variable_name_unsafe);
							break;
						case "numeric":
							$number = (isset($this->$variable_name_unsafe)) ? $this->$variable_name_unsafe : $this->$variable_name_safe;
							$sFinalValue = (is_numeric($number)) ? $number : 0;
							break;
						case "boolean":
							$bool = (isset($this->$variable_name_unsafe)) ? $this->$variable_name_unsafe : $this->$variable_name_safe;
							$sFinalValue = ($bool) ? "1" : "0";
							break;
						case "timestamp":
							if(is_numeric($this->$variable_name_unsafe))
							{
								$sFinalValue = mysql_from_unix($this->$variable_name_unsafe);
							}
							else
							{
								if(isset($this->$variable_name_safe))
								{
									$sFinalValue = mysql_from_unix($this->$variable_name_safe);
								}
								else
								{
									$sFinalValue = mysql_from_unix(unix_from_local($this->$variable_name_unsafe));
								}
							}
							break;
						case "string":
							$sFinalValue = (isset($this->$variable_name_unsafe)) ? mysql_real_escape_string($this->$variable_name_unsafe) : mysql_real_escape_string($this->$variable_name_safe);
							break;
						case "default":
							$sFinalValue = mysql_real_escape_string($this->$variable_name_unsafe);
							break;
					}
					
					$sFinalValue = "'{$sFinalValue}'";
					$sKey = "`{$sKey}`";
					
					$sKeyList[] = $sKey;
					$sValueList[] = $sFinalValue;
				}
				else
				{
					if($this->autoloading === false)
					{
						$classname = get_class($this);
						throw new Exception("Database insertion failed: prototype property {$value['key']} not found in object of type {$classname}.");
					}
				}
			}
			
			
			if($insert_mode == CPHP_INSERTMODE_INSERT)
			{
				$sQueryKeys = implode(", ", $sKeyList);
				$sQueryValues = implode(", ", $sValueList);
				$query = "INSERT INTO {$this->table_name} ({$sQueryKeys}) VALUES ({$sQueryValues})";
			}
			elseif($insert_mode == CPHP_INSERTMODE_UPDATE)
			{
				$sKeyValueList = array();
				
				for($i = 0; $i < count($sKeyList); $i++)
				{
					$sKey = $sKeyList[$i];
					$sValue = $sValueList[$i];
					$sKeyValueList[] = "{$sKey} = {$sValue}";
				}
				
				$sQueryKeysValues = implode(", ", $sKeyValueList);
				$query = "UPDATE {$this->table_name} SET {$sQueryKeysValues} WHERE `{$this->id_field}` = '{$this->sId}'";
			}
			
			if($result = mysql_query_cached($query, 0, "", true))
			{
				if($insert_mode == CPHP_INSERTMODE_INSERT)
				{
					/* Temporary PDO implementation. */
					if(!empty($cphp_config->database->pdo))
					{
						$this->sId = $database->lastInsertId();
					}
					else
					{
						$this->sId = mysql_insert_id();
					}
				}
				
				$this->RefreshData();
				
				return $result;
			}
			else
			{
				$classname = get_class($this);
				var_dump($database->errorInfo());
				throw new DatabaseException("Database insertion query failed in object of type {$classname}. Error message: " . mysql_error());
			}
		}
		else
		{
			$classname = get_class($this);
			throw new Exception("No verification query defined for {$classname} class.");
		}
	}
	
	public function RetrieveChildren($type, $field)
	{
		// Not done yet!
		
		if(!isset($cphp_config->class_map->$type))
		{
			$classname = get_class($this);
			throw new NotFoundException("Non-existent 'type' argument passed on to {$classname}.RetrieveChildren function.");
		}
		
		$parent_type = get_parent_class($cphp_config->class_map->$type);
		if($parent_type !== "CPHPDatabaseRecordClass")
		{
			$parent_type = ($parent_type === false) ? "NONE" : $parent_type;
			$classname = get_class($this);
			throw new TypeException("{$classname}.RetrieveChildren expected 'type' argument of parent-type CPHPDatabaseRecordClass, but got {$parent_type} instead.");
		}
		
		$query = "";
	}
	
	public function PurgeCache()
	{
		if(strpos($this->fill_query, ":Id") !== false)
		{
			$fill_query = str_replace(":Id", "'%d'", $this->fill_query);
		}
		else
		{
			$fill_query = $this->fill_query;
		}
		
		$query = sprintf($fill_query, $this->sId);
		$key = md5($query) . md5($query . "x");
		
		$query_hash = md5($this->fill_query);
		$parameter_hash = md5(serialize(array(':Id' => (int) $this->sId)));
		$pdo_key = $query_hash . $parameter_hash;
		
		mc_delete($key);
		mc_delete($pdo_key);
	}
	
	public function RenderTemplate($template = "")
	{
		if(!empty($template))
		{
			$this->render_template = $template;
		}
		
		return $this->DoRenderInternalTemplate();
	}
	
	public function Export()
	{
		// Exports the object as a nested array. Observes the export prototype.
		$export_array = array();
		
		foreach($this->prototype_export as $field)
		{
			$variable_name = "s{$field}";
			if(is_object($this->$variable_name))
			{
				if(!empty($this->$variable_name->sId))
				{
					$export_array[$field] = $this->$variable_name->Export();
				}
				else
				{
					$export_array[$field] = null;
				}
			}
			else
			{
				$export_array[$field] = $this->$variable_name;
			}
		}
		
		return $export_array;
	}
	
	// Define events
	
	protected function EventConstructed() { }
}
