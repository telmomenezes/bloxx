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
// $Id: bloxx_photo.php,v 1.9 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_Photo extends Bloxx_Module
{
        function Bloxx_Photo()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'photo';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'title';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['default_mode'] = 'news';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'image' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
                        'thumb' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => false),
                        'gallery' => array('TYPE' => 'BLOXX_PHOTOGALLERY', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'form' => TRUST_EDITOR,
                        'full' => TRUST_GUEST,
                        'thumbnail' => TRUST_GUEST,
                        'title' => TRUST_GUEST
                );
        }        
        
        function create()
        {
                if(isset($_FILES['image']['tmp_name']))
                {

						$thumb_side = $this->getConfig('thumb_side');
						$full_side = $this->getConfig('full_side');

                        include_once(CORE_DIR . 'bloxx_image_utils.php');
                        
                        $or_width = getJpegWidth($_FILES['image']['tmp_name']);
                        $or_height = getJpegHeight($_FILES['image']['tmp_name']);
                        
                        if (($or_width > $thumb_side) || ($or_height > $thumb_side))
                        {
                        	if ($or_width > $or_height)
                        	{
                        
                                $this->thumb = scaleJpegWidth($_FILES['image']['tmp_name'], $thumb_side);
                        	}
                        	else
                        	{
                        
                                $this->thumb = scaleJpegHeight($_FILES['image']['tmp_name'], $thumb_side);
                        	}
                        }
                        
                        if (($or_width > $full_side) || ($or_height > $full_side))
                        {
                        	if ($or_width > $or_height)
                        	{
                        
                                $this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $full_side);
                        	}
                        	else
                        	{
                        
                                $this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $full_side);
                        	}
                        }
                }

                $new_id = Bloxx_Module::create();
        }

        //Condition to list photos by gallery
        function insertListConditions()
        {       
                if(isset($_GET['gallery'])){
                
                        $this->insertWhereCondition('gallery', '=', $_GET['gallery']);
                }
        }

//  Render methods .............................................................
        
	function doRenderForm($param, $target, $jump, $other_params, $mt)                
	{                

		unset($_GET['return_id']);
		unset($_GET['id']);
		$html_out = $this->renderForm(-1, false, $mt);

		return $html_out;
	}
	
	function doRenderFull($param, $target, $jump, $other_params, $mt)
	{
                        
		$this->getRowByID($param);
		$html_out = $this->renderImage('image');
		$mt->setItem('image', $html_out);
                        
		return $mt->renderView();
	}
	
	function doRenderTitle($param, $target, $jump, $other_params, $mt)
	{

		$this->getRowByID($param);
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                        
		return $mt->renderView();
	}
	
	function doRenderThumbnail($param, $target, $jump, $other_params, $mt)
	{

		$full_image_page = $this->getConfig('full_photo_page');

		$this->getRowByID($param);

		$html_out = '<a href="index.php?id=' . $full_image_page . '&param=' . $param . '">';
		$html_out .= $this->renderImage('thumb');
		$html_out .= '</a>';
                        
		$mt->setItem('thumb', $html_out);                        
                        
		$html_out = '<a href="index.php?id=' . $full_image_page . '&param=' . $param . '">';
		$html_out .= $this->title;
		$html_out .= '</a>';
                        
		$mt->setItem('title', $html_out);

		return $mt->renderView();
	}
}
?>
