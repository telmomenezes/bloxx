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
                        'new_edit_delete' => TRUST_GUEST,
                        'save_db' => TRUST_GUEST,
                        'saveall' => TRUST_GUEST,
                        'menu_saveall' => TRUST_GUEST,
                        'menu_home' => TRUST_GUEST,
                        'menu_site' => TRUST_GUEST,
                        'menu_change_password' => TRUST_GUEST,
                        'menu_install_mod' => TRUST_GUEST,
                        'menu_uninstall_mod' => TRUST_GUEST,
                        'menu_update_mod' => TRUST_GUEST,
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
                        'edit' => TRUST_ADMINISTRATOR,
                        'delete' => TRUST_ADMINISTRATOR,
                        'save_db' => TRUST_ADMINISTRATOR,
                        'saveall' => TRUST_ADMINISTRATOR,
                        'dummy' => TRUST_ADMINISTRATOR,
                        'uninstall_mod' => TRUST_ADMINISTRATOR,
                        'install_mod' => TRUST_ADMINISTRATOR,
                        'module_list' => TRUST_ADMINISTRATOR
                );
        }
        
        function getStyleList()
        {
                return array(
                        'Label' => 'AdminFormLabel',
                        'Field' => 'AdminFormField',
                        'Button' => 'AdminFormButton',
                        'Link' => 'AdminLink',
                        'Navigator' => 'AdminNavigator'
                );
        }

        function doRender($mode, $id, $target)
        {
                global $_POST;

                if(!$this->verifyTrust(TRUST_ADMINISTRATOR, $id)){

                        return $this->renderAdminLogin();
                }

                if($mode == 'module_list'){

                        include_module_once('moduletemplate');
                        $mt = new Bloxx_ModuleTemplate();
                        $mt->getTemplate($this, $mode);
                        
                        include_module_once('modulemanager');
                        $module_manager = new Bloxx_ModuleManager();
                        $mt->setItem('main', $module_manager->render('module_list', -1));
                        $html_out = $mt->renderView();
                        
                        return $html_out;
                }
                else if($mode == 'module'){

                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;

                        $item = new $modname();

                        include_once(CORE_DIR.'bloxx_admin.php');
                        include_once(CORE_DIR.'bloxx_style.php');
                        include_once(CORE_DIR.'bloxx_form.php');

                        $style = new Bloxx_Style();
                        $admin = new Bloxx_Admin();
                        $style_admin_form_label = $admin->getGlobalStyle('Label');
                        $style_admin_form_field = $admin->getGlobalStyle('Field');
                        $style_admin_form_button = $admin->getGlobalStyle('Button');

                        $form = new Bloxx_Form();
                        $form->setMode('new_edit_delete');
                        $form->setParam($item->name);
                        $html_out = $form->renderHeader('admin', 'edit');
                        $html_out .= $form->startSelect('item', 20, $style_admin_form_field);
                        
                        $item->clearWhereCondition();
                        $item->runSelect();

                        while($item->nextRow()) {

                                $label = $item->label_field;
                                
                                if(isset($item->$label)){
                                
                                        $label = $item->$label;
                                }
                                $label = $item->renderLabel();
                                $html_out .= $form->addSelectItem($item->id, $label);
                        }

                        $html_out .= $form->endSelect();
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_NEW, $style_admin_form_button);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_EDIT, $style_admin_form_button);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_DELETE, $style_admin_form_button);
                        $html_out .= $form->renderFooter();
                        
                        return $html_out;
                }
                else if($mode == 'edit'){
                
                        include_module_once($id);
                        $modname = 'Bloxx_' . $id;

                        $item = new $modname();
                        $html_out = $item->renderForm($_POST['item']);
                        
                        return $html_out;
                }
                else if($mode == 'saveall'){
                
                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_label = $this->getGlobalStyle('Label');
                        $style_admin_form_field = $this->getGlobalStyle('Field');
                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('save_db');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'save_db');

                        $html_out .= $style->renderStyleHeader($style_admin_form_label);
                        $html_out .=  LANG_ADMIN_DIRECTORY;
                        $html_out .= $style->renderStyleFooter($style_admin_form_label);
                        $html_out .= $form->renderInput('save_dir', '', '', $style_admin_form_field);

                        $html_out .= $form->renderSubmitButton('Gravar', $style_admin_form_button);

                        $html_out .= $form->renderFooter();
                        
                        return $html_out;
                }
                else if($mode == 'menu_saveall'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('saveall');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'saveall');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_SAVE_ALL, $style_admin_form_button);
                        $html_out .= $form->renderFooter();
                        
                        return $html_out;
                }
                else if($mode == 'menu_home'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('module_list');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'module_list');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_HOME, $style_admin_form_button);
                        $html_out .= $form->renderFooter();
                        
                        return $html_out;
                }
                else if($mode == 'menu_site'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('');
                        $form->setParam('');

                        include_module_once('config');
                        $config = new Bloxx_Config();
                        $id = $config->getMainPage();
        
                        $html_out = $form->renderHeader('', 0, $id);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_SITE, $style_admin_form_button);
                        $html_out .= $form->renderFooter();
                        
                        return $html_out;
                }
                else if($mode == 'menu_change_password'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('change_password');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CHANGE_PASSWORD, $style_admin_form_button);
                        $html_out .= $form->renderFooter();

                        return $html_out;
                }
                else if($mode == 'menu_install_mod'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('install_mod');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_INSTALL_MOD, $style_admin_form_button);
                        $html_out .= $form->renderFooter();

                        return $html_out;
                }
                else if($mode == 'menu_uninstall_mod'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('uninstall_mod');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UNINSTALL_MOD, $style_admin_form_button);
                        $html_out .= $form->renderFooter();

                        return $html_out;
                }
                else if($mode == 'menu_update_mod'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('update_mod');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UPDATE_MOD, $style_admin_form_button);
                        $html_out .= $form->renderFooter();

                        return $html_out;
                }
                else if($mode == 'menu_about'){

                        include_once(CORE_DIR.'bloxx_style.php');

                        $style = new Bloxx_Style();

                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('about');
                        $form->setParam($this->name);

                        $html_out = $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_ABOUT, $style_admin_form_button);
                        $html_out .= $form->renderFooter();

                        return $html_out;
                }
                else if($mode == 'new_edit_delete'){

                        if(!isset($_POST['item'])){
                        
                                $_POST['item'] = null;
                        }

                        $html_out = '';

                        if($_POST['submit'] == LANG_ADMIN_NEW){
                        
                                include_module_once($id);
                                $modname = 'Bloxx_' . $id;

                                $item = new $modname();
                                $html_out .= $item->renderForm(-1);
                        }
                        else if($_POST['submit'] == LANG_ADMIN_EDIT){
                        
                                include_module_once($id);
                                $modname = 'Bloxx_' . $id;

                                $item = new $modname();
                                $html_out .= $item->renderForm($_POST['item']);
                        }
                        else if($_POST['submit'] == LANG_ADMIN_DELETE){
                        
                                include_once(CORE_DIR.'bloxx_style.php');

                                $style = new Bloxx_Style();

                                $style_admin_form_label = $this->getGlobalStyle('Label');
                                $style_admin_form_field = $this->getGlobalStyle('Field');
                                $style_admin_form_button = $this->getGlobalStyle('Button');

                                include_module_once($id);
                                $modname = 'Bloxx_' . $id;
                                
                                $modinst = new $modname();
                                $modinst->getRowByID($_POST['item'], false);
                                $label_field = $modinst->label_field;

                                $html_out .= $style->renderStyleHeader($style_admin_form_label);
                                $html_out .= LANG_ADMIN_WARNING1;
                                
                                if(isset($modinst->$label_field)){
                                
                                        $html_out .= $modinst->$label_field;
                                }
                                $html_out .= LANG_ADMIN_WARNING2;
                                $html_out .= $id;
                                $html_out .= '".';
                                $html_out .= '<br><br>';
                                $html_out .= 'Esta acção é irreversível. Tem a certeza que deseja continuar?';
                                $html_out .= $style->renderStyleFooter($style_admin_form_label);
                                $html_out .= '<br><br>';
                                
                                include_once(CORE_DIR.'bloxx_form.php');

                                $form = new Bloxx_Form();
                                $form->setMode('module');
                                $form->setParam($id);

                                $html_out .= $form->renderHeader('admin', 'delete');
                                $html_out .= $form->renderInput('item', 'hidden', $_POST['item'], $style_admin_form_field);
                                $html_out .= $form->renderInput('target_module', 'hidden', $id, $style_admin_form_field);
                                $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM, $style_admin_form_button);
                                $html_out .= $form->renderFooter();
                        }
                        
                        return $html_out;

                }
                else if($mode == 'change_password'){

                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                        $html_out = $ident->render('change_password', -1);
                        
                        return $html_out;
                }
                else if($mode == 'install_mod'){

                        include_once(CORE_DIR.'bloxx_style.php');
                        $style = new Bloxx_Style();
                        $style_admin_form_label = $this->getGlobalStyle('Label');
                        $style_admin_form_field = $this->getGlobalStyle('Field');
                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        $html_out = $style->renderStyleHeader($style_admin_form_label);
                        $html_out .= LANG_ADMIN_INSTALL_MOD;
                        $html_out .= $style->renderStyleFooter($style_admin_form_label);

                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('module_list');

                        $html_out .= $form->renderHeader('admin', 'install_mod');
                        $html_out .= $form->startSelect('module_to_install', 1, $style_admin_form_field);

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

                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM, $style_admin_form_button);

                        return $html_out;
                }
                else if($mode == 'uninstall_mod'){

                        include_once(CORE_DIR.'bloxx_style.php');
                        $style = new Bloxx_Style();
                        $style_admin_form_label = $this->getGlobalStyle('Label');
                        $style_admin_form_field = $this->getGlobalStyle('Field');
                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        $html_out .= $style->renderStyleHeader($style_admin_form_label);
                        $html_out .= LANG_ADMIN_UNINSTALL_MOD;
                        $html_out .= $style->renderStyleFooter($style_admin_form_label);
                        
                        include_once(CORE_DIR.'bloxx_form.php');

                        $form = new Bloxx_Form();
                        $form->setMode('uninstall_mod_confirm');
                        //$form->setParam($id);

                        $html_out .= $form->renderHeader('admin', 'dummy');
                        $html_out .= $form->startSelect('module_to_uninstall', 1, $style_admin_form_field);
                        
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
                        
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM, $style_admin_form_button);

                        return $html_out;
                }
                else if($mode == 'uninstall_mod_confirm'){
                
                        include_once(CORE_DIR.'bloxx_style.php');
                        $style = new Bloxx_Style();
                        $style_admin_form_label = $this->getGlobalStyle('Label');
                        $style_admin_form_field = $this->getGlobalStyle('Field');
                        $style_admin_form_button = $this->getGlobalStyle('Button');

                        global $_POST;

                        include_module_once('modulemanager');
                        $mm = new Bloxx_ModuleManager();
                        $mm->getRowByID($_POST['module_to_uninstall']);

                        $html_out = $style->renderStyleHeader($style_admin_form_label);
                        $html_out .= LANG_ADMIN_UNISTALL_MOD_WARNING1;
                        $html_out .= $mm->module_name;
                        $html_out .= LANG_ADMIN_UNISTALL_MOD_WARNING2;
                        $html_out .= '<br><br>';
                        $html_out .= LANG_ADMIN_WARNING3;
                        $html_out .= '<br><br>';
                        $html_out .= $style->renderStyleFooter($style_admin_form_label);
                        
                        $form = new Bloxx_Form();
                        $form->setMode('module_list');
                        //$form->setParam($id);

                        $html_out .= $form->renderHeader('admin', 'uninstall_mod');
                        $html_out .= $form->renderInput('module_to_uninstall', 'hidden', $_POST['module_to_uninstall'], $style_admin_form_field);
                        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM, $style_admin_form_button);
                        
                        return $html_out;
                }
                else if($mode == 'about'){

                        include_module_once('style');
                        $style = new Bloxx_Style();
                        $style_admin_form_label = $this->getGlobalStyle('Label');

                        $html_out = $style->renderStyleHeader($style_admin_form_label);
                        $html_out .= 'Bloxx core version ' . BLOXX_CORE_VERSION . '<br><br>';
                        $html_out .= 'Copyright 2002 - 2005 Telmo Menezes. All rights reserved.<br>';
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
                        $html_out .= $style->renderStyleFooter($style_admin_form_label);

                        return $html_out;
                }
                else if($mode == 'navigator'){

                        global $_GET;

                        if(isset($_GET['mode'])){
                        
                                if($_GET['mode'] == 'module'){
                                
                                        $html_out = $this->renderNavigator($_GET['param']);
                                        return $html_out;
                                }

                                if($_GET['mode'] == 'new_edit_delete'){

                                        if(isset($_POST['item'])){
                                        
                                                $html_out = $this->renderNavigator($_GET['param'], $_POST['item']);
                                                return $html_out;
                                        }
                                        else{
                                        
                                                $html_out = $this->renderNavigator($_GET['param']);
                                                return $html_out;
                                        }
                                }
                        }
                        
                        $html_out = $this->renderNavigator();

                        return $html_out;
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
                
                include_module_once('style');
                $style = new Bloxx_Style();
                $style_admin_navigator = $this->getGlobalStyle('Navigator');

                $html_out = $style->renderStyleHeader($style_admin_navigator);
        
                $html_out .= '<a href="index.php?id=' . $admin_page . '">backend</a>';
        
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
                
                $html_out .= $style->renderStyleFooter($style_admin_navigator);
                
                return $html_out;
        }
}
