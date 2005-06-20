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
// $Id: bloxx_moduletemplate.php,v 1.8 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_ModuleTemplate extends Bloxx_Module
{
        var $items;
        var $loops;
        var $loop_count;
        var $loop_index;
        var $current_loop;
        var $tokenizer;

        function Bloxx_ModuleTemplate()
        {
                $this->name = 'moduletemplate';
                $this->module_version = 1;
                $this->label_field = 'view';
                $this->use_init_file = true;
                
                $this->items = array();
                $this->loops = array();
                $this->loop_count = 0;
                $this->loop_index = 0;
                $this->curent_loop = '';
                
                $this->Bloxx_Module();
        }
        
        function init()
        {
       		$this->items = array();
        	$this->loops = array();
            $this->loop_count = 0;
            $this->loop_index = 0;
            $this->curent_loop = '';
        }
        
        function getTableDefinition()
        {
                return array(
                        'moduleid' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'view' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'optional_name' => array('TYPE' => 'STRING', 'SIZE' => 100),
                        'template' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => true)
                );
        }
        
        function setItem($item, $value)
        {

                $this->items[$item] = $value;
                $this->curent_loop = '';
        }
        
        function setLoopItem($item, $value)
        {

                $loop_array = &$this->items[$this->curent_loop];
                
                if(!isset($loop_array[$this->loop_index])){
                
                        $loop_array[$this->loop_index] = array();
                }
                
                $loop_items = &$loop_array[$this->loop_index];
                
                $loop_items[$item] = $value;
        }
        
        function startLoop($loop)
        {

                $this->items[$loop] = array();
                $this->loop_index = 0;
                $this->curent_loop = $loop;
        }
        
        function nextLoopIteration()
        {
                $this->loop_index++;
        }
        
        function renderView()
        {
                include_once(CORE_DIR . 'bloxx_tokenizer.php');
                $this->tokenizer = new Bloxx_Tokenizer();

                $this->parseLoops();
                
                $html_out = '';
                
                foreach($this->loops as $loop_key => $loop_val){

                        if($loop_val['name'] == null){
                        
                                $html_out .= $this->parseItems($loop_val['content']);
                        }
                        else{

                                foreach($this->items[$loop_val['name']] as $item_array){

                                        $html_out .= $this->parseItems($loop_val['content'], $item_array);
                                }
                        }
                }

                return $html_out;
        }
        
        function parseItems($loop_content, $loop_items = null)
        {

                $begin_tag = true;

                if(substr($loop_content, 0, 1) != "<"){

                        $begin_tag = false;
                }

                $tok = $this->tokenizer->getToken($loop_content, '<');
                $count = strlen($tok) + 1;

                $html_out = '';
                $bloxx_content = '';
                $waiting_for_bloxx_end = false;

                while($tok){

                        if(substr($tok, 0, 10) == "bloxx_item"){

                                $waiting_for_bloxx_end = true;
                                $bloxx_content = $tok;
                        }
                        else if(substr($tok, 0, 11) == "/bloxx_item"){

                                $this->parseItem($bloxx_content, $html_out, $loop_items);

                                $waiting_for_bloxx_end = false;
                        }
                        else if($waiting_for_bloxx_end){

                                $bloxx_content .= '<' . $tok;
                        }
                        else{

                                if($begin_tag){

                                        $html_out .= "<";
                                }

                                $html_out .= $tok;
                        }

                        $begin_tag = true;

                        $tok = $this->tokenizer->getToken('<');
                }

                return $html_out;
        }
        
        function parseLoops()
        {

                $begin_tag = true;

                if(substr($this->template, 0, 1) != "<"){

                        $begin_tag = false;
                }

                $tok = $this->tokenizer->getToken($this->template, '<');
                $count = strlen($tok) + 1;

                $html_out = '';
                $loop_content = '';
                $waiting_for_loop_end = false;

                while($tok){

                        if(substr($tok, 0, 10) == "bloxx_loop"){

                                $waiting_for_loop_end = true;
                                $this->addLoop($loop_content);
                                $loop_content = $tok;
                        }
                        else if(substr($tok, 0, 11) == "/bloxx_loop"){

                                $this->parseLoop($loop_content);

                                $waiting_for_loop_end = false;
                                $loop_content = '';
                        }
                        else if($waiting_for_loop_end){

                                $loop_content .= '<' . $tok;
                        }
                        else{

                                if($begin_tag){

                                        $loop_content .= "<";
                                }

                                $loop_content .= $tok;
                        }

                        $begin_tag = true;

                        $tok = $this->tokenizer->getToken('<');
                }

                $this->addLoop($loop_content);
        }
        
        function addLoop($loop_content, $loop_name = null)
        {

                if($loop_content == ''){
                
                        return;
                }
                
                $this->loops[$this->loop_count++] = array('name' => $loop_name,
                                                        'content' => $loop_content);
        }
        
        function parseLoop($loop_content)
        {
                
                $regex = '(bloxx_loop[^>]*>)(.*)';
                ereg($regex, $loop_content, $regs);

                $loop_tag = $regs[1];
                $content = $regs[2];
                
                $nparams = substr_count($loop_tag, "=");

                $regex = '([^> ]*)';

                for($n = 0; $n < $nparams; $n++){

                        $regex .= ' ([^> ]*)';
                }

                $regex .= '>(.*)';

                ereg($regex, $loop_tag, $regs);
                $tag = $regs[1];

                for($n = 0; $n < $nparams; $n++){

                        ereg('(.*)="(.*)"', $regs[$n + 2], $par);
                        $$par[1] = $par[2];
                }

                $this->addLoop($content, $name);
        }
        
        function parseItem($bloxx_html, &$html_out, $loop_items = null)
        {

                $nparams = substr_count($bloxx_html, "=");

                $regex = '([^> ]*)';

                for($n = 0; $n < $nparams; $n++){

                        $regex .= ' ([^> ]*)';
                }

                $regex .= '>(.*)';

                ereg($regex, $bloxx_html, $regs);
                $tag = $regs[1];

                for($n = 0; $n < $nparams; $n++){

                        ereg('(.*)="(.*)"', $regs[$n + 2], $par);
                        $$par[1] = $par[2];
                }

                if($loop_items == null){
                
                        $html_out .= $this->items[$name];
                }
                else{
                
                        $html_out .= $loop_items[$name];
                }
        }
        
        function getTemplate($mod, $view, $template)
        {
        
                $mod_id = $mod->getModID();
                
                $this->clearWhereCondition();
                $this->insertWhereCondition('moduleid', '=', $mod_id);
                $this->insertWhereCondition('view', '=', $view);
                
                if ($template != null)
                {
                	$this->insertWhereCondition('optional_name', '=', $template);
                }
                
                $this->runSelect();

                if (!$this->nextRow()){
                
                        return false;
                }
                
                return true;
        }
        
        function renderLabel()
        {
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                $mm->getRowByID($this->moduleid);
                $label = $mm->module_name . ' -> ' . $this->view;
                return $label;
        }
}
?>
