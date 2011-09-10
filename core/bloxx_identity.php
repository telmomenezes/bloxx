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
//          Silas Francisco <draft@dog.kicks-ass.net>
//
// $Id: bloxx_identity.php,v 1.11 2005-09-05 22:55:40 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');
require_once(CORE_DIR . 'bloxx_modulemanager.php');
include_once(CORE_DIR . 'bloxx_role.php');
include_once(CORE_DIR . 'bloxx_session.php');

class Bloxx_Identity extends Bloxx_Module
{
        function Bloxx_Identity()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'identity';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'username';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->session = new Bloxx_Session();                
                $this->is_loged_in = false;                                
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'realname' => array('TYPE' => 'STRING', 'SIZE' => 80, 'NOTNULL' => true, 'USER' => true),
                        'username' => array('TYPE' => 'STRING', 'SIZE' => 10, 'NOTNULL' => true, 'USER' => true),
                        'password' => array('TYPE' => 'PASSWORD', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true, 'HIDDEN' => true),
                        'email' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'USER' => true, 'CONFIDENTIAL' => true),
                        'confirm_hash' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'HIDDEN' => true),
                        'confirmed' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true, 'HIDDEN' => true),
                        'role' => array('TYPE' => 'BLOXX_ROLE', 'SIZE' => -1, 'NOTNULL' => true, 'CONFIDENTIAL' => true)
                );
        }
        
        function getLocalRenderTrusts()
        {
                return array(
                        'loginbox' => TRUST_GUEST,
                        'logout_button' => TRUST_GUEST,
                        'logout_link' => TRUST_GUEST,
                        'welcome' => TRUST_GUEST,
                        'register' => TRUST_GUEST,
                        'change_password' => TRUST_USER,
                        'change_data' => TRUST_USER,
                        'confirm' => TRUST_GUEST,
                        'confirm_email' => TRUST_GUEST
                );
        }
        
        function getLocalCommandTrusts()
        {
                return array(
                        'login' => TRUST_GUEST,
                        'logout' => TRUST_GUEST,
                        'register' => TRUST_GUEST,
                        'change_password' => TRUST_USER
                );
        }        

        function login($login, $password)
        {
                global $warningmessage;
        
                if (!$login) {

                        $warningmessage = LANG_IDENTITY_ERROR_NO_USERNAME;
                        return false;
                }
                else if (!$password) {

                        $warningmessage = LANG_IDENTITY_ERROR_NO_PASSWORD;
                        return false;
                }
                else {
                
                        //$login=strtolower($login);
                        //$password=strtolower($password);
                        
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('username', '=', $login);
                        $this->insertWhereCondition('password', '=', md5($password));
                        $this->runSelect();
                        
                        if (!$this->nextRow()){
                                //Error - User not found or invalid password
                                $warningmessage = LANG_IDENTITY_ERROR_LOGIN_DENIED;
                                return false;
                        } 
                        else {
                                if ($this->confirmed == 1) {
                                
                                        $this->session->createSession($login);                                        
                                        return true;
                                }
                                else {
                                
                                        //Error - registration not confirmed yet
                                        $warningmessage = LANG_IDENTITY_ERROR_UNCONFIRMED_REGISTRATION;
                                        return false;
                                }
                        }
                }
        }
        
        function checkPassword($id, $password)
        {
                $ident = new Bloxx_Identity();
                $ident->getRowByID($id);
                
                if($this->confirmed != 1){

                        return false;
                }
                
                $md5_pass = md5($password);
                
                return ($md5_pass == $ident->password);
        }

        function logout()
        {

                $this->session->removeSession();
        }
        
        function isLoggedIn() {
                
                //have we already run the hash checks? 
                //If so, return the pre-set var
                if (isset($this->is_loged_in) && $this->is_loged_in)
                {
                
                        return true;
                } // WARNING Must check if this is safe
                
                $this->is_loged_in = $this->session->exists();				

                return $this->is_loged_in;
        }
        
        function create()
        {
        		global $warningmessage;
                
                if($_POST['password'] != $_POST['password_again']){

                        $warningmessage = LANG_IDENTITY_ERROR_NEW_PASSWORD_MISMATCH;
                        return false;
                }
                
                $ident = new Bloxx_Identity();
                $ident->clearWhereCondition();
                $ident->insertWhereCondition('username', '=', $_POST['username']);
                $ident->runSelect();
                        
                if ($ident->nextRow())
                {
                	$warningmessage = LANG_IDENTITY_USER_EXISTS;
                    return false;
                }
                
                $ident = new Bloxx_Identity();
                $ident->clearWhereCondition();
                $ident->insertWhereCondition('email', '=', $_POST['email']);
                $ident->runSelect();
                        
                if ($ident->nextRow())
                {
                	$warningmessage = LANG_IDENTITY_EMAIL_EXISTS;
                    return false;
                }


                $this->username = $_POST['username'];
                $this->realname = $_POST['realname'];
                $this->email = strtolower($_POST['email']);
                
                $this->password = md5($_POST['password']);
                $this->confirm_hash = md5($_POST['email'] . $this->hidden_hash_var);
                $this->confirmed = 0;
                
                $this->role = $this->getConfig('base_role');

                $res = $this->insertRow();
                
                if ($res === false)
                {                
					return false;
                }                
                
                $confirm_email = $this->getConfig('confirm_email');
                $ident = new Bloxx_Identity();
                $message = $ident->render('confirm_email', $this->id, $_POST['password']);
                
                include_module_once('config');
                $config = new Bloxx_Config();
                $site_name = $config->getConfig('site_name');
                
                include_once(THIRD_PARTY_DIR . 'phpmailer/class.phpmailer.php');
				$mail = new PHPMailer();
				$mail->From = $confirm_email;
				$mail->FromName = $site_name;
				$mail->Subject = LANG_IDENTITY_CONFIRM_EMAIL_SUBJECT; 
				$mail->IsMail();
				$mail->Body = $message;
    			$mail->AltBody = 'Sorry, your mail client must support HTML mails to use this feature.';
    			$mail->AddAddress($this->email);
    			$mail->Send();

                $warningmessage = LANG_IDENTITY_CONFIRM_MAIL_SENT;
                
                return $res;
        }
        
        function update()
        {

                //Allow only admins or indentity owners
                if((!$this->verifyTrust(TRUST_ADMINISTRATOR))
                && ($this->userID() != $_POST['id'])){

                        return false;
                }

                if(!$this->getRowByID($_POST['id'])){

                        return false;
                }

                if(isset($_POST['password']) && ($_POST['password'] != $this->password)){

                        return false;
                }
                
                $email = $this->email;

                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if(isset($_POST[$k])){

                                $this->$k = $_POST[$k];
                        }
                }
                
                if($email != $this->email){
                
                        $this->confirmed = 0;
                        $this->confirm_hash = md5($this->email.$this->hidden_hash_var);
                        
                        $message = LANG_IDENTITY_CHANGE_EMAIL;
                        $config = new Bloxx_Config();
                        $site_url = $config->getConfig('site_url');
                        $confirm_page = $this->getConfig('confirm_page');
                        $message .= $site_url . '/index.php?id=' . $confirm_page . '&email=' . $this->email . '&code=' . $this->confirm_hash;

                        $confirm_email = $config->getConfig('confirm_email');

                        //echo $message;

                        mail($this->email, LANG_IDENTITY_CHANGE_EMAIL_SUBJECT, $message, 'From: ' . $confirm_email);
                }

                return $this->updateRow(true);
        }
        
        function userID()
        {       
        		$username = $this->session->getLogin();
        		
                if ($username != null)
                {                
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('username', '=', $username);
                        $this->runSelect();
                        
                        if ($this->nextRow())
                        {                        
                                return $this->id;
                        }
                        else
                        {                        
                                return -1;
                        }
                }
                else
                {        
                        return -1;
                }
        }
        
        function groups()
        {
                $id = $this->userID();
        
                include_module_once('grouplink');
                
                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition('identity_id', '=', $id);
                $gl->runSelect();
                
                $glist = null;
                
                while($gl->nextRow()){

                        if(!isset($glist)){
                        
                                $glist = array($gl->group_id);
                        }
                        else{
                        
                                $glist = $glist + array($gl->group_id);
                        }
                }
                
                return $glist;
        }
        
        function belongsToGroups()
        {
                $id = $this->userID();

                include_module_once('grouplink');

                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition('identity_id', '=', $id);
                $gl->runSelect();

                if($gl->nextRow()){
                
                        return true;
                }
                
                return false;
        }
        
        function belongsToGroup($group_id)
        {
                $id = $this->userID();

                include_module_once('grouplink');

                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition('identity_id', '=', $id);
                $gl->runSelect();

                while($gl->nextRow()){

                        if($gl->group_id == $group_id){
                        
                                return true;
                        }
                }

                return false;
        }
        
        function getPersonalInfo()
        {
        	
        	$personal_info_module = $this->getConfig('personal_info_module');
		
			if ($personal_info_module != null)
			{
				
				include_module_once($personal_info_module);
				$personal_info_module = 'Bloxx_' . $personal_info_module; 
				$pi = new $personal_info_module();
				
				$pi->clearWhereCondition();
				$pi->insertWhereCondition('identity_id', '=', $this->id);
				$pi->runSelect();
                        
				if ($pi->nextRow())
				{
					return $pi;                   
				}
				else
				{
					return null;
				}
			}
			else
			{
				return null;
			}
        }

//  Render methods .............................................................

	function doRenderLoginbox($param, $target, $jump, $other_params, $mt)
    {
    	
    	include_once(CORE_DIR . 'bloxx_form.php');
                	                        
        $form = new Bloxx_Form();
        $html_out = $form->renderHeader($this->_BLOXX_MOD_PARAM['name'], 'login');
        $mt->setItem('header', $html_out);

        $html_out =  LANG_IDENTITY_USERNAME;
		$mt->setItem('username_label', $html_out);

		$html_out = $form->renderInput('username', '', '');
		$mt->setItem('username', $html_out);

		$html_out =  LANG_IDENTITY_PASSWORD;
		$mt->setItem('password_label', $html_out);

		$html_out = $form->renderInput('password', 'password', '');
		$mt->setItem('password', $html_out);

		$html_out = $form->renderSubmitButton(LANG_IDENTITY_LOGIN);
		$mt->setItem('button', $html_out);
                        
		$html_out = $form->renderFooter();
		$mt->setItem('footer', $html_out);
                                
		return $mt->renderView();
	}

	function doRenderLogout_Button($param, $target, $jump, $other_params, $mt)
    {
    	
		include_once(CORE_DIR . 'bloxx_form.php');
                	                        
		$form = new Bloxx_Form();
                        
		$html_out = $form->renderHeader($this->_BLOXX_MOD_PARAM['name'], 'logout', $this->getCurrentPageID());
		$html_out .= $form->renderSubmitButton(LANG_IDENTITY_LOGOUT);
		$html_out .= $form->renderFooter();
                        
		$mt->setItem('button', $html_out);
		return $mt->renderView();
	}
	
	function doRenderLogout_Link($param, $target, $jump, $other_params, $mt)
    {
    	
		include_once(CORE_DIR . 'bloxx_form.php');
                	                        
		$form = new Bloxx_Form();
                        
		$html_out = $form->renderHeader($this->_BLOXX_MOD_PARAM['name'], 'logout', $this->getCurrentPageID());
		$html_out .= $form->renderFooter();
		$html_out .= $form->renderSubmitLink(LANG_IDENTITY_LOGOUT);
                        
		$mt->setItem('link', $html_out);
		return $mt->renderView();
	}
	
	function doRenderWelcome($param, $target, $jump, $other_params, $mt)
	{
		
		$login = $this->session->getLogin();
		$username = '';
        		
		if ($login != null)
		{                
			$this->clearWhereCondition();
			$this->insertWhereCondition('username', '=', $login);
			$this->runSelect();
                        
			if ($this->nextRow())
			{                        
				$username = $this->realname;
			}
		}
                    
		$html_out =  LANG_IDENTITY_WELCOME . ' ' . $username;
		$mt->setItem('welcome', $html_out);

		return $mt->renderView();
	}
	
	function doRenderChange_Password($param, $target, $jump, $other_params, $mt)
	{
		
		$id = $this->userID();

		if ($id != -1)
		{
			include_once(CORE_DIR . 'bloxx_form.php');
                        	
			$form = new Bloxx_Form();
			$html_out = $form->renderHeader($this->_BLOXX_MOD_PARAM['name'], 'change_password');
			$mt->setItem('header', $html_out);

			$html_out =  LANG_IDENTITY_OLD_PASSWORD;
			$mt->setItem('old_password_label', $html_out);

			$html_out = $form->renderInput('old_password', 'password', '');
			$mt->setItem('old_password', $html_out);

			$html_out =  LANG_IDENTITY_NEW_PASSWORD;
			$mt->setItem('new_password_label', $html_out);

			$html_out = $form->renderInput('new_password', 'password', '');
			$mt->setItem('new_password', $html_out);

			$html_out =  LANG_IDENTITY_NEW_PASSWORD_AGAIN;
			$mt->setItem('new_password_again_label', $html_out);

			$html_out = $form->renderInput('new_password_again', 'password', '');
			$mt->setItem('new_password_again', $html_out);

			$html_out = $form->renderSubmitButton(LANG_IDENTITY_CHANGE_PASSWORD);
			$mt->setItem('button', $html_out);
                                
			$html_out = $form->renderFooter();
			$mt->setItem('footer', $html_out);
			
		}
		
		return $mt->renderView();
	}
	
	function doRenderRegister($param, $target, $jump, $other_params, $mt)
	{
		
		include_once (CORE_DIR . 'bloxx_form.php');
		$form = new Bloxx_Form();
		
		$form->setFromGlobals();
		
		$header = $form->renderHeader('identity', 'register', $this->getMainPageID());		
		$mt->setItem('header', $header);
		
		$mt->startLoop('form');

		include_once (CORE_DIR . 'bloxx_moduleform.php');
		$mf = new Bloxx_ModuleForm($this, false);
		$mf->init();
		$mf->applyFieldInputList(-1, $mt);
		
		$personal_info_module = $this->getConfig('personal_info_module');
		
		if ($personal_info_module != null)
		{
			include_module_once($personal_info_module);
			$personal_info_module = 'Bloxx_' . $personal_info_module; 
			$pi = new $personal_info_module();
			$mf = new Bloxx_ModuleForm($pi, false);
			$mf->init();
			$mf->applyFieldInputList(-1, $mt);
		}
		
		include_module_once('admin');
		$text = LANG_ADMIN_CREATE;
		$mt->setItem('button', $form->renderSubmitButton($text));
		
		$footer = $form->renderFooter();
		$mt->setItem('footer', $footer);

		return $mt->renderView();
	}
	
	function doRenderChange_Data($param, $target, $jump, $other_params, $mt)
	{                        
		return $this->renderForm($this->userID(), false, $mt, $this->getMainPageID());
	}
	
	function doRenderConfirm($param, $target, $jump, $other_params, $mt)
	{                 
		       
		$this->insertWhereCondition('confirm_hash', '=', $_GET['code']);
		$this->runSelect();

		if ($this->nextRow() 
			&& ($this->confirmed == 0) 
			&& ($this->email == $_GET['email']))
		{
			
			$this->confirmed = 1;
			$this->updateRow();
			
			$personal_info_module = $this->getConfig('personal_info_module');
		
			if ($personal_info_module != null)
			{
				include_module_once($personal_info_module);
				$personal_info_module = 'Bloxx_' . $personal_info_module;
				$pi = new $personal_info_module();
				$pi->onConfirmation($this->id);
			}

			$html_out = LANG_IDENTITY_CONFIRMATION_MESSAGE;
			$mt->setItem('message', $html_out);
		}
                        
		//Give no information on failure type for security reasons.
                        
		return $mt->renderView();
	}
	
	function doRenderConfirm_Email($param, $target, $jump, $other_params, $mt)
	{
		$ident = new Bloxx_Identity();
		$ident->getRowByID($param);

		$mt->setItem('realname', $ident->realname);
		$mt->setItem('username', $ident->username);				
		$mt->setItem('password', $target);		
                
		$config = new Bloxx_Config();
		$site_url = $config->getConfig('site_url');
		$confirm_page = $this->getConfig('confirm_page');
		
		$link = $site_url . "/index.php?id=" . $confirm_page . "&email=" . $ident->email . "&code=" . $ident->confirm_hash;
		
		$html_out = '<a href="' . $link . '">' . $link . '</a>';
		$mt->setItem('link', $html_out);
                
		return $mt->renderView();
	}

	
//  Command methods ............................................................
	
	function execCommandLogin()
	{                

		$this->login($_POST['username'], $_POST['password']);
	}
	
	function execCommandLogout()
	{

		$this->logout();
	}
	
	function execCommandRegister()
	{
		
		$identity_id = $this->create();		
		
		if ($identity_id === false)
		{
			return;
		}
		
		$personal_info_module = $this->getConfig('personal_info_module');
		
		if ($personal_info_module != null)
		{
			include_module_once($personal_info_module);
			$personal_info_module = 'Bloxx_' . $personal_info_module;
			$pi = new $personal_info_module();
			$_POST['identity_id'] = $identity_id; 
			$pi->create();
		}
	}
	
	function execCommandChange_Password()
	{
		
		global $warningmessage;

		if (!$this->checkPassword($this->userID(), $_POST['old_password']))
		{
                        
			$warningmessage = LANG_IDENTITY_ERROR_WRONG_PASSWORD;
			return;
		}
                        
		if ($_POST['new_password'] != $_POST['new_password_again'])
		{
                        
			$warningmessage = LANG_IDENTITY_ERROR_NEW_PASSWORD_MISMATCH;
			return;
		}
                        
		$this->password = md5($_POST['new_password']);
                        
		if ($this->updateRow())
		{
                        
			$warningmessage = LANG_IDENTITY_PASSWORD_UPDATED;
		}
		else
		{
                        
			$warningmessage = LANG_GLOBAL_DBERROR;
		}
	}
}
?>
