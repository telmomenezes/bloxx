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
// $Id: bloxx_photonews.php,v 1.5 2005-02-25 12:24:13 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_PhotoNews extends Bloxx_Module
{
        function Bloxx_PhotoNews()
        {
                $this->name = 'photonews';
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
                        'image' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
                        'thumb' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => false),
                        'photopos' => array('TYPE' => 'ENUM_PHOTONEWS_PHOTOPOS', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'news_header' => TRUST_GUEST,
                        'news' => TRUST_GUEST,                        
                        'news_form' => TRUST_EDITOR
                );
        }       

        function doRender($mode, $id, $target, $mt)
        {                
                
                $html_out = '';

                if($mode == 'news_header'){

                        $detailed_link = $this->getConfig('detailed_link');
                        $detailed_page = $this->getConfig('detailed_page');

                        $this->getRowByID($id);
                        
                        $html_out = $this->title;
                        $mt->setItem('title', $html_out);
                        
                        $html_out = getDateAndTimeString($this->publish_date);
                        $mt->setItem('datetime', $html_out);
                                                
                        if ($this->image != 'empty') {
                        	
                        	$html_out = $this->renderImage('thumb', 'left');                        
                        	$mt->setItem('thumb', $html_out);                        	
                        }
                        
                        $html_out = $this->renderAutoText($this->intro);
                        $mt->setItem('intro', $html_out);
                        
                        $html_out = '<a href="index.php?id=' . $detailed_page . '&param=' . $id . '&target=news">';
                        $html_out .= $detailed_link;
                        $html_out .= '</a>';
                        $mt->setItem('detailed_link', $html_out);
                                                
                        return $mt->renderView();
                }
                else if($mode == 'news'){

                        $this->getRowByID($id);
                        
                        $html_out = $this->title;
                        $mt->setItem('title', $html_out);
                                                
                        $html_out = getDateAndTimeString($this->publish_date);
                        $mt->setItem('datetime', $html_out);

						$html_out = $this->renderImage('image', 'left');
                        $mt->setItem('image', $html_out);
                        
                        $html_out = $this->renderAutoText($this->intro);
                        $mt->setItem('intro', $html_out);
                        
                        $html_out = $this->renderAutoText($this->extended);
                        $mt->setItem('extended', $html_out);                        

                        return $mt->renderView();
                }
                else if($mode == 'news_form'){

                        $this->publish_date = time();
                        $html_out .= $this->renderForm(-1, false, $mt);

                        return $html_out;
                }                
        }
        
        function create()
        {

                global $_FILES;

                if (isset($_FILES['image']['tmp_name'])
                	&& ($_FILES['image']['tmp_name'] != '')) {

                        include_once(CORE_DIR . 'bloxx_image_utils.php');

                        $or_width = getJpegWidth($_FILES['image']['tmp_name']);
                        $or_height = getJpegHeight($_FILES['image']['tmp_name']);
                        
                        $max_photo_width = $this->getConfig('max_photo_width');
                        $max_photo_height = $this->getConfig('max_photo_height');
                        $max_thumb_width = $this->getConfig('max_thumb_width');
                        $max_thumb_height = $this->getConfig('max_thumb_height');

                        if($or_width > $or_height){

                                if($or_width > $max_thumb_width){
                                
                                        $this->thumb = scaleJpegWidth($_FILES['image']['tmp_name'], $max_thumb_width);
                                }
                                if($or_width > $max_photo_width){

                                        $this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $max_photo_width);
                                }
                        }
                        else{

                                if($or_height > $max_thumb_height){

                                        $this->thumb = scaleJpegHeight($_FILES['image']['tmp_name'], $max_thumb_height);
                                }
                                if($or_height > $max_photo_height){

                                        $this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $max_photo_height);
                                }
                        }
                }
                else {
                	
                	$this->image = 'empty';
                	$this->thumb = 'empty';
                }

                $new_id = Bloxx_Module::create();
        }
}
?>
