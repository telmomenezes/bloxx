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
// $Id: bloxx_pollvote.php,v 1.5 2005-08-08 16:38:36 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_PollVote extends Bloxx_Module
{
        function Bloxx_PollVote()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'pollvote';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'id';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'poll_id' => array('TYPE' => 'BLOXX_POLL', 'SIZE' => -1, 'NOTNULL' => true),
                        'user_id' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => true),
                        'vote' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true)
                );
        }
}
?>
