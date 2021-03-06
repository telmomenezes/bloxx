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
// $Id: bloxx_grouplink.php,v 1.4 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_GroupLink extends Bloxx_Module
{
        function Bloxx_GroupLink()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'grouplink';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'id';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'group_id' => array('TYPE' => 'BLOXX_USERGROUP', 'SIZE' => -1, 'NOTNULL' => true),
                        'identity_id' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => true)
                );
        }                
        
        function renderLabel()
        {
                include_module_once('identity');
                $ident = new Bloxx_Identity();
                $ident->getRowbyID($this->identity_id);
                $user = $ident->username;
                
                include_module_once('usergroup');
                $group = new Bloxx_UserGroup();
                $group->getRowByID($this->group_id);
                $groupname = $group->groupname;

                $label = $user . ' -> ' . $groupname;
                
                return $label;
        }
}
?>
