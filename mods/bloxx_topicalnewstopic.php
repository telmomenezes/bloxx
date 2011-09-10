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
// $Id: bloxx_topicalnewstopic.php,v 1.5 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_TopicalNewsTopic extends Bloxx_Module
{
        function Bloxx_TopicalNewsTopic()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'topicalnewstopic';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'topic_name';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['default_mode'] = 'topic_symbol';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'topic_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'topic_symbol' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true, 'USER' => true)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'topic_symbol' => TRUST_GUEST,
                        'topic_form' => TRUST_EDITOR
                );
        }

//  Render methods .............................................................
        
	function doRenderTopic_Symbol($param, $target, $jump, $other_params, $mt)
	{

		$this->getRowByID($param);

		$html_out .= $this->topic_symbol;

		return $html_out;
	}
	
	function doRenderTopic_Form($param, $target, $jump, $other_params, $mt)
	{

		$html_out .= $this->renderForm(-1, false);

		return $html_out;
	}
}
?>
