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

function include_module_once($module)
{
        global $BLOXX_INCLUDED_MODULES;
        
        if(isset($BLOXX_INCLUDED_MODULES[$module])){
        
                return;
        }
        
        $BLOXX_INCLUDED_MODULES[$module] = true;

        $file_name = 'bloxx_' . strtolower($module) . '.php';
        
        if(file_exists(CORE_DIR.$file_name)){
        
                include_once(CORE_DIR.$file_name);
        }
        else if(file_exists(MODS_DIR.$file_name)){

                include_once(MODS_DIR.$file_name);
        }
        
        include_once(CORE_DIR.'bloxx_config.php');
        
        $config = new Bloxx_Config();
        $lang = $config->getConfig('default_language');

        //Include module language module
        $lang_file = LANG_DIR . $lang . '/' . 'bloxx_lang_' . $lang . '_' . strtolower($module) . '.php';
        
        if(file_exists($lang_file)){

                include_once($lang_file);
        }

        //Inlude global language module
        $lang_file = LANG_DIR . $lang . '/' . 'bloxx_lang_' . $lang . '.php';

        if(file_exists($lang_file)){

                include_once($lang_file);
        }
}

function include_enum_once($enum)
{
        global $BLOXX_INCLUDED_ENUMS;
        
        $enum_name = 'ENUM_' . $enum;

        if(isset($BLOXX_INCLUDED_ENUMS[$enum_name])){

                return;
        }

        $BLOXX_INCLUDED_ENUMS[$enum_name] = true;

        $file_name = strtolower($enum_name) . '.php';

        if(file_exists(ENUM_DIR . $file_name)){

                include_once(ENUM_DIR . $file_name);
        }

        include_once(CORE_DIR.'bloxx_config.php');

        $config = new Bloxx_Config();
        $lang = $config->getConfig('default_language');

        //Include module language module
        $lang_file = LANG_DIR . $lang . '/' . 'enum_lang_' . $lang . '_' . strtolower($enum) . '.php';

        if(file_exists($lang_file)){

                include_once($lang_file);
        }
}

function build_link($id, $view, $param, $target, $link_text, $return, $vars = null)
{
        global $_GET;

        $param_before = false;

        $html_out = '<a href="index.php?';
        
        if(($id != '') && ($id != null)){
        
                $html_out .= 'id=' . $id;
                
                $param_before = true;
        }
        
        if(($view != '') && ($view != null)){
        
                if($param_before){
                
                        $html_out .= '&';
                }

                $html_out .= 'mode=' . $view;

                $param_before = true;
        }
        
        if(($param != '') && ($param != null)){

                if($param_before){

                        $html_out .= '&';
                }

                $html_out .= 'param=' . $param;

                $param_before = true;
        }
        
        if(($target != '') && ($target != null)){

                if($param_before){

                        $html_out .= '&';
                }

                $html_out .= 'target=' . $target;

                $param_before = true;
        }
        
        if($return == true){

                if(isset($_GET['id'])){
                
                        if($param_before){

                                $html_out .= '&';
                        }

                        $html_out .= 'return_id=' . $_GET['id'];

                        $param_before = true;
                }
                if(isset($_GET['view'])){

                        if($param_before){

                                $html_out .= '&';
                        }

                        $html_out .= 'return_view=' . $_GET['view'];

                        $param_before = true;
                }
                if(isset($_GET['param'])){

                        if($param_before){

                                $html_out .= '&';
                        }

                        $html_out .= 'return_param=' . $_GET['param'];

                        $param_before = true;
                }
                if(isset($_GET['target'])){

                        if($param_before){

                                $html_out .= '&';
                        }

                        $html_out .= 'return_target=' . $_GET['target'];

                        $param_before = true;
                }
        }
        
        if(isset($vars)){
        
                foreach($vars as $k => $v){
                
                        if($param_before){

                                $html_out .= '&';
                        }
                        
                        $html_out .= $k . '=' . $v;
                        
                        $param_before = true;
                }
        }
        
        $html_out .= '">' . $link_text . '</a>';
        
        return $html_out;
}

function getExtraGetVars()
{
        global $_GET;
        $extra_vars = array();
        
        foreach($_GET as $k => $v){
        
                if(($k != 'id')
                        && ($k != 'mode')
                        && ($k != 'param')
                        && ($k != 'target')
                        && ($k != 'return_id')
                        && ($k != 'return_view')
                        && ($k != 'return_param')
                        && ($k != 'return_target')){
                        
                        $extra_vars[$k] = $v;
                }
        }
        
        return $extra_vars;
}

function getDateAndTimeString($timestamp)
{
        $dts = date("Y-m-d H:i", $timestamp);
        
        return $dts;
}

function getDateString($timestamp)
{
        $dts = date("Y-m-d", $timestamp);

        return $dts;
}
?>
