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
// $Id: bloxx_photogallery.php,v 1.6 2005-03-04 20:49:37 tmenezes Exp $

require_once 'defines.php';
require_once (CORE_DIR.'bloxx_module.php');

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
        return array ('title' => array ('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true));
    }

    function getRenderTrusts()
    {
        return array ('form' => TRUST_EDITOR, 'title' => TRUST_GUEST, 'gallery' => TRUST_GUEST, 'gallery_link' => TRUST_GUEST, 'gallery_list' => TRUST_GUEST);
    }

    function getStyleList()
    {
        return array ('Title' => 'NormalLink');
    }

    function doRender($mode, $id, $target, $mt)
    {

        if ($mode == 'form')
        {

            $html_out .= $this->renderForm(-1, false, $mt);

            return $html_out;
        } else
            if ($mode == 'title')
            {

                $this->getRowByID($id);
                $mt->setItem('title', $this->title);

                return $mt->renderView();
            } else
                if ($mode == 'gallery')
                {

                    $ppl = $this->getConfig('pics_per_line');

                    include_module_once('photo');
                    $img = new Bloxx_Photo();
                    $img->clearWhereCondition();
                    $img->insertWhereCondition('gallery', '=', $id);
                    $img->runSelect();

                    $counter = 0;

                    $html_out = '<table border=0 cellpadding=0 cellspacing=0>';
                    $mt->setItem('table_start', $html_out);

                    $mt->startLoop('table');

                    while ($img->nextRow())
                    {

                        $mt->nextLoopIteration();

                        $html_out = '';

                        if (($counter % $ppl) == 0)
                        {

                            $html_out = '<tr>';
                        }

                        $mt->setLoopItem('start_row', $html_out);

                        $html_out = '<td>';
                        $mt->setLoopItem('start_cell', $html_out);

                        $img_render = new Bloxx_Photo();
                        $html_out = $img_render->render('thumbnail', $img->id);
                        $mt->setLoopItem('thumbnail', $html_out);

                        $html_out = '</td>';
                        $mt->setLoopItem('end_cell', $html_out);

                        $html_out = '';

                        if (($counter % $ppl) == ($ppl -1))
                        {

                            $html_out = '</tr>';
                        }

                        $mt->setLoopItem('end_row', $html_out);

                        $counter ++;
                    }

                    while (($counter % $ppl) != 0)
                    {

                        $mt->nextLoopIteration();

                        $html_out = '';

                        if (($counter % $ppl) == 0)
                        {

                            $html_out = '<tr>';
                        }

                        $mt->setLoopItem('start_row', $html_out);

                        $html_out = '<td>';
                        $mt->setLoopItem('start_cell', $html_out);
                        $mt->setLoopItem('thumbnail', '');
                        $html_out = '</td>';
                        $mt->setLoopItem('end_cell', $html_out);

                        $html_out = '';

                        if (($counter % $ppl) == ($ppl -1))
                        {

                            $html_out = '</tr>';
                        }

                        $mt->setLoopItem('end_row', $html_out);

                        $counter ++;
                    }

                    $html_out = '</table>';
                    $mt->setItem('table_end', $html_out);

                    return $mt->renderView();
                } else
                    if ($mode == 'gallery_link')
                    {

                        $gal_page = $this->getConfig('view_gallery_page');

                        $gallery = new Bloxx_PhotoGallery();
                        $gallery->getRowByID($id);

                        $html_out = '<a href="index.php?id='.$gal_page.'&gallery='.$id.'">';
                        $html_out .= $gallery->title;
                        $html_out .= '</a>';

                        $mt->setItem('link', $html_out);

                        return $mt->renderView();
                    } else
                        if ($mode == 'gallery_list')
                        {

                            $gallery = new Bloxx_PhotoGallery();
                            $gallery->clearWhereCondition();
                            $gallery->runSelect();

                            $mt->startLoop('list');
                            while ($gallery->nextRow())
                            {

                                $mt->nextLoopIteration();
                                $html_out = $gallery->render('gallery_link', $gallery->id);

                                $mt->setLoopItem('link');
                            }

                            return $mt->renderView();
                        }
    }
}
?>
