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

class Bloxx_Forum extends Bloxx_Module
{
        function Bloxx_Forum()
        {
                $this->name = 'forum';
                $this->module_version = 1;
                $this->label_field = 'title';

                $this->use_init_file = true;

                $this->default_mode = 'forum_header';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'USER' => true),
                        'description' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'forum_list' => TRUST_GUEST,
                        'forum_header' => TRUST_GUEST,
                        'forum_title' => TRUST_GUEST,
                        'form' => TRUST_EDITOR
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

                if($mode == 'forum_header'){

                        include_module_once('comment');
                        $forum_page = $this->getConfig('read_forum_page');

                        $forum = new Bloxx_Forum();
                        $forum->getRowByID($id);
                        
                        $html_out = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';
                        $html_out .= '<td width="20%">';
                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $forum_page . '&param=' . $id . '&target=forum">';
                        $html_out .= $forum->title;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);
                        $html_out .= '</td><td width="60%">';
                        $html_out .= $style->renderStyleHeader($style_title);
                        $html_out .= $forum->description;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= '</td><td width="20%">';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $msg = new Bloxx_Comment();
                        $html_out .= $msg->render('count', $forum->id, 'forum');
                        $html_out .= ' Mensagens';
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '</td></tr></table>';

                        return $html_out;
                }
                else if($mode == 'forum_title'){

                        include_module_once('comment');

                        $forum = new Bloxx_Forum();
                        $forum->getRowByID($id);

                        $html_out = $forum->title;

                        return $html_out;
                }
                else if($mode == 'form'){

                        global $HTTP_GET_VARS;
                        unset($HTTP_GET_VARS['return_id']);
                        unset($HTTP_GET_VARS['id']);
                        
                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
                else if($mode == 'forum_list'){

                        $forum = new Bloxx_Forum();
                        $forum->clearWhereCondition();
                        $forum->runSelect();

                        $html_out = null;
                        
                        while($forum->nextRow()){
                        
                                $html_out .= $forum->render('forum_header', $forum->id);
                        }

                        return $html_out;
                }
        }
}
?>
