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
// $Id: bloxx_news.php,v 1.5 2005-02-18 17:35:46 tmenezes Exp $

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

        function getRenderTrusts()
        {
                return array(
                        'news_header' => TRUST_GUEST,
                        'news' => TRUST_GUEST,
                        'news_title' => TRUST_GUEST,
                        'news_intro' => TRUST_GUEST,
                        'news_detailed_link' => TRUST_GUEST,
                        'news_datetime' => TRUST_GUEST,
                        'news_form' => TRUST_EDITOR
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
                
                $html_out = '';

                if($mode == 'news_header'){

                        $detailed_link = $this->getConfig('detailed_link');
                        $detailed_page = $this->getConfig('detailed_page');

                        $this->getRowByID($id);

                        $html_out = $style->renderStyleHeader($style_title);
                        $html_out .= $this->title;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= getDateAndTimeString($this->publish_date);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $this->renderAutoText($this->intro);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $detailed_page . '&param=' . $id . '&target=news">';
                        $html_out .= $detailed_link;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
                else if($mode == 'news'){

                        $this->getRowByID($id);

                        $html_out = $style->renderStyleHeader($style_title);
                        $html_out .= $this->title;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= getDateAndTimeString($this->publish_date);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $this->renderAutoText($this->intro);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $this->renderAutoText($this->extended);
                        $html_out .= $style->renderStyleFooter($style_text);

                        return $html_out;
                }
                else if($mode == 'news_form'){

                        $this->publish_date = time();
                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
                else if($mode == 'news_title'){

                        $this->getRowByID($id);

                        $html_out .= $this->title;

                        return $html_out;
                }
                else if($mode == 'news_datetime'){

                        $this->getRowByID($id);

                        $html_out .= getDateAndTimeString($this->publish_date);

                        return $html_out;
                }
                else if($mode == 'news_intro'){

                        $this->getRowByID($id);

                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $this->renderAutoText($this->intro);
                        $html_out .= $style->renderStyleFooter($style_text);

                        return $html_out;
                }
                else if($mode == 'news_detailed_link'){

                        $detailed_link = $config->getConfig('detailed_link');
                        $detailed_page = $config->getConfig('detailed_page');

                        $this->getRowByID($id);

                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $detailed_page . '&param=' . $id . '&target=news">';
                        $html_out .= $detailed_link;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
        }
}
?>
