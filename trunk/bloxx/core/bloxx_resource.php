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

class Bloxx_Resource extends Bloxx_Module
{
        function Bloxx_Resource()
        {
                $this->name = 'resource';
                $this->module_version = 1;
                $this->label_field = 'resourcename';
                $this->use_init_file = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'resourcename' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true)
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
        
        function newDataElement()
        {

                parent::newDataElement();

                mkdir(MAIN_DIR . 'res/' . $this->resourcename);
        }
}
?>
