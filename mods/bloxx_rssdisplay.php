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
// $Id: bloxx_rssdisplay.php,v 1.6 2005-08-08 16:38:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_RssDisplay extends Bloxx_Module
{
        function Bloxx_RssDisplay()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'rssdisplay';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;                
                $this->_BLOXX_MOD_PARAM['use_init_file'] = false;
                
                $this->Bloxx_Module();
        }
        
        function tableDefinition()
        {
                return array();
        }
        
        function getLocalRenderTrusts()
        {
                return array(
                        'rss_display' => TRUST_GUEST
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
                $this->style = new Bloxx_Style();
                $this->style_title = $this->getGlobalStyle('Title');
                $this->style_text = $this->getGlobalStyle('Text');
                $this->style_link = $this->getGlobalStyle('Link');
                $this->html_out = '';
        
                if($mode == 'rss_display'){
                
                        $parser = xml_parser_create();
                        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
                        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'ISO-8859-1');

                        xml_set_object($parser, $this);
                        xml_set_element_handler($parser, "tag_open", "tag_close");
                        xml_set_character_data_handler($parser, "cdata");

                        if(!($fp = fopen("http://www.wired.com/news/feeds/rss2/0,2610,3,00.xml", "r"))){

                                //could not open XML input
                                return false;
                        }

                        while ($data = fread($fp, 4096)) {

                                if (!xml_parse($parser, $data, feof($fp))) {
                                        die(sprintf("XML error: %s at line %d",
                                        xml_error_string(xml_get_error_code($parser)),
                                        xml_get_current_line_number($parser)));
                                }
                        }

                        xml_parser_free($parser);

                        return $this->html_out;
                }
        }
        
        function tag_open($parser, $tag, $attributes)
        {
                $this->current_tag = $tag;

                if($tag == 'item'){

                        $this->in_item = true;
                        $this->parser_item = false;
                }
                else if($this->in_item){

                        $this->parser_item = true;
                }
                else{

                        $this->in_item = false;
                        $this->parser_item = false;
                }
        }

        function cdata($parser, $cdata)
        {
                if($this->parser_item){

                        $this->last_field = $this->current_field;
                        $field = $this->current_tag;
                        $this->current_field = $field;

                        if($this->last_field == $this->current_field){

                                $this->$field .= $cdata;
                        }
                        else{

                                $this->$field = $cdata;
                        }
                }
        }

        function tag_close($parser, $tag)
        {
                $this->parser_item = false;

                if($tag == 'item'){

                        $this->in_item = false;

                        $this->html_out .= $this->style->renderStyleHeader($this->style_title);
                        $this->html_out .= $this->title;
                        $this->html_out .= $this->style->renderStyleFooter($this->style_title);
                        $this->html_out .= $this->style->renderStyleHeader($this->style_text);
                        $this->html_out .= $this->description;
                        $this->html_out .= $this->style->renderStyleFooter($this->style_text);
                        $this->html_out .= $this->style->renderStyleHeader($this->style_link);
                        $this->html_out .= '&nbsp;<a href="' . $this->link . '">>></a>';
                        $this->html_out .= $this->style->renderStyleFooter($this->style_link);
                        $this->html_out .= '<br><br>';
                }
        }
}
?>
