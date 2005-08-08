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
// $Id: bloxx_style.php,v 1.5 2005-08-08 16:38:34 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Style extends Bloxx_Module
{
        function Bloxx_Style()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'style';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'title';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true),
                        'css' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }
        
        function generateStyleSheetFile()
        {
                $html_out =  '
                <style TYPE="text/css">
                <!--
                ';
                
                $style = new Bloxx_Style();
                $style->clearWhereCondition();
                $style->runSelect();

                while($style->nextRow()) {

                        $html_out .= $style->css;
                }
                
                $html_out .=  '
                -->
                </style>
                ';
                
                $css_file = fopen('bloxx.css', 'w');			
				fwrite($css_file, $html_out);
				fclose($css_file);
        }
        
        function renderStyleSheet()
        {
        	$html_out = '<link rel="stylesheet" href="bloxx.css" type="text/css">';
        	
        	return $html_out;        	
        }
        
        function create()
        {

				Bloxx_Module::create();
                $this->generateStyleSheetFile();                
        }
        
        function update()
        {

				Bloxx_Module::update();
                $this->generateStyleSheetFile();                
        }
        
        //deprecated
        function renderStyleHeader($style_name)
        {
                $html_out =  '<span class=' . $style_name . '>';
                
                return $html_out;
        }
        
        //deprecated
        function renderStyleFooter($style_name)
        {
                $html_out = '</span>';
                
                return $html_out;
        }
}
?>
