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

global $HTTP_GET_VARS;

$mod = $HTTP_GET_VARS['module'];
$id = $HTTP_GET_VARS['id'];
$field = $HTTP_GET_VARS['field'];

include_module_once($mod);
$mod = "Bloxx_" . $mod;
$mod_inst = new $mod();

$mod_inst->clearWhereCondition();
$mod_inst->insertWhereCondition("id=" . $id);
$mod_inst->runSelect();

if($mod_inst->nextRow()){

        $data = $mod_inst->$field;

        Header("Content-type: image/jpeg");
        echo $data;
}
?>
