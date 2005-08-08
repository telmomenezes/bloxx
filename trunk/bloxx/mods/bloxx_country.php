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
// $Id: bloxx_country.php,v 1.1 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_Country extends Bloxx_Module
{
	function Bloxx_Country()
	{
		$this->_BLOXX_MOD_PARAM['name'] = 'country';
		$this->_BLOXX_MOD_PARAM['module_version'] = 1;
		$this->_BLOXX_MOD_PARAM['label_field'] = 'country_name';
		$this->_BLOXX_MOD_PARAM['use_init_file'] = true;
		$this->_BLOXX_MOD_PARAM['default_mode'] = 'name_flag';
                
		$this->Bloxx_Module();
	}

	function getTableDefinition()
	{
		return array(
			'country_name' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
			'country_code' => array('TYPE' => 'STRING', 'SIZE' => 2, 'NOTNULL' => true, 'USER' => true)
		);
	}

	function getLocalRenderTrusts()
	{
		return array(
			'name_flag' => TRUST_GUEST,
			'list' => TRUST_GUEST
		);
	}
        

//  Render methods .............................................................
        
	function doRenderName_Flag($param, $target, $jump, $other_params, $mt)        
	{                		
						
		$this->getRowByID($param);
        
        $mt->setItem('name', $this->country_name);
        
        $html_out = '<img src="res/bloxx_country/';
        $html_out .= $this->country_code;
        $html_out .= '.gif" alt="';
        $html_out .= $this->country_name;
        $html_out .= '"></img>';
                        		
		$mt->setItem('flag', $html_out);
                                                
		return $mt->renderView();
	}
	
	function doRenderList($param, $target, $jump, $other_params, $mt)        
	{
		$this->clearWhereCondition();
		$this->setListQueryLimits(20);		
		$this->runSelect();
		
		include_module_once('list');
		$list = new Bloxx_List();
		$html_out = $list->render('navigator', -1, -1);
		$mt->setItem('navigator', $html_out);
                        
		$mt->startLoop('list');
                        
		while ($this->nextRow())
		{
			$mt->nextLoopIteration();
			$mt->setLoopItem('country', $this->country_name);
		}
		
		return $mt->renderView();
	}                
}
?>
