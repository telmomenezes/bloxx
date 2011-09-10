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
// $Id: enum_photonews_photopos.php,v 1.2 2005-02-18 17:34:56 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_enum.php');

global $ENUM_PHOTONEWS_PHOTOPOS;
$ENUM_PHOTONEWS_PHOTOPOS = new Bloxx_Enum();

$ENUM_PHOTONEWS_PHOTOPOS->addElement(0, 'TOP_LEFT');
$ENUM_PHOTONEWS_PHOTOPOS->addElement(1, 'TOP_RIGHT');
$ENUM_PHOTONEWS_PHOTOPOS->addElement(2, 'BOTTOM_LEFT');
$ENUM_PHOTONEWS_PHOTOPOS->addElement(3, 'BOTTOM_RIGHT');

?>