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

class Bloxx_ModuleManager extends Bloxx_Module
{
        function Bloxx_ModuleManager()
        {
                $this->name = 'modulemanager';
                $this->module_version = 1;
                $this->label_field = 'module_name';
                $this->use_init_file = false;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'module_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'version' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }
        
        function getRenderTrusts()
        {
                return array(
                        'module_list' => TRUST_GUEST
                );
        }
        
        function doRender($mode, $id, $target)
        {
                if($mode == 'module_list'){
                
                        include_once(CORE_DIR.'bloxx_admin.php');
                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();
                        $admin = new Bloxx_Admin();
                        $style_admin_link = $admin->getGlobalStyle('Link');
                
                        include_module_once('identity');
                        $user = new Bloxx_Identity();
        
                        $this->clearWhereCondition();
                        $this->runSelect();
                        
                        $page_id = $this->getCurrentPageID();
                        
                        $html_out = '<table border="0" cellspacing="0" cellpadding="0">';
                        $html_out .= '<tr><td valign="top">';
                
                        while($this->nextRow()) {
                        
                                if($this->isCoreModule($this->module_name)){
                                
                                        $html_out .= '<img src="res/system/bloxx_icon_core.gif" align="middle"></img>';

                                        $html_out .= '<span class="' . $style_admin_link . '">';
                                        $html_out .= '<a href="index.php?id='.$page_id.'&mode=module&param=' . $this->module_name . '">';
                                        $html_out .= $this->module_name;
                                        $html_out .= '</a> version: ' . $this->version;
                                        $html_out .= '</span>';
                                        $html_out .= '<br>';
                                }
                        }
                        
                        $html_out .= '</td><td valign="top">';

                        $mm = new Bloxx_ModuleManager();
                        $mm->clearWhereCondition();
                        $mm->runSelect();
                        
                        while($mm->nextRow()) {

                                if(!$mm->isCoreModule($mm->module_name)){

                                        $html_out .= '<img src="res/system/bloxx_icon_module.gif" align="middle"></img>';

                                        $html_out .= '<span class="' . $style_admin_link . '">';
                                        $html_out .= '<a href="index.php?id='.$page_id.'&mode=module&param=' . $mm->module_name . '">';
                                        $html_out .= $mm->module_name;
                                        $html_out .= '</a> version: ' . $mm->version;
                                        $html_out .= '</span>';
                                        $html_out .= '<br>';
                                }
                        }
                        
                        $html_out .= '</td></tr></table>';
                        
                        return $html_out;
                }
        }
        
        function register($module, $version)
        {
                $this->clearWhereCondition();
                $this->insertWhereCondition("module_name='" . $module . "'");
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
                $this->clearWhereCondition();
                $this->insertWhereCondition("module_name='" . $module . "'");
                $this->runSelect();
                
                if($this->nextRow()){

                        return $this->id;
                }
                else{

                        return -1;
                }
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
