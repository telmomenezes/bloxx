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
// Authors: Silas Francisco <draft@dog.kicks-ass.net>
//
// $Id: bloxx_logs.php,v 1.5 2005-02-26 00:36:18 secretdraft Exp $

require_once(CORE_DIR . 'bloxx_module.php');

/**
 * Bloxx_Logs Implements log system.
 *
 * @package   Bloxx_Core
 * @version   $Id: bloxx_logs.php,v 1.5 2005-02-26 00:36:18 secretdraft Exp $
 * @category  Core
 * @copyright Copyright &copy; 2002-2005 The Bloxx Team
 * @license   The GNU General Public License, Version 2
 * @author    Silas Francisco <draft@dog.kicks-ass.net>
 */
class Bloxx_Logs extends Bloxx_Module
{
		function Bloxx_Logs($ownerModuleId = '-1')
		{
			$this->name = 'logs';
			$this->module_version = 1;
			$this->use_init_file = false;
			$this->no_private = true;
			
			$this->_ownerModuleId = $ownerModuleId;
			
			$this->Bloxx_Module();	
		}
	
        function getTableDefinition() 
        {        
                return array(
                		'ownerModuleId' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'addr'          => array('TYPE' => 'STRING', 'SIZE' => 15, 'NOTNULL' => true),
                        'timestamp'     => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'priority'      => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'message'       => array('TYPE' => 'STRING', 'SIZE' => 256, 'NOTNULL' => true));
        }
        
        function getRenderTrusts()
        {
                return array(
                        'logs' => TRUST_ADMINISTRATOR
                );
        }

		function doRender($mode, $id, $target, $mt)
		{
			include_once(CORE_DIR . 'bloxx_form.php');
			
			if ($mode == 'logs')
			{	
				$form = new Bloxx_Form();
				
				$html_out = $form->renderHeader($this->name, 'view');
				$html_out .= $form->startSelect('priority', '1');
				$html_out .= $form->addSelectItem(LOG_CRIT, $this->getPriorityName(LOG_CRIT));
				$html_out .= $form->addSelectItem(LOG_ERR, $this->getPriorityName(LOG_ERR));
				$html_out .= $form->addSelectItem(LOG_WARNING, $this->getPriorityName(LOG_WARNING));
				$html_out .= $form->addSelectItem(LOG_NOTICE, $this->getPriorityName(LOG_NOTICE));
				$html_out .= $form->endSelect();
				$html_out .= $form->renderSubmitButton('Show');	
				$html_out .= $form->renderFooter();
				$mt->setItem('loglevel', $html_out);
											
				if (isset($_POST['priority']))
				{
					$this->clearWhereCondition();
					$this->insertWhereCondition('priority', '<=', $_POST['priority']);
					$this->runSelect();
					
					$html_out = '';
					
					include_module_once('manager');
					$mm = new Bloxx_ModuleManager();
					
					while ($this->nextRow())
					{
						$html_out .= '[' . $this->addr . '] ' . '[' . $mm->getModuleName($this->ownerModuleId) . '] '
							. ' [' . date('r', $this->timestamp) . '] ' 
							. '[' . $this->getPriorityName($this->priority) . '] '
							. $this->message . "<br>";
					}
					
					$mt->setItem('logs', $html_out);
				}				
				
				return $mt->renderView();
			}
			
		}
        		
		/**
		 * writeLog Logs a message.
		 *
		 * @param int    $priority Message priority (LOG_CRIT LOG_ERR LOG_WARNING LOG_NOTICE).
		 * @param string $message  Message to log.
		 *
		 * @access public
		 */
		function writeLog($priority, $message)
		{
			if ($priority <= $this->getConfig('logLevel'))
			{
				$this->ownerModuleId = $this->_ownerModuleId;	
				$this->addr      = $_SERVER['REMOTE_ADDR'];
				$this->timestamp = time();
				$this->priority  = $priority;
				$this->message   = $message;
			
				$this->insertRow();
			}
		}
		
		/**
		 * getPriorityName Translates priority number to priority name.
		 * 
		 * @param int $priority Priority number.
		 * 
		 * @return string Priority name.
		 * 
		 * @access public
		 */
		function getPriorityName($priority)
		{
			switch ($priority)
			{
				case LOG_CRIT: 	  return 'critical';
				case LOG_ERR:     return 'error';
				case LOG_WARNING: return 'warning';
				case LOG_NOTICE:  return 'notice';
				default: return '';
			}
		}
}
?>
