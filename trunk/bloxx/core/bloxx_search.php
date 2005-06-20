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
// $Id: bloxx_search.php,v 1.5 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';
include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Search extends Bloxx_Module
{
		// To be done yet...
	
        function Bloxx_Search()
        {
                $this->name = 'search';
                $this->module_version = 1;
                $this->label_field = 'id';
                $this->use_init_file = true;
                
                $this->Bloxx_Module();
        }        
}
?>
