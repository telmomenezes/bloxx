<?php

//
// Bloxx - Open Source Content Management System
//
// Copyright 2002 - 2005 Telmo Menezes. All rights reserved.
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

include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_State extends Bloxx_Module
{
        function Bloxx_State()
        {
                $this->name = 'state';
                $this->module_version = 1;
                $this->label_field = 'item_name';
                $this->use_init_file = true;
                $this->no_private = true;
                
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
        
        function doRender($mode, $id, $target)
        {
        }
        
        function getValue($module, $item)
        {
                global $_COOKIE;
        
                include_module_once('identity');
                $ident = new Bloxx_Identity();
                
                if($ident->id() == -1){

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
                $this->insertWhereCondition('owner_identity', '=', $ident->id());
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
                global $_COOKIE;

                include_module_once('identity');
                $ident = new Bloxx_Identity();

                setcookie('bloxx_state' . $module . $item, $value, (time()+2592000),'/','',0);
                $_COOKIE['bloxx_state' . $module . $item] = $value;
                
                if($ident->id() == -1){
                
                        return;
                }
        
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $module_id = $mm->getModuleID($module);

                include_module_once('identity');
                $ident = new Bloxx_Identity();

                $this->clearWhereCondition();
                $this->insertWhereCondition('owner_identity', '=', $ident->id());
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module', '=', $module_id);
                $this->runSelect();

                if (!$this->nextRow()){

                        $this->item_value = $value;
                        $this->owner_identity = $ident->id();
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
                $this->insertWhereCondition('owner_identity', '=', $ident->id());
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module', '=', $module_id);
                $this->runSelect();

                if ($this->nextRow()){

                        $this->deleteRowByID($this->id);
                }
        }
}
?>
