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
// $Id: bloxx_search.php,v 1.3 2005-02-18 17:34:56 tmenezes Exp $

require_once 'defines.php';
include_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Search extends Bloxx_Module
{
        function Bloxx_Search()
        {
                $this->name = 'search';
                $this->module_version = 1;
                $this->label_field = 'id';
                $this->use_init_file = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'module_id' => array('TYPE' => 'BLOXX_MODULEMANAGER', 'SIZE' => -1, 'NOTNULL' => true),
                        'view' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'num_rows' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'num_columns' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'html_header' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false),
                        'html_footer' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false),
                        'html_after_row' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false),
                        'html_after_column' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }
        
        function getRenderTrusts()
        {
                return array(
                        'list' => TRUST_GUEST,
                        'prev' => TRUST_GUEST,
                        'next' => TRUST_GUEST
                );
        }
        
        function doRender($mode, $id, $target)
        {
        
                $this->getRowByID($id);
                
                if(!isset($target)){
                
                        $target = -1;
                }
                
                if($mode == 'list'){

                        $html_out = $this->html_header;
                
                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($this->module_id);
                        $mname = $mm->module_name;
                        include_module_once($mname);
                        $mname = 'Bloxx_' . $mname;
                        $inst = new $mname();
                        
                        $html_out .= $inst->renderList($target,
                                $this->num_columns,
                                $this->num_rows,
                                $this->view,
                                $this->html_after_row,
                                $this->html_after_column);
                                
                        $html_out .= $this->html_footer;
                        
                        return $html_out;
                }
                else if($mode == 'next'){
                
                        global $_GET;
                        
                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($this->module_id);
                        $mname = $mm->module_name;
                        include_module_once($mname);
                        $mname = 'Bloxx_' . $mname;
                        $inst = new $mname();
                        
                        $next_id = $inst->nextListID($target, $this->num_columns * $this->num_rows);

                        if($next_id != -1){
                
                                $html_out = build_link($_GET['id'],
                                                null,
                                                null,
                                                $next_id,
                                                'next >',
                                                false,
                                                getExtraGetVars());
                                                
                                return $html_out;
                        }
                        else{
                        
                                return '';
                        }
                }
                else if($mode == 'prev'){

                        global $_GET;

                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($this->module_id);
                        $mname = $mm->module_name;
                        include_module_once($mname);
                        $mname = 'Bloxx_' . $mname;
                        $inst = new $mname();
                        
                        $prev_id = $inst->previousListID($target, $this->num_columns * $this->num_rows);
                        
                        if($prev_id != $target){

                                $html_out = build_link($_GET['id'],
                                                null,
                                                null,
                                                $prev_id,
                                                '< prev',
                                                false,
                                                getExtraGetVars());
                                                
                                return $html_out;
                        }
                        else{
                        
                                return '';
                        }
                }
        }
}
?>
