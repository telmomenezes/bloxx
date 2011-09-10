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
// $Id: bloxx_quotation.php,v 1.2 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Quotation extends Bloxx_Module
{
        function Bloxx_Quotation()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'quotation';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'email';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['default_mode'] = 'ask_quotation';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                		'company_name' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),
                		'contact_name' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),
                		'phone' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),                        
                        'email' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),
                        'request' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),                        
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'ask_quotation' => TRUST_GUEST                        
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'register' => TRUST_GUEST                        
                );
        }
        
        function create()
        {                       
        		Bloxx_Module::create(false);
        	
                $target_email = $this->getConfig('quotation_email');                

				$message = '';                                        
                $message .= constant('F_LANG_QUOTATION_COMPANY_NAME') . "\n";
                $message .= $this->company_name . "\n\n";
                $message .= constant('F_LANG_QUOTATION_CONTACT_NAME') . "\n";
                $message .= $this->contact_name . "\n\n";
                $message .= constant('F_LANG_QUOTATION_PHONE') . "\n";
                $message .= $this->phone . "\n\n";
                $message .= constant('F_LANG_QUOTATION_EMAIL') . "\n";
                $message .= $this->email . "\n\n";
                $message .= constant('F_LANG_QUOTATION_REQUEST') . "\n";
                $message .= $this->request . "\n\n";

                mail($target_email,
                	constant('LANG_QUOTATION_EMAIL_SUBJECT'),
                	$message,
					'From: ' . $target_email);
					
				global $warningmessage;
				$warningmessage = constant('LANG_QUOTATION_REQUEST_RECEIVED');                
        }
        
//  Render methods .............................................................
        
	function doRenderAsk_Quotation($param, $target, $jump, $other_params, $mt)
	{
        
		$html_out = $this->renderForm(-1, false, $mt);

		return $html_out;
	}


//  Command methods ............................................................
	
	function execCommandRegister()
	{

		$this->create();
	}                
}
?>
