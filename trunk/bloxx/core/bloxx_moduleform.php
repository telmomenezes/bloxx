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
// $Id: bloxx_moduleform.php,v 1.1 2005-06-20 11:26:08 tmenezes Exp $

require_once 'defines.php';

class Bloxx_ModuleForm
{
	
	var $form;
	var $inadmin;
	var $owner_module;
	var $id;
	
	function Bloxx_ModuleForm($owner_module, $inadmin)
	{
		$this->inadmin = $inadmin;
		$this->owner_module = $owner_module;
	}

	function renderForm($id, $module_template, $return_id = -1, $command = null)
	{
		$this->id = $id;

		include_module_once('admin');
		include_module_once('moduletemplate');

		$admin = new Bloxx_Admin();

		include_once (CORE_DIR . 'bloxx_form.php');

		$this->form = new Bloxx_Form();

		if ($this->inadmin)
		{

			$this->form->setMode('module');
			$this->form->setParam($this->owner_module->name);
		}
		else
		{

			$this->form->setFromGlobals();
		}

		$header = '';

		if (isset($command) && ($command != null))
		{
			$header .= $this->form->renderHeader($this->owner_module->name, $command, $return_id);
		}
		else if ($id >= 0)
		{

			$header .= $this->form->renderHeader($this->owner_module->name, 'generic_edit', $return_id);
		}
		else
		{

			$header .= $this->form->renderHeader($this->owner_module->name, 'generic_create', $return_id);
		}

		if ($id >= 0)
		{

			$this->owner_module->getRowByID($id, true);

			$header .= $this->form->renderInput('id', 'hidden', $id);
		}

		$header .= $this->form->renderInput('target_module', 
												'hidden', 
												$this->owner_module->name);

		$module_template->setItem('header', $header);
		$module_template->startLoop('form');

		$def_in = $this->owner_module->tableDefinitionLangComplete();
		
		foreach($def_in as $k => $v)
		{
			$def[$k] = $v;
			
			if($v['TYPE'] == 'PASSWORD')
			{
				$v2 = $v;
				$v2['TYPE'] = 'PASSWORD2';
				$v2['FIELD_NAME'] .= '_again'; 
				$def[$k . '_again'] = $v2;
			}
		}

		$footer = '';

		foreach ($def as $k => $v)
		{

			$module_template->nextLoopIteration();
			$hidden = false;

			if ((!$this->inadmin) 
				&& ((!isset ($v['USER'])) 
				|| (!$v['USER'])))
			{

				$hidden = true;
			}
			else
			{
				if (($k != 'private_content') &&
					(!((($v['TYPE'] == 'PASSWORD') 
					|| ($v['TYPE'] == 'PASSWORD2')) 
					&& ($this->id >= 0))))
				{

					$lang_code = null;
					if (isset ($v['LANG_CODE']))
					{

						$lang_code = $v['LANG_CODE'];
					}

					$field_name = $v['FIELD_NAME'];
					
					if($v['TYPE'] == 'PASSWORD2')
					{
						$field_name = substr($field_name, 0, -6);
					}
					
					$field_label = $this->owner_module->fieldLabel($field_name, $lang_code);
					
					if($v['TYPE'] == 'PASSWORD2')
					{
						$field_label .= ' (again)';
					}

					$module_template->setLoopItem('label', $field_label);
				}
			}

			$length = 0;

			if ((($id >= 0) || (!$this->inadmin)) && (isset ($this->owner_module->$k)))
			{

				$value = $this->owner_module->$k;
			}
			else
			{

				$value = '';
			}
			
			$field = '';

			//Render field acording to type
			if ($k == 'private_content')
			{
				//Don't show this field.
			}
			elseif ($v['TYPE'] == 'TEXT')
			{
				$field = $this->renderTextField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'HTML')
			{
				$field = $this->renderHTMLField($k, $value, $v, $hidden);				
			}
			elseif (substr($v['TYPE'], 0, 6) == "BLOXX_")
			{
				$field = $this->renderBloxxField($k, $value, $v, $hidden);
			}
			elseif (substr($v['TYPE'], 0, 5) == "ENUM_")
			{
				$field = $this->renderEnumField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'PASSWORD')
			{
				$field = $this->renderPasswordField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'PASSWORD2')
			{
				$field = $this->renderPassword2Field($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'FILE')
			{
				$field = $this->renderFileField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'IMAGE')
			{
				$field = $this->renderImageField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'DATE')
			{
				$field = $this->renderDateField($k, $value, $v, $hidden);
			}
			elseif ($v['TYPE'] == 'NUMBER')
			{
				$field = $this->renderNumberField($k, $value, $v, $hidden);
			}
			else
			{
				$field = $this->renderDefaultField($k, $value, $v, $hidden);
			}
			
			if ($field != '')
			{
				if ($hidden)
				{
					$footer .= $field;
				}
				else
				{
					$module_template->setLoopItem('field', $field);
				}
			}
		}

		//Render group selector here...
		include_module_once('identity');
		$ident = new Bloxx_Identity();

		if (((!isset ($this->owner_module->no_private)) 
			|| (!$this->owner_module->no_private)) 
			&& (($ident->belongsToGroups()) 
			|| $this->inadmin))
		{

			$glist = $ident->groups();

			include_module_once('usergroup');

			$module_template->nextLoopIteration();

			$module_template->setLoopItem('label', LANG_USERGROUP_PRIVATE_TO_GROUP);

			$select_input = $this->form->startSelect('private_content', 1);

			if ($this->owner_module->private_content == 0)
			{

				$selected = true;
			}
			else
			{

				$selected = false;
			}

			$select_input .= $this->form->addSelectItem(0, LANG_USERGROUP_NO, $selected);

			if (isset ($glist))
			{

				foreach ($glist as $v)
				{

					$grp = new Bloxx_UserGroup();
					$grp->getRowByID($v);

					if ($this->owner_module->private_content == $v)
					{

						$selected = true;
					}
					else
					{

						$selected = false;
					}

					$select_input .= $this->form->addSelectItem($v, $grp->groupname, $selected);
				}
			}

			$select_input .= $this->form->endSelect();

			$module_template->setLoopItem('field', $select_input);
		}

		if ($id >= 0)
		{

			$module_template->setItem('button', 
				$this->form->renderSubmitButton(LANG_ADMIN_APPLY_CHANGES));
		}
		else
		{

			$text = LANG_ADMIN_CREATE;
			$module_template->setItem('button', $this->form->renderSubmitButton($text));
		}

		$footer .= $this->form->renderFooter();
		$module_template->setItem('footer', $footer);

		return $module_template->renderView();
	}
	
	function renderTextField($name, $value, $def, $hidden)
	{
		if ($this->inadmin)
		{

			return $this->form->renderTextArea($name, 30, 80, $value);
		}
		else
		{

			return $this->form->renderTextArea($name, 30, 70, $value);
		}
	}
	
	function renderHTMLField($name, $value, $def, $hidden)
	{
		if ($this->inadmin)
		{

			return $this->form->renderTextArea($name, 30, 80, $value);
		}
		else
		{

			return $this->form->renderTextArea($name, 30, 70, $value);
		}
	}
	
	function renderBloxxField($name, $value, $def, $hidden)
	{
		if($hidden)
		{
			return '';
		}
		
		$select_field = $this->form->startSelect($name, 1);

		$typemod = substr($def['TYPE'], 6);
		include_module_once($typemod);
		$typemod = 'bloxx_' . $typemod;
		$typeinst = new $typemod();

		$typeinst->clearWhereCondition();
		$typeinst->runSelect();

		while ($typeinst->nextRow())
		{

			$labelf = $typeinst->label_field;

			if ($typeinst->id == $value)
			{

				$selected = true;
			}
			else
			{

				$selected = false;
			}

			$select_field .= $this->form->addSelectItem($typeinst->id, $typeinst-> $labelf, $selected);
		}

		$select_field .= $this->form->endSelect();
		
		return $select_field;
	}

	function renderEnumField($name, $value, $def, $hidden)
	{
		if($hidden)
		{
			return '';
		}
		
		$select_field = $this->form->startSelect($name, 1);

		$enum_name = substr($def['TYPE'], 5);
		$enum_var = 'ENUM_' . $enum_name;
		include_enum_once($enum_name);

		global $$enum_var;
		$enum = $$enum_var->getEnum();

		foreach ($enum as $k => $v)
		{

			if ($k == $value)
			{

				$selected = true;
			}
			else
			{

				$selected = false;
			}

			$langname = $this->owner_module->enumLabel($enum_name, $v, $lang_code);

			$select_field .= $this->form->addSelectItem($k, $langname, $selected);
		}

		$select_field .= $this->form->endSelect();

		return $select_field;
	}
	
	function renderPasswordField($name, $value, $def, $hidden)
	{			
		if ($this->id >= 0)
		{
			return '';
		}
			
		return $this->form->renderInput($name, 'password', $value);			
	}
	
	function renderPassword2Field($name, $value, $def, $hidden)
	{			
		if ($this->id >= 0)
		{
			return '';
		}
			
		return $this->form->renderInput($name, 'password', $value);			
	}
	
	function renderFileField($name, $value, $def, $hidden)
	{
		
		$file_input = $this->form->renderInput('MAX_FILE_SIZE', 'hidden', 300000, null);
		$file_input .= $this->form->renderInput($name, 'file', $value);
		return $file_input;
	}
	
	function renderImageField($name, $value, $def, $hidden)
	{
		if($hidden)
		{
			return '';
		}
		
		$image_input = '<img width="100" src="image.php?module='
			. $this->owner_module->name
			. '&id='.$this->owner_module->id
			. '&field='
			.$name
			.'"></img>';
		$image_input .= '<br>';
		$image_input .= $this->form->renderInput('MAX_FILE_SIZE', 'hidden', 9999999, null);
		$image_input .= $this->form->renderInput($name, 'file', '');
		return $image_input;
	}
	
	function renderDateField($name, $value, $def, $hidden)
	{
		$date = getDate($value);

		$date_input = $this->form->renderInput($name . '__day', 'input', $date['mday'], 2, 2);
		$date_input .= $this->form->renderMonthSelector($name . '__month', $date['mon']);
		$date_input .= $this->form->renderInput($name . '__year', 'input', $date['year'], 4, 4);
		
		return $date_input;
	}
	
	function renderNumberField($name, $value, $def, $hidden)
	{
		if($hidden)
		{
			$field_type = 'hidden';
		}
		else
		{
			$field_type = 'input';
		}
		
		return $this->form->renderInput($name, $field_type, $value, 10, 15);
	}
	
	function renderDefaultField($name, $value, $def, $hidden)
	{
		
		$maxsize = $def['SIZE'];

		if ($maxsize <= 0)
		{

			$maxsize = 255;
		}

		$size = $maxsize;

		if ($maxsize > 80)
		{

			$size = 80;
		}
		
		if($hidden)
		{
			$field_type = 'hidden';
		}
		else
		{
			$field_type = 'input';
		}
		
		return $this->form->renderInput($name, $field_type, $value, $size, $maxsize);
	}
}
?>
