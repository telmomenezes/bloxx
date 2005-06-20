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
// $Id: bloxx_menubar.php,v 1.5 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_MenuBar extends Bloxx_Module
{

	function Bloxx_MenuBar()
	{

		$this->name = 'menubar';
		$this->module_version = 1;
		$this->label_field = 'barname';
		$this->use_init_file = true;
		$this->no_private = true;
		$this->java_script = true;                
                
		$this->Bloxx_Module();
	}


	function getTableDefinition()
	{
		return array(
			'barname' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true),
			'code' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true)
		);
	}

	function getLocalRenderTrusts()
	{
		return array(
			'menubar' => TRUST_GUEST
		);
	}
	
	function renderBodyParams($view, $param, $target)
	{
		return 'onLoad="onLoad()" onResize="onResize()"';
	}

//  Render methods .............................................................
        
	function doRenderMenubar($param, $target, $jump, $other_params, $mt)	
    {           

		$this->getRowByID($param);
			
		$menu_var_name = 'menubar' . $param;
			
		$html_out = '<script language="JavaScript1.2">';
		$html_out .= 'var ' . $menu_var_name . ' = ';
		$html_out .= $this->code;
		$html_out .= '</script>';
		$html_out .= '<script language="JavaScript1.2">';
		$html_out .= 'menus.add(' . $menu_var_name . ');';
		$html_out .= '</script>';

		return $html_out;
	}
}
?>
