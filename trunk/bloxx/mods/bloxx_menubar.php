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
// $Id: bloxx_menubar.php,v 1.3 2005-02-18 17:35:46 tmenezes Exp $

require_once 'defines.php';

require_once(CORE_DIR.'bloxx_module.php');



class Bloxx_MenuBar extends Bloxx_Module

{

        var $menuTopShift;
        var $menuRightShift;
        var $menuLeftShift;
        var $thresholdY;
        var $abscissaStep;
        var $menuStructure;
        var $tree;



        function Bloxx_MenuBar()

        {

                $this->name = 'menubar';

                $this->module_version = 1;

                $this->label_field = 'barname';

                

                $this->use_init_file = true;

                $this->no_private = true;



                $this->menuStructure = "";

                $this->separator = "|";



                $this->_nodesCount = 0;

                $this->tree = array();

                $this->_maxLevel = array();

                $this->_firstLevelCnt = array();

                $this->_firstItem = array();

                $this->_lastItem = array();



                $this->header = "";

                $this->listl = "";

                $this->father_keys = "";

                $this->father_vals = "";

                $this->moveLayers = "";

                $this->_firstLevelMenu = array();

                $this->footer = "";



                $this->transparentIcon = "transparent.png";

                $this->_hasIcons = array();

                $this->forwardArrowImg["src"] = "res/system/forward-arrow.png";

                $this->forwardArrowImg["width"] = 4;

                $this->forwardArrowImg["height"] = 7;

                $this->downArrowImg["src"] = "res/system/down-arrow.gif";

                $this->downArrowImg["width"] = 9;

                $this->downArrowImg["height"] = 5;

                $this->menuTopShift = 6;

                $this->menuRightShift = 7;

                $this->menuLeftShift = 2;

                $this->thresholdY = 5;

                $this->abscissaStep = 140;
                
                $this->prependedUrl = null;
                
                $this->imgdir = null;
                
                $this->imgwww = null;
                
                $this->Bloxx_Module();

        }

        

        function getTableDefinition()

        {

                return array(

                        'barname' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true),

                        'code' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => true, 'LANG' => true)

                );

        }

        

        function getRenderTrusts()

        {

                return array(

                        'menubar' => TRUST_GUEST

                );

        }

        

        function getStyleList()

        {

                return array(

                        'Menubar' => 'Menubar',

                        'Menuitem' => 'Menuitem',

                        'Submenu' => 'Submenu',

                        'Subframe' => 'Submenuframe',

                        'Item' => 'Submenuitem',

                        'Arrow' => 'MenuArrow'

                );

        }

        

        function doRender($mode, $id, $target)

        {

                

                $this->style_menubar = $this->getGlobalStyle('Menubar');

                $this->style_menuitem = $this->getGlobalStyle('Menuitem');

                $this->style_submenu = $this->getGlobalStyle('Submenu');

                $this->style_subframe = $this->getGlobalStyle('Subframe');

                $this->style_item = $this->getGlobalStyle('Item');

                $this->style_arrow = $this->getGlobalStyle('Arrow');

        

                if($mode == 'menubar'){



                        $this->getRowByID($id);



                        $html_out = $this->printMenu("menu");

                        $html_out .= $this->printFooter();



                        return $html_out;

                }

        }

        

        function doRenderJavaScript($mode, $id)

        {

                $this->style_menubar = $this->getGlobalStyle('Menubar');

                $this->style_menuitem = $this->getGlobalStyle('Menuitem');

                $this->style_submenu = $this->getGlobalStyle('Submenu');

                $this->style_subframe = $this->getGlobalStyle('Subframe');

                $this->style_item = $this->getGlobalStyle('Item');

                $this->style_arrow = $this->getGlobalStyle('Arrow');

        

                if($mode == 'menubar'){

                

                        $this->getRowByID($id);

                        

                        $this->setMenuStructureString($this->code);

                        $this->parseStructureForMenu("menu");

                        $this->newHorizontalMenu("menu");

                        $js_out = $this->printHeader();



                        return $js_out;

                }

        }



        function parseCommon($menu_name = "")

        {



                $this->_hasIcons[$menu_name] = false;

                

                for ($cnt = $this->_firstItem[$menu_name]; $cnt <= $this->_lastItem[$menu_name]; $cnt++){

                

                        $this->_hasIcons[$cnt] = false;

                        $this->tree[$cnt]["layer_label"] = "L" . $cnt;

                        $current_node[$this->tree[$cnt]["level"]] = $cnt;

                        

                        if(!$this->tree[$cnt]["child_of_root_node"]){

                        

                                $this->tree[$cnt]["father_node"] = $current_node[$this->tree[$cnt]["level"]-1];

                                $this->father_keys .= ",'L" . $cnt . "'";

                                $this->father_vals .= ",'" . $this->tree[$this->tree[$cnt]["father_node"]]["layer_label"] . "'";

                        }

                        

                        $this->tree[$cnt]["not_a_leaf"] = ($this->tree[$cnt+1]["level"]>$this->tree[$cnt]["level"] && $cnt<$this->_lastItem[$menu_name]);

                        

                        if($this->tree[$cnt]["not_a_leaf"]){

                        

                                $this->tree[$cnt]["layer_content"] = "";

                                $this->listl .= ",'" . $this->tree[$cnt]["layer_label"] . "'";

                        }

                        

                        if($this->tree[$cnt]["parsed_icon"] == ""){

                        

                                $this->tree[$cnt]["iconsrc"] = $this->transparentIcon;

                                $this->tree[$cnt]["iconwidth"] = 16;

                                $this->tree[$cnt]["iconheight"] = 16;

                                $this->tree[$cnt]["iconalt"] = " ";

                        }

                        else{

                        

                                if($this->tree[$cnt]["level"] > 1){

                                

                                        $this->_hasIcons[$this->tree[$cnt]["father_node"]] = true;

                                }

                                else{

                                

                                        $this->_hasIcons[$menu_name] = true;

                                }

                                

                                $this->tree[$cnt]["iconsrc"] = $this->tree[$cnt]["parsed_icon"];

                                $this->tree[$cnt]["iconalt"] = "O";

                        }

                }

        }



        function _updateFooter($menu_name = "")

        {

        

                for($cnt = $this->_firstItem[$menu_name]; $cnt <= $this->_lastItem[$menu_name]; $cnt++){

                

                        if($this->tree[$cnt]["not_a_leaf"]){



                                $this->footer .= '

<div id="' . $this->tree[$cnt]["layer_label"] . '" class="' . $this->style_submenu . '" onmouseover="clearLMTO();" onmouseout="setLMTO();">

<table border="0" cellspacing="0" cellpadding="0">

<tr>

<td nowrap="nowrap">

<div class="' . $this->style_subframe . '">

' . $this->tree[$cnt]["layer_content"] . '

</div>

</td>

</tr>

</table>

</div>

                        ';

                        }

                }

        }



        function newHorizontalMenu($menu_name = "")

        {

                if(!isset($this->_firstItem[$menu_name]) || !isset($this->_lastItem[$menu_name])){



                        return false;

                }



                $this->parseCommon($menu_name);



                $this->_firstLevelMenu[$menu_name] = "";



                $Bloxx = $this->_firstItem[$menu_name];

                $this->moveLayers .= "\tvar " . $menu_name . "TOP = getOffsetTop('" . $menu_name . "L" . $Bloxx . "');\n";

                $this->moveLayers .= "\tvar " . $menu_name . "HEIGHT = getOffsetHeight('" . $menu_name . "L" . $Bloxx . "');\n";

                $menu = '';

                for($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++){



                        if($this->tree[$cnt]["not_a_leaf"]){

                        

                                if($this->tree[$cnt]["child_of_root_node"]){

                                

                                        $this->moveLayers .= "\tsetTop('" . $this->tree[$cnt]["layer_label"] . "', "  . $menu_name . "TOP + " . $menu_name . "HEIGHT);\n";

                                        $this->moveLayers .= "\tmoveLayerX1('" . $this->tree[$cnt]["layer_label"] . "', '" . $menu_name . "');\n";

                                }

                        }



                        if($this->tree[$cnt]["child_of_root_node"]){

                        

                                if($this->tree[$cnt]["not_a_leaf"]){

                                

                                        $this->tree[$cnt]["onmouseover"] = " onmouseover=\"moveLayerX1('" . $this->tree[$cnt]["layer_label"] . "', '" . $menu_name . "') ; LMPopUp('" . $this->tree[$cnt]["layer_label"] . "', false);\"";

                                }

                                else{

                                

                                        $this->tree[$cnt]["onmouseover"] = " onmouseover=\"shutdown();\"";

                                }


                                $menu .= '

<td><div id="' . $menu_name . $this->tree[$cnt]["layer_label"] .

'" class="' . $this->style_menuitem . '" onmouseover="clearLMTO();" onmouseout="setLMTO();">

<a href="' . $this->tree[$cnt]["parsed_href"] .

'"' . $this->tree[$cnt]["onmouseover"] . $this->tree[$cnt]["parsed_title"] .

$this->tree[$cnt]["parsed_target"] .'><img

align="top" src="' . $this->imgwww . $this->transparentIcon .

'" width="1" height="16" border="0" alt="" />

                        ';



                                if($this->tree[$cnt]["parsed_icon"] != ""){



                                        $menu .= '

<img

align="top" src="' . $this->imgwww . $this->tree[$cnt]["iconsrc"] .

'" width="' . $this->tree[$cnt]["iconwidth"] . '" height="' .

$this->tree[$cnt]["iconheight"] . '" border="0"

alt="' . $this->tree[$cnt]["iconalt"] . '" />&nbsp;

                                ';

                                }



                                $menu .= $this->tree[$cnt]["text"];



                                if($this->tree[$cnt]["not_a_leaf"]){



                                        $menu .= '

&nbsp;<img src="' . $this->downArrowImg["src"] .

'" width="' . $this->downArrowImg["width"] . '" height="' . $this->downArrowImg["height"] . '"

border="0" alt=">>" />

                                        ';

                                }



                                $menu .= '

&nbsp;&nbsp;&nbsp;</a>

</div>

</td>

                                ';

                        }

                        else{

                        

                                if($this->tree[$cnt]["not_a_leaf"]){

                                

                                        $this->tree[$cnt]["onmouseover"] = " onmouseover=\"moveLayerX('" . $this->tree[$cnt]["layer_label"] . "') ; moveLayerY('" . $this->tree[$cnt]["layer_label"] . "') ; LMPopUp('" . $this->tree[$cnt]["layer_label"] . "', false);\"";

                                }

                                else{

                                

                                        $this->tree[$cnt]["onmouseover"] = " onmouseover=\"LMPopUp('" . $this->tree[$this->tree[$cnt]["father_node"]]["layer_label"] . "', true);\"";

                                }



                                $sub = '

<div id="ref' . $this->tree[$cnt]["layer_label"] . '" class="' . $this->style_item . '">

<a href="' . $this->tree[$cnt]["parsed_href"] . '"' . $this->tree[$cnt]["onmouseover"] . $this->tree[$cnt]["parsed_title"] .

$this->tree[$cnt]["parsed_target"] . '><img

align="top" src="' . $this->imgwww . $this->transparentIcon . '" width="1" height="16" border="0"

alt="" />

                                ';



                                if($this->_hasIcons[$this->tree[$cnt]["father_node"]]){



                                        $sub .= '

<img align="top" src="

' . $this->imgwww . $this->tree[$cnt]["iconsrc"] . '"

width="' . $this->tree[$cnt]["iconwidth"] . '" height="' .

$this->tree[$cnt]["iconheight"] . '" border="0"

alt="' . $this->tree[$cnt]["iconalt"] . '" />&nbsp;

                                        ';

                                }



                                $sub .= $this->tree[$cnt]["text"];



                                if($this->tree[$cnt]["not_a_leaf"]){



                                        $sub .= '

&nbsp;<img

class="' . $this->style_arrow . '" src="' . $this->forwardArrowImg["src"] . '"

width="' . $this->forwardArrowImg["width"] . '" height="' .

$this->forwardArrowImg["height"] . '"border="0" alt=">>" />

                                        ';

                                }



                                $sub .= '

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>

</div>

                                ';



                                $this->tree[$this->tree[$cnt]["father_node"]]["layer_content"] .= $sub;

                        }

                }



                $menuRoot = '

<table border="0" cellspacing="0" cellpadding="0">

<tr>

<td>

<div class="' . $this->style_menubar . '">

<table border="0" cellspacing="0" cellpadding="0">

<tr>

                ';



                $menuRoot .= $menu;



                $menuRoot .= '

</tr>

</table>

</div>

</td>

</tr>

</table>

                ';



                $this->_firstLevelMenu[$menu_name] = $menuRoot;



                $this->_updateFooter($menu_name);



                return $this->_firstLevelMenu[$menu_name];

        }



        function makeHeader()

        {



                $this->listl = "listl = [" . substr($this->listl, 1) . "];";

                $this->father_keys = "father_keys = [" . substr($this->father_keys, 1) . "];";

                $this->father_vals = "father_vals = [" . substr($this->father_vals, 1) . "];";



                $this->header = '

        <script language="JavaScript" type="text/javascript">

<!--



menuTopShift = ' . $this->menuTopShift . ';

menuRightShift = ' . $this->menuRightShift . ';

menuLeftShift = ' . $this->menuLeftShift . ';



var thresholdY = ' . $this->thresholdY . ';

var abscissaStep = ' . $this->abscissaStep . ';



toBeHidden = new Array();

toBeHiddenLeft = new Array();

toBeHiddenTop = new Array();



' . $this->listl . '

var numl = listl.length;



father = new Array();

for (i=1; i<=' . $this->_nodesCount . '; i++) {

        father["L" + i] = "";

}

' . $this->father_keys . '

' . $this->father_vals . '

for (i=0; i<father_keys.length; i++) {

        father[father_keys[i]] = father_vals[i];

}



lwidth = new Array();

var lwidthDetected = 0;



function moveLayers() {

        if (!lwidthDetected) {

                for (i=0; i<numl; i++) {

                        lwidth[listl[i]] = getOffsetWidth(listl[i]);

                }

                lwidthDetected = 1;

        }

        if (IE4) {

                for (i=0; i<numl; i++) {

                        setWidth(listl[i], abscissaStep);

                }

        }

' . $this->moveLayers . '

}



back = new Array();

for (i=1; i<=' . $this->_nodesCount . '; i++) {

        back["L" + i] = 0;

}



// -->

</script>

                ';



                return $this->header;

        }



        function printHeader()

        {

                $this->makeHeader();

                return $this->header;

        }



        function printMenu($menu_name)

        {

                return $this->_firstLevelMenu[$menu_name];

        }



        function makeFooter()

        {



                $this->footer .= '

<script language="JavaScript" type="text/javascript">

<!--

loaded = 1;

// -->

</script>

                ';



                return $this->footer;

        }



        function printFooter()

        {



                $this->makeFooter();

                return $this->footer;

        }



        function setMenuStructureString($tree_string)

        {

        

                $this->menuStructure = ereg_replace(chr(13), "", $tree_string);

                if ($this->menuStructure == "") {



                        return false;

                }

                return true;

        }



        function parseStructureForMenu($menu_name = "")

        {

        

                $this->_maxLevel[$menu_name] = 0;

                $this->_firstLevelCnt[$menu_name] = 0;

                $this->_firstItem[$menu_name] = $this->_nodesCount + 1;

                $cnt = $this->_firstItem[$menu_name];

                $menuStructure = $this->menuStructure;



                while($menuStructure != ""){

                

                        $before_cr = strcspn($menuStructure, "\n");

                        $buffer = substr($menuStructure, 0, $before_cr);

                        $menuStructure = substr($menuStructure, $before_cr+1);

                        

                        if(substr($buffer, 0, 1) != "#"){

                        

                                $tmp = rtrim($buffer);

                                $node = explode($this->separator, $tmp);

                                

                                for($i = count($node); $i <= 6; $i++){

                                

                                        $node[$i] = "";

                                }

                                

                                $this->tree[$cnt]["level"] = strlen($node[0]);

                                $this->tree[$cnt]["text"] = $node[1];

                                $this->tree[$cnt]["href"] = $node[2];

                                $this->tree[$cnt]["title"] = $node[3];

                                $this->tree[$cnt]["icon"] = $node[4];

                                $this->tree[$cnt]["target"] = $node[5];

                                $this->tree[$cnt]["expanded"] = $node[6];

                                $cnt++;

                        }

                }



                $this->_lastItem[$menu_name] = count($this->tree);

                $this->_nodesCount = $this->_lastItem[$menu_name];

                $this->tree[$this->_lastItem[$menu_name]+1]["level"] = 0;

                $this->_postParse($menu_name);

        }



        function _postParse($menu_name = "")

        {

        

                for($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++){

                

                        $this->tree[$cnt]["child_of_root_node"] = ($this->tree[$cnt]["level"] == 1);

                        $this->tree[$cnt]["parsed_text"] = stripslashes($this->tree[$cnt]["text"]);

                        $this->tree[$cnt]["parsed_href"] = (ereg_replace(" ", "", $this->tree[$cnt]["href"]) == "") ? "#" : $this->prependedUrl . $this->tree[$cnt]["href"];

                        $this->tree[$cnt]["parsed_title"] = ($this->tree[$cnt]["title"] == "") ? "" : " title=\"" . addslashes($this->tree[$cnt]["title"]) . "\"";

                        $fooimg = $this->imgdir . $this->tree[$cnt]["icon"];

                        

                        if($this->tree[$cnt]["icon"] == "" || !(file_exists($fooimg))){

                        

                                $this->tree[$cnt]["parsed_icon"] = "";

                        }

                        else{

                        

                                $this->tree[$cnt]["parsed_icon"] = $this->tree[$cnt]["icon"];

                                $Bloxx = getimagesize($fooimg);

                                $this->tree[$cnt]["iconwidth"] = $Bloxx[0];

                                $this->tree[$cnt]["iconheight"] = $Bloxx[1];

                        }

                        

                        $this->tree[$cnt]["parsed_target"] = ($this->tree[$cnt]["target"] == "") ? "" : " target=\"" . $this->tree[$cnt]["target"] . "\"";

                        $this->_maxLevel[$menu_name] = max($this->_maxLevel[$menu_name], $this->tree[$cnt]["level"]);

                        

                        if ($this->tree[$cnt]["level"] == 1){

                        

                                $this->_firstLevelCnt[$menu_name]++;

                        }

                }

        }

}

?>

