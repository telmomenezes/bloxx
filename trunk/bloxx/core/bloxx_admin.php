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
// $Id: bloxx_admin.php,v 1.11 2005-06-22 20:05:34 tmenezes Exp $

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

        function getLocalRenderTrusts()
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

        function getLocalCommandTrusts()
        {
                return array(
                        'save_db' => TRUST_ADMINISTRATOR,
                        'saveall' => TRUST_ADMINISTRATOR,
                        'edit' => TRUST_ADMINISTRATOR,
                        'dummy' => TRUST_ADMINISTRATOR,
                        'uninstall_mod' => TRUST_ADMINISTRATOR,
                        'install_mod' => TRUST_ADMINISTRATOR,
                        'module_list' => TRUST_ADMINISTRATOR
                );
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

	function render($view, 
					$param, 
					$target = -1, 
					$jump = -1, 
					$other_params = null, 
					$template = null)
	{
		if (!$this->verifyTrust(TRUST_ADMINISTRATOR, $param))
		{

        	return $this->renderAdminLogin();
    	}
    	
    	return Bloxx_Module::render($view, $param, $target, $jump, $other_params, $template);
	}

//  Render methods .............................................................

	function doRenderModule_List($param, $target, $jump, $other_params, $mt)
	{
		                
		include_module_once('modulemanager');
        $mm = new Bloxx_ModuleManager();

        include_module_once('identity');
        $user = new Bloxx_Identity();

        $mm->clearWhereCondition();
        $mm->runSelect();

        $page_id = $this->getCurrentPageID();

        $mt->startLoop('core_list');

        while ($mm->nextRow())
        {
                        
        	$mt->nextLoopIteration();

            if($mm->isCoreModule($mm->module_name))
            {

            	$mod_name = '<a href="index.php?id=' . $page_id . '&mode=module&param=' . $mm->module_name . '">';
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

        while ($mm->nextRow())
        {
        	
			$mt->nextLoopIteration();

            if (!$mm->isCoreModule($mm->module_name))
            {

            	$mod_name = '<a href="index.php?id=' . $page_id . '&mode=module&param=' . $mm->module_name . '">';
                $mod_name .= $mm->module_name;
                $mod_name .= '</a>';
                $mt->setLoopItem('name', $mod_name);

                $mod_version = 'ver: ' . $mm->version;
                $mt->setLoopItem('version', $mod_version);
			}
		}
                        
        return $mt->renderView();
	}
	
	function doRenderModule($param, $target, $jump, $other_params, $mt)
	{

    	include_module_once($param);
        $modname = 'Bloxx_' . $param;

        $item = new $modname();

        include_once(CORE_DIR . 'bloxx_form.php');

        $form = new Bloxx_Form();
        $form->setView('new_row');
        $form->setParam($item->name);
        $new_button = $form->renderHeader('admin', 'edit');
        $new_button .= $form->renderSubmitButton(LANG_ADMIN_NEW);
        $new_button .= $form->renderFooter();
                        
        $mt->setItem('new_button', $new_button);
                        
        $item->clearWhereCondition();
        $item->runSelect();
                        
        $mt->startLoop('list');

        while($item->nextRow())
        {
                        
        	$mt->nextLoopIteration();

            $label = $item->label_field;
                                
            if (isset($item->$label))
            {
                                
            	$label = $item->$label;
            }
            $label = $item->renderLabel();
                                
            $form = new Bloxx_Form();
            $form->setView('edit_row');
            $form->setParam($item->name);
            $edit_item = $form->renderHeader('admin', 'edit');
            $edit_item .= $form->renderInput('item', 'hidden', $item->id);
            $edit_item .= $form->renderSubmitLink($label);
            $edit_item .= $form->renderFooter();
                                
            $mt->setLoopItem('edit_item', $edit_item);
                                
            $form = new Bloxx_Form();
            $form->setView('delete_row');
            $form->setParam($item->name);
            $delete_item = $form->renderHeader('admin', 'edit');
            $delete_item .= $form->renderInput('item', 'hidden', $item->id);
            $delete_item .= $form->renderSubmitLink('X');
            $delete_item .= $form->renderFooter();
                                
            $mt->setLoopItem('delete_item', $delete_item);
		}
                        
        return $mt->renderView();
    }
    
	function doRenderEdit($param, $target, $jump, $other_params, $mt)
	{
                
    	include_module_once($param);
        $modname = 'Bloxx_' . $param;
        $mod_inst = new $modname();
                                                
        $html_out = $mod_inst->renderForm($_POST['item']);
                        
        return $html_out;
	}
	
	function doRenderSaveAll($param, $target, $jump, $other_params, $mt)
	{

    	include_once(CORE_DIR . 'bloxx_form.php');

        $form = new Bloxx_Form();
        $form->setView('saveall');
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
    
    function doRenderMenu($param, $target, $jump, $other_params, $mt)
    {

    	include_once(CORE_DIR . 'bloxx_form.php');
                        
        $mt->startLoop('options');

        $form = new Bloxx_Form();
        $form->setView('saveall');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'saveall');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_SAVE_ALL);
        $html_out .= $form->renderFooter();
       	$mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        $form = new Bloxx_Form();
        $form->setView('module_list');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'module_list');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_HOME);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        $form = new Bloxx_Form();
        $form->setView('');
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
        $form->setView('change_password');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'dummy');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_CHANGE_PASSWORD);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

		$form = new Bloxx_Form();
        $form->setView('install_mod');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'dummy');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_INSTALL_MOD);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        $form = new Bloxx_Form();
        $form->setView('uninstall_mod');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'dummy');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UNINSTALL_MOD);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        $form = new Bloxx_Form();
        $form->setView('update_mod');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'dummy');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_UPDATE_MOD);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        $form = new Bloxx_Form();
        $form->setView('about');
        $form->setParam($this->name);
        $html_out = $form->renderHeader('admin', 'dummy');
        $html_out .= $form->renderSubmitButton(LANG_ADMIN_ABOUT);
        $html_out .= $form->renderFooter();
        $mt->setLoopItem('button', $html_out);
        $mt->nextLoopIteration();

        return $mt->renderView();
	}
	
	function doRenderNew_Row($param, $target, $jump, $other_params, $mt)
	{
                                                                                        
		include_module_once($param);
        $modname = 'Bloxx_' . $param;

        $item = new $modname();
        $html_out = $item->renderForm(-1, true, $mt);
                        
        return $html_out;
	}
	
	function doRenderEdit_Row($param, $target, $jump, $other_params, $mt)
	{
                                        
		include_module_once($param);
		$modname = 'Bloxx_' . $param;

		$item = new $modname();
		$html_out = $item->renderForm($_POST['item'], true, $mt);
                        
		return $html_out;
	}
	
	function doRenderDelete_Row($param, $target, $jump, $other_params, $mt)
	{                
						
		include_module_once($param);
        $modname = 'Bloxx_' . $param;
                                
        $modinst = new $modname();
        $modinst->getRowByID($_POST['item'], false);
        $label_field = $modinst->label_field;

        $html_out = LANG_ADMIN_WARNING1;
        
        if (isset($modinst->$label_field))
        {
                                
			$html_out .= $modinst->$label_field;
		}
		
		$html_out .= LANG_ADMIN_WARNING2;
        $html_out .= $param;
		$html_out .= '".';
		$html_out .= '<br /><br />';
		$html_out .= LANG_ADMIN_WARNING3;
                        
		$mt->setItem('warning', $html_out);
                                
		include_once(CORE_DIR . 'bloxx_form.php');

		$form = new Bloxx_Form();
		$form->setView('module');
		$form->setParam($param);

		$html_out = $form->renderHeader($param, 'generic_delete');
		$html_out .= $form->renderInput('item', 'hidden', $_POST['item']);
		$html_out .= $form->renderInput('target_module', 'hidden', $param);
		$html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
		$html_out .= $form->renderFooter();
		$mt->setItem('button', $html_out);
                        
		return $mt->renderView();
	}
	
    function doRenderChange_Password($param, $target, $jump, $other_params, $mt)
    {

		include_module_once('identity');
		$ident = new Bloxx_Identity();
		$html_out = $ident->render('change_password', -1);
                        
		return $html_out;
	}
	
    function doRenderInstall_Mod($param, $target, $jump, $other_params, $mt)
    {
    	
		$mt->setItem('label', LANG_ADMIN_INSTALL_MOD);

		include_once(CORE_DIR.'bloxx_form.php');

		$form = new Bloxx_Form();
		$form->setView('module_list');

		$html_out = $form->renderHeader('admin', 'install_mod');
		$mt->setItem('header', $html_out);
                        
		$html_out = $form->startSelect('module_to_install', 1);

		include_module_once('modulemanager');

		$dh = opendir(MODS_DIR);

		while (($file = readdir($dh)) !== false)
		{

			if (($file != '.') && ($file != '..') && !is_dir($file))
			{

				include_once(MODS_DIR . $file);

				$mod_name = substr($file, 0, -4);
				$mod_name = substr($mod_name, 6);

				$mm = new Bloxx_ModuleManager();
				$mid = $mm->getModuleID($mod_name);

				if($mid <= 0)
				{
                                        
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
	
	function doRenderUninstall_Mod($param, $target, $jump, $other_params, $mt)
	{

		$mt->setItem('label', LANG_ADMIN_UNINSTALL_MOD);
                        
		include_once(CORE_DIR . 'bloxx_form.php');

		$form = new Bloxx_Form();
		$form->setView('uninstall_mod_confirm');

		$html_out = $form->renderHeader('admin', 'dummy');
		$mt->setItem('header', $html_out);
                        
		$html_out = $form->startSelect('module_to_uninstall', 1);
                        
		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
		$mm->clearWhereCondition();
		$mm->runSelect();

		while ($mm->nextRow())
		{

			if (!$mm->isCoreModule())
			{
                                
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
	
	function doRenderUninstall_Mod_Confirm($param, $target, $jump, $other_params, $mt)
	{
		
		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
		$mm->getRowByID($_POST['module_to_uninstall']);

		$html_out = LANG_ADMIN_UNISTALL_MOD_WARNING1;
		$html_out .= $mm->module_name;
		$html_out .= LANG_ADMIN_UNISTALL_MOD_WARNING2;
		$html_out .= '<br /><br />';
		$html_out .= LANG_ADMIN_WARNING3;
		$html_out .= '<br /><br />';
		$mt->setItem('warning', $html_out);
                        
		$form = new Bloxx_Form();
		$form->setView('module_list');

		$html_out = $form->renderHeader('admin', 'uninstall_mod');
		$html_out .= $form->renderInput('module_to_uninstall', 'hidden', $_POST['module_to_uninstall']);
		$html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
		$html_out .= $form->renderFooter();
		$mt->setItem('button', $html_out);
                        
		return $mt->renderView();
	}
	
	function doRenderAbout($param, $target, $jump, $other_params, $mt)
	{
		
		$html_out = 'Bloxx core version ' . BLOXX_CORE_VERSION . '<br><br>';
		$html_out .= 'Copyright &copy; 2002 - 2005 The Bloxx Team. All rights reserved.<br>';
		$html_out .= '<br />';
		$html_out .= 'Bloxx is free software; you can redistribute it and/or modify<br>';
		$html_out .= 'it under the terms of the GNU General Public License as published by<br>';
		$html_out .= 'the Free Software Foundation; either version 2 of the License, or<br>';
		$html_out .= '(at your option) any later version.<br>';
		$html_out .= '<br />';
		$html_out .= 'Bloxx is distributed in the hope that it will be useful,<br>';
		$html_out .= 'but WITHOUT ANY WARRANTY; without even the implied warranty of<br>';
		$html_out .= 'MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br>';
		$html_out .= 'GNU General Public License for more details.<br>';
		$html_out .= '<br />';
		$html_out .= 'You should have received a copy of the GNU General Public License<br>';
		$html_out .= 'along with Bloxx; if not, write to the Free Software<br>';
		$html_out .= 'Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br>';

		$mt->setItem('about', $html_out);
		
		return $mt->renderView();
	}
	
	function doRenderNavigator($param, $target, $jump, $other_params, $mt)
	{

		$html_out = '';

		if (isset($_GET['mode']))
		{
                        
			if ($_GET['mode'] == 'module')
			{
                                
				$html_out = $this->renderNavigator($_GET['param']);
			}
			else if ($_GET['mode'] == 'edit_row')
			{

				if (isset($_POST['item']))
				{
                                        
					$html_out = $this->renderNavigator($_GET['param'], $_POST['item']);                                        
				}
				else
				{

					$html_out = $this->renderNavigator($_GET['param']);
				}
			}
			else
			{
                                
				$html_out = $this->renderNavigator();
			}
		}
		else
		{
                        
			$html_out = $this->renderNavigator();
		}

		$mt->setItem('navigator', $html_out);
		return $mt->renderView();
	}

	
//  Command methods ............................................................

	function execCommandEdit()
	{

		//just used to pass the values from the form
	}

	function execCommandSaveAll()
	{

		//just used to pass the values from the form
	}
	
	function execCommandModule_List()
	{

		//just used to pass the values from the form
	}
	
	function execCommandDummy()
	{

		//just used to pass the values from the form
	}
	
	function execCommandSave_DB()
	{

		$file_name = $_POST['save_dir'] . '.bloxx';
		$handle = fopen($file_name, "w");
		fclose($handle);
                
		include_module_once('modulemanager');

		$mm = new Bloxx_ModuleManager();
		$mm->clearWhereCondition();
		$mm->runSelect();

		while($mm->nextRow())
		{

			$name = $mm->module_name;
			echo $name;
			include_module_once($name);
			$name = 'Bloxx_' . $name;
			$mod_inst = new $name();
			$mod_inst->saveDataToFile($_POST['save_dir']);
		}
	}
	
	function execCommandUninstall_Mod()
	{

		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
		$mm->getRowByID($_POST['module_to_uninstall']);
                        
		$mname = $mm->module_name;
		include_module_once($mname);
		$mname = 'Bloxx_' . $mname;
		$minst = new $mname();
		$minst->uninstall();
	}
	
	function execCommandInstall_Mod()
	{

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
