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

$dh = opendir(MODS_DIR);

while (($file = readdir($dh)) !== false) {

        if(is_file(MODS_DIR . $file) && ($file != '.') && ($file != '..')){

                include_once(MODS_DIR . $file);

                $mod_name = substr($file, 0, -4);
                $mod = new $mod_name();
                $mod->install();
        }
}

closedir($dh);

$dh = opendir(MODS_DIR);

while (($file = readdir($dh)) !== false) {

        if(is_file($file) && ($file != '.') && ($file != '..')){
        
                $mod_name = substr($file, 0, -4);
                $mod = new $mod_name();
                $mod->afterInstall();
        }
}

closedir($dh);
?>
