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
// $Id: bloxx_comment.php,v 1.8 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Comment extends Bloxx_Module
{
        function Bloxx_Comment()
        {
                $this->name = 'comment';
                $this->module_version = 1;
                $this->label_field = 'subject';
                
                $this->use_init_file = true;
                
                $this->default_mode = 'comment';
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'user_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'subject' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'USER' => true),
                        'content' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),
                        'parent_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'parent_type' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getLocalRenderTrusts()
        {
                return array(
                        'comment_link' => TRUST_USER,
                        'read_comments_link' => TRUST_GUEST,
                        'read_comments' => TRUST_GUEST,
                        'comment' => TRUST_GUEST,
                        'comment_header' => TRUST_GUEST,
                        'new_comment' => TRUST_GUEST,
                        'comments_count' => TRUST_GUEST,
                        'count' => TRUST_GUEST
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'newmsg' => TRUST_USER
                );
        }
        
        function renderCommentsTree($id, $target, $indent, $mt)
        {
                $width = $indent * 50;
        
                $comm = new Bloxx_Comment();

                $comm->clearWhereCondition();
                $comm->insertWhereCondition('parent_id', '=', $id);
                $comm->insertWhereCondition('parent_type', '=', $target);
                $comm->setOrderBy('publish_date', true);
                $comm->runSelect();

                while ($comm->nextRow())
                {
                		$mt->nextLoopIteration();

                        //Hack while we don't have normal/expanded view
                        if (true || ($indent == 0))
                        {
                        	
                                $html_out = $comm->render('comment', $comm->id);
                                $mt->setLoopItem('comment', $html_out);
                                $html_out = $comm->render('comment_link', $comm->id, 'comment');
                                $mt->setLoopItem('comment_link', $html_out);
                        }
                        else
                        {
                        
                                $html_out = $comm->render('comment_header', $comm->id, 'comment');
                                $mt->setLoopItem('comment', $html_out);
                        }
                        
                        $comm->renderCommentsTree($comm->id, 'comment', $indent + 1, $mt);
                }
        }
        
        function countComments($id, $target)
        {
                $count = 0;
        
                $comm = new Bloxx_Comment();

                $comm->clearWhereCondition();
                $comm->insertWhereCondition('parent_id', '=', $id);
                $comm->insertWhereCondition('parent_type', '=', $target);
                $comm->runSelect();

                while($comm->nextRow()){

                        $count++;
                        $count += $comm->countComments($comm->id, 'comment');
                }

                return $count;
        }
        
        function create()
        {

                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if(isset($_POST[$k])){

                                $this->$k = $_POST[$k];
                        }
                }
                
                $this->publish_date = time();

                return $this->insertRow();
        }

//  Render methods .............................................................
        
	function doRenderComment_Link($param, $target, $jump, $other_params, $mt)        
    {
                
		$new_comment_page = $this->getConfig('new_comment_page');
                        
		$html_out = build_link($new_comment_page, 'new_comment', $param, $target, LANG_COMMENT_COMMENT, true);
		$mt->setItem('link', $html_out);                      
                  
		return $mt->renderView();
	}
	
	function doRenderRead_Comments_Link($param, $target, $jump, $other_params, $mt)
    {

		$read_comments_page = $this->getConfig('read_comments_page');

		$html_out = build_link($read_comments_page, 'read_comments', $param, $target, LANG_COMMENT_READ_COMMENT, false);
		$mt->setItem('link', $html_out);

		return $mt->renderView();
	}
	
	function doRenderNew_Comment($param, $target, $jump, $other_params, $mt)
	{
                
		$tname = 'Bloxx_' . $target;
		include_module_once($target);
		$target_module = new $tname();

		$html_out = $target_module->render($target_module->default_mode, $param);
		$mt->setItem('target_view', $html_out);
                
		$this->parent_id = $param;
		$this->parent_type = $target;
                        
		include_module_once('identity');
		$ident = new Bloxx_Identity();
		$this->user_id = $ident->id();
                        
		return $this->renderForm(-1, false, $mt, -1, 'newmsg');
	}
	
	function doRenderRead_Comments($param, $target, $jump, $other_params, $mt)
    {

		$tname = 'Bloxx_'.$target;
		include_module_once($target);
		$target_module = new $tname();
                        
		$html_out = $target_module->render($target_module->default_mode, $param);
		$mt->setItem('target_view', $html_out);

		$html_out = $this->render('comment_link', $param, $target_module->name);
		$mt->setItem('comment_link', $html_out);
                        
		$mt->startLoop('comments_tree');
		$this->renderCommentsTree($param, $target, 0, &$mt);

		return $mt->renderView();
	}
	
	function doRenderComments_Count($param, $target, $jump, $other_params, $mt)
	{

		$id_in = $param;

		$count = $this->countComments($param, $target);
                        
		$read_comments_page = $this->getConfig('read_comments_page');

		if ($count == 1)
		{
                                
			$text = $count . ' ' . LANG_COMMENT_ONE_COMMENT;
		}
		else
		{
                                
			$text = $count . ' ' . LANG_COMMENT_COMMENTS;
		}
                                
		$html_out = build_link($read_comments_page, 'read_comments', $id_in, $target, $text, false);
		$mt->setItem('count_link', $html_out);

		return $mt->renderView();
	}
	
	function doRenderCount($param, $target, $jump, $other_params, $mt)
    {

		$id_in = $param;

		$count = $this->countComments($param, $target);

		$mt->setItem('count', $count);

		return $mt->renderView();
	}
	
	function doRenderComment($param, $target, $jump, $other_params, $mt)
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
                        
		$html_out = LANG_COMMENT_BY . $ident->username;
		$mt->setItem('comment_by', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('date', $html_out);
                        
		$html_out = $this->renderAutoText($this->content);
		$mt->setItem('content', $html_out);                        

		return $mt->renderView();
	}
	
	function doRenderComment_Header($param, $target, $jump, $other_params, $mt)
	{
                
		include_module_once('identity');

		$ident = new Bloxx_Identity();

		if (!isset($this->id))
		{

			$this->getRowByID($param);
		}
                        
		$ident->getRowByID($this->user_id);
                        
		$read_comments_page = $config->getConfig('read_comments_page');
                        
		$html_out = build_link($read_comments_page, 'read_comments', $param, $target, $this->subject, false);
		$mt->renderItem('read_comments_link', $html_out);
                        
		$html_out = LANG_COMMENT_BY . $ident->username;
		$mt->renderItem('comment_by', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->renderItem('date', $html_out);                        

		return $mt->renderView();	
	}


//  Command methods ............................................................

	function execCommandNewMsg()
	{

		$this->create();
	}       
}
?>
