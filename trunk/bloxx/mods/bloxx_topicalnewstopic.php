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

class Bloxx_TopicalNewsTopic extends Bloxx_Module
{
        function Bloxx_TopicalNewsTopic()
        {
                $this->name = 'topicalnewstopic';
                $this->module_version = 1;
                $this->label_field = 'topic_name';

                $this->use_init_file = true;

                $this->default_mode = 'topic_symbol';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'topic_name' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'topic_symbol' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true, 'USER' => true)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'topic_symbol' => TRUST_GUEST,
                        'topic_form' => TRUST_EDITOR
                );
        }

        function getStyleList()
        {
                return array(
                        'Title' => 'NormalTitle',
                        'Text' => 'NormalText',
                        'Link' => 'NormalLink'
                );
        }

        function doRender($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');
                $style_text = $this->getGlobalStyle('Text');
                $style_link = $this->getGlobalStyle('Link');

                if($mode == 'topic_symbol'){

                        $this->getRowByID($id);

                        $html_out .= $this->topic_symbol;

                        return $html_out;
                }
                else if($mode == 'topic_form'){

                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
        }
}
?>
