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
// $Id: index.php,v 1.4 2005-02-18 17:34:55 tmenezes Exp $

require_once('defines.php');
require_once('functions.php');
require_once(CORE_DIR.'bloxx_page.php');
require_once(CORE_DIR.'bloxx_identity.php');

global $warningmessage;
unset($warningmessage);

$page = new Bloxx_Page();

global $_POST;
global $_GET;

//Hack para remover o irritante comportamento das magic quotes
//Será a melhor maneira?
//Precisa de ser aplicado também a $_GET?
if(get_magic_quotes_gpc()){

        $_POST = array_map("stripslashes", $_POST);
}
        
if(isset($_POST['module']) && $_POST['module'] != ''){

        $modname = 'bloxx_'.$_POST['module'];
        
        include_module_once($_POST['module']);

        $mod = new $modname();
        $mod->processForm($_POST['command']);
}

$id = 1;

if(isset($_GET['id'])){

        $id = $_GET['id'];
}
else{

        include_module_once('config');
        $config = new Bloxx_Config();
        $id = $config->getMainPage();
}


$html_out = $page->render('normal', $id);
echo $html_out;
?>
