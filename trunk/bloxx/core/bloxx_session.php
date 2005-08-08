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
// $Id: bloxx_session.php,v 1.12 2005-08-08 16:38:35 tmenezes Exp $

require_once(CORE_DIR . 'bloxx_module.php');

/**
 * Bloxx_Session Handles everything about user sessions.
 *
 * @package   Bloxx_Core
 * @version   $Id: bloxx_session.php,v 1.12 2005-08-08 16:38:35 tmenezes Exp $
 * @category  Core
 * @copyright Copyright &copy; 2002-2005 The Bloxx Team
 * @license   The GNU General Public License, Version 2
 * @author    Silas Francisco <draft@dog.kicks-ass.net>
 */
class Bloxx_Session extends Bloxx_Module 
{        
        function Bloxx_Session() 
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'session';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['use_init_file'] = false;
                $this->_BLOXX_MOD_PARAM['no_private'] = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition() 
        {        
                return array(
                        'login' => array('TYPE' => 'STRING', 'SIZE' => 10, 'NOTNULL' => true),
                        'session' => array('TYPE' => 'STRING', 'SIZE' => 32, 'NOTNULL' => true),
                        'timelimit' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'addr' => array('TYPE' => 'STRING', 'SIZE' => 15, 'NOTNULL' => true));
        }

		/**
		 * createSession Creates a session by making database entries and creating user cookies.
		 *
		 * @param string $login Login of an already authenticated user.
		 *
		 * @uses $_COOKIE['login'] User login.
		 * @uses $_COOKIE['session'] Session hash.
		 * 
		 * @access public
		 */
        function createSession($login) 
        {       
                $this->cleanOldSessions($login);
                
                $this->login = $login;
                $this->session = md5(uniqid(mt_rand(), true));
                
                $sessionTime = $this->getConfig('sessionTime');
                if ($sessionTime == null) 
                {                
                	$this->timelimit = time() + 2592000;
                }
                else 
                {                	
                	$this->timelimit = time() + ($sessionTime * 60);
                }
                
                $this->addr = $_SERVER['REMOTE_ADDR'];
                
                if ($this->insertRow()) 
                {                
                        $this->createCookie('login', $this->login, $this->timelimit);
                        $this->createCookie('session', $this->session, $this->timelimit);
                }

                $this->writeLog(LOG_NOTICE, $login . ' has logged in.');
        }
        
		/**
		 * removeSession Removes session by cleaning database entries and user cookies.
		 *
		 * @uses $_COOKIE['login'] Login of the session to remove.
		 * @uses $_COOKIE['session'] Session hash of the session to remove.
		 * 
		 * @access public
		 */        
        function removeSession() 
        {                
                if (isset($_COOKIE['login']) && isset($_COOKIE['session'])) 
                {                                        
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('login', '=', $_COOKIE['login']);
                        $this->insertWhereCondition('session', '=', $_COOKIE['session']);
                        $this->runSelect();
                
                        if ($this->nextRow()) 
                        {                        
                                $this->deleteRowByID($this->id);

                				$this->writeLog(LOG_NOTICE, $_COOKIE['login'] . ' has logged out.');
                        }
                		
                        $this->removeCookie('login');
                        $this->removeCookie('session');
                }
        }
        
		/**
		 * exists Informs if a given session exists and is active.
		 * 
		 * @uses $_COOKIE['login'] User login.
		 * @uses $_COOKIE['session'] Session hash.
		 * 
		 * @return bool false if session doesnt exist, expired or invalid and true otherwise.
		 * 
		 * @access public
		 */        
        function exists() 
        {
        	        
                if (isset($_COOKIE['login']) && isset($_COOKIE['session'])) 
                {                        
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('login', '=', $_COOKIE['login']);
                        $this->insertWhereCondition('session', '=', $_COOKIE['session']);
                        $this->runSelect();

                        if ($this->nextRow()) 
                        {
                        	
                                if ($this->addr != $_SERVER['REMOTE_ADDR']) 
                                {                                        
                                        $this->writeLog(LOG_CRIT, 'Hijack attempt to' 
                                        	. $_COOKIE['login'] . ' session.');
                                        		
                                        // blacklistIP();
                                        return false;                                        
                                } 
                                else
                                {                                        
                                        if (time() < $this->timelimit) 
                                        {
                                                return true;                                                
                                        } 
                                        else 
                                        {                                                
                                                $this->removeSession();
                                                return false;
                                        }
                                }                                
                        } 
                        else 
                        {                              
                                $this->writeLog(LOG_WARNING,'Identify attempt as '
                                	. $_COOKIE['login'] . ' but session doesnt exist.');
                                	
                                
                                $this->removeCookie('login');
                                $this->removeCookie('session');
                                        
                                return false;
                        }
                }
                
                return false;        
        }

		/**
		 * cleanOldSessions Cleans all expired sessions of a given login.
		 *
		 * @param string $login User login.
		 *
		 * @access public
		 */        
        function cleanOldSessions($login)
        {
        	$this->clearWhereCondition();
        	$this->insertWhereCondition('login', '=', $login);
        	$this->runSelect();
        	
        	while ($this->nextRow()) 
        	{
        		if (time() > $this->timelimit) 
        		{        			
        			$this->deleteRowByID($this->id);
        		}
        	}
        }

		/**
		 * createCookie Sets a cookie and loads it.
		 *
		 * @param string $name 		Cookie name.
		 * @param string $value 	Cookie value.
		 * @param long	 $timelimit	Cookie expire time.
		 *
		 * @access private
		 */                
        function createCookie($name, $value, $timelimit)
        {
    		setcookie($name, $value, $timelimit, '/', '', 0);
			$_COOKIE[$name] = $value;
        }
        
		/**
		 * removeCookie Unsets a cookie and unloads it.
		 *
		 * @param string $name Cookie name.
		 *
		 * @access private
		 */
		 function removeCookie($name)
		 {
		 	setcookie($name, '', 0, '/', '', 0);
		 	unset ($_COOKIE[$name]);
		 }
		 
		/**
		 * getLogin Returns login name.
		 *
		 * @return string Login name if session exists, null otherwise.
		 *
		 * @access public
		 */
		function getLogin()
		{
			if ($this->exists())
			{
				return $_COOKIE['login'];
			}
			else
			{
				return null;
			}
		}
}
?>
