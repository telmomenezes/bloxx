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
// $Id: bloxx_state.php,v 1.8 2005-08-08 16:38:34 tmenezes Exp $

include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_State extends Bloxx_Module
{
        function Bloxx_State()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'state';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'item_name';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = false;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'owner_identity' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => true),
                        'owner_module' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'item_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'item_value' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true)
                );
        }        
        
        function getValue($module, $item)
        {
                include_module_once('identity');
                $ident = new Bloxx_Identity();
                
                if($ident->userID() == -1){

                        if(isset($_COOKIE['bloxx_state' . $module . $item])){
                        
                                return $_COOKIE['bloxx_state' . $module . $item];
                        }
                        else{
                        
                                return null;
                        }
                }
        
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $module_id = $mm->getModuleID($module);
                
                include_module_once('identity');
                $ident = new Bloxx_Identity();
        
                $this->clearWhereCondition();
                $this->insertWhereCondition('owner_identity', '=', $ident->userID());
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module', '=', $module_id);
                $this->runSelect();
                
                if (!$this->nextRow()){

                        return null;
                }
                else {
                
                        return $this->item_value;
                }
        }
        
        function setValue($module, $item, $value)
        {
                include_module_once('identity');
                $ident = new Bloxx_Identity();

                setcookie('bloxx_state' . $module . $item, $value, (time()+2592000),'/','',0);
                $_COOKIE['bloxx_state' . $module . $item] = $value;
                
                if($ident->userID() == -1){
                
                        return;
                }
        
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $module_id = $mm->getModuleID($module);

                include_module_once('identity');
                $ident = new Bloxx_Identity();

                $this->clearWhereCondition();
                $this->insertWhereCondition('owner_identity', '=', $ident->userID());
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module', '=', $module_id);
                $this->runSelect();

                if (!$this->nextRow()){

                        $this->item_value = $value;
                        $this->owner_identity = $ident->userID();
                        $this->owner_module = $module_id;
                        $this->item_name = $item;
                        $this->insertRow();
                }
                else{
                
                        $this->item_value = $value;
                        $this->updateRow();
                }
        }
        
        function unsetValue($module, $item)
        {

                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $module_id = $mm->getModuleID($module);

                include_module_once('identity');
                $ident = new Bloxx_Identity();

                $this->clearWhereCondition();
                $this->insertWhereCondition('owner_identity', '=', $ident->userID());
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module', '=', $module_id);
                $this->runSelect();

                if ($this->nextRow()){

                        $this->deleteRowByID($this->id);
                }
        }
}
?>
