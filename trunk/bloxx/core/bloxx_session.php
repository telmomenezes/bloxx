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
// Authors: Silas Francisco <draft@dog.kicks-ass.com>
//
// $Id: bloxx_session.php,v 1.5 2005-02-18 17:34:56 tmenezes Exp $

require_once(CORE_DIR . 'bloxx_module.php');

class Bloxx_Session extends Bloxx_Module {
        
        function Bloxx_Session() {
                
                $this->name = 'session';
                $this->module_version = 1;
                $this->use_init_file = false;
                $this->no_private = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition() {
                
                return array(
                        'login' => array('TYPE' => 'STRING', 'SIZE' => 10, 'NOTNULL' => true),
                        'session' => array('TYPE' => 'STRING', 'SIZE' => 32, 'NOTNULL' => true),
                        'timelimit' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'addr' => array('TYPE' => 'STRING', 'SIZE' => 15, 'NOTNULL' => true));
        }

        function createSession($login) {
        
                global $_COOKIE;
                global $_SERVER;
                
                $this->login = $login;
                $this->session = md5(uniqid(mt_rand(), true));
                $this->timelimit = time() + 2592000; // Timelimit restriction goes here
                $this->addr = $_SERVER["REMOTE_ADDR"];
                
                if($this->insertRow()) {
                
                        setcookie('login', $this->login, $this->timelimit, '/', '', 0);
                        setcookie('session', $this->session, $this->timelimit, '/', '', 0);
                
                        $_COOKIE["login"] = $this->login;
                        $_COOKIE["session"] = $this->session;
                }
        }
        
        function removeSession() {
                
                global $_COOKIE;
                
                if(isset($_COOKIE["login"]) && isset($_COOKIE["session"])) {
                                        
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('login', '=', $_COOKIE["login"]);
                        $this->insertWhereCondition('session', '=', $_COOKIE["session"]);
                        $this->runSelect();
                
                        if($this->nextRow()) {
                        
                                $this->deleteRowByID($this->id);
                        }
                
                        setcookie('login', '', (time()+2592000), '/', '', 0);
                        setcookie('session', '', (time()+2592000), '/', '', 0);
                
                        unset ($_COOKIE["login"]);
                        unset ($_COOKIE["session"]);
                }
        }
        
        function exists() {
        
                global $_COOKIE;
                global $_SERVER;
                
                if(isset($_COOKIE["login"]) && isset($_COOKIE["session"])) {
                        
                        $this->clearWhereCondition();
                        $this->insertWhereCondition('login', '=', $_COOKIE["login"]);
                        $this->insertWhereCondition('session', '=', $_COOKIE["session"]);
                        $this->runSelect();

                        if($this->nextRow()) {

                                if($this->addr != $_SERVER["REMOTE_ADDR"]) {
                                        
                                        // blacklistIP();
                                        return false;
                                        
                                } else {
                                        
                                        if(time() < $this->timelimit) {
                                                
                                                return true;
                                                
                                        } else {
                                                
                                                $this->removeSession();
                                                return false;
                                        }
                                }
                                
                        } else {
                                
                                setcookie('login', '', (time()+2592000), '/', '', 0);
                                setcookie('session', '', (time()+2592000), '/', '', 0);
                
                                unset ($_COOKIE["login"]);
                                unset ($_COOKIE["session"]);
                                        
                                return false;
                        }
                }
                
                return false;        
        }
}
?>
