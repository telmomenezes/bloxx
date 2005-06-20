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
// $Id: bloxx_dbobject.php,v 1.4 2005-06-20 11:26:08 tmenezes Exp $

require_once 'DB.php';
require_once 'PEAR.php';
//require_once 'defines.php';

class Bloxx_DBObject
{

        var $name;
        var $N = 0;
        var $_database_dsn = DATABASE_DSN;
        var $_database = '';
        var $_results = '';
        var $_condition = '';
        var $_group_by = '';
        var $_order_by = '';
        var $_having = '';
        var $_limit = '';
        var $_data_select = '*';
        var $_join = '';
        
        //Abstract function to be implemented in child classes
        function tableDefinition(){}

        //telmo
        function getRowByID($id, $all_lang_fields = false)
        {
        
        		$this->clearWhereCondition();        		
                $this->insertWhereCondition('id', '=', $id);
        
                $ret = $this->runSelect();
                
                if ($ret > 0) {

                        $this->nextRow(!$all_lang_fields);
                }
                
                return $ret;
        }

        //telmo
        function runSelect()
        {
                $this->N = 0;
                $tmpcond = $this->_condition;
                $this->_query('SELECT ' .
                $this->_data_select .
                        ' FROM ' . $this->name . " " .
                $this->_join .
                $this->_condition . ' '.
           
                $this->_group_by . ' '.
                $this->_having . ' '.
                $this->_order_by . ' '.
            
                $this->_limit);

                $this->_condition = $tmpcond;

                return $this->N;
        }
        
        function getCount()
        {
                $this->N = 0;
                $tmpcond = $this->_condition;
                $this->_query('SELECT ' .
                'COUNT(id) as value' .
                        ' FROM ' . $this->name . " " .
                $this->_join .
                $this->_condition . ' '.
           
                $this->_group_by . ' '.
                $this->_having . ' '.
                $this->_order_by . ' '.
            
                $this->_limit);

                $this->_condition = $tmpcond;
                
                $result = &$this->_results;
                $array = $result->fetchRow(DB_FETCHMODE_ASSOC);

				return $array['value'];
        }

        //telmo
        function nextRow($only_selected_lang_field = true)
        {
        		
        		global $G_LANGUAGE;
        	
                if (!@$this->N) {

                        return false;
                }
                
                $result = &$this->_results;
                $array = $result->fetchRow(DB_FETCHMODE_ASSOC);

                if (!is_array($array)) {

                        return false;
                }

                foreach($array as $k => $v) {
        
                        $kk = str_replace(".", "_", $k);
                        $kk = str_replace(" ", "_", $kk);
                
                        if((substr($kk, -8, 6) == "_LANG_") && $only_selected_lang_field){

                                include_once(CORE_DIR.'bloxx_config.php');
                                
                                if(substr($kk, -2, 2) == $G_LANGUAGE){

                                        $kglobal = substr($kk, 0, -8);
                                        $this->$kglobal = $array[$k];
                                }
                        }
                        else{
                
                                $this->$kk = $array[$k];
                        }
                }
                
                if (!empty($this->_data_select)) {
        
                        foreach(array('_join','_group_by','_order_by', '_having', '_limit','_condition') as $k) {
            
                                $this->$k = '';
                        }
                
                        $this->_data_select = '*';
                }

                return true;
        }

        //telmo
        function clearWhereCondition()
        {
                $this->_condition = '';
        }
    
        //telmo
        function unsetAllFields()
        {
    
                $def = $this->tableDefinition();
                
                if($def != null){
        
                        foreach($def as $k => $v){
        
                                unset($this->$k);
                        }
                }
        }
    
        //telmo
        function insertIsNullCondition($field, $logic = 'AND')
        {

                $cond = $field . ' IS NULL';
        
                if ($this->_condition) {
        
                        $this->_condition .= ' ' . $logic . ' ' . $cond;
            
                        return true;
                }
        
                $this->_condition = ' WHERE ' . $cond;
        
                return true;
        }
        
        //telmo
        function insertWhereCondition($field, $op, $value, $logic = 'AND')
        {

                // Value must be filtered for sql injection

                $def = $this->getTableDefinition();

                if(isset($def[$field])){

                        $fdef = $def[$field];
                        $type = $fdef['TYPE'];

                        if($this->needsQuotes($type)){

                                $value = '"' . $value . '"';
                        }
                }

                $cond = $field . $op . $value;

                if ($this->_condition) {

                        $this->_condition .= ' ' . $logic . ' ' . $cond;

                        return true;
                }

                $this->_condition = ' WHERE ' . $cond;

                return true;
        }

        //telmo
        function setOrderBy($order = false, $desc = false)
        {
                $desc_cond = '';
        
                if($desc){
        
                        $desc_cond = ' desc ';
                }
    
                if ($order === false) {
                
                        $this->_order_by = '';
                        return true;
                }

                if (!trim($order)) {
        
                        return false;
                }
        
                if (!$this->_order_by) {
        
                        $this->_order_by = " ORDER BY {$order} " . $desc_cond;
                        return true;
                }
        
                $this->_order_by .= " , {$order}" . $desc_cond;
        
                return true;
        }

        //telmo
        function setGroupBy($group = false)
        {
                if ($group === false) {
                
                        $this->_group_by = '';
                        return true;
                }

                if (!trim($group)) {
        
                        return false;
                }
        
        
                if (!$this->_group_by) {

                        $this->_group_by = " GROUP BY {$group} ";
                        return true;
                }
                $this->_group_by .= " , {$group}";

                return true;
        }

        //telmo
        function setHaving($having = false)
        {
                if ($having === false) {
        
                        $this->_having = '';
            
                        return true;
                }

                if (!trim($having)) {
        
                        return false;
                }
        
        
                if (!$this->_having) {
        
                        $this->_having = " HAVING {$having} ";
            
                        return true;
                }
        
                $this->_having .= " , {$having}";
                return true;
        }

        //telmo
        function setLimit($a = null)
        {
                if ($a === null) {
        
                        $this->_limit = '';
           
                        return true;
                }
            
                $this->_limit = " LIMIT $a";
        
                return true;
        }

        //telmo
        function insertRow($set_key = false)
        {
                if(!$set_key){
        
                        unset($this->id);
                }
        
                $this->_connect();

                $__DB  = &$this->_database;
                $items = $this->tableDefinition();

                $datasaved = 1;
                $leftq = '';
                $rightq = '';
                $key = false;
                $keys = $this->_get_keys();

                foreach($items as $k => $v) {
        
                        if((!$set_key) && ($k == 'id')){
            
                                continue;
                        }
                        
                        if(isset($v['LANG']) && ($v['LANG'] == true)){
            
                                include_module_once('language');

                                $lang = new Bloxx_Language();

                                $lang->clearWhereCondition();
                                $lang->runSelect();

                                while ($lang->nextRow()) {
                
                                        $klang = $k . "_LANG_" . $lang->code;
                        
                                        if (!isset($this->$klang)) {
                                        
                                                continue;
                                        }
                                        
                                        if((!$this->needsQuotes($v['TYPE'])) && (!isset($this->$klang))){

                                                continue;
                                        }

                
                                        if ($leftq) {
                                
                                                $leftq  .= ', ';
                                                $rightq .= ', ';
                                        }
                                
                                        $leftq .= "$klang ";

                                        if (strtolower($this->$klang) === 'null') {

                                                $rightq .= " NULL ";
                                        }
                                        else{
                                        
                                                $rightq .= $this->prepareValue($this->$klang, $v['TYPE']);
                                        }
                                }
                        }
                        else{
            
                                if (!isset($this->$k)) {
                
                                        continue;
                                }
                                
                                if((!$this->needsQuotes($v['TYPE'])) && ($this->$k == null)){

                                        continue;
                                }
            
                                if ($leftq) {
                        
                                        $leftq  .= ', ';
                                        $rightq .= ', ';
                                }
                        
                                $leftq .= "$k ";

                                if (strtolower($this->$k) === 'null') {
            
                                        $rightq .= " NULL ";
                                }
                                else{

                                        $rightq .= $this->prepareValue($this->$k, $v['TYPE']);
                                }
                        }

                }
                if ($leftq) {
        
                        //echo "INSERT INTO {$this->name} ($leftq) VALUES ($rightq) <br>";
                        $r = $this->_query("INSERT INTO {$this->name} ($leftq) VALUES ($rightq) ");
            
                        if (PEAR::isError($r)) {

                                return false;
                        }
            
                        if ($r < 1) {

                                return false;
                        }

                        $this->$key = mysql_insert_id();

                        if (isset($this->$key)) {
            
                                return $this->$key;
                        }
            
                        return true;
                }

                return false;
        }


        //telmo
        function updateRow($lang_complete = false)
        {
                $this->_connect();

                if($lang_complete){

                        $items = $this->tableDefinitionLangComplete();
                }
                else{

                        $items = $this->tableDefinition();
                }
                
                $keys  = $this->_get_keys();

                if (!$items) {

                        return false;
                }

                $values  = '';

                foreach($items as $k => $v) {
        
                        if (!isset($this->$k)) {
                        
                                continue;
                        }
                        
                        if((!$this->needsQuotes($v['TYPE'])) && ($this->$k == null)){

                                continue;
                        }

                        if ($values)  {
            
                                $values .= ', ';
                        }

                        if (strtolower($this->$k) === 'null') {

                                $values .= "$k = NULL";
                        }
                        else{

                                $values .= "$k = " . $this->prepareValue($this->$k, $v['TYPE']);
                        }
                }


                $this->_build_condition($items, $keys);

                if ($values && $this->_condition) {
        
                        //echo "UPDATE  {$this->name}  SET {$values} {$this->_condition} ";
                        $numrows = $this->_query("UPDATE  {$this->name}  SET {$values} {$this->_condition} ");
            
                        if (PEAR::isError($numrows)) {

                                return false;
                        }
                        if ($numrows < 1) {

                                return false;
                        }

                        return $numrows;
                }

                return false;
        }


        //telmo
        function deleteRowByID($id)
        {
                $this->_connect();
        
                $count = $this->_query("DELETE FROM {$this->name} WHERE id={$id}");
            
                if (PEAR::isError($count)) {

                        return false;
                }
                if ($count < 1) {

                        return false;
                }

                return true;
        }

        function fetchRow($row = null)
        {
                if (!$this->name) {
        
                        return false;
                }
                if ($row === null) {

                        return false;
                }
                if (!$this->N) {

                        return false;
                }

                $result = &$this->_results;
                $array  = $results->fetchrow(DB_FETCHMODE_ASSOC, $row);

                foreach($array as $k => $v) {
        
                        $kk = str_replace(".", "_", $k);

                        $this->$kk = $array[$k];
                }

                return true;
        }

        function _get_keys()
        {
    
                return array('id');
        }

        function _connect()
        {
        		global $dbconnection;
        		
                if (!$dbconnection)
                {

                        $dbconnection = DB::connect($this->_database_dsn);
                        
                        if (DB::isError($dbconnection)) {

                                echo 'DB connection error.<br>';
                                return;                                
                        }
                }
                
                $this->_database = $dbconnection;

                return true;
        }

        function _query($string)
        {
        		//global $query_count;
        		//$query_count++;
        		//echo $query_count . ' ';
        		//echo $string . '<br>';
        	
                $this->_connect();

                $result = $this->_database->query($string);
                
                //echo $string . ' count: ' . $result->numrows() . '<br>';                

                if (DB::isError($result)) {

                        //echo $result->toString();
                        return false;
                }

                switch (strtolower(substr(trim($string),0,6))) {
                        case 'insert':
                        case 'update':
                        case 'delete':
                                return $this->_database->affectedRows();
                }

                $this->_results = $result;

                $this->N = 0;

                if (method_exists($result, 'numrows')) {
        
                        $this->N = $result->numrows();
                }
        }
    
        //telmo
        function createTable()
        {
                $def = $this->tableDefinition();
        
                $sql = 'CREATE TABLE ' . $this->name . '(';
        
                foreach($def as $k => $v){
        
                        if(isset($v['LANG']) && ($v['LANG'] == true)){
                
                                include_module_once('language');
                        
                                $lang = new Bloxx_Language();
                        
                                $lang->clearWhereCondition();
                                $lang->runSelect();

                                while ($lang->nextRow()) {
                        
                                        $sql .= $k . '_LANG_' . $lang->code . ' ' . $this->_translateType($v['TYPE'], $v['SIZE']);

                                        if(isset($v['NOTNULL'])){

                                                $sql .= ' NOT NULL';
                                        }
                                        if($k == 'id'){

                                                $sql .= ' AUTO_INCREMENT';
                                        }
                                        $sql .= ',';
                                }
                        }
                        else{
        
                                $sql .= $k . ' ' . $this->_translateType($v['TYPE'], $v['SIZE']);
                
                                if(isset($v['NOTNULL'])){
                
                                        $sql .= ' NOT NULL';
                                }
                                if($k == 'id'){
                
                                        $sql .= ' AUTO_INCREMENT';
                                }
                                $sql .= ',';
                        }
                }
        
                $sql .= ' PRIMARY KEY(id))';
        
                $ret = $this->_query($sql);
        
                return $ret;
        }
    
        //telmo
        function addTableColumn($name, $def)
        {
                $sql = 'ALTER TABLE ' . $this->name . ' ADD ' . $name;

                $sql .= ' ' . $this->_translateType($def['TYPE'], $def['SIZE']);

                if(isset($def['NOTNULL'])){

                        $sql .= ' NOT NULL';
                }
        
                return $this->_query($sql);
        }
    
        //telmo
        function removeTableColumn($name)
        {
                $sql = 'ALTER TABLE ' . $this->name . ' DROP ' . $name;

                return $this->_query($sql);
        }
    
        //telmo
        function dropTable()
        {
                $def = $this->tableDefinition();

                $sql = 'DROP TABLE ' . $this->name;

                return $this->_query($sql);
        }
        
        function _translateType($type, $size)
        {
                if($type == 'NUMBER'){

                        return 'INT';
                }
                else if($type == 'CREATORID'){

                        return 'INT';
                }
                else if($type == 'STRING'){

                        return 'VARCHAR(' . $size . ')';
                }
                else if($type == 'PASSWORD'){

                        return 'VARCHAR(255)';
                }
                else if($type == 'TEXT'){

                        return 'TEXT';
                }
                else if($type == 'HTML'){

                        return 'TEXT';
                }
                else if($type == 'DATETIME'){

                        return 'BIGINT';
                }
                else if($type == 'CREATIONDATETIME'){

                        return 'BIGINT';
                }
                else if($type == 'DATE'){

                        return 'BIGINT';
                }
                else if(substr($type, 0, 6) == "BLOXX_"){

                        return 'INT';
                }
                else if(substr($type, 0, 5) == "ENUM_"){

                        return 'INT';
                }
                else if($type == 'FILE'){

                        return 'VARCHAR(255)';
                }
                else if($type == 'IMAGE'){

                        return 'LONGBLOB';
                }
        }
        
        function prepareValue($value, $type)
        {
                if($this->needsQuotes($type)){

                        $out = $this->_database->quote($value) . " ";
                }
                else{

                        $out = ' ' . $value . ' ';
                }
                
                return $out;
        }
        
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
        
        function _build_condition($keys, $filter = array(), $negative_filter = array())
        {
                $this->_connect();

                $__DB  = &$this->_database;

                foreach($keys as $k => $v) {

                        if ($filter) {
                
                                if (!in_array($k, $filter)) {
                        
                                        continue;
                                }
                        }
                        if ($negative_filter) {
                
                                if (in_array($k, $negative_filter)) {
                        
                                        continue;
                                }
                        }
                        if (!isset($this->$k)) {
                
                                continue;
                        }
                        
                        if (strtolower($this->$k) === 'null') {
                
                                $this->insertIsNullCondition($k);
                        }
                        else{

                                $this->insertWhereCondition($k, '=', $this->$k);
                        }
                }
        }
}
?>
