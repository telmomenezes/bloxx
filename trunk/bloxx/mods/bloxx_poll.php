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

class Bloxx_Poll extends Bloxx_Module
{
        function Bloxx_Poll()
        {
                $this->name = 'poll';
                $this->module_version = 1;
                $this->label_field = 'question';

                $this->use_init_file = true;

                $this->default_mode = 'results';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'question' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
                        'option1' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option2' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option3' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option4' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option5' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option6' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option7' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option8' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option9' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'option10' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'results' => TRUST_GUEST,
                        'vote' => TRUST_GUEST,
                        'form' => TRUST_EDITOR
                );
        }
        
        function getFormTrusts()
        {
                return array(
                        'vote' => TRUST_GUEST
                );
        }

        function getStyleList()
        {
                return array(
                        'Title' => 'NormalTitle',
                        'Text' => 'NormalText',
                        'Link' => 'NormalLink',
                        'SmallTitle' => 'NormalTitle',
                        'SmallText' => 'NormalText',
                        'SmallLink' => 'NormalLink',
                        'Field' => 'SmallFormField',
                        'Button' => 'SmallFormButton',
                );
        }

        function doRender($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');
                $style_text = $this->getGlobalStyle('Text');
                $style_link = $this->getGlobalStyle('Link');
                $style_smalltitle = $this->getGlobalStyle('SmallTitle');
                $style_smalltext = $this->getGlobalStyle('SmallText');
                $style_smalllink = $this->getGlobalStyle('SmallLink');
                $style_button = $this->getGlobalStyle('Button');
                $style_field = $this->getGlobalStyle('Field');

                if($mode == 'vote'){

                        $this->getRowByID($id);
                        
                        $results_page = $this->getConfig('results_page');

                        $html_out = $style->renderStyleHeader($style_smalltitle);
                        $html_out .= $this->question;
                        $html_out .= $style->renderStyleFooter($style_smalltitle);
                        $html_out .= '<br><br>';
                        
                        include_once(CORE_DIR.'bloxx_form.php');
                        $form = new Bloxx_Form();
                        $form->setParam($this->id);
                        $html_out .= $form->renderHeader('poll', 'vote', $results_page);
                        
                        $html_out .= $form->renderInput('poll_id', 'hidden', $this->id, $style_field);
                        
                        $html_out .= '<table border="0" cellspacing="0" cellpadding="0">';
                        
                        for($n = 1; $n <= 10; $n++){

                                $opt = 'option' . $n;
                                
                                if($this->$opt != ''){

                                        $html_out .= '<tr><td valign="top">';
                                        $html_out .= $form->renderInput('option', 'radio', $n, $style_field);
                                        $html_out .= '</td><td>';

                                        $html_out .= '<td valign="top">';
                                        $html_out .= $style->renderStyleHeader($style_smalltext);
                                        $html_out .= $this->$opt;
                                        $html_out .= $style->renderStyleFooter($style_smalltext);
                                        $html_out .= '</td></tr>';
                                }
                        }
                        
                        $html_out .= '</table><br>';
                        
                        $html_out .= $form->renderSubmitButton('Votar', $style_button);
                        
                        $html_out .= $form->renderFooter();
                        
                        $html_out .= '<br>';
                        $html_out .= $style->renderStyleHeader($style_smalllink);
                        $html_out .= build_link($results_page, 'results', $this->id, 'poll', LANG_POLL_VIEW_RESULTS, true);
                        $html_out .= $style->renderStyleFooter($style_smalllink);

                        return $html_out;
                }
                else if($mode == 'results'){

                        $this->getRowByID($id);
                        
                        $bar_color = $this->getConfig('bar_color');
                        
                        $total = $this->voteCount();

                        $html_out = $style->renderStyleHeader($style_title);
                        $html_out .= $this->question;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= '<br><br>';
                        
                        $html_out .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';

                        for($n = 1; $n <= 10; $n++){

                                $opt = 'option' . $n;

                                if($this->$opt != ''){

                                        $html_out .= '<tr><td><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
                                        $html_out .= '<td width="30%" align="right">';
                                        $html_out .= $style->renderStyleHeader($style_text);
                                        $html_out .= $this->$opt;
                                        $html_out .= $style->renderStyleFooter($style_text);
                                        $html_out .= '</td>';
                                        
                                        $html_out .= '<td width="20"><img src="res/system/transparent_pixel.gif" width="20" height="1"></td>';
                                        
                                        $parcial = $this->voteCount($n);
                                        
                                        if($total == 0){
                                        
                                                $percent = 0;
                                        }
                                        else{
                                        
                                                $percent = round(($parcial / $total) * 100);
                                        }
                                        
                                        $barwidth = $percent * 3;
                                        
                                        $html_out .= '<td width="70%"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
                                        $html_out .= '<td width="' . $barwidth . '" height="12" bgcolor="' . $bar_color . '">';
                                        $html_out .= '<img src="res/system/transparent_pixel.gif" width="' . $barwidth . '" height="12">';
                                        $html_out .= '</td>';

                                        $html_out .= '<td width="100%">';
                                        
                                        $html_out .= $style->renderStyleHeader($style_text);
                                        if($parcial == 1){
                                        
                                                $html_out .= '&nbsp;' . $percent . '% ' . $parcial . ' ' . LANG_POLL_VOTE;
                                        }
                                        else{
                                        
                                                $html_out .= '&nbsp;' . $percent . '% ' . $parcial . ' ' . LANG_POLL_VOTES;
                                        }
                                        $html_out .= $style->renderStyleFooter($style_text);
                                        
                                        $html_out .= '</td>';
                                        
                                        $html_out .= '</tr></table></td>';
                                        
                                        $html_out .= '</tr></table></td></tr>';
                                        
                                        $html_out .= '<tr><td>&nbsp;</td><td></td></tr>';
                                }
                        }
                        
                        $html_out .= '</tr></table>';

                        return $html_out;
                }
                else if($mode == 'form'){

                        $this->publish_date = time();
                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
        }
        
        function doProcessForm($command)
        {
                global $_POST, $warningmessage;

                if($command == 'vote'){

                        if($_POST['option'] == 0){

                                $warningmessage = LANG_POLL_ERROR_NO_OPTION_SELECTED;
                                return false;
                        }
                        
                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                        
                        if($ident->id() <= 0){
                        
                                $warningmessage = LANG_POLL_ERROR_MUST_BE_REGISTERED;
                                return false;
                        }
                        
                        if($this->voted($ident->id(), $_POST['poll_id'])){

                                $warningmessage = LANG_POLL_ERROR_CANT_VOTE_TWICE;
                                return false;
                        }
                        
                        include_module_once('pollvote');
                        $vote = new Bloxx_PollVote();
                        
                        $vote->poll_id = $_POST['poll_id'];
                        $vote->user_id = $ident->id();
                        $vote->vote = $_POST['option'];
                        
                        $vote->insertRow();
                }
        }
        
        function voteCount($opt = null)
        {
                include_module_once('pollvote');
                $vote = new Bloxx_PollVote();
                $vote->clearWhereCondition();
                $vote->insertWhereCondition('poll_id', '=', $this->id);
                
                if($opt != null){
                
                        $vote->insertWhereCondition('vote', '=', $opt);
                }
                
                return $vote->runSelect();
        }
        
        function voted($user_id, $poll_id)
        {
                include_module_once('pollvote');
                $vote = new Bloxx_PollVote();
                $vote->clearWhereCondition();
                $vote->insertWhereCondition('user_id', '=', $user_id);
                $vote->insertWhereCondition('poll_id', '=', $poll_id);

                return ($vote->runSelect() > 0);
        }
}
?>
