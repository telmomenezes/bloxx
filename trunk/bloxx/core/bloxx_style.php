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

class Bloxx_Style extends Bloxx_Module
{
        function Bloxx_Style()
        {
                $this->name = 'style';
                $this->module_version = 1;
                $this->label_field = 'title';
                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true),
                        'css' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false)
                );


        }
        
        function renderStyleSheet()
        {
                $html_out =  '
                <style TYPE="text/css">
                <!--
                ';
                
                $this->clearWhereCondition();
                $this->runSelect();

                while($this->nextRow()) {

                        $html_out .= $this->css;
                }
                
                $html_out .=  '
                -->
                </style>
                ';
                
                return $html_out;
        }
        
        function renderStyleHeader($style_name)
        {
                $html_out =  '<span class=' . $style_name . '>';
                
                return $html_out;
        }
        
        function renderStyleFooter($style_name)
        {
                $html_out = '</span>';
                
                return $html_out;
        }
}
?>
