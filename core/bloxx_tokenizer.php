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
// $Id: bloxx_tokenizer.php,v 1.2 2005-02-18 17:34:56 tmenezes Exp $

class Bloxx_Tokenizer
{
        var $cur_string;

        function Bloxx_Tokenizer()
        {
                $this->cur_string = '';
        }
        
        function getToken($arg1, $arg2 = null)
        {
                $token = '';
        
                if($arg2 != null){
                
                        $this->cur_string = $arg1;
                        $token = $arg2;
                }
                else{

                        $token = $arg1;
                }

                $result = strtok($this->cur_string, $token);

                $this->removeResult($result);

                return $result;
        }
        
        function removeResult($result)
        {
        
                $this->cur_string = substr($this->cur_string, strlen($result) + 1);
        }
}

?>
