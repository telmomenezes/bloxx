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

class Bloxx_Photo extends Bloxx_Module
{
        function Bloxx_Photo()
        {
                $this->name = 'photo';
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
                        'image' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
                        'thumb' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => false),
                        'gallery' => array('TYPE' => 'BLOXX_PHOTOGALLERY', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'form' => TRUST_EDITOR,
                        'full' => TRUST_GUEST,
                        'thumbnail' => TRUST_GUEST,
                        'title' => TRUST_GUEST
                );
        }

        function getStyleList()
        {
                return array(
                        'Title' => 'NormalLink'
                );
        }

        function doRender($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');

                if($mode == 'form'){

                        global $HTTP_GET_VARS;
                        unset($HTTP_GET_VARS['return_id']);
                        unset($HTTP_GET_VARS['id']);
                        $html_out = $this->renderForm(-1, false);

                        return $html_out;
                }
                else if($mode == 'full'){
                        
                        $this->getRowByID($id);
                        $html_out = $this->renderImage('image');
                        
                        return $html_out;
                }
                else if($mode == 'title'){

                        $this->getRowByID($id);
                        $html_out = $this->title;

                        return $html_out;
                }
                else if($mode == 'thumbnail'){

                        $full_image_page = $this->getConfig('full_photo_page');

                        $this->getRowByID($id);
                        
                        $html_out = '
                        <table cellpadding=0 cellspacing=0 width=90 height=90">
                                <tr>
                                        <td>
                                                <img src="res/system/transparent_pixel.gif" width=10 height=10></img>
                                        </td>
                                        <td>
                                                <img src="res/system/transparent_pixel.gif" width=80 height=1></img>
                                        </td>
                                        <td>
                                                <img src="res/system/transparent_pixel.gif" width=10 height=10></img>
                                        </td>
                                </tr>
                                <tr valign=center align=center>
                                        <td><img src="res/system/transparent_pixel.gif" width=1 height=80></img></td>
                                        <td valign=center align=center>
                        ';

                        $html_out .= '<a href="index.php?id=' . $full_image_page . '&param=' . $id . '">';
                        $html_out .= $this->renderImage('thumb');
                        
                        $html_out .= '
                                        </td>
                                        <td></td>
                                </tr>
                                <tr>
                                        <td width=10>
                                                <img src="res/system/transparent_pixel.gif" width=10 height=10></img>
                                        </td>
                                        <td align="center">';

                        $html_out .= $style->renderStyleHeader($style_title);
                        $html_out .= '<a href="index.php?id=' . $full_image_page . '&param=' . $id . '">';
                        $html_out .= $this->title;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_title);
                                        
                        $html_out .= '
                                        </td>
                                        <td>
                                                <img src="res/system/transparent_pixel.gif" width=10 height=10></img>
                                        <td>
                                </tr>
                        </table>
                        ';

                        return $html_out;
                }
        }
        
        function create()
        {
                global $_FILES;

                if(isset($_FILES['image']['tmp_name'])){

                        include_once(CORE_DIR . 'bloxx_image_utils.php');
                        
                        $or_width = getJpegWidth($_FILES['image']['tmp_name']);
                        $or_height = getJpegHeight($_FILES['image']['tmp_name']);
                        
                        if($or_width > $or_height){
                        
                                $this->thumb = scaleJpegWidth($_FILES['image']['tmp_name'], 50);
                        }
                        else{
                        
                                $this->thumb = scaleJpegHeight($_FILES['image']['tmp_name'], 50);
                        }
                }

                $new_id = Bloxx_Module::create();
        }

        //Condition to list photos by gallery
        function insertListConditions()
        {
                global $HTTP_GET_VARS;
                
                if(isset($HTTP_GET_VARS['gallery'])){
                
                        $this->insertWhereCondition('gallery = ' . $HTTP_GET_VARS['gallery']);
                }
        }
}
?>
