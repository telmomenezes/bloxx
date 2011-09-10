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
// $Id: bloxx_list.php,v 1.6 2005-08-08 16:38:33 tmenezes Exp $

require_once 'defines.php';
include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_List extends Bloxx_Module
{
	function Bloxx_List()
	{
		$this->_BLOXX_MOD_PARAM['name'] = 'list';
		$this->_BLOXX_MOD_PARAM['module_version'] = 1;                
		$this->_BLOXX_MOD_PARAM['use_init_file'] = false;
                
		$this->Bloxx_Module();
	}
        
	function getTableDefinition()
	{
		return array();
	}
        
	function getLocalRenderTrusts()
	{
		return array(
			'navigator' => TRUST_GUEST			
		);
	}
	
	function buildLink($label, $page_num)
	{
		$html_out = '<a href="index.php?';
		
		foreach ($_GET as $k => $v)
		{
			if ($k != 'list_current_page')
			{
				$html_out .= $k . '=' . $v . '&';
			}
		}
		
		$html_out .= 'list_current_page=' . $page_num . '">';
		$html_out .= $label;
		$html_out .= '</a>';
		
		return $html_out; 
	}                
        
//  Render methods .............................................................
        
	function doRenderNavigator($param, $target, $jump, $other_params, $mt)
	{		
		
		$pages_in_navigator = 10;
		
		$count = 1;
		
		if (isset($_GET['list_count']))
		{
			$count = $_GET['list_count'];
		}
		
		$results_per_page = 10;
		
		if (isset($_GET['list_results_per_page']))
		{
			$results_per_page = $_GET['list_results_per_page'];
		}
		
		$current = 1;
		
		if (isset($_GET['list_current_page']))
		{
			$current = $_GET['list_current_page'];
		}
		
		$page_count = ceil($count / $results_per_page);		
		
		if ($page_count < 2)
		{
			return '';
		}		
		
		if ($current > 1)
		{
			$html_out = $this->buildLink('< prev', $current - 1) . '&nbsp;&nbsp;'; 
			$mt->setItem('previous', $html_out);
		}
		
		if ($current < $page_count)
		{
			$html_out = $this->buildLink('next >', $current + 1) . '&nbsp;&nbsp;';
			$mt->setItem('next', $html_out);
		}
		
		$page_start = 1;
		$page_end = $page_count;
		
		if ($page_count > $pages_in_navigator)
		{
			$half_navigator = ceil($pages_in_navigator / 2);
			
			if ($current < $half_navigator)
			{
				$page_start = 1;
				$page_end = $pages_in_navigator;
			}
			else if($current > ($page_count - $half_navigator))
			{
				$page_start = $page_count - $pages_in_navigator + 1;
				$page_end = $page_count;
			}
			else
			{
				$page_start = $current - $half_navigator + 1;
				$page_end = $current + $half_navigator; 
			}
		}
		
		$mt->startLoop('pages');
		for ($n = $page_start; $n <= $page_end; $n++)
		{
			$mt->nextLoopIteration();
			
			$html_out = '';
			if ($current == $n)
			{
				$html_out = $n . '&nbsp;&nbsp;';
			}
			else
			{
				$html_out = $this->buildLink($n, $n) . '&nbsp;&nbsp;';
			}
			$mt->setLoopItem('page', $html_out);
		}
                        
		return $mt->renderView();
	}
}
?>
