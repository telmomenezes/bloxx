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

require_once(CORE_DIR.'bloxx_module.php');

class Bloxx_MailComment extends Bloxx_Module
{
        var $mailParams = array('Received:',
                                        'Return-Path: ',
                                        'X-Originating-IP: ',
                                        'X-Originating-Email: ',
                                        'X-Sender: ',
                                        'MIME-Version: ',
                                        'Content-Type: ',
                                        'X-OriginalArrivalTime: ',
                                        'From: ',
                                        'Subject: ',
                                        'Date: ',
                                        'Message-ID: ',
                                        'Errors-To: ',
                                        'Reply-To: ',
                                        'X-Topica-Id: ',
                                        'List-Help: ',
                                        'List-Unsubscribe: ',
                                        'Content-Transfer-Encoding: ',
                                        'X-Mailer: ',
                                        'Content-disposition: ',
                                        'X-Accept-Language: ',
                                        'X-AntiVirus: ',
                                        'Content-language: ',
                                        'To: ');


        function Bloxx_MailComment()
        {
                $this->name = 'mailcomment';
                $this->module_version = 1;
                $this->label_field = 'subject';
                
                $this->use_init_file = true;
                
                $this->default_mode = 'comment';
                
                $this->Bloxx_Module();
        }
        
        function getTableDefinition()
        {
                return array(
                        'user_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'subject' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true, 'USER' => true),
                        'author' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => false, 'USER' => true),
                        'email' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => false, 'USER' => true),
                        'content' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),
                        'rawcontent' => array('TYPE' => 'TEXT', 'SIZE' => -1, 'NOTNULL' => true, 'USER' => true),
                        'parent_id' => array('TYPE' => 'NUMBER', 'SIZE' => -1, 'NOTNULL' => true),
                        'parent_type' => array('TYPE' => 'STRING', 'SIZE' => 100, 'NOTNULL' => true),
                        'publish_date' => array('TYPE' => 'DATETIME', 'SIZE' => -1, 'NOTNULL' => false)
                );
        }

        function getRenderTrusts()
        {
                return array(
                        'comment_link' => TRUST_USER,
                        'read_comments_link' => TRUST_GUEST,
                        'read_comments' => TRUST_GUEST,
                        'comment' => TRUST_GUEST,
                        'comment_header' => TRUST_GUEST,
                        'new_comment' => TRUST_GUEST,
                        'comments_count' => TRUST_GUEST,
                        'count' => TRUST_GUEST,
                        'list_view' => TRUST_GUEST,
                        'reformat' => TRUST_GUEST
                );
        }
        
        function getStyleList()
        {
                return array(
                        'Title' => 'NormalTitle',
                        'Text' => 'NormalText',
                        'Link' => 'NormalLink',
                        'Info' => 'NormalText',
                        'HeaderBlock' => 'CommentHeader',
                        'BodyBlock' => 'CommentBlock',
                        'ContentBlock' => 'CommentContent'
                );
        }
        
        function renderCommentsTree($id, $target, $indent, $list_view)
        {
                $style = new Bloxx_Style();
                $style_block = $this->getGlobalStyle('BodyBlock');
        
                $width = $indent * 50;
        
                $comm = new Bloxx_MailComment();

                $comm->clearWhereCondition();
                $comm->insertWhereCondition("parent_id='" . $id . "'");
                $comm->insertWhereCondition("parent_type='" . $target . "'");
                $comm->setOrderBy('publish_date', true);
                $comm->setLimit(25);
                $comm->runSelect();

                while($comm->nextRow()){

                        $html_out .= '<table width="100%" cellpadding="0" cellspacing="0"><tr><td width="' . $width . '"><img src="res/system/transparent_pixel.gif" width="' . $width . '" height="1"></td><td width="100%">';

                        if(!$list_view){

                                $html_out .= $style->renderStyleHeader($style_block);
                                $html_out .= $comm->render('comment', $comm->id);
                                //$html_out .= '<br>';
                                //$html_out .= $comm->render('comment_link', $comm->id, 'comment');
                                $html_out .= $style->renderStyleFooter($style_block);
                        }
                        else{

                                $html_out .= $comm->render('comment_header', $comm->id, $comm->parent_type);
                        }

                        $html_out .= '</td></tr></table>';
                        
                        $html_out .= $comm->renderCommentsTree($comm->id, 'mailcomment', $indent + 1, $list_view);
                }

                return $html_out;
        }
        
        function countComments($id, $target)
        {
                $count = 0;
        
                $comm = new Bloxx_MailComment();

                $comm->clearWhereCondition();
                $comm->insertWhereCondition("parent_id='" . $id . "'");
                $comm->insertWhereCondition("parent_type='" . $target . "'");
                $comm->runSelect();

                while($comm->nextRow()){

                        $count++;
                        $count += $comm->countComments($comm->id, 'comment');
                }

                return $count;
        }
        
        function doRender($mode, $id, $target)
        {
                $style = new Bloxx_Style();
                $style_title = $this->getGlobalStyle('Title');
                $style_text = $this->getGlobalStyle('Text');
                $style_link = $this->getGlobalStyle('Link');
                $style_info = $this->getGlobalStyle('Info');
                $style_header = $this->getGlobalStyle('HeaderBlock');
                $style_content = $this->getGlobalStyle('ContentBlock');
                
                if($mode == 'comment_link'){

                        include_module_once('comment');
                        $comment = new Bloxx_Comment();
                        $new_comment_page = $comment->getConfig('new_comment_page');

                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= build_link($new_comment_page, 'new_comment', $id, $target, LANG_COMMENT_COMMENT, true);
                        $html_out .= $style->renderStyleFooter($style_link);
                  
                        return $html_out;
                }
                else if($mode == 'read_comments_link'){

                        include_module_once('comment');
                        $comment = new Bloxx_Comment();
                        $new_comment_page = $comment->getConfig('read_comments_page');

                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= build_link($read_comments_page, 'read_comments', $id, $target, LANG_COMMENT_READ_COMMENT, false);
                        $html_out .= $style->renderStyleFooter($style_link);

                        return $html_out;
                }
                else if($mode == 'new_comment'){
                
                        $tname = 'Bloxx_'.$target;
                        include_module_once($target);
                        $target_module = new $tname();

                        $html_out .= $target_module->render($target_module->default_mode, $id);
                
                        $this->parent_id = $id;
                        $this->parent_type = $target;
                        
                        include_module_once('identity');
                        $ident = new Bloxx_Identity();
                        $this->user_id = $ident->id();
                        
                        $html_out .= $this->renderForm(-1, false);

                        return $html_out;
                }
                else if($mode == 'read_comments'){

                                
                        include_module_once('MailComment');
                        $target_module = new Bloxx_MailComment();
                        
                        $html_out .= $target_module->render($target_module->default_mode, $id);

                        $html_out .= '<br>';
                                        
                        $html_out .= $this->renderCommentsTree($id, 'mailcomment', 0, false);
                        $html_out .= '<br><br>';

                        return $html_out;
                }
                else if($mode == 'list_view'){

                        $tname = 'Bloxx_' . $target;

                        $file_name = 'bloxx_' . strtolower($target) . '.php';

                        if(file_exists(CORE_DIR . $file_name)){

                                include_module_once($target);
                                $target_module = new $tname();

                                $html_out .= $target_module->render($target_module->default_mode, $id);

                                $html_out .= '<br>';
                        }

                        $html_out .= $this->renderCommentsTree($id, $target, 0, true);
                        $html_out .= '<br><br>';

                        return $html_out;
                }
                else if($mode == 'comments_count'){

                        $id_in = $id;

                        $count = $this->countComments($id, $target);
                        
                        if($count > 0){
                        

                                include_module_once('config');
                                $config = new Bloxx_Config();
                                $read_comments_page = $config->getValue('comment', 'read_comments_page');

                                if($count == 1){
                                
                                        $text = '(' . $count . ' ' . LANG_MAILCOMMENT_ONE_COMMENT . ')';
                                }
                                else{
                                
                                        $text = '(' . $count . ' ' . LANG_MAILCOMMENT_COMMENTS . ')';
                                }

                                $html_out .= $style->renderStyleHeader($style_link);
                                $html_out .= build_link($read_comments_page, 'read_comments', $id_in, $target, $text, false);
                                $html_out .= $style->renderStyleFooter($style_link);
                        }

                        return $html_out;
                }
                else if($mode == 'count'){

                        $id_in = $id;

                        $count = $this->countComments($id, $target);

                        $html_out = $count;

                        return $html_out;
                }
                else if($mode == 'comment'){

                        include_module_once('identity');
                        
                        $ident = new Bloxx_Identity();

                        if(!isset($this->id)){
                        
                                $this->getRowByID($id);
                        }
                        
                        $ident->getRowByID($this->user_id);

                        include_module_once('config');
                        $config = new Bloxx_Config();
                        $comment_header_color = $config->getValue('comment', 'comment_header_color');

                        $html_out .= $style->renderStyleHeader($style_header);
                        $html_out .= $style->renderStyleHeader($style_title);
                        $html_out .= $this->subject;
                        $html_out .= $style->renderStyleFooter($style_title);
                        $html_out .= '<br>';
                        $html_out .= $style->renderStyleHeader($style_info);
                        $html_out .= LANG_MAILCOMMENT_BY . $this->author;
                        $html_out .= ', ' . getDateAndTimeString($this->publish_date);
                        $html_out .= $style->renderStyleFooter($style_info);
                        $html_out .= $style->renderStyleFooter($style_header);
                        $html_out .= $style->renderStyleHeader($style_content);
                        $html_out .= $style->renderStyleHeader($style_text);

                        if($this->verifyTrust(TRUST_ADMINISTRATOR)){
                                
                                $read_comments_page = $this->getConfig('read_comments_page');
                                $html_out .= $style->renderStyleHeader($style_link);
                                $html_out .= build_link($read_comments_page, 'reformat', $id, $target, 'Reformatar', false);
                                $html_out .= $style->renderStyleFooter($style_link);
                                $html_out .= '<br><br>';
                        }
                        
                        $html_out .= $this->renderAutoText($this->content);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= $style->renderStyleFooter($style_content);

                        return $html_out;
                }
                else if($mode == 'comment_header'){

                        if(!isset($this->id)){

                                $this->getRowByID($id);
                        }
                        
                        $read_comments_page = $config->getConfig('read_comments_page');

                        $html_out .= '<ul><li>';
                        $html_out .= $style->renderStyleHeader($style_link);
                        $html_out .= build_link($read_comments_page, 'read_comments', $id, $target, $this->subject, false);
                        $html_out .= $style->renderStyleFooter($style_link);
                        $html_out .= $style->renderStyleHeader($style_text);
                        $html_out .= '&nbsp;' . LANG_MAILCOMMENT_BY . $this->author;
                        $html_out .= ', ' . getDateAndTimeString($this->publish_date);
                        $html_out .= $style->renderStyleFooter($style_text);
                        $html_out .= '</li></ul>';

                        return $html_out;
                }
                else if($mode == 'reformat'){

                        $this->getRowByID($id);
                        
                        $this->processMail($this->rawcontent, true);
                
                        $html_out .= $this->render('comment', $id);
                        
                        return $html_out;
                }
        }
        
        function create()
        {
                include_module_once('bloxx_role');
        
                if(!$this->verifyTrust(TRUST_USER)){

                        return false;
                }
                
                global $HTTP_POST_VARS;

                $def = $this->tableDefinitionLangComplete();

                foreach($def as $k => $v){

                        if(isset($HTTP_POST_VARS[$k])){

                                $this->$k = $HTTP_POST_VARS[$k];
                        }
                }
                
                $this->publish_date = time();

                return $this->insertRow();
        }
        
        function processMail($mail, $update = false)
        {
                $this->rawcontent = $mail;
                
                if(!$update){
                
                        $this->publish_date = time();
                }
                
                $params = $this->explodeMailParams($mail);

                $subject = $this->getSubject($params);
                $this->subject = $subject;
                $this->author = $this->getFrom($params);
                $this->email = $this->getEmail($params);
                $this->content = $this->getBody($params);

                $mcid = $this->findInsertPoint($subject);

                if($mcid < 0){
                
                        $this->parent_type = 'dummy';
                        $this->parent_id = -$mcid;
                }
                else{
                
                        $this->parent_type = 'mailcomment';
                        $this->parent_id = $mcid;
                }

                if($update){
                
                        return $this->updateRow();
                }
                else{
                
                        return $this->insertRow();
                }
        }
        
        function explodeMailParams($msg)
        {
                $params = array();
                $oldParam = 'inicio';
                $curToken = '';
                $CRLF = false;
                $headerEnd = false;
                $body = '';

                for($n = 0; ($n < strlen($msg)) && (!$headerEnd); $n++){

                        $cc = substr($msg, $n, 1);
                        
                        if($cc == chr(10)){
                        
                                if($CRLF){
                                
                                        $headerEnd = true;
                                        $body = substr($msg, $n);
                                }
                                else{
                                
                                        $CRLF = true;
                                }
                        }
                        else if($cc != chr(13)){
                        
                                $CRLF = false;
                        }
                        
                        $curToken .= $cc;

                        $param = $this->getMailParam($curToken);
                        if($param != null){

                                $value = substr($curToken, 0, -strlen($param));

                                if($oldParam != 'inicio'){
                                
                                        $params[strtolower($oldParam)] = $value;
                                }
                                
                                $oldParam = $param;
                                $curToken = '';
                        }
                }
                
                $params['body'] = $body;
                
                return $params;
        }
        
        function getMailParam($token)
        {
                
                foreach($this->mailParams as $p){
                
                        $pLength = strlen($p);
                        
                        if(strtolower(substr($token, -$pLength)) == strtolower($p)){
                        
                                return $p;
                        }
                }
                
                return null;
        }
        
        function getSubject($params)
        {
        
                $subject = $params['subject: '];
                $subject = $this->removeIso($subject);
                $subject = $this->strRemove($subject, "Spam Alert: ");
                $subject = str_replace("_", " ", $subject);
                $subject = quoted_printable_decode($subject);

                $subject = $this->strRemove($subject, "[academica]");
                
                return $this->removeFrontSpaces($subject);
        }
        
        function getFrom($params)
        {
        
                $from = $params['from: '];
                $from = $this->strRemove($from, '"');

                $parts = explode("<", $from);
                $from = $parts[0];

                $from = $this->removeIso($from);
                $from = str_replace("_", " ", $from);
                $from = quoted_printable_decode($from);

                return $from;
        }
        
        function getEmail($params)
        {
        
                $from = $params['from: '];
                
                $parts = explode("<", $from);
                $email = $parts[1];
                $email = substr($email, 0, -2);
                
                return $email;
        }
        
        function getBody($params)
        {
                $body = quoted_printable_decode($params['body']);
                
                if($this->isMultiPartMime($body)){
                
                        $body = $this->extractMultiPart($body);
                }
                
                $body = $this->removeReplyLines($body);
                $body = $this->removePubBlocks($body);
                $body = $this->removePubLines($body);
                $body = $this->removeMensagemOriginal($body);
                
                //$body = nl2br($body);
                $body = $this->rnToBr($body);
                
                return $body;
        }
        
        function getData($params)
        {

                return $params['date: '];
        }
        
        function removeReplyLines($msg)
        {

                $out = '';
                $tok = strtok($msg, "\r\n");

                while($tok){

                        if((substr($tok, 0, 1) != '>')
                         && ((substr($tok, 0, 3) != 'On ') || (substr($tok, -6) != 'wrote:'))
                         && ((substr($tok, 0, 3) != 'At ') || (substr($tok, -6) != 'wrote:'))
                         ){
                        
                                $out .= $tok . "\r\n";
                        }
                        
                        $tok = strtok("\r\n");
                }
                
                return $out;
        }
        
        function removePubBlocks($msg)
        {

                $out = '';
                $tok = strtok($msg, "\r\n");
                
                $pub = false;

                while($tok){

                        if(($tok == '===========================================================')
                          || ($tok == '--^----------------------------------------------------------------')
                          || ($tok == '***********************************************************')){

                                $pub = !$pub;
                        }
                        else{
                        
                                if(!$pub){
                                
                                        $out .= $tok . "\r\n";
                                }
                        }

                        $tok = strtok("\r\n");
                }

                return $out;
        }
        
        function removePubLines($msg)
        {

                $out = '';
                $tok = strtok($msg, "\r\n");

                $pub = false;

                while($tok){

                        if((substr($tok, 0, 20) != "____________________")
                          && (substr($tok, 0, 11) != "MSN Hotmail")
                          && (substr($tok, 0, 39) != "http://br.download.yahoo.com/messenger/")
                          && (substr($tok, 0, 16) != "Yahoo! Messenger")){

                                $out .= $tok . "\r\n";
                        }

                        $tok = strtok("\r\n");
                }

                return $out;
        }
        
        function removeMensagemOriginal($msg)
        {

                $out = '';
                $tok = strtok($msg, "\r\n");

                $end = false;

                while($tok){
                
                        if((substr($tok, 0, 28) == "----- Original Message -----")
                          || (substr($tok, 0, 27) == "-----Mensagem original-----")){
                        
                                $end = true;
                        }

                        if(!$end){
                        
                                $out .= $tok . "\r\n";
                        }
                        
                        $tok = strtok("\r\n");
                }

                return $out;
        }
        
        function strRemove($str, $rmv)
        {
                return implode('', explode("$rmv", $str));
        }
        
        function rnToBr($str)
        {
                return implode("<br>", explode("\r\n", $str));
        }
        
        function isMultiPartMime($msg)
        {

                $tok = strtok($msg, "\r\n");

                return (substr($tok, 0, 44) == "This is a multi-part message in MIME format.");
        }
        
        function extractMultiPart($msg)
        {
        
                $out = '';
                $tok = strtok($msg, "\r\n");

                $extract = false;
                $end = false;

                while($tok && (!$end)){

                        if($extract && (substr($tok, 0, 16) != "------=_NextPart")){
                        
                                $out .= $tok . "\r\n";
                        }

                        if(substr($tok, 0, 16) == "------=_NextPart"){

                                if($extract){
                                
                                        $end = true;
                                }
                                
                                $extract = true;
                        }

                        $tok = strtok("\r\n");
                }
                
                $out = $this->removeHeaderLines($out);

                return $out;
        }
        
        function removeHeaderLines($msg)
        {
        
                $out = '';
                $tok = strtok($msg, "\r\n");

                while($tok){

                        $isHeader = false;

                        foreach($this->mailParams as $p){

                                $pLength = strlen($p);

                                if(strtolower(substr($tok, 0, $pLength)) == strtolower($p)){

                                        $isHeader = true;
                                }
                        }

                        if(!$isHeader){

                                $out .= $tok . "\r\n";
                        }
                        
                        $tok = strtok("\r\n");
                }
                
                return $out;
        }
        
        function findInsertPoint($subj, $stop = true)
        {
        
                $subject = $this->removeFrontSpaces($subj);
                if(strtolower(substr($subject, 0, 4)) == "re: "){

                        $deepSubject = $this->removeFrontSpaces(substr($subject, 4));
                        
                        $insertPoint = $this->findInsertPoint($deepSubject, false);
                        
                        if($stop){
                        
                                return $insertPoint;
                        }
                        else{
                        
                                $mc = new Bloxx_MailComment();
                                $mc->clearWhereCondition();
                                $mc->insertWhereCondition("parent_id=" . $insertPoint);
                                $mc->insertWhereCondition("parent_type='mailcomment'");
                                $mc->insertWhereCondition("subject like '" . $subject . "%'");
                                $mc->setOrderBy('publish_date', true);
                                $mc->runSelect();

                                if($mc->nextRow()){

                                        return $mc->id;
                                }
                                else{

                                        return -1;
                                }
                        }
                }
                else if($stop){
                
                        return -1;
                }
                else{
                
                        $mc = new Bloxx_MailComment();
                        $mc->clearWhereCondition();
                        $mc->insertWhereCondition("parent_id=1");
                        $mc->insertWhereCondition("parent_type='dummy'");
                        $mc->insertWhereCondition("subject='" . $subject . "'");
                        $mc->setOrderBy('publish_date', true);
                        $mc->runSelect();
                        
                        if($mc->nextRow()){

                                return $mc->id;
                        }
                        else{

                                return -1;
                        }
                }
        }
        
        function removeFrontSpaces($str)
        {
                $end = false;
        
                for($n = 0; ($n < strlen($str)) && (!$end); $n++){

                        $cc = substr($str, $n, 1);

                        if($cc != " "){
                        
                                $end = true;
                        }
                }
                
                return substr($str, $n - 1);
        }

        function removeIso($str)
        {
                $str = $this->strRemove($str, "=?iso-8859-1?Q?");
                $str = $this->strRemove($str, "=?ISO-8859-1?Q?");
                $str = $this->strRemove($str, "=?iso-8859-15?Q?");
                $str = $this->strRemove($str, "=?ISO-8859-15?Q?");
                $str = $this->strRemove($str, "?=");
                
                return $str;
        }
}
?>
