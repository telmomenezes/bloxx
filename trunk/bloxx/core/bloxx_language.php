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
// $Id: bloxx_language.php,v 1.2 2005-02-18 17:34:56 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Language extends Bloxx_Module
{
        function Bloxx_Language()
        {
                $this->name = 'language';
                $this->module_version = 1;
                $this->label_field = 'language_name';
                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'code' => array('TYPE' => 'STRING', 'SIZE' => 2, 'NOTNULL' => true),
                        'language_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true)
                );
        }
        
        function getRenderTrusts()
        {
                return array(
                );
        }
        
        function doRender($mode, $id, $target)
        {
        }
}
?>
