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
// $Id: bloxx_role.php,v 1.3 2005-02-18 17:34:56 tmenezes Exp $

define('TRUST_GUEST', 0);
define('TRUST_USER', 1);
define('TRUST_MODERATOR', 2);
define('TRUST_EDITOR', 3);
define('TRUST_DELETER', 4);
define('TRUST_ADMINISTRATOR', 5);
define('TRUST_BOSS', 6);

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');
require_once(CORE_DIR.'bloxx_modulemanager.php');

class Bloxx_Role extends Bloxx_Module
{
        function Bloxx_Role()
        {
                $this->name = 'role';
                $this->module_version = 1;
                $this->label_field = 'rolename';
                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                $def = array(
                        'rolename' => array('TYPE' => 'STRING', 'SIZE' => 50, 'NOTNULL' => true),
                        'trust_base' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                );

                $manager = new Bloxx_ModuleManager();

                $manager->clearWhereCondition();
                $manager->runSelect();
                
                while($manager->nextRow()){
                
                        $def['trust_' . $manager->module_name] = array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true);
                }

                return $def;
        }
        
        function doRender($mode, $id, $target)
        {
        }


        
        function registerRole($rolename, $trust_base)
        {
                $this->clearWhereCondition();
                $this->insertWhereCondition('rolename', '=', $rolename);
                $this->runSelect();
                
                if ($this->nextRow()) {
                
                        //Erro: o nome escolhido já existe.';
                        return false;
                }
                else{
                        $this->rolename = $rolename;
                        $this->trust_base = $trust_base;
                        
                        if (!$this->insertRow()){
                        
                                //Erro:  ocorreu um erro de acesso à nossa base de dados.
                                return false;
                        } 
                        else {

                                return true;
                        }
                }
        }
        
        function install()
        {
                parent::install();
                $this->registerRole('Administrators', TRUST_BOSS);
        }
        
        function registerModule($module_name)
        {
                $col = 'trust_' . $module_name;
                $def = array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true);
                $this->addTableColumn($col, $def);
                
                $this->clearWhereCondition();
                $this->runSelect();

                if ($this->nextRow()) {

                        $this->$col = $this->trust_base;
                        $this->updateRow();
                }
        }
        
        function unRegisterModule($module_name)
        {
                $col = 'trust_' . $module_name;
                $this->removeTableColumn($col);
        }
        
        function getTrust($module_name)
        {
                $col = 'trust_' . $module_name;
                return $this->$col;
        }
}
?>
