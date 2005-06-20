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
// $Id: bloxx_htmlarea.php,v 1.1 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_HTMLArea extends Bloxx_Module
{
        function Bloxx_HTMLArea()
        {
                $this->name = 'htmlarea';
                $this->module_version = 1;
                $this->label_field = 'title';
                $this->use_init_file = true;
                $this->default_mode = 'htmlarea';
                $this->java_script = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => false, 'USER' => true),
                        'content' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => false, 'USER' => true)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'show' => TRUST_GUEST,
                        'edit' => TRUST_EDITOR
                );
        }        
        
        function renderBodyParams($view, $param, $target)
		{
			if ($view == 'edit')
			{
				return 'onload="initDocument()"';
			}
		}

//  Render methods .............................................................
        
	function doRenderShow($param, $target, $jump, $other_params, $mt)                        
	{

		$this->getRowByID($param);
                        
		$html_out = $this->content;
		$mt->setItem('content', $html_out);                        

		return $mt->renderView();
	}
	
	function doRenderEdit($param, $target, $jump, $other_params, $mt)
	{                        
                        
		include_once (CORE_DIR . 'bloxx_form.php');
		include_module_once('admin');

		$form = new Bloxx_Form();
		$form->setFromGlobals();

		$header = $form->renderHeader('htmlarea', 'generic_edit', $return_id);

		$this->getRowByID($param, true);

		$header .= $form->renderInput('id', 'hidden', $param);
		$header .= $form->renderInput('target_module', 
										'hidden', 
										'htmlarea');

		$mt->setItem('header', $header);
		
		$footer = '';

		$value = $this->content;			
			
		$field = 
			'<textarea id="editor" name="content" style="height: 30em; width: 100%;">'
			. $value
			. '</textarea>';				
			
		$mt->setItem('htmlarea', $field);

		$mt->setItem('button', 
			$form->renderSubmitButton(LANG_ADMIN_APPLY_CHANGES));
		

		$footer = $form->renderFooter();
		$mt->setItem('footer', $footer);

		return $mt->renderView();
	}
}
?>
