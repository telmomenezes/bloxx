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

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_StyleLink extends Bloxx_Module
{
        function Bloxx_StyleLink()
        {
                $this->name = 'stylelink';
                $this->module_version = 1;
                $this->label_field = 'id';
                $this->use_init_file = false;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'module_id' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'module_style' => array('TYPE' => 'STRING', 'SIZE' => 30, 'NOTNULL' => true),
                        'global_style' => array('TYPE' => 'STRING', 'SIZE' => 30, 'NOTNULL' => true)
                );
        }

        function getRenderTrusts()
        {

        }

        function getFormTrusts()
        {

        }

        function doRender($mode, $id, $target)
        {

        }

        function doProcessForm($command)
        {

        }
        
        function insertLink($module, $module_style, $global_style)
        {
                $this->module_id = $module;
                $this->module_style = $module_style;
                $this->global_style = $global_style;
                
                if (!$this->insertRow()){

                        //Erro:  ocorreu um erro de acesso à nossa base de dados.
                        return false;
                }
        }
        
        function linkExists($module, $module_style, $global_style)
        {
                $stlink = new Bloxx_StyleLink();
        
                $stlink->insertWhereCondition("module_id=" . $module);
                $stlink->insertWhereCondition("module_style='" . $module_style . "'");
                $stlink->insertWhereCondition("global_style='" . $global_style . "'");
                
                $stlink->runSelect();
                
                if($stlink->nextRow()){
                
                        return $stlink->id;
                }
                else{
                
                        return -1;
                }
        }
        
        function renderLabel()
        {
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $mm->getRowByID($this->module_id);
                $label = $this->module_style . ' -> ' . $this->global_style . ' (' . $mm->module_name . ')';
                return $label;
        }
        
        function insertRow($set_key = false)
        {

                $id = $this->linkExists($this->module_id, $this->module_style, $this->global_style);

                if($id >= 0){
                
                        $this->id = $id;
                        Bloxx_DBObject::updateRow(false);
                }
                else{
                
                        Bloxx_DBObject::insertRow($set_key);
                }
        }
}
?>
