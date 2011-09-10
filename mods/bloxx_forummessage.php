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
// $Id: bloxx_forummessage.php,v 1.2 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_ForumMessage extends Bloxx_Module
{
        function Bloxx_ForumMessage()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'forummessage';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'subject';                
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;                
                $this->_BLOXX_MOD_PARAM['default_mode'] = 'default_view';
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'user_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'subject' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'USER' => true),
                        'content' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),
                        'parent_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'parent_forum_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'parent_type' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'message' => TRUST_GUEST,
                        'default_view' => TRUST_GUEST,
                        'topic' => TRUST_GUEST,
                        'new_message' => TRUST_USER,                 
                        'count_topics' => TRUST_GUEST,
                        'count_messages' => TRUST_GUEST,
                        'count_replies' => TRUST_GUEST,
                        'new_topic_link' => TRUST_USER,
                        'new_message_link' => TRUST_USER,
                        'last_forum_message' => TRUST_GUEST,
                        'last_topic_message' => TRUST_GUEST,
                        'compact_message' => TRUST_GUEST
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'newmsg' => TRUST_USER
                );
        }
        
        function getCountTopics($id)
        {                
                $comm = new Bloxx_ForumMessage();
                $comm->clearWhereCondition();
               	$comm->insertWhereCondition('parent_id', '=', $id);
                $comm->insertWhereCondition('parent_type', '=', 'forum');
                
                return $comm->getCount();
        }
        
        function getCountMessages($id)
        {
                $comm = new Bloxx_ForumMessage();
                $comm->clearWhereCondition();
               	$comm->insertWhereCondition('parent_forum_id', '=', $id);                
                
                return $comm->getCount();
        }
        
        function getCountReplies($id)
        {
                $comm = new Bloxx_ForumMessage();
                $comm->clearWhereCondition();
               	$comm->insertWhereCondition('parent_id', '=', $id);
               	$comm->insertWhereCondition('parent_type', '=', 'forummessage');                
                
                return $comm->getCount();
        }
        
        function getLastForumMessage($id)
        {
                $comm = new Bloxx_ForumMessage();
                $comm->clearWhereCondition();
                $comm->insertWhereCondition('parent_forum_id', '=', $id);
               	$comm->setOrderBy('publish_date', true);
               	$comm->setLimit(1);                
                $comm->runSelect();                
                
                if (!$comm->nextRow())
                {
                	return null;
                }
                
                return $comm;
        }
        
        function getLastTopicMessage($id)
        {
                $comm = new Bloxx_ForumMessage();
                $comm->clearWhereCondition();
                $comm->insertWhereCondition('parent_id', '=', $id);
                $comm->insertWhereCondition('parent_type', '=', 'forummessage');
               	$comm->setOrderBy('publish_date', true);
               	$comm->setLimit(1);                
                $comm->runSelect();                
                
                if (!$comm->nextRow())
                {
                	return null;
                }
                
                return $comm;
        }                
        
        function create()
        {

                $def = $this->tableDefinitionLangComplete();

                foreach ($def as $k => $v)
                {

                        if(isset($_POST[$k]))
                        {

                                $this->$k = $_POST[$k];
                        }
                }
                
                $this->publish_date = time();

                return $this->insertRow();
        }
    
//  Render methods .............................................................
        
	function doRenderNew_Message($param, $target, $jump, $other_params, $mt)
	{                
                
		$tname = 'Bloxx_' . $target;
		include_module_once($target);
		$target_module = new $tname();

		$html_out = $target_module->render($target_module->_BLOXX_MOD_PARAM['default_mode'], $param);
		$mt->setItem('target_view', $html_out);
                
		$this->parent_id = $param;
		$this->parent_type = $target;
                        
		if ($this->parent_type == 'forum')
		{
			$this->parent_forum_id = $this->parent_id;
		}
		else if ($this->parent_type == 'forummessage')
		{
			$parmsg = new Bloxx_ForumMessage();
			$parmsg->getRowByID($this->parent_id);
			$this->parent_forum_id = $parmsg->parent_id;
		}
		else
		{
			$this->parent_forum_id = -1;
		}
                        
		include_module_once('identity');
		$ident = new Bloxx_Identity();
		$this->user_id = $ident->userID();
                        
		return $this->renderForm(-1, false, $mt, -1, 'newmsg');
	}
        
	function doRenderCount_Topics($param, $target, $jump, $other_params, $mt)                
	{
                        
		$count = $this->getCountTopics($param);

		$mt->setItem('count', $count);

		return $mt->renderView();
	}
        
	function doRenderCount_Messages($param, $target, $jump, $other_params, $mt)
	{
                        
		$count = $this->getCountMessages($param);

		$mt->setItem('count', $count);

		return $mt->renderView();
	}
        
	function doRenderCount_Replies($param, $target, $jump, $other_params, $mt)
	{
                        
		$count = $this->getCountReplies($param);

		$mt->setItem('count', $count);

		return $mt->renderView();
	}
        
	function doRenderMessage($param, $target, $jump, $other_params, $mt)
	{                		

		include_module_once('identity');
                        
		$ident = new Bloxx_Identity();

		if (!isset($this->id))
		{
                        
			$this->getRowByID($param);
		}
                        
		$anchor = '';
                        
		if ($this->parent_type == 'forum')
		{
			$anchor = 'topic';
		}
		else
		{
			$anchor = $this->id;
		}
                        
		$ident->getRowByID($this->user_id);
                        
		include_module_once('forumvisit');
		$visit = new Bloxx_ForumVisit();

		$html_out = '<a name="' . $anchor . '">';
		$html_out .= $this->subject;                        
		$mt->setItem('subject', $html_out);
                        
		$html_out = $ident->username;
		$mt->setItem('message_by', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('date', $html_out);
                        
		$html_out = $this->renderAutoText($this->content);
		$mt->setItem('content', $html_out);                        

		return $mt->renderView();
	}
        
	function doRenderDefault_View($param, $target, $jump, $other_params, $mt)
	{

		include_module_once('identity');
                        
		$ident = new Bloxx_Identity();

		if (!isset($this->id))
		{
                        
			$this->getRowByID($param);
		}
                        
		$ident->getRowByID($this->user_id);

		$html_out = $this->subject;
		$mt->setItem('subject', $html_out);
                        
		$html_out = $ident->username;
		$mt->setItem('message_by', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('date', $html_out);
                        
		$html_out = $this->renderAutoText($this->content);
		$mt->setItem('content', $html_out);                        

		return $mt->renderView();
	}
        
	function doRenderTopic($param, $target, $jump, $other_params, $mt)
	{

		include_module_once('identity');
                        
		$ident = new Bloxx_Identity();

		if (!isset($this->id))
		{
                        
			$this->getRowByID($param);
		}
                        
		$ident->getRowByID($this->user_id);

		$read_topic_page = $this->getConfig('read_topic_page');
		$html_out = build_link($read_topic_page, 'read_topic', $param, 'forummessage', $this->subject, false);
		$mt->setItem('subject', $html_out);
                        
		$html_out = $ident->username;
		$mt->setItem('message_by', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('date', $html_out);
                        
		$html_out = $this->render('count_replies', $this->id);                        
		$mt->setItem('replies', $html_out);
                        
		include_module_once('forumvisit');
		$visit = new Bloxx_ForumVisit();
		
		if ($visit->isNew($param))
		{
			$mt->setItem('new', '[novo]');                        	
		}
                                                
		$html_out = $this->render('last_topic_message', $this->id);                        
		$mt->setItem('last_topic_message', $html_out);

		return $mt->renderView();
	}
        
	function doRenderNew_Topic_Link($param, $target, $jump, $other_params, $mt)
	{                		
                
		$new_message_page = $this->getConfig('new_message_page');
		$new_topic_link_text = $this->getConfig('new_topic_link_text');
                        
		$html_out = build_link($new_message_page, 'new_message', $param, $target, $new_topic_link_text, true);
		$mt->setItem('link', $html_out);                      
                  
		return $mt->renderView();
	}
        
	function doRenderNew_Message_Link($param, $target, $jump, $other_params, $mt)
	{                		
                
		$new_message_page = $this->getConfig('new_message_page');
		$new_message_link_text = $this->getConfig('new_message_link_text');
                        
		$html_out = build_link($new_message_page, 'new_message', $param, $target, $new_message_link_text, true);
		$mt->setItem('link', $html_out);                      
                  
		return $mt->renderView();
	}
        
	function doRenderLast_Forum_Message($param, $target, $jump, $other_params, $mt)
	{                		
                
		$message = $this->getLastForumMessage($param);
                        
		if ($message == null)
		{
			return '';
		}
                        
		$topic_id = -1;
		$anchor = null;
                        
		if ($message->parent_type == 'forum')
		{
			$topic_id = $message->id;
			$anchor = 'topic';
		}
		else if ($message->parent_type == 'forummessage')
		{
			$topic_id = $message->parent_id;
			$anchor = $message->id;
		}
                        
                        
		$read_topic_page = $this->getConfig('read_topic_page');
		$html_out = build_link($read_topic_page, 'read_topic', $topic_id, 'forummessage', getDateAndTimeString($message->publish_date), false, null, $anchor);                        
                        
		$mt->setItem('date', $html_out);
                        
		$ident = new Bloxx_Identity();                                                
		$ident->getRowByID($message->user_id);
		$mt->setItem('user', $ident->username);
                  
		return $mt->renderView();
	}
        
	function doRenderLast_Topic_Message($param, $target, $jump, $other_params, $mt)
	{                		
                
		$message = $this->getLastTopicMessage($param);
                        
		if ($message == null)
		{
			return '';
		}
                                                
		$topic_id = $message->parent_id;
		$anchor = $message->id;                                                
                        
		$read_topic_page = $this->getConfig('read_topic_page');
		$html_out = build_link($read_topic_page, 'read_topic', $topic_id, 'forummessage', getDateAndTimeString($message->publish_date), false, null, $anchor);                        
                        
		$mt->setItem('date', $html_out);
                        
		$ident = new Bloxx_Identity();                                                
		$ident->getRowByID($message->user_id);
		$mt->setItem('user', $ident->username);
                  
		return $mt->renderView();
	}
        
	function doRenderCompact_Message($param, $target, $jump, $other_params, $mt)
	{
		
		$message = new Bloxx_ForumMessage();                		                
		$message->getRowByID($param);
                        
		if ($message == null)
		{
			return '';
		}
                        
		$topic_id = -1;
		$anchor = null;
                        
		if ($message->parent_type == 'forum')
		{
			$topic_id = $message->id;
			$anchor = 'topic';
		}
		else if ($message->parent_type == 'forummessage')
		{
        	$topic_id = $message->parent_id;
        	$anchor = $message->id;
        }
                        
                        
        $read_topic_page = $this->getConfig('read_topic_page');
		$html_out = build_link($read_topic_page, 'read_topic', $topic_id, 'forummessage', $message->subject, false, null, $anchor);                        
                        
        $mt->setItem('subject', $html_out);
                        
        $ident = new Bloxx_Identity();                                                
        $ident->getRowByID($message->user_id);
        $mt->setItem('user', $ident->username);
                        
        $mt->setItem('date', getDateAndTimeString($message->publish_date));
                  
        return $mt->renderView();
	}

	
//  Command methods ............................................................

	function execCommandNewMsg()
	{

		$this->create();
	}                
}
?>
