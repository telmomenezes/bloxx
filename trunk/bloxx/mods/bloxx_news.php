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
// $Id: bloxx_news.php,v 1.7 2005-06-20 11:26:09 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_News extends Bloxx_Module
{
        function Bloxx_News()
        {
                $this->name = 'news';
                $this->module_version = 1;
                $this->label_field = 'title';

                $this->use_init_file = true;

                $this->default_mode = 'news';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'intro' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'extended' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'news_header' => TRUST_GUEST,
                        'news' => TRUST_GUEST,                        
                        'news_form' => TRUST_EDITOR
                );
        }        

//  Render methods .............................................................
        
	function doRenderNews_Header($param, $target, $jump, $other_params, $mt)
	{

		$detailed_link = $this->getConfig('detailed_link');
		$detailed_page = $this->getConfig('detailed_page');

		$this->getRowByID($param);
                        
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('datetime', $html_out);                                                
                        
		$html_out = $this->renderAutoText($this->intro);
		$mt->setItem('intro', $html_out);                        
                        
		$html_out = '<a href="index.php?id=' . $detailed_page . '&param=' . $param . '&target=news">';
		$html_out .= $detailed_link;
		$html_out .= '</a>';
		$mt->setItem('detailed_link', $html_out);

		return $mt->renderView();
	}
	
	function doRenderNews($param, $target, $jump, $other_params, $mt)
	{

		$this->getRowByID($param);
                        
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('datetime', $html_out);
                        
		$html_out = $this->renderAutoText($this->intro);
		$mt->setItem('intro', $html_out);
                        
		$html_out = $this->renderAutoText($this->extended);
		$mt->setItem('extended', $html_out);                      

		return $mt->renderView();
	}
	
	function doRenderNews_Form($param, $target, $jump, $other_params, $mt)
	{

		$this->publish_date = time();
		$html_out .= $this->renderForm(-1, false, $mt);

		return $html_out;
	}                
}
?>
