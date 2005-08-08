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
// $Id: bloxx_newsletter.php,v 1.2 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Newsletter extends Bloxx_Module
{
        function Bloxx_Newsletter()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'newsletter';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'email';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['default_mode'] = 'subscribe';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(                        
                        'email' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true)                        
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'subscribe' => TRUST_GUEST                        
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'subscribe' => TRUST_GUEST
                );
        }        
        
        function checkEmail($email)
        {
        	if (!preg_match("/^( [a-zA-Z0-9] )+( [a-zA-Z0-9\._-] )*@( [a-zA-Z0-9_-] )+( [a-zA-Z0-9\._-] +)+$/" , $email))
        	{
  				list($username,$domain)=split('@',$email);
  				
  				if(!checkdnsrr($domain,'MX'))
  				{
   					return false;
  				}
  				
  				return true;
 			}
 			return false;
		}
		
//  Render methods .............................................................
        
	function doRenderSubscribe($param, $target, $jump, $other_params, $mt)
	{
                        
		include_once(CORE_DIR . 'bloxx_form.php');
		$form = new Bloxx_Form();                        
		$html_out = $form->renderHeader('newsletter', 'subscribe', -1);                        
		$mt->setItem('header', $html_out);                        
                                        
		$html_out = $form->renderInput('email', 'input', '<o seu email aqui>');
		$mt->setItem('email', $html_out);                                                                                       
                        
		$html_out = $form->renderSubmitButton('subscribe');
		$mt->setItem('button', $html_out);
                        
		$html_out = $form->renderFooter();
		$mt->setItem('footer', $html_out);                                                                        

		return $mt->renderView();
	}

	
//  Command methods ............................................................

	function execCommandSubscribe()
	{
		
		global $warningmessage;
                	
		$email = trim($_POST['email']);
		$email = strtolower($email);
                	  
		if (!$this->checkEmail($email))
		{ 
			
			$warningmessage = 'Erro: email incorrecto.';
			return;
		}
					
		$nl = new Bloxx_Newsletter();
		$nl->clearWhereCondition();
		$nl->insertWhereCondition('email', '=', $email);                        
		$nl->runSelect();
                        
		if ($nl->nextRow())
		{
			
			$warningmessage = 'Erro: email repetido, encontra-se inscrito.';
			return;
		}
                   	
		$nl = new Bloxx_Newsletter();        
		$nl->email = $email;
		$nl->insertRow();					

		$warningmessage = 'Registo na newsletter efectuado.';
	}            
}
?>
