<?php
/*
 * Created on Feb 5, 2005
 *
 * draft
 * 
 */

require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_Session extends Bloxx_Module {
	
	function Bloxx_Session() {
		
		$this->name = 'session';
		$this->module_version = 1;
		//$this->label_field = 'login';
		//$this->use_init_file = true;
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
		$this->session = md5(mt_rand(0, 99999999999999999));
		$this->timelimit = time() + 2592000; // Timelimit restriction goes here
		$this->addr = $_SERVER["REMOTE_ADDR"];
		
		$this->insertRow();
		
		setcookie('login', $this->login, $this->timelimit, '/', '', 0);
		setcookie('session', $this->session, $this->timelimit, '/', '', 0);
		
		$_COOKIE["login"] = $this->login;
		$_COOKIE["session"] = $this->session;
	}
	
	function removeSession() {
		
		global $_COOKIE;
		
		if(isset($_COOKIE["login"]) && isset($_COOKIE["session"])) {
					
			$this->clearWhereCondition();
			$this->insertWhereCondition("login='" . $_COOKIE["login"] . "'");
			$this->insertWhereCondition("session='" . $_COOKIE["session"] . "'");
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
			$this->insertWhereCondition("login='" . $_COOKIE["login"] . "'");
			$this->insertWhereCondition("session='" . $_COOKIE["session"] . "'");
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
