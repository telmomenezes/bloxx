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
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_PersonalInfo extends Bloxx_Module
{
        function Bloxx_PersonalInfo()
        {
                $this->name = 'personalinfo';
                $this->module_version = 1;
                $this->label_field = 'full_name';

                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }

        function getTableDefinition()
        {
                return array(
                        'user_id' => array('TYPE' => 'BLOXX_IDENTITY', 'SIZE' => -1, 'NOTNULL' => true, 'HIDDEN' => true),
                        'address' => array('TYPE' => 'STRING', 'SIZE' => 255, 'USER' => true, 'CONFIDENTIAL' => true),
                        'postal_code' => array('TYPE' => 'STRING', 'SIZE' => 10, 'USER' => true, 'CONFIDENTIAL' => true),
                        'city' => array('TYPE' => 'STRING', 'SIZE' => 50, 'USER' => true, 'CONFIDENTIAL' => true),
                        'phone_home' => array('TYPE' => 'STRING', 'SIZE' => 20, 'USER' => true, 'CONFIDENTIAL' => true),
                        'phone_work' => array('TYPE' => 'STRING', 'SIZE' => 20, 'USER' => true, 'CONFIDENTIAL' => true),
                        'phone_mobile' => array('TYPE' => 'STRING', 'SIZE' => 20, 'USER' => true, 'CONFIDENTIAL' => true),
                        'birth_date' => array('TYPE' => 'DATE', 'SIZE' => -1, 'USER' => true),
                        'info' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'USER' => true)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'info' => TRUST_GUEST,
                        'form' => TRUST_USER,
                        'user_link' => TRUST_USER,
                        'list' => TRUST_GUEST
                );
        }

        function getStyleList()
        {
                return array(
                        'Title' => 'NormalTitle',
                        'Text' => 'NormalText',
                        'Link' => 'NormalLink'
                );
        }

        function doRender($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');
                $style_text = $this->getGlobalStyle('Text');
                $style_link = $this->getGlobalStyle('Link');

                if($mode == 'info'){

                        $this->getRowByID($id);

                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                        $ident->getRowByID($this->user_id);
                        
                        $html_out = $ident->renderRow($style_title, $style_text);
                        $html_out .= $this->renderRow($style_title, $style_text);

                        return $html_out;
                }
                else if($mode == 'form'){
                
                        include_module_once('identity');
                        $ident = new Bloxx_Identity();

                        $this->user_id = $ident->id();
                        $html_out = $this->renderForm($id, false);

                        return $html_out;
                }
                else if($mode == 'user_link'){
                
                        $edit_page = $this->getValue('edit_page');
                
                        if($this->infoExists()){

                                include_module_once('identity');
                                $ident = new Bloxx_Identity();
                                
                                $info = new Bloxx_PersonalInfo();
                                $info->clearWhereCondition();
                                $info->insertWhereCondition('user_id=' . $ident->id());
                                $info->runSelect();
                                $info->nextRow();
                
                                $html_out = build_link($edit_page, 'form', $info->id, null, LANG_PERSONALINFO_EDIT_INFO, false);
                        }
                        else{
                        
                                $html_out = build_link($edit_page, 'form', -1, null, LANG_PERSONALINFO_CREATE_INFO, false);
                        }
                        
                        return $html_out;
                }
                else if($mode == 'list'){
                
                        $html_out = '';

                        $info_page = $this->getConfig('info_page');
                        
                        include_module_once('grouplink');
                        $link = new Bloxx_GroupLink();
                        $link->clearWhereCondition();
                        $link->insertWhereCondition('group_id='.$id);
                        
                        $link->runSelect();
                        
                        while($link->nextRow()){
                        
                                $info = new Bloxx_PersonalInfo();
                                $info->clearWhereCondition();
                                $info->insertWhereCondition('user_id=' . $link->identity_id);
                                $info->runSelect();

                                include_module_once('identity');
                                $ident = new Bloxx_Identity();
                                $ident->getRowByID($link->identity_id);
                        
                                if($info->nextRow()){
                                
                                        $html_out .= $style->renderStyleHeader($style_link);
                                        $html_out .= build_link($info_page, 'form', $info->id, null, $ident->realname, false);
                                        $html_out .= $style->renderStyleFooter($style_link);
                                        $html_out .= '<br>';
                                }
                        }
                        
                        return $html_out;
                }
        }
        
        function infoExists()
        {
                include_module_once('identity');
                $ident = new Bloxx_Identity();
        
                $info = new Bloxx_PersonalInfo();
                $info->clearWhereCondition();
                $info->insertWhereCondition('user_id=' . $ident->id());

                return ($info->runSelect() > 0);
        }
        
        function create()
        {
                include_module_once('bloxx_role');

                include_module_once('identity');
                $ident = new Bloxx_Identity();
                
                global $HTTP_POST_VARS;

                if($HTTP_POST_VARS['user_id'] != $ident->id()){
                
                        if(!$this->verifyTrust(TRUST_ADMINISTRATOR)){

                                return false;
                        }
                }

                parent::create(false);
        }
        
        function update()
        {
                include_module_once('bloxx_role');

                include_module_once('identity');
                $ident = new Bloxx_Identity();

                global $HTTP_POST_VARS;

                if($HTTP_POST_VARS['user_id'] != $ident->id()){

                        if(!$this->verifyTrust(TRUST_ADMINISTRATOR)){

                                return false;
                        }
                }

                parent::update(false);
        }
}
?>
