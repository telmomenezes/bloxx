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
// $Id: bloxx_initparser.php,v 1.3 2005-06-20 11:26:08 tmenezes Exp $

define('PARSE_STATE_FIND_ROW', 0);
define('PARSE_STATE_FIND_FIELD', 1);
define('PARSE_STATE_FIELD_FOUND', 2);

class Bloxx_InitParser
{

        var $module;
        var $data;
        var $state;

        function Bloxx_InitParser($mod)
        {
        
                $this->module = $mod;
                $this->state = PARSE_STATE_FIND_ROW;
        }
        
        function init()
        {
        
                $file = INIT_FILE;

                if(!file_exists($file)){

                        return false;
                }
                
                if(!($fp = fopen($file, "r"))){

                        return false;
                }

                $this->data = fread($fp, filesize($file));
        }
        
        function parse()
        {

				$def = $this->module->getTableDefinition();

                $mod_block = $this->data;
                $separator = '[MODULE ' . $this->module->name . ']';
                $pieces = explode($separator, $mod_block);
                $count = count($pieces);

                if($count < 1){
                
                        return;
                }
                else if($count > 1){
                
                        $mod_block = $pieces[1];
                }
                else{
                
                        $mod_block = $pieces[0];
                }
                
                $separator = '[_' . $this->module->name . ']';
                $pieces = explode($separator, $mod_block);
                $count = count($pieces);

                if($count < 1){

                        return;
                }
                else{

                        $mod_block = $pieces[0];
                }

                //echo '>>>>>>>>>' . $this->module->name . '<br>';
                //echo $mod_block . '<br>';
                //echo $count;
                //echo '<br><br><br>===============================<br><br><br>';
                //$count_inserts = 0;
                //return;
        
                $tok = strtok($mod_block, '[');
                
                while($tok){

                        if($this->state == PARSE_STATE_FIND_ROW){
                        
                                //echo $tok . '<br>';
                        
                                if(substr($tok, 0, 4) == 'row]'){

                                        $this->module->unsetAllFields();
                                        $this->state = PARSE_STATE_FIND_FIELD;
                                }
                                else{
                                
                                        //do nothing
                                }
                        }
                        else if($this->state == PARSE_STATE_FIND_FIELD){
                        
                                if(substr($tok, 0, 5) == '_row]'){

                                        //echo $this->module->name . '<br>';
                                        $this->module->insertRow(true);
                                        //$count_inserts++;
                                
                                        $this->state = PARSE_STATE_FIND_ROW;
                                }
                                else{

                                        $this->state = PARSE_STATE_FIELD_FOUND;
                                
                                        $parts = explode(']', $tok);
                                        
                                        $field = $parts[0];
                                        $value = $parts[1];

                                        $value = str_replace('$dolar', '$', $value);
                                        $value = str_replace('$open_bracket', '[', $value);
                                        
                                        //Binary types must be decoded to binary format
                                        $v = $def[$field];
										if($v['TYPE'] == 'IMAGE')
										{											
											$value = base64_decode($value);
										}
                                        
                                        $this->module->$field = $value;
                                        
                                        //echo $field . ' => ' . $value . '<br>';
                                }
                        }
                        else{
                        
                                $this->state = PARSE_STATE_FIND_FIELD;
                        }
                        
                        $tok = strtok('[');
                }
                
                //echo 'count inserts: ' . $count_inserts . '<br><br>';
        }
}
?>
