<?php

//
// Bloxx - Open Source Content Management System
//
// Copyright 2002 - 2005 Telmo Menezes. All rights reserved.
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

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_ModuleTemplate extends Bloxx_Module
{
        var $items;

        function Bloxx_ModuleTemplate()
        {
                $this->name = 'moduletemplate';
                $this->module_version = 1;
                $this->label_field = 'view';
                $this->use_init_file = true;
                
                $this->items = array();
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'moduleid' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'view' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'template' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => true),

                );
        }
        
        function setItem($item, $value)
        {

                $this->items[$item] = $value;
        }
        
        function renderView()
        {
                
                $begin_tag = true;

                if(substr($this->template, 0, 1) != "<"){

                        $begin_tag = false;
                }

                $tok = strtok($this->template, '<');
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

                                $this->parseBloxx($bloxx_content, $html_out);

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

                        $tok = strtok('<');
                }
                
                return $html_out;
        }
        
        function parseBloxx($bloxx_html, &$html_out)
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

                $html_out .= $this->items[$name];
        }
        
        function getTemplate($mod, $view)
        {
                $mod_id = $mod->getModID();
                
                $this->clearWhereCondition();
                $this->insertWhereCondition("moduleid=" . $mod_id);
                $this->insertWhereCondition("view='" . $view . "'");
                $this->runSelect();

                if (!$this->nextRow()){
                
                        return false;
                }
                
                return true;
        }
}
?>
