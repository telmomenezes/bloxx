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

class Bloxx_Form
{
        var $form_name;

        function Bloxx_Form()
        {
                global $G_FORM_COUNT;
                
                if(!isset($G_FORM_COUNT)){
                
                        $G_FORM_COUNT = 0;
                }
                else{
                
                        $G_FORM_COUNT++;
                }
        
                $this->form_name = 'form' . $G_FORM_COUNT;
        }
        
        function setMode($mode)
        {
                $this->mode = $mode;
        }
        
        function setParam($param)
        {
                $this->param = $param;
        }
        
        function setFromGlobals()
        {
                global $HTTP_GET_VARS;
        
                if(isset($HTTP_GET_VARS['return_mode'])){
                
                        $this->mode = $HTTP_GET_VARS['return_mode'];
                }
                else if(isset($HTTP_GET_VARS['mode'])){

                        $this->mode = $HTTP_GET_VARS['mode'];
                }
                
                if(isset($HTTP_GET_VARS['return_param'])){

                        $this->param = $HTTP_GET_VARS['return_param'];
                }
                else if(isset($HTTP_GET_VARS['param'])){

                        $this->param = $HTTP_GET_VARS['param'];
                }
                
                if(isset($HTTP_GET_VARS['return_target'])){

                        $this->target = $HTTP_GET_VARS['return_target'];
                }
                else if(isset($HTTP_GET_VARS['target'])){

                        $this->target = $HTTP_GET_VARS['target'];
                }
        }
        
        function renderHeader($module, $command, $id = -1, $style = null)
        {
                global $HTTP_GET_VARS;
                
                $html_out = '<form enctype="multipart/form-data"';
                
                if($style != null){
                
                        $html_out .= ' class="' . $style . '"';
                }
                
                $html_out .= ' name="' . $this->form_name . '"';
                $html_out .= ' action="index.php';
                
                $url_params = array();
                
                if($id > 0){
                
                        $url_params['id'] = $id;
                }
                else if(isset($HTTP_GET_VARS['return_id'])){

                        $url_params['id'] = $HTTP_GET_VARS['return_id'];
                }
                else if(isset($HTTP_GET_VARS['id'])){
                
                        $url_params['id'] = $HTTP_GET_VARS['id'];
                }
                
                if(isset($this->mode)){
                
                        $url_params['mode'] = $this->mode;
                }
                
                if(isset($this->param)){

                        $url_params['param'] = $this->param;
                }
                
                if(isset($this->target)){

                        $url_params['target'] = $this->target;
                }
                
                if(count($url_params) > 0){

                        $html_out .= '?';
                }
                
                $first_param = true;
                
                foreach($url_params as $k => $v){

                        if($first_param){
                        
                                $first_param = false;
                        }
                        else{
                        
                                $html_out .=  '&';
                        }
                
                        $html_out .=  $k . '=' . $v;
                }
                
                $html_out .=  '" method="POST">';
                
                $html_out .= '
                <input name="module" type="hidden" value="'.$module.'">
                <input name="command" type="hidden" value="'.$command.'">
                ';
                
                return $html_out;
        }
        
        function renderFooter()
        {
                $html_out = '</form>';
                return $html_out;
        }
        
        function renderSubmitButton($text, $style)
        {
                include_once CORE_DIR . 'bloxx_style.php';
                $styleobj = new Bloxx_Style();
        
                $html_out = $styleobj->renderStyleHeader($style);
        
                $html_out .= '
                <input type="SUBMIT" name="submit" value="'.$text.'" class="'.$style.'">
                ';
                
                $html_out .= $styleobj->renderStyleFooter($style);
                
                return $html_out;
        }
        
        function renderSubmitLink($text, $style)
        {
                include_once CORE_DIR . 'bloxx_style.php';
                $styleobj = new Bloxx_Style();

                $html_out = '
                <a href="javascript:document.' . $this->form_name . '.submit()"';
                $html_out .= '>';
                $html_out .= $text . '</a>';

                return $html_out;
        }
        
        function renderInput($name, $type, $value, $style, $size=20, $maxlength=255)
        {
                include_once CORE_DIR . 'bloxx_style.php';
                $styleobj = new Bloxx_Style();
        
                $html_out = $styleobj->renderStyleHeader($style);
        
                $html_out .= '
                <input name="'.$name.'" type="'.$type.'" value="'.$value.'" class="'.$style.'" size="'.$size.'" maxlength="'.$maxlength.'">';
                
                $html_out .= $styleobj->renderStyleFooter($style);
                
                return $html_out;
        }
        
        function startSelect($name, $size, $style)
        {
                include_once CORE_DIR . 'bloxx_style.php';
                $styleobj = new Bloxx_Style();

                $html_out = $styleobj->renderStyleHeader($style);
        
                $html_out .= '
                <SELECT size="'.$size.'" name="'.$name.'" class="'.$style.'">
                ';
                
                $html_out .= $styleobj->renderStyleFooter($style);
                
                return $html_out;
        }
        
        function endSelect()
        {
                $html_out = '
                </SELECT>
                ';
                
                return $html_out;
        }
        
        function addSelectItem($value, $label, $selected = false)
        {
                $html_out = '<OPTION ';
                
                if($selected){
                
                        $html_out .= 'selected ';
                }
                
                $html_out .= 'value=" ' . $value . ' ">
                ' . $label;
                
                return $html_out;
        }
        
        function renderTextArea($name, $rows, $cols, $value, $style)
        {
                include_once CORE_DIR . 'bloxx_style.php';
                $styleobj = new Bloxx_Style();

                $html_out = $styleobj->renderStyleHeader($style);
        
                $html_out .=  '<textarea name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '" class="'.$style.'">';
                $html_out .=  $value;
                $html_out .=  '</textarea>';
                
                $html_out .= $styleobj->renderStyleFooter($style);
                
                return $html_out;
        }
        
        function renderMonthSelector($name, $value, $style)
        {
                $html_out = $this->startSelect($name, 1, $style);
                if($value == 1) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(1, LANG__JANUARY, $sel);
                if($value == 2) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(2, LANG__FEBRUARY, $sel);
                if($value == 3) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(3, LANG__MARCH, $sel);
                if($value == 4) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(4, LANG__APRIL, $sel);
                if($value == 5) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(5, LANG__MAY, $sel);
                if($value == 6) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(6, LANG__JUNE, $sel);
                if($value == 7) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(7, LANG__JULY, $sel);
                if($value == 8) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(8, LANG__AUGUST, $sel);
                if($value == 9) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(9, LANG__SEPTEMBER, $sel);
                if($value == 10) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(10, LANG__OCTOBER, $sel);
                if($value == 11) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(11, LANG__NOVEMBER, $sel);
                if($value == 12) $sel = true; else $sel = false;
                $html_out .= $this->addSelectItem(12, LANG__DECEMBER, $sel);
                $html_out .= $this->endSelect();
                
                return $html_out;
        }
}

?>
