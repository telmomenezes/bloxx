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
require_once CORE_DIR.'bloxx_dbobject.php';

/**
 * Bloxx_Module
 *
 * @author      Telmo Menezes <telmo@cognitiva.net>
 * @copyright   Copyright &copy; 2002-2005 Telmo Menezes <telmo@cognitiva.net>
 * @license     http://www.gnu.org/licenses/gpl.txt The GNU General Public License, Version 2
 * @category    Bloxx
 * @package     core
 * @since       Bloxx 0.1
 */
class Bloxx_Module extends Bloxx_DBObject
{
        //abstract
        function doRender($mode, $id, $target){}
        function doProcessForm($command){}
        function getRenderTrusts(){}
        function getFormTrusts(){}
        function getStyleList(){return array();}
        function doRenderJavaScript($mode, $id){}
        function getTableDefinition(){}
        
        function Bloxx_Module()
        {

                $def = $this->tableDefinition();
                
                if($def != null){
        
                        foreach($def as $k => $v){
                
                                $this->$k = null;
                        }
                }
        }
        
        function tableDefinition()
        {
                $def1 = array(
                        'id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true, 'HIDDEN' => true),
                        'workflow' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'HIDDEN' => true)
                );
                
                if((!isset($this->no_private)) || (!$this->no_private)){

                        $def_private = array('private_content' => array('TYPE' => 'BLOXX_USERGROUP', 'SIZE' => -1, 'NOTNULL' => true));
                        $def1 = array_merge($def1, $def_private);
                }
                
                $def2 = $this->getTableDefinition();
                
                return array_merge($def1, $def2);
        }

        /**
        * Renders a view for a data element of the module
        *
        * @param  string  $mode
        * @param  integer $id
        * @param  integer $target
        * @return string
        * @access public
        * @static
        */
        function render($mode, $id, $target=null)
        {
                include_module_once('moduletemplate');
                $mt = new Bloxx_ModuleTemplate();
                $mt->getTemplate($this, $mode);
        
                if($this->isGenericRender($mode)){
                
                        return $this->doGenericRender($mode, $id, $target);
                }
        
                $trusts = $this->getRenderTrusts();
                
                if(isset($trusts[$mode]) && $this->verifyTrust($trusts[$mode], $id)){
                
                        return $this->doRender($mode, $id, $target, $mt);
                }
        }
        
        function renderJavaScript($mode, $id)
        {
                $js_out = '';
                $js_file = JAVASCRIPT_DIR . 'bloxx_' . $this->name;
                
                $js_out = '<script language="JavaScript" type="text/javascript" src="' .
                $js_file .
                '"></script>';
                
                $js_out .= $this->doRenderJavaScript($mode, $id);
                
                return $js_out;
        }
        
        function processForm($command)
        {
                $trusts = $this->getFormTrusts();

                if(isset($trusts[$command]) && $this->verifyTrust($trusts[$command])){
                
                        $this->doProcessForm($command);
                }
        }
        
        function getVersion()
        {
                return $this->module_version;
        }
        
        function install()
        {
        
                if($this->tableDefinition() != null){
                
                        $ret = $this->createTable();
                        
                        if(PEAR::isError($ret)){

                                return $ret;
                        }
                }
                
                include_module_once('modulemanager');
                $module_manager = new Bloxx_ModuleManager();
                $module_manager->register($this->name, $this->getVersion());
                
                if($this->name != 'modulemanager'){
                
                        include_module_once('role');
                        $role = new Bloxx_Role();
                        $role->registerModule($this->name);
                }
        }
        
        function afterInstall()
        {
                
                //Insert rows from .bloxx files
                $this->parse();
        }
        
        function renderForm($id, $inadmin, $module_template)
        {
        
                include_module_once('admin');
                include_module_once('moduletemplate');

                $admin = new Bloxx_Admin();
                
                include_once(CORE_DIR . 'bloxx_form.php');
                
                $form = new Bloxx_Form();
                
                if($inadmin){
                
                        $form->setMode('module');
                        $form->setParam($this->name);
                }
                else{
                
                        $form->setFromGlobals();
                }
                
                $header = '';

                if($id >= 0){

                        $header .= $form->renderHeader('admin', 'change');
                }
                else{

                        $header .= $form->renderHeader('admin', 'create');
                }
        
                if($id >= 0){
                
                        $this->getRowByID($id, true);
  
                        $header .= $form->renderInput('id', 'hidden', $id);
                }
                
                $header .= $form->renderInput('target_module', 'hidden', $this->name);
                
                $module_template->setItem('header', $header);
                $module_template->startLoop('form');
   
                $def = $this->tableDefinitionLangComplete();
                
                foreach($def as $k => $v){
                
                        $module_template->nextLoopIteration();
                
                        $form_type = '';
                
                        if((!$inadmin) && ((!isset($v['USER'])) || (!$v['USER']))){
                        
                                $form_type = 'hidden';
                        }
                        else if(($v['TYPE'] != 'PASSWORD') && ($k != 'private_content')){
                                
                                $lang_code = null;
                                if(isset($v['LANG_CODE'])){
                                
                                        $lang_code = $v['LANG_CODE'];
                                }
                                
                                $module_template->setLoopItem('label',  $this->fieldLabel($v['FIELD_NAME'], $lang_code));
                        }
                        
                        $length = 0;

                        if((($id >= 0) || (!$inadmin)) && (isset($this->$k))){

                                $value = $this->$k;
                        }
                        else{

                                $value = '';
                        }
                        
                        if(($v['TYPE'] == 'TEXT') || ($v['TYPE'] == 'HTML')) {
                                
                                if($inadmin){
                                
                                        $module_template->setLoopItem('field', $form->renderTextArea($k, 30, 80, $value));
                                }
                                else{
                                
                                        $module_template->setLoopItem('field', $form->renderTextArea($k, 30, 70, $value));
                                }
                        }
                        else if($k == 'private_content'){
                        
                                //Don't show this field.
                        }
                        else if((substr($v['TYPE'], 0, 6) == "BLOXX_")  && ($form_type != 'hidden')){
                        
                                $select_field = $form->startSelect($k, 1);
                                
                                $typemod = substr($v['TYPE'], 6);
                                include_module_once($typemod);
                                $typemod = 'bloxx_' . $typemod;
                                $typeinst = new $typemod();
                                
                                $typeinst->clearWhereCondition();
                                $typeinst->runSelect();

                                while($typeinst->nextRow()){

                                        $labelf = $typeinst->label_field;
                                        
                                        if($typeinst->id == $value){
                                        
                                                $selected = true;
                                        }
                                        else{
                                        
                                                $selected = false;
                                        }
                                        
                                        $select_field .= $form->addSelectItem($typeinst->id, $typeinst->$labelf, $selected);
                                }
                                
                                $select_field .= $form->endSelect();
                                $module_template->setLoopItem('field', $select_field);
                        }
                        else if((substr($v['TYPE'], 0, 5) == "ENUM_")  && ($form_type != 'hidden')){

                                $select_field = $form->startSelect($k, 1);

                                $enum_name = substr($v['TYPE'], 5);
                                $enum_var = 'ENUM_' . $enum_name;
                                include_enum_once($enum_name);

                                global $$enum_var;
                                $enum = $$enum_var->getEnum();

                                foreach($enum as $k => $v){

                                        if($k == $value){

                                                $selected = true;
                                        }
                                        else{

                                                $selected = false;
                                        }
                                        
                                        $langname = $this->enumLabel($enum_name, $v, $lang_code);

                                        $select_field .= $form->addSelectItem($k, $langname, $selected);
                                }

                                $select_field .= $form->endSelect();
                                
                                $module_template->setLoopItem('field', $select_field);
                        }
                        else if($v['TYPE'] == 'PASSWORD'){
                        
                                if($id < 0){

                                        $module_template->setLoopItem('label', $k);
                                        $module_template->setLoopItem('field', $form->renderInput($k, 'password', $value));
                                        $module_template->nextLoopIteration();
                                        $module_template->setLoopItem('label', $k . ' (again)');
                                        $module_template->setLoopItem('field', $form->renderInput($k . '_again', 'password', $value));
                                }
                        }
                        else if($v['TYPE'] == 'FILE'){

                                $file_input = $form->renderInput('MAX_FILE_SIZE', 'hidden', 300000, null);
                                $file_input .= $form->renderInput($k, 'file', $value);
                                $module_template->setLoopItem('field', $file_input);
                        }
                        else if($v['TYPE'] == 'IMAGE'){

                                if($form_type != 'hidden'){

                                        $image_input = '<img width="100" src="image.php?module=' . $this->name . '&id=' . $this->id . '&field=' . $k . '"></img>';
                                        $image_input .= '<br>';
                                        $image_input .= $form->renderInput('MAX_FILE_SIZE', 'hidden', 9999999, null);
                                        $image_input .= $form->renderInput($k, 'file', '');
                                        $module_template->setLoopItem('field', $image_input);
                                }
                        }
                        else if($v['TYPE'] == 'DATE'){

                                $date = getDate($value);

                                $date_input = $form->renderInput($k . '__day', 'input', $date['mday'], 2, 2);
                                $date_input .= $form->renderMonthSelector($k . '__month', $date['mon']);
                                $date_input .= $form->renderInput($k . '__year', 'input', $date['year'], 4, 4);
                                $module_template->setLoopItem('field', $date_input);
                        }
                        else if($v['TYPE'] == 'NUMBER'){

                                $module_template->setLoopItem('field', $form->renderInput($k, $form_type, $value, 10, 15));

                        }
                        else{

                                $maxsize = $v['SIZE'];
                                
                                if($maxsize <= 0){
                                
                                        $maxsize = 255;
                                }
                                
                                $size = $maxsize;
                                
                                if($maxsize > 80){
                                
                                        $size = 80;
                                }
                                
                                $module_template->setLoopItem('field', $form->renderInput($k, $form_type, $value, $size, $maxsize));
                        }
                }
                
                //Render group selector here...
                include_module_once('identity');
                $ident = new Bloxx_Identity();

                if(((!isset($this->no_private))
                        || (!$this->no_private))
                        && (($ident->belongsToGroups()) || $inadmin)){
                
                        $glist = $ident->groups();

                        include_module_once('usergroup');
                
                        $module_template->nextLoopIteration();

                        $module_template->setLoopItem('label', LANG_USERGROUP_PRIVATE_TO_GROUP);

                        $select_input = $form->startSelect('private_content', 1);
                        
                        if($this->private_content == 0){

                                $selected = true;
                        }
                        else{

                                $selected = false;
                        }
                        
                        $select_input .= $form->addSelectItem(0, LANG_USERGROUP_NO, $selected);

                        if(isset($glist)){
                        
                                foreach($glist as $v){

                                        $grp = new Bloxx_UserGroup();
                                        $grp->getRowByID($v);

                                        if($this->private_content == $v){

                                                $selected = true;
                                        }
                                        else{

                                                $selected = false;
                                        }

                                        $select_input .= $form->addSelectItem($v, $grp->groupname, $selected);
                                }
                        }

                        $select_input .= $form->endSelect();
                        
                        $module_template->setLoopItem('field', $select_input);
                }
   
                if($id >= 0){

                        $module_template->setItem('button', $form->renderSubmitButton(LANG_ADMIN_APPLY_CHANGES));
                }
                else{
                
                        $text = LANG_ADMIN_CREATE;
                        $module_template->setItem('button', $form->renderSubmitButton($text));
                }
                
                $module_template->setItem('footer', $form->renderFooter());
                
                return $module_template->renderView();
        }
        
        function assignValuesFromPost($new)
        {

                global $_POST;
        
                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if($v['TYPE'] == 'DATE'){

                                if(isset($_POST[$k . '__month'])
                                && isset($_POST[$k . '__day'])
                                && isset($_POST[$k . '__year'])
                                ){

                                        $this->$k = mktime(0, 0, 0, $_POST[$k . '__month'], $_POST[$k . '__day'], $_POST[$k . '__year']);
                                }
                        }
                        else if($new && ($v['TYPE'] == 'CREATIONDATETIME')){
                        
                                $this->$k = time();
                        }
                        else if($new && ($v['TYPE'] == 'CREATORID')){

                                include_module_once('identity');
                                $ident = new Bloxx_Identity();
                                $this->$k = $ident->id();
                        }
                        else if($v['TYPE'] == 'IMAGE'){

                                global $_FILES;

                                if((isset($_FILES[$k]['tmp_name'])) && (!isset($this->$k))){
                                        
                                        if(isset($v['IMG_WIDTH']) && !isset($v['IMG_HEIGHT'])){

                                                include_once(CORE_DIR . 'bloxx_image_utils.php');
                                                $this->$k = scaleJpegWidth($_FILES[$k]['tmp_name'], $v['IMG_WIDTH']);
                                        }
                                        else{
                                        
                                                $this->$k = fread(fopen($_FILES[$k]['tmp_name'], "r"), $_FILES[$k]['size']);
                                        }
                                }
                        }
                        else{

                                if(isset($_POST[$k])){

                                        $this->$k = $_POST[$k];
                                }
                        }
                }
        }
        
        function create($verify_trust = true)
        {
                if($verify_trust){
                
                        if(!$this->verifyTrust(TRUST_ADMINISTRATOR)){
                
                                return false;
                        }
                }
        
                $this->assignValuesFromPost(true);

                $wf = -1;
                
                if(!$this->hasWorkflow()){

                        $wf = 1;
                }
                else{

                        $this->workflow = $wf;
                        global $warningmessage;
                        $warningmessage = LANG__WORKFLOW_SUBMIT;
                }

                return $this->newDataElement();
        }
        
        function update($verify_trust = true)
        {
                global $_POST;
        
                if($verify_trust){
                
                        if(!$this->verifyTrust(TRUST_ADMINISTRATOR)){

                                return false;
                        }
                }
                
                if(!$this->getRowByID($_POST['id'])){
                
                        return false;
                }
        
                $this->assignValuesFromPost(false);
                
                return $this->updateRow(true);
        }
        
        function verifyTrust($trust, $id = -1)
        {
        
                include_module_once('role');

                $current_trust = $this->getTrust();
                
                if($current_trust < $trust){
                
                        return false;
                }
                
                if((!isset($this->no_private) || (!$this->no_private))
                        && ($this->private_content > 0)){
                        
                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                
                        if(!$ident->belongsToGroup($this->private_content)){

                                return false;
                        }
                }

                if($id > 0){
                
                        if(($current_trust < TRUST_MODERATOR) && ($this->hasWorkflow())){

                                $modclone = $this->modClone();
                                $modclone->getRowByID($id);

                                if($modclone->workflow <= 0){
                        
                                        return false;
                                }
                        }
                }
                
                return true;
        }
        
        function getTrust()
        {
        
                include_module_once('identity');

                $ident = new Bloxx_Identity();
                $iid = $ident->id();

                $trust = TRUST_GUEST;

                if($iid != -1){

                        $role = new Bloxx_Role();
                        $role->getRowByID($ident->role);
                        $trust = $role->getTrust($this->name);
                }
                
                return $trust;
        }
        
        function getCurrentPageID()
        {
                global $_GET;
        
                if(isset($_GET['id'])){

                        return $_GET['id'];
                }
                else{

                        include_once(CORE_DIR.'bloxx_config.php');
                        $system = new Bloxx_Config();
                        return $system->getMainPage();
                }
        }
        
        function getGlobalStyle($module_style)
        {
                include_module_once('stylelink');
                $stylelink = new Bloxx_StyleLink();
                
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                
                $stylelink->clearWhereCondition();
                $stylelink->insertWhereCondition('module_id', '=', $mm->getModuleID($this->name));
                $stylelink->insertWhereCondition('module_style', '=', $module_style);
                $stylelink->runSelect();

                if (!$stylelink->nextRow()){
                
                        //estilo não encontrado
                        return;
                }
                
                return $stylelink->global_style;
        }
        
        function parse()
        {

                include_module_once('initparser');
                $p = new Bloxx_InitParser($this);
                $result = $p->init();
                $result = $p->parse();
                
                return true;
        }
        
        function newDataElement()
        {
        
                return $this->insertRow(false);
        }
        
        function saveDataToFile($file_path)
        {
                $file_name = $file_path . '.bloxx';
                
                echo $file_name . '<br>';
                
                $handle = fopen($file_name, "a+");

                $this->clearWhereCondition();
                $this->runSelect();
                
                fwrite($handle, "[MODULE " . $this->name . "]\n");

                while($this->nextRow(false)){
                
                        fwrite($handle, "[row]\n");
                
                        $def = $this->tableDefinitionLangComplete();

                        foreach($def as $k => $v){
                
                                fwrite($handle, "[" . $k . "]");

                                $value = $this->$k;
                                
                                $value = str_replace('$', '$dolar', $value);
                                $value = str_replace('[', '$open_bracket', $value);
                                
                                fwrite($handle, $value);
                                fwrite($handle, "[_" . $k . "]\n");
                        }
                
                        fwrite($handle, "[_row]\n");
                
                }
                
                fwrite($handle, "[_" . $this->name . "]\n");
                
                fclose($handle);
        }
        
        function getConfig($item_name)
        {
                include_module_once('config');
                
                $config = new Bloxx_Config();
                return $config->getValue($this->getModID(), $item_name);
        }
        
        function tableDefinitionLangComplete()
        {
                $def = $this->tableDefinition();
                
                include_module_once('language');
                
                foreach($def as $k => $v){
                
                        if(isset($v['LANG']) && $v['LANG']){

                                $lang = new Bloxx_Language();
                                $lang->clearWhereCondition();
                                $lang->runSelect();

                                while ($lang->nextRow()) {
                                
                                        $klang = $k . '_LANG_' . $lang->code;
                                        $v['LANG_CODE'] = $lang->code;
                                        $v['FIELD_NAME'] = $k;
                                        $ret[$klang] = $v;
                                }
                        }
                        else{

                                $v['FIELD_NAME'] = $k;
                                $ret[$k] = $v;
                        }
                }
                
                return $ret;
        }
        
        function renderLabel()
        {

                $label = $this->label_field;
                
                if(!isset($this->$label)){
                
                        return null;
                }
                return $this->$label;
        }
        
        function getRowIDFromEnd($count)
        {
                $this->clearWhereCondition();
                $this->setOrderBy('id', true);
                $this->setLimit($count);
                $this->runSelect();

                $n = $count;

                if((isset($this->no_private)) && ($this->no_private)){

                        while($n > 0){

                                if(!$this->nextRow()){

                                        $n = 0;
                                }

                                $n--;
                        }

                        return $this->id;
                }
                else{
                        include_module_once('identity');
                        $ident = new Bloxx_Identity();

                        $hasWorkflow = $this->hasWorkflow();

                        while($n > 0){

                                if(!$this->nextRow()){

                                        $n = 0;
                                }


                                if((($this->private_content <= 0)
                                        || ($ident->belongsToGroup($this->private_content)))
                                &&((!$hasWorkflow)
                                        || ($this->workflow > 0))){

                                        $n--;
                                }
                        }

                        return $this->id;
                }
        }
        
        function fieldLabel($field, $lang_code)
        {
                if(($field == 'id') || ($field == 'workflow')){
                
                        $field_label = constant('F_LANG__' . strtoupper($field));
                }
                else{
                
                        $field_label = constant('F_LANG_' . strtoupper($this->name) . '_' . strtoupper($field));
                }
                
                if(isset($lang_code)){
                
                        include_module_once('language');
                        $lang = new Bloxx_Language();
                        $lang->insertWhereCondition('code', '=', $lang_code);
                        $lang->runSelect();

                        if($lang->nextRow()){
                        
                                $field_label .= ' (' . $lang->language_name . ')';
                        }
                }
        
                return $field_label;
        }
        
        function enumLabel($enum, $enum_element, $lang_code)
        {

                $enum_label = constant('E_LANG_' . strtoupper($enum) . '_' . strtoupper($enum_element));

                return $enum_label;
        }
        
        function renderRow($style_title, $style_text)
        {
                include_once(CORE_DIR.'bloxx_style.php');
                $style = new Bloxx_Style();

                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if(($v['TYPE'] != 'PASSWORD') && ($k != 'private_content') && ((!isset($v['HIDDEN']) || (!$v['HIDDEN'])))){
                        
                                if((isset($v['CONFIDENTIAL']) && ($v['CONFIDENTIAL']))){

                                        if(!$this->verifyTrust(TRUST_ADMINISTRATOR)){
                                        
                                                continue;
                                        }
                                }

                                $html_out = $style->renderStyleHeader($style_title);
                                $lang_code = null;
                                if(isset($v['LANG_CODE'])){
                                
                                        $lang_code = $v['LANG_CODE'];
                                }
                                $html_out .= $this->fieldLabel($v['FIELD_NAME'], $lang_code);
                                $html_out .= $style->renderStyleFooter($style_title);
                                $html_out .= '<br>';


                                if($v['TYPE'] == 'DATE'){

                                        $html_out .= $style->renderStyleHeader($style_text);
                                        $html_out .= getDateString($this->$k);
                                        $html_out .= $style->renderStyleFooter($style_text);
                                }
                                else if(substr($v['TYPE'], 0, 6) == "BLOXX_"){

                                        $typemod = substr($v['TYPE'], 6);
                                        include_module_once($typemod);
                                        $typemod = 'bloxx_' . $typemod;
                                        $typeinst = new $typemod();

                                        $typeinst->getRowByID($this->$k);

                                        $labelf = $typeinst->label_field;

                                        $html_out .= $style->renderStyleHeader($style_text);
                                        $html_out .= $typeinst->$labelf;
                                        $html_out .= $style->renderStyleFooter($style_text);
                                }
                                else{

                                        $html_out .= $style->renderStyleHeader($style_text);
                                        $html_out .= $this->$k;
                                        $html_out .= $style->renderStyleFooter($style_text);
                                }
                                
                                $html_out .= '<br><br>';
                        }
                }

                //Private to group info
                include_module_once('identity');
                $ident = new Bloxx_Identity();

                if(((!isset($this->no_private))
                        || (!$this->no_private))
                        && ($this->private_content > 0)){

                        $glist = $ident->groups();

                        include_module_once('usergroup');

                        $html_out .= $style->renderStyleHeader($style_title);
                        $html_out .= LANG_USERGROUP_PRIVATE_TO_GROUP;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= '<br>';

                        $grp = new Bloxx_UserGroup();
                        $grp->getRowByID($this->private_content);

                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= $grp->groupname;
                        $html_out .= $style->renderStyleFooter($style_text);

                        $html_out .= '<br><br>';
                }

                return $html_out;
        }
        
        function renderAutoText($field)
        {
                return nl2br($field);
        }
        
        function hasWorkflow()
        {
                $wf = $this->getConfig('workflow');
                
                return ($wf > 0);
        }
        
        function modClone()
        {
                $mname = 'Bloxx_' . $this->name;
                $modclone = new $mname();
                return $modclone;
        }
        
        function submissionCount()
        {
                $this->clearWhereCondition();
                $this->insertWhereCondition('workflow', '<=', '0');
                return $this->runSelect();
        }
        
        function renderImage($field, $align = null)
        {
        
                $html_out = '<img src="image.php?module=' . $this->name . '&id=' . $this->id . '&field=' . $field . '" border="0"';
                
                if($align != null){
                
                        $html_out .= ' align="' . $align . '" ';
                }
                
                $html_out .= '></img>';
                
                return $html_out;
        }

        function insertListConditions()
        {
                return;
        }
        
        function renderList($start_id,
                                $columns,
                                $rows,
                                $mode,
                                $html_after_row,
                                $html_after_column)
        {
        
                $this->clearWhereCondition();
                $this->insertWhereCondition('id', '>=', $start_id);
                $this->insertListConditions();
                $this->setOrderBy('id');
                $this->runSelect();

                $html_out = '';

                $total = $columns * $rows;
                $counter = 0;
                
                while(($this->nextRow()) && ($counter < $total)){

                        $clone = $this->modClone();
                        $html_out .= $clone->render($mode, $this->id);

                        $counter++;
                        
                        //End of row
                        if(($counter % $columns) == 0){

                                $html_out .= $html_after_row;
                        }
                        //End of column
                        else{
                        
                                $html_out .= $html_after_column;
                        }
                }

                //Balance table (render empty cells in last row)
                while(($counter % $columns) != 0){

                        $counter++;
                        //End of row
                        if(($counter % $columns) == 0){

                                $html_out .= $html_after_row;
                        }
                        //End of column
                        else{

                                $html_out .= $html_after_column;
                        }
                }
                
                return $html_out;
        }
        
        function nextListID($start_id, $count)
        {

                $this->clearWhereCondition();
                $this->insertWhereCondition('id', '>=', $start_id);
                $this->insertListConditions();
                $this->setOrderBy('id');
                $this->runSelect();

                $html_out = '';
                $n = 0;
                $id = $start_id;

                while(($this->nextRow()) && ($n <= $count)){

                        $id = $this->id;
                        $n++;
                }
                
                if($n <= $count){
                
                        return -1;
                }

                return $id;
        }
        
        function previousListID($start_id, $count)
        {

                $this->clearWhereCondition();
                $this->insertWhereCondition('id', '<=', $start_id);
                $this->insertListConditions();
                $this->setOrderBy('id', true);
                $this->runSelect();

                $html_out = '';
                $n = 0;
                $id = $start_id;

                while(($this->nextRow()) && ($n <= $count)){

                        $id = $this->id;
                        $n++;
                }

                return $id;
        }
        
        function getState($item)
        {
                include_module_once('state');
                $state = new Bloxx_State();
                return $state->getValue($this->name, $item);
        }
        
        function setState($item, $value)
        {
                include_module_once('state');
                $state = new Bloxx_State();
                $state->setValue($this->name, $item, $value);
        }
        
        function getModID()
        {
                include_module_once('modulemanager');
                $mm = new Bloxx_ModuleManager();
                return $mm->getModuleID($this->name);
        }
        
        function isGenericRender($mode)
        {
        
                if($mode == 'configdata'){
                
                        return true;
                }
                
                return false;
        }

        function doGenericRender($mode, $id, $target)
        {

                if($mode == 'configdata'){
                
                        return $this->getConfig($id);
                }
        }

        var $table_rows;
        var $module_version;
        var $label_field;
        var $default_mode;
        var $parser_item;
        var $current_tag;
        var $last_field;
        var $current_field;
        var $use_init_file;
        var $private_content;
}

?>
