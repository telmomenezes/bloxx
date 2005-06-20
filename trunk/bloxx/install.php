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
// $Id: install.php,v 1.9 2005-06-20 11:26:08 tmenezes Exp $

function bloxxInstall() 
{
	require_once('defines.php');
	require_once('functions.php');
	include_once(CORE_DIR.'bloxx_page.php');
	include_once(CORE_DIR.'bloxx_modulemanager.php');
	include_once(CORE_DIR.'bloxx_config.php');
	include_once(CORE_DIR.'bloxx_logs.php');
	include_once(CORE_DIR.'bloxx_session.php');
	include_once(CORE_DIR.'bloxx_identity.php');
	include_once(CORE_DIR.'bloxx_role.php');
	include_once(CORE_DIR.'bloxx_admin.php');
	include_once(CORE_DIR.'bloxx_style.php');
	include_once(CORE_DIR.'bloxx_stylelink.php');
	include_once(CORE_DIR.'bloxx_moduletemplate.php');
	include_once(CORE_DIR.'bloxx_resource.php');
	include_once(CORE_DIR.'bloxx_headerfooter.php');
	include_once(CORE_DIR.'bloxx_language.php');
	include_once(CORE_DIR.'bloxx_usergroup.php');
	include_once(CORE_DIR.'bloxx_grouplink.php');
	include_once(CORE_DIR.'bloxx_workflow.php');
	include_once(CORE_DIR.'bloxx_state.php');
	include_once(CORE_DIR.'bloxx_list.php');

	$module_manager = new Bloxx_ModuleManager();
	$module_manager->install(false);
	$system = new Bloxx_Config();
	$system->install(false);
	$language = new Bloxx_Language();
	$language->install(false);
	$language->afterInstall();
	$page = new Bloxx_Page();
	$page->install(false);
	$user = new Bloxx_Identity();
	$user->install(false);
	$admin = new Bloxx_Admin();
	$admin->install(false);
	$logs = new Bloxx_Logs();
	$logs->install(false);
	$session = new Bloxx_Session();
	$session->install(false);
	$style = new Bloxx_Style();
	$style->install(false);
	$stylelink = new Bloxx_StyleLink();
	$stylelink->install(false);
	$mt = new Bloxx_ModuleTemplate();
	$mt->install(false);
	$headerfooter = new Bloxx_HeaderFooter();
	$headerfooter->install(false);
	$resource = new Bloxx_Resource();
	$resource->install(false);
	$group = new Bloxx_UserGroup();
	$group->install(false);
	$grouplink = new Bloxx_GroupLink();
	$grouplink->install(false);
	$wf = new Bloxx_Workflow();
	$wf->install(false);
	$state = new Bloxx_State();
	$state->install(false);
	$list = new Bloxx_List();
	$list->install(false);

	$module_manager->afterInstall();
	$system->afterInstall();
	$page->afterInstall();
	$user->afterInstall();
	$admin->afterInstall();
	$logs->afterInstall();
	$session->afterInstall();
	$style->afterInstall();
	$stylelink->afterInstall();
	$mt->afterInstall();
	$headerfooter->afterInstall();
	$resource->afterInstall();
	$group->afterInstall();
	$grouplink->afterInstall();
	$wf->afterInstall();
	$state->afterInstall();
	$list->afterInsta