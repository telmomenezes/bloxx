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
// $Id: bloxx_photonews.php,v 1.7 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_PhotoNews extends Bloxx_Module
{
	function Bloxx_PhotoNews()
	{
		$this->_BLOXX_MOD_PARAM['name'] = 'photonews';
		$this->_BLOXX_MOD_PARAM['module_version'] = 1;
		$this->_BLOXX_MOD_PARAM['label_field'] = 'title';
		$this->_BLOXX_MOD_PARAM['use_init_file'] = true;
		$this->_BLOXX_MOD_PARAM['default_view'] = 'news';                
		$this->_BLOXX_MOD_PARAM['order_field'] = 'publish_date';
                
		$this->Bloxx_Module();
	}

	function getTableDefinition()
	{
		return array(
			'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
			'intro' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true, 'USER' => true),
			'extended' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => false, 'LANG' => true, 'USER' => true),
			'image' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
			'small_image' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => false),
			'thumb' => array('TYPE' => 'IMAGE', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => false),
			'topic' => array('TYPE' => 'BLOXX_TOPICALNEWSTOPIC', 'SIZE' => -1, 'NOTNULL' => false, 'USER' => true),
			'importance' => array('TYPE' => 'ENUM_PHOTONEWS_IMPORTANCE', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),
			'publish_date' => array('TYPE' => 'CREATIONDATETIME', 'SIZE' => -1, 'NOTNULL' => false)
		);
	}

	function getLocalRenderTrusts()
	{
		return array(
			'news_header' => TRUST_GUEST,
			'main_news_header' => TRUST_GUEST,
			'news' => TRUST_GUEST,
			'news_line' => TRUST_GUEST,
			'topic_list' => TRUST_GUEST,
			'list' => TRUST_GUEST,                        
			'news_form' => TRUST_EDITOR
		);
	}       
        
	function create()
	{

		global $_FILES;

		if (isset($_FILES['image']['tmp_name'])
			&& ($_FILES['image']['tmp_name'] != ''))
		{

			include_once(CORE_DIR . 'bloxx_image_utils.php');

			$or_width = getJpegWidth($_FILES['image']['tmp_name']);
			$or_height = getJpegHeight($_FILES['image']['tmp_name']);
                        
			$max_image_width = $this->getConfig('max_image_width');
			$max_image_height = $this->getConfig('max_image_height');
			$max_small_image_width = $this->getConfig('max_small_image_width');
			$max_small_image_height = $this->getConfig('max_small_image_height');
			$max_thumb_width = $this->getConfig('max_thumb_width');
			$max_thumb_height = $this->getConfig('max_thumb_height');

			if ($or_width > $or_height)
			{

				//if($or_width > $max_thumb_width){
                                
					$this->thumb = scaleJpegWidth($_FILES['image']['tmp_name'], $max_thumb_width);
				//}
			if ($or_width > $max_image_width)
			{
				$this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $max_image_width);
			}
			else
			{
				$this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $or_width);
			}
			//if($or_width > $max_small_image_width){

				$this->small_image = scaleJpegWidth($_FILES['image']['tmp_name'], $max_small_image_width);
			//}
			}
			else
			{
				//if($or_height > $max_thumb_height){

					$this->thumb = scaleJpegHeight($_FILES['image']['tmp_name'], $max_thumb_height);
				//}
				if($or_height > $max_image_height)
				{
					$this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $max_image_height);
				}
				else
				{
					$this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $or_height);
				}
                                
				//if($or_height > $max_small_image_height){

					$this->small_image = scaleJpegHeight($_FILES['image']['tmp_name'], $max_small_image_height);
				//}
			}
		}
		else
		{       	
			$this->image = 'empty';
			$this->small_image = 'empty';
			$this->thumb = 'empty';
		}

		$new_id = Bloxx_Module::create();
	}
        
	function update()
	{
        	
		$this->assignValuesFromPost(false);        		

		global $_FILES;

		if (isset($_FILES['image']['tmp_name'])
			&& ($_FILES['image']['tmp_name'] != ''))
		{                								

			include_once(CORE_DIR . 'bloxx_image_utils.php');

			$or_width = getJpegWidth($_FILES['image']['tmp_name']);
			$or_height = getJpegHeight($_FILES['image']['tmp_name']);
                        
			$max_image_width = $this->getConfig('max_image_width');
			$max_image_height = $this->getConfig('max_image_height');
			$max_small_image_width = $this->getConfig('max_small_image_width');
			$max_small_image_height = $this->getConfig('max_small_image_height');
			$max_thumb_width = $this->getConfig('max_thumb_width');
			$max_thumb_height = $this->getConfig('max_thumb_height');

			if ($or_width > $or_height)
			{

				//if($or_width > $max_thumb_width){
                                
					$this->thumb = scaleJpegWidth($_FILES['image']['tmp_name'], $max_thumb_width);
				//}
				if ($or_width > $max_image_width)
				{
					$this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $max_image_width);
				}
				else
				{
					$this->image = scaleJpegWidth($_FILES['image']['tmp_name'], $or_width);
				}
				//if($or_width > $max_small_image_width){

					$this->small_image = scaleJpegWidth($_FILES['image']['tmp_name'], $max_small_image_width);
				//}
			}
			else
			{

				//if($or_height > $max_thumb_height){

					$this->thumb = scaleJpegHeight($_FILES['image']['tmp_name'], $max_thumb_height);
				//}
				if ($or_height > $max_image_height)
				{
					$this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $max_image_height);
				}
				else
				{
					$this->image = scaleJpegHeight($_FILES['image']['tmp_name'], $or_height);
				}
				//if($or_height > $max_small_image_height){

					$this->small_image = scaleJpegHeight($_FILES['image']['tmp_name'], $max_small_image_height);
				//}
			}
		}

		$this->updateRow(true);
	}
	

//  Render methods .............................................................
        
	function doRenderNews_Header($param, $target, $jump, $other_params, $mt)        
	{                
                
		$detailed_link = $this->getConfig('detailed_link');
		$detailed_page = $this->getConfig('detailed_page');
						
		$this->getRowByID($param);
                        
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('datetime', $html_out);
                                                
		if ($this->thumb != 'empty')
		{
                        	
			$html_out = $this->renderImage('thumb');                        
			$mt->setItem('thumb', $html_out);                        	
		}
                        
		$html_out = $this->renderAutoText($this->intro);
		$mt->setItem('intro', $html_out);
                        
		include_module_once('topicalnewstopic');
		$topic = new Bloxx_TopicalNewsTopic();
		$topic->getRowByID($this->topic);
		$mt->setItem('topic', $topic->topic_symbol);

		$html_out .= $topic->topic_symbol;
                        
		$html_out = '<a href="index.php?id=' . $detailed_page . '&param=' . $param . '&target=photonews">';
		$html_out .= $detailed_link;
		$html_out .= '</a>';
		$mt->setItem('detailed_link', $html_out);
                                                
		return $mt->renderView();
	}
	
	function doRenderNews_Line($param, $target, $jump, $other_params, $mt)
	{

		$detailed_page = $this->getConfig('detailed_page');

		$news = new Bloxx_PhotoNews;
		$news->getRowByID($param);                        
                                                
		$html_out = '<a href="index.php?id=' . $detailed_page . '&param=' . $param . '&target=photonews">';
		$html_out .= $news->title;
		$html_out .= '</a>';
		$mt->setItem('title', $html_out);
                        
		$html_out = getDateAndTimeString($news->publish_date);
		$mt->setItem('datetime', $html_out);
                                                
		return $mt->renderView();
	}
	
	function doRenderMain_News_Header($param, $target, $jump, $other_params, $mt)
	{

		$detailed_link = $this->getConfig('detailed_link');
		$detailed_page = $this->getConfig('detailed_page');

		$this->getRowByID($param);
                        
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                        
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('datetime', $html_out);
                                                
		if ($this->small_image != 'empty')
		{
                        	
			$html_out = $this->renderImage('small_image');                        
			$mt->setItem('small_image', $html_out);                        	
		}
                        
		$html_out = $this->renderAutoText($this->intro);
		$mt->setItem('intro', $html_out);
                       	
		include_module_once('topicalnewstopic');
		$topic = new Bloxx_TopicalNewsTopic();
		$topic->getRowByID($this->topic);
		$mt->setItem('topic', $topic->topic_symbol);

		$html_out .= $topic->topic_symbol;
                        
		$html_out = '<a href="index.php?id=' . $detailed_page . '&param=' . $param . '&target=photonews">';
		$html_out .= $detailed_link;
		$html_out .= '</a>';
		$mt->setItem('detailed_link', $html_out);
                                                
		return $mt->renderView();
	}
	
	function doRenderNews($param, $target, $jump, $other_params, $mt)                
	{		
		$this->getRowByID($param);
                        
		$html_out = $this->title;
		$mt->setItem('title', $html_out);
                                                
		$html_out = getDateAndTimeString($this->publish_date);
		$mt->setItem('datetime', $html_out);

		if ($this->image != 'empty')
		{
							
			$html_out = $this->renderImage('image');
			$mt->setItem('image', $html_out);
		}
						
		if ($this->small_image != 'empty')
		{
                        	
			$html_out = $this->renderImage('small_image');                        
			$mt->setItem('small_image', $html_out);                        	
		}
                        
		$html_out = $this->renderAutoText($this->intro);
		$mt->setItem('intro', $html_out);
                        
		$html_out = $this->renderAutoText($this->extended);
		$mt->setItem('extended', $html_out);                        

		return $mt->renderView();
	}
	
	function doRenderNews_Form($param, $target, $jump, $other_params, $mt)
	{

		$this->publish_date = time();
		$html_out .= $this->renderForm(-1, false, $mt);

		return $html_out;
	}
	
	function doRenderTopic_List($param, $target, $jump, $other_params, $mt)
	{
		
		$this->clearWhereCondition();
		$this->insertWhereCondition('topic', '=', $param);
		$this->setOrderBy('publish_date', true);
		$this->setListQueryLimits(15);
		$this->runSelect();

		include_module_once('list');
		$list = new Bloxx_List();
		$html_out = $list->render('navigator', -1, -1);
		$mt->setItem('navigator', $html_out);

		$mt->startLoop('list');

		while ($this->nextRow())
		{
			
			$mt->nextLoopIteration();
			$mt->setLoopItem('news', $this->render('news_line', $this->id));
		}
                    
		return $mt->renderView();
	}
	
	function doRenderList($param, $target, $jump, $other_params, $mt)
	{
		
		$this->clearWhereCondition();		
		$this->setOrderBy('publish_date', true);
		$this->setListQueryLimits(15);
		$this->runSelect();
		
		include_module_once('list');
		$list = new Bloxx_List();
		$html_out = $list->render('navigator', -1, -1);
		$mt->setItem('navigator', $html_out);

		$mt->startLoop('list');

		while ($this->nextRow())
		{
			
			$mt->nextLoopIteration();
			$mt->setLoopItem('news', $this->render('news_line', $this->id));
		}
                    
		return $mt->renderView();
	}                
}
?>
