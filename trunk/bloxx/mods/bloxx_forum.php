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
// $Id: bloxx_forum.php,v 1.6 2005-06-20 11:26:09 tmenezes Exp $

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

                $this->default_mode = 'default_view';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'USER' => true),
                        'description' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true),
                        'hidden' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'forum_list' => TRUST_GUEST,
                        'hidden_forum_list' => TRUST_GUEST,
                        'default_view' => TRUST_GUEST,
                        'forum_header' => TRUST_GUEST,
                        'forum_title' => TRUST_GUEST,
                        'forum' => TRUST_GUEST,
                        'read_topic' => TRUST_GUEST,
                        'navigator' => TRUST_GUEST
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'show' => TRUST_EDITOR,
                        'hide' => TRUST_EDITOR
                );
        }
                
    
//  Render methods .............................................................
        
	function doRenderForum_Header($param, $target, $jump, $other_params, $mt)
    {
						
		include_module_once('forummessage');
		$forum_page = $this->getConfig('read_forum_page');

		$forum = new Bloxx_Forum();
		$forum->getRowByID($param);
                        
		$html_out = '<a href="index.php?id=' . $forum_page . '&param=' . $param . '&target=forum">';
		$html_out .= $forum->title;
		$html_out .= '</a>';
		$mt->setItem('link', $html_out);
                        
		$html_out = $forum->description;
		$mt->setItem('description', $html_out);
                        
		if ($this->verifyTrust(TRUST_EDITOR, $param))
		{
			
			$html_out = '';
			include_once(CORE_DIR.'bloxx_form.php');
							
			if ($forum->hidden == 1)
			{
				$form = new Bloxx_Form();
				$html_out .= $form->renderHeader('forum', 'show');
				$html_out .= $form->renderInput('forum_id', 'hidden', $param);
				$html_out .= $form->renderSubmitButton('Mostrar');
				$html_out .= $form->renderFooter();
			}
			else
			{								
				$form = new Bloxx_Form();
				$html_out .= $form->renderHeader('forum', 'hide');
				$html_out .= $form->renderInput('forum_id', 'hidden', $param);
				$html_out .= $form->renderSubmitButton('Esconder');
				$html_out .= $form->renderFooter();
			}
							
			$mt->setItem('hide_show_button', $html_out);
		}
                        
		$msg = new Bloxx_ForumMessage();
		$html_out = $msg->render('count_topics', $forum->id);                        
		$mt->setItem('topic_count', $html_out);
                        
		$msg = new Bloxx_ForumMessage();
		$html_out = $msg->render('count_messages', $forum->id);                        
		$mt->setItem('message_count', $html_out);
                        
		$msg = new Bloxx_ForumMessage();
		$html_out = $msg->render('last_forum_message', $forum->id);                        
		$mt->setItem('last_forum_message', $html_out);
                        
		return $mt->renderView();
	}
        
	function doRenderDefault_View($param, $target, $jump, $other_params, $mt)
	{
						
		include_module_once('forummessage');
		$forum_page = $this->getConfig('read_forum_page');

		$forum = new Bloxx_Forum();
		$forum->getRowByID($param);
                        
		$html_out = '<a href="index.php?id=' . $forum_page . '&param=' . $param . '&target=forum">';
		$html_out .= $forum->title;
		$html_out .= '</a>';
		$mt->setItem('link', $html_out);
                        
		$html_out = $forum->description;
		$mt->setItem('description', $html_out);
                        
		$msg = new Bloxx_ForumMessage();
		$html_out = $msg->render('count', $forum->id, 'forum');
		$mt->setItem('message_count', $html_out);
                        
		return $mt->renderView();
	}
        
	function doRenderForum_Title($param, $target, $jump, $other_params, $mt)
	{

		$forum = new Bloxx_Forum();
		$forum->getRowByID($param);

		$mt->setItem('title', $forum->title);

		return $mt->renderView();
	}
        
	function doRenderForum_List($param, $target, $jump, $other_params, $mt)
	{

		$forum = new Bloxx_Forum();
		$forum->clearWhereCondition();
		$forum->insertWhereCondition('hidden', '<>', 1);
		$forum->runSelect();
                        
		$mt->startLoop('list');
                        
		while ($forum->nextRow())
		{
			$mt->nextLoopIteration();
                        
			$html_out = $forum->render('forum_header', $forum->id);
			$mt->setLoopItem('forum', $html_out);
		}

		return $mt->renderView();
	}
        
	function doRenderHidden_Forum_List($param, $target, $jump, $other_params, $mt)                
	{

		$forum = new Bloxx_Forum();
		$forum->clearWhereCondition();
		$forum->insertWhereCondition('hidden', '=', 1);
		$forum->runSelect();
                        
		$mt->startLoop('list');
                        
		while ($forum->nextRow())
		{
			$mt->nextLoopIteration();
                        
			$html_out = $forum->render('forum_header', $forum->id);
			$mt->setLoopItem('forum', $html_out);
		}

		return $mt->renderView();
	}
        
	function doRenderForum($param, $target, $jump, $other_params, $mt)
	{              			
		
		include_module_once('forumvisit');
		include_module_once('forummessage');
		$msg = new Bloxx_ForumMessage();
		$msg->clearWhereCondition();
		$msg->insertWhereCondition('parent_type', '=', 'forum');
		$msg->insertWhereCondition('parent_id', '=', $param);
		$msg->setOrderBy('publish_date', true);
		$msg->runSelect();
                        
		$mt->startLoop('topics');
                        
		while ($msg->nextRow())
		{
			$mt->nextLoopIteration();
                        
			$html_out = $msg->render('topic', $msg->id);
			$mt->setLoopItem('topic', $html_out);                                
		}              			

		return $mt->renderView();
	}
        
	function doRenderRead_Topic($param, $target, $jump, $other_params, $mt)                
	{
		
		include_module_once('forumvisit');
		$visit = new Bloxx_ForumVisit();
		
		if ($visit->isNew($param))
		{                        	
			$visit = new Bloxx_ForumVisit();
			$visit->registerVisit($param);
		}
                	                		
		include_module_once('forummessage');
						
		$mt->startLoop('messages');
						
		$msg = new Bloxx_ForumMessage();
		$msg->getRowByID($param);
                        
		$mt->nextLoopIteration();
		$html_out = $msg->render('message', $msg->id);
		$mt->setLoopItem('message', $html_out);
						
		$msg = new Bloxx_ForumMessage();
		$msg->clearWhereCondition();
		$msg->insertWhereCondition('parent_type', '=', 'forummessage');
		$msg->insertWhereCondition('parent_id', '=', $param);
		$msg->setOrderBy('publish_date', true);
		$msg->runSelect();
                        
		while ($msg->nextRow())
		{
			$mt->nextLoopIteration();
                        
			$html_out = $msg->render('message', $msg->id);
			$mt->setLoopItem('message', $html_out);
		}

		return $mt->renderView();
	}
        
	function doRenderNavigator($param, $target, $jump, $other_params, $mt)
	{                	
                	
		$forum_page = $this->getConfig('foruns_page');
                    
		$page = new Bloxx_Page();
		$page->getRowByID($forum_page);
                    
		$html_out = build_link($forum_page, null, null, null, $page->title, false);
                	
		if ($target == 'forum')
		{
			$forum = new Bloxx_Forum();
			$forum->getRowByID($param);
                        
			$html_out .= ' > ' . $forum->title;
		}
		else if ($target == 'forummessage')
		{
			include_module_once('forummessage');
                		
			$msg = new Bloxx_ForumMessage();
			$msg->getRowByID($param);
                        
			if ($msg->parent_type == 'forummessage')
			{
				$msg_id = $msg->parent_id;
				$msg = new Bloxx_ForumMessage();
				$msg->getRowByID($msg_id);
			}
                        
			$forum_id = $msg->parent_id;
                        
			$forum = new Bloxx_Forum();
			$forum->getRowByID($forum_id);
                        
			$forum_page = $this->getConfig('read_forum_page');
                        
			$html_out .= ' > ';
			$html_out .= '<a href="index.php?id=' . $forum_page . '&param=' . $forum_id . '&target=forum">';
			$html_out .= $forum->title;
			$html_out .= '</a>';
                        
			$html_out .= ' > ' . $msg->subject;
		}
                	
		$mt->setItem('navigator', $html_out);
		
		return $mt->renderView();                	
	}


//  Command methods ............................................................
	
	function execCommandShow()
	{
		
		$this->getRowByID($_POST['forum_id']);
		$this->hidden = 2;
		$this->updateRow(true);
	}
	
	function execCommandHide()
	{
		
		$this->getRowByID($_POST['forum_id']);
		$this->hidden = 1;
		$this->updateRow(true);
	}
}
?>
