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
// $Id: bloxx_error.php,v 1.1 2005-08-08 16:38:33 tmenezes Exp $

define('ERROR_REPORT_MODE_SILENT', 0);
define('ERROR_REPORT_MODE_PRINT', 1);
define('ERROR_REPORT_MODE_PRINT_SENSITIVE', 2);

define('WARNING_REPORT_MODE_SILENT', 0);
define('WARNING_REPORT_MODE_PRINT', 1);
define('WARNING_REPORT_MODE_PRINT_SENSITIVE', 2);

define('WARNING_DEBUG_MODE_SILENT', 0);
define('WARNING_DEBUG_MODE_PRINT', 1);
define('WARNING_DEBUG_MODE_PRINT_SENSITIVE', 2);

class Bloxx_Error
{
	function Bloxx_Error($error_report_mode, $warning_report_mode, $debug_report_mode)
	{
		$this->error_report_mode = $error_report_mode; 
		$this->warning_report_mode = $warning_report_mode;
		$this->debug_report_mode = $debug_report_mode; 
	}
	
	function error($module_name, $message, $sensitive = null)
	{
		if ($this->error_report_mode == ERROR_REPORT_MODE_PRINT)
		{
			echo '<br />BLOXX ERROR '
					. '(' 
					. $module_name 
					. '): ' 
					. $message
					. '<br />'; 
		}
		else if ($this->error_report_mode == ERROR_REPORT_MODE_PRINT_SENSITIVE)
		{
			echo '<br />BLOXX ERROR '
					. '(' 
					. $module_name 
					. '): ' 
					. $message
					. ' '
					. $sensitive 
					. '<br />'; 
		}
		
		die();
	}
	
	function warning($module_name, $message, $sensitive = null)
	{
		if ($this->warning_report_mode == WARNING_REPORT_MODE_PRINT)
		{
			echo '<br />BLOXX WARNING '
					. '('
					. $module_name
					. '): '
					. $message
					. '<br />'; 
		}
		else if ($this->warning_report_mode == WARNING_REPORT_MODE_PRINT_SENSITIVE)
		{
			echo '<br />BLOXX WARNING '
					. '('
					. $module_name
					. '): '
					. $message
					. ''
					. $sensitive
					. '<br />'; 
		}
	}
	
	function debug($module_name, $message, $sensitive = null)
	{
		if ($this->debug_report_mode == DEBUG_REPORT_MODE_PRINT)
		{
			echo '<br />BLOXX DEBUG '
					. '('
					. $module_name
					. '): '
					. $message
					. '<br />'; 
		}
		if ($this->debug_report_mode == DEBUG_REPORT_MODE_PRINT_SENSITIVE)
		{
			echo '<br />BLOXX DEBUG '
					. '('
					. $module_name
					. '): '
					. $message
					. ' '
					. $sensitive
					. '<br />'; 
		}
	}
}
?>
