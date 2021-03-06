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
// $Id: bloxx_page.php,v 1.11 2005-09-05 22:55:40 tmenezes Exp $

require_once 'defines.php';
require_once(CORE_DIR . 'bloxx_module.php');
require_once(CORE_DIR . 'bloxx_role.php');

class Bloxx_Page extends Bloxx_Module
{
        function Bloxx_Page()
        {
                $this->_BLOXX_MOD_PARAM['name'] = 'page';
                $this->_BLOXX_MOD_PARAM['module_version'] = 1;
                $this->_BLOXX_MOD_PARAM['label_field'] = 'title';
                $this->_BLOXX_MOD_PARAM['use_init_file'] = true;
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'title' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'LANG' => true),
                        'content' => array('TYPE' => 'HTML', 'SIZE' => -1, 'NOTNULL' => false, 'LANG' => true),
                        'headerfooter' => array('TYPE' => 'BLOXX_HEADERFOOTER', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }
        
        function getLocalRenderTrusts()
        {
                return array(
                        'normal' => TRUST_GUEST,
                        'internal' => TRUST_GUEST
                );
        }
        
	function renderPage($view, $param, $target, $jump, $other_params, $mt)
    {
        	
    	global $warningmessage;
        
        include_module_once('style');
        include_module_once('headerfooter');
        include_once(CORE_DIR . 'bloxx_tokenizer.php');
                
        $tokenizer = new Bloxx_Tokenizer();
                
        $this->getRowByID($param);                

		$html_part_1 = '';

		//Transactional XHTML doctype
		//$html_part_1 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				                                                
        $html_part_1 .= '<html><head>';        
                
        include_module_once('config');
        $config = new Bloxx_Config();        
        $site_name = $config->getConfig('site_name');
        $config = new Bloxx_Config();
        $page_title_separator = $config->getConfig('page_title_separator');
        $config = new Bloxx_Config();
        $description = $config->getConfig('description');
        $config = new Bloxx_Config();
        $keywords = $config->getConfig('keywords');
                
        $html_part_1 .= '<title>' . $site_name . $page_title_separator . $this->title . '</title>';
        
        $html_part_1 .= '<meta name="description" content="' . $description . '" />';
        $html_part_1 .= '<meta name="keywords" content="' . $keywords . '" />';		
		
		$html_part_1 .= '
		<meta name="Generator" content="Bloxx" />
		<meta name="robots" content="index, follow" />';
                
        $javascript_part = '';
        $body_part = '';
                
        if (isset($warningmessage))
        {
                
        	$javascript_part .= '<script language="JavaScript">alert("' . $warningmessage . '")</script>';
        }
                
        $style = new Bloxx_Style();
        $html_part_2 = $style->renderStyleSheet();

        $hf = new Bloxx_HeaderFooter();
       
        if (isset($this->headerfooter))
        {        	        
        	$hf->getRowByID($this->headerfooter);
        }
                
        //Fist Pass---------------------------------------------
        $content = $hf->header_html . $this->content . $hf->footer_html;
                
        $begin_tag = true;
                
        if (substr($content, 0, 1) != "<")
        {
                
        	$begin_tag = false;
        }
                
       	$tok = $tokenizer->getToken($content, '<');
        $count = strlen($tok) + 1;
                
        $bloxx_content = '';
        $first_pass = '';
                
        $waiting_for_bloxx_end = false;
                
        while($tok)
        {
                        
        	if ((substr($tok, 0, 13) == "bloxx_private")
            	|| (substr($tok, 0, 13) == "bloxx_discard"))
            {
                        
            	$waiting_for_bloxx_end = true;
            	$bloxx_content = $tok;
            }
            else if (substr($tok, 0, 14) == "/bloxx_private")
            {
                        
            	$this->preParseBloxx($bloxx_content, $first_pass);

                $waiting_for_bloxx_end = false;
            }
            else if (substr($tok, 0, 14) == "/bloxx_discard")
            {

            	$this->preParseBloxx($bloxx_content, $first_pass);

                $waiting_for_bloxx_end = false;
            }
            else if ($waiting_for_bloxx_end)
            {
                        
            	$bloxx_content .= '<' . $tok;
            }
            else
            {
                        
            	if ($begin_tag)
            	{
                                
                	$first_pass .= "<";
                }
                                
                $first_pass .= $tok;
            }
                        
            $begin_tag = true;
                        
            $tok = $tokenizer->getToken('<');
        }
                
        //Second Pass---------------------------------------------
        $begin_tag = true;

        if (substr($first_pass, 0, 1) != "<")
        {

        	$begin_tag = false;
        }

        $tok = $tokenizer->getToken($first_pass, '<');
        $count = strlen($tok) + 1;

        $bloxx_content = '';

        while($tok)
        {

        	if (substr($tok, 0, 9) == "bloxx_mod")
        	{

            	$waiting_for_bloxx_end = true;
                $bloxx_content = $tok;
            }
            else if (substr($tok, 0, 10) == "/bloxx_mod")
            {				

            	$this->parseBloxx($bloxx_content, $html_part_2, $javascript_part, $body_part);

                $waiting_for_bloxx_end = false;
            }
            else if ($waiting_for_bloxx_end)
            {

            	$bloxx_content .= '<' . $tok;
            }
            else
            {

            	if($begin_tag)
            	{

                	$html_part_2 .= "<";
                }

                $html_part_2 .= $tok;
            }

            $begin_tag = true;

            $tok = $tokenizer->getToken('<');
		}
                
        if($view == 'internal')
        {
                
        	return $html_part_2;
        }
                
        $body = '<body ' . $body_part . ' ' . $hf->bodytag_params . '>';
        
        $body .= '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />';
        
        $html_part_2 = '</head>'
        	. $body
            . $html_part_2
            . '</body></html>';
                
        $html_out = $html_part_1 . $hf->extra_head_code . $javascript_part . $html_part_2;
                
        return $html_out;
	}
        
        function preParseBloxx($bloxx_html, &$html_out)
        {                
                if(substr($bloxx_html, 0, 13) == "bloxx_private"){
                        
                        ereg('([^> ]*) ([^> ]*) ([^> ]*)>(.*)', $bloxx_html, $regs);
                        $tag = $regs[1];
                        $p1 = $regs[2];
                        $p2 = $regs[3];
                        $content = $regs[4];

                        ereg('(.*)="(.*)"', $p1, $regs);
                        $$regs[1] = $regs[2];
                        ereg('(.*)="(.*)"', $p2, $regs);
                        $$regs[1] = $regs[2];
                        
                        $mname = 'Bloxx_' . $module;

                        include_module_once($module);

                        $module_inst = new $mname();
                        
                        $needed_trust = constant('TRUST_' . $trust);
                        
                        if($module_inst->verifyTrust($needed_trust)){
                        
                                $html_out .= $content;
                        }
                }
                else if(substr($bloxx_html, 0, 13) == "bloxx_discard"){

                        ereg('([^> ]*) ([^> ]*) ([^> ]*)>(.*)', $bloxx_html, $regs);
                        $tag = $regs[1];
                        $p1 = $regs[2];
                        $p2 = $regs[3];
                        $content = $regs[4];

                        ereg('(.*)="(.*)"', $p1, $regs);
                        $$regs[1] = $regs[2];
                        ereg('(.*)="(.*)"', $p2, $regs);
                        $$regs[1] = $regs[2];

                        $mname = 'Bloxx_' . $module;

                        include_module_once($module);

                        $module_inst = new $mname();

                        $needed_trust = constant('TRUST_' . $trust);

                        if(!$module_inst->verifyTrust($needed_trust)){

                                $html_out .= $content;
                        }
                }
        }
        
        function parseBloxx($bloxx_html, &$html_out, &$javascript_out, &$body_out)
        {                
                $target = null;

                if (substr($bloxx_html, 0, 9) == "bloxx_mod")
                {

                        $nparams = substr_count($bloxx_html, '"');
                        $nparams /= 2;                        

                        $regex = '([^> ]*)';

                        for ($n = 0; $n < $nparams; $n++)
                        {

                                $regex .= ' ([^> ]*)';
                        }

                        $regex .= '>(.*)';

                        ereg($regex, $bloxx_html, $regs);
                        $tag = $regs[1];
                        
                        $other_params = array();

                        for ($n = 0; $n < $nparams; $n++)
                        {								

                                ereg('(.*)="(.*)"', $regs[$n + 2], $par);
                                
                                if (($par[1] == 'module')
                                	|| ($par[1] == 'view')
                                	|| ($par[1] == 'param')
                                	|| ($par[1] == 'target')
                                	|| ($par[1] == 'jump'))
                                {
                                	
                                	$$par[1] = $par[2];
                                }
                                else
                                {
                                	$other_params[$par[1]] = $par[2];
                                }
                        }

                        $content = $regs[$n + 2];
                        
                        if ($module == 'from_url')
                        {

                                if (isset($_GET['module']))
                                {

                                        $module = $_GET['module'];
                                }                                
                        }

						/*if ($module == '')
						{
							return false;
						}*/

                        $mname = 'Bloxx_' . $module;						

                        include_module_once($module);

                        $module_inst = new $mname();

                        if ($view == 'from_url')
                        {

                                if (isset($_GET['mode']))
                                {

                                       $view = $_GET['mode'];
                                }
                                else{

                                        $view = $module_inst->_BLOXX_MOD_PARAM['default_view'];
                                }
                        }

                        if ($target == 'from_url')
                        {

                                if (isset($_GET['target']))
                                {

                                        $target = $_GET['target'];
                                }
                                else{

                                        $target = 0;
                                }
                        }
                        else if (substr($target, 0, 4) == "var_")
                        {
                        
                                ereg('last-(.*)', $target, $regs);
                                $varname = $regs[1];
                                
                                if (isset($_GET[$varname]))
                                {

                                        $target = $_GET[$varname];
                                }
                                else
                                {

                                        $target = 0;
                                }
                        }

                        if (isset($param))
                        {
                        
                                if (($param == -1) || ($param == 'from_url'))
                                {

                                        if (isset($_GET['param']))
                                        {

                                                $param = $_GET['param'];
                                        }
                                        else
                                        {

                                                $param = 0;
                                        }
                                }
                                else if (substr($param, 0, 4) == "last")
                                {

                                        $count_back = 1;

                                        if ($param != "last")
                                        {

                                                ereg('last-(.*)', $param, $regs);
                                                $count_back = $regs[1] + 1;
                                        }

                                        $param = $module_inst->getRowIDFromEnd($count_back);
                                }
                                else if (substr($param, 0, 10) == "targetlast")
                                {

                                        $tname = 'Bloxx_' . $target;
                                        include_module_once($target);
                                        $target_module = new $tname();

                                        $count_back = 1;

                                        if ($param != "targetlast")
                                        {

                                                ereg('targetlast-(.*)', $param, $regs);
                                                $count_back = $regs[1] + 1;
                                        }

                                        $param = $target_module->getRowIDFromEnd($count_back);
                                }
                                else if (substr($param, 0, 4) == "var_")
                                {

                                        ereg('var_(.*)', $param, $regs);
                                        $varname = $regs[1];

                                        if (isset($_GET[$varname]))
                                        {

                                                $param = $_GET[$varname];
                                        }
                                        else
                                        {

                                                $param = 0;
                                        }
                                }
                        }
                        else
                        {
                        
                                $param = null;
                        }

                        $javascript_out .= $module_inst->renderJavaScript($view, $param, $target);
                        $body_out .= $module_inst->renderBodyParams($view, $param, $target) . ' ';
                        
                        if (isset($template))
                        {
                        	$html_out .= $module_inst->render($view, $param, $target, $jump, $other_params, $template);                        
                        }
                        else
                        {
                        	$html_out .= $module_inst->render($view, $param, $target, $jump, $other_params);
                        }
                }
        }

//  Render methods .............................................................
        
	function doRenderNormal($param, $target, $jump, $other_params, $mt)
    {
        	
    	return $this->renderPage('normal', $param, $target, $jump, $other_params, $mt);
	}
	
	function doRenderInternal($param, $target, $jump, $other_params, $mt)
    {
        	
    	return $this->renderPage('internal', $param, $target, $jump, $other_params, $mt);
	}
}
?>
