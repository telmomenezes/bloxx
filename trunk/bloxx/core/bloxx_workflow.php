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
// $Id: bloxx_workflow.php,v 1.7 2005-06-22 20:05:34 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Workflow extends Bloxx_Module
{
        function Bloxx_Workflow()
        {
                $this->name = 'workflow';
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
                        'submissions_list' => TRUST_MODERATOR,
                        'workflow' => TRUST_MODERATOR,
                        'accept_edit_reject' => TRUST_MODERATOR
                );
        }

        function getLocalCommandTrusts()
        {
                return array(
                        'accept_reject' => TRUST_MODERATOR
                );
        }
        
//  Render methods .............................................................

	function doRenderSubmissions_List($param, $target, $jump, $other_params, $mt)
	{

		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
                        
		$mm->clearWhereCondition();
		$mm->runSelect();
                        
		$html_out = '';
                        
		while ($mm->nextRow())
		{

			$mod = $mm->getModuleInstance();
                                
			if ($mod->hasWorkflow())
			{
                                        
				$count = $mod->submissionCount();

				$workflow_page = $this->getConfig('workflow_page');
                                        
				if ($count == 1)
				{

					$link_text = $mm->module_name . '  (' .  $count . ' ' . LANG_WORKFLOW_ONE_NEW . ')';
					$html_out = $style->renderStyleHeader($style_link);
					$html_out .= build_link($workflow_page, 'workflow', $mm->id, 0, $link_text, false);
					$html_out .= $style->renderStyleFooter($style_link);
					$html_out .= '<br />';
				}
				if ($count > 1)
				{

					$link_text = $mm->module_name . '  (' .  $count . ' ' . LANG_WORKFLOW_NEW . ')';
					$html_out .= $style->renderStyleHeader($style_link);
					$html_out .= build_link($workflow_page, 'workflow', $mm->id, 0, $link_text, false);
					$html_out .= $style->renderStyleFooter($style_link);
					$html_out .= '<br />';
				}

			}
		}
		
		return $html_out;
	}
	
	function doRenderWorkflow($param, $target, $jump, $other_params, $mt)
	{
                        
		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
		$mm->getRowByID($param);
		$mod = $mm->getModuleInstance();
                
		$mod->clearWhereCondition();
		$mod->insertWhereCondition('workflow', '<=', '0');
		$mod->runSelect();
                        
		while ($mod->nextRow())
		{

			$workflow_page = $this->getConfig('workflow_page');
                        
			$link_text = $mod->renderLabel();
			$html_out .= $style->renderStyleHeader($style_link);
			$html_out .= build_link($workflow_page, 'accept_edit_reject', $id, $mod->id, $link_text, false);
			$html_out .= $style->renderStyleFooter($style_link);
			$html_out .= '<br />';
		}
                        
		return $html_out;
	}
	
	function doRenderAccept_Edit_Reject($param, $target, $jump, $other_params, $mt)
	{
                
		include_module_once('modulemanager');
		$mm = new Bloxx_ModuleManager();
		$mm->getRowByID($param);
		$mod = $mm->getModuleInstance();

		$html_out .= $mod->render($mod->default_mode, $target);

		include_once(CORE_DIR.'bloxx_form.php');
		$form = new Bloxx_Form();
		$form->setView('workflow');
		$form->setParam($param);

		$html_out .= $form->renderHeader('workflow', 'accept_reject');
		$html_out .= $form->renderInput('moduleid', 'hidden', $param, '', 0, 0);
		$html_out .= $form->renderInput('itemid', 'hidden', $target, '', 0, 0);
		$html_out .= $form->renderSubmitButton(LANG_WORKFLOW_ACCEPT, $style_button);
		$html_out .= $form->renderSubmitButton(LANG_WORKFLOW_REJECT, $style_button);
		$html_out .= $form->renderFooter();
                        
		return $html_out;
	}
	
//  Command methods ............................................................
	function execCommandAccept_Reject()
	{

		if ($_POST['submit'] == LANG_WORKFLOW_ACCEPT)
		{
                        
			include_module_once('modulemanager');
			$mm = new Bloxx_ModuleManager();
			$mm->getRowByID($_POST['moduleid']);
			$mod = $mm->getModuleInstance();
                                
			$mod->getRowByID($_POST['itemid']);
			$mod->workflow = 1;
			$mod->updateRow(true);
		}
		else if ($_POST['submit'] == LANG_WORKFLOW_REJECT)
		{
                        
			include_module_once('modulemanager');
			$mm = new Bloxx_ModuleManager();
			$mm->getRowByID($_POST['moduleid']);
			$mod = $mm->getModuleInstance();

			$mod->deleteRowByID($_POST['itemid']);
		}
	}
}
