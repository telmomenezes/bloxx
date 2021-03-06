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
// $Id: bloxx_resource.php,v 1.4 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Resource extends Bloxx_Module
{
        function Bloxx_Resource()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'resource';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'resourcename';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'resourcename' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true)
                );
        }                

        function doProcessForm($command)
        {

        }
        
        function newDataElement()
        {

                parent::newDataElement();

                mkdir(MAIN_DIR . 'res/' . $this->resourcename);
        }
}
?>
