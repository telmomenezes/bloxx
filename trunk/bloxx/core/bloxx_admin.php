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
// $Id: bloxx_admin.php,v 1.8 2005-02-22 23:03:22 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Admin extends Bloxx_Module
{
        function Bloxx_Admin()
        {
                $this->name = 'admin';
                $this->module_version = 1;
                //$this->label_field = '';
                $this->default_mode = 'module_list';
                $this->use_init_file = false;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }

        function tableDefinition()
        {
                return null;
        }

        function getRenderTrusts()
        {
                return array(
                        'module_list' => TRUST_GUEST,
                        'module' => TRUST_GUEST,
                        'edit' => TRUST_GUEST,
                        'new_row' => TRUST_GUEST,
                        'edit_row' => TRUST_GUEST,
                        'delete_row' => TRUST_GUEST,
                        'save_db' => TRUST_GUEST,
                        'saveall' => TRUST_GUEST,
                        'menu' => TRUST_GUEST,
                        'install_mod' => TRUST_GUEST,
                        'uninstall_mod' => TRUST_GUEST,
                        'update_mod' => TRUST_GUEST,
                        'uninstall_mod_confirm' => TRUST_GUEST,
                        'change_password' => TRUST_GUEST,
                        'menu_about' => TRUST_GUEST,
                        'about' => TRUST_GUEST,
                        'navigator' => TRUST_GUEST
                );
        }

        function getFormTrusts()
        {
                return array(
                        'change' => TRUST_USER,      //special case, trust is checked in update()
                        'create' => TRUST_GUEST,     //special case, trust is checked in create()
                        'delete' => TRUST_ADMINISTRATOR,
                        'save_db' => TRUST_ADMINISTRATOR,
                        'saveall' => TRUST_ADMINISTRATOR,
                        'edit' => TRUST_ADMINISTRATOR,
                        'dummy' => TRUST_ADMINISTRATOR,
                        'uninstall_mod' => TRUST_ADMINISTRATOR,
                        'install_mod' => TRUST_ADMINISTRATOR,
                        'module_list' => TRUST_ADMINISTRATOR
                );
        }

        function doRender($mode, $id, $target, $mt)
        {

                global $_POST;

                if(!$this->verifyTrust(TRUST_ADMINISTRATOR, $id)){

                        return $this->renderAdminLogin();
                }

                if($mode == 'module_list'){
                        
                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();

                        include_module_once('identity');
                        $user = new Bloxx_Identity();

                        $mm->clearWhereCondition();
                        $mm->runSelect();

                        $page_id = $this->getCurrentPageID();

                        $mt->startLoop('core_list');

                        while($mm->nextRow()) {
                        
                                $mt->nextLoopIteration();

                                if($mm->isCoreModule($mm->module_name)){

                                        $mod_name = '<a href="index.php?id='.$page_id.'&mode=module&param=' . $mm->module_name . '">';
                                        $mod_name .= $mm->module_name;
                                        $mod_name .= '</a>';
                                        $mt->setLoopItem('name', $mod_name);
                                        
                                        $mod_version = 'ver: ' . $mm->version;
                                        $mt->setLoopItem('version', $mod_version);
                                }
                        }

                        $mm = new Bloxx_ModuleManager();
                        $mm->clearWhereCondition();
                        $mm->runSelect();
                        
                        $mt->startLoop('module_list');

                        while($mm->nextRow()) {
                        
                                $mt->nextLoopIteration();

                                if(!$mm->isCoreModule($mm->module_name)){

                                        $mod_name = '<a href="index.php?id='.$page_id.'&mode=module&param=' . $mm->module_name . '">';
                                        $mod_name .= $mm->module_name;
                                        $mod_name .= '</a>';
                                        $mt->setLoopItem('name', $mod_name);

                                        $mod_version = 'ver: ' . $mm->version;
                                        $mt->setLoopItem('version', $mod_version);
                                }
                        }
                        
                        return $mt->renderView();
                }
                else if($mode == 'module'){

                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;

                        $item = new $modname();

                        include_once(CORE_DIR . 'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('new_row');
                        $form->setParam($item->name);
                        $new_button = $form->renderHeader('admin', 'edit');
                        $new_button .= $form->renderSubmitButton(LANG_ADMIN_NEW);
                        $new_button .= $form->renderFooter();
                        
                        $mt->setItem('new_button', $new_button);
                        
                        $item->clearWhereCondition();
                        $item->runSelect();
                        
                        $mt->startLoop('list');

                        while($item->nextRow()) {
                        
                                $mt->nextLoopIteration();

                                $label = $item->label_field;
                                
                                if(isset($item->$label)){
                                
                                        $label = $item->$label;
                                }
                                $label = $item->renderLabel();
                                
                                $form = new Bloxx_Form();
                                $form->setMode('edit_row');
                                $form->setParam($item->name);
                                $edit_item = $form->renderHeader('admin', 'edit');
                                $edit_item .= $form->renderInput('item', 'hidden', $item->id);
                                $edit_item .= $form->renderSubmitLink($label);
                                $edit_item .= $form->renderFooter();
                                
                                $mt->setLoopItem('edit_item', $edit_item);
                                
                                $form = new Bloxx_Form();
                                $form->setMode('delete_row');
                                $form->setParam($item->name);
                                $delete_item = $form->renderHeader('admin', 'edit');
                                $delete_item .= $form->renderInput('item', 'hidden', $item->id);
                                $delete_item .= $form->renderSubmitLink('X');
                                $delete_item .= $form->renderFooter();
                                
                                $mt->setLoopItem('delete_item', $delete_item);
                        }
                        
                        return $mt->renderView();
                }
                else if($mode == 'edit'){
                
                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;
                        $mod_inst = new $modname();
                                                
                        $html_out = $mod_inst->renderForm($_POST['item']);
                        
                        return $html_out;
                }
                else if($mode == 'saveall'){

                        include_once(CORE_DIR . 'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('save_db');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'save_db');
                        $mt->setItem('header', $html_out);

                        $mt->setItem('label', LANG_ADMIN_DIRECTORY);

                        $html_out = $form->renderInput('save_dir', '', '');
                        $mt->setItem('field', $html_out);
                        
                        $html_out = $form->renderSubmitButton(LANG_ADMIN_SAVE_ALL);
                        $mt->setItem('button', $html_out);

                        $html_out = $form->renderFooter();
                        $mt->setItem('footer', $html_out);
                        
                        return $mt->renderView();
                }
                else if($mode == 'menu'){

                        include_once(CORE_DIR . 'bloxx_form.php');
                        
                        $mt->startLoop('options');

                        $form = new Bloxx_Form();
                        $form->setMode('saveall');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'saveall');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_SAVE_ALL);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('module_list');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'module_list');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_HOME);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('');
                        $form->setParam('');
                        include_module_once('config');
                        $config = new Bloxx_Config();
                        $id = $config->getMainPage();
                        $html_out = $form->renderHeader('', 0, $id);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_SITE);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('change_password');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CHANGE_PASSWORD);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('install_mod');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_INSTALL_MOD);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('uninstall_mod');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UNINSTALL_MOD);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('update_mod');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UPDATE_MOD);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        $form = new Bloxx_Form();
                        $form->setMode('about');
                        $form->setParam($this->name);
                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_ABOUT);
                        $html_out .= $form->renderFooter();
                        $mt->setLoopItem('button', $html_out);
                        $mt->nextLoopIteration();

                        return $mt->renderView();
                }
                else if($mode == 'new_row'){
                                                                                        
                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;

                        $item = new $modname();
                        $html_out = $item->renderForm(-1, true, $mt);
                        
                        return $html_out;
                }
                else if($mode == 'edit_row'){
                                        
                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;

                        $item = new $modname();
                        $html_out = $item->renderForm($_POST['item'], true, $mt);
                        
                        return $html_out;
                }
                else if($mode == 'delete_row'){
                

                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;
                                
                        $modinst = new $modname();
                        $modinst->getRowByID($_POST['item'], false);
                        $label_field = $modinst->label_field;

                        $html_out = LANG_ADMIN_WARNING1;
                        if(isset($modinst->$label_field)){
                                
                                $html_out .= $modinst->$label_field;
                        }
                        $html_out .= LANG_ADMIN_WARNING2;
                        $html_out .= $id;
                        $html_out .= '".';
                        $html_out .= '<br><br>';
                        $html_out .= LANG_ADMIN_WARNING3;
                        
                        $mt->setItem('warning', $html_out);
                                
                        include_once(CORE_DIR . 'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('module');
                        $form->setParam($id);

                        $html_out = $form->renderHeader('admin', 'delete');
                        $html_out .= $form->renderInput('item', 'hidden', $_POST['item']);
                        $html_out .= $form->renderInput('target_module', 'hidden', $id);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
                        $html_out .= $form->renderFooter();
                        $mt->setItem('button', $html_out);
                        
                        return $mt->renderView();

                }
                else if($mode == 'change_password'){

                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                        $html_out = $ident->render('change_password', -1);
                        
                        return $html_out;
                }
                else if($mode == 'install_mod'){


                        $mt->setItem('label', LANG_ADMIN_INSTALL_MOD);

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('module_list');

                        $html_out = $form->renderHeader('admin', 'install_mod');
                        $mt->setItem('header', $html_out);
                        
                        $html_out = $form->startSelect('module_to_install', 1);

                        include_module_once('modulemanager');

                        $dh = opendir(MODS_DIR);

                        while (($file = readdir($dh)) !== false) {

                                if(($file != '.') && ($file != '..') && !is_dir($file)){

                                        include_once(MODS_DIR . $file);

                                        $mod_name = substr($file, 0, -4);
                                        $mod_name = substr($mod_name, 6);

                                        $mm = new Bloxx_ModuleManager();
                                        $mid = $mm->getModuleID($mod_name);

                                        if($mid <= 0){
                                        
                                                $html_out .= $form->addSelectItem($mod_name, $mod_name, false);
                                        }
                                }
                        }

                        closedir($dh);

                        $html_out .= $form->endSelect();
                        $mt->setItem('selector', $html_out);
                        
                        $html_out = $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
                        $mt->setItem('button', $html_out);
                        
                        $html_out = $form->renderFooter();
                        $mt->setItem('footer', $html_out);

                        return $mt->renderView();
                }
                else if($mode == 'uninstall_mod'){

                        $mt->setItem('label', LANG_ADMIN_UNINSTALL_MOD);
                        
                        include_once(CORE_DIR . 'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('uninstall_mod_confirm');

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $mt->setItem('header', $html_out);
                        
                        $html_out = $form->startSelect('module_to_uninstall', 1);
                        
                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->clearWhereCondition();
                        $mm->runSelect();

                        while($mm->nextRow()){

                                if(!$mm->isCoreModule()){
                                
                                        $html_out .= $form->addSelectItem($mm->id, $mm->module_name, false);
                                }
                        }

                        $html_out .= $form->endSelect();
                        $mt->setItem('selector', $html_out);
                        
                        $html_out = $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
                        $mt->setItem('button', $html_out);
                        
                        $html_out = $form->renderFooter();
                        $mt->setItem('footer', $html_out);

                        return $mt->renderView();
                }
                else if($mode == 'uninstall_mod_confirm'){

                        global $_POST;

                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($_POST['module_to_uninstall']);

                        $html_out = LANG_ADMIN_UNISTALL_MOD_WARNING1;
                        $html_out .= $mm->module_name;
                        $html_out .= LANG_ADMIN_UNISTALL_MOD_WARNING2;
                        $html_out .= '<br><br>';
                        $html_out .= LANG_ADMIN_WARNING3;
                        $html_out .= '<br><br>';
                        $mt->setItem('warning', $html_out);
                        
                        $form = new Bloxx_Form();
                        $form->setMode('module_list');

                        $html_out = $form->renderHeader('admin', 'uninstall_mod');
                        $html_out .= $form->renderInput('module_to_uninstall', 'hidden', $_POST['module_to_uninstall']);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
                        $html_out .= $form->renderFooter();
                        $mt->setItem('button', $html_out);
                        
                        return $mt->renderView();
                }
                else if($mode == 'about'){

                        $html_out = 'Bloxx core version ' . BLOXX_CORE_VERSION . '<br><br>';
                        $html_out .= 'Copyright &copy; 2002 - 2005 The Bloxx Team. All rights reserved.<br>';
                        $html_out .= '<br>';
                        $html_out .= 'Bloxx is free software; you can redistribute it and/or modify<br>';
                        $html_out .= 'it under the terms of the GNU General Public License as published by<br>';
                        $html_out .= 'the Free Software Foundation; either version 2 of the License, or<br>';
                        $html_out .= '(at your option) any later version.<br>';
                        $html_out .= '<br>';
                        $html_out .= 'Bloxx is distributed in the hope that it will be useful,<br>';
                        $html_out .= 'but WITHOUT ANY WARRANTY; without even the implied warranty of<br>';
                        $html_out .= 'MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br>';
                        $html_out .= 'GNU General Public License for more details.<br>';
                        $html_out .= '<br>';
                        $html_out .= 'You should have received a copy of the GNU General Public License<br>';
                        $html_out .= 'along with Bloxx; if not, write to the Free Software<br>';
                        $html_out .= 'Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br>';

                        $mt->setItem('about', $html_out);
                        return $mt->renderView();
                }
                else if($mode == 'navigator'){

                        $html_out = '';
                        global $_GET;

                        if(isset($_GET['mode'])){
                        
                                if($_GET['mode'] == 'module'){
                                
                                        $html_out = $this->renderNavigator($_GET['param']);
                                }
                                else if($_GET['mode'] == 'edit_row'){

                                        if(isset($_POST['item'])){
                                        
                                                $html_out = $this->renderNavigator($_GET['param'], $_POST['item']);                                        
                                        }
                                        else{
                                        

                                                $html_out = $this->renderNavigator($_GET['param']);
                                        }
                                }
                                else{
                                
                                        $html_out = $this->renderNavigator();
                                }
                        }
                        else{
                        
                                $html_out = $this->renderNavigator();
                        }

                        $mt->setItem('navigator', $html_out);
                        return $mt->renderView();
                }
        }

        function doProcessForm($command)
        {
                global $_POST;

                if($command == 'edit'){

                        //just used to pass the values from the form
                }
                else if($command == 'saveall'){

                        //just used to pass the values from the form
                }
                else if($command == 'module_list'){

                        //just used to pass the values from the form
                }
                else if($command == 'dummy'){

                        //just used to pass the values from the form
                }
                else if($command == 'change'){

                        include_module_once($_POST['target_module']);

                        $modname = 'Bloxx_' . $_POST['target_module'];
                        $item = new $modname();
                        
                        $item->update();
                }
                else if($command == 'create'){

                        include_module_once($_POST['target_module']);

                        $modname = 'Bloxx_' . $_POST['target_module'];
                        $item = new $modname();

                        $item->create();
                }
                else if($command == 'save_db'){

                        $file_name = $_POST['save_dir'] . '.bloxx';
                        $handle = fopen($file_name, "w");
                        fclose($handle);
                
                        include_module_once('modulemanager');

                        $mm = new Bloxx_ModuleManager();
                        $mm->clearWhereCondition();
                        $mm->runSelect();

                        while($mm->nextRow()) {

                                $name = $mm->module_name;
                                echo $name;
                                include_module_once($name);
                                $name = 'Bloxx_' . $name;
                                $mod_inst = new $name();
                                $mod_inst->saveDataToFile($_POST['save_dir']);
                        }
                }
                else if($command == 'delete'){

                        include_module_once($_POST['target_module']);

                        $modname = 'Bloxx_' . $_POST['target_module'];
                        $item = new $modname();
                        
                        $item->deleteRowByID($_POST['item']);
                }
                else if($command == 'uninstall_mod'){
                
                        global $_POST;

                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($_POST['module_to_uninstall']);
                        
                        $mname = $mm->module_name;
                        include_module_once($mname);
                        $mname = 'Bloxx_' . $mname;
                        $minst = new $mname();
                        $minst->uninstall();
                }
                else if($command == 'install_mod'){

                        global $_POST;

                        $mname = $_POST['module_to_install'];
                        $mname = substr($mname, 1);
                        $mname = substr($mname, 0, -1);
                        include_module_once($mname);
                        $mname = 'Bloxx_' . $mname;
                        $minst = new $mname();
                        $minst->install();
                        $minst->afterInstall();
                }
        }
        
        function renderAdminLogin()
        {
                $html_out = '';
                
                return $html_out;
        }
        
        function renderNavigator($module = null, $item = null)
        {
                include_module_once('config');
                $config = new Bloxx_Config();
                $admin_page = $config->getConfig('admin_page');
        
                $html_out = '<a href="index.php?id=' . $admin_page . '">backend</a>';
        
                if($module != null){
                
                        $html_out .= ' >> <a href="index.php?id=' . $admin_page . '&mode=module&param=' . $module . '">' . $module . '</a>';
                        
                        if($item != null){

                                include_module_once($module);
                                $modname = 'Bloxx_' . $module;
                                $inst = new $modname();
                                
                                $inst->getRowByID($item);
                                $labelname = $inst->label_field;

                                if(isset($inst->$labelname)){
                                
                                        $html_out .= ' >> ' . $inst->$labelname;
                                }
                        }
                }
                
                return $html_out;
        }
}
