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
// $Id: bloxx_modulemanager.php,v 1.7 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_ModuleManager extends Bloxx_Module
{
        function Bloxx_ModuleManager()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'modulemanager';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'module_name';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'module_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'version' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }
        
        function register($module, $version)
        {
                $this->clearWhereCondition();
                $this->insertWhereCondition('module_name', '=', $module);
                $this->runSelect();
                
                if($this->nextRow()){
                
                        if($this->version != $version){
                        
                                $this->version = $version;
                                $this->updateRow();
                        }
                }
                else{
                
                        $this->module_name = $module;
                        $this->version = $version;
                        $this->insertRow();
                }
        }
        
        function unRegister($module)
        {
                $this->deleteRowByID($this->getModuleID($module));
        }
        
        function getModuleID($module)
        {
        		global $CACHE_MOD_ID;
        		
        		if (!isset($CACHE_MOD_ID[$module]))
        		{
                	$this->clearWhereCondition();
                	$this->insertWhereCondition('module_name', '=', $module);
                	$this->runSelect();
                
                	if($this->nextRow()){

						$CACHE_MOD_ID[$module] = $this->id;
                	}
                	else
                	{

                        $CACHE_MOD_ID[$module] =  -1;
                	}
        		}
        		
        		return $CACHE_MOD_ID[$module];
        }
        
        function getModuleName($moduleId)
        {
        		global $CACHE_MOD_NAME;
        		
        		if (!isset($CACHE_MOD_NAME[$moduleId]))        		
        		{
        	
                	$this->clearWhereCondition();
                	$this->insertWhereCondition('id', '=', $moduleId);
                	$this->runSelect();
                
                	if($this->nextRow()){

                        $CACHE_MOD_NAME[$moduleId] = $this->module_name;
                	}
                	else{

                        $CACHE_MOD_NAME[$moduleId] = -1;
                	}
        		}
        		
        		return $CACHE_MOD_NAME[$moduleId];
        }
        
        function isCoreModule($mod_name = '')
        {

                if($mod_name == ''){
                
                        $mod_name = $this->module_name;
                }

                return file_exists(CORE_DIR.'bloxx_'.strtolower($mod_name).'.php');
        }
        
        function getModuleInstance()
        {
        	
                include_module_once($this->module_name);
                $mname = 'Bloxx_' . $this->module_name;
                $minst = new $mname();
                
                return $minst;
        }
}
?>
