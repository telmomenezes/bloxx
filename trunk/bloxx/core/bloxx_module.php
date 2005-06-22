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
// $Id: bloxx_module.php,v 1.14 2005-06-22 20:05:35 tmenezes Exp $

require_once 'defines.php';
require_once CORE_DIR.'bloxx_dbobject.php';

/**
  * This is the class from where all Bloxx Module classes are derived from.
  *
  * @package   bloxx_core
  * @version   $Id: bloxx_module.php,v 1.14 2005-06-22 20:05:35 tmenezes Exp $
  * @category  Core
  * @copyright Copyright &copy; 2002-2005 The Bloxx Team
  * @license   The GNU General Public License, Version 2
  * @author    Telmo Menezes <telmo@cognitiva.net>
  */
class Bloxx_Module extends Bloxx_DBObject
{

	/**
	  * Default constructor.
	  * 
	  */
	function Bloxx_Module()
	{

		$def = $this->tableDefinition();

		if ($def != null)
		{

			foreach ($def as $k => $v)
			{

				$this-> $k = null;
			}
		}
	}

	//abstract methods
	function getLocalRenderTrusts()
	{
		return array();
	}
	function getLocalCommandTrusts()
	{
		return array();
	}
	function getRenderTrusts()
	{
		$trusts1 = array(
                        'generic_delete' => TRUST_DELETER,
                        'generic_edit' => TRUST_EDITOR,
                        'generic_create' => TRUST_MODERATOR
                	);
		$trusts2 = $this->getLocalRenderTrusts();

		return array_merge($trusts1, $trusts2);
	}
	function getCommandTrusts()
	{
		$trusts1 = array(
                        'generic_create' => TRUST_MODERATOR,
                        'generic_edit' => TRUST_EDITOR,
                        'generic_delete' => TRUST_DELETER
                	);
		$trusts2 = $this->getLocalCommandTrusts();
		
		return array_merge($trusts1, $trusts2);
	}

	function doRenderJavaScript($mode, $id)
	{
	}
	
	function getTableDefinition()
	{
	}

	/**
	  * Returns the array with the table definition for the module.
	  * 
	  * This method merges an array with global module field definitions
	  * with the array of specific field definitions provided by each specific
	  * module. 
	  *
	  * @return array  Field definition list array.
	  *
	  */
	function tableDefinition()
	{

		$def1 = array ('id' => array ('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true, 'HIDDEN' => true), 'workflow' => array ('TYPE' => 'NUMBER', 'SIZE' => -1, 'HIDDEN' => true));

		if ((!isset ($this->no_private)) || (!$this->no_private))
		{

			$def_private = array ('private_content' => array ('TYPE' => 'BLOXX_USERGROUP', 'SIZE' => -1, 'NOTNULL' => true));
			$def1 = array_merge($def1, $def_private);
		}

		$def2 = $this->getTableDefinition();

		return array_merge($def1, $def2);
	}

	/**
	  * Renders a view for a data element in the module.
	  *
	  * @param string   $mode      Render mode
	  * @param int      $id        Item database ID
	  * @param int      $target    Optional target item database ID
	  *
	  * @return string  HTML block
	  *
	  */

	function render($view, 
					$param, 
					$target = -1, 
					$jump = -1, 
					$other_params = null, 
					$template = null)
	{

		$trusts = $this->getRenderTrusts();		

		if (isset($trusts[$view]) && $this->verifyTrust($trusts[$view], $id))
		{
			include_module_once('moduletemplate');
			
			global $CACHE_MT;
			
			if (!isset($CACHE_MT[$this->name][$view][$template]))
			{
			
				$CACHE_MT[$this->name][$view][$template] = new Bloxx_ModuleTemplate();
			
				if ((!$CACHE_MT[$this->name][$view][$template]->getTemplate($this, $view, $template))
					&& ($this->isGenericRender($view)))
				{
					$conf_inst = new Bloxx_Config();
					$CACHE_MT[$this->name][$view][$template]->getTemplate($conf_inst, $view, $template);
				}
			}			
			
			$mt = $CACHE_MT[$this->name][$view][$template];	
			
			if ($this->isGenericRender($view))
			{

				return $this->doGenericRender($view, $param, $target, $mt);
			}
			
			if ($this->verifyTrust(TRUST_EDITOR, $param))
			{
				$html_out = $this->doGenericRender('generic_edit_button', $param, $target, $mt);
				$mt->setItem('generic_edit_button', $html_out);
			}
			if ($this->verifyTrust(TRUST_DELETER, $param))
			{
				$html_out = $this->doGenericRender('generic_delete_button', $param, $target, $mt);
				$mt->setItem('generic_delete_button', $html_out);
			}			

			global $GLOBAL_CURRENT_JUMP_STRING;
			
			if ($jump != -1)
			{
				$GLOBAL_CURRENT_JUMP_STRING = $jump;
			}
			else
			{
				unset($GLOBAL_CURRENT_JUMP_STRING);
			}

			$do_render_method = 'doRender' . $view;			
			return $this->$do_render_method($param, $target, $jump, $other_params, $mt);
		}
	}

	/**
	  * Renders the JavaScript code for specific module view.
	  *
	  * @param string   $mode      Render mode
	  * @param int      $id        Item database ID
	  *
	  * @return string  JavaScript code.
	  *
	  */

	function renderJavaScript($mode, $id)
	{

		$js_out = '';

		if (isset ($this->java_script) && ($this->java_script == true))
		{

			$js_file = JAVASCRIPT_DIR.'bloxx_'.$this->name;
			$js_out = '<script language="JavaScript" type="text/javascript" src="'.$js_file.'"></script>';
			$js_out .= $this->doRenderJavaScript($mode, $id);
		}

		return $js_out;
	}
	
	function renderBodyParams($view, $param, $target)
	{
		return '';
	}

	/**
	  * Processes a command on a module.
	  *
	  * Verifies trust and the calls doProcessForm() on the current module.
	  *
	  * @param  string   $command  Command name.
	  *
	  */

	function execCommand($command)
	{
		$trusts = $this->getCommandTrusts();

		if (isset ($trusts[$command]) && $this->verifyTrust($trusts[$command]))
		{

			if($this->isGenericCommand($command))
			{
				
				$this->execGenericCommand($command);
			}
			else
			{
				$exec_method = 'execCommand' . $command;
				$this->$exec_method();
			}
		}
	}

	/**
	  * Returns the module version.
	  *
	  * @return string  Module version.
	  *
	  */

	function getVersion()
	{

		return $this->module_version;
	}

	/**
	  * Installs a module.
	  * 
	  * Creates the database table, registers module on the ModuleManager.
	  *
	  * @return error
	  *
	  */

	function install($register = true)
	{

		if ($this->tableDefinition() != null)
		{

			$ret = $this->createTable();

			if (PEAR::isError($ret))
			{

				return $ret;
			}
		}

		if ($register)
		{
			include_module_once('modulemanager');
			$module_manager = new Bloxx_ModuleManager();
			$module_manager->register($this->name, $this->getVersion());

			if ($this->name != 'modulemanager')
			{

				include_module_once('role');
				$role = new Bloxx_Role();
				$role->registerModule($this->name);
			}
		}
	}

	/**
	  * Insert rows from .bloxx files for this module.
	  */

	function afterInstall()
	{
		if (isset ($this->use_init_file) && $this->use_init_file)
		{

			include_module_once('initparser');
			$p = new Bloxx_InitParser($this);
			$result = $p->init();
			$result = $p->parse();
		}

		return true;
	}

	/**
	  * Renders a create/edit form for a module.
	  *
	  * @param int                    $id               Database ID of the item to 
	  *                                                 edit, -1 to create
	  * @param boolean                $inadmin          Are we calling this from the 
	  *                                                 backend?
	  * @param Bloxx_ModuleTemplate   $module_template  The module template.
	  * 
	  * @return string  HTML block.
	  *
	  */

	function renderForm($id, $inadmin, $module_template, $return_id = -1, $command = null)
	{

		include_once (CORE_DIR . 'bloxx_moduleform.php');
		
		$mf = new Bloxx_ModuleForm($this, $inadmin);
		return $mf->renderForm($id, $module_template, $return_id, $command);
	}

	function assignValuesFromPost($new)
	{

		$def = $this->tableDefinitionLangComplete();

		foreach ($def as $k => $v)
		{

			if ($v['TYPE'] == 'DATE')
			{

				if (isset ($_POST[$k.'__month']) && isset ($_POST[$k.'__day']) && isset ($_POST[$k.'__year']))
				{

					$this-> $k = mktime(0, 0, 0, $_POST[$k.'__month'], $_POST[$k.'__day'], $_POST[$k.'__year']);
				}
			}
			else
				if ($new && ($v['TYPE'] == 'CREATIONDATETIME'))
				{

					$this-> $k = time();
				}
				else
					if ($new && ($v['TYPE'] == 'CREATORID'))
					{

						include_module_once('identity');
						$ident = new Bloxx_Identity();
						$this-> $k = $ident->id();
					}
					else
						if ($v['TYPE'] == 'IMAGE')
						{							

							if ((isset ($_FILES[$k]['tmp_name'])) && (!isset ($this-> $k)))
							{

								if (isset ($v['IMG_WIDTH']) && !isset ($v['IMG_HEIGHT']))
								{

									include_once (CORE_DIR.'bloxx_image_utils.php');
									$this-> $k = scaleJpegWidth($_FILES[$k]['tmp_name'], $v['IMG_WIDTH']);
								}
								else
								{

									$this-> $k = fread(fopen($_FILES[$k]['tmp_name'], "r"), $_FILES[$k]['size']);
								}
							}
						}
						else
						{

							if (isset ($_POST[$k]))
							{

								$this-> $k = $_POST[$k];
							}
						}
		}
	}

	function create()
	{

		$this->assignValuesFromPost(true);

		$wf = -1;

		if (!$this->hasWorkflow())
		{

			$wf = 1;
		}
		else
		{

			$this->workflow = $wf;
			global $warningmessage;
			$warningmessage = LANG__WORKFLOW_SUBMIT;
		}

		return $this->newDataElement();
	}

	function update()
	{

		if (!$this->getRowByID($_POST['id']))
		{
			return false;
		}

		$this->assignValuesFromPost(false);

		return $this->updateRow(true);
	}

	function verifyTrust($trust, $id = -1)
	{

		include_module_once('role');

		$current_trust = $this->getTrust();

		if ($current_trust < $trust)
		{

			return false;
		}

		if ((!isset ($this->no_private) || (!$this->no_private)) && ($this->private_content > 0))
		{

			include_module_once('identity');
			$ident = new Bloxx_Identity();

			if (!$ident->belongsToGroup($this->private_content))
			{

				return false;
			}
		}

		if ($id > 0)
		{

			if (($current_trust < TRUST_MODERATOR) && ($this->hasWorkflow()))
			{

				$modclone = $this->modClone();
				$modclone->getRowByID($id);

				if ($modclone->workflow <= 0)
				{

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

		if ($iid != -1)
		{

			$role = new Bloxx_Role();
			$role->getRowByID($ident->role);
			$trust = $role->getModuleTrust($this->name);
		}

		return $trust;
	}

	function getCurrentPageID()
	{

		if (isset ($_GET['id']))
		{

			return $_GET['id'];
		}
		else
		{

			include_once (CORE_DIR . 'bloxx_config.php');
			$system = new Bloxx_Config();
			return $system->getMainPage();
		}
	}
	
	function getMainPageID()
	{

		include_once (CORE_DIR . 'bloxx_config.php');
		$system = new Bloxx_Config();
		return $system->getMainPage();		
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

		fwrite($handle, "[MODULE ".$this->name."]\n");

		while ($this->nextRow(false))
		{

			fwrite($handle, "[row]\n");

			$def = $this->tableDefinitionLangComplete();

			foreach ($def as $k => $v)
			{

				fwrite($handle, "[" . $k . "]");

				$value = $this->$k;

				//Binary types must be encoded to text format
				if($v['TYPE'] == 'IMAGE')
				{
					$value = base64_encode($value);
				}

				$value = str_replace('$', '$dolar', $value);
				$value = str_replace('[', '$open_bracket', $value);

				fwrite($handle, $value);
				fwrite($handle, "[_".$k."]\n");
			}

			fwrite($handle, "[_row]\n");

		}

		fwrite($handle, "[_".$this->name."]\n");

		fclose($handle);
	}

	function getConfig($item_name, $generic = false)
	{
		include_module_once('config');

		$config = new Bloxx_Config();
		
		$modid = $this->getModID();
		
		if ($generic)
		{
			$modid = $config->getModID();
		}
		
		return $config->getValue($modid, $item_name);
	}

	function tableDefinitionLangComplete()
	{
		$def = $this->tableDefinition();

		include_module_once('language');

		foreach ($def as $k => $v)
		{

			if (isset ($v['LANG']) && $v['LANG'])
			{

				$lang = new Bloxx_Language();
				$lang->clearWhereCondition();
				$lang->runSelect();

				while ($lang->nextRow())
				{

					$klang = $k.'_LANG_'.$lang->code;
					$v['LANG_CODE'] = $lang->code;
					$v['FIELD_NAME'] = $k;
					$ret[$klang] = $v;
				}
			}
			else
			{

				$v['FIELD_NAME'] = $k;
				$ret[$k] = $v;
			}
		}

		return $ret;
	}

	function renderLabel()
	{

		$label = $this->label_field;

		if (!isset ($this-> $label))
		{

			return null;
		}
		return $this-> $label;
	}

	function getRowIDFromEnd($count)
	{
		if(isset($this->order_field) && ($this->order_field != ''))
		{
			$order_field = $this->order_field;
		}
		else
		{
			$order_field = 'id';
		}
		
		$this->clearWhereCondition();
		$this->setOrderBy($order_field, true);
		$this->setLimit($count);
		$this->runSelect();

		$n = $count;

		if ((isset ($this->no_private)) && ($this->no_private))
		{

			while ($n > 0)
			{

				if (!$this->nextRow())
				{

					$n = 0;
				}

				$n --;
			}

			return $this->id;
		}
		else
		{
			include_module_once('identity');
			$ident = new Bloxx_Identity();

			$hasWorkflow = $this->hasWorkflow();

			while ($n > 0)
			{

				if (!$this->nextRow())
				{

					$n = 0;
				}

				if ((($this->private_content <= 0) || ($ident->belongsToGroup($this->private_content))) && ((!$hasWorkflow) || ($this->workflow > 0)))
				{

					$n --;
				}
			}

			return $this->id;
		}
	}

	function fieldLabel($field, $lang_code)
	{		
		
		if (($field == 'id') || ($field == 'workflow'))
		{

			$field_label = constant('F_LANG__'.strtoupper($field));
		}
		else
		{

			$field_label = constant('F_LANG_'.strtoupper($this->name).'_'.strtoupper($field));
		}

		if (isset ($lang_code))
		{

			include_module_once('language');
			$lang = new Bloxx_Language();
			$lang->insertWhereCondition('code', '=', $lang_code);
			$lang->runSelect();

			if ($lang->nextRow())
			{

				$field_label .= ' ('.$lang->language_name.')';
			}
		}

		return $field_label;
	}

	function enumLabel($enum, $enum_element, $lang_code)
	{

		$enum_label = constant('E_LANG_'.strtoupper($enum).'_'.strtoupper($enum_element));

		return $enum_label;
	}

	function renderRow($style_title, $style_text)
	{
		include_once (CORE_DIR.'bloxx_style.php');
		$style = new Bloxx_Style();

		$def = $this->tableDefinitionLangComplete();

		foreach ($def as $k => $v)
		{

			if (($v['TYPE'] != 'PASSWORD') && ($k != 'private_content') && ((!isset ($v['HIDDEN']) || (!$v['HIDDEN']))))
			{

				if ((isset ($v['CONFIDENTIAL']) && ($v['CONFIDENTIAL'])))
				{

					if (!$this->verifyTrust(TRUST_ADMINISTRATOR))
					{

						continue;
					}
				}

				$html_out = $style->renderStyleHeader($style_title);
				$lang_code = null;
				if (isset ($v['LANG_CODE']))
				{

					$lang_code = $v['LANG_CODE'];
				}
				$html_out .= $this->fieldLabel($v['FIELD_NAME'], $lang_code);
				$html_out .= $style->renderStyleFooter($style_title);
				$html_out .= '<br>';

				if ($v['TYPE'] == 'DATE')
				{

					$html_out .= $style->renderStyleHeader($style_text);
					$html_out .= getDateString($this-> $k);
					$html_out .= $style->renderStyleFooter($style_text);
				}
				else
					if (substr($v['TYPE'], 0, 6) == "BLOXX_")
					{

						$typemod = substr($v['TYPE'], 6);
						include_module_once($typemod);
						$typemod = 'bloxx_'.$typemod;
						$typeinst = new $typemod ();

						$typeinst->getRowByID($this-> $k);

						$labelf = $typeinst->label_field;

						$html_out .= $style->renderStyleHeader($style_text);
						$html_out .= $typeinst-> $labelf;
						$html_out .= $style->renderStyleFooter($style_text);
					}
					else
					{

						$html_out .= $style->renderStyleHeader($style_text);
						$html_out .= $this-> $k;
						$html_out .= $style->renderStyleFooter($style_text);
					}

				$html_out .= '<br><br>';
			}
		}

		//Private to group info
		include_module_once('identity');
		$ident = new Bloxx_Identity();

		if (((!isset ($this->no_private)) || (!$this->no_private)) && ($this->private_content > 0))
		{

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
		//HACK TO OPTIMIZE - disable for workflow to work
		return false;
		
		$wf = $this->getConfig('workflow');

		return ($wf > 0);
	}

	function modClone()
	{
		$mname = 'Bloxx_'.$this->name;
		$modclone = new $mname ();
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

		$html_out = '<img src="image.php?module='.$this->name.'&id='.$this->id.'&field='.$field.'" border="0"';

		if ($align != null)
		{

			$html_out .= ' align="'.$align.'" ';
		}

		$html_out .= '></img>';

		return $html_out;
	}

	function insertListConditions()
	{
		return;
	}

	function renderList($start_id, $columns, $rows, $mode, $html_after_row, $html_after_column)
	{

		$this->clearWhereCondition();
		$this->insertWhereCondition('id', '>=', $start_id);
		$this->insertListConditions();
		$this->setOrderBy('id');
		$this->runSelect();

		$html_out = '';

		$total = $columns * $rows;
		$counter = 0;

		while (($this->nextRow()) && ($counter < $total))
		{

			$clone = $this->modClone();
			$html_out .= $clone->render($mode, $this->id);

			$counter ++;

			//End of row
			if (($counter % $columns) == 0)
			{

				$html_out .= $html_after_row;
			}
			//End of column
			else
			{

				$html_out .= $html_after_column;
			}
		}

		//Balance table (render empty cells in last row)
		while (($counter % $columns) != 0)
		{

			$counter ++;
			//End of row
			if (($counter % $columns) == 0)
			{

				$html_out .= $html_after_row;
			}
			//End of column
			else
			{

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

		while (($this->nextRow()) && ($n <= $count))
		{

			$id = $this->id;
			$n ++;
		}

		if ($n <= $count)
		{

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

		while (($this->nextRow()) && ($n <= $count))
		{

			$id = $this->id;
			$n ++;
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

		if (($mode == 'configdata')
			|| ($mode == 'generic_delete')
			|| ($mode == 'generic_edit')
			|| ($mode == 'generic_create')
			|| ($mode == 'generic_delete_button')
			|| ($mode == 'generic_edit_button'))
		{

			return true;
		}

		return false;
	}

	function isGenericCommand($command)
	{

		if (($command == 'generic_create')
			|| ($command == 'generic_delete')
			|| ($command == 'generic_edit'))
		{

			return true;
		}

		return false;
	}

	function doGenericRender($mode, $id, $target, $mt)
	{

		if ($mode == 'configdata')
		{

			return $this->getConfig($id);
		}
		else if ($mode == 'generic_delete')
		{
			include_module_once('admin');
			
			$this->getRowByID($id);
			$label_field = $this->label_field;

			$html_out = LANG_ADMIN_WARNING1;
			if (isset($this->$label_field))
			{
                                
				$html_out .= $this->$label_field;
			}
			$html_out .= LANG_ADMIN_WARNING2;
			$html_out .= $this->name;
			$html_out .= '".';
			$html_out .= '<br><br>';
			$html_out .= LANG_ADMIN_WARNING3;
                        
			$mt->setItem('warning', $html_out);
                                
			include_once(CORE_DIR . 'bloxx_form.php');

			$form = new Bloxx_Form();
			$form->setView('module');
			$form->setParam($this->name);

			$html_out = $form->renderHeader($this->name, 'generic_delete');
			$html_out .= $form->renderInput('item', 'hidden', $_GET['delete_item_id']);
			$html_out .= $form->renderInput('target_module', 'hidden', $this->name);
			$html_out .= $form->renderSubmitButton(LANG_ADMIN_CONFIRM);
			$html_out .= $form->renderFooter();
			$mt->setItem('button', $html_out);
                        
			return $mt->renderView();
		}
		else if ($mode == 'generic_edit')
		{

			$html_out = $this->renderForm($id, false, $mt);            
			return $html_out;
		}
		else if ($mode == 'generic_create')
		{

			$html_out = $this->renderForm(-1, false, $mt);            
			return $html_out;
		}
		else if ($mode == 'generic_delete_button')
		{
			
			$generic_delete_page = $this->getConfig('generic_delete_page', true);
			
			$vars = array('module' => $this->name,
							'delete_item_id' => $id); 
			return build_link($generic_delete_page, 'generic_delete', $id, null, 'X', true, $vars);
            
            return $delete_item;
		}
		else if ($mode == 'generic_edit_button')
		{

			$generic_edit_page = $this->getConfig('generic_edit_page', true);
			$vars = array('module' => $this->name); 
			return build_link($generic_edit_page, 'generic_edit', $id, null, 'E', true, $vars);
		}
	}
	
	function execGenericCommand($command)
	{
		if ($command == 'generic_edit')
		{                
			$this->update();
        }
        else if ($command == 'generic_create')
        {
			$this->create();
        }
		else if ($command == 'generic_delete')
		{
						
            $this->deleteRowByID($_POST['item']);
		}
	}

	/**
	 * writeLog Logs a message.
	 *
	 * @param int    $priority Message priority (syslog constants).
	 * @param string $message  Message to log.
	 *
	 * @access public
	 */
	function writeLog($priority, $message)
	{
		include_module_once('logs');
		$logs = new Bloxx_Logs($this->getModID($this->name));
		$logs->writeLog($priority, $message);
	}

	function uninstall()
	{
		include_once(CORE_DIR . 'bloxx_role.php');
        $role = new Bloxx_Role();
        $role->unRegisterModule($this->name);
                
        include_module_once('module_manager');
        $mm = new Bloxx_ModuleManager();
        $mm->unRegister($this->name);

        $ret = $this->dropTable();
                
        if (PEAR::isError($ret))
        {

        	return $ret;
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
