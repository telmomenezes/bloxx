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

class Bloxx_PhotoGallery extends Bloxx_Module
{
        function Bloxx_PhotoGallery()
        {
                $this->name = 'photogallery';
                $this->module_version = 1;
                $this->label_field = 'title';

                $this->use_init_file = true;

                $this->default_mode = 'news';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'form' => TRUST_EDITOR,
                        'title' => TRUST_GUEST,
                        'gallery' => TRUST_GUEST,
                        'gallery_link' => TRUST_GUEST,
                        'gallery_list' => TRUST_GUEST
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

                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
                else if($mode == 'title'){

                        $this->getRowByID($id);
                        $html_out = $this->title;

                        return $html_out;
                }
                else if($mode == 'gallery'){

                        $ppl = $this->getConfig('pics_per_line');

                        include_module_once('photo');
                        $img = new Bloxx_Photo();
                        $img->clearWhereCondition();
                        $img->insertWhereCondition('gallery=' . $id);
                        $img->runSelect();

                        $counter = 0;

                        $html_out = '<table cellpadding=0 cellspacing=0>';
                        
                        while($img->nextRow()){
                        
                                if(($counter % $ppl) == 0){

                                        $html_out .= '<tr>';
                                }
                        
                                $html_out .= '<td>';
                                $img_render = new Bloxx_Photo();
                                $html_out .= $img_render->render('thumbnail', $img->id);
                                $html_out .= '</td>';
                                
                                if(($counter % $ppl) == ($ppl - 1)){

                                        $html_out .= '</tr>';
                                }
                                
                                $counter++;
                        }
                        
                        while(($counter % $ppl) != 0){
                        
                                if(($counter % $ppl) == 0){

                                        $html_out .= '<tr>';
                                }

                                $html_out .= '<td>';
                                $html_out .= '</td>';

                                if(($counter % $ppl) == ($ppl - 1)){

                                        $html_out .= '</tr>';
                                }

                                $counter++;
                        }
                        
                        $html_out .= '</table>';
                        
                        return $html_out;
                }
                else if($mode == 'gallery_link'){

                        $gal_page = $this->getConfig('view_gallery_page');

                        $gallery = new Bloxx_PhotoGallery();
                        $gallery->getRowByID($id);

                        $html_out = $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $gal_page . '&gallery=' . $id . '">';
                        $html_out .= $gallery->title;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
                else if($mode == 'gallery_list'){

                        $gallery = new Bloxx_PhotoGallery();
                        $gallery->clearWhereCondition();
                        $gallery->runSelect();

                        $html_out = null;

                        while($gallery->nextRow()){

                                $html_out .= $gallery->render('gallery_link', $gallery->id);
                                $html_out .= '<br>';
                        }

                        return $html_out;
                }
        }
}
?>
