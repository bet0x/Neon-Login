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

$cphp_mysql_connected = false;

if(!empty($cphp_config->database->driver))
{
	if(empty($cphp_config->database->database))
	{
		die("No database was configured. Refer to the CPHP manual for instructions.");
	}
	
	if(empty($cphp_config->database->pdo))
	{
		if(mysql_connect($cphp_config->database->hostname, $cphp_config->database->username, $cphp_config->database->password))
		{
			if(mysql_select_db($cphp_config->database->database))
			{
				$cphp_mysql_connected = true;
			}
			else
			{
				die("Could not connect to the specified database. Refer to the CPHP manual for instructions.");
			}
		}
		else
		{
			die("Could not connect to the specified database server. Refer to the CPHP manual for instructions.");
		}
	}
	else
	{
		try
		{
			$database = new CachedPDO("mysql:host={$cphp_config->database->hostname};dbname={$cphp_config->database->database}", $cphp_config->database->username, $cphp_config->database->password);
			$database->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
			$cphp_mysql_connected = true;
		}
		catch (Exception $e)
		{
			die("Could not connect to the specified database. Refer to the CPHP manual for instructions.");
		}
	}
}

class CachedPDO extends PDO
{
	public function CachedQuery($query, $parameters = array(), $expiry = 60)
	{
		$query_hash = md5($query);
		$parameter_hash = md5(serialize($parameters));
		$cache_hash = $query_hash . $parameter_hash;
			
		$return_object = new stdClass;
		
		if($result = mc_get($cache_hash))
		{
			$return_object->source = "memcache";
			$return_object->data = $result;
		}
		else
		{
			$statement = $this->prepare($query);
			
			if(count($parameters) > 0)
			{
				foreach($parameters as $key => $value)
				{
					$type = $this->GuessType($value);
					
					if(is_numeric($value) && $type == PDO::PARAM_STR)
					{
						/* PDO library apparently thinks it's part of a strongly typed language and doesn't do any typecasting.
						 * We'll do it ourselves then. */
						 $value = (int) $value;
						 $type = PDO::PARAM_INT;
					}
					
					$statement->bindValue($key, $value, $type);
				}
			}
			
			$statement->execute();
			if($result = $statement->fetchAll(PDO::FETCH_ASSOC))
			{
				if(count($result) > 0)
				{
					if($expiry != 0)
					{
						mc_set($cache_hash, $result, $expiry);
					}
					
					$return_object->source = "database";
					$return_object->data = $result;
				}
				else
				{
					return false;
				}
			}
			else
			{
				/* This will fire when there are zero results AND on syntax errors. */
				return null;
			}
		}
			
		return $return_object;
	}
	
	public function GuessType($value)
	{
		if(is_int($value))
		{
			return PDO::PARAM_INT;
		}
		elseif(is_bool($value))
		{
			return PDO::PARAM_BOOL;
		}
		elseif(is_null($value))
		{
			return PDO::PARAM_NULL;
		}
		else
		{
			return PDO::PARAM_STR;
		}
	}
}
