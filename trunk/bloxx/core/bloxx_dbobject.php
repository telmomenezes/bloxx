<?php
// Bloxx - Open Source Content Management System
//
// Copyright (c) 2002 - 2005 The Bloxx Team. All rights reserved.
//
// Bloxx is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// Bloxx is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with Bloxx; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// Authors: Telmo Menezes <telmo@cognitiva.net>
//
// $Id: bloxx_dbobject.php,v 1.5 2005-08-08 16:38:34 tmenezes Exp $

require_once 'adodb/adodb.inc.php';

/**
 * This class is the Bloxx data base abstraction layer.
 * 
 * Bloxx_DBObject is the parent class of Bloxx_Module and takes care
 * of abstracting object data persistence and data base operations.
 * 
 * Bloxx_DBObject uses ADOdb.
 * 
 * @author 	Telmo Menezes <telmo@cognitiva.net>
 * @package core
 * @see 	Bloxx_Module
 */
class Bloxx_DBObject
{		
   /**
	* Constructor
 	* 
    */
	function Bloxx_DBObject($host = false)
	{		       
        $this->_condition = '';
        $this->_group_by = '';
        $this->_order_by = '';
        $this->_having = '';        
        $this->_data_select = $this->_BLOXX_MOD_PARAM['name'] . '.*';
        $this->_join = '';
		$this->_limit = false;
		
		$this->connect($host);
	}

   /**
	* Connects to the database.
 	*
 	* If an active connection already exists, do nothing. The active connection
 	* will be used.
    * 
    */	
	function connect($host)
	{
		global $DB_CONNECTION;
				
		if (!$DB_CONNECTION)
		{
			if (!$host)
			{
				$DB_CONNECTION = NewADOConnection(DATABASE_DSN);
			}
			else
			{
				$DB_CONNECTION = NewADOConnection($host);
			}
                        
			if (!$DB_CONNECTION)
			{

				global $BLOXX_ERROR;
				$BLOXX_ERROR->error('DBObject', 'connect(): ' . $DB_CONNECTION->ErrorMsg(), DATABASE_DSN);
				
				return false;                                
			}
			
			$DB_CONNECTION->SetFetchMode(ADODB_FETCH_ASSOC);
		}
		
		return true;
	}

   /**
	* Performs a database query
 	*
	* @return true on success, false otherwise.
	* 
    */	
	function query($string)
	{
		global $DB_CONNECTION;
		
		if ($this->_limit)
		{
			$this->_results = $DB_CONNECTION->SelectLimit($string,
												$this->_limit_num_rows,
												$this->_limit_offset);
		}
		else
		{		
			$this->_results = $DB_CONNECTION->Execute($string);
		}
		
		if (!$this->_results)
		{
			global $BLOXX_ERROR;			
			$BLOXX_ERROR->error('DBObject', 'query(): ' . $DB_CONNECTION->ErrorMsg(), $string);
			
			return false;
		} 		

		return true;
	}
	
   /**
	* Performs a database SELECT.
	* 
	* Uses parameters previously set by other methods.
 	*
	* @return true on success, false otherwise.
	* 
    */
	function runSelect()
	{
		$query = 'SELECT '
			. $this->_data_select
			. ' FROM '
			. $this->_BLOXX_MOD_PARAM['name']
			. ' '
			. $this->_join
			. ' '
			. $this->_condition
			. ' '
			. $this->_group_by
			. ' '
			. $this->_having
			. ' '
			. $this->_order_by;
						
		return $this->query($query);
	}
	
   /**
	* Fetches the next row in the result set.
 	*
 	* @param $lang_fields use language fields
 	*
	* @return true on success, false otherwise.
	* 
    */
	function nextRow($lang_fields = true)
	{
        		
		global $G_LANGUAGE;
        	
		if (!$this->_results)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->warning('DBObject', 'nextRow: no valid result set');
				
			return false;
		}
		
		if ($this->_results->EOF)
		{
			// Not an error, normal use causes this.			
			return false;
		}                
		
		foreach($this->_results->fields as $k => $v)
		{			        	
			$kk = str_replace(".", "_", $k);
			$kk = str_replace(" ", "_", $kk);
                
			if ($lang_fields && (substr($kk, -8, 6) == "_LANG_"))
			{

				include_once(CORE_DIR . 'bloxx_config.php');
                                
				if (substr($kk, -2, 2) == $G_LANGUAGE)
				{

					$kglobal = substr($kk, 0, -8);
					$this->$kglobal = $this->_results->fields[$k];
				}
			}
			else
			{
                
				$this->$kk = $this->_results->fields[$k];
			}
		}
		
		$this->_results->MoveNext();
		
		return true;
	}
	
   /**
	* Fetches the row with the specidied id.
	* 
	* @param $id			row id
	* @param $lang_fields 	use language fields
 	*
	* @return true on success, false otherwise.
	* 
    */
	function getRowByID($id, $all_lang_fields = false)
	{
        
		$this->clearWhereCondition();        		
		$this->insertWhereCondition('id', '=', $id);
        
		$ret = $this->runSelect();
                
		if ($ret > 0)
		{
			$this->nextRow(!$all_lang_fields);
		}
                
		return $ret;
	}
	
   /**
	* Returns the number of rows in this table.
	* 	
	* @return numer of rows, -1 on failure
	* 
    */	
	function getCount()
	{
		if (!$this->query('SELECT '
			. 'COUNT(id) as value'
			. ' FROM '
			. $this->_BLOXX_MOD_PARAM['name']
			. ' '
			. $this->_join
			. ' '
			. $this->_condition
			. ' '
			. $this->_group_by
			. ' '
			. $this->_having
			. ' '
			. $this->_order_by))
		{
			
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'getCount: error in query');

			return -1;
		}
		
		if (!$this->_results)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'getCount: no valid result set');
				
			return -1;
		}
		
		if ($this->_results->EOF)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'getCount: empty result set');
			
			return -1;
		}      

		return $this->_results->fields['value'];		
	}

   /**
	* Returns the array with the table definition.
	*
	* Function to be implemented in child classes
	*  	
	* @return array with table definition, null if it doesn't exist
	* 
    */        
	function tableDefinition()
	{
		return null;
	}

   /**
	* Clears the select WHERE condition.
	* 
    */
	function clearWhereCondition()
	{
		$this->_condition = '';
	}
    
   /**
	* Unsets all table field member variables.
	* 
    */
	function unsetAllFields()
	{
    
		$def = $this->tableDefinition();
                
		if ($def != null)
		{
        
			foreach ($def as $k => $v)
			{
        
				unset($this->$k);
			}
		}
	}
    
   /**
	* Inserts the condition that the give field is null to the query.
	* 
	* @param $field		field to test
	* @param $logic 	logical connector to use, default is AND.
	* 
    */
	function insertIsNullCondition($field, $logic = 'AND')
	{

		$cond = $field . ' IS NULL';
        
		if ($this->_condition)
		{
        
			$this->_condition .= ' ' . $logic . ' ' . $cond;
                                    
		}
        else
        {
			$this->_condition = ' WHERE ' . $cond;            
        }
	}
        
   /**
	* Inserts a condition to the query.
	* 
	* @param $field		field to test
	* @param $op	 	logical operator to use (<, >, =, <>, etc..).
	* @param $value		value to test
	* @param $logic 	logical connector to use, default is AND.
	* 
    */
	function insertWhereCondition($field, $op, $value, $logic = 'AND')
	{

		// TODO: Value must be filtered for sql injection!
		
		$def = $this->getTableDefinition();

		if (isset($def[$field]))
		{

			$fdef = $def[$field];
			$type = $fdef['TYPE'];

			if ($this->needsQuotes($type))
			{

				$value = '"' . $value . '"';
			}
		}

		$cond = $field . $op . $value;

		if ($this->_condition)
		{

			$this->_condition .= ' ' . $logic . ' ' . $cond;

			return;
		}

		$this->_condition = ' WHERE ' . $cond;
	}

   /**
	* Sets field to order by the query.
	* 
	* If called with no parameters, clears any previous order condition.
	* 
	* @param $field		field to order by
	* @param $desc	 	true = ascending, false = descending (default)
	* 
    */
	function setOrderBy($field = false, $desc = false)
	{
		$desc_cond = '';
        
		if($desc)
		{
        
			$desc_cond = ' desc ';
		}
    
		if ($field === false)
		{
                
			$this->_order_by = '';
			return;
		}		
        
		if (!$this->_order_by)
		{
        
			$this->_order_by = ' ORDER BY ' . $field . ' ' . $desc_cond;
			return;
		}
        
		$this->_order_by .= ' , ' . $field . ' ' . $desc_cond;
	}

   /**
	* Sets field to group by the query.
	* 
	* If called with no parameters, clears any previous group by condition.
	* 
	* @param $field		field to group by
	* 
    */
	function setGroupBy($field = false)
	{
		if ($field === false)
		{
                
			$this->_group_by = '';
		}        
                
		if (!$this->_group_by)
		{

			$this->_group_by = ' GROUP BY ' . $field . ' ';
			return;
		}
		$this->_group_by .= ' , ' . $field;
	}

   /**
	* Sets having condition on the query.
	* 
	* If called with no parameters, clears any previous having condition.
	* 
	* @param $having	having condition
	* 
    */
	function setHaving($having = false)
	{
		if ($having === false)
		{
        
			$this->_having = '';
			return;
		}        
        
		if (!$this->_having)
		{
        
			$this->_having = ' HAVING ' . $having . ' ';
            return;
		}
        
		$this->_having .= ' , ' . $having;                
	}

   /**
	* Sets limit on the query.
	* 
	* First parameter is the maximum number of rows to be returned by the
	* query, second parameter is the offset.
	* 
	* @param $num_rows	maximum number of rows to be returned
	* @param $offset	offset to start query by
	* 
    */
	function setLimit($num_rows, $offset = 0)
	{		       
		$this->_limit = true;
		$this->_limit_num_rows = $num_rows;
		$this->_limit_offset = $offset;        
	}
	
   /**
	* Sets join on select query.
	* 
	* Provide another DBObject to join with in the query,
	* 
	* @param $obj			object to join with
	* @param $local_key 	field to join with on the local object
	* @param $outside_key	field to join with on the outside object
	* @param $type			join type: LEFT, RIGHT or INNER (default)
	* 
    */
	function setJoin($obj, $local_key, $outside_key = 'id', $type = 'INNER')
	{		       
		$this->_join = $type
						. ' JOIN '
						. $obj->_BLOXX_MOD_PARAM['name']
						. ' ON '
						. $this->_BLOXX_MOD_PARAM['name']
						. '.'
						. $local_key
						. '='
						. $obj->_BLOXX_MOD_PARAM['name']
						. '.'
						. $outside_key;         
	}

   /**
	* Inserts row using current data in database field methods.
	* 
	* @param $set_key	use value on $this->id, do not by default (false).
	* 
	* @return inserted row id on success, -1 on failure
    */
	function insertRow($set_key = false)
	{
		if (!$set_key)
		{
			unset($this->id);
		}
                
		$items = $this->tableDefinition();
		$leftq = '';
		$rightq = '';

		foreach ($items as $k => $v)
		{        
			if ($set_key || ($k != 'id'))
			{            
                        
				if (isset($v['LANG']) && ($v['LANG'] == true))
				{
					include_module_once('language');

					$lang = new Bloxx_Language();

					$lang->clearWhereCondition();
					$lang->runSelect();

					while ($lang->nextRow())
					{
                
						$klang = $k . "_LANG_" . $lang->code;
                        
						if ((isset($this->$klang))
							&& ($this->needsQuotes($v['TYPE']) || isset($this->$klang)))
						{						
                
							if ($leftq)
							{
                                
								$leftq  .= ', ';
								$rightq .= ', ';
							}
                                
							$leftq .= "$klang ";

							if (strtolower($this->$klang) === 'null')
							{
								$rightq .= " NULL ";
							}
							else
							{
								$rightq .= $this->prepareValue($this->$klang, $v['TYPE']);
							}
						}
					}
				}
				else
				{
					if ((isset($this->$k))
						&& (($this->needsQuotes($v['TYPE'])) || ($this->$k != null)))
					{
						            
						if ($leftq)
						{                        
							$leftq  .= ', ';
							$rightq .= ', ';
						}
                        
						$leftq .= "$k ";

						if (strtolower($this->$k) === 'null')
						{
							$rightq .= " NULL ";
						}
						else
						{
							$rightq .= $this->prepareValue($this->$k, $v['TYPE']);
						}
					}
				}
			}
		}
		
		if ($leftq)
		{
			global $DB_CONNECTION;			
			
			$sql = 'INSERT INTO ' . $this->_BLOXX_MOD_PARAM['name'] . '(' . $leftq . ') VALUES (' . $rightq . ')';
			// echo $sql . '<br />';			
			$r = $DB_CONNECTION->Execute($sql);
            
			if (!$r)
			{
				echo 'insertRow()';
				global $BLOXX_ERROR;
				$BLOXX_ERROR->error('DBObject', 'insertRow(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
				return -1;
			}						
		
			if (!$set_key)
			{	
				$this->id = $DB_CONNECTION->Insert_ID();
			}
		
			// Insert blobs	
			foreach ($items as $k => $v)
			{
				// Detect all possible BLOB types in the future
				if ($v['TYPE'] == 'IMAGE')
				{										
					
					if (!$DB_CONNECTION->UpdateBlob($this->_BLOXX_MOD_PARAM['name'],
						$k,
						$this->$k,
						'id=' . $this->id))
					{
						global $BLOXX_ERROR;
						$BLOXX_ERROR->error('DBObject', 'insertRow: insert blob failed. table: ' . $this->_BLOXX_MOD_PARAM['name'] . ' row: ' . $k);
						return -1;
					}
				}
			}                        

			return $this->id;			
		}

		global $BLOXX_ERROR;
		$BLOXX_ERROR->error('DBObject', 'insertRow: no data');
		
		return -1;
	}

   /**
	* Updates current row using data in database field methods.
	* 
	* @param $lang_complete	use language specific field names.
	* 
	* @return true on success, false on failure
	* 
    */
	function updateRow($lang_complete = false)
	{    
		if ($lang_complete)
		{
			$items = $this->tableDefinitionLangComplete();
		}
		else
		{
			$items = $this->tableDefinition();
		}             

		if (!$items)
		{
			return false;
		}

		$values  = '';

		foreach ($items as $k => $v)
		{
        
			if (!isset($this->$k))
			{            
				continue;
			}
                        
			//is this a good idea?
			if ((!$this->needsQuotes($v['TYPE'])) && ($this->$k == null))
			{
				$this->$k = 0;
			}

			if ($values)
			{
				$values .= ', ';
			}

			if (strtolower($this->$k) === 'null')
			{
				$values .= $k . ' = NULL';
			}
			else
			{
				$values .= $k . '=' . $this->prepareValue($this->$k, $v['TYPE']);
			}
		}

		$this->insertWhereCondition('id', '=', $this->id);

		if ($values && $this->_condition)
		{
			global $DB_CONNECTION;
			
			$sql = 'UPDATE ' . $this->_BLOXX_MOD_PARAM['name'] . ' SET ' . $values . ' ' . $this->_condition;
			//echo $sql . '<br />';
			$result = $DB_CONNECTION->Execute($sql);
            
			if (!$result)
			{
				global $BLOXX_ERROR;
				$BLOXX_ERROR->error('DBObject', 'updateRow(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
				
				return false;
			}
			
			// Update blobs	
			foreach ($items as $k => $v)
			{
				// Detect all possible BLOB types in the future
				if ($v['TYPE'] == 'IMAGE')
				{
					if (!$DB_CONNECTION->UpdateBlob($this->_BLOXX_MOD_PARAM['name'],
						$k,
						$this->$k,
						'id=' . $this->id))
					{
						global $BLOXX_ERROR;
						$BLOXX_ERROR->error('DBObject', 'updateRow: update blob failed. table: ' . $this->_BLOXX_MOD_PARAM['name'] . ' row: ' . $k);
						return false;
					}
				}
			}			

			return true;
		}

		global $BLOXX_ERROR;
		$BLOXX_ERROR->error('DBObject', 'updateRow: no data to update');
			
		return false;
	}


   /**
	* Deletes row by it's ID.
	* 
	* @param $id	ID of row to delete
	* 
	* @return true on success, false on failure
	* 
    */
	function deleteRowByID($id)
	{
		global $DB_CONNECTION;
		
    	$sql = 'DELETE FROM ' . $this->_BLOXX_MOD_PARAM['name'] . ' WHERE id=' . $id;     
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'deleteRowByID(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}

   /**
	* Deletes rows by condition.
	* 
	* @return true on success, false on failure
	* 
    */
	function deleteRows()
	{
		global $DB_CONNECTION;
		
    	$sql = 'DELETE FROM '
    			. $this->_BLOXX_MOD_PARAM['name']
    			. ' '
    			. $this->_condition;
    			     
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'deleteRows(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}
    
   /**
	* Creates the database table for this class.
	* 
	* @return true on success, false on failure
	* 
    */
	function createTable()
	{
		$def = $this->tableDefinition();
        
		$sql = 'CREATE TABLE ' . $this->_BLOXX_MOD_PARAM['name'] . '(';
        
		foreach ($def as $k => $v)
		{
			if (isset($v['LANG']) && ($v['LANG'] == true))
			{
                
				include_module_once('language');
                        
				$lang = new Bloxx_Language();
                        
				$lang->clearWhereCondition();
				$lang->runSelect();

				while ($lang->nextRow())
				{
                        
					$sql .= $k 
						. '_LANG_'
						. $lang->code
						. ' '
						. $this->_translateType($v['TYPE'], $v['SIZE']);

					if (isset($v['NOTNULL']))
					{
						$sql .= ' NOT NULL';
					}
					if ($k == 'id')
					{
						$sql .= ' AUTO_INCREMENT';
					}
					$sql .= ',';
				}
			}
			else
			{
				$sql .= $k . ' ' . $this->_translateType($v['TYPE'], $v['SIZE']);
                
				if (isset($v['NOTNULL']))
				{
					$sql .= ' NOT NULL';
				}
				
				if ($k == 'id')
				{
					$sql .= ' AUTO_INCREMENT';
				}
				
				$sql .= ',';
			}
		}
        
		$sql .= ' PRIMARY KEY(id))';
        
        global $DB_CONNECTION;
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'createTable(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}
    
   /**
	* Adds a column to the table.
	* 
	* @param $name	column name
	* @param $def	column parameters
	* 
	* @return true on success, false on failure
	* 
    */
	function addTableColumn($name, $def)
	{
		$sql = 'ALTER TABLE ' . $this->_BLOXX_MOD_PARAM['name'] . ' ADD ' . $name;

		$sql .= ' ' . $this->_translateType($def['TYPE'], $def['SIZE']);

		if (isset($def['NOTNULL']))
		{
			$sql .= ' NOT NULL';
		}
        
        global $DB_CONNECTION;
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'addTableColumn():' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}
    
   /**
	* Remove a column from the table.
	* 
	* @param $name	column name
	* 
	* @return true on success, false on failure
	* 
    */
	function removeTableColumn($name)
	{
		$sql = 'ALTER TABLE ' . $this->_BLOXX_MOD_PARAM['name'] . ' DROP ' . $name;

		global $DB_CONNECTION;
        $result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'removeTableColumn(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}
    
   /**
	* Create database.
	* 
	* @param $db_name	Database name
	* 
	* @return true on success, false on failure
	* 
    */
	function createDatabase($db_name)
	{
		$sql = 'CREATE DATABASE ' . $db_name;

		global $DB_CONNECTION;
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'createDatabase(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}
	
	/**
	* Drop database.
	* 
	* @param $db_name	Database name
	* 
	* @return true on success, false on failure
	* 
    */
	function dropDatabase($db_name)
	{
		$sql = 'DROP DATABASE ' . $db_name;

		global $DB_CONNECTION;
		$result = $DB_CONNECTION->Execute($sql);
            
		if (!$result)
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->error('DBObject', 'dropDatabase(): ' . $DB_CONNECTION->ErrorMsg(), $sql);
			
			return false;
		}

		return true;
	}

   /**
	* Get the database type associated with the Bloxx type.
	* 
	* @param $type	Bloxx type
	* @param $size	size parameter
	* 
	* @return database type, null on failure
	* 
    */        
	function _translateType($type, $size)
	{
		if ($type == 'NUMBER')
		{
			return 'INT';
		}
		else if ($type == 'CREATORID')
		{
			return 'INT';
		}
		else if ($type == 'STRING')
		{
			return 'VARCHAR(' . $size . ')';
		}
		else if ($type == 'PASSWORD')
		{
			return 'VARCHAR(255)';
		}
		else if ($type == 'TEXT')
		{
			return 'TEXT';
		}
		else if ($type == 'HTML')
		{
			return 'TEXT';
		}
		else if ($type == 'DATETIME')
		{
			return 'BIGINT';
		}
		else if ($type == 'CREATIONDATETIME')
		{
			return 'BIGINT';
		}
		else if ($type == 'DATE')
		{
			return 'BIGINT';
		}
		else if (substr($type, 0, 6) == "BLOXX_")
		{
			return 'INT';
		}
		else if (substr($type, 0, 5) == "ENUM_")
		{
			return 'INT';
		}
		else if ($type == 'FILE')
		{
			return 'VARCHAR(255)';
		}
		else if ($type == 'IMAGE')
		{
			return 'LONGBLOB';
		}
		else if ($type == 'REMOTE_IP')
		{
			return 'VARCHAR(15)';
		}
		else
		{
			global $BLOXX_ERROR;
			$BLOXX_ERROR->warning('DBObject', '_translateType: unknown type: ' . $type);
			
			return null;
		}
	}

   /**
	* Prepare a value for inclusion in the sql query.
	* 
	* @param $value	value
	* @param $type	data type
	* 
	* @return prepared value
	* 
    */        
	function prepareValue($value, $type)
	{
		if ($this->isBlob($type))
		{
			$out = "'null'";
		}
		else if ($this->needsQuotes($type))
		{
			global $DB_CONNECTION;
						
			// Check magic_quotes stuff here
			$out = $DB_CONNECTION->qstr($value);			
		} 
		else
		{
			$out = ' ' . $value . ' ';
		}
                
		return $out;
	}

   /**
	* Determine if a certains data type needs quotes.
	* 
	* @param $type	data type
	* 
	* @return true if quotes are needed, false if not
	* 
    */        
	function needsQuotes($type)
	{    
		if(($type != 'NUMBER')
			&& ($type != 'DATETIME')
			&& ($type != 'CREATORID')
			&& ($type != 'DATE')
			&& ($type != 'CREATIONDATETIME')
			&& (substr($type, 0, 6) != "BLOXX_")
			&& (substr($type, 0, 5) != "ENUM_")){

			return true;
		}
                
		return false;
	}

   /**
	* Determine if a certains data type is a BLOB.
	* 
	* @param $type	data type
	* 
	* @return true if BLOB, false if not
	* 
    */        
	function isBlob($type)
	{ 						   
		if ($type == 'IMAGE')
		{			
			return true;
		}
                
		return false;
	}
}
?>
