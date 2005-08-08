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
// $Id: bloxx_search.php,v 1.6 2005-08-08 16:38:34 tmenezes Exp $

require_once 'defines.php';
include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Search extends Bloxx_Module
{
	
	function Bloxx_Search()
	{
		$this->_BLOXX_MOD_PARAM['name'] = 'search';
		$this->_BLOXX_MOD_PARAM['module_version'] = 1;
		$this->_BLOXX_MOD_PARAM['label_field'] = 'id';
		$this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                
		$this->Bloxx_Module();
	}
        
	function getTableDefinition()
	{
		return array(
			'title' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),				
			'definition' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true)				
		);
	}

	function getLocalRenderTrusts()
	{
		return array(
			'search_input' => TRUST_GUEST,
			'search_results' => TRUST_GUEST
		);
	}
        
	function getLocalCommandTrusts()
	{
		return array(
			'dummy' => TRUST_GUEST
		);
	}
	
	
//  Render methods .............................................................

	function doRenderSearch_Input($param, $target, $jump, $other_params, $mt)
	{
		include_once(CORE_DIR . 'bloxx_form.php');
                	                        
        $form = new Bloxx_Form();
        $html_out = $form->renderHeader('search', 'dummy');
        $mt->setItem('header', $html_out);
		
		$html_out = $form->renderInput('address', '', '');
		$mt->setItem('input', $html_out);		

		$html_out = $form->renderSubmitButton('Search');
		$mt->setItem('button', $html_out);
                        
		$html_out = $form->renderFooter();
		$mt->setItem('footer', $html_out);
                                
		return $mt->renderView();
	}
	
	function doRenderSearch_Results($param, $target, $jump, $other_params, $mt)
	{
		$this->getRowByID($param);
		
		
	}


//  Command methods ............................................................

	function execCommandDummy()
	{
		// do nothing
	}
}
?>
