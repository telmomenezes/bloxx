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
// $Id: bloxx_config.php,v 1.3 2005-02-18 17:34:56 tmenezes Exp $

//require_once 'defines.php';
include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Config extends Bloxx_Module
{
        function Bloxx_Config()
        {
                $this->name = 'config';
                $this->module_version = 1;
                $this->label_field = 'item_name';
                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'owner_module_id' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'item_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'item_type' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'item_value' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true)
                );
        }
        
        function doRender($mode, $id, $target)
        {
        }
        
        function getValue($module_id, $item)
        {
                $this->clearWhereCondition();
                $this->insertWhereCondition('item_name', '=', $item);
                $this->insertWhereCondition('owner_module_id', '=', $module_id);
                $this->runSelect();
                
                if (!$this->nextRow()){

                        return null;
                }
                else {
                
                        return $this->item_value;
                }
        }
        
        function getConfig($item)
        {
                return $this->getValue($this->getModID(), $item);
        }
        
        function getMainPage()
        {
                return $this->getValue($this->getModID(), 'main_page');
        }
}
?>
