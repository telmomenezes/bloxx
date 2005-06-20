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
// $Id: bloxx_poll.php,v 1.8 2005-06-20 11:26:09 tmenezes Exp $

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
                        'publish_date' => array('TYPE' => 'CREATIONDATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'results' => TRUST_GUEST,
                        'vote' => TRUST_GUEST
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'vote' => TRUST_GUEST
                );
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

//  Render methods .............................................................
        
	function doRenderVote($param, $target, $jump, $other_params, $mt)        
	{

		$this->getRowByID($param);
                        
		$results_page = $this->getConfig('results_page');
                        
		$html_out = $this->question;
		$mt->setItem('question', $html_out);
                        
		include_once(CORE_DIR . 'bloxx_form.php');
		$form = new Bloxx_Form();
		$form->setParam($this->id);
		$html_out = $form->renderHeader('poll', 'vote', $results_page);                        
		$html_out .= $form->renderInput('poll_id', 'hidden', $this->id);
		$mt->setItem('header', $html_out);                                               
                        
		$mt->startLoop('options');
                        
		for ($n = 1; $n <= 10; $n++)
		{

			$mt->nextLoopIteration();
								
			$opt = 'option' . $n;
                                
			if($this->$opt != '')
			{
                                        
				$html_out = $form->renderInput('option', 'radio', $n);
				$mt->setLoopItem('radio', $html_out);                                        
                                                                               
				$html_out = $this->$opt;
				$mt->setLoopItem('label', $html_out);                                        
			}
		}                                               
                        
		$html_out = $form->renderSubmitButton('Votar');
		$mt->setItem('button', $html_out);
                        
		$html_out = $form->renderFooter();
		$mt->setItem('footer', $html_out);
                                                                        
		$html_out = build_link($results_page, 'results', $this->id, 'poll', LANG_POLL_VIEW_RESULTS, true);
		$mt->setItem('results', $html_out);                        

		return $mt->renderView();
	}
        
	function doRenderResults($param, $target, $jump, $other_params, $mt)
	{

		$this->getRowByID($param);
                        
		$max_bar_size = $this->getConfig('max_bar_size');
                        
		$total = $this->voteCount();
                        
		$html_out = $this->question;
		$mt->setItem('question', $html_out);
		$mt->setItem('total_votes', $total);                        

		$mt->startLoop('results');
						
		for ($n = 1; $n <= 10; $n++)
		{
                        	
			$mt->nextLoopIteration();

			$opt = 'option' . $n;

			if ($this->$opt != '')
			{                                        
                                        
				$html_out = $this->$opt;
				$mt->setLoopItem('option_label', $html_out);                                                                               

				$parcial = $this->voteCount($n);
                                        
				if ($total == 0)
				{
                                        
					$percent = 0;
					$size = 0;
				}
				else
				{
                                        
					$percent = round(($parcial / $total) * 100);
					$size = round(($parcial / $total) * $max_bar_size);
				}
                                        
				$html_out = '<img src="res/system/transparent_pixel.gif" width="' . $size . '" height="1"></img>';
				$mt->setLoopItem('option_hor_bar', $html_out);
                                        
				$html_out = '<img src="res/system/transparent_pixel.gif" height="' . $size . '" width="1"></img>';
				$mt->setLoopItem('option_ver_bar', $html_out);
                                                                                
				$mt->setLoopItem('option_count', $parcial);
				$mt->setLoopItem('option_percent', $percent);                                        
                                                                                
				if($parcial == 1)
				{
                                        
					$mt->setLoopItem('option_votes', LANG_POLL_VOTE);
				}
				else
				{
                                        
					$mt->setLoopItem('option_votes', LANG_POLL_VOTES);
				}                                        
			}
		}

		return $mt->renderView();
	}
	

//  Command methods ............................................................

	function execCommandVote()
	{
		
		global $warningmessage;

		if ($_POST['option'] == 0)
		{

			$warningmessage = LANG_POLL_ERROR_NO_OPTION_SELECTED;
			return false;
		}
                        
		include_module_once('identity');
		$ident = new Bloxx_Identity();
                        
		if ($ident->id() <= 0)
		{
                        
			$warningmessage = LANG_POLL_ERROR_MUST_BE_REGISTERED;
			return false;
		}
                        
		if ($this->voted($ident->id(), $_POST['poll_id']))
		{

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
?>
