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
// $Id: bloxx_forumvisit.php,v 1.2 2005-06-22 20:05:35 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_ForumVisit extends Bloxx_Module
{
        function Bloxx_ForumVisit()
        {
                $this->name = 'forumvisit';
                $this->module_version = 1;
                $this->label_field = 'title';

                $this->use_init_file = false;

                $this->default_mode = 'default_view';
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'user_id' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => true),
                        'message_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'last_visit' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => true)
                );
        }
        
        function registerVisit($message_id)
        {
        	include_module_once('identity');
            $ident = new Bloxx_Identity();
            
            $user_id = $ident->id();
            
            if ($user_id < 0)
            {
            	return;
            }
            
            $this->clearWhereCondition();
            $this->insertWhereCondition('user_id', '=', $user_id);
            $this->insertWhereCondition('message_id', '=', $message_id);
            $this->runSelect();
                        
            if (!$this->nextRow())
            {
            	$this->message_id = $message_id;
        		$this->user_id = $user_id;
        		$this->last_visit = time();        		
        	
        		$this->insertRow();
            }
            else
            {
            	$this->last_visit = time();
            	$this->updateRow();
            }           
        }
        
        function isNew($message_id)
        {
        	include_module_once('identity');
            $ident = new Bloxx_Identity();
            
            $user_id = $ident->id();
            
            if ($user_id < 0)
            {
            	return false;
            }
            
            $this->clearWhereCondition();
            $this->insertWhereCondition('user_id', '=', $user_id);
            $this->insertWhereCondition('message_id', '=', $message_id);
            $this->runSelect();                 
                        
            if (!$this->nextRow())
            {
            	return true;
            }
            
            return false;            
        }
}
?>
