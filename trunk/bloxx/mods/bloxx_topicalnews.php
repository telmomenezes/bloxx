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

class Bloxx_TopicalNews extends Bloxx_Module
{
        function Bloxx_TopicalNews()
        {
                $this->name = 'topicalnews';
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
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false),
                        'topic' => array('TYPE' => 'BLOXX_TOPICALNEWSTOPIC', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
                        'author' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => false, 'HIDDEN' => true)
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
                        'news_form' => TRUST_USER
                );
        }

        function getStyleList()
        {
                return array(
                        'Title' => 'NormalTitle',
                        'Info' => 'NormalInfo',
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
                $style_info = $this->getGlobalStyle('Info');

                if($mode == 'news_header'){

                        $detailed_link = $this->getConfig('detailed_link');
                        $detailed_page = $this->getConfig('detailed_page');

                        $this->getRowByID($id);

                        $html_out = $this->renderNewsHeader($mode, $id, $target);

                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $detailed_page . '&param=' . $id . '&target=topicalnews">';
                        $html_out .= $detailed_link;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
                else if($mode == 'news'){

                        $this->getRowByID($id);

                        $html_out .= $this->renderNewsHeader($mode, $id, $target);
                        $html_out .= '<p>';
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $this->renderAutoText($this->extended);
                        $html_out .= $style->renderStyleFooter($style_text);

                        return $html_out;
                }
                else if($mode == 'news_form'){

                        $this->publish_date = time();
                        
                        include_module_once('identity');
                        $identity = new Bloxx_Identity();
                        
                        $this->author = $identity->id();
                        
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

                        $detailed_link = $this->getConfig('detailed_link');
                        $detailed_page = $this->getConfig('detailed_page');

                        $this->getRowByID($id);

                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= '<a href="index.php?id=' . $detailed_page . '&param=' . $id . '&target=news">';
                        $html_out .= $detailed_link;
                        $html_out .= '</a>';
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
        }
        
        function renderNewsHeader($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');
                $style_text = $this->getGlobalStyle('Text');
                $style_link = $this->getGlobalStyle('Link');
                $style_info = $this->getGlobalStyle('Info');
        
                $this->getRowByID($id);

                $html_out = $style->renderStyleHeader($style_title);
                $html_out .= $this->title;
                $html_out .= $style->renderStyleFooter($style_title);

                $html_out .= '<br>';

                include_module_once('topicalnewstopic');
                $topic = new Bloxx_TopicalNewsTopic();
                $topic->getRowByID($this->topic);

                $html_out .= $topic->topic_symbol;
                $html_out .= '<br>';
                $html_out .= $style->renderStyleHeader($style_info);
                $html_out .= getDateAndTimeString($this->publish_date);
                $html_out .= '&nbsp;';

                include_module_once('identity');
                $identity = new Bloxx_Identity();
                $identity->getRowByID($this->author);

                $html_out .= LANG_TOPICALNEWS_BY . $identity->username;
                $html_out .= $style->renderStyleFooter($style_info);
                $html_out .= '<p>';
                $html_out .= $style->renderStyleHeader($style_text);
                $html_out .= $this->renderAutoText($this->intro);
                $html_out .= $style->renderStyleFooter($style_text);
                $html_out .= '<p>';
                
                return $html_out;
        }
        
        function create()
        {
                include_module_once('bloxx_role');

                if(!$this->verifyTrust(TRUST_USER)){

                        return false;
                }

                $this->publish_date = time();
                
                include_module_once('identity');
                $identity = new Bloxx_Identity();
                $this->author = $identity->id();

                parent::create(false);
        }
}
?>
