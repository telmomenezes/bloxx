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

require_once('defines.php');
require_once('functions.php');
require_once(CORE_DIR.'bloxx_page.php');
require_once(CORE_DIR.'bloxx_identity.php');

global $warningmessage;
unset($warningmessage);

$page = new Bloxx_Page();

global $HTTP_POST_VARS;
global $HTTP_GET_VARS;

//Hack para remover o irritante comportamento das magic quotes
//Será a melhor maneira?
//Precisa de ser aplicado também a $HTTP_GET_VARS?
if(get_magic_quotes_gpc()){

        $HTTP_POST_VARS = array_map("stripslashes", $HTTP_POST_VARS);
}
        
if(isset($HTTP_POST_VARS['module']) && $HTTP_POST_VARS['module'] != ''){

        $modname = 'bloxx_'.$HTTP_POST_VARS['module'];
        
        include_module_once($HTTP_POST_VARS['module']);

        $mod = new $modname();
        $mod->processForm($HTTP_POST_VARS['command']);
}

$id = 1;

if(isset($HTTP_GET_VARS['id'])){

        $id = $HTTP_GET_VARS['id'];
}
else{

        include_module_once('config');
        $config = new Bloxx_Config();
        $id = $config->getMainPage();
}


$html_out = $page->render('normal', $id);
echo $html_out;
?>
