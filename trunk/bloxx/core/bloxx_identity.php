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
require_once(CORE_DIR.'bloxx_modulemanager.php');
include_once(CORE_DIR.'bloxx_role.php');

class Bloxx_Identity extends Bloxx_Module
{
        function Bloxx_Identity()
        {
                $this->name = 'identity';
                $this->module_version = 1;
                $this->label_field = 'username';
                
                $this->hidden_hash_var = 'aalkb4we1addft71Rj9';
                
                $this->is_loged_in = false;
                $this->use_init_file = true;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'realname' => array('TYPE' => 'STRING', 'SIZE' => 80, 'NOTNULL' => true, 'USER' => true),
                        'username' => array('TYPE' => 'STRING', 'SIZE' => 10, 'NOTNULL' => true, 'USER' => true),
                        'password' => array('TYPE' => 'PASSWORD', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true, 'HIDDEN' => true),
                        'email' => array('TYPE' => 'STRING', 'SIZE' => 50, 'NOTNULL' => true, 'USER' => true, 'CONFIDENTIAL' => true),
                        'confirm_hash' => array('TYPE' => 'STRING', 'SIZE' => 255, 'NOTNULL' => true, 'HIDDEN' => true),
                        'confirmed' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true, 'HIDDEN' => true),
                        'role' => array('TYPE' => 'BLOXX_ROLE', 'SIZE' => -1, 'NOTNULL' => true, 'CONFIDENTIAL' => true)
                );
        }
        
        function getRenderTrusts()
        {
                return array(
                        'loginbox' => TRUST_GUEST,
                        'loginbox_admin' => TRUST_GUEST,
                        'register' => TRUST_GUEST,
                        'change_password' => TRUST_USER,
                        'change_data' => TRUST_USER,
                        'confirm' => TRUST_GUEST
                );
        }
        
        function getFormTrusts()
        {
                return array(
                        'login' => TRUST_GUEST,
                        'logout' => TRUST_GUEST,
                        'change_password' => TRUST_USER
                );
        }
        
        function getStyleList()
        {
                return array(
                        'Label' => 'SmallFormLabel',
                        'Field' => 'SmallFormField',
                        'Button' => 'SmallFormButton',
                        'Text' => 'SmallFormText'
                );
        }
        
        function doRender($mode, $id, $target)
        {
                include_once(CORE_DIR.'bloxx_form.php');
                include_once(CORE_DIR.'bloxx_style.php');
                
                
                global $HTTP_COOKIE_VARS;
                
                if($mode == 'loginbox'){
        
                        $id = $this->id();
                
                        if($id == -1){
                        
                                $style = new Bloxx_Style();
                                $style_small_form_label = $this->getGlobalStyle('Label');
                                $style_small_form_field = $this->getGlobalStyle('Field');
                                $style_small_form_button = $this->getGlobalStyle('Button');
                        
                                $form = new Bloxx_Form();
                                $html_out = $form->renderHeader($this->name, 'login');
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_USERNAME;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= $style->renderStyleHeader($style_small_form_field);
                                $html_out .= $form->renderInput('username', '', '', $style_small_form_field);
                                $html_out .= $style->renderStyleFooter($style_small_form_field);
                                $html_out .= '<br>';
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_PASSWORD;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= $style->renderStyleHeader($style_small_form_field);
                                $html_out .= $form->renderInput('password', 'password', '', $style_small_form_field);
                                $html_out .= $style->renderStyleFooter($style_small_form_field);
                                $html_out .= '<br>';
                                $html_out .= $form->renderSubmitButton(LANG_IDENTITY_LOGIN, $style_small_form_button);
                                $html_out .= $form->renderFooter();
                                
                                return $html_out;
                        }
                        else{
                        
                                $form = new Bloxx_Form();
                                $style_small_form_button = $this->getGlobalStyle('Button');
                                $style_small_form_label = $this->getGlobalStyle('Label');
                                $style_small_text = $this->getGlobalStyle('Text');

                                global $HTTP_GET_VARS;
                                unset($HTTP_GET_VARS['return_id']);
                                unset($HTTP_GET_VARS['id']);
                        
                                $html_out = $form->renderHeader($this->name, 'logout');
                        
                                $style = new Bloxx_Style();

                                $html_out .= $style->renderStyleHeader($style_small_text);
                                $html_out .=  LANG_IDENTITY_WELCOME . ' ' . $HTTP_COOKIE_VARS["login"];
                                $html_out .= $style->renderStyleFooter($style_small_text);
                        
                                $html_out .= $form->renderSubmitButton(LANG_IDENTITY_LOGOUT, $style_small_form_button);
                        
                                $html_out .= $form->renderFooter();
                                
                                return $html_out;
                        }
                }
                else if($mode == 'loginbox_admin'){

                        include_once(CORE_DIR.'bloxx_admin.php');

                        $id = $this->id();

                        if($id == -1){

                                $style = new Bloxx_Style();
                                $admin = new Bloxx_Admin();
                                $style_small_form_label = $admin->getGlobalStyle('Label');
                                $style_small_form_field = $admin->getGlobalStyle('Field');
                                $style_small_form_button = $admin->getGlobalStyle('Button');

                                $form = new Bloxx_Form();
                                $html_out = $form->renderHeader($this->name, 'login');
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_USERNAME;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= $form->renderInput('username', '', '', $style_small_form_field);
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_PASSWORD;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= $form->renderInput('password', 'password', '', $style_small_form_field);
                                $html_out .= $form->renderSubmitButton(LANG_IDENTITY_LOGIN, $style_small_form_button);
                                $html_out .= $form->renderFooter();
                                
                                echo $html_out;
                        }
                        else{

                                $style = new Bloxx_Style();
                                $admin = new Bloxx_Admin();
                                $form = new Bloxx_Form();
                                $style_small_form_button = $admin->getGlobalStyle('Button');
                                $style_small_form_label = $admin->getGlobalStyle('Label');
                                $style_small_text = $admin->getGlobalStyle('Text');

                                $html_out = $form->renderHeader($this->name, 'logout');

                                $style = new Bloxx_Style();

                                $html_out .= $form->renderSubmitButton(LANG_IDENTITY_LOGOUT, $style_small_form_button);
                                $html_out .= $form->renderFooter();
                                
                                return $html_out;
                        }
                }
                if($mode == 'change_password'){

                        $id = $this->id();

                        if($id != -1){

                                $style = new Bloxx_Style();
                                $style_small_form_label = $this->getGlobalStyle('Label');
                                $style_small_form_field = $this->getGlobalStyle('Field');
                                $style_small_form_button = $this->getGlobalStyle('Button');

                                $form = new Bloxx_Form();
                                $html_out = $form->renderHeader($this->name, 'change_password');
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_OLD_PASSWORD;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= '<br>';
                                $html_out .= $form->renderInput('old_password', 'password', '', $style_small_form_field);
                                $html_out .= '<br>';
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_NEW_PASSWORD;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= '<br>';
                                $html_out .= $form->renderInput('new_password', 'password', '', $style_small_form_field);
                                $html_out .= '<br>';
                                $html_out .= $style->renderStyleHeader($style_small_form_label);
                                $html_out .=  LANG_IDENTITY_NEW_PASSWORD_AGAIN;
                                $html_out .= $style->renderStyleFooter($style_small_form_label);
                                $html_out .= '<br>';
                                $html_out .= $form->renderInput('new_password_again', 'password', '', $style_small_form_field);
                                $html_out .= '<br><br>';
                                $html_out .= $form->renderSubmitButton(LANG_IDENTITY_CHANGE_PASSWORD, $style_small_form_button);
                                $html_out .= $form->renderFooter();

                                return $html_out;
                        }
                }
                else if($mode == 'register'){

                        global $HTTP_GET_VARS;
                        unset($HTTP_GET_VARS['return_id']);
                        unset($HTTP_GET_VARS['id']);
                        return $this->renderForm(-1, false);
                }
                else if($mode == 'change_data'){

                        global $HTTP_GET_VARS;
                        unset($HTTP_GET_VARS['return_id']);
                        unset($HTTP_GET_VARS['id']);
                        return $this->renderForm($this->id(), false);
                }
                else if($mode == 'confirm'){

                        global $HTTP_GET_VARS;
                        
                        $style = new Bloxx_Style();
                        $style_small_text = $this->getGlobalStyle('Text');
                        
                        $this->insertWhereCondition("confirm_hash='" . $HTTP_GET_VARS['code'] . "'");
                        $this->runSelect();

                        if ($this->nextRow() && ($this->confirmed == 0) && ($this->email == $HTTP_GET_VARS['email'])){

                                $this->confirmed = 1;
                                $this->updateRow();

                                $html_out .= $style->renderStyleHeader($style_small_text);
                                $html_out .= LANG_IDENTITY_CONFIRMATION_MESSAGE;
                                $html_out .= $style->renderStyleFooter($style_small_text);
                        }
                        
                        //Give no information on failure type for security reasons.
                        
                        return $html_out;
                }
        }
        
        function doProcessForm($command)
        {
                global $HTTP_POST_VARS, $warningmessage;

                if($command == 'login'){

                        $this->login($HTTP_POST_VARS['username'], $HTTP_POST_VARS['password']);
                }
                else if($command == 'logout'){

                        $this->logout();
                }
                else if($command == 'change_password'){

                        if(!$this->checkPassword($this->id(), $HTTP_POST_VARS['old_password'])){
                        
                                $warningmessage = LANG_IDENTITY_ERROR_WRONG_PASSWORD;
                                return;
                        }
                        
                        if($HTTP_POST_VARS['new_password'] != $HTTP_POST_VARS['new_password_again']){
                        
                                $warningmessage = LANG_IDENTITY_ERROR_NEW_PASSWORD_MISMATCH;
                                return;
                        }
                        
                        $this->password = md5($HTTP_POST_VARS['new_password']);
                        
                        if($this->updateRow()){
                        
                                $warningmessage = LANG_IDENTITY_PASSWORD_UPDATED;
                        }
                        else{
                        
                                $warningmessage = LANG_GLOBAL_DBERROR;
                        }
                }
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
                        $this->insertWhereCondition("username='" . $login . "'");
                        $this->insertWhereCondition("password='" . md5($password) . "'");
                        $this->runSelect();
                        
                        if (!$this->nextRow()){
                                //Erro - Utilizador não encontrado ou código de acesso incorrecto
                                $warningmessage = LANG_IDENTITY_ERROR_LOGIN_DENIED;
                                return false;
                        } 
                        else {
                                if ($this->confirmed == 1) {
                                
                                        $this->setTokens($login);
                                        return true;
                                }
                                else {
                                
                                        //Erro - Ainda não confirmou o seu registo
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
                global $HTTP_COOKIE_VARS;

                setcookie('login','',(time()+2592000),'/','',0);
                setcookie('id_hash','',(time()+2592000),'/','',0);
                
                unset($HTTP_COOKIE_VARS["login"]);
                unset($HTTP_COOKIE_VARS["id_hash"]);
        }
        
        function setTokens($login_in) {
                
                global $HTTP_COOKIE_VARS;
                
                if (!$login_in) {
                
                        //Erro - Falta login
                        return false;
                }
                
                $login = $login_in;
                $id_hash = md5($login.$this->hidden_hash_var);

                setcookie('login', $login, (time()+2592000),'/','',0);
                setcookie('id_hash', $id_hash, (time()+2592000),'/','',0);
                
                $HTTP_COOKIE_VARS["login"] = $login;
                $HTTP_COOKIE_VARS["id_hash"] = $id_hash;
        }
        
        function isLoggedIn() {
        
                global $HTTP_COOKIE_VARS;
                
                //have we already run the hash checks? 
                //If so, return the pre-set var
                if (isset($this->is_loged_in) && $this->is_loged_in) {
                
                        return true;
                }
                if (isset($HTTP_COOKIE_VARS["login"]) && isset($HTTP_COOKIE_VARS["id_hash"])) {
                
                        $hash = md5($HTTP_COOKIE_VARS["login"].$this->hidden_hash_var);
                        
                        if ($hash == $HTTP_COOKIE_VARS["id_hash"]) {
                        
                                $this->is_loged_in = true;
                                return true;
                        }
                        else {
                        
                                $this->is_loged_in = false;
                                return false;
                        }
                }
                else {
                
                        $this->is_loged_in = false;
                        return false;
                }
        }
        
        function create()
        {
                global $HTTP_POST_VARS;
                
                if($HTTP_POST_VARS['password'] != $HTTP_POST_VARS['password_again']){

                        $warningmessage = LANG_IDENTITY_ERROR_NEW_PASSWORD_MISMATCH;
                        return false;
                }


                $this->username = $HTTP_POST_VARS['username'];
                $this->realname = $HTTP_POST_VARS['realname'];
                $this->email = $HTTP_POST_VARS['email'];
                
                $this->password = md5($HTTP_POST_VARS['password']);
                $this->confirm_hash = md5($HTTP_POST_VARS['email'].$this->hidden_hash_var);
                $this->confirmed = 0;
                
                $this->role = $this->getConfig('base_role');

                $res = $this->insertRow();
                
                if($res === false){
                
                        return false;
                }
                
                $message = LANG_IDENTITY_CONFIRM_EMAIL . "\n\n";
                $message .= 'Username: ' . $HTTP_POST_VARS['username'] . "\n";
                $message .= 'Password: ' . $HTTP_POST_VARS['password'] . "\n\n";
                
                $config = new Bloxx_Config();
                $site_url = $config->getConfig('site_url');
                $confirm_page = $this->getConfig('confirm_page');
                $message .= $site_url . "/index.php?id=" . $confirm_page . "&email=" . $this->email . "&code=" . $this->confirm_hash;
                
                $confirm_email = $this->getConfig('confirm_email');
                
                //echo $message;

                mail($this->email, LANG_IDENTITY_CONFIRM_EMAIL_SUBJECT, $message, 'From: ' . $confirm_email);
                
                global $warningmessage;

                $warningmessage = LANG_IDENTITY_CONFIRM_MAIL_SENT;
        }
        
        function update()
        {
                global $HTTP_POST_VARS;

                //Allow only admins or indentity owners
                if((!$this->verifyTrust(TRUST_ADMINISTRATOR))
                && ($this->id() != $HTTP_POST_VARS['id'])){

                        return false;
                }

                if(!$this->getRowByID($HTTP_POST_VARS['id'])){

                        return false;
                }

                if(isset($HTTP_POST_VARS['password']) && ($HTTP_POST_VARS['password'] != $this->password)){

                        return false;
                }
                
                $email = $this->email;

                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if(isset($HTTP_POST_VARS[$k])){

                                $this->$k = $HTTP_POST_VARS[$k];
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
        
        function id()
        {
                global $HTTP_COOKIE_VARS;
        
                if ($this->isLoggedIn()){
                
                        $username = $HTTP_COOKIE_VARS["login"];
                        $this->clearWhereCondition();
                        $this->insertWhereCondition("username='" . $username . "'");
                        $this->runSelect();
                        
                        if($this->nextRow()){
                        
                                return $this->id;
                        }
                        else{
                        
                                return -1;
                        }
                }
                else{
        
                        return -1;
                }
        }
        
        function groups()
        {
                $id = $this->id();
        
                include_module_once('grouplink');
                
                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition("identity_id=" . $id);
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
                $id = $this->id();

                include_module_once('grouplink');

                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition("identity_id=" . $id);
                $gl->runSelect();

                if($gl->nextRow()){
                
                        return true;
                }
                
                return false;
        }
        
        function belongsToGroup($group_id)
        {
                $id = $this->id();

                include_module_once('grouplink');

                $gl = new Bloxx_GroupLink();
                $gl->clearWhereCondition();
                $gl->insertWhereCondition("identity_id=" . $id);
                $gl->runSelect();

                while($gl->nextRow()){

                        if($gl->group_id == $group_id){
                        
                                return true;
                        }
                }

                return false;
        }
}

/*

function user_lost_password ($email,$user_name) {
        global $feedback,$hidden_hash_var;
        global $cifroes_system_mail_address;
        if ($email && $user_name) {
                $user_name=strtolower($user_name);
                $sql="SELECT * FROM cliente WHERE numero_contribuinte='$user_name' AND email='$email'";
                $result=db_query($sql);
                if (!$result || db_numrows($result) < 1) {
                        //no matching user found
                        $feedback .= ' Número de contribuinte ou email incorrecto ';
                        return false;
                } else {
                        //create a secure, new password
                        $new_pass=strtolower(substr(md5(time().$user_name.$hidden_hash_var),1,14));

                        //update the database to include the new password
                        $sql="UPDATE cliente SET password='". md5($new_pass) ."' WHERE numero_contribuinte='$user_name'";
                        $result=db_query($sql);
                        //send a simple email with the new password
                        mail ($email,'Recuperação de Password','A sua Password '.
                                'foi alterada para: '.$new_pass,'From: '.$cifroes_system_mail_address);
                        $feedback .= ' A nova password foi enviada para o seu email. ';
                        return true;
                }
        } else {
                $feedback .= ' Tem que inserir o seu número de contribuinte e email. ';
                return false;
        }
}

function validate_email ($address) {
        return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address));
}

        var $hidden_hash_var;
        var $is_loged_in;
}*/

?>
