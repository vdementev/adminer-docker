<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 5.5.1
*/namespace
Adminer;const
VERSION="5.5.1";error_reporting(24575);set_error_handler(function($rc,$tc){return!!preg_match('~^Undefined (array key|offset|index)~',$tc);},E_WARNING|E_NOTICE);$Pc=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Pc||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$Hi=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Hi)$$X=$Hi;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($f=null){return($f?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$vb=adminer()->credentials();$J=Driver::connect($vb[0],$vb[1],$vb[2]);return(is_object($J)?$J:null);}function
idf_unescape($t){if(!preg_match('~^[`\'"[]~',$t))return$t;$pe=substr($t,-1);return
str_replace($pe.$pe,$pe,substr($t,1,-1));}function
q($Q){return
connection()->quote($Q);}function
idx($sa,$w,$j=null){return($sa&&array_key_exists($w,$sa)?$sa[$w]:$j);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$Xi,$Pc=false){$J=array();foreach($Xi
as$w=>$X)$J[stripslashes($w)]=(is_array($X)?remove_slashes($X,$Pc):($Pc?$X:stripslashes($X)));return$J;}function
bracket_escape($t,$Aa=false){static$vi=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($t,($Aa?array_flip($vi):$vi));}function
min_version($aj,$Ge="",$f=null){$f=connection($f);$rh=$f->server_info;if($Ge&&preg_match('~([\d.]+)-MariaDB~',$rh,$A)){$rh=$A[1];$aj=$Ge;}return$aj&&version_compare($rh,$aj)>=0;}function
charset(Db$e){return(min_version("5.5.3",0,$e)?"utf8mb4":"utf8");}function
ini_set($Jf,$Y){return(function_exists('ini_set')?\ini_set($Jf,$Y):false);}function
ini_bool($Qd){$X=ini_get($Qd);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($Qd){$X=ini_get($Qd);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Zi,$N,$V,$F){$_SESSION["pwds"][$Zi][$N][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
get_val($H,$l=0,$lb=null){$lb=connection($lb);$I=$lb->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return($K?$K[$l]:false);}function
get_vals($H,$c=0){$J=array();$I=connection()->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$c];}return$J;}function
get_key_vals($H,$f=null,$uh=true){$f=connection($f);$J=array();$I=$f->query($H);if(is_object($I)){while($K=$I->fetch_row()){if($uh)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$f=null,$k="<p class='error'>"){$lb=connection($f);$J=array();$I=$lb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!$f&&$k&&(defined('Adminer\PAGE_HEADER')||$k=="-- "))echo$k.error()."\n";return$J;}function
unique_array($K,array$v){foreach($v
as$u){if(preg_match("~PRIMARY|UNIQUE~",$u["type"])&&!$u["partial"]){$J=array();foreach($u["columns"]as$w){if(!isset($K[$w]))continue
2;$J[$w]=$K[$w];}return$J;}}}function
escape_key($w){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$w,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($w);}function
where(array$Z,array$m=array()){$J=array();foreach((array)$Z["where"]as$w=>$X){$w=bracket_escape($w,true);$c=escape_key($w);$l=idx($m,$w,array());$Lc=$l["type"];$J[]=$c.(JUSH=="sql"&&$Lc=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$l["full_type"])?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Lc,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($l,q($X))))));if(JUSH=="sql"&&preg_match('~char|text~',$Lc)&&preg_match("~[^ -@]~",$X))$J[]="$c = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$w)$J[]=escape_key($w)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,array$m=array()){parse_str($X,$Ra);remove_slashes(array(&$Ra));return
where($Ra,$m);}function
where_link($r,$c,$Y,$Gf="="){return"&where%5B$r%5D%5Bcol%5D=".urlencode($c)."&where%5B$r%5D%5Bop%5D=".urlencode(($Y!==null?$Gf:"IS NULL"))."&where%5B$r%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$d,array$m,array$M=array()){$J="";foreach($d
as$w=>$X){if($M&&!in_array(idf_escape($w),$M))continue;$ta=convert_field($m[$w]);if($ta)$J
.=", $ta AS ".idf_escape($w);}return$J;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($C,$Y,$ye=2592000){header("Set-Cookie: $C=".rawurlencode($Y).($ye?"; expires=".gmdate("D, d M Y H:i:s",time()+$ye)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($Oi,$pb){$J=@file_get_contents($Oi,false,$pb);if(function_exists('http_get_last_response_headers'))$http_response_header=http_get_last_response_headers();return
array($J,(preg_match('~^HTTP/[\d.]+ (\d+)~',$http_response_header[0],$A)?$A[1]:''),);}function
get_settings($rb){parse_str($_COOKIE[$rb],$vh);return$vh;}function
get_setting($w,$rb="adminer_settings",$j=null){return
idx(get_settings($rb),$w,$j);}function
save_settings(array$vh,$rb="adminer_settings"){$Y=http_build_query($vh+get_settings($rb));cookie($rb,$Y);$_COOKIE[$rb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==1))session_start();}function
stop_session($Vc=false){$Ri=ini_bool("session.use_cookies");if(!$Ri||$Vc){session_write_close();if($Ri&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($w){return$_SESSION[$w][DRIVER][SERVER][$_GET["username"]];}function
set_session($w,$X){$_SESSION[$w][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Zi,$N,$V,$i=null){$Ni=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($i!==null?"db|":"").($Zi=='mssql'||$Zi=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Ni,$A);return"$A[1]?".(sid()?SID."&":"").($Zi!="server"||$N!=""?urlencode($Zi)."=".urlencode($N)."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($i!=""?"&db=".urlencode($i):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($_,$B=null){if($B!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($_!==null?$_:$_SERVER["REQUEST_URI"]))][]=$B;}if($_!==null){if($_=="")$_=".";header("Location: $_");exit;}}function
query_redirect($H,$_,$B,$Mg=true,$yc=true,$Gc=false,$ii=""){if($yc){$Hh=microtime(true);$Gc=!connection()->query($H);$ii=format_time($Hh);}$Ch=($H?adminer()->messageQuery($H,$ii,$Gc):"");if($Gc){adminer()->error
.=error().$Ch.script("messagesPrint();")."<br>";return
false;}if($Mg)redirect($_,$B.$Ch);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$H:(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";");return
connection()->query($H);}function
apply_queries($H,array$T,$uc='Adminer\table'){foreach($T
as$R){if(!queries("$H ".$uc($R)))return
false;}return
true;}function
queries_redirect($_,$B,$Mg){$Hg=implode("\n",Queries::$queries);$ii=format_time(Queries::$start);return
query_redirect($Hg,$_,$B,$Mg,false,!$Mg,$ii);}function
format_time($Hh){return
sprintf('%.3f s',max(0,microtime(true)-$Hh));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($ag=""){return
substr(preg_replace("~(?<=[?&])($ag".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($w,$Gb=false,$Mb=""){$Nc=$_FILES[$w];if(!$Nc)return
null;foreach($Nc
as$w=>$X)$Nc[$w]=(array)$X;$J='';foreach($Nc["error"]as$w=>$k){if($k)return$k;$C=$Nc["name"][$w];$qi=$Nc["tmp_name"][$w];$nb=file_get_contents($Gb&&preg_match('~\.gz$~',$C)?"compress.zlib://$qi":$qi);if($Gb){$Hh=substr($nb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Hh))$nb=iconv("utf-16","utf-8",$nb);elseif($Hh=="\xEF\xBB\xBF")$nb=substr($nb,3);}$J
.=$nb;if($Mb)$J
.=(preg_match("($Mb\\s*\$)",$nb)?"":$Mb)."\n\n";}return$J;}function
upload_error($k){$Pe=($k==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($k?'Unable to upload a file.'.($Pe?" ".sprintf('Maximum allowed file size is %sB.',$Pe):""):'File does not exist.');}function
repeat_pattern($ng,$x){return
str_repeat("$ng{0,65535}",$x/65535)."$ng{0,".($x%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Hc=false){$J=table_status($R,$Hc);return($J?reset($J):array("Name"=>$R));}function
column_foreign_keys($R){$J=array();foreach(adminer()->foreignKeys($R)as$n){foreach($n["source"]as$X)$J[$X][]=$n;}return$J;}function
fields_from_edit(){$J=array();foreach((array)$_POST["field_keys"]as$w=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$w];$_POST["fields"][$X]=$_POST["field_vals"][$w];}}foreach((array)$_POST["fields"]as$w=>$X){$C=bracket_escape($w,true);$J[$C]=array("field"=>$C,"privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>1,"auto_increment"=>($w==driver()->primary),);}return$J;}function
dump_headers($Bd,$hf=false){$J=adminer()->dumpHeaders($Bd,$hf);$Xf=$_POST["output"];if($Xf!="text")header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Bd).".$J".($Xf!="file"&&preg_match('~^[0-9a-z]+$~',$Xf)?".$Xf":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$J;}function
dump_csv(array$K){$Ai=$_POST["format"]=="tsv";foreach($K
as$w=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Ai?'\t':'[,;]|^$').'~',$X))$K[$w]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($Ai?"\t":";")),$K)."\r\n";}function
apply_sql_function($p,$c){return($p?($p=="unixepoch"?"DATETIME($c, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$c)"):$c);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($Oc){if(is_link($Oc))return;$o=@fopen($Oc,"c+");if(!$o)return;@chmod($Oc,0660);if(!flock($o,LOCK_EX)){fclose($o);return;}return$o;}function
file_write_unlock($o,$Ab){rewind($o);fwrite($o,$Ab);ftruncate($o,strlen($Ab));file_unlock($o);}function
file_unlock($o){flock($o,LOCK_UN);fclose($o);}function
first(array$sa){return
reset($sa);}function
password_file($g){$Oc=get_temp_dir()."/adminer.key";if(!$g&&!file_exists($Oc))return'';$o=file_open_lock($Oc);if(!$o)return'';$J=stream_get_contents($o);if(!$J){$J=rand_string();file_write_unlock($o,$J);}else
file_unlock($o);return$J;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($X,$z,array$l,$hi){if(is_array($X)){$J="";if(array_filter($X,'is_array')==array_values($X)){$je=array();foreach($X
as$W)$je+=array_fill_keys(array_keys($W),null);foreach(array_keys($je)as$ie)$J
.="<th>".h($ie);foreach($X
as$W){$J
.="<tr>";foreach(array_merge($je,$W)as$Ui)$J
.="<td>".select_value($Ui,$z,$l,$hi);}}else{foreach($X
as$ie=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($ie):"")."<td>".select_value($W,$z,$l,$hi);}return"<table>$J</table>";}if(!$z)$z=adminer()->selectLink($X,$l);if($z===null){if(is_mail($X))$z="mailto:$X";if(is_url($X))$z=$X;}$X=driver()->value($X,$l);$J=adminer()->editVal($X,$l);if($J!==null){if(!is_utf8($J))$J="\0";elseif($hi!=""&&is_shortable($l))$J=shorten_utf8($J,max(0,+$hi));else$J=h($J);}return
adminer()->selectVal($J,$z,$l,$X);}function
is_blob(array$l){return
preg_match('~blob|bytea|raw|file~',$l["type"])&&!in_array($l["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($ic){$ua='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Yb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$ng="$ua+(\\.$ua+)*@($Yb?\\.)+$Yb";return
is_string($ic)&&preg_match("(^$ng(,\\s*$ng)*\$)i",$ic);}function
is_url($Q){$Yb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Yb?\\.)+$Yb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$l){return!preg_match('~'.number_type().'|date|time|year~',$l["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)\]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$A)?array($A[4].$A[5],$A[2].$A[8]):array($N,''));}function
count_rows($R,array$Z,$ae,array$q){$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($ae&&(JUSH=="sql"||count($q)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$q).")$H":"SELECT COUNT(*)".($ae?" FROM (SELECT 1$H GROUP BY ".implode(", ",$q).") x":$H));}function
slow_query($H){$i=adminer()->database();$ji=adminer()->queryTimeout();$zh=driver()->slowQuery($H,$ji);$f=null;if(!$zh&&support("kill")){$f=connect();if($f&&($i==""||$f->select_db($i))){$ke=get_val(connection_id(),0,$f);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$ke&token=".get_token()."'); }, 1000 * $ji);");}}ob_flush();flush();$J=@get_key_vals(($zh?:$H),$f,false);if($f){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$Kg=rand(1,1e6);return($Kg^$_SESSION["token"]).":$Kg";}function
verify_token(){list($ri,$Kg)=explode(":",$_POST["token"]);return($Kg^$_SESSION["token"])==$ri&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Q){$oa=array_flip(str_split(compress_alphabet()));$x=strlen($Q);$Wi=($x?13*($x-1)/2-$oa[$Q[0]]:0);$b="";$Wg=0;$Xg=0;for($r=1;$r<$x;$r+=2){$Wg=($Wg<<13)+$oa[$Q[$r]]*93+$oa[$Q[$r+1]];$Xg+=13;while($Xg>=8&&$Wi>=8){$Xg-=8;$Wi-=8;$b
.=chr($Wg>>$Xg);$Wg&=(1<<$Xg)-1;}}if($b=="")return"";return(function_exists('gzinflate')?gzinflate($b):inflate($b));}function
inflate($b){$ve=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$we=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$Sb=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$Ub=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$J="";$G=0;do{$Qc=inflate_bits($b,$G,1);$U=inflate_bits($b,$G,2);if(!$U){$G=($G+7)&~7;$x=inflate_bits($b,$G,16);$G+=16;$J
.=substr($b,$G>>3,$x);$G+=$x<<3;}else{if($U==1){$Be=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Vb=array_fill(0,30,5);}else{$Ae=inflate_bits($b,$G,5)+257;$Tb=inflate_bits($b,$G,5)+1;$D=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$af=array_fill(0,19,0);$Ze=inflate_bits($b,$G,4)+4;for($r=0;$r<$Ze;$r++)$af[$D[$r]]=inflate_bits($b,$G,3);$bf=inflate_table($af);$xe=array();while(count($xe)<$Ae+$Tb){$Rh=inflate_symbol($b,$G,$bf);if($Rh==16)$xe=array_merge($xe,array_fill(0,inflate_bits($b,$G,2)+3,end($xe)));elseif($Rh==17)$xe=array_merge($xe,array_fill(0,inflate_bits($b,$G,3)+3,0));elseif($Rh==18)$xe=array_merge($xe,array_fill(0,inflate_bits($b,$G,7)+11,0));else$xe[]=$Rh;}$Be=array_slice($xe,0,$Ae);$Vb=array_slice($xe,$Ae);}$Ce=inflate_table($Be);$Xb=inflate_table($Vb);while(($Rh=inflate_symbol($b,$G,$Ce))!=256){if($Rh<256)$J
.=chr($Rh);else{$x=$ve[$Rh-257]+inflate_bits($b,$G,$we[$Rh-257]);$Wb=inflate_symbol($b,$G,$Xb);$xf=strlen($J)-$Sb[$Wb]-inflate_bits($b,$G,$Ub[$Wb]);for($r=0;$r<$x;$r++)$J
.=$J[$xf+$r];}}}}while(!$Qc);return$J;}function
inflate_bits($b,&$G,$sb){$J=0;for($r=0;$r<$sb;$r++){$J+=((ord($b[$G>>3])>>($G&7))&1)<<$r;$G++;}return$J;}function
inflate_table(array$xe){$R=array();$Ya=0;for($Ga=1;$Ga<=max($xe);$Ga++){foreach($xe
as$Rh=>$x){if($x==$Ga){$R[$Ga][$Ya]=$Rh;$Ya++;}}$Ya<<=1;}return$R;}function
inflate_symbol($b,&$G,array$R){$Ya=0;$Ga=0;do{$Ya=($Ya<<1)+inflate_bits($b,$G,1);$Ga++;}while(!isset($R[$Ga][$Ya]));return$R[$Ga][$Ya];}function
script($Ah,$ui="\n"){return"<script".nonce().">$Ah</script>$ui";}function
script_src($Oi,$Jb=false){return"<script src='".h($Oi)."'".nonce().($Jb?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($C,$Y=""){return"<input type='hidden' name='".h($C)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$Q);}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($C,$Y,$Ta,$me="",$Ff="",$Xa="",$oe=""){$J="<input type='checkbox' name='$C' value='".h($Y)."'".($Ta?" checked":"").($oe?" aria-labelledby='$oe'":"").">".($Ff?script("qsl('input').onclick = function () { $Ff };",""):"");return($me!=""||$Xa?"<label".($Xa?" class='$Xa'":"").">$J".h($me)."</label>":$J);}function
optionlist($Kf,$mh=null,$Si=false){$J="";foreach($Kf
as$ie=>$W){$Lf=array($ie=>$W);if(is_array($W)){$J
.='<optgroup label="'.h($ie).'">';$Lf=$W;}foreach($Lf
as$w=>$X)$J
.='<option'.($Si||is_string($w)?' value="'.h($w).'"':'').($mh!==null&&($Si||is_string($w)?(string)$w:$X)===$mh?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($C,array$Kf,$Y="",$Ef="",$oe=""){static$me=0;$ne="";if(!$oe&&substr($Kf[""],0,1)=="("){$me++;$oe="label-$me";$ne="<option value='' id='$oe'>".h($Kf[""]);unset($Kf[""]);}return"<select name='".h($C)."'".($oe?" aria-labelledby='$oe'":"").">".$ne.optionlist($Kf,$Y)."</select>".($Ef?script("qsl('select').onchange = function () { $Ef };",""):"");}function
html_radios($C,array$Kf,$Y="",$qh=""){$J="";foreach($Kf
as$w=>$X)$J
.="<label><input type='radio' name='".h($C)."' value='".h($w)."'".($w==$Y?" checked":"").">".h($X)."</label>$qh";return$J;}function
confirm($B="",$nh="qsl('input')"){return
script("$nh.onclick = () => confirm('".($B?js_escape($B):'Are you sure?')."');","");}function
print_fieldset($s,$ue,$dj=false){echo"<fieldset><legend>","<a href='#fieldset-$s'>$ue</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$s');",""),"</legend>","<div id='fieldset-$s'".($dj?"":" class='hidden'").">\n";}function
bold($Ia,$Xa=""){return($Ia?" class='active $Xa'":($Xa?" class='$Xa'":""));}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
pagination($E,$yb){return" ".($E==$yb?$E+1:'<a href="'.h(remove_from_uri("page|next").($E?"&page=$E".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($E+1)."</a>");}function
hidden_fields(array$Eg,array$Ed=array(),$yg=''){$J=false;foreach($Eg
as$w=>$X){if(!in_array($w,$Ed)){if(is_array($X))hidden_fields($X,array(),$w);else{$J=true;echo
input_hidden(($yg?$yg."[$w]":$w),$X);}}}return$J;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),(SERVER!==null?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
file_input($Sd){$Ke="max_file_uploads";$Le=ini_get($Ke);$Li="upload_max_filesize";$Mi=ini_get($Li);return(ini_bool("file_uploads")?$Sd.script("qsl('input[type=\"file\"]').onchange = partialArg(fileChange, "."$Le, '".sprintf('Increase %s.',"$Ke = $Le")."', ".ini_bytes("upload_max_filesize").", '".sprintf('Increase %s.',"$Li = $Mi")."')"):'File uploads are disabled.');}function
enum_input($U,$va,array$l,$Y,$lc=""){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$Ie);$yg=($l["type"]=="enum"?"val-":"");$Ta=(is_array($Y)?in_array("null",$Y):$Y===null);$J=($l["null"]&&$yg?"<label><input type='$U'$va value='null'".($Ta?" checked":"")."><i>$lc</i></label>":"");foreach($Ie[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$Ta=(is_array($Y)?in_array($yg.$X,$Y):$Y===$X);$J
.=" <label><input type='$U'$va value='".h($yg.$X)."'".($Ta?' checked':'').'>'.h(adminer()->editVal($X,$l)).'</label>';}return$J;}function
input(array$l,$Y,$p,$za=false){$C=h(bracket_escape($l["field"]));echo"<td class='function'>";if(is_array($Y)&&!$p)$p="json";$ge=($p=="json"||preg_match('~^jsonb?$~',$l["full_type"]));if($ge&&$Y!=''&&(JUSH!="pgsql"||$l["type"]!="json"))$Y=json_encode(is_array($Y)?$Y:json_decode($Y),128|64|256);$Vg=(JUSH=="mssql"&&$l["auto_increment"]);if($Vg&&!$_POST["save"])$p=null;$ed=(isset($_GET["select"])||$Vg?array("orig"=>'original'):array())+adminer()->editFunctions($l);$qc=driver()->enumLength($l);if($qc){$l["type"]="enum";$l["length"]=$qc;}$va=" name='fields[$C]".($l["type"]=="enum"||$l["type"]=="set"?"[]":"")."'".($za?" autofocus":"");echo
driver()->unconvertFunction($l)." ";$R=$_GET["edit"]?:$_GET["select"];if($l["type"]=="enum")echo
h($ed[""])."<td>".adminer()->editInput($R,$l,$va,$Y);else{$qd=(in_array($p,$ed)||isset($ed[$p]));echo(count($ed)>1?"<select name='function[$C]'>".optionlist($ed,$p===null||$qd?$p:"")."</select>".on_help("event.target.value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($ed))).'<td>';$Sd=adminer()->editInput($R,$l,$va,$Y);if($Sd!="")echo$Sd;elseif(preg_match('~bool~',$l["type"]))echo"<input type='hidden'$va value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$va value='1'>";elseif($l["type"]=="set")echo
enum_input("checkbox",$va,$l,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($l)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$C'>";elseif($ge)echo"<textarea$va cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';elseif(($gi=preg_match('~text|lob|memo~i',$l["type"]))||preg_match("~\n~",$Y)){if($gi&&JUSH!="sqlite")$va
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$va
.=" cols='30' rows='$L'";}echo"<textarea$va>".h($Y).'</textarea>';}else{$Ci=driver()->types();$Re=(!preg_match('~int~',$l["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$l["length"],$A)?((preg_match("~binary~",$l["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$l["unsigned"]?1:0)):($Ci[$l["type"]]?$Ci[$l["type"]]+($l["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$l["type"]))$Re+=7;echo"<input".((!$qd||$p==="")&&preg_match('~(?<!o)int(?!er)~',$l["type"])&&!preg_match('~\[\]~',$l["full_type"])?" type='number'":"")." value='".h($Y)."'".($Re?" data-maxlength='$Re'":"").(preg_match('~char|binary~',$l["type"])&&$Re>20?" size='".($Re>99?60:40)."'":"")."$va>";}echo
adminer()->editHint($R,$l,$Y);$Rc=0;foreach($ed
as$w=>$X){if($w===""||!$X)break;$Rc++;}if($Rc&&count($ed)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Rc);");}}function
process_input(array$l){$t=bracket_escape($l["field"]);$p=idx($_POST["function"],$t);if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?idf_escape($l["field"]):false);if($p=="NULL")return"NULL";if(is_blob($l)&&ini_bool("file_uploads")){$Nc=get_file("fields-$t");if(!is_string($Nc))return
false;return
driver()->quoteBinary($Nc);}$Y=idx($_POST["fields"],$t);if($Y===null)return
false;if($l["type"]=="enum"||driver()->enumLength($l)){$Y=idx($Y,0);if($Y=="orig"||!$Y)return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($l["auto_increment"]&&$Y=="")return
null;if($l["type"]=="set")$Y=implode(",",(array)$Y);if($p=="json"){$p="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
adminer()->processInput($l,$Y,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$ph="<ul>\n";foreach(table_status('',true)as$R=>$S){$C=adminer()->tableName($S);if(isset($S["Engine"])&&$C!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Ag="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$C</a>";echo"$ph<li>".($I?$Ag:"<p class='error'>$Ag: ".error())."\n";$ph="";}}}echo($ph?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($eb,$xh=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $eb, $xh) }, onmouseout: helpMouseout});","");}function
edit_form($R,array$m,$K,$Ki,$k=''){$Uh=adminer()->tableName(table_status1($R,true));page_header(($Ki?'Edit':'Insert'),$k,array("select"=>array($R,$Uh)),$Uh);adminer()->editRowPrint($R,$m,$K,$Ki);if($K===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$gc=false;if(!$m)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout nowrap'>".script("qsl('table').onkeydown = editingKeydown;");$za=!$_POST;foreach($m
as$C=>$l){echo"<tr><th>".adminer()->fieldName($l);$j=idx($_GET["set"],bracket_escape($C));if($j===null){$j=$l["default"];if($l["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$j,$Tg))$j=$Tg[1];if(JUSH=="sql"&&preg_match('~binary~',$l["type"]))$j=bin2hex($j);}$Y=($K!==null?($K[$C]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$l["type"])&&is_array($K[$C])?implode(",",$K[$C]):(is_bool($K[$C])?+$K[$C]:$K[$C])):(!$Ki&&$l["auto_increment"]?"":(isset($_GET["select"])?false:$j)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$l);if(($Ki&&!isset($l["privileges"]["update"]))||$l["generated"])echo"<td class='function'><td>".select_value($Y,'',$l,null);else{$gc=true;$p=($_POST["save"]?idx($_POST["function"],$C,""):($Ki&&preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Ki&&$Y==$l["default"]&&preg_match('~^[\w.]+\(~',$Y))$p="SQL";if(preg_match("~time~",$l["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$p="now";}if($l["type"]=="uuid"&&$Y=="uuid()"){$Y="";$p="uuid";}if($za!==false)$za=($l["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($l,$Y,$p,$za);if($za)$za=false;}}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($gc){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($Ki?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($Ki?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."…', this); };"):"");}echo($Ki?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$x=80,$Nh=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$x).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$x).")($)?)",$Q,$A);return
h($A[1]).$Nh.(isset($A[2])?"":"<i>…</i>");}function
icon($Ad,$C,$_d,$li){return"<button type='submit' ".($C?"name='$C'":"draggable='true'")." title='".h($li)."' class='icon icon-$Ad".($C?"":" jsonly")."'><span>$_d</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('+c0=@iDWB2P?H+.Sh=;^l:{,zS(WKASq!V)jBmWb?2$th#Z-Ku]_;>Lan<jnKeCU;:~t5y5*eC7ehsBn^yDY2;h.o6kG`I;s9C
)r`$=4hC#.fpJw%?YwL)mj-qNj1#L>,CO1y.amH0(RpCZ1G"yjmkq-_R-.iBdAq:6sKYqdsMi9p{
;Dh5b-]C[00Vuu~-r].#d<y3??SZOYfAs^[*sI*i3FAx("`^,&jKhl`PXm8rvI:]pZ^n40+0sDIxK.O?g3[wmh9(BpHB:oy4*3yMo!0/G2+c<KpnP[2^zCJYKEq/M@<I
d91xW})*U(1VbQ>hF{sZ:v;;4p*/Y6kxKmE%fSxhy
WvupXRo985$6.$t1.o`SEgxt?2]J_t&!m
?E]Zt/S7j_,HS[kyX/]xM0Ej8@GVFnKOjGSk
y[%aV/THZsqO)Z"R71/qZCM!|l~@kQwFTHQ%UU{"/q}>~(V$z
plAor[*UnR,dC4&L;+e?"]_a0o|d1GWOSa]03EO8`.lu`8SUn,(V2:CW"D@1.[&&R???Wl)G;rzhl_bkj*s.TL%l%I~?)=eys$D;!u^;)t!opm^d=bYQ2gf_=Ua!m
BU*,bQ)=omlZD&@eU6vVX?b%0.n%VS]GbVb;VJYrsB>Si9H;.He[#,n8Lg`K!`<WU;!GCGmP)&:>b))?bYiB$YGoJkl*#$`3+owpU)$(OE[VgI2*+(5Mgi(D%lQt3YS..0~By#tL6it7ta>SC..k{uX3e!U
b-.V"Fs/xILp&5s*9+U1#;Wtt0lPX]d=tunSSW[3+ql+{$RV|l
`fci"x]k!I.;Y7hehmY1?ss%ox4PVnT>%6ttbriZGLE2*Vum36g@4(-Q6GQ
?Ann.}2Pc8T0GwD;Y|l*UyML,N-x4Lg/)9YL<e&,518>!DL.Op[(HB$WhkyuQ6pP#oNIr>o-4<i,:/ds8sS
?}Z)wyE46!*Y
[*:up.)%Z(W;#ttO@Sws~L8,9DT6?O&+pF>+eIz4+t|(m]8E2ODd1"sPQMp,pFXd0aa:AXYfQ=0K$gm8[4--76%e!x"yy#|3TQu%nk(E%;{>g`ABN^r[CEK]GU_-F,bSnA:>yO?X+%
n#_Da(KuaFo1KSVZ[5M9YgfdGF0bN-)j`$E7v3RRGO#C-I@J$k-c]P1,mc?d0lm,"AWQf#>&$Ty0$x"LMKEDVb%Chy&<p{?lBx<8F:?[uNA$b41&;*EP.w18
"O1ep*n3Q+7>/0}x)y[wj<FWXOS7qi<ry,Nn(&>UV_*D4*i#/A#m"5*#/B&4E;[g<tAenG5,gZfDx5|F36NFnN?6oWK]"F}p0S7xHER;:Tk8{y2irFYxs7S-a>AfC+0Z6]DAAk80k*b<;-}P7OI#+oL^<D7$u/Sk-E%6[dw4j;pKLJ<6v#"?@]28Gm-pQn:#L;DGIqneW
P>kk,rBE|anI%2g%Ih6%KO0[CqzV2y(R+E!-&Z<&{l<[8gxP;-[T#=~Jo6c
&:9qs?*i%?7[j/,&=[)gR,31a461N4`sC)L9lUuh"3`;N;Go}s@6Jf`%ocVd<1bc
<2(jIBa*lo<SOL.W>UI2s<gfY8*xoboIM~RC!#,AS,FoR/p]3wEkm;Ga,9G)Pf+k
>7/VN!<tBdkcHodco#>/,[+DU93C+yJ&qoW(vkA$PNG6c(]X
/U(`dMKoW?*BBndz<2yS)n@ot:kFsH&kD<7S^%syOf$5ASlCW[vNq&CW8,lF0L:tHVWKNAr1A9I`]O`MJjNaP}?_3k"V[u6*/0
;1"I=RRs~F,5PnVxv8]?[9t:bG3:M>bT^/E.d*b*KV"h4p`9Ynl?j2WJ&u6)(JqcC.IM&"BX}@FTmA@A%#q$#AJlwv*#Pw3tf
X)]aNskV4H#6Bku5iZjHj^OdWH:n~N3-!7AK-H-6`h%tfV$t9vh=)o}%:=(?Qq^W{k)_#x?su7Ej,7ml7AEj6"y3#4v9nHC-?Hqm8raoE7g"Y(jE)Q/KwT:2{-G:b_uFDRl2bNK@:^{X^$OGv(4+DK{^#TF/;
a
#:yRz(<TRXj+ZbRV">7*,O
@UAr8E@=[`3#oD-?2Rt@n6u8$)&X^APDeZ_[;!EKqKswXFWo_i*4rKxA*E,/N7oD
xSxqj;";X,>Ak=p&9JgTTgVkaq!D%9U@#,?X[jmxZTRyDweAIx)W,qMm:WTy()UQ%jgZ-x37ue7kkq.@{[jq1P^uhnSnhy6WNyEmpPgJQm^Go23m_tO,Gn:L_B^wUx"a[oFtLM/n]c|ecn*yVF}v[by,ap5x{5.9F1%]EEj27ypKkK&KlFQ_,b[yve0XsP"cf2_*/qiuyLrIMM/7_lI]zz)0;MCFubqBp_bQU7MwVy)-tr%Q
l8WUIbF6)"c?SFKIXwIZZ|SKm`A.</ancccTY"w<.wi2l:_T&lww){p5CN>wGo_~Z"_$([qgb8A=H:XwJS!Kji[ke|D&+fTN(aS$xayFy=0/D=mXXgeDh2]ne=>JH3ATi]fj:;=N++#W]<4S`mXsd<Fhw]b1)aMt("Iv)&^6xo/@R%De
40s.d8V2$0u>*
5LS5BrbEvJB:G
QrG=bQyo2hKiSagWBC*[03iq@
;8AG{CI[vgMbo=&]CUH<2J#$I)1Opt;
6T_jLmdf=2?`o;ceXMwP>-gZ#179]@=tG1y:#fDl~DPi3@0Pjer:?BMi#3O-$sf6Y-10c<%b.Y<N]q+EwBy*!.u)_J/:$1ZjZmVc;jAJz]imwg,Qtb/S)/lX$Re17Kkyi`5%p*2)Bc3C72~KdCcBLgteSrJdei-TeF-_jdE>im]H"6GkaM;`]=iIA1dp`5NaSeBn%PUU.T$cQSlj3E8A8X#K;/ZT*D?H|Z3_vxvQ&?&OEDYaz0f8PA@]U/
HrU3]~c7hieBb{7/crTVx%/+5-*@S12x^^
Tv
NI5,KYeq_~a8coh/]c#A&jg"
&,b-vH%!vWf)Cd?u$.4!t;luAGj,?6:4#c9tp+AX9^K-Tsh(]mx9)5m[0[6`Y[f0AU(j(G;#Lu7;hN1E^x<Jw.H?@^wF*
E+>WNXA0zOIN%9NBtef`lb4:]RLqoc4u4UX7}S9M**i;G,WqWJil4oFtPVS%6d&,IlU.BB&6v0|ojAX#bG/)sV6mX<7,(YbjMSVB?Y;@XTx@>Rj_x9I4yB-4pjwniDT2``8K1q$fi>RudNs1(kY8VU]/x..C=[7:.o]KW?L^j^^^5*>BHxaY<2bRZ4,SiA:c
cb"61L;(Y^&fSkA,:+o;TAcVL-8ihCI^:Mm@@nB2.o4mYJK.Keb:h=@a?=V(&@/|rn>})es]IsX)Ijwn3hT24#Y@j%5x?:o"m@b}V#ncaM@*Kt)86t.ocwm(K5H=0XfdV"&vit9HOnYfYxKT8k936(jg`1`G%SH83}bA_s,&>u=ATHaA^]*/`W8D&&*<Zi@K]%lo"S^TQY%&yL%~("8.r{QK3QqW7e+y%(=)3`<CbS(mFiXzcj<i?6ql@nEHXf/eFxM)>)a~Nc2=)LnV^?4J+T-T-vw#5vt-RvC_*MD"d$?u]S6sJM2^-=0;xuAOQ>P
?0v|T/LfA,1P[ZK^[F3o]m,1LPkT`@,+R}%<RG&/l7iwR@$/lPC,]9sw9+6L<c%k2S4}PJUULEfX[kQn<"8gEn("A1v}M:;,tCka]2w1"q]]wj2Nd[x^TIA)G<(yw.MTtX');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('%OsbOb3V?!K0U*/_K8!!_q-f@v$o[s#eo
DBus<$lON[d8].,qN*m@-poH?R.tyR)O-"TS%KHdM%X3Y.^#Y_hHfaP;q*c?klD(H<QI%Lx,N=.GQ1$T#bV4tq#Nz%
?3w,vbJXqxP3M+9rv`E3"+&ta#-z:q#uH,gsfr;30Y
YD](8VM$vfC0N0c@>"2)GKk6Or*glU2;_e]
lG[qCHj,)N{dgvz5Bxo,St/",-!<m&YguCEOcf=QW&YqSe0y
4tMFl.S5
Q&<3MG&3I$EKa#w5d>?0G#ziI.mcH[Y^xh]n`F<v_9TD-S)yGMvrG^6L=^u]hu~T)XX@%sH2|^MUMM~_O=Lb/j<P[#8jzJ=&?+Lf&PJqQq.7RL#R!3,OUtS?m?2iGs<Z53YxESS/kZ&u!9n9m4#Z~@-oU.[]>QKtIKdd<Hl)Jt[fL#S_x"K+ANa$N"-vrNv

s;AjH"K_hHtyTN0yfA1K;/;F1cD>Dq?!D}QS[!.vMbxL7Yt*f95&ALDq5fB7EhPixM9U-DAhYDVZvb&*VNnYm(ws!LkSX1hT*rf?L?8pLXxiM@vwMc5+L:2N1cn6*pvi+]b-Aj?Ky>:w8"9"eJEDH7YPC/H4sp2BTlYJ4)>xqL$7MHqrU^F
Mq,+cY]Z4{i1WT_a+CI<OH2!jmJU5RI>@LSbVBv>m0_UI3wJw,DVQw*|?w%hHQT(fAdA:}Pu?y<E7VZ;o&F!SfCnKXXmJG3b-0(S1ciBQRFKE$/KyBZ-x~Y&F6),Vt<HA&pAezJiGbD]=9MElur7.Zf,Qp^{f?%4w+^N,&@4A@$CRbNWa
BpG&xQXj#<V0ho]GUh(F0y)N*XAG09Hi!8MGrt3p7rNw=qLDJ7BG1f8rSCi^;*wx=3-B=cetZY63!qW$uGUnB%^t^=7NDGe@tP');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*hcbOg~ZSGrK,tnTpUC"nSgx@i@Au14)aq,?AJwvi&hL-=aQ=P|#W=sd(@=1d0#u
.Dh9L{3$vyM[!L&}1
o)Z&TjD5G9jK5-IUAr/2utCtE`FD<N4e_mc2W<c60>hiMM/`qL$1QlWFAYW#hIG_m1*ub?EHk)ZQMp4m_SG[
{GtgNjd`{cWocVqWla-"!N>PM:dEf/.(cs/Tvc.V
t}/;D)c~39?dv{Bsj282IuM8,{G;"WQl/W_eX&aD)k5_y
U$czc4T3;7hcQj>yJ$-s;U%xdq6+SzkhO-Hnk]%Qq<5QZx]eb[O4Z6C~,~Q~s.K6Q}H;)71&lMM{o-Hqy:^9SK@+uAB"OZJL3B`Q3RE!6N+4TV,fIeY#Wu<^oGnRYUYlh@M
y?`|><Qvv/DQYtSWb&;LyPgjDR%gXw"j+mB*!>q:Qp60w&<WL~r;gk;_J.B/`FcQRZ:6EU(jN<l*tn@n)5A&d0i
rUm3.+_nPLoa88x-LN_OVr0~Z_JC+&U"(M?Dt&_Y*%Uf_R8qH7:["om(P&iLTuxk,H/P!HZc0wrd:^cw!bvAM)wnlI"SO,ZoETvlFH^LDa@MvlN?yPFmWlmZsNk^q*X=[zLwGNC%`-E]_NEjvNO8(7)76+JLJy-8<e]jBhDO@2xC=7R8TM@6J?u9Qb#%j{^7;<IK#0W<G|pVKBWgQ;yLg]wWoB*^@%6VxLb;3M$6L^)^RRB2eSa=n3,E,i<L>4@^3v=q,^Lp0x=k5^b}n=Ie8?v3r8fR_D2wqj6mKFyyJ.yQ`3E1GWk@v_BXHBL&`}U1g~>-f;La*{%g($PUor+!%UXQxN_)lMc`b1LGmX`uP+g!,S=n>s^yp~0"MR/6PhTCn(<ei+Wk[:Map~q.c0"A
A=WJ:JnwJVR
;PN<(H^&J"{4(+a%A_>_iZk(7<>twY{!>_5uvn`)zn7"..@y2U,#Y*ri^D3*4rwgjvsG=G
K
KU?CiYQhtubWZ
mCZo
h;D-BobX^u}Ce]Hs^iGfYZ_(DR/ndSj<"#iMgoIdP!8Qv#!7f)rT}NF*"NGc=EW<SxQ?YTP!{QkFpZsO$--(!!z*?R=&M]D"A(nL^h%eXO?MVb)xb,P_G2>EE
}A+T*s`hRd[No=]@>e"PsIw_3-}UNK#p{A+x8V"Og>tQ#>NYnEh&_4^Z2jUvVgqmPVwB>kW$lUOt:$qVvi?xY/k(65OPqG)dQ<@3xR,&P)+=[FN.Y]3/=P}dW4ruq>L(q>->9tS/eDEDlLyp}Gb)317pN5iApal[o-2&][jcnfv]/mYbbSN4gVhSU"q9!]+R#g`oNn$<e64^W$7IRmjWMN3+B/jo}eC:$fi:hPbIS>R:_oO!;IB[Q?=PYO0MceXC+khRL0!`$;"_vtXqx)q&dCB<k#[!IS2]n8uC&$|Lc$/4e2Y$H2M(*X$)r/Y2E#n9b*eK]v!3@lL@|yxPpGa=oi1r9JM(LYDR8md7-"5cIMyxRupBVm7l5->FpSVGsx%5T3aB,
"=)&785(~IP([%Q(MF8]<*5e:lD9Z6yGN-WlD?""i-|
Pj[MpVT_;[Wavge?K1}&>3uOI&_
]:SS_K{-n@Cw7u_uV!lw:"}65q3f8taY:sp6]-pT$f~L!2`3H8=i|T}KuBVTH[jS2n6"Y+Ru>.l*U7RQ/L&bNcAbOlHiSsS1b
Iv(j#Uk=Gi:":$pHNLb0LgWviMY0J%.15D]]h%,b[8q5fOO`8Vs?XF-pbs7gvl!8?]mI~io8/:Go635o/Pek:Zr5bGhVdD3>v:ZInby5+^n4uFTkcE7M{<R6<3F8^7-jJGXfDw_lTY2JsY["XWX
!OFHqdDwHkle%PL`j))S]oO1@n-,h7RkpF&p{oDj5vluaWZ+i`EBIw|+@wB0V3kJ8X9%K&ko2<egaHP8I&x5AOt63:"bbNufQPfv]#C[VQuio(MQ6[e5dYamQ^:.L-ctoH/[AN(1JN.DUx?E6Rs=<w@25$)#V
Rb!17>UXQ^&ZSL*]OAgch/phl*)oEiuwE#PR/>[NyOdC?p?oml,:"v:0y=]VUyjZH)*ZqVr(I2a2_*PH<<t*8J0I?uoT@/$GKJZ.]fO-60`@G@O;lDjXA-bym
OH)usO_ZsVQtL=!.d9?<_CQqyh~HI9q1",u.st#Tb!7rHGg4"1kZ=n}vTh_riA_i?c5K>G5[E^`,gDr3EDy&#+GkS3Zd,E&Q`<1J[XT+s7E(?5|Pl2?X`VWVgwkKQdZ#IFJ_Th[y#nwmOWRX~mceTF-<T,S_j#n:?V6N
c=LhNYgREbxRKDP^Kscc]@d0b0uyQbcZ$*[qL@hc-Ai_nocNGLmvbq`O3YQ_=cu6j$xKg8[_%sP$Z@rC&[Vg5IJX"H&ow;p:mO`9%YDK&r-1-d$ompw0T&IZtswB8!W1C&f&6?:<na?y7>lUViBZJm3CFF$,kUV#UzO,9n"j1dk<I%m$sda$!Ta1%w&4YAc%])mQwgoh6xl@FUGb6&
p_ZjaCj)t0:5$!1PkoQ(T%zu}A?Rd
/dLn8!_JS56"U%;avgyU&fu#K#$7zCyk1n7KOhq7AyW@Lj[$q(noA,VYWfo#w%7b&TJ8r1_3V>)PCP3-dt>X!A|sdMBOI1odddY;v_F^
C%!L^kYV4`u#I])b[u(,?gLp
EQNN/D6I/22OA?u!FnJ[KL%ZH#HHICmia7lk|@)9--I3f6xL.I
whcR_g[t^|S)2h!;P2R-_i)CWVxM@?OCdMEvIqADhOJQkb6k=9e_de&1-~2nu]d~XN1BZ.QX-{]Y$OIJ[@2!h,R_">Clt}X>,#b`Kmpy:p?8bBN}Eaq7dKCoPVgwxP/8*gTDfL1z*edbM^<Xsj.G$x=YcFxZUQh-D0)]?$rL2DBLh5/F*7Sjke/sTdWR8Qfz<S90z!VR1AyQg(]n`UoLobr@w]HPVU5=HD-0=z#vwcMb$7k>/YSH%&]U5{jFfu<4fFYLu[2P8mI:"]qJD~Gbj_nV2h<j$ud*e8q*OT8Ww2V1L
Hr/?Jj>UR?VZ7:ve&e#0ssVN!)Y`1ZQRl6)Hx
oU:%6Vsg-Id.?va?A7x`k(aCu+EE&3%w8
p[Wa`m?G?hm9JIyi#A,-qokLI9AQ%*[V
=EAm|kVKu]|IL+:6
UVSum?[c,@GOsJB`_$"t/{@Z@{r6409|)Z1#l9RSax72ob4^k@[IKq$cb<?C=7NcKe<,CAnr
%rtr%>>%v.]v:^QtZ-ACm5v
&>&`Iaf:nF7heJs454xBFBHt2`f53:.od>W
ns>AIwDM"dNJ3y&!]DVi6C90xsme:c;Gu3"9)2,qMJ8r1TSHV<k[}C>Wuu!7iw">^UA_-prB#:ew"
q@FEjt#r|flmYy8j!CMZel#(t6%lvF4o]GVxqn&HRp|4RGn*$<d$;jD`fa3c+x]x9-b:W95[G6^p4o1U7`n.Q_pZ1P_5awJZtNLd2IlTbBYnly!okK|ctT{%O>z"Re08tAM&qOv<n@X`@oH9kq[R47/?u6l_d=a+n<W57G`^Kl
Y7D8(N?>]:%$`SL
51g|"LgKMMw3v_9JtB0Ly[;C9&8QT^-px)Q|4<yWO-N/[qWAba6`jiC5-69LrZx?H*i4iCPYiFHJ!&rZ,qj~@AX)3xKwtjq=](?UEu%XO|_Ww8?cMr`(1/w%XmvPkBQXLw(0r]@,w0Hx.v;5_.^bw"KVxK514>2yy|y:It9{dC"U4=H/!6$zbG*=^P%+s9:Vwck_<1&3dT>|T~UoJsM
P*bgL`yS79s>h"=`oAxd/YAo^tUsju*.-f1(Yywbb%lJA3!Rc4%Y+}QMax(#_eW<FZYqLa<409s*6]w5eG!Gtnt.;@FgF)DsHd@78,)9gA
7dcS3Z{d+;@B3_yL
k
c$M()qNM
W91Sqg4)LL%##W5U$U<rJrqr*QA%!9UOSPkUR0qY(lQK>6FP~h6?%w#e%H+?s7#.n(k_C]He*lGWY=J6vq}cM,(MtT~K&"9#*:R>xd3u)kgJ$fn2V[YpwTBZ65S(B@[v!ti!~^aW+IRy;3m2Sm?@>#_$]72nU#aR~mJ8B]P0R(Dvhr*`/I0fY[eY0p?_Qe<5"`T:aD=mcE2tFtce|yz^"%t/DftQ0*/[G-58uN9hpwqh;RLk2@?!4I(8r*R+u;Uj^LTwI7^?,pfh!A4S
"8[Jt<R[0(o|pv$a9V:-H7UNxJQAdS&!nT"H6]3JkfR8nAp36"XbH[8coaHzZ
%-dU=0gii7EE4~"j6I6:ql."BLcYG61|.2eMu%wZHomT7c#rFZWX@
&ky$2>3$D7GI(ayW3).XbnpW))@~,Qi,KnCh@96K)J9!p!+a8%/1r[/2Mx#m(H/F&.H]r_CYf:KI,u2P*w!yl1.)eCIFS6fyEZv?aVr.)GXR*WUKV]ha,*5[(~Kk.@A$E2"8lZF{I(yKOvc3"
<XCsIObKs#:~"(y5Q3oU,jHNhs/g&ZlYE?CD>%;S"<KUdIkf.Kq/wB:}Nnj>Dd[Q^ZK9utoZ)"70=)Z,q?R"._JNWO4an][GgJ<
V@)Q)sf=#l8xbgQ[[|6MG"_cgc;l@*Z"=ofpo-<B$V<DKM9lcEV/4gt4i#r7.NV$DqRb_R:xJN&>Vl,K=.X2I?Y=*BT9lE>{<v@#q@Z7w*0N)1Y/DEG-)X-^#AA["d05;tfrbd+?.Q8XuW@@<8q^lMiq5v>X>G/^hlbFO=v+
9+xw`-FxpS->2+?KsC=<t)/=EB373&wA^@Fd,O{H2Cvse7<!ZsGlB03@dx1<@ie<}.)QVmEMg8"jHG@4L`~qyJ`O5*g^"@$"ngdYr?I+d>.5Tea$Y,*E}JH.%7m&lNXjjVJ1X8P>u!90xce"xQgokpN/Hqar6WU<GiscV%^0No,:R-Yo8YD9m7}a_2A.SBn:L9I31tu^rha8;]._G&h("Reei=?>}6;QFGQ0Z[@7pyGe6?UZ:R~%[!h0O3E8P^Xc53za-P@VW_e!<#<gB(gmW_!4*_Et+mo8RYs)<tyt?0yABXwyZo+UZVkb94~EC22R|OHh=dhJLqo*GNbIhPH!}<V#8REnu(ntxX-_|c[ELBghCB7/S2YY5(n
9f$8caxoNFWNmIrZZ;@+/7#t}ICS^Y1G/U$
F<yu04AOJaJJ>=Dj22n,[.KWZ$BG8t&LRj[$5s;QEN@--.%evg!-Nm_1y
fdpTaNo6W*i,%($b[lz<Z7Z>1()ubPQ+CjdQ:9F&1mBAp4no,ru:kJBaG%WtiRD
jkg[5Fe]0m]v)X4p4AxOPBbOL!?q.oLc}@2xNWrV1jgD,@zfSp+P`qk.Aq,#w2H@!6S>l@FO0Ftu"WJP-P<fW2r+et_#>4EU
@/o%A,!v$3D:!Q
Q6<@|My6C&T4jtN)Kb1o:utHI56Rr>VnldMJ=en=A_*8]Sbh0W6ssEZ^~4C=]0s)+blaXMl5mSQy2t`S3mGf*^,N-h;W6>?m8.-goKWio+O["xoQk/8.So[@zj;&RE0<VtYAv)Zc9q<&a5Psuqi#kj{_dVb0Xq,Tp
gW!K5kMM+UVoo(g`a6>fW#abE!6#GWIHuXvku474|u0-K9-2B81Gnp7huLUf;JR#uB>P2pTQ<ZE;#G@k#.9$:,q3@BLo?`C7m8RkL@p@GFXiOz!l9QQUn#]i8`_jc;i0*mm-MfcXD`-26Q6/OopY{
ocv3ewLIz/#
jL1*C7u,9VTt4;^e[3GgteoLvZJy2VPi92_YWjSp=Z*=I1t-X+En*
oLx=9fjbM<#6N+GVLfb7)D/6o^ktdRx3?ggF|=Vkq2=G/cr8=%w4iN^`p_}!:Cy?JSxWDOkfMCZVpws<)R9*MazkYgn=&g?_klr`|M4V8Cu@Mw<B5<b[f%ZQA7RdJ^F@E!Fd!y$yzwIw7`RANZIyaz)o`U>Z>KL6Bvhb:h):*UZw<66yyTBz%*-chD[]}J8qA"p
ihF8>IISe:P/[JPHd<d%ggDGnVae|^~HP>.i.C=>"`)a=s3oES2`ce*6e%~DF_85N>`KnYk3;tWUOIlCktBF]0@GDt(uDPf6q0*3fyZJxN%7(5"M&DOPt^xw)OjdK3%LBl#doK%E"^a$3bKC!SJSw@C(UT`P5q,c[riVzBovSW$13+`8RX]I9)m^8d`vmOU&>SOAG$X5Ie-/rrZk?el^qs[w`3.V+m?]8/ZsKOvdM7$g2<UXarO^Wod(
Q`kG^Ygvy4
ig}e$L
h>Ag*w?_iqYCqX*;h)f>2WCwR*x*4xxIC0!iO|*ji%_<M5Dd&DtPV?WMF!c3me:n>nFn!B
bkL>~>c`4?,RY,JH+=t>^bU<EC03FS0ll1~(fH*WxyvYV2HyDL_L~>Tnc<SWn
0$)rh@=-[8$"5Iv>ExP*}SnN_yiD[32;jICSa3`aJsM."d,GGOu%,O+(X-#cH6f!eAsxgDaLvA6DO$!=;.0#=myh#F*[h$RY;)pNAa;+z^c[G][_m1335CEc^RO.PHciYQk*~eyDrO|O~&8r<oE,x9Ae&NG&"waNbDVKKW1#T<C]Qk0TK9v9t!F[rYPDdX>9|*!F4M#ZVcGI}ot[m&^CA#5`fQvHU9v7[FDF,XMG.i#gfPBD._~RfB*cKT-p-_J?pyxu|n!`,-,J!h}s}BM+AZQkB:FwQ*6IFguV?FL#$kjA#C)iUv%+GwtFg-:^9D^g4:D>TQ5EqTgjk,us*s-8$P_e[+jH
HodDUf:VXZFtjJo+rVqgk<Twib/*CnP%@lP2/6/1%7cu?`qA;,i-H+HCtSb%!YCKfH(eXdQ"]9l.
b@HtkN_PdQ1`n12_dlR[6nURroCMzCBhH".XS0/`2BiVia^E}FpRrP=N!>L[n4^X0NO5e*hEXLU]W_DIc5N;x(@!1qhH8$p3Kd,oU9#ftJHHx(KXe[OsyFcW.hl6KQ
OE6h;3/NH<IlEJFp">]24pFu23,GJu^trfRi#X=)>"1U?kr!k#X<ITK-c[5w+l&TWu4s<XJ8_W]w+LFJ<IZACA(f[Z&J+g%CV^xf!xR>TKZKH&@T<V:@^KBGR(J(;_FJXMW(Gjg=:Tm@h;>JI"L5nr"fL|$~-P%;8$-:-0RTHOR|v")69D"B%E;:SEgNtUmYY_g9NpA:JS1X&qAJ]xnIa#,y.mgW0LWrF:s}9-M!JTy/^f3-II9UWpW__2L7^t@(tHv;n5s2!/]H
V]>,+b[:&t)eu.yqgpU1Y@2TTw*8|MXvL5"/7b,VVF_AotmU!3hRC!`-Z(Ym7BOsu8*C*I|HR#`?}WBSCL#wWd_4r(`E`ApcYZHZLi?+!YbLn(0.Z4xLxZw7KAe/)6Y7Tvm/8B;KHJJ"}=,mwS4##kCJW)50f(.vqk2Ev,1cxIowrE]]*Cf7;_gBlZ"+jm0iiQ$q{1A2DoA>tjTZRs4+-:1W}"d.U)d-AX1
~WL!}j@vUpM0q0XInBObxUWM:CU#,D7#(6ieqiyiCd(,?#twIoZ"XfQ">c$5S3&Qi*q;N!2nQCgXGou:OIV4>Vn)[A@_H8z;h$Ho1k~:&KSh}aJv)O~&!&ng;^lYsv9-Wa[0#`<=73J6Fso:)*X6LX^5}K&/1YUE{E;+`*/Hxc)4xHlBlYte<5V!
^22pNMYU>3<;oxU35#7<uMAN_wG!wt/7HYNYl}0Zj5-{J$z&^de!,2%Jh|[{^dyE(b+Q2%P];SNZ7eZw?2n8s`!K;GYBS/T>Eqk2FE)1FjL}ZSaiR$9w;;pqiswJgc2zYvDNa2wW46tq%inPQM"nG:u7(Owy@.C`-"_T-SWIfF>AIFHQpt@
@j2[-
0V9*D
l0CR6d/hLMyDN97~r}/Lt-VdEtFLvhG)Csoe?`[V/AHjVic0!7:TC$+DAV6<)PG|0msR08&Qmo1/``A[,$qp^WE6dN[0eYH=lX!o/+*lfOh)$&tfVHg4=(4*ZoMObJy#.<dVjHy~41tpogC-iMq7k*JebE[Z&E-2V3K?+!D,$Iz)5Kw70-=)78vU[s:z*U=|gp9tJI)P&^2i`<P;rB^"@t)K)Ru_Z<KJ3`U"gk*-7e/6EPuvfR!u@sr
<Xi+pdmFkb[eKS!3Tik`mLUDbF1$yM<3jch#8"Z:gBVG!EtLU}fBY*,<C->ZQ8lOfF*<3f:[mkKn50ue"E#,>;>sNfAq*7Bf[o3%&T%=dFqkb+Lm:rrO;G+T&4xfVGm(cX7fEA]igLAf6=1MsK;t#u3).jD1e7x!G|,Uq4C!8zFvYKiFA!fw_I*dZ~v<F;U0`//;vpvw.v[aNSktLX$t0)+S`*=ai|7[!Tu;uZ<lq[jAW#=,<_Hthx,A4
qa&p$oo@s&6]rr`=,BjobTx7N1.2S+xR8O)9lKh4ANWKe7j3r8gDBj+]0gz(nMfCwn4dW>a1*B*S<ne_clj$g@S|V^pWq]"]1tT+(!cH;?1C[.mxl?*{am?zA:!4+~1X$c/cG{a]tUu@w;oZ(Ag$8wNmTJJAKzX*-"EZ.!jAl0l[9]&fX#j-9*LhW~h~StZ{U]E{VG6F]!39?_&|9Gi+-#`a<y<!T8N`Z/uo;9;&cSudlc5cboHLNgHcqRls0u_`qQE
1-#B&0p)4.lT@lFzC>b0VO#Qan$w+-HYu{8E_}r{VIN/MQH@vy7DT4f)X5V"<sk{*Qr-jOY/:j[9H^`+kSJ!WD&KP6uCVX;wCnaG!j2Cks?T)?4yMB)d<rTdej&RH9MqQo@Ksje6^!u92To4:u:#,%@]HcfAwAJamddgmMMsOe;4G_lQ6(-[CUxS`3mQZD+zu/8~f"^hJdt/S{O=#u4jUtdMa,qE7;z%x)E,0._O_HV@>Cc9hAv?@&$MK+Des8&8whAeJjWaL8w:VGKla`i`fdlK]DHjG;+LSMnQj}!!XL,s
xtpSFyVfYb]I1p|`?rR7GTvxaxbItM:2Qnoy7e;=)5d[Gm{;e,(=ly8w=sgk2XgjxUgO/>i*sw/k8WbP2Lf[$GQi_g@Vr?9A.".cL1@4`M}!exr])@[=wMF=dK^l!oNWEYvW&8Uq{92E7Tu3mx5[$Ev)~c,#/_rcfHB3Y^M;s`[iFQo,%qAc-p-lag|ls[#:L(r.tS,/P,X$cjVFZ"(Uu
2*~yYCpY>8,X,uXKhBZ99m444B1tvvpwjCB1W&.LPy:uqcYMN@*s.(f_uy}mX#zd%nMD(dGu6^TM^Z/r:F-wM=.F0cs2_$.z%HtEEAvUiTe]F-fciw]n4rLC%i@p*hfc.NrvJFu<Ir&1JOp]y,!y|e*7kQmx((-C%sXxTP,GJ_&f0I6teBy&%=)nV8XgrXaLd2[2~=T_U7F16aM>;k{AH(%p2#%(7P;ofRCY..CN,,v]G<gi[vfBYY&=:tSz"ghtW?kaqc?yA4BoA+JH~A&b9-q>pNSHrM?7!eiT
cw0Bx_A=G/>p@%bz"!L!_E(1JiW[OED[H/M>an8#svP_WSXQUdGrq}L[e&@*H>c%vdIQ?6toIq=YuzB,c]w_m;Qqj##:$Itb
qAIx%Q|XA8n60;eo/s/0Mn&H4,ISo
(v=7;.A
{x&<.asvm/q#5;YJ)UV?%]xsYvIl}WoB!$_XmRiW:O]jY6aS)^A.e`&Ln9a.%*)LzC~SSu4V,sCqPvQn"YA2lDfn^XdY&gjcUZ.z&HSb|dd>0@l=v)NWwMh9r@4up"I-?q-E:Lkb-8X"CE]7;"^dVFt)Q4o[i+|dae>ds%~*Al]z$TzqTc3vh#w;s*7oj>9hpgmQU.}C2N>L2ujH{7F.pBDT`)Z$<n%]3P23h(vw:Bh3:@cqtxj$d$5gZD+!dM{d@X1O*WcBLb>r#pScX"bHAh$]rl7[VWTv6ALR1hN0lr%H)jBkz9]wy=V2nZtP2;++KwQ&_M2v#Edg7X
r0C8hK&UC1fKgPE.J4yMGOFpXc3dJWL?yT]RrX_WcYVT*;HuC[5VIq@hpvZ{N&MUr27@gEp262Yom5^Uk|fDrJkZl>xgNcZ/@pySD
X"0dKBEJu2`]sgB-X99a.:3=-TXw!R_^SPkaXgcm?b+GNx,Hk;CotP5:"Q
-Hj31l?>-qyZ{TiIZqgv{?2D2K%?uG7t;f
7|tzSWx|EyV-N~,"B*ZYSSW%>i%(4ysr:_p`PylAoJKbxlMP]Q$_p%hWYj!W9DTydG*j"i&1jAP!ZvwR6[98KM"|3B:_;yZ_5Zz)0n*o!HXs72jR4Wt0,""Q*X?9f_aFLneWK
TM&S$cZR=>"6B;2kNC*1#tL0IWM{;B2t"1Y1ZO/GZ*=oLBwIK4JLu97!k<FqAnyD?VDSvar!-n-_@mLyNiU^T_wleJ983aIRd:-1bEdE%P"H"CGA!N,Z,m17jZH2M/($={H|$L+xEV]yX>h,kSJ!8lYoe,"W1v:7lQ+Qxtt:e8OQ%)&u$Hx|l6izXeiy;A.Ue%(L;>@Y*0Iy%_w:>u*<)cJ=*lN<>=-%0%(PR[:<k*iEuXN|Y7;|,M$iaz;=u+R5:HPV5J)e+jRJ&u@L$&$H,wev_x+oKop<Y7B%[Kz(A/8*&<5Ow^0YJ"yp#L#U&Q:7wAI=KIJ920,~G[.+z):(U%6q[h.Q7tv}"v%"UE?s,yy7^|K8I}n3rVEbD69|:;R(_L!R(Tz&ZQ74T!EGFegQhia%lmxh(5@X@/P=e*]XKiNmxaPeKdE**El.Y-L$a=hby|&5.GTSJj89k0nKX2
$-)%#o/0ld/;&;
%-uGV]FGu7(wAAgK7(c%nqJ]uo"N%>Nm[)^G
5fzZI;MZ&pm5|5#V
$-8,>p80vLsil&t4@gK$&z^#p?eH7*Iuw4f_*1r/e?6;:bt:M!=P[Av{j:gT]940n*MuwA');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(')sl]XsBZC.#vUHN*,qp=UoMafK{75R65]-T1<aefyEUSWR""g^[N&)BQjOz</n})$PwRZ#isHnUbeyBg5Ym<{7G:2e%EVoH.ao%n+@9ws"5$36BGTrFt(vCui){1BHgei]jtCqhIV/*6wbl.i7prv5n%"q;^By4^Ei=uiGXa1MI]On"r2]lcxso:n;tlO*"i^jt/YCguyVTGpFF[L>H<oh7MYnNShIwy]Z&c*uTBfGSB]uuvLxpv_gMl]<D>U5-7yD%2CkfXys~iAd:&#od6)0njbD7>
%+pQa5H%v=!)F=Yt$IJAuze~-|*CV#V2xGHp]~w{XEUc
_WKg6p(XTVJY[7
r_Yw#CnIKC(r(]`a?&;Ye;Qy4Z+BY]t^Ia(exL2iUfilPWG9O&4nO?7tK0N#a}2bQckUuw3"MeRKFJM1H0:p$P.Y^q4x0M^p5&sqVO]KhlB!eHU<YF4b0)^GE*.nF<fLV=hBVF3n5
GoiBIud|U0u5&HvT<3>-*}C]d8,zWaT>(`Xmp;MU@b!AS=a7!bcD_{fPpbjcMPElW,W-k;@A@$80eXO16?Hq[ira4rr11uwwW&FMB1Fpu`>aUE6"un-<`I+DFc;^&asbT}hYfLy5YeeCGp>]agt/J+WaDXmkU(Yt/*a@u@^8x3m+UYX3cxP]-ycl!>?"-0m+#>Go+cgX2=XAHZNPI[vk:LR)k`Hn%G&AJ2x;UEs}!.1}Qy;.b)jUaH%uMbwONR=.E*q4Z=m`KP2nCEkS>a7hXjq!@W<5IjdQEijE>|O?%%DyCpR$k3Ib3g[j>mDnOS@$G_Z^U.JYANmC%.Xm?|kIs
J&IBvpYIY[W=eG]E2Ck+/MJL]$[+`"v63sq&]qbc_jx"yZSoMP^Sg<y]0ZoHp%`a[Y3>jbv_4lIKlPM+(I,UUP:OV!bAo{v(2=3WSUx:F:b/7zuR1W`&Q1iICyL;*R&vJMtk:x<vV#qa0;=/Ab/*Lt/0gal09[!Sj=K-ke>"ES58mlPG3LO??aUQW
6R_?*5xg3v5e9irG)]q9TD,;k(-pVVu0M!o68naEd7+SuJ.{(U@qS,u.Xn!bD35.%BGs>tmP;n[E`Vy]p!*uddOTY>m4c#i]>HHkf"b.m1gDr3II&A`Pbq6x,4CP3`p<la=?:5r`X>T:?[;?"XbO4Tl_uY8{[-"{KrgFa*DwX2r|a2*rbz^<x"pq@)Gak{P<D`?S%pm;D]=ewZTo/;V&]WFDe$w;r~W2y[*PtdEw13(a6?uZhEy^^*MQ?A.r4u]ee5NgY{]60>DTAQ,#fe3`&.*"C15bAlulQ1))bzt<QajY8A@0[
b[unM%qu58E=W(P2Ny&m"M6~JJL^X^n#0[>
nxDcKJG>7^]ILdJuxTG(Hwu}M7[I/<Ks&&o:te9J*?mSDPZe8PMz]3avTB>|3!YD^"Kz:E%K9n>&V{KDl`_y]@[1_}0,eQs:E"7^DqigJDqKrH*[r1fkDj)br8<t-G`@[=wHhX`6mr>rcBMdeJ4{;Jp_MlM$%+YP=W.]CT,Ya0^-Ewh"g%BGwfG0A-r@?U9;_7jLk{*p.&]{^OS"trO}#e89tXnn[KB:Vde5QsjAH)61Ue9=^1fIm0WXT`QuDCVLgk;yhOX6)`#tvy&ix.N9K`86R,nsv-LVNCNQc48cy{l>7gV:]z=w8.IIu]Zi#u)d,Cd2Rd0Pjk$fc^/1
F%?Gv]S4:Y$F{($"WLWu4rS5mHlNo-jN`*jbA7Pc=#)Xb$mgM=+iaK7:p]N!)8"XZsBK93E`,S7r,yhXqFz?9.73j_/jpu_mG1<iD3F^UXxac.1gA*M]2pY`s
mmNl})f-p[F4QRo?#?DR#_qLU6G3"wdT.IJkynj!k](YtMmm/Dn
i3VB/C1U5mtwuWRA/>uKsS|:VkU[Xxm#{](
(509xmn#R%>2{T]:U,3,%C_pKcQ^He9WTHuZqW^LHp1Ew^==P-AksDh$_s1f8#MOmD,50/=<w<77Ro1jl0"*t*/_kp2xl
uuksikbL^0BC[0GE8V2!SW)SB,*LupPa3+jZKjY0_ChuJ=v)~qYhxGWqdESYisKqJvNM109HtcciegeoNTWWB5x_WJh8rYMW`;lsjN=hnb<F;PWdQ1Hs.E1h?gWn-.cRTmgIQ][*y,Ol:WMwP7pyeT2tMw;O0,G`Gj}T>4oUj6Wc<Jsl),<xS,Nkf.eL|XghI,Ndbo$X;Xsx
3n/62@`{KxxOL}vuKyW~]eJNn3H(x)F&5vY@^&6(7lwZ1SalrnW*siU0wP]!U+&#(vW6ea#43anIw>K_F%c11E_I/RnJ
Q1R+NMIvHMuT}_z7w[GvhAg$}.Hv(cTxdM!aj;^!"atPpGONz.$=_b{4ci`ufW0
%mNggL?%BWZ`$V)13fI^%JI*L8Pblh+
Slp70X2!Jst[XV<evFiIaFLQ8V?vl,JrM`gC,_:(Y5SO{Vj7O&Z_BSx(?-1KiXlLUpB/V"g-x
U<@l*pT>T_%F0@8&pELL?aIA,sv<tS>>Dw[u+u;Nte#3E2Us)!u#jpm/-Nw>r210UGZe%I8j55-e`"74+UwWVsgihZ#(8?jg`wo<{<;f#;.%Me*Z?/[Or2u&[%KIRXtXf[
9nCIne&1r0"Yi}WP)uwrWoK/mPN9rcTD({,^3m4V!yQhlngT[TFU3}VT:@7tiJuzu7Boh=)3^(VhQ#LGZnZv9]p-XY"Zg=w~H9Sr<hKGLoK3xo-9=70&Q<fw0".k9!5?IH;k.Iv-19F/Hxz(6a8at)MJAc,
DNA8uD=m/|MAf3P`d]cT+.YS]bcs.XDjF)<_XdX;1o?"gOi4>!FxMEAckS
O
vXIrm;I=3k"6^]"7ghPy&=LE0EgylV=1{_e6t5@_}#tr~s];"DYSiOhl1XK8^A8c*3(I!09a%FwcTm:?8gfg:+!lm$pN{46j)::,J0>T.PPF4u-8-m7#mmmuOv{-R;m9ld
B)kl+&FWlTe;@13Z
PW,jLW?GwQh7kriJPcZ];9z0a+{TDrYN9Pw0;#*pMaSF>o]M3#wM]
pN~[x"[!9urG];5c}@_rHCknJ$a_;Q@<7%1Qb*RC+vs$3q}fTJIu#JoIIUCMR(8gp"W]ASr]ung[UPQVA#2?f.HQ7H0qFN`:9trZPO>BrW>gxjCgGtB!XfF@b9ZGH3&%::t[GT2a.:J7$`(!b9<Y+XG-EY{brQST;U3&~9-O5rQvVFtwvY/0g@RD71HY:Z[fmtrV"`S9uDe")-Hb=7|Q})5HZiz`CeI)rvGCzB4s^8^(l1R-y$ev4(2JS)C:)<ZK29;,Oz#;fM/xfYsv&"7g-3B@+q$2N%w-kZ(f^k@w6>-ZGYG`!dy6&^0s/TSG*J27j2DX{O#D>O4"ij;/va-3Ek{I,LRBWq}B#s
YFtQIb4{_Z1CcsQkuTr}0)+gmABIRhh]LOXkFqsZ
Deq>XiIklAfEX>9^pj6*5(k,XQ]_F/U7SLiXVSq+Fh`+_xAN3M7K8Y#
E5l5~
Q8N3}21AS-RbXC%A}2":9Q(UJS9PGZ]Vc(@_d>Y"hV2C;)p5N.P0-Z15NrEPbpRIt7DfLAF(vQEp~R%qkA}#~>5fzD!67+"G9@8m<>^c2g
%ZG
lKYD[#P5l,!w!IZ~f$`W%cmJ^93[Wyd(`%q}$l&_"<&~UG@bih/z>(c4K[f"K0-Fu}xf*s,DH:IFIk=]yw:945]oZ]RL=7)dC!H?0Xb]U97`)Mo08gkJ+SQ9$.XqrqX-x.]!r>v;fV.jRg96tBg#55kI.r#`+0Iy164h=&6s>-V}?oc2^{#)C.Q~1e5Ay|1#K~Y]G*-AQz#{UmPqr?1WvN[1Y+dIi~W|i>
:$87kBuQr&)0GP&&Xq]:mYY
c-{"4LGFG8_;#eYGL7&g1
_)7[gqVtDB7S*>Ub5AE-?^]l]4_b:`7^Q:ndLUl"~<Wv9qnEq)reGYF:TlN:ivjx(6#6gGX=/>$N;eq=C@n#}I}Vp;Obhj(,foR(fN=w1B`&lFhm(Az3.OH4C2oo&1Q^D+5&-fm:}2^(zWI`K_PmaPOin+)oYk#SwY2lxIWW[y&4Mv]a)0-[^;Ou,ZHi{2Bvy](e<!32&2UV+5`uYy<X]@WW|xNT>xq@CZw$1Td3{mys9blfA@9cRQksc^6n
y`HC8pUuk#n$Xk1F/5:_[zXf[#fcJ{,6NyD85qWAqI8jpJmBY@G;w/nnVibNZam64$37?NV7_=$How#r)1v5XkMLYc;~Y"3z1zsd4g/3Fs"FBX1N3}SHG{!I`Q<KhhwTh|jmapjVUe/
Nv
Aw.5zy95ix0MHL;2h=^8#xEo50/H!=P#]1`&Nm$"w(-=;rXuVwGkN_EVwNyE&C`#$vrSWg_YtVt.IBqv{EQAU`$V|5npP;sL8hW[Ql_T~y63yxB;k0ne]l+xzruo:azyTa|,<:lo(by*xDD@6^1Z/fM_&FI#Zo@J.d7H{Xkmf&u9+8M)e:ut3SwCO0ZmSq=e6-n!JW{wXh:MY2>91OXGdq>$Xf{=7;0mR.EG3-V5j3,fmU[EheJB$RC/z4yl?B?&ZsP_g
o`,_x>AY.E@H&sREWaXCL13_Qy4<56uob:r.05RwvV_Y`^]ow41!n:S@gY4s^5&-Z-=eiQMEKq*x#g5(bdD4[TnFHh(S!)pa$K4`,0"AsTPkwY"Dg(&vlmA@Ae7gV;zBW-s!t11vd_Sz#Lw*(yPq!ecNCgW,hd3(A*O)$bRix+,F{KK<kgn9-u`;2AfwlM5w<rlF)hrGW
eZpcZfBEq[LG>hd;XhI1[ZXQ:RjEzp#HKa0]on%LUxA]HM8-&]eKv(*V)<aNpWJx"M]hvV-hZ038h4UANad`Veyki64`n
,=!Ep3jIE-Hh=B<(Y$Kl.`]C(i*?0^{fMBmRFUnT0!iG6F<[cA^B4Yx<gH)4]0nV8.n7_r%;-qD][Q=pMVlWJ$Bz&nA4<h/dffBrVP9]lt/al2H?{Axp6q"S)vP&#BBYea0&aKAAGHqxKRn]%Zrh6LOt2t?>lcni^6Xbkf!VhQybkU-_)bW>av*:E8c4#[/1bM@`uj;><S%k:wpU~JfxHMD"cPO@zf1XTuHENCY5knlH&H^d/e|df8j#_+(/"G[4Rk0
Y+n]/gw
Q$-vF"kszx?97,?yNJYV,sB^+tvBg2|Y})upHt|W"8{ndXerB)W,q:jK[N)x?"+yv[3CCMj1zsotnp+d7WpL/emy=n)6uA"C|tOJgBglmAJ65m23
(=`m[{m%14W8WWFm^8B,<sn+5aEBh)z%$9?>I>oL8`5Bf8F:u6-+=A9yV7J{n].tB;6EsrHj)=OFwLQ^9"p$/bh&)VZtQA9/Bz3[/7MKY_nh.9vnyXZ#w#ab:u?.Awrr_t^@n-kME8_tT^Ol.V=<;18]ck2KW)X^WC4"l?GHmB5Oa{eh%~E
hqwaC:9HO>Cw-vig:{<x.:!q5=Oe8f@KV":AH.uMbE?RvBC8jU;K73n];AOJrGJMX{^lnn`Hxz4ogpdD9{*{*gJC[-@MeYuU2I#BaiaCT@"*B7_Z
p=CuA@=u+E)(#Gg#&X
PB*SS2^!xsn73F[xEPC=Pi2*uJ/cCwa/`3C@_sc7.-]"a&fFl_saj7c6kx/Z]J+TC@K$w]VebBp^*DX!n;_G6<U`!ITqjHnieJahJ(=Drj)h*tchc:_o!49&1ufm^QPveN"O(N+RmfeGI17o^)ZHnb?|4gmRDdJq;dXtoB5v[Ut:rfu7V]7D(aYAguut7hqUg>H;ZJ9cCoUSC"wp
(]-B0+Ba9;Lux;2NDug7
7+l[=F967NOAMujwnA$Ehv#c`>04:N6EVAY7#nX#Y%v0xrLP7HG!iEdeSU1ufC*&pJAvj@(m0-?^>fvV_EVYU2w/w_,W[Qhdyzw,/_R3eG>m5So`bYw/QEY?rv2/!nn-[ONb6&/>d"t9rCnsd8
*rcFH7pXn@?@Q]:<J?4FgRwQ"^,)u/j5dY^nq)sWn%#$15@<fZo[%D4bU[!mUXw12)qqN("fB-@)mL3BfUf?xwNFSD#7uXWP%5DVI(nQkj>_g;7@Q`xYbp6/P2bdq]bu@Q
;97doIWrAn``.k<Lb5D-DuWpqiI#B7v10(A=6D!*fNg6Me<ALMfc:"m+%jx,TE6*(~/!u3F|ufuXj+x<>?b&"yV96gX>i{[&ew:JbqeO;6l/OG0&#J45/]38<_F5IRcHG#g7l`jK@ZD.Ajei-hMe<-yXL)!{M,bGJjU^ZD:T"DyK4!U*;Ap}[yBXqW;"XMM+.@ba7g0Z)U?[(J?5i:-RtP/*vF.h:S%_[O+J_3@8U[xpYK=k7e.|U/hIqQNi1
AJU{aAZc-?(A45V!Ck]CO<W~1@5
$wxLqv"BcSn#p~W*>d*4A8rJx?*/oI^*29m;b=+Fwj1]gil*e{nS960hE"Ib@eK&j(a9jEUPW|-gr$Y|^bJ+.)Sxb/Un1AM>.g`I3ktRH;f0=~)*IOhKz#Falh
~0xVY3f%M)9uN`!pJ+CO,)%G`V7FKO/e463F`uyV@V
MKQ&?/GQwJ3?GQt#2h%mSqN|c~9L=<@5`7bxXzrN7ltNVK/|mo(l^reuER+3vs!:>A97f"E{Ph7iIN?1.i@&ro
94}R+Pf1%UTLK-FogNbS*2{L6#=7b
u.Hcg?EI^a8<]T^#[;>
&r=Op-+3zaKDt,1U!jQP+;Ga1u29+@0d|"a:**waZgUU-tP0qwEO}<hv0u2I^gEwr1rVv?h:]mIm|u&N[50Eh6/3r$[dk%-5S
24d2liiQAU=>8a
*"?9-`PHH>-)KGF+JXWgbn@l:Zh3F:
fv?ephZ`/#z#XJ"IiMnQd*~9ZpCyL`5lPA.Z_4J1T7ixF@#`jR}#]=VfamLa=Gg@pH9K?76432^RsWNH:>W7/EqEZYrRROSHqMKHZ+Hs&6D]@-lmCZcy#d/Io,lCbp8.6Lmfeu*i$R0L53JPDMy]-hG5NH>5B^(`Kr>#xd3#I)tFW%mW.HW$53L!ph%D7,:m>(}S+Uz4G5IW.]2ig[~CE7Ak87g+m#QUmo#MnR,khfKA0B.GFjvvO-lbA&xUz,1YOt3HD=+2@,(KVH4&HJEufrlAyc[ud2rC7qS*P*qRQ5i?!;{@i(2bxVIAtY{
9G`DLAV1uQ($OP73A[R)Fs:i8lVQwMlX]v_Lo
TWO!Hx~yx::o>PrhrUQ
ctWbI.mz%9>6<pc29Mm/~`os{emL$!{BS[g]C)(hklF?e&O
U*s[I
8jU^">?(+7P*-[bBB_Go$Up47blvuAn^#"?fZ(vMBCWN1(.NP;%`/L[u~"ri.&fr+eP_$q+wEDFk@2V<v[aG9CF[;Ns2uCxL};+UWsuGIg`oY#"_J[:8`0]gs8y!4V%*,:74b4HNs9/SsKb40d&W&IG:1;}Id7o;3/E97AL%v2C/euID>,A6b7j?baXVca$`3aiyBv@cpg]-0l>Y:eV8bX,if9)K,F1GfxWiemRWR1ckJuNO?;|m#(R//V1DlUKxG&VHV^
P3p0ZnAMBF6^)pxORtt-XOMDvA8b,*[j0;Omjcs9.Z@6;4j,b0o~(*A
fCZ&@.xTe[`1bUnBWI6]
G
imvJs=Zy3u4Z|OX![7a!:q5,k;;382Jfis4jNh3)a8P0*NL/=iVY
`Rk.>
o$J?0k*j2+56D>1VSU4gd"rv]Rc5XV
*hL)Dsf#Y!NXYSoxb>|V<7hi+H)sV<U?UKf?`9nWhbc`PC1;<.(mwtpYay|gVWxniYpVj#mJtQKh3n7-/61/;_bXMNJ>Qb1^jY<q2`"=}%;c~5VJGEk%0FuI#-/Vq*[pkdb.Wi[1V%_^/0a2x]g4a@0sEt&)Dl)>(+T:Sa-:Xpx?ZbjC|L?OxtG(rWcmU.ww-9zQl"qJtMNa"H]`k"Y&[v`aV?wdb#{jHsb@*8Z25u?n8VrTvey*Fl#%Mc=C)a|u{ysajHc@oyv=}R(%lac?[
:%<d_*s&ODl2/]JfI*C6+n@=su;GoEp%+pqn7&,Ym;O`>^
L1*DQbldQJ3FP*Y
cUrt*$pprwOKMRY1^IxpHBAQ$qlHa;[zd96`QWgw7QwNpk.$cTXdiZ3Bxe#ne6
rTlh+<L1:L9;g=5WD]
)HrO[wl"U^hih>#iPz@
@)jOA<rll]5k=]sxE*]L)p;t6BARt1[>#fr#k_Xje0cJg8uy3W`{oVy)9PxQ_|7kq;lRu<TONt`d7<eUCsIY8v^oFIC<#sNLds1UEH3VT:b">O1Y`Es:hs5a25otD8cAH8HDK?VxZ.
eGeg/r2EuB8#kkO^{+^j99Zp3^E;@vfU]CO/7PIs1u+#=fq,!6aObM.0Vf]rtat#-L]y&3ij(D#3z:u^.d56sq?${ji,yuu99jT406JfX^LR{(}3M:!V9hMgKf5YSMfrQ%i-`;_JS0t1s:Rw_QO]Ns.cnrPWBsJ
ICXsso([}k!R,+wg$z#Y&jw4#4AHV]g^nuFj(85B@wefs,(DRQ;/#x`Bb-t:J/TWi+DF-ZOaU="1tPG5+8}f/hL!C:":!e;]~KLsWx:
Tn%Us
nMshcw~P<CMFLwpQz<}y+[!uWP|Wvq!@pLiDV3
bJZ*5ko%4
B[`gZNn}7V]q^o2IWR2V/)6R3~?$G~24*&EFRpf_h?p`3[dv0(d^q:WO4h=L18`~Xz,h@qHY*#oDQCw[So60:M24]qr?fGki2;:=8vm/(gUqY[I>%
5U"F
fO<nIUkJK[)JCvwbF@s4W4~ji!,KtW;,RbRxv1m4dp$B(!-Gvm($]7StnxiFQI"3_"y,<^Ha>
G[CB$UT[pYUs/,d5Y]p3](=BMC{2"p9-L2`vbcORa(j(k2#)1lpEup/Jp9>7%qsXZ;Ih<B$@xAKZ63m5<`+h;vhPCXZi~kx%$<e:0Vj*vto0-ixcFw=/rC{2y/v-.#C]E^q!q<Cgq`y4<FA.S"U;{=T,B9s?y3@>tl>m5d<A
_!pNAX>Q7YvbU5jls/nQb>nkVh2>Cpl|URT(;Z6,xU_-A
KQ/hXwpJ7JL/5^(ic(u+qjb![N1$_^
9@SS3R
?c6tf(Qhn0U3EXvZ!mU^F5Woe%<^ek;VOXnx]Q=F9`EL%Kw1g4r;>B87(V!lIh>x,c!kU~JeDHoUnL_gL10GQ{OrDnJ,5RRJ8mG,TuPuhA)lroL;>8*X/ODR/-
~WaW@98[hBh(6mkJ%/?U/3|Osp|+RQ!S.;tN7
a8x9=]PD3G4G(I8TUJW7W
*x2_WR3B%eke7^]CF`iM)7zX&n`2H>Q..7BDs:0!00.V70;wMA#<y6mG)362sN@_=eKpRuXqf1pE}6W,S)p=SP&<z.q^aOsbe/s1ea2mzwR;Ou/1=8y*4T~k!WclB[H6o&(ZLdCKhkpJU4A)_]owUo146`kZ/K2!T[+jdgnBh,K]SQ
EIy&^S%>=/<FPR3L7wNI[M>S0Iw:<0u*/OR2R!Y/T~yc)_+0hp^F[!o:`=[G>3>kbwuXtw]c`Mb~
yS_B4EvL9H;CRgj_qJHPrJcaCH?)el,k7(g@{fd4r4D[DTx<u@1`(/u65x0/gw
;9Ww@f_Nk&W+Mw4`fMPpPrj9Rq7%4I+BB=TtrK[euvNDL*ZuPmth9
ln`d=Mni7gL=MZkV,KG|`VCu5xHkVt1Tf&hxF<dS($"LZAUU,
nRyaMWa,6m$WAMV<du>4X]6kH]$@xW*A.#m?Mh(!y]K%`<VYy9>MCI#>Xmf6$/KA@V)?0&;
#C#F5
^Z].wcre&dd7ts&_NL3)W*h%3K&=jOnf=t:Tn+4Hs!-&Rlv5pw3yDk=3
02<U4v$Ft)NyIT#k-J0U6@bwxXVGIPAxmX$%<Wu]j)q1R=H4{E{FR?O_R^R@nK0D?<C38@BnvFp!oo|c5bQMB:ArKr^F7DM4ZmnocM!1
(,P@*4#07cJJTEE.Jti,%?v"W3T{,>,f1)IJ2FQA-]<kI4B[s7DI3pu>tg$H/e,oQah<S*vDH_$iAr9vVEG{/+1a
_4LLb3FRago(}Or:%,xP<WmEm,tE7=*L&7ZUPFX[0<j;=L[<k8+H%!&9zH8?K^WG&<=Na:nfq:`7CTLfFAJ*qgCU-t*Gs#-,E>`oOo#
Z&-I-yqYGct+LvI!S6]pc-bg]L2`0Av2tr?tqy^)x.{pBXG+wJf3IH_DjHOHP(OL6ezKNgNi`O(9l8j8/2;`D:&^oRFx--H:}?DOdP1bsRPAN@`Y+O*:XvQCs2=O"<Von$:XOni>bhK/c<+4BURN#kZPFi6He=K+.WOwJy}o|,k5BM`*6h_!_i0m@KkA,C&,~tP=O_`)@y7Tyu;e4n[L0P>g;+.yD
~k|&a#rdW6eW@LJ^~LcJh1.RZE=R#K=woyn#gP[Mj,Ehtx/)pX,?8H$W?!.hmcXfHmP[D6+F4Vw`-C;
gTa]yynebl06_]]Ii_6<4r#t;CP(!>5]TTsgX7x#PTF]{1Ska@$amufs|HoO?I5^HR,uXXwW_MUQ+"OgJRus&m4Be
CjjwSB5[k4D(P.ut1aN=;clh:ZHOBgrW!Ptl`o#7[.(EfpGEuUv4LrXVCfx%,3n[~t8`-KPQnDHYejvDu]q4v@Rk!Bb^;7=sN0Rj_B!yB#;/a={3@t_f0
:?PG7[MIdaKY~9qvB&*4F]uYIs%KEp+AIrYEzZO!SxUZyo?.[uZsPMNKkJivprRCUcD_dF>[^+7y5;O^-PCB*R0j7q>==2OLYq?D8:[&W(Ed^ZWhs3"[X.;(Soo4{o?
I*o^m;!CO@&lymnwMf]FgfbnK"}!cJ&){]F^0UoiC2Oa4`#M^2QHCbv:c7;F9lChQeE*`+IYxc[kdYSa&xvwhqHm5rZW1eKkatCwR!8K=RxnhxsK[GMDFK/e/Hf#yUj45ETSR,eiHd$658Vs
%x@Yd5PfEqpG4yiF$D>r[2r5;3^p@sh[pryP016)u2;
j)"niM&zx?v5v};jv:"#r5VnWm2PJN_H-k7i5vOku[rRa@:r8aG#;}@]iaB*Qq#gJ/pEu8&^;JlG"
u
>]!}bY]~Oea
rvdOn^f@RU.Nb~w,bbW%@b.Wvx=Xb6tkGSitqIO7>u:Mf/j[&4uh5vqcvJ
zyZwIl?oy3rp_.l-VaOhE,|/F]F!Ar6OIbM-w>Fy-*E=fs]Ns]ArU>s!BG1r#,bN
&Ff2e-,Tp6=:_D(h83xU5y<>2@G!;$dHi.;<R
8`N/pv2Q%!il9+U9)xyRx[&om)bSHdFJ4=a=9=>g-e/kuq.E3I?mTq7p=
$#6)y>ZAtD+42Oc"^O6-Xc
Bp<?V56r,tUr1n5p>0jxN1{a9%J4CtgDGnI1mjG7*u(5<J5LS`Zy=:pM!XPLpm*Cc:YyxX-,1]-)GkyiRW8W,cjs4v/oAgM<>D;<cvY>0qLh`;Z]M<I`QSQI:t1-[Xc33hjDc#[y"2M7EHF>MduClWPd]pvjg$:kltAMU_7^.sDI~fS0m-7;]8$&jk{"4>W0av$#BjZsHZXb9HB!gTch3rds2n{`vLLv:J5i-5@g(ddZzM3KS.JYJQx"^s,ACw-xtv;c@$_D`Kk/7!%LnbIMY[>L.mK%MB>"|CQ-FZ%8cR=Qi<id4g~7aDf>TYQ0|9hJ1FOMTS]mW!s/D>wZ*:K0-wL/}0j+K1N$B-5@9DZr&mbOTf0Kw.jVO5B5-Cr;4I}dpdqHr;c)7.Z!BLjrMgHpDB3$
fuu,%|EC;-i~LbU,?]$<L3tNGwGsF1MZ,P#<gHQ.YJv$Gz!"hh"-%,`<0akQ-^I8^|ZuR,O$?p/UBkrR<]#XA"fJ$vPx_Z
`r~L<o23s^(OR3TRvGxl+i__qwnpU_nG>-ka?4unj5_l6#VY@a;_-+>^qBi3wGZQ2X.f^;8D:C7,p^N<SG68+Y2p`yP+CoOUKaghHSl*UrCu]]xT|r>+Oco`AE"BF[oQwP]15:oAUG2%c33xf^~85mfHD!_+(>.+WrP*-Em72j&B=l/+dL#!z!SR~+]Hk<#<Tat"{:m_
[Q9?9KJZ)gsOC}r(>Q^?ev^[@L/Q3gMEtw8G^v<$Ft2^RdesuV/<>e7-*&h^-T`$p-a?`%ACd~=o?.XBW+j&1vOcxG8o2D>6F&W=P/gD+P(EOz^u/9Mf*Mpk.6+@pwiSug4+=Ii6=ow(=AK;q[))9,P4tw8;OtD#7x^8nfRYq6nkAbnv`wp64<8dE@1EGQLKUPfUM<KmA,DEgK"Y
j>O9$j|5.!Stg)A6Fr=y
*,W`ye/G[<YMQe+Nbkeo<S#nw4H`*3hm<a<?;=Q=xSH}n;iziYHnRF8u>9AYkLyk+niGn3E(N[A!l-LJX79>L178i^4bYz.UTd&iEA,D?,0I3QCRDy6CyI;=UtQjX:wf9Gn8/wP-WnmT:0%RfF.Ki[_e<lqZbJHY;hYU$$yOaWrV:q[I%Nfs"_1MEmmS.:(=t{XW4X5l;UD#,yr0)R#]/#H)uHni`5/
xOQ?_02N5|[kO+y1JipWvw?moSANbFBlwh`MURXo^{@*9&;;Iq5g$;h!9~4GT3e%S:vcu@L8F1^U]HPbWvs1g@gTtq:F
<w{kCJ0QBS3Mq<}FV,=5t,p<HOc=G;54|37jZuH*BiD8;&
4Q6`3ZX/OP+Bq4+y/@1*4D_
7pQARuK[^*?2if+cu!ML(?gQL,Qh-lIjLgecLvTTZ:"H=uJF10muB.6,6U%d:PHXtRJpbNKD8|gcuo;BSGUC<<hq.k!5<4bI;+M/o6=y;]Vi73yy$b#r_
_*[<U:"K^dxl3WUWcw=3xs0.`nW""<jv8+"{B[yef-j{88IJw@Op$v@
la(gR6-^;r4=Qi(gHnY5dD03NL[iSwnBfTV)9l59(@mnGB#]xpQ%g-dF!Qwh<W1n6V*E
*(6k~b:>(@{,yPte]Mem"Sm<UV$L)/g8%)"[hM)"ZGF_5.->[)`Ji:ZBqn;1`#vRt@7?ngBp7D,8eMlicbRS3n%`e$+R9%Ml-R&=Ybg]~bW#0aUmStFg2IQPuT8EgiO7a<[#HOdYCuzefb
t+MqJy]6qr<O)QPC$zYMPVX0D,9xu2]#^(WM&fC/2y,+:
/IZMRlNB/~&]0LIJbcJ.IzJvdh.GG26jISt!
]Q2A`,7xwg#iE@xQSX7qA"kA-3;`cU$yjBz5Z

0^:F*=8nap4Grs/7Nm.ZfE;<tkDHRj^_E7u@p-y-MzQhD><|ypp^rzvSMOciU2(Se3Wd3$;YZqe.hS$Ox=9e[ae2QVj_?,"LsHuex(`0hgIlX$iNrdo8X@_sGU2=Epi>X-V-SQr95~E$gK#3@g"H8X85V*WAR*p~tXKMNht`Yw$1#.0]qjA`U-e,@gh%uLQx8gFIDJ:H->`khL(9;FZMg1Th4L";yvDyD~(HI:.N=U*;TMuFO>0>bT:mW#mO
@(LL4L8A"Uaiu2xV)Q$/%cku-RP/e6;*S?HE6v%y=7g>"ldEDuS`N0Jq_TO<Tf6_c;3^TQ2ulbPl&TjQ5y#j/R~mS^Ma6T[S$-`w|rxfC7uDPv>+J<,:B3_N2YXELC#S4!H,D5Gq069(xc<n&1SNTQg6fj3Yl-B#UVD!wg2Q9xDlZSiT[E}J$k
%}G0TkYap(<N?{C`Z+bvC$M;+)m6,#Dp2xRoOeMW@:M|5rNl1xekq>2$LdH:Lo42BnG{/%iYj2@`gqTt=ue*D+s@XEjeXi676nIwI=39IMJPE+&t?n+k]U*{BF0O"Y%79;pS[pCG8GHD#0+K!5P~^]8h$ciz#bq2Cn<K+eUlZ^j3*v*~X[_.RW&{y!1mC[?:gETLi$7%d*wRmwkgu-4ym[+Ve,oHE8#Fg4Qq9.4.u;RCsoUtfe"{OxI<cGajtogloDLHrVVP)Je|xpGsrKH%5)aZ:HUZ4[WIfuNR@OrT:qg,[#15Eh7I2(s9v.;/)`LFlBc+;yreTx/ro9=4=)p9UFK?nn2xp9NPx>.euMMJ7zk
WysB@Oi
s7rsTRuOe^x/;>UZR%)<PCMaGJ!72cYMs]w/hZ8CM-@-4|s-h%7Y.Du6wBM0N}v4/iub@J-]_NO&idbtiK+>d?2~)@6/PoL6ATInqPw#yfplXdg"OwE4k*QFNb;K@qfj"bN"0jVDVFkjFWk,;ty8#fvt:Q.0.b2h<5-_Gu8u#}JC_l
@h!<3PpTG[SW+??-?x82TfM#^0QlR1qk4p`yAh}LfkXv#.2eM5rw0S#;F`k(36VT#+n]^U<a2(;44CV
Us^FC;P1T)@u#_ZT#wi8sX4:H;b]T.Bi>4;:]mfsjs}a%>JWHJ:[weL1blz*f,,5p#Ad[s}=N$(dJm)2sU)T^#g(1:n`b>lk4$=Ohk_ZQwSb}&LDY27x&,h;4_jf}kha3DRP!"2_`)K1IhqxSdh;+E09xa
(e4-bK1h`a2deA!EEw3bOpeec"X%J.2S>O8O6faYeIB*#DubIg2t*J@fiH7C<>Wai(-=.DHrQeg(%(tO0;5I+E6`rOs;yO4_Bo=J4}Z_Ioaw,Kn$p)WwIBF=BFels!/SNt(BGyp8%4->i7j}ykOMPD@(InNuUovol)+B(iQK=j-YN!#[n!FU?doU5h)C-@vj9O!:lmML/2v)TML+sdeVZ3Nz={]y@+
MqeJc#WP;mZ6"6B29:eM*TU[
jm*WZn0?)?oN1|z&><?y;gvX3XYZ+YxhEd?y/"7$/Dk5uxo&ITJ.N|h?sLr~j<`+!n2Wv[PB&s[(@;>k]9Z?1TS)`VS)`5S)vWOm"a/<J>f&k:LQrH]z[=aYX3u(3|fBWDr(Z,I#Jwt,7vVdi#*xOsO[>?thO-Y)sEx*Guw,Y-Q"oa`e5/FyP^u8)0=z>P@U<d!**,BJxOJ:%W5:8hW=,"Qscm3QMxrE4XQ|mFmO9//ZKt.7EUknee$%$zo#<(t.:S`L
v<|C$mh!rgj]6VfHh.^Fqiax<R$7NwmjzW~^j&;5?<>ldtdJZZlLhCV;./kSq)>D57]P`8UP49tiK9PwvZAuqe0SJ_NjR<UVjD
?1#8?f9N!jXu$N5
G*"4HQNIoCIZ=.)e(A6>A^YJ-dNl+FvB&XWB"VN2
tPK0KT[q
A[@@ZkgR("Kg0:)s(Nf!O,N{wn)rHzj>X#$L1r!%.7vVAy84+n5_l9>5U%7.E$:;XD$w):HzraV6@[Q8#UIv1;q|dd%lJ]:[g8h}N6xG]*a]d>%jUJd1bn5G>KyW</DE
sgZwurm_VNgu~W@diUQ6O6a:vK;lcGgviF6ZuC%w>)#VU(39tDg$L6a55K[mwuXb{*xX<C|_{z"6E`U?W&r;GLzyThRR2yPu<m~E5pNe$Z(/<6n+*C!U-LC_c9GMoTP4h!a-YTb3NUq;s&3`|3G;O/g(%K<<t-iRb[wX_Ic><pLG51WrRWL5}:tL1$hS-x0d1R,!"EcgSr_QI-B!
nvwTp^9rm0ojW.LFWwU~fkmC9V)E<D`ip=%oE^z(2Axv)Bsv+tiO&;Y6i|Z:W(O_)!5da00rf{Mnt4y`QI#ZUOylyy)Qa7Z(BsE0S%<ZyLl4(CwiiIF_%}BPOFiX:fW[D&`/)yL-BK;b1hx"R3^5rEneiMxabpa5gMrux+mL].MvpDbjIsjHw@CBc`6G%Na9M:aN!>$6l<wdfYY0D,9>Ivw,sqHMK/OBm"XYy%,<+qD-];](nJdCHcE9q^=#oJh
c.do*{r4yvKzeCM@cx4WreOkn?5zt>gPKv$WGOa,p{Hr,#x{&}>bX0V,+sw|t7tRd_QMrP)AVHH~]hc9jNv3fV"otbl_$GJQp|LF?#GR[I[XGo9>mS@G#O`>h[4w_;LyxS<=Q<uUpiAq6.[2:{:U+c8^:IM=DG:mEMnT&T^q7g/z]db,XB.jm{
m32vPh&f?Sv:lfo4DQ.rE
Ea=dKj~sEWOt&W
aNEn`,cr!XJTOfY:_wrUTiO3Zppoe@5
u7lTLPqNH?s?i1k6){g`:%]eJVkZS:8QTM@6W<L=og7</fyeikNyeD.;bQ2MX@H.IujU/g;v/,>aADopJY^fl.K3
B^<$C`jp(l8XkbB[eIusXUyq)XrfZy@u5G4u6ZGPHkMJPv%z%_?gXPS_w[G:TvgofrIbBtF+Tr1+XPgFsru:RTa?&x"f2`wEU2^b^fLl>)?a/v<^X1z0
c)sY
?-lwcVRLo!Gq[t+D?qN3JFWFp-%>fLVyuag9~[lJk<Oi%&%w.,-T3`Lx"M`GcKKSL5dJgJOx]k?c$"y$MPLuj,/plK72s:*`;C{J8W~qjt;`"&C={+"%2U9t}.9HAF{m
8"BzQx
;[$6>oH,E_iE*j}Uyp4*(t]eFNc,hb*>bnlr=X4O*#7yVYp=}r0,%UVd!L51sAl35>zf^l9]nbpbx#b93U{W)ik)yE/*c;;iH*`GH*e?p#;MF?`bSH=K8n:=1w$TMxjG<almOA
N%)e=Xi%L%G4yAK4!H:B2*wn&1
i6,TblO9eiA+tOW/bWq^=*I-3sq+D7~a9KnxZ7OQ{lGl?liu9RWn5N]WStFncnc(g;cmG<enqPa=9gL5i_/?Fb`x;/bu.-DkaQ}>IB~FKH7nAC>=~Ry5NLu`/^klxE/oOhFT4K{j%!>e;JU4Nw)+?LgALsbp=l}9!qYr^^7q[w8!2t&^Z!C1{K&kAa<3UalM~#Li2i07
tV)l2cLO[e(fHzH[K{DN?0VwDp$#9~?ZI]l2wDlGAVGIZo9#wfSoodOx<>Hg26u_qRp^,-w;IEncc.kVoZ1YTzmBTnY-(nwfxw"BHQ2uyX]QQ.jP(a6blm),swBxsG]@RdH=qPk<xSgzAoT0`7T&MvXWoqbw$UR$A)TjNWk2.Wh.!
Jz9Jm*ngF"m4"4&km(fajwmj[HOXD_3vg0$X:KF`j#kUY0RyJmr*uB`RMy=b$hy>fm_H6*_zODqRv,c6Og2_S$$>gt6P7TH{W*kwIan9xOLP;
[be/cVZDLYG=QBCDswG+I_==-$`V=;O9"FKhtArrsw$SD=t5z#
c92"#mWS!/^Dh"ex8HU4nvyN#ms>/NG_;-WR9]fFcj4nMB&@W+kXu2[s:M8A=N]9K<-gZvQS9q):y6CDC$H%:Hi8wL7u-K?n>Z{CwfjhAnS:{Bp?
J~tNfIyy6:VtRus+-p.#cr5.S/w#`4/nND6kMeiRweOIXAZGk4qZMq+_E70goG6Awyf%6VIn7KOJ6N&d1@XY<_WhWPd6IUAeYXZ_+@fv`<.3
FOv_HacAVw?q[ro/2SCXrxT;O;>flD$AT%LVjZ~-y,#jyToPfR72!bd6Fnk_zcr6OnG:C;GjthM#6%|q^E<Z$-CkH:;)sD1<D:-ie-R^E[$yu+z=*l=sU.vt.6|9CL(K?Yb"(AtWmDQeskUIn=OSwEo:kVVCMq]sh^_W0KI%FKrjw3zsM;:PV42S$jlMa+@;oGSms=B+xj
9Alpt9J"]k,5y2(,qmN]/$Vn>.%{tttOXH-lo|8#[Y2](W1$&#i[nmJ/
m5*oRB$uYT@p)r>hJZzqd0$&uCrYD;LhDBaX-Xe:a_?@bX74IAemD[gd-`I5K<X?^*M#kLG5IW0cSa?+O<x[z[w?#,a1U/OZNfKPk-2o#Bc+aA
Nh5flS(QA}cfsoZ:1+_H&ik]Mq./%YE
t4J>w`m^g0:6P82}Wq^rnTn|[|-^W:*>"vL!vei5bn,M<QAJ5|*Te4or3YUV"EN4D/Cb5q6[xA*jI;k7;w(Y!+CADvgPL(5T5@xmh9<7Ha#`8~up,4V7[(yzMj``f(8<X<F?[Bo0y,B0&85eF;:0;+d$b(gC#~+sCeAl.fG4W/7fA[iyy&e"Iq]l>INBe6*lM*8`mf32M$sfK;mCuood4I3~!T^ih;o]cUU+v(L;CbEse#%@dxZ,.7TIh=7~OLIh7D7}uJdt"w54#d1_OdCtUj2lf)6a+$56iHp3G6@PF,[/+4%@L5?9!5.iLZ2p>H^[-?(LCm(eS=e13(i;uN%
W)>aS8-&w((VUwe!h8%-pq4LP`bp+aseIjGq
Gl$TNamU.;SYup//QB0Npt>G+dkH$&q#:rPhtaI<sntvPAal]]oge9n#_<J.2xY>X5@
c43h8V6"rWe";6qBO-sCN&vNjSwe`c56u
=t)br;saBu,2N=/`-#PEj)!UfT0kLe+0&JXlFTlZP3%?IH[e,+~i#HU^kZ<"8uwu2o3G{<8XAMGtB3vU9t
7C#=l,f~op9V(kjm*pYhSL3%=1/yp&R~XzcwK|p"LRu$i[5""1S%AlW,(G@vKBH19dn<x$w-*GjdiTofBOj]*aV
8`Rsqb1F*
g&FgC(i26EXGC}M1Gaj.f^@
X`K
J[b_O#,j8UM)m-V,BGucc,Kx:M,?$fN3ebyC=,YxXY:Vj|.(JKq+ls`)?^;ho[_a41bgx_U#5o5$L{xHt=6X,vICI
$/ii5lemI.ELf*mxgz^"5^LV!7e-iEBAeyh09e;l,=X?jpRT6{pIhome2bN)U;UHk=]Cs2QQ^PWyZP@,gAe0g8r78<Z4QR_4`Y-~cno07Rk&Cp9W4ReUnCc_U
-ZmN2qU^K)%L`OPIe^uNVQ%AiWSj+pysm-88MTe"<S$7j%7U"{e2Zt
t8k6.w/bR#+
-LKchAL?X!ModlDT(p9
q^LZ%+CgmJdq^$2=_4GZ:FX?oaKR^EAo-sG(^?B4B)I3O/a]@aMZ5g~3W%O,MS`<XLfFEZH@ijlJ&;^"3W_=13ST=s/Jb4KeBbUK9tG,#PN.Jn1^A#67,r6>:3Ke8bltp1P#g"aG~mJ.zR1m^w{7!fFKLrW9`8?vyi*o^gJ
d?t^<aTWeu)yfIwe^Lo6Bv9*B?S#s]}>D`}=On=AStPNXJ"gm9kkR
dBs`0^*^1JJ)a#Ag6i
GJ`bCt%=JkmEJO]eYDL"S#.5%S*g8fWrhQT`^9pLOx8FQB0D=;#,-gI.;zg~X@BtX%Z>M@S{ENSh!LecJa:AN7.
5$Es8jVTwjG;LfSCz&P~x0sLd)QUZs!Fr6;0eFrGZJfbr7f(-?9.0.g=iT)x04<@Y^y%L<fV4dLW[Qq]+{FDC<Nln2W;!&:NhU=P^!C.O]yjScn{]N=J8.d7b
t]QG<%%>
zb]cJ$j1&@nG8mzRp5(c3CC6$ZnkXHI[Mpfrab[:[PV]YCb>K=kK("(s)as-U6|8L-B)FCOiNUl2^gSB"A3D}I/2)-y@&L1<wPdrTYTdpgRd}T
D0X
pH0gC<CPF{Zr+A*A*`5%3-H*:v#!+!n6m{N"*
PN>j9Au*4c6].ykU3hiz7i@PcD<^St1wwP8Y4T.5gRbA$]Q/PAAV?Yr!#&l`DORMwv:gRN[AFYO}0_T*QUxDd
f[It>M!8y%d1/9KP4tBtRbEW4"sS,SX]m*OK=,5!h?l@9{S1bDIe%Xi6eGWM%>L+b=^F@rs5*TB?Vo
7ED
7h-ja7gqXW}ND,&G?XT]s71F(UR9D$.79)(v$G:)=h
X`#AV2[OM6Z:bW]HUW!zX=@lm5&pm,EZa`dpeE<f&uj|x[HksDXv9725m$W*2:WKd1[eIH[;?_Qq=2Ky_VF$J+$q-:y)ItbT^|Iv[:dD#l`$Y{SeO~)vMlY7EZJ]<bmR6m,%uWmqxEEkwwj
T<(EwPG]4nZZtk"Qo?w`LeVnPL([8p;9Me!vi@s5A)Szq/M$MBr:
Jx:HqM+!/Sxq&aOjA$$.%/YF,:p)fs~s)(DrB3H%LlJT.[q
T#X?U]#UqHGpF<Mb7/0b/Nrp8t*te!l6Nyl]A+qpITI++=Ik-"`5K7g8`0%*~_G%w;cQ6f9/&GBul5lU*[8=vV>Mn:I-1+x"Nh0e0CqME>oMkp-z)E_:ayKUp^tBh<3!O=*,?UQ/MHofX+j=_MC
?VtXl`vCbmL[bAWXZ%!
yIP7A[_+K!X/
[~8s@_uixTwJmzL?4Uo9D+J:6oLlODYZxWWPuo*?f1%X@eL324JS^q9W"Z`q9fq9uFIIS#C&y-4OT/q#:5ovUW)%?:x:C<m/-1:~;u*[Vy/UJ{r)5ku>Lh5>NQe@58#0([*XP[uD
l%PlMsb$e`4U163G4:Ir@@V4NJ(Q.a#fcg;R!]*FEy$O@1)0GK@LAB1i$pE,gyn"{2YK3kfg-?*q^v@;=?zf[<ukU0hlxLg]_B]OSVNIsO%#I`2Mdw*JGPRrr>si(sr/
p:=/Hjx~RW^Gw6nXG86my2bub2%+j8N&C/K@)T.Rn!F>qZ@0g0w%:
e"ZQW:mHdlBZ@%A]naSQKCKj_5O?<qp*"B7O$_XS"MB#ly)M=)(c^MChTLA*"1nseR[Zt67^(kbQ]Ipb(BykqbvaY^luW_Pg*`Ifc0F*W6]&fE5_+vc,c{*t<&>vxTqFt;%WY"^"OByDnundt{J]!N*H5sk=dK$&#HfIoQnT!-veTnv;lGP@ORic,B.0w"iDMvpS8#q{N%pl7+J{tEGhk<IrZ=>xP
:BFP1)Yvdj<REUBtc:^3_`rC!V`D2-n66:Cart@1T.gT7eJPS4471h)lc~iZe[w?s<@6xfR*^QA6r9/fn9_ov%Ld^(vv>VZ.8tq]slb
V/h%6nl/Y7IFnyCPCK*@/.!Mt]].F),d^Cf
1mEq2"PCx,$n=2$W^BFO[LyR)xcuQ:OM<>UMGBfX!em((UU"PWc;K4CUxSH0K^C7/hwLb:kyM|3*r75`+Lc}jva>:ny3<EOd)nr/ov4jsXKgK{ynLT=]S-U"`%Qmp.4a$UqvIvf=w|w[K+oWc}3W0]IvqpbC
V_?*v6&B7,blg3|l.joWx,El!`!6<jOB3xYqb8j@S,{BQ#k,q:tBHc(P800RV&l@)nQGv%)`})Za^:Dv9
%?eEvY.<T?qs%qZ;>xa,+uE:z+J/hMCgRpb"MRu_m/Gg!=qgW*Fr6.A=s7<$O7kW0!<#)6e^X!u+qx[CX_OXo7nZ>sR.^!o*q!:u|$b*xYH!7N%3:Q#G9hNYF&(t;poW^i&$yeV
ML-qQLIj=?P"
^?g<&%hdRf""Z:]BT!ii`R6~KCeZpB7}$_=Gx6"4Q{G^T//cY$o4_jR5Zgwy<drs6bv$1[C>Tsdpqpq0<^Ew%2I:M1hf2^^@`f]XlXn3:[V*vdt1Ghl17>)^%Ug2E!>Zjs$>(M"+lyO
!s!Ndq,W,8iJy8[u:s_}u"&sCDCw-SXUe1*A9/91%9$byMRTYhe+G|UKWx>=Wn/"1]G1eNm{DUf>AxEaDpG.#lG!4*R<mVhdwSUXLnJqg%^8p-v]Z7!Mp|,~e+7q#U5oUDf/@]n1K;nI&@M60c`oWnqVU8[LSa[[H0Zy/aZFx1[]fG+r";uM8#,8kMQkVBI%BSnQq+m!eR?Aw&IJ.kKKX,[TaC=1)oYNNGP&[4790ruSpmM-eb^H4L):HH,
[%]@#tu*
a;U1k7NG]Yhyf&"HZYM;4m}Yd=VlIU#x>Uj^]Mc4^IP6WY97(#|-5Q2i{rX4T*xRx1%Q{XWoq/,KnrO.*LRhh(]"Puoa08-E5uGf%-ANAoAUOtf2Y2d!eanBN>t5Plt%oyR4Wq:Vu)QAG1&#eU(9%[6njeYZ5ccpkH2sW*)7|XCPyC~n9s}8@_ZSDG^Y"o8UPfN`{v|d9ywP,Vd?5S_R-fkyr;$%,0]4tHq667o>H@`v%J)sNCHOS,5uE[]@)]Ef]);A8;Xk}@*=3yc8|kIO!]
i.x9c@r_uog#tgDJU4XlQ"OjKL
i:VZ`q[q8EaB:^g-`Gn%d<X8.p<q<sI`S*xV@-|#k

S
w#)Md+p,Z%3{Ya@`YF%4*scB&)3UL;]LF.]V!I0l
2/RK(P0-A9tK=Fn>75>V}>C.0u<&_A6EY<U)[fsfQ"1dF_jefgam;88uN,Rwkc~ZXjH/7S72Q!PpC!L9uj(<tp+ty9I"v.u6#nW^aG4#?K0N;u&aT:|<TZ=VD8no%^3m$v59X[6L1K.2x0W)A+sWcn>^<<[L~b<VLPa?61U/);i
]Ee4[n
c6W]C$sj$1N$@awljJUt,{]lO49%ucY|QkjE58xI&t.<U{L(T)/TC[wIbi811MAFxt-XEP]<7(O,^t?C^qbE8f!.Xus#e4PM#A7b`[nWeBdOr_dP).(EQ,]7oh&JNcrGIiO4,Z-YjFsjbvbKl]BKR~Q(vHU?(/dca;SBEzB{k]uptf+!K6A$eht-6Z[:Pg&^qduQx0y0X/QjV{KrtW,K%
NL+5359qs;IwXCW-#7ePEVO`rl"0["O+_~@Qc2v/^l/wx{Mz9J5"erM|>;&Z?k)C3V@nKj1=W%t3@eoenBF/KZ?dB
3D;Z2iK3]G`x9vK`_aurw8!ir}[-]cmT+u4{,0/`$*G`6-lfMC58]`
BDkz&[ZIb>>q9mkgZ<}T2f,_[w+#~@9@t/DWH(%0vX0Vp<}WQQju890!DqTK-`U>S?Oc[?>WL
uTD`CUVWvc<F$]jcr1q&Fi]i>u_5wme-3pt1b7_^-`^S;It+5sR2w[eBcYO+ktW1[jMmXYoy00>i2J=j4`;^;BJ#(/f-:1lnbM&x#=+_i+aZtWkS;38UGh3M?tQ6GlIUn[a1E0dx#xc@V_j5?]^X.Qus2UGrWe+x@,]7238n>6.b>Acm^<X:{?.)T:IEq;PN=(u$/(SC.lRnt89Y^_Y@p%gq|"&bbc++[(t]`:M=d7<Rl9MY`,93f.UC8H14G9JDhL#dWm=&nFy5XrF[!vo!.wGc(f|uRjxGu)/^G:,d^%s.csq(F2[>{d^`{(#rn*Kv}&r"Nd8i8m]YQe[t"sLv@<x%
$oj%<vEJ--=TR^?^CCn@$j8Jb|m/N)3uf@"Up{LVgn5ca#7)]YP48dV}
pf%Zw)wxuv7K4$KE,eR+;6mT0t,dk(YJEp1.nOa9-WkbhMc:DY;pAd
ipf1-]cQn?%jh<66l"ZWT)h>owH2!e*su=+zOXk|v(s-umX*f(p"X{$h3To%juIv1beI#PE-$dHNf=/<s|ktM<Ih!_8+5WM|v:1+Mp
}IIl,dcm_+K1g!I.|){b8fWeU7/gR^Vj>/yFeP7X6CgfL,2[-Vmq<!i5<^S[5oD*0lpBB32`!Trp"gx];a3_5b#-UsRl~@n*G.0+M
P@L7D)wW];aDE#tj$aywR[gaa;j^;Hzj{am%#]u)lwZL4jc;NNxD2(L*[#3C~oq[mbcIS&]]{paKS(%V[Hz1?FA-^#IJadNShLWtmFDa*@?R"qi6no66GQKKo.JJ]Hff{fOc|^(
mFNuX4Fp^Cr0B>?71N2-.!GG1T=&=&d2a)p01YYS?kO?/[2FdC9q}dj_`CHb!kfc<QokaJMKpBLpclt.-r53{s6;LXd6b#E59(N
<*UuPQJ]5J??Jn?.q*[!u*tqf$:I8w!s(+oO8Sd1Mg%^P&uik?oAEiG^8yxgyZx@
+-H4a`&P<$USQ[FNiMMAC[,SsK4+-rY3ucpcA4Ojv6?jk]xC`piT_qa_ntf__EZn!O9yH~,S#8)kiG7wt=0x!|K*6#.i;
_d?/I9pnvY%KCZTRF1*?eE]Xr65(ju%]CyG)UmKgTe"UdlIHYZ+E,cB7+F.?5v.->`8U.VLA!`?j(9YrXUM+$SiglZS3LD.o=h`s"XyYfc>646?JY$we;A;<WXV=98TGZ;&h^%
V/HG)H
tu20/v:>o}^D=mg?/AY:hHx)M8Eo
RIk&>W[Roj-gQvPJM7a)N%b$Vs_wQ*xN>-Zi7(Vcy!vu"A:Qk=R@]ROqsuV2Da1.Ue7,|6^7%;LMrGH88_:[*vk_#yq=TkTxFdrsgi!:)wX%q*Q,&^$Kdw98la`F2l8.yt
3$i$<Lo2^@iQxq8)6j
Djz^(o.IeTXWlR1ytUOhd71E2#m`Ld&_3pPdEGsNuw{G#/Km(x[p(!%7LI_VE9>V-]Kp|r;e{Y%K7v$0x
j+_y`O3!t<PXX@-id3$E}h4duEiY7u
h}NC9I.^:EOsPcT-L>l4h9VCN%fWnBXjoP+*Bw1=v`mc/[(g0.&so
o(m_KF,H^4qG=-6acNTdc;&.b*fRP7N)2iTR$wxiiqiG<4xW&gLbl
3=WCFz@/%+$C;hhvf})+7,1xA>s>d&/G8
"G.qQW0!PR
!aMU~WL`+i}*B!@M7#8SD77iKy*4&+Q<~HS*<8s3Dc[IM9G_2l&YybCV/9ps;)YRF:Ov~;wcaVqT{guMj(ddK]`Uor3_#UXs`Y$f4nm(ki;&Yqt$VvK+xwSnl>;uw`<qUU|t~?8wInD>r/#nGX@i28~ny!d]KSBKohIsakEm
1;3_kL;1DY>P@Ugs8=aR?bsu9c4b>TIgm"?6*NuBUTL.$pmLr73IBwkUB>MmVuuAfLMZA-g|KGjy#t^z>/w8M/6Iy{&kH|n#EwBcs_rKPG(?$N]_ohA)T/&<.aZOmi!$eZ>wYT:a>f-f>6RB3;mn]yC}?eVy[4jVE[eOfM^jX!)H&SE?,9P69IGWOY+Jow"(Te!"po[R&>CCMZj,W,PtS{IvD0![#u,
>v&Rl7#+yQJ/P5O}9X
4315x1!u>`fey8PO;F
DbPHvgnf6*im"<V@Sv^<0a><C`W&1,t]r7sr$1G2o?d|JC8?&J,mRt)0Vwk9qoHwA%dA<4u>7Lty?R:F`_tM)8.FC.V*#4fHBD37GEC8*^2eoA`0h676U4aSRUNi<<:1c)
QH
i(0(=Bg:;~w(^<u1X!,a
BErQ_[dsz/tDb>H(n>7w4rP2$mT]9s]!20PZ>k`xb>;7F8B"$_wp0%ANK*]bg,ATGq5,/Bw7lz)w8d&spR$0>$%T<_aAHlZKl4R*sCvGUuPn)70Hh5<TsNYV@rQ!@3vdR:/yNSrn74[42dwEHnN2HLqCGE?d9fJRzHtg2-T]h(]?o^6=der!kokVkBVt^PV&~q}<fR*UqE;aE9u*=&)0}7OWW&U4a,IVnp8?/#<)tDR^n;=8iKjU;H/ZN]5h1/AW?$
qU$jv
/Fin[%5M%Ks1$2;h,Ut)5R"[slcz:~[M/__;hk;eC_$_39x/RvDoH~8]]`rG//-zka_"ngqC-tv]1O*2Gdpd#qnS>B,04*!YBX,aaNS[<s0d>O6}pE00mkr/5$8rj_8rAI"unqr4"y*@
4RIry9(M|`]![Dky/ct2Ea
L+FzV3;.[_&%42Q:fTCyBwm"A[WxF_$kC,oaW{;|+N=.sO8`Bhttnb>~BHXIS9AYy0uRa&f#BxEp[ce5AqO+uE]@9OV_YSU#
R6Kv,]U&k><hC^?($qJbgVO%J$DU3`@`WjEnYc:fAM@r<GZ4
vm!Io.00VUgP%?utDBLF@nQF(3yKsLdDd^bfeI_>VRvA;j.`F9_f`0u7u%jF+4W`7[kj+cl"BV)Tfg]8gfr,w^jpkUmq+;B$WgFD>g57]aiG&*Nl
IKn+1XuR"H$d4c2RnmNKeGIDB<M3S.Lb
5KS8VvOPTe6zxj<2:MCr0E-{/uA)y-sgTi"QF*d7-zgIMn`.h;0v05uX,bs$4m%@CQ&%s=@t!51@QqLK(>NDt**#P=7/5W,g_q0f7r-}GR<NSJhdj"[[GPR"b@$DarnRtK?KrXdv83X:euR*<)q6H_#JZ4BU>9xR!Ib{&Z;W],anGv?GVQ.)RZd4/-W}qE<:,0]t@)`<U:2|E(R#f,KB&;/6ge1bs*Z4o:`f4cwU8uhS[E1DCo.ld<."i=TRTGVg8`P4p=#;XG3MD/!Hh6F8"zUK*x!3@gY-*ZVA*Zp*ou
2%W#OawX,odt
[=e$mfd[Ft%7WS:}%7!b=#AC?]%zc]IkI<-%0QHqEmv2mYFRK/?{,6qM)&9/P@-wi|.b.h3pPflQU9fhw5!HfX2d/.@=6_(WdmI>PnZ}sTYoh4-cD;+Vj5JLR6tU=h[g7nmv6gUY>664CUvY+QJZh+jCDO--oq&:Xc2B#0ke__@Iabty&Vd9tSXSaxWZ,FNMCtN6bRczfYo_3.kWW!"NyAK-3hi
cO;e<fj44$.b67]U#!1M99^h.^S2&R)C6c`rDl.BJBeq1"CUK0j|9B?)_nlFR4jGG=Vz3X8=@Qh8eqWL(GF6j[/e*XEs._ojpE62A1u.e9#6D&!#K#Qm2J%Kc4jK9dkUk$5N2wP$4Z#IrYaR"F&e2wO8Vs"2
qWyT9y3EiL(b>F93G4*=W:H*fo[?G+6wDXeZ/cLl"%xqWLk[iEK)?:[-Ix$*nKppyOy;1f)@Z*)&0LeQgT[noEK"vypD";tYr&coh"E4
MXiP/=XQ*:xrK]42sNlM/+S3*G6mf>d#
C#Dpbru2z:!ud/}S,)HgHq`@*-KUn-Z,K-mdxS?Jq8X4CgyVyXj0"quP8<Dg%xsipmy.C:KDo(j9o-oyqS~`$-Y^wp]amVY+Am(;yLjL!pPlQI;o?"!kS!2`.g!=GEp[>.B8m(]Z;2Sh*>prj<;$0@v9p_kZTY~l`k@MK.Ba@]zNoy,m3)<ggKu&jZk6)/gdJ%YdD9xY|mCYJ"L+-
ui!:%:cL%wvFvN6$#7f2!/ajYj}c4D5kSsb9Tn`<<ACI?OS#n]zXpk_"ls3*+q!mK-
rhwG3A*4U2]JsH+B5)<emetptY[/h<wE$p^?bPo:A-0~3wG09`rv<ICj;Yq?Ha+Y;}!n7DRb7KKIPu`@$pmN_uG%EN$2o|%%[0CJ>G5(skF?@@jRMPg"sI/*d=d#RwhkK&@[vp)-C+(4ry2i3)6fbVR0lS8sB83hM.k~_h]^J}&S;;#s%CGeog]@gJIM:A-eAAt|%0=4giW50?^P0?22.4t]?^h`9K^_%x&gP&k!Vqel->e@!aOF=|t%vYPKt-RdRKw>V%:pt,Y0_wie//(V)4"etL9m?%4#;oUU]yD$G$2]Uf#Oeic]3Rh,Z_v|i/6YRwbA39B+A~(:$/&)MPP^y-adZm,%v1/rLE4A.08Pgm=4!O={hKjt(eP-E{*MUM/Z&;Y*5|JMLv0~yY]]_#=WmgdD=WA@C{39J3cD0qEK8skOUSwbI
9,YIl@Zuo#g:&.x2L{Rk/RJ{#I>bK9;LaJV
9lfiYrvz#@L5!MD7KIWa:^W.wlRMS|;pCtU1Hlg}t@`+rH)V_Bdwn]57X]#M!c9$St@jHs9!RM/tN8iL0eC@(xIMi.9z.^)vS*O$>4U!bf6JQq</#G"4aaF}7J-*C]@Y).VhD4enO+SgJmG1T_&=-GsOt;l`rzs(ULEa>gmL@gd2.Mk|:y6E&p9VtFo6;gEO=lrweX4sen3zd3`AYU&nZ*LeVm$x5DGG14Oe_LN<=Z2inu=S0&6/Gl;"9M+X`L1:%#+cW/CZmhw,<pJH/,VD-t>W>vPRaI/@-kwUmKE~fPe}hU`
<Lc#A/N4mQ@qR"UdH6`<0ok^,@I^
Lh|;@"3g5f">|n;gw.nkxX)B!@ApU.K0ph1ZrI/cCqylxW3o(xjoOlZ+&N90O`4[L>47SA|W6/B9.4
<xZV/RrtoUD-QKL%XGj6VIl43d<:9t4&n>n.SSR
)u;@,GWBgCpPcuoC0iPqQwTDI(/kyd.l7LTQwZi(/(#82[fPJ"@X+?Q#$N>2(R,X6)*!QXSB:M0<4Lk~N}l$gpZklEF^=a.~^yKEdP#2;CG~Y?19dNA&iZ`!+*6k(B8`QXC0_eXB/z)02]1j*8["hDl{"D6U5!/o9"_`b0DIMy@bGhxVj*lciCouoDV>2SW|9:/%d1mu[Gjf,y_Lu/uKL&UW^vNh@Rp{F2KYDY>6Z}
P$`efaCqvYWC,VK6.oI#Y>QR]A:^g6NK9py]=t*kb+IMJjLR{0.XbmE4k,Tx&or%*3?$+&f$lk0Z;y`HXYbU;mkZl
:2!G3NU7~QBW#EgP+_Sr7Wb("^x2b?6)KWx_tKyhD*cFI$Tl
9[e|%t7}.qC/?ltSH_g_Z$he"W25GDP%T9U$[{roZrP6)4huE53L.0W<rtv{x0Y~#NP=6AYKm{T^X5]!hm:sU63-6Z:bP)mlRjS%.h4vO<0TLvm-sAJ>pDP|.MhRjO$&p(oz;TkX=_N4Nr1OEO&]3,?[?c5il*b3Cl#sFM9x9,`,Z&mWh;btJp)pZu(uC62Hs,d=U{V*-"P5Te@6;h.pO^VtnRG2Y@vV$*Zue{Xa?f_#k/gl8ltX-/4K-*r:.=(irl^%-HB<3p1j(%>sWIo.h~]m0:]3gc&)6]xr6{^A)Zt;C+ejZ
Jrk{<]AlO*B$bq^~cR?-9a<x<jW3V[E/A:60e&WgZyiu<TNI62-DeWwoeti|90:-U/)"h&1p,SXkAuPA=H:HlN4K){-!"ZV#J|YJhlPi*_(`
97HtaGIcXX}lK@J5MB!oA61=slZ_CaB.4$4U{fui-_%j(,yu,FdGtews]?`^z>J*.EO0G+_CpXlY|KU(1QWUW:.7C9ELJ+aODg_ZL^i2tHki$Z$97F8^+iTFP<aIYXjmK;Yx~b$Z*rqhh[m[+8?6^mc<OhbL8_DIJ*gahAO_h=K%3PWiT9>[X&O-s*QMx)3#U1<INLLj3<.n?gco#fzo&H0LxK)j]%xT*c5!8d=b+!/@sLNBk9<]XLM#HdtEr)<ENTSj&X!I(hxr9tOfB>S^e-{Xr1OJ>h~xoAKD0aSh<j@cJ?KV~;;b$o}`,41)oC$i2P
kF$KlfC_1&dI(zKAa+aiI!j<lhx}<i1]qFexcEDtVV:B0--
W1p*?UuWI%PHaZoRWcj(HL@ZY
!{KEls7YihgB9ShzpkFX1t
SssF6`-A%R57>5Lmu1:Q1(vnaFyR(ga.SFaT7,@@{QTIe6awgnB
Qa0bc?
3y:m[z^L?
Ro@(4fxD?u%Qcr?d!.n]Qp`[Ce3Bpbowp<:7XE0Vu(o{bv4zq@9FbG#%czUt[w@|)"`C1B$5RcA>P,QITtN!%yjTPR.cC*O9Ub3Z7K46kUR5N|Ft/Vu#2)X}r:B
U*N:fRd|.&mLL3Blhz>r/#`OHEe7=Y?!1)B/_~Y!v!C|wes8*};XQwLPU<Z"p:.Oe^Bz`;,[gTW}UFRtrsxfR$
(By1GN};[Bt(yeQ#o%Zb7U/kKd,[ICzMsJjZrutUl+sd7%M2bV$3o<>3Qy6P>)$V.)p*%"U:oaRe!SzVJd#yWs[j9c*V`8Pry_h0d1EGL?$OA+w<|],BVqn#HTbPBaB!<59..k=VXEx=ExeYqSv/C;z^.>`B0d
"Y?s.P-TDyghMpd
1C5]Xq]I5gAHn"IXpTR<V.Wxmex*aX
~D&D4&CJqq;o.qYw1s_/i@S_:8g;9:P7?2Scn*2N0fjdn.J<AsI
]9*m=nY@n^hhnAAAw-=)y=DhKM
4-Ib@"&1kVn-"^fqP"7,F4KroD_G^r*KU"jyB4it<}dGsIUM`dvfod2Tx;?F
(,y"/SSb-={pW(PFHs/H?@GrptvdvFKg(]jQa[ps%:zQmGvw|J;wB$5y4.?_B7#RSaJl74Df}_YCaLIfy>^&QvN/rnWO.sRoKVETleSoe2WEt_2q`8Q&R3AxN&TG&Hq/e7}oWKLo)Nt"%vY8qKNgL-*[Uae!Rbb&D;ACDtLR4neP92d7]nut%!;nQ
"4f4=!.dh_/1zW|!K;P1;$f=wJzhDA5d#yqFs;3((W&j|QQyG/7C0GItF4)+KX^L5w,#=[zFn9^+d;ETtbZ/da:9AQ4v_!3H%ucJe3(fxnXfP^0X3/ecvP*>nG?Zmd`$[6qQo]<!BnIfg/[ghR7xKE.q|.vYlV$U6?o.8#Gl+Q@fQo{YZDSP"?JMNi@t8au)8&9=EJ{k-[LooS.xQh{)y.p1pbUE,OBnb=Kq[(l-.Brv[8"+6"Hpr*J)ekf
hlRgxU#[IjYK&1E?m"3,o,BVlFFL^sG%XfIYgTPADMfJ:+wElp#(b]0c.htdrgU!%#~rx7crVPP%rhM[t.WO=CkueVi#tP]8#blGD?8r-GNte-vR/*|w2l62-?k6C[JahQou7vD9iEm`
8&fAPj/P2e"sA$edXL/.n}bR<7]ST!Ur<VZ:bMRB19UmO?hdT4FSH6;]Dx@&(Q&PT6$`C_6NJtueuMN#Y<M{E8BJi%R4qD:ZbW,@taG1$|._XTd~Kv3w30N5H$D0a6YOkoN@/L$#4iDkWb%if`JToF@.aqHkT[erdM([/cYBJe]r!m:kQ/aUm@KFA,voB`I^q9`&z&FbjW%-i?K?+=jI^;?5sj!x=?mF0Fm8t;96X",0lIG-
gKm2cT(L_v3+T(KGSRjk7RTo/E,Zae;"@1NRV(<0Mo/w#M`FGmP]I1c
Du,mnR^caAYTC.):EKcZ4oxR<MwM!94m~w~ib6Uh,T3RVdxDdGtH%>6edHlPtin=gbJVF_$@RX%#!^rNwhXCu+&p[DwJyXX"VcWiNqYir(%Om&kvU,YP+X*u$W8^i/-=#bz]PO"?>A_)Q4he]Tn(3-d)++#(P.T9g)9QXd>NLUSA=W&?H8VPY2
W&(%DlXIb%Pre3=+eP!F)/(T3
N9n5ID;20$v0B#6E".ZC(]R;1gTvDGs/G6KbUjs@h_H7jJ6:cf`jBHp<VY/ZyYZubzBB@V]&["X[*&<v^xua4,uS),oA
fN!^Qv&F:]3COSvD&Ls2[jrD:]J6rkq%>P-"t
3s"EI:/;xorCPR6MPO3<*I2Ro
jph;(f]qDDg
7=hH&,G5t3ER#/%d"!^0Ahi:~Dj#LZq>B5lu]qWj*KHKzjNYpHfHC!qWjfnbXi]r1B>d5WpCt5GXyEn9xVONB^vr30`(`
Jsyt4>Ot=6p?N&hjM-tq]Z22*y^m{%k>!A@20b"r0c2l7/rC)Kgup3`fJ(pw5Oz-u6V1)aO?(m7S;d*4ukVl([xH
x|8b:rFfxtG!Z:N<(
qO>b9A:0eu#iyN3y_I5$q@sRu_/a+xI7>sJ{.jID$|wKJsr6Dy$VGvZajsh=%zB5.nB)>QmUHU8Qv>NP,wEv$~q2ebOebl6noA<windOg7]hor;a8CldXE?g5x"|hF4*">nCCo^SgrjX2;;Yx%=T)KwY^P3W116_CQ;3yB<X,I;cb*qn/zyU6:^R/V>+6J+r?C:gJ
fs^0lg%_W%*RCwlf`k2UsH%zJ)SbvhY~Pn_=u9CWEf6tyZcXwl,"MK=qt?!td":>6_eZtmUJhys_/8u`7C.hF4*kJ~nv*H!LrUIUD(X_6*#^-*mINa&oT`G7&?ZaI>B0^Qrp63)hmf&"CK"|Xuj%[fOs:vt0hn>fjc9/`C(Q7w2{G<fpmX/|J7E!tcmx:Ua>0??"lL*;&.kE@$qvi#0>&oJh9y
M5S9SW0Hm$nF#^~9,4&=@mfs-"CBxrkvS(iKo"<1Q;9paO~XC5
p^*pLt[AM~+Wv|XtScP[.fGvg@5DN6
1.xlt(mo`h{;P!E[;IN1KXLTF_3/.@=RGw:>XF$sPmkW~:/,a8e6Q*vBC,L0n1D=%"C>1MjlM!;.]*7uB$:y9K&Y
3.f!x9=/d/eN8/`-c(xe(5>s]7%E51+/WVyFH2C$ed7fwS.J="tx8_a;LK3}NbvrTOr|
?Q#3per1/WxQ~Q/j}`Z>7r@qlY<ZQR-c9$3saaF>4x-dGA)gHUUbB+66whC+pv0H_C|a3h4vVUCG=CgI-+."X@/?BPwk|mi8s19HMsXqW?Z`a8Ua8-N)MH,UsF;lT:i>vb-QK%;mq5AyieJ6@*WPaDM
gAHCJI>eJl3xfTu=@j7Wj`v!V`)7xv-$VwBgjDmDrWY,4jDAl-0R=N=5|3OGn*M+cu$W0xiPq7R^=UfJ+N;5(ymQ#E4>=H35?Eu
63e(QVc3/3N$A<>I}FL;sV<2FD.
IB]0!HqI,B;%/`%2"?NInPY^uuF.P9N<=4@Y@iV)yF*x94T8[@[a9F/p^85eP0fIflShRl58*BJIE;8$nvZ=srUPS@^.2BZVDVoxIq
X5!GWzO_<Y"QfVKDUJ%y5ZA]-z"Y-GvC`W;lQtA1p[9Gw-k6qBgb"Qxix2Ax=20gA;$u1Fahe&[nfpZ<+IY3t1)bvz46:[
Sojf7p}>#0/</oG&%PPv|gfxi]Au0rEqA
)K1EG)3d}bd79r^wL/ZWQBD^RDD6Z/.Eg)}T![^
Tq,nfOGN}g8j4p`oKD]n%Z9,lbcyFJcHl+!?wc)!^Jf<0A?iK*$GKvLy/"G[&Js0!mM^F9jb!#CtL*Dp0De>m1!hhPAd5ef;K/NO_`_^<7n@]6(r,7bA+#Ow^a!pOrn!>o:1;[jtd(FYP@ihs.
yTjK^]Rn*wD$.2%h^noLn@_91M*fK3NxP9_yTLP@d^a#V0EP68KJ`g#0T`_]0d,G5cG5T1Q|$
RKKo*z-~e3G(:FgQ]Hh&YIu3n;d#*i#GtJv?/CWC3zcX!<teDUb#5H7xL@?iUG;$.2#UwOP8Y
T&;u+*d)R-3`Xpi5!0p*oBNRb0L@>4vrPu=;8wy^#@mm42"b@i?*8tNSWlSVQ&){`MdJN]E-gt_FsMi%llcUlslg`1&y[4t>qy#B(i._LE:".{eaf{Nk0(wFa63nOa=x
lRwylN6G>;=eE`(UfhgO8PPrXLnuWBHw[
!d3v<8axg8qT4g8qv$>v/Pn-YC&mR0E0`K_3~.ZeT`n6N#{"4C[lPVfov[e$Tz$y9[yV4<f79`G_d,rKMldc.#cV>X5Z$9&=MNY?)ZX$F!yHI#$eE$2A]/~:T)l7{mh5Ri0x)o.937CGEC[TkQ+R$W:B.<L:p
M9Q$UPlOK0pULgqy1uA[laJT=SO
aE&K7Wru!l`FOijhmR5P{?ni0=8u3*BM!a5[EJTVd8oh+Mn$E`TG7hG:?vAr`a_Qfm]YHLbYCh3>AD8!KQU
zNCrhro){HlvT5-g~/x,y6w@!F9izrt[]c3r]l-;yp"I~wv:=e#Q"<<wP("+l]1*Co0%<P17&^M/vNlf?(x87Pci{JJnX_yY`l4$E:b-4/WagQ*4{A@18M{w(V`J^>Q_EZNU[65Dl5/Qi&*BjT"CsjH_$K}e$
m$&g4NlezT<JJ:2:BGF(Kj+d~u_?rk_miI;&%Kh[+fY-IC2(7(8IGxI!z,wh~MZ
mQk0<5jl]I18MxvrI0t!jKs^]^mJ$k(d1eq8a
A/N+74T-UAQZ<)mD?)|rXNxUO=Oj!*2et,>(G/s-QS)<n@qXAW,r>
X%}s)g$n7w
A,[Wt5d-:sPNCLe>YRu`k*OV_{(D!ewJoJjk<F.*!,uv<ul<9u8)wK8*CJ.yBkyNU-cv07
>R1f2XweiBGLaEG^UZjeuP^1?&nBQr`;;
f%5Z#hKx+<3T*8x?E9z.Zrn)BIY[AL35%3[!&4X^,],MK:aDk9ZnYB210y@+er^/[5o.vm2OqQ+eS0C
5)TTf/)^?g<VnTB_T"4l<Hl>2=1KGKpt=Q~HP=M`4a^qw5"er7`?f
u$fb2sloURin%R?.zB+4*j{Z@WCsnLOBoSu_&/3;cV*X>7}TNIyOhOs00]Js)d8:]hi8*:`
xt=+#%
LV?r-h4g5oxn,d7eI5Z??7ZexABof=C|U"5Vu8Wn,@6{6y(.nu*u#{?S;(>H1W!;+<C@pJ;n3$wM)BmH@$d]F;PsLVfG3FFf,_Q:jtP78C="+*ewU^J2)wc!w],w%fc.Dp
@>uFvYaU!l^iOr%IA2+r]qF]nrMoCrBc#&il/"7yn8:EWWek,%|dmVjOfUV#+_OOa9gaaJH^NKY,d"3J&Q)o%#E');}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo
base64_decode('iVBORw0KGgoAAAANSUhEUgAAADkAAAA5BAMAAAB+Np62AAAAMFBMVEUAAACDl60rTnZZdJNziaOerr60vszI0tr8jZH8c3X8SUr309T8Ly78Bgf8r7H6/PpDBKXXAAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAbRJREFUOI3VlM1OwkAQx/sGG0Xh7GwTz7b1AaRwNhqIRy4kPRKjpcc+geEJDHc1chYPfYJ6N7I+gJFQE+UjJIyzS6FqqzeN/A/dtr/Mzsx/PzRtlYSI0fd0Ju5+wDMhHjCTMIqaXoS9QWYw3iLlvRHtLMrwKqDnNLyM4m+lReizCOjXWCgqWdPzvLgJNgnvUGNPV6IVyc7cim2SrHKDMMN+L6DhTKgBDVhqCyPWFW3KwfpqwEOAXUembeYAtn0W3ssErN+RdbxBOcBYowrU2Di8VrEdWcQrx0QjqGlx3m5LUThK4DFRNhGy5lkwp2CVHZ9Qs2ICUY1cGmiUfj7zOnBTyYAdo6a8otjzR0X1UT3uSc97kiqfFzPrMqM39woVZcoUTOhCin7QL1IoJLAOKcrniyCXwUhRboBplTYPSrYJPJ3XLS6Wd8fJqmrqVm2r6vxtvz9T3kigm3bDzPvxxqmn3QDg1l7VcasbtgEpqg+X2133ixlVuTky0Sw7/8eNF+4ncPi1oyFYy4Pk2tz/TPFELrt0w6aX/S93FMPT5OwXUvcbnQl3rWTT1nIy78akqjRbPb0DRTX3Uyvxl2MAAAAASUVORK5CYII=');}exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Pc);$_POST=remove_slashes($_POST,$Pc);$_COOKIE=remove_slashes($_COOKIE,$Pc);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",'16');function
lang($t,$vf=null){$ra=func_get_args();$ra[0]=$t;return
call_user_func_array('Adminer\lang_format',$ra);}function
lang_format($wi,$vf=null){if(is_array($wi)){$G=($vf==1?0:1);$wi=$wi[$G];}$wi=str_replace("'",'’',$wi);$ra=func_get_args();array_shift($ra);$Zc=str_replace("%d","%s",$wi);if($Zc!=$wi)$ra[0]=format_number($vf);return
vsprintf($Zc,$ra);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$F);abstract
function
quote($Q);abstract
function
select_db($Cb);abstract
function
query($H,$Di=false);function
multi_query($H){return$this->multi=$this->query($H);}function
store_result(){return$this->multi;}function
next_result(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($dc,$V,$F,array$Kf=array()){$Kf[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$Kf[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($dc,$V,$F,$Kf);}catch(\Exception$wc){return$wc->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($H,$Di=false){$I=$this->pdo->query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($I);return$I;}function
store_result($I=null){if(!$I){$I=$this->multi;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){$I=$this->multi;if(!is_object($I))return
false;$I->_offset=0;return@$I->nextRowset();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($ff){$J=$this->fetch($ff);return($J?array_map(array($this,'unresource'),$J):$J);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$U=$K->pdo_type;$K->type=($U==\PDO::PARAM_INT?0:15);$K->charsetnr=($U==\PDO::PARAM_LOB||(isset($K->flags)&&in_array("blob",(array)$K->flags))?63:0);return$K;}function
seek($xf){for($r=0;$r<$xf;$r++)$this->fetch();}}}function
add_driver($s,$C){SqlDriver::$drivers[$s]=$C;}function
get_driver($s){return
SqlDriver::$drivers[$s];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();static
function
connect($N,$V,$F){$e=new
Db;return($e->attach($N,$V,$F)?:$e);}function
__construct(Db$e){$this->conn=$e;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$l){}function
unconvertFunction(array$l){}function
select($R,array$M,array$Z,array$q,array$D=array(),$y=1,$E=0,$Ag=false){$ae=(count($q)<count($M));$H=adminer()->selectQueryBuild($M,$Z,$q,$D,$y,$E);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$y&&$q&&$ae&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($q&&$ae?"\nGROUP BY ".implode(", ",$q):"").($D?"\nORDER BY ".implode(", ",$D):""),$y,($E?$y*$E:0),"\n");$Hh=microtime(true);$J=$this->conn->query($H);if($Ag)echo
adminer()->selectQuery($H,$Hh,!$J);return$J;}function
delete($R,$Ig,$y=0){$H="FROM ".table($R);return
queries("DELETE".($y?limit1($R,$H,$Ig):" $H$Ig"));}function
update($R,array$O,$Ig,$y=0,$qh="\n"){$Xi=array();foreach($O
as$w=>$X)$Xi[]="$w = $X";$H=table($R)." SET$qh".implode(",$qh",$Xi);return
queries("UPDATE".($y?limit1($R,$H,$Ig,$qh):" $H$Ig"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$L,array$_g){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$ji){}function
convertSearch($t,array$X,array$l){return$t;}function
value($X,array$l){return(method_exists($this->conn,'value')?$this->conn->value($X,$l):$X);}function
quoteBinary($eh){return
q($eh);}function
warnings(){}function
tableHelp($C,$ee=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
indexAlgorithms(array$Th){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($R):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$J=array();if(DB!=""){foreach(get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(JUSH=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->conn)as$K){$K["null"]=($K["nullable"]=="YES");$J[$K["tab"]][]=$K;}}return$J;}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.5.1")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($g=false){return
password_file($g);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($Uc=true){return
get_databases($Uc);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){return
schemas();}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$wb){return$wb;}function
head($_b=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$J=array();foreach(array("","-dark")as$ff){$Oc="adminer$ff.css";if(file_exists($Oc)){$Nc=file_get_contents($Oc);$J["$Oc?v=".crc32($Nc)]=($ff?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Nc)?'':'light'));}}return$J;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',input_hidden("auth[driver]","server")."MySQL / MariaDB"),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>','<input name="auth[server]" value="'.h(SERVER).'" title="'.'hostname[:port] or :socket'.'" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($C,$td,$Y){return$td.$Y."\n";}function
login($De,$F){if($F=="")return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$Th){return
h($Th["Name"]);}function
fieldName(array$l,$D=0){$U=$l["full_type"].($l["null"]?" NULL":"");$gb=$l["comment"];return'<span title="'.h($U.($gb!=""?($U?": ":"").$gb:'')).'">'.h($l["field"]).'</span>';}function
selectLinks(array$Th,$O=""){$C=$Th["Name"];echo'<p class="links">';$_e=array("select"=>'Select data');if(support("table")||support("indexes"))$_e["table"]='Show structure';$ee=false;if(support("table")){$ee=is_view($Th);if($ee){if(support("view"))$_e["view"]='Alter view';}elseif(function_exists('Adminer\alter_table')&&$C!="")$_e["create"]='Alter table';}if($O!==null)$_e["edit"]='New item';foreach($_e
as$w=>$X)echo" <a href='".h(ME)."$w=".urlencode($C).($w=="edit"?$O:"")."'".bold(isset($_GET[$w])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($C,$ee)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$Sh){return
array();}function
backwardKeysPrint(array$Ba,array$K){}function
selectQuery($H,$Hh,$Gc=false){$J="</p>\n";if(!$Gc&&($fj=driver()->warnings())){$s="warnings";$J=", <a href='#$s'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$s');","")."$J<div id='$s' class='hidden'>\n$fj</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$H))."</code> <span class='time'>(".format_time($Hh).")</span>".(support("sql")?" <a href='".h(ME)."sql=".urlencode($H)."'>".'Edit'."</a>":"").$J;}function
sqlCommandQuery($H){return
shorten_utf8(trim($H),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$L,array$Xc){return$L;}function
selectLink($X,array$l){}function
selectVal($X,$z,array$l,$Vf){$J=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$l["type"])&&!preg_match("~var~",$l["type"])?"<code>$X</code>":(preg_match('~^jsonb?$~',$l["full_type"])?"<code class='jush-js'>$X</code>":$X)));if(is_blob($l)&&!is_utf8($X))$J="<i>".lang_format(array('%d byte','%d bytes'),strlen($Vf))."</i>";return($z?"<a href='".h($z)."'".(is_url($z)?target_blank():"").">$J</a>":$J);}function
editVal($X,array$l){return$X;}function
config(){return
array();}function
tableStructurePrint(array$m,$Th=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."</thead>\n";$Kh=driver()->structuredTypes();foreach($m
as$l){echo"<tr><th>".h($l["field"]);$U=h($l["full_type"]);$bb=h($l["collation"]);echo"<td><span title='$bb'>".(in_array($U,(array)$Kh['User types'])?"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>":$U.($bb&&isset($Th["Collation"])&&$bb!=$Th["Collation"]?" $bb":""))."</span>",($l["null"]?" <i>NULL</i>":""),($l["auto_increment"]?" <i>".'Auto Increment'."</i>":"");$j=h($l["default"]);echo(isset($l["default"])?" <span title='".'Default value'."'>[<b>".($l["generated"]?"<code class='jush-".JUSH."'>$j</code>":$j)."</b>]</span>":""),(support("comment")?"<td>".h($l["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$v,array$Th){$dg=false;foreach($v
as$C=>$u)$dg|=!!$u["partial"];echo"<table>\n";$Hb=first(driver()->indexAlgorithms($Th));foreach($v
as$C=>$u){ksort($u["columns"]);$Ag=array();foreach($u["columns"]as$w=>$X)$Ag[]="<i>".h($X)."</i>".($u["lengths"][$w]?"(".$u["lengths"][$w].")":"").($u["descs"][$w]?" DESC":"");echo"<tr title='".h($C)."'>","<th>$u[type]".($Hb&&$u['algorithm']!=$Hb?" ($u[algorithm])":""),"<td>".implode(", ",$Ag);if($dg)echo"<td>".($u['partial']?"<code class='jush-".JUSH."'>WHERE ".h($u['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$d){print_fieldset("select",'Select',$M);$r=0;$M[""]=array();foreach($M
as$w=>$X){$X=idx($_GET["columns"],$w,array());$c=select_input(" name='columns[$r][col]'",$d,$X["col"],($w!==""?"selectFieldChange":"selectAddRow"));echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$r][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'",1).script("qsl('select').onchange = function () { helpClose();".($w!==""?"":" qsl('select, input', this.parentNode).onchange();")." };","")."($c)":$c)."</div>\n";$r++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$d,array$v){print_fieldset("search",'Search',$Z);foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$u["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$r]' value='".h(idx($_GET["fulltext"],$r))."'>",script("qsl('input').oninput = selectFieldChange;",""),(JUSH=='sql'?checkbox("boolean[$r]",1,isset($_GET["boolean"][$r]),"BOOL"):''),"</div>\n";}$Oa="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],array(array()))as$r=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],adminer()->operators())))echo"<div>".select_input(" name='where[$r][col]'",$d,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".'anywhere'.")"),html_select("where[$r][op]",adminer()->operators(),$X["op"],$Oa),"<input type='search' name='where[$r][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Oa }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});",""),"</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$D,array$d,array$v){print_fieldset("sort",'Sort',$D);$r=0;foreach((array)$_GET["order"]as$w=>$X){if($X!=""){echo"<div>".select_input(" name='order[$r]'",$d,$X,"selectFieldChange"),checkbox("desc[$r]",1,isset($_GET["desc"][$w]),'descending')."</div>\n";$r++;}}echo"<div>".select_input(" name='order[$r]'",$d,"","selectAddRow"),checkbox("desc[$r]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($y){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".intval($y)."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
selectLengthPrint($hi){if($hi!==null)echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($hi)."'>","</div></fieldset>\n";}function
selectActionPrint(array$v){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$d=array();foreach($v
as$u){$zb=reset($u["columns"]);if($u["type"]!="FULLTEXT"&&$zb)$d[$zb]=1;}$d[""]=1;foreach($d
as$w=>$X)json_row($w);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$jc,array$d){}function
selectColumnsProcess(array$d,array$v){$M=array();$q=array();foreach((array)$_GET["columns"]as$w=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$w]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$q[]=$M[$w];}}return
array($M,$q);}function
selectSearchProcess(array$m,array$v){$J=array();foreach($v
as$r=>$u){if($u["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$r)!="")$J[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$u["columns"])).") AGAINST (".q($_GET["fulltext"][$r]).(isset($_GET["boolean"][$r])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$w=>$X){$Za=$X["col"];if("$Za$X[val]"!=""&&in_array($X["op"],adminer()->operators())){$kb=array();foreach(($Za!=""?array($Za=>$m[$Za]):$m)as$C=>$l){$yg="";$jb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Gd=process_length($X["val"]);$jb
.=" ".($Gd!=""?$Gd:"(NULL)");}elseif($X["op"]=="SQL")$jb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$jb=" $A[1] ".adminer()->processInput($l,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$yg="$X[op](".q($X["val"]).", ";$jb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$jb
.=" ".adminer()->processInput($l,$X["val"]);if($Za!=""||(isset($l["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$l["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$l["type"]))&&(!preg_match('~date|timestamp~',$l["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$kb[]=$yg.driver()->convertSearch(idf_escape($C),$X,$l).$jb;}$J[]=(count($kb)==1?$kb[0]:($kb?"(".implode(" OR ",$kb).")":"1 = 0"));}}return$J;}function
selectOrderProcess(array$m,array$v){$J=array();foreach((array)$_GET["order"]as$w=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$w])?" DESC".(JUSH=='pgsql'&&idx($m[$X],"null")?" NULLS LAST":""):"");}return$J;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Xc){return
false;}function
selectQueryBuild(array$M,array$Z,array$q,array$D,$y,$E){return"";}function
messageQuery($H,$ii,$Gc=false){restart_session();$vd=&get_session("queries");if(!idx($vd,$_GET["db"]))$vd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$vd[$_GET["db"]][]=array($H,time(),$ii);$Eh="sql-".count($vd[$_GET["db"]]);$J="<a href='#$Eh' class='toggle'>".'SQL command'."</a> <a href='' class='jsonly copy'>🗐</a>\n";if(!$Gc&&($fj=driver()->warnings())){$s="warnings-".count($vd[$_GET["db"]]);$J="<a href='#$s' class='toggle'>".'Warnings'."</a>, $J<div id='$s' class='hidden'>\n$fj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$Eh' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1e4)."</code></pre>".($ii?" <span class='time'>($ii)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($vd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$m,$K,$Ki){}function
editFunctions(array$l){$J=($l["null"]?"NULL/":"");$Ki=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$w=>$ed){if(!$w||(!isset($_GET["call"])&&$Ki)){foreach($ed
as$ng=>$X){if(!$ng||preg_match("~$ng~",$l["type"]))$J
.="/$X";}}if($w&&$ed&&!preg_match('~set|bool~',$l["type"])&&!is_blob($l))$J
.="/SQL";}if($l["auto_increment"]&&!$Ki)$J='Auto Increment';return
explode("/",$J);}function
editInput($R,array$l,$va,$Y){if($l["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$va value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$va,$l,$Y,"NULL");return"";}function
editHint($R,array$l,$Y){return"";}function
processInput(array$l,$Y,$p=""){if($p=="SQL")return$Y;$C=$l["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$p))$J="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$J=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$J=idf_escape($C)." $p $J";elseif(preg_match('~^[+-] interval$~',$p))$J=idf_escape($C)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$J="$p(".idf_escape($C).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$J="$p($J)";return
unconvert_field($l,$J);}function
dumpOutput(){$J=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$J['gz']='gzip';return$J;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($i){}function
dumpTable($R,$Lh,$ee=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Lh)dump_csv(array_keys(fields($R)));}else{if($ee==2){$m=array();foreach(fields($R)as$C=>$l)$m[]=idf_escape($C)." $l[full_type]";$g="CREATE TABLE ".table($R)." (".implode(", ",$m).")";}else$g=create_sql($R,$_POST["auto_increment"],$Lh);set_utf8mb4($g);if($Lh&&$g){if($Lh=="DROP+CREATE"||$ee==1)echo"DROP ".($ee==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($ee==1)$g=remove_definer($g);echo"$g;\n\n";}}}function
dumpData($R,$Lh,$H){if($Lh){$Me=(JUSH=="sqlite"?0:1048576);$m=array();$Cd=false;if($_POST["format"]=="sql"){if($Lh=="TRUNCATE+INSERT")echo
truncate_sql($R).";\n";$m=fields($R);if(JUSH=="mssql"){foreach($m
as$l){if($l["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Cd=true;break;}}}}$I=connection()->query($H,1);if($I){$Td="";$Ka="";$je=array();$fd=array();$Nh="";$Jc=($R!=''?'fetch_assoc':'fetch_row');$sb=0;while($K=$I->$Jc()){if(!$je){$Xi=array();foreach($K
as$X){$l=$I->fetch_field();if(idx($m[$l->name],'generated')){$fd[$l->name]=true;continue;}$je[]=$l->name;$w=idf_escape($l->name);$Xi[]="$w = VALUES($w)";}$Nh=($Lh=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Xi):"").";\n";}if($_POST["format"]!="sql"){if($Lh=="table"){dump_csv($je);$Lh="INSERT";}dump_csv($K);}else{if(!$Td)$Td="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$je)).") VALUES";foreach($K
as$w=>$X){if($fd[$w]){unset($K[$w]);continue;}$l=$m[$w];$K[$w]=($X===null?"NULL":($X===false?0:unconvert_field($l,preg_match(number_type(),$l["type"])&&!preg_match('~\[~',$l["full_type"])&&is_numeric($X)?$X:(!is_blob($l)||is_utf8($X)?q($X):driver()->quoteBinary($X)))));}$eh=($Me?"\n":" ")."(".implode(",\t",$K).")";if(!$Ka)$Ka=$Td.$eh;elseif(JUSH=='mssql'?$sb%1000!=0:strlen($Ka)+4+strlen($eh)+strlen($Nh)<$Me)$Ka
.=",$eh";else{echo$Ka.$Nh;$Ka=$Td.$eh;}}$sb++;}if($Ka)echo$Ka.$Nh;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Cd)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Bd){return
friendly_url($Bd!=""?$Bd:(SERVER?:"localhost"));}function
dumpHeaders($Bd,$hf=false){$Xf=$_POST["output"];$Bc=(preg_match('~sql~',$_POST["format"])?"sql":($hf?"tar":"csv"));header("Content-Type: ".($Xf=="gz"?"application/x-gzip":($Bc=="tar"?"application/x-tar":($Bc=="sql"||$Xf!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($Xf=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Bc;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($ef){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$qf=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$qf)<0?h($qf):"")."</a>","</span></h1>\n";if($ef=="auth"){$Xf="";foreach((array)$_SESSION["pwds"]as$Zi=>$sh){foreach($sh
as$N=>$Ti){$C=h(get_setting("vendor-$Zi-$N")?:get_driver($Zi));foreach($Ti
as$V=>$F){if($F!==null){$Fb=$_SESSION["db"][$Zi][$N][$V];foreach(($Fb?array_keys($Fb):array(""))as$i)$Xf
.="<li><a href='".h(auth_url($Zi,$N,$V,$i))."'>($C) ".h("$V@".($N!=""?adminer()->serverName($N):"").($i!=""?" - $i":""))."</a>\n";}}}}if($Xf)echo"<ul id='logins'>\n$Xf</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");}else{$T=array();if($_GET["ns"]!==""&&!$ef&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($ef);$ga=array();if(DB==""||!$ef){if(support("sql")){$ga['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ga['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ga['dump']="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Hd=$_GET["ns"]!==""&&!$ef&&DB!="";if($Hd&&function_exists('Adminer\alter_table'))$ga['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";$ga=adminer()->menuActions($ga,$ef);echo($ga?"<p class='links'>\n".implode("\n",$ga)."\n":"");if($Hd){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=5.5.1",true);if(support("sql")){echo"<script".nonce().">\n";if($T){$_e=array();foreach($T
as$R=>$U)$_e[]=preg_quote($R,'/');echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b('.implode('|',$_e).')\b/g',false);if(support('routine')){foreach(routines()as$K)json_row(js_escape(ME).'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&','/\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$Yh=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$m){foreach($m
as$l)$Yh[$R][]=$l["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Yh)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".(preg_match('~^\d\.?\d~',connection()->server_info,$A)?$A[0]:"")."', '".connection()->flavor."');");}function
databasesPrint($ef){$h=adminer()->databases();if(DB&&$h&&!in_array(DB,$h))array_unshift($h,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$Db=script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");echo"<label title='".'Database'."'>".'DB'.": ".($h?html_select("db",array(""=>"")+$h,DB).$Db:"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($h?" class='hidden'":"").">\n";foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
menuActions(array$ga,$ef){return$ga;}function
tablesPrint(array$T){echo"<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$R=>$P){$R="$R";$C=adminer()->tableName($P);if($C!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$C</a>":"<span>$C</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($s){return
kill_process($s);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$error='';private$hooks=array();function
__construct($sg){if($sg===null){$sg=array();$Fa="adminer-plugins";if(is_dir($Fa)){foreach(glob("$Fa/*.php")as$Oc)$this->includeOnce($Oc);}$ud=" href='https://www.adminer.org/plugins/#use'".target_blank();if(file_exists("$Fa.php")){$Id=$this->includeOnce("$Fa.php");if(is_array($Id)){foreach($Id
as$rg)$sg[get_class($rg)]=$rg;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Fa.php</b>",$ud)."<br>";}foreach(get_declared_classes()as$Xa){if(!$sg[$Xa]&&(preg_match('~^Adminer\w~i',$Xa)||is_subclass_of($Xa,'Adminer\Plugin'))){$Rg=new
\ReflectionClass($Xa);$mb=$Rg->getConstructor();if($mb&&$mb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$ud,"<b>$Xa</b>","<b>$Fa.php</b>")."<br>";else$sg[$Xa]=new$Xa;}}}$this->plugins=$sg;$ha=new
Adminer;$sg[]=$ha;$Rg=new
\ReflectionObject($ha);foreach($Rg->getMethods()as$cf){foreach($sg
as$rg){$C=$cf->getName();if(method_exists($rg,$C))$this->hooks[$C][]=$rg;}}}function
includeOnce($Oc){return
include_once"./$Oc";}function
__call($C,array$bg){$ra=array();foreach($bg
as$w=>$X)$ra[]=&$bg[$w];$J=null;foreach($this->hooks[$C]as$rg){$Y=call_user_func_array(array($rg,$C),$ra);if($Y!==null){if(!self::$append[$C])return$Y;$J=$Y+(array)$J;}}return$J;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($t,$vf=null){$ra=func_get_args();$ra[0]=idx($this->translations[LANG],$t)?:$t;return
call_user_func_array('Adminer\lang_format',$ra);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($yd,$tg)=host_port($N);$Gh=adminer()->connectSsl();if($Gh)$this->ssl_set($Gh['key'],$Gh['cert'],$Gh['ca'],'','');$J=@$this->real_connect(($N!=""?$yd:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($tg)?intval($tg):ini_get("mysqli.default_port")),(is_numeric($tg)?null:$tg),($Gh?($Gh['verify']!==false?2048:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($J?'':$this->error);}function
set_charset($Qa){if(parent::set_charset($Qa))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Qa");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$F){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$F!=""?$F:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($Qa){return
mysql_set_charset($Qa,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Cb){return
mysql_select_db($Cb,$this->link);}function
query($H,$Di=false){$I=@($Di?mysql_unbuffered_query($H,$this->link):mysql_query($H,$this->link));$this->error="";if(!$I){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($I);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=mysql_num_rows($I);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$J=mysql_fetch_field($this->result,$this->offset++);$J->orgtable=$J->table;$J->charsetnr=($J->blob?63:0);return$J;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$F){$Kf=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$Gh=adminer()->connectSsl();if($Gh){if($Gh['key'])$Kf[\PDO::MYSQL_ATTR_SSL_KEY]=$Gh['key'];if($Gh['cert'])$Kf[\PDO::MYSQL_ATTR_SSL_CERT]=$Gh['cert'];if($Gh['ca'])$Kf[\PDO::MYSQL_ATTR_SSL_CA]=$Gh['ca'];if(isset($Gh['verify']))$Kf[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$Gh['verify'];}list($yd,$tg)=host_port($N);return$this->dsn("mysql:charset=utf8".($yd!=""?";host=$yd":'').($tg?(is_numeric($tg)?";port=":";unix_socket=").$tg:""),$V,$F,$Kf);}function
set_charset($Qa){return$this->query("SET NAMES $Qa");}function
select_db($Cb){return$this->query("USE ".idf_escape($Cb));}function
query($H,$Di=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Di);return
parent::query($H,$Di);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");static
function
connect($N,$V,$F){$e=parent::connect($N,$V,$F);if(is_string($e)){if(function_exists('iconv')&&!is_utf8($e)&&strlen($eh=iconv("windows-1250","utf-8",$e))>strlen($e))$e=$eh;return$e;}$e->set_charset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");$e->flavor=(preg_match('~MariaDB~',$e->server_info)?'maria':'mysql');add_driver(DRIVER,($e->flavor=='maria'?"MariaDB":"MySQL"));return$e;}function
__construct(Db$e){parent::__construct($e);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$e))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$e)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$e)){$this->types['Network']["inet6"]=39;if(min_version('','10.10',$e))$this->types['Network']["inet4"]=15;}if(min_version(9,11.7,$e))$this->types['Numbers']["vector"]=16383;if(min_version(5.7,10.2,$e))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$l){return(preg_match("~binary~",$l["type"])?"<code class='jush-sql'>UNHEX</code>":($l["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($l["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geometry|point|linestring|polygon~",$l["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$L,array$_g){$d=array_keys(reset($L));$yg="INSERT INTO ".table($R)." (".implode(", ",$d).") VALUES\n";$Xi=array();foreach($d
as$w)$Xi[$w]="$w = VALUES($w)";$Nh="\nON DUPLICATE KEY UPDATE ".implode(", ",$Xi);$Xi=array();$x=0;foreach($L
as$O){$Y="(".implode(", ",$O).")";if($Xi&&(strlen($yg)+$x+strlen($Y)+strlen($Nh)>1e6)){if(!queries($yg.implode(",\n",$Xi).$Nh))return
false;$Xi=array();$x=0;}$Xi[]=$Y;$x+=strlen($Y)+2;}return
queries($yg.implode(",\n",$Xi).$Nh);}function
slowQuery($H,$ji){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$ji FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($ji*1000).") */ $A[2]";}}function
convertSearch($t,array$X,array$l){return(preg_match('~char|text|enum|set~',$l["type"])&&!preg_match("~^utf8~",$l["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($t USING ".charset($this->conn).")":$t);}function
quoteBinary($eh){return"X".q(bin2hex($eh));}function
warnings(){$I=$this->conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();print_select_result($I);return
ob_get_clean();}}function
tableHelp($C,$ee=false){$Fe=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower("information-schema-".($Fe?"$C-table/":str_replace("_","-",$C)."-table.html"));if(DB=="mysql")return($Fe?"mysql$C-table/":"system-schema.html");}function
partitionsInfo($R){$cd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$I=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $cd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=array();list($J["partition_by"],$J["partition"],$J["partitions"])=$I->fetch_row();$jg=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $cd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$J["partition_names"]=array_keys($jg);$J["partition_values"]=array_values($jg);return$J;}function
hasCStyleEscapes(){static$La;if($La===null){$Fh=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$La=(strpos($Fh,'NO_BACKSLASH_ESCAPES')===false);}return$La;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
indexAlgorithms(array$Th){return(preg_match('~^(MEMORY|NDB)$~',$Th["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($t){return"`".str_replace("`","``",$t)."`";}function
table($t){return
idf_escape($t);}function
get_databases($Uc){$J=get_session("dbs");if($J===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$J=($Uc?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$y,$xf=0,$qh=" "){return" $H$Z".($y?$qh."LIMIT $y".($xf?" OFFSET $xf":""):"");}function
limit1($R,$H,$Z,$qh="\n"){return
limit($H,$Z,1,0,$qh);}function
db_collation($i,array$cb){$J=null;$g=get_val("SHOW CREATE DATABASE ".idf_escape($i),1);if(preg_match('~ COLLATE ([^ ]+)~',$g,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$g,$A))$J=$cb[$A[1]][-1];return$J;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$h){$J=array();foreach($h
as$i)$J[$i]=count(get_vals("SHOW TABLES IN ".idf_escape($i)));return$J;}function
table_status($C="",$Hc=false){$J=array();foreach(get_rows($Hc?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($C!=""?"AND TABLE_NAME = ".q($C):"ORDER BY Name"):"SHOW TABLE STATUS".($C!=""?" LIKE ".q(addcslashes($C,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($C!="")$K["Name"]=$C;$J[$K["Name"]]=$K;}return$J;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
fields($R){$Fe=(connection()->flavor=='maria');$J=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$K){$l=$K["COLUMN_NAME"];$U=$K["COLUMN_TYPE"];$gd=$K["GENERATION_EXPRESSION"];$Ec=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Ec,$fd);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$He);$j=$K["COLUMN_DEFAULT"];if($j!=""){$de=preg_match('~text|json~',$He[1]);if(!$Fe&&$de)$j=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($j));if($Fe||$de){$j=($j=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$j));}if(!$Fe&&preg_match('~binary~',$He[1])&&preg_match('~^0x(\w*)$~',$j,$A))$j=pack("H*",$A[1]);}$J[$l]=array("field"=>$l,"full_type"=>$U,"type"=>$He[1],"length"=>$He[2],"unsigned"=>ltrim($He[3].$He[4]),"default"=>($fd?($Fe?$gd:stripslashes($gd)):$j),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Ec=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Ec,$A)?$A[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$K[PRIVILEGES],where,order")),"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($fd[1]=="PERSISTENT"?"STORED":$fd[1]),);}return$J;}function
indexes($R,$f=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$f)as$K){$C=$K["Key_name"];$J[$C]["type"]=($C=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$K["Index_type"])?$K["Index_type"]:"INDEX"):"UNIQUE")));$J[$C]["columns"][]=$K["Column_name"];$J[$C]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$C]["descs"][]=null;$J[$C]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($R){static$ng='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=array();$tb=get_val("SHOW CREATE TABLE ".table($R),1);if($tb){preg_match_all("~CONSTRAINT ($ng) FOREIGN KEY ?\\(((?:$ng,? ?)+)\\) REFERENCES ($ng)(?:\\.($ng))? \\(((?:$ng,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$tb,$Ie,PREG_SET_ORDER);foreach($Ie
as$A){preg_match_all("~$ng~",$A[2],$Ah);preg_match_all("~$ng~",$A[5],$ci);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$Ah[0]),"target"=>array_map('Adminer\idf_unescape',$ci[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$J;}function
view($C){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($C),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$w=>$X)sort($J[$w]);return$J;}function
information_schema($i){return($i=="information_schema")||(min_version(5.5)&&$i=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($i,$bb){return
queries("CREATE DATABASE ".idf_escape($i).($bb?" COLLATE ".q($bb):""));}function
drop_databases(array$h){$J=apply_queries("DROP DATABASE",$h,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($C,$bb){$J=false;if(create_database($C,$bb)){$T=array();$cj=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$cj[]=$R;else$T[]=$R;}$J=(!$T&&!$cj)||move_tables($T,$cj,$C);drop_databases($J?array(DB):array());}return$J;}function
auto_increment(){$ya=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$u){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$u["columns"],true)){$ya="";break;}if($u["type"]=="PRIMARY")$ya=" UNIQUE";}}return" AUTO_INCREMENT$ya";}function
alter_table($R,$C,array$m,array$Wc,$gb,$mc,$bb,$xa,$ig){$pa=array();foreach($m
as$l){if($l[1]){$j=$l[1][3];if(preg_match('~ GENERATED~',$j)){$l[1][3]=(connection()->flavor=='maria'?"":$l[1][2]);$l[1][2]=$j;}$pa[]=($R!=""?($l[0]!=""?"CHANGE ".idf_escape($l[0]):"ADD"):" ")." ".implode($l[1]).($R!=""?$l[2]:"");}else$pa[]="DROP ".idf_escape($l[0]);}$pa=array_merge($pa,$Wc);$P=($gb!==null?" COMMENT=".q($gb):"").($mc?" ENGINE=".q($mc):"").($bb?" COLLATE ".q($bb):"").($xa!=""?" AUTO_INCREMENT=$xa":"");if($ig){$jg=array();if($ig["partition_by"]=='RANGE'||$ig["partition_by"]=='LIST'){foreach($ig["partition_names"]as$w=>$X){$Y=$ig["partition_values"][$w];$jg[]="\n  PARTITION ".idf_escape($X)." VALUES ".($ig["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $ig[partition_by]($ig[partition])";if($jg)$P
.=" (".implode(",",$jg)."\n)";elseif($ig["partitions"])$P
.=" PARTITIONS ".(+$ig["partitions"]);}elseif($ig===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",$pa)."\n)$P");if($R!=$C)$pa[]="RENAME TO ".table($C);if($P)$pa[]=ltrim($P);return($pa?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$pa)):true);}function
alter_indexes($R,$pa){$Pa=array();foreach($pa
as$X)$Pa[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Pa));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$cj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$cj)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$cj,$ci){$Ug=array();foreach($T
as$R)$Ug[]=table($R)." TO ".idf_escape($ci).".".table($R);if(!$Ug||queries("RENAME TABLE ".implode(", ",$Ug))){$Lb=array();foreach($cj
as$R)$Lb[table($R)]=view($R);connection()->select_db($ci);$i=idf_escape(DB);foreach($Lb
as$C=>$bj){if(!queries("CREATE VIEW $C AS ".str_replace(" $i."," ",$bj["select"]))||!queries("DROP VIEW $i.$C"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$cj,$ci){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$C=($ci==DB?table("copy_$R"):idf_escape($ci).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $C"))||!queries("CREATE TABLE $C LIKE ".table($R))||!queries("INSERT INTO $C SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$yi=$K["Trigger"];if(!queries("CREATE TRIGGER ".($ci==DB?idf_escape("copy_$yi"):idf_escape($ci).".".idf_escape($yi))." $K[Timing] $K[Event] ON $C FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($cj
as$R){$C=($ci==DB?table("copy_$R"):idf_escape($ci).".".table($R));$bj=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $C"))||!queries("CREATE VIEW $C AS $bj[select]"))return
false;}return
true;}function
trigger($C,$R){if($C=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($C));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($C,$U){$m=get_rows("SELECT
	PARAMETER_NAME field,
	DATA_TYPE type,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^(]+\\\\(?|\\\\)$', '') length,
	REGEXP_REPLACE(DTD_IDENTIFIER, '^[^ ]+ ', '') `unsigned`,
	1 `null`,
	DTD_IDENTIFIER full_type,
	".($U=="FUNCTION"?"''":"PARAMETER_MODE")." `inout`,
	CHARACTER_SET_NAME collation
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($C)."
ORDER BY ORDINAL_POSITION");$J=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($C))->fetch_assoc();if($m&&$m[0]['field']=='')$J['returns']=array_shift($m);$J['fields']=$m;return$J;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($C,array$K){return
idf_escape($C);}function
last_id($I){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$e,$H){return$e->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$H);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$xa,$Lh){$J=get_val("SHOW CREATE TABLE ".table($R),1);if(!$xa)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Cb,$Lh=""){$C=idf_escape($Cb);$J="";if(preg_match('~CREATE~',$Lh)&&($g=get_val("SHOW CREATE DATABASE $C",1))){set_utf8mb4($g);if($Lh=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $C;\n";$J
.="$g;\n";}return$J."USE $C";}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$l){if(preg_match("~binary~",$l["type"]))return"HEX(".idf_escape($l["field"]).")";if($l["type"]=="bit")return"BIN(".idf_escape($l["field"])." + 0)";if($l["type"]=="vector")return(connection()->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($l["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$l["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($l["field"]).")";}function
unconvert_field(array$l,$J){if(preg_match("~binary~",$l["type"]))$J="UNHEX($J)";if($l["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if($l["type"]=="vector")$J=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($J)";if(preg_match("~geometry|point|linestring|polygon~",$l["type"])){$yg=(min_version(8)?"ST_":"");$J=$yg."GeomFromText($J, $yg"."SRID($l[field]))";}return$J;}function
support($Ic){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').')$~',$Ic);}function
kill_process($s){return
queries("KILL ".number($s));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types(){return
array();}function
type_values($s){return"";}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($gh,$f=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));function
page_header($li,$k="",$Ja=array(),$mi=""){page_headers();if(is_ajax()&&$k){page_messages($k);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$ni=$li.($mi!=""?": $mi":"");$oi=strip_tags($ni.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$oi,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=5.5.1"),'">
';$xb=adminer()->css();if(is_int(key($xb)))$xb=array_fill_keys($xb,'light');$rd=in_array('light',$xb)||in_array('',$xb);$pd=in_array('dark',$xb)||in_array('',$xb);$_b=($rd?($pd?null:false):($pd?:null));$Se=" media='(prefers-color-scheme: dark)'";if($_b!==false)echo"<link rel='stylesheet'".($_b?"":$Se)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=5.5.1")."'>\n";echo"<meta name='color-scheme' content='".($_b===null?"light dark":($_b?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=5.5.1");if(adminer()->head($_b))echo"<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.5.1")."'>\n";foreach($xb
as$Oi=>$ff){$va=($ff=='dark'&&!$_b?$Se:($ff=='light'&&$pd?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$va href='".h($Oi)."'>\n";}echo"\n<body class='".'ltr'." nojs";adminer()->bodyClass();echo"'>\n",script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick".(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '".VERSION."')")."});
document.body.classList.replace('nojs', 'js');
if (!window.isSecureContext) {
	document.body.classList.add('insecure');
}
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ja!==null){$z=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($z?:".").'">'.get_driver(DRIVER).'</a> » ';$z=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ja===false)echo"$N\n";else{echo"<a href='".h($z)."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ja)))echo'<a href="'.h($z."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> » ';if(is_array($Ja)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Ja
as$w=>$X){$Nb=(is_array($X)?$X[1]:h($X));if($Nb!="")echo"<a href='".h(ME."$w=").urlencode(is_array($X)?$X[0]:$X)."'>$Nb</a> » ";}}echo"$li\n";}}echo"<h2>$ni</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($k);$h=&get_session("dbs");if(DB!=""&&$h&&!in_array(DB,$h,true))$h=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$wb){$sd=array();foreach($wb
as$w=>$X)$sd[]="$w $X";header("Content-Security-Policy: ".implode("; ",$sd));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"'none'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$sf;if(!$sf)$sf=base64_encode(rand_string());return$sf;}function
page_messages($k){$Ni=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Ye=idx($_SESSION["messages"],$Ni);if($Ye){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Ye)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$Ni]);}if($k)echo"<div class='error'>$k</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($ef=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($ef);echo"</div>\n";if($ef!="auth")echo'<form action="" method="post">
<p class="logout">
<span>',h($_GET["username"])."\n",'</span>
<input type="submit" name="logout" value="Logout" id="logout">
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($jf){while($jf>=2147483648)$jf-=4294967296;while($jf<=-2147483649)$jf+=4294967296;return(int)$jf;}function
long2str(array$W,$ej){$eh='';foreach($W
as$X)$eh
.=pack('V',$X);if($ej)return
substr($eh,0,end($W));return$eh;}function
str2long($eh,$ej){$W=array_values(unpack('V*',str_pad($eh,4*ceil(strlen($eh)/4),"\0")));if($ej)$W[]=strlen($eh);return$W;}function
xxtea_mx($kj,$jj,$Oh,$ie){return
int32((($kj>>5&0x7FFFFFF)^$jj<<2)+(($jj>>3&0x1FFFFFFF)^$kj<<4))^int32(($Oh^$jj)+($ie^$kj));}function
encrypt_string($Jh,$w){if($Jh=="")return"";$w=array_values(unpack("V*",pack("H*",md5($w))));$W=str2long($Jh,true);$jf=count($W)-1;$kj=$W[$jf];$jj=$W[0];$Gg=floor(6+52/($jf+1));$Oh=0;while($Gg-->0){$Oh=int32($Oh+0x9E3779B9);$ec=$Oh>>2&3;for($Yf=0;$Yf<$jf;$Yf++){$jj=$W[$Yf+1];$if=xxtea_mx($kj,$jj,$Oh,$w[$Yf&3^$ec]);$kj=int32($W[$Yf]+$if);$W[$Yf]=$kj;}$jj=$W[0];$if=xxtea_mx($kj,$jj,$Oh,$w[$Yf&3^$ec]);$kj=int32($W[$jf]+$if);$W[$jf]=$kj;}return
long2str($W,false);}function
decrypt_string($Jh,$w){if($Jh=="")return"";if(!$w)return
false;$w=array_values(unpack("V*",pack("H*",md5($w))));$W=str2long($Jh,false);$jf=count($W)-1;$kj=$W[$jf];$jj=$W[0];$Gg=floor(6+52/($jf+1));$Oh=int32($Gg*0x9E3779B9);while($Oh){$ec=$Oh>>2&3;for($Yf=$jf;$Yf>0;$Yf--){$kj=$W[$Yf-1];$if=xxtea_mx($kj,$jj,$Oh,$w[$Yf&3^$ec]);$jj=int32($W[$Yf]-$if);$W[$Yf]=$jj;}$kj=$W[$jf];$if=xxtea_mx($kj,$jj,$Oh,$w[$Yf&3^$ec]);$jj=int32($W[0]-$if);$W[0]=$jj;$Oh=int32($Oh-0x9E3779B9);}return
long2str($W,true);}$pg=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($w)=explode(":",$X);$pg[$w]=$X;}}function
add_invalid_login(){$Da=get_temp_dir()."/adminer-invalid";foreach(glob("$Da*")?:array($Da)as$Oc){$o=file_open_lock($Oc);if($o)break;}if(!$o)$o=file_open_lock("$Da-".rand_string());if(!$o)return;$Yd=json_decode(stream_get_contents($o),true);$ii=time();if($Yd){foreach($Yd
as$Zd=>$X){if($X[0]<$ii)unset($Yd[$Zd]);}}$Xd=&$Yd[adminer()->bruteForceKey()];if(!$Xd)$Xd=array($ii+30*60,0);$Xd[1]++;file_write_unlock($o,json_encode($Yd));}function
check_invalid_login(array&$pg){$Yd=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$Oc){$o=file_open_lock($Oc);if($o){$Yd=json_decode(stream_get_contents($o),true);file_unlock($o);break;}}$Xd=idx($Yd,adminer()->bruteForceKey(),array());$rf=($Xd[1]>29?$Xd[0]-time():0);if($rf>0)auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($rf/60)),$pg);}$wa=$_POST["auth"];if($wa){session_regenerate_id();$Zi=$wa["driver"];$N=$wa["server"];$V=$wa["username"];$F=(string)$wa["password"];$i=$wa["db"];set_password($Zi,$N,$V,$F);$_SESSION["db"][$Zi][$N][$V][$i]=true;if($wa["permanent"]){$w=implode("-",array_map('base64_encode',array($Zi,$N,$V,$i)));$Bg=adminer()->permanentLogin(true);$pg[$w]="$w:".base64_encode($Bg?encrypt_string($F,$Bg):"");cookie("adminer_permanent",implode(" ",$pg));}if(count($_POST)==1||DRIVER!=$Zi||SERVER!=$N||$_GET["username"]!==$V||DB!=$i)redirect(auth_url($Zi,$N,$V,$i));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$w)set_session($w,null);unset_permanent($pg);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($pg&&!$_SESSION["pwds"]){session_regenerate_id();$Bg=adminer()->permanentLogin();foreach($pg
as$w=>$X){list(,$Wa)=explode(":",$X);list($Zi,$N,$V,$i)=array_map('base64_decode',explode("-",$w));set_password($Zi,$N,$V,decrypt_string(base64_decode($Wa),$Bg));$_SESSION["db"][$Zi][$N][$V][$i]=true;}}function
unset_permanent(array&$pg){foreach($pg
as$w=>$X){list($Zi,$N,$V,$i)=array_map('base64_decode',explode("-",$w));if($Zi==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$i==DB)unset($pg[$w]);}cookie("adminer_permanent",implode(" ",$pg));}function
auth_error($k,array&$pg){$th=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$th]||$_GET[$th])&&!$_SESSION["token"])$k='Session expired, please login again.';else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$k
.=($k?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($pg);}}if(!$_COOKIE[$th]&&$_GET[$th]&&ini_bool("session.use_only_cookies"))$k='Session support must be enabled.';$bg=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$bg["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$k,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($pg);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$e='';if(isset($_GET["username"])&&is_string(get_password())){list($yd,$tg)=host_port(SERVER);if(preg_match('~[^-\w.:/]~',$yd.$tg))auth_error('Invalid server.',$pg);if(preg_match('~^-?\d+~',$tg,$A)&&($A[0]<1024||$A[0]>65535))auth_error('Connecting to privileged ports is not allowed.',$pg);check_invalid_login($pg);$vb=adminer()->credentials();$e=Driver::connect($vb[0],$vb[1],$vb[2]);if(is_object($e)){Db::$instance=$e;Driver::$instance=new
Driver($e);if($e->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$De=null;if(!is_object($e)||($De=adminer()->login($_GET["username"],get_password()))!==true){$k=(is_string($e)?nl_br(h($e)):(is_string($De)?$De:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($k,$pg);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($wa&&$_POST["token"])$_POST["token"]=get_token();$k='';if($_POST){if(!verify_token()){$Qd="max_input_vars";$Qe=ini_get($Qd);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$w){$X=ini_get($w);if($X&&(!$Qe||$X<$Qe)){$Qd=$w;$Qe=$X;}}}$k=(!$_POST["token"]&&$Qe?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$Qd'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$k=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$k
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($I,$f=null,array$Pf=array(),$y=0){$_e=array();$v=array();$d=array();$Ha=array();$Ci=array();$J=array();for($r=0;(!$y||$r<$y)&&($K=$I->fetch_row());$r++){if(!$r){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($fe=0;$fe<count($K);$fe++){$l=$I->fetch_field();$C=$l->name;$Of=(isset($l->orgtable)?$l->orgtable:"");$Nf=(isset($l->orgname)?$l->orgname:$C);if($Pf&&JUSH=="sql")$_e[$fe]=($C=="table"?"table=":($C=="possible_keys"?"indexes=":null));elseif($Of!=""){if(isset($l->table))$J[$l->table]=$Of;if(!isset($v[$Of])){$v[$Of]=array();foreach(indexes($Of,$f)as$u){if($u["type"]=="PRIMARY"){$v[$Of]=array_flip($u["columns"]);break;}}$d[$Of]=$v[$Of];}if(isset($d[$Of][$Nf])){unset($d[$Of][$Nf]);$v[$Of][$Nf]=$fe;$_e[$fe]=$Of;}}if($l->charsetnr==63)$Ha[$fe]=true;$Ci[$fe]=$l->type;echo"<th".($Of!=""||$l->name!=$Nf?" title='".h(($Of!=""?"$Of.":"").$Nf)."'":"").">".h($C).($Pf?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($C),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$w=>$X){$z="";if(isset($_e[$w])&&!$d[$_e[$w]]){if($Pf&&JUSH=="sql"){$R=$K[array_search("table=",$_e)];$z=ME.$_e[$w].urlencode($Pf[$R]!=""?$Pf[$R]:$R);}else{$z=ME."edit=".urlencode($_e[$w]);foreach($v[$_e[$w]]as$Za=>$fe){if($K[$fe]===null){$z="";break;}$z
.="&where".urlencode("[".bracket_escape($Za)."]")."=".urlencode($K[$fe]);}}}$l=array('type'=>($Ha[$w]?'blob':($Ci[$w]==254?'char':'')),);$X=select_value($X,$z,$l,null);echo"<td".($Ci[$w]<=9||$Ci[$w]==246?" class='number'":"").">$X";}}echo($r?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$J;}function
referencable_primary($oh){$J=array();foreach(table_status('',true)as$Uh=>$R){if($Uh!=$oh&&fk_support($R)){foreach(fields($Uh)as$l){if($l["primary"]){if($J[$Uh]){unset($J[$Uh]);break;}$J[$Uh]=$l;}}}}return$J;}function
textarea($C,$Y,$L=10,$db=80){echo"<textarea name='".h($C)."' rows='$L' cols='$db' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($va,array$Kf,$Y="",$Ef="",$qg=""){$bi=($Kf?"select":"input");return"<$bi$va".($Kf?"><option value=''>$qg".optionlist($Kf,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$qg'>").($Ef?script("qsl('$bi').onchange = $Ef;",""):"");}function
json_row($w,$X=null,$uc=true){static$Rc=true;if($Rc)echo"{";if($w!=""){echo($Rc?"":",")."\n\t\"".addcslashes($w,"\r\n\t\"\\/").'": '.($X!==null?($uc?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$Rc=false;}else{echo"\n}\n";$Rc=true;}}function
edit_type($w,array$l,array$cb,array$Yc=array(),array$Fc=array()){$U=$l["type"];echo"<td><select name='".h($w)."[type]' class='type' aria-labelledby='label-type'>";if($U&&!array_key_exists($U,driver()->types())&&!isset($Yc[$U])&&!in_array($U,$Fc))$Fc[]=$U;$Kh=driver()->structuredTypes();if($Yc)$Kh['Foreign keys']=$Yc;echo
optionlist(array_merge($Fc,$Kh),$U),"</select><td>","<input name='".h($w)."[length]' value='".h($l["length"])."' size='3'".(!$l["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($cb?"<input list='collations' name='".h($w)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'")." value='".h($l["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($w)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(driver()->unsigned,$l["unsigned"]).'</select>':''),(isset($l['on_update'])?"<select name='".h($w)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"CURRENT_TIMESTAMP":$l["on_update"])).'</select>':''),($Yc?"<select name='".h($w)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$l["on_delete"])."</select> ":" ");}function
process_length($x){$pc=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$pc(?:\\s*,\\s*$pc)*+\\s*\\)?\\s*\$~",$x)&&preg_match_all("~$pc~",$x,$Ie)?"(".implode(",",$Ie[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$x)));}function
process_type(array$l,$ab="COLLATE"){return" $l[type]".process_length($l["length"]).(preg_match(number_type(),$l["type"])&&in_array($l["unsigned"],driver()->unsigned)?" $l[unsigned]":"").(preg_match('~char|text|enum|set~',$l["type"])&&$l["collation"]?" $ab ".(JUSH=="mssql"?$l["collation"]:q($l["collation"])):"");}function
process_field(array$l,array$Bi){if($l["on_update"])$l["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$l["on_update"]);return
array(idf_escape(trim($l["field"])),process_type($Bi),($l["null"]?" NULL":" NOT NULL"),default_value($l),(preg_match('~timestamp|datetime~',$l["type"])&&$l["on_update"]?" ON UPDATE $l[on_update]":""),(support("comment")&&$l["comment"]!=""?" COMMENT ".q($l["comment"]):""),($l["auto_increment"]?auto_increment():null),);}function
default_value(array$l){if($l["default"]===null)return"";$j=str_replace("\r","",$l["default"]);$fd=$l["generated"];return(in_array($fd,driver()->generated)?(JUSH=="mssql"?" AS ($j)".($fd=="VIRTUAL"?"":" $fd"):" GENERATED ALWAYS AS ($j) $fd"):(preg_match('~^GENERATED ~i',$j)?" $j":" DEFAULT ".(preg_match('~char|binary|text|json|enum|set~',$l["type"])||preg_match('~^(?![a-z])~i',$j)?(JUSH=="sql"&&preg_match('~text|json~',$l["type"])?"(".q($j).")":q($j)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($j)":$j)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$w=>$X){if(preg_match("~$w|$X~",$U))return" class='$w'";}}function
edit_fields(array$m,array$cb,$U="TABLE",array$Yc=array()){$m=array_values($m);$Ib=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$hb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",)),"<td id='label-default'$Ib>".'Default value',(support("comment")?"<td id='label-comment'$hb>".'Comment':"");echo"<td>".icon("plus","add[".(support("move_col")?0:count($m))."]","+",'Add next'),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");foreach($m
as$r=>$l){$r++;$Qf=$l[($_POST?"orig":"field")];$Qb=(isset($_POST["add"][$r-1])||(isset($l["field"])&&!idx($_POST["drop_col"],$r)))&&(support("drop_col")||$Qf=="");echo"<tr".($Qb?"":" style='display: none;'").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$r][inout]",explode("|",driver()->inout),$l["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",'Move')." ":"");if($Qb)echo"<input name='fields[$r][field]' value='".h($l["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$r-1])?" autofocus":"").">";echo
input_hidden("fields[$r][orig]",$Qf);edit_type("fields[$r]",$l,$cb,$Yc);if($U=="TABLE"){echo"<td>".checkbox("fields[$r][null]",1,$l["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$r'".($l["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Ib>".(driver()->generated?html_select("fields[$r][generated]",array_merge(array("","DEFAULT"),driver()->generated),$l["generated"])." ":checkbox("fields[$r][generated]",1,$l["generated"],"","","","label-default"));$va=" name='fields[$r][default]' aria-labelledby='label-default'";$Y=h($l["default"]);echo(preg_match('~\n~',$l["default"])?"<textarea$va rows='2' cols='30' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$va value='$Y'>"),(support("comment")?"<td$hb><input name='fields[$r][comment]' value='".h($l["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");}echo"<td>",(support("move_col")?icon("plus","add[$r]","+",'Add next')." ":""),($Qf==""||support("drop_col")?icon("cross","drop_col[$r]","x",'Remove'):"");}}function
process_fields(array&$m){if($_POST["add"]){$m=array_values($m);array_splice($m,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
normalize_enum(array$A){$X=$A[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($hd,array$Dg,$d,$Cf){if(!$Dg)return
true;if($Dg==array("ALL PRIVILEGES","GRANT OPTION"))return($hd=="GRANT"?queries("$hd ALL PRIVILEGES$Cf WITH GRANT OPTION"):queries("$hd ALL PRIVILEGES$Cf")&&queries("$hd GRANT OPTION$Cf"));return
queries("$hd ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$d, ",$Dg).$d).$Cf);}function
drop_create($ac,$g,$bc,$fi,$cc,$_,$Xe,$Ve,$We,$Af,$of){if($_POST["drop"])query_redirect($ac,$_,$Xe);elseif($Af=="")query_redirect($g,$_,$We);elseif($Af!=$of){$ub=queries($g);queries_redirect($_,$Ve,$ub&&queries($ac));if($ub)queries($bc);}else
queries_redirect($_,$Ve,queries($fi)&&queries($cc)&&queries($ac)&&queries($g));}function
create_trigger($Cf,array$K){$ki=" $K[Timing] $K[Event]".(preg_match('~ OF~',$K["Event"])?" $K[Of]":"");return"CREATE TRIGGER ".idf_escape($K["Trigger"]).(JUSH=="mssql"?$Cf.$ki:$ki.$Cf).rtrim(" $K[Type]\n$K[Statement]",";").";";}function
create_routine($bh,array$K){$O=array();$m=(array)$K["fields"];ksort($m);foreach($m
as$l){if($l["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$l["inout"])?"$l[inout] ":"").idf_escape($l["field"]).process_type($l,"CHARACTER SET");}$Kb=rtrim($K["definition"],";");return"CREATE $bh ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($bh=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(JUSH=="pgsql"?" AS ".q($Kb):"\n$Kb;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key(array$n){$i=$n["db"];$tf=$n["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$n["source"])).") REFERENCES ".($i!=""&&$i!=$_GET["db"]?idf_escape($i).".":"").($tf!=""&&$tf!=$_GET["ns"]?idf_escape($tf).".":"").idf_escape($n["table"])." (".implode(", ",array_map('Adminer\idf_escape',$n["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$n["on_delete"])?" ON DELETE $n[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$n["on_update"])?" ON UPDATE $n[on_update]":"").($n["deferrable"]?" $n[deferrable]":"");}function
tar_file($Oc,$pi){$J=pack("a100a8a8a8a12a12",$Oc,644,0,0,decoct($pi->size),decoct(time()));$Va=8*32;for($r=0;$r<strlen($J);$r++)$Va+=ord($J[$r]);$J
.=sprintf("%06o",$Va)."\0 ";echo$J,str_repeat("\0",512-strlen($J));$pi->send();echo
str_repeat("\0",511-($pi->size+511)%512);}function
doc_link(array$mg,$gi="<sup>?</sup>"){$rh=connection()->server_info;$aj=preg_replace('~^(\d\.?\d).*~s','\1',$rh);$Pi=array('sql'=>"https://dev.mysql.com/doc/refman/$aj/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$aj)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$rh)."&id=",);if(connection()->flavor=='maria'){$Pi['sql']="https://mariadb.com/kb/en/";$mg['sql']=(isset($mg['mariadb'])?$mg['mariadb']:str_replace(".html","/",$mg['sql']));}return($mg[JUSH]?"<a href='".h($Pi[JUSH].$mg[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$aj":""))."'".target_blank().">$gi</a>":"");}function
db_size($i){if(!connection()->select_db($i))return"?";$J=0;foreach(table_status()as$S)$J+=$S["Data_length"]+$S["Index_length"];return
format_number($J);}function
set_utf8mb4($g){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$g)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$k)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$k,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$w=>$X){if(support($w))echo"<a href='".h(ME)."$w='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$h=adminer()->databases();if($h){$hh=support("scheme");$cb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<thead><tr>".(support("database")?"<td>":"")."<th>".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'>".'Compute'."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n";$h=($_GET["dbsize"]?count_tables($h):array_flip($h));foreach($h
as$i=>$T){$ah=h(ME)."db=".urlencode($i);$s=h("Db-".$i);echo"<tr>".(support("database")?"<td>".checkbox("db[]",$i,in_array($i,(array)$_POST["db"]),"","","",$s):""),"<th><a href='$ah' id='$s'>".h($i)."</a>";$bb=h(db_collation($i,$cb));echo"<td>".(support("database")?"<a href='$ah".($hh?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$bb</a>":$bb),"<td align='right'><a href='$ah&amp;schema=' id='tables-".h($i)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($i)."'>".($_GET["dbsize"]?db_size($i):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n".input_hidden("all").script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".'Drop'."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}if(!empty(adminer()->plugins)){echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach(adminer()->plugins
as$rg){$Ob=(method_exists($rg,'description')?$rg->description():"");if(!$Ob){$Rg=new
\ReflectionObject($rg);if(preg_match('~^/[\s*]+(.+)~',$Rg->getDocComment(),$A))$Ob=$A[1];}$ih=(method_exists($rg,'screenshot')?$rg->screenshot():"");echo"<li><b>".get_class($rg)."</b>".h($Ob?": $Ob":"").($ih?" (<a href='".h($ih)."'".target_blank().">".'screenshot'."</a>)":"")."\n";}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}adminer()->afterConnect();class
TmpFile{private$handler;var$size;function
__construct(){$this->handler=tmpfile();}function
write($ob){$this->size+=strlen($ob);fwrite($this->handler,$ob);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$m=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=driver()->select($a,$M,array(where($_GET,$m)),$M);$K=($I?$I->fetch_row():array());echo
driver()->value($K[0],$m[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$m=fields($a);if(!$m)$k=error()?:'No tables.';$S=table_status1($a);$C=adminer()->tableName($S);page_header(($m&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($C!=""?$C:h($a)),$k);$Zg=array();foreach($m
as$w=>$l)$Zg+=$l["privileges"];adminer()->selectLinks($S,(isset($Zg["insert"])||!support("table")?"":null));$gb=$S["Comment"];if($gb!="")echo"<p class='nowrap'>".'Comment'.": ".h($gb)."\n";if($m)adminer()->tableStructurePrint($m,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$K){$z=preg_replace('~ns=[^&]*~',"ns=".urlencode($K["ns"]),ME);echo"<li><a href='".h($z."table=".urlencode($K["table"]))."'>".($K["ns"]!=$_GET["ns"]?"<b>".h($K["ns"])."</b>.":"").h($K["table"])."</a>";}echo"</ul>\n";}$Pd=driver()->inheritsFrom($a);if($Pd){echo"<h3>".'Inherits from'."</h3>\n";tables_links($Pd);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<h3 id='indexes'>".'Indexes'."</h3>\n";$v=indexes($a);if($v)adminer()->tableIndexesPrint($v,$S);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.'Alter indexes'."</a>\n";}if(!is_view($S)){if(fk_support($S)){echo"<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$Yc=foreign_keys($a);if($Yc){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td></thead>\n";foreach($Yc
as$C=>$n){echo"<tr title='".h($C)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$n["source"]))."</i>";$z=($n["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($n["db"]),ME):($n["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($n["ns"]),ME):ME));echo"<td><a href='".h($z."table=".urlencode($n["table"]))."'>".($n["db"]!=""&&$n["db"]!=DB?"<b>".h($n["db"])."</b>.":"").($n["ns"]!=""&&$n["ns"]!=$_GET["ns"]?"<b>".h($n["ns"])."</b>.":"").h($n["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$n["target"]))."</i>)","<td>".h($n["on_delete"]),"<td>".h($n["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($C)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.'Add foreign key'."</a>\n";}if(support("check")){echo"<h3 id='checks'>".'Checks'."</h3>\n";$Sa=driver()->checkConstraints($a);if($Sa){echo"<table>\n";foreach($Sa
as$w=>$X)echo"<tr title='".h($w)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($w))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.'Create check'."</a>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<h3 id='triggers'>".'Triggers'."</h3>\n";$_i=triggers($a);if($_i){echo"<table>\n";foreach($_i
as$w=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($w)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($w))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.'Add trigger'."</a>\n";}$Od=driver()->inheritedTables($a);if($Od){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$eg=driver()->partitionsInfo($a);if($eg)echo"<p><code class='jush-".JUSH."'>BY ".h("$eg[partition_by]($eg[partition])")."</code>\n";tables_links($Od);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$Vh=array();$Wh=array();$Kc=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Ie,PREG_SET_ORDER);foreach($Ie
as$r=>$A){$Vh[$A[1]]=array((float)$A[2],(float)$A[3]);$Wh[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$si=0;$Ea=-1;$gh=array();$Qg=array();$te=array();$na=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$G=0;$gh[$R]["fields"]=array();foreach($na[$R]as$l){$G+=1.25;$Kc[$R][$l["field"]]=$G;$gh[$R]["fields"][$l["field"]]=$l;}$gh[$R]["pos"]=($Vh[$R]?:array($si,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$re=$Ea;if(idx($Vh[$R],1)||idx($Vh[$X["table"]],1))$re=min(idx($Vh[$R],1,0),idx($Vh[$X["table"]],1,0))-1;else$Ea-=.1;while($te[(string)$re])$re-=.0001;$gh[$R]["references"][$X["table"]][(string)$re]=array($X["source"],$X["target"]);$Qg[$X["table"]][$R][(string)$re]=$X["target"];$te[(string)$re]=true;}}$si=max($si,$gh[$R]["pos"][0]+2.5+$G);}echo'<div id="schema" style="height: ',$si,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(",",$Wh)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$si,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';foreach($gh
as$C=>$R){echo"<div class='table' style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($C).'"><b>'.h($C)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($R["fields"]as$l){$X='<span'.type_class($l["type"]).' title="'.h($l["type"].($l["length"]?"($l[length])":"").($l["null"]?" NULL":'')).'">'.h($l["field"]).'</span>';echo"<br>".($l["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$di=>$Sg){foreach($Sg
as$re=>$Ng){$se=$re-idx($Vh[$C],1);$r=0;foreach($Ng[0]as$Ah)echo"\n<div class='references' title='".h($di)."' id='refs$re-".($r++)."' style='left: $se"."em; top: ".$Kc[$C][$Ah]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$se)."em;'></div></div>";}}foreach((array)$Qg[$C]as$di=>$Sg){foreach($Sg
as$re=>$d){$se=$re-idx($Vh[$C],1);$r=0;foreach($d
as$ci)echo"\n<div class='references arrow' title='".h($di)."' id='refd$re-".($r++)."' style='left: $se"."em; top: ".$Kc[$C][$ci]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$se)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($gh
as$C=>$R){foreach((array)$R["references"]as$di=>$Sg){if($gh[$di]){foreach($Sg
as$re=>$Ng){$df=$si;$Oe=-10;foreach($Ng[0]as$w=>$Ah){$ug=$R["pos"][0]+$Kc[$C][$Ah];$vg=$gh[$di]["pos"][0]+$Kc[$di][$Ng[1][$w]];$df=min($df,$ug,$vg);$Oe=max($Oe,$ug,$vg);}echo"<div class='references' id='refl$re' style='left: $re"."em; top: $df"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($Oe-$df)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".urlencode($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$k){$j=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$Qh){if(support($Qh))$j[$Qh."s"]='';}save_settings(array_intersect_key($_POST+$j,array_flip(array("output","format","db_style","table_style","data_style"))+$j),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Bc=dump_headers((count($T)==1?key($T):DB),(DB==""||$_GET["ns"]===""||count($T)>1));$ce=preg_match('~sql~',$_POST["format"]);if($ce){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$Lh=$_POST["db_style"];$h=array(DB);if(DB==""){$h=$_POST["databases"];if(is_string($h))$h=explode("\n",rtrim(str_replace("\r","",$h),"\n"));}foreach((array)$h
as$i){adminer()->dumpDatabase($i);if(connection()->select_db($i)){if($ce){if($Lh)echo
use_sql($i,$Lh).";\n\n";$Wf="";if($_POST["types"]){foreach(types()as$s=>$U){$qc=type_values($s);if($qc)$Wf
.=($Lh!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($qc);\n\n";else$Wf
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$C=$K["ROUTINE_NAME"];$bh=$K["ROUTINE_TYPE"];$g=create_routine($bh,array("name"=>$C)+routine($K["SPECIFIC_NAME"],$bh));set_utf8mb4($g);$Wf
.=($Lh!='DROP+CREATE'?"DROP $bh IF EXISTS ".idf_escape($C).";;\n":"")."$g;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$g=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($g);$Wf
.=($Lh!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$g;;\n\n";}}echo($Wf&&JUSH=='sql'?"DELIMITER ;;\n\n$Wf"."DELIMITER ;\n\n":$Wf);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$gh){if($gh!=""){set_schema($gh);if(DB==""&&(information_schema(DB)||$gh=="pg_catalog"))continue;}$cj=array();foreach(table_status('',true)as$C=>$S){$R=(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["tables"]));$Ab=(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["data"]));if($R||$Ab){$pi=null;if($Bc=="tar"){$pi=new
TmpFile;ob_start(array($pi,'write'),1e5);}adminer()->dumpTable($C,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$cj[]=$C;elseif($Ab){$m=fields($C);adminer()->dumpData($C,$_POST["data_style"],"SELECT *".convert_fields($m,$m)." FROM ".table($C));}if($ce&&$_POST["triggers"]&&$R&&($_i=trigger_sql($C)))echo"\nDELIMITER ;;\n$_i\nDELIMITER ;\n";if($Bc=="tar"){ob_end_flush();tar_file((DB!=""?"":"$i/")."$C.csv",$pi);}elseif($ce)echo"\n";}}if(function_exists('Adminer\foreign_keys_sql')){foreach(table_status('',true)as$C=>$S){$R=(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["tables"]));if($R&&!is_view($S))echo
foreign_keys_sql($C);}}foreach($cj
as$bj)adminer()->dumpTable($bj,$_POST["table_style"],1);if($Bc=="tar")echo
pack("x512");}}}}adminer()->dumpFooter();exit;}page_header('Export',$k,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$Eb=array('','USE','DROP+CREATE','CREATE');$Xh=array('','DROP+CREATE','CREATE');$Bb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Bb[]='INSERT+UPDATE';$K=get_settings("adminer_export");if(!$K)$K=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$K["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$K["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$Eb,$K["db_style"]).(support("type")?checkbox("types",1,$K["types"],'User types'):"").(support("routine")?checkbox("routines",1,$K["routines"],'Routines'):"").(support("event")?checkbox("events",1,$K["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$Xh,$K["table_style"]).checkbox("auto_increment",1,$K["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$K["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Bb,$K["data_style"]),'</table>
<p><input type="submit" value="Export">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");$zg=array();if($_GET["ns"]===""){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly'>".'Schema'."</label>","</thead>\n",script("qs('#check-schemas').onclick = partial(formCheck, /^schemas\\[/);");foreach(adminer()->schemas()as$gh)echo"<tr><td>".checkbox("schemas[]",$gh,true,$gh,"","block")."\n";}elseif(DB!=""){$Ta=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Ta class='jsonly'>".'Table'."</label>".script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$Ta class='jsonly'></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$cj="";$Zh=tables_list();foreach($Zh
as$C=>$U){$yg=preg_replace('~_.*~','',$C);$Ta=($a==""||$a==(substr($a,-1)=="%"?"$yg%":$C));$Ag="<tr><td>".checkbox("tables[]",$C,$Ta,$C,"","block");if($U!==null&&!preg_match('~table~i',$U))$cj
.="$Ag\n";else
echo"$Ag<td align='right'><label class='block'><span id='Rows-".h($C)."'></span>".checkbox("data[]",$C,$Ta)."</label>\n";$zg[$yg]++;}echo$cj;if($Zh)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$h=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($h?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly'>".script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").'Database'."</label>","</thead>\n";if($h){foreach($h
as$i){if(!information_schema($i)){$yg=preg_replace('~_.*~','',$i);echo"<tr><td>".checkbox("databases[]",$i,$a==""||$a=="$yg%",$i,"","block")."\n";$zg[$yg]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$Rc=true;foreach($zg
as$w=>$X){if($w!=""&&$X>1){echo($Rc?"<p>":" ")."<a href='".h(ME)."dump=".urlencode("$w%")."'>".h($w)."</a>";$Rc=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$I=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$hd=$I;if(!$I)$I=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($hd?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<th></thead>\n";while($K=$I->fetch_assoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.'Edit'."</a>\n";if(!$hd||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$k&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$wd=&get_session("queries");$vd=&$wd[DB];if(!$k&&$_POST["clear"]){$vd=array();redirect(remove_from_uri("history"));}stop_session();page_header((isset($_GET["import"])?'Import':'SQL command'),$k);$ze='--'.(JUSH=='sql'?' ':'');if(!$k&&$_POST){$o=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$Dh=adminer()->importServerPath();$o=@fopen((file_exists($Dh)?$Dh:"compress.zlib://$Dh.gz"),"rb");$H=($o?fread($o,1e6):false);}else$H=get_file("sql_file",true,";");if(is_string($H)){if(($Te=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($Te,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$Gg=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$vd||first(end($vd))!=$Gg){restart_session();$vd[]=array($Gg,time());set_session("queries",$wd);stop_session();}}$Bh="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$ze)[^\n]*\n?|--\r?\n)";$Mb=driver()->delimiter;$xf=0;$lc=true;$f=connect();if($f&&DB!=""){$f->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$f);}$fb=0;$sc=array();$cg='[\'"'.(JUSH=="sql"?'`#':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$ze.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$ti=microtime(true);$ia=get_settings("adminer_import");while($H!=""){if(!$xf&&preg_match("~^$Bh*+DELIMITER\\s+(\\S+)~i",$H,$A)){$Mb=preg_quote($A[1]);$H=substr($H,strlen($A[0]));}elseif(!$xf&&JUSH=='pgsql'&&preg_match("~^($Bh*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$A)){$Mb="\n\\\\\\.\r?\n";$xf=strlen($A[0]);}else{preg_match("($Mb\\s*|$cg)",$H,$A,PREG_OFFSET_CAPTURE,$xf);list($ad,$G)=$A[0];if(!$ad&&$o&&!feof($o))$H
.=fread($o,1e5);else{if(!$ad&&rtrim($H)=="")break;$xf=$G+strlen($ad);if($ad&&!preg_match("(^$Mb)",$ad)){$Ma=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($G>0&&strtolower($H[$G-1])=="e"));$ng=($ad=='/*'?'\*/':($ad=='['?']':(preg_match("~^$ze|^#~",$ad)?"\n":preg_quote($ad).($Ma?'|\\\\.':''))));while(preg_match("($ng|\$)s",$H,$A,PREG_OFFSET_CAPTURE,$xf)){$eh=$A[0][0];if(!$eh&&$o&&!feof($o))$H
.=fread($o,1e5);else{$xf=$A[0][1]+strlen($eh);if(!$eh||$eh[0]!="\\")break;}}}else{$lc=false;$Gg=substr($H,0,$G+($Mb[0]=="\n"?3:0));$fb++;$Ag="<pre id='sql-$fb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($Gg)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$Bh*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Gg,$A)!==0){echo$Ag,"<p class='error'>".sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$A[1])?'ATTACH':'VACUUM INTO')."\n";$sc[]=" <a href='#sql-$fb'>$fb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Ag;ob_flush();flush();}$Hh=microtime(true);if(connection()->multi_query($Gg)&&$f&&preg_match("~^$Bh*+USE\\b~i",$Gg))$f->query($Gg);do{$I=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Ag:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$sc[]=" <a href='#sql-$fb'>$fb</a>";if($_POST["error_stops"])break
2;}else{$ii=" <span class='time'>(".format_time($Hh).")</span>".(strlen($Gg)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Gg))."'>".'Edit'."</a>":"");$ka=connection()->affected_rows;$fj=($_POST["only_errors"]?"":driver()->warnings());$gj="warnings-$fb";if($fj)$ii
.=", <a href='#$gj'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$gj');","");$_c=null;$Pf=null;$Ac="explain-$fb";if(is_object($I)){$y=$_POST["limit"];$Pf=print_select_result($I,$f,array(),$y);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$uf=$I->num_rows;echo"<p class='sql-footer'>".($uf?($y&&$uf>$y?sprintf('%d / ',$y):"").lang_format(array('%d row','%d rows'),$uf):""),$ii;if($f&&preg_match("~^($Bh|\\()*+SELECT\\b~i",$Gg)&&($_c=explain($f,$Gg)))echo", <a href='#$Ac'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Ac');","");$s="export-$fb";echo", <a href='#$s'>".'Export'."</a>".script("qsl('a').onclick = partial(toggle, '$s');","")."<span id='$s' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ia["output"])." ".html_select("format",adminer()->dumpFormat(),$ia["format"]).input_hidden("query",$Gg)."<input type='submit' name='export' value='".'Export'."'>".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$Bh*+(CREATE|DROP|ALTER)$Bh++(DATABASE|SCHEMA)\\b~i",$Gg)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$ka)."$ii\n";}echo($fj?"<div id='$gj' class='hidden'>\n$fj</div>\n":"");if($_c){echo"<div id='$Ac' class='hidden explain'>\n";print_select_result($_c,$f,$Pf);echo"</div>\n";}}$Hh=microtime(true);}while(connection()->next_result());}$H=substr($H,$xf);$xf=0;}}}}if($lc)echo"<p class='message'>".'No commands to execute.'."\n";elseif($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$fb-count($sc))," <span class='time'>(".format_time($ti).")</span>\n";elseif($sc&&$fb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$sc)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form">
';$yc="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$Gg=$_GET["sql"];if($_POST)$Gg=$_POST["query"];elseif($_GET["history"]=="all")$Gg=$vd;elseif($_GET["history"]!="")$Gg=idx($vd[$_GET["history"]],0);echo"<p>";textarea("query",$Gg,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"<p>";adminer()->sqlPrintAfter();echo"$yc\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$md=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",file_input("SQL$md: <input type='file' name='sql_file[]' multiple>\n$yc"),"</div></fieldset>\n";$Fd=adminer()->importServerPath();if($Fd)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Fd)."$md</code>"),' <input type="submit" name="webfile" value="'.'Run file'.'">',"</div></fieldset>\n";echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$vd){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($vd);$X;$X=prev($vd)){$w=key($vd);list($Gg,$ii,$hc)=$X;echo'<a href="'.h(ME."sql=&history=$w").'">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$ii)."'>".@date("H:i:s",$ii)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$ze).*~m",'',$Gg)))),80,"</code>").($hc?" <span class='time'>($hc)</span>":"")."<br>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$m=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$m):""):where($_GET,$m));$Ki=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($m
as$C=>$l){if((!$Ki&&!isset($l["privileges"]["insert"]))||adminer()->fieldName($l)=="")unset($m[$C]);}if($_POST&&!$k&&!isset($_GET["select"])){$_=$_POST["referer"];if($_POST["insert"])$_=($Ki?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$_))$_=ME."select=".urlencode($a);$v=indexes($a);$Fi=unique_array($_GET["where"],$v);$Jg="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($_,'Item has been deleted.',driver()->delete($a,$Jg,$Fi?0:1));else{$O=array();foreach($m
as$C=>$l){$X=process_input($l);if($X!==false&&$X!==null)$O[idf_escape($C)]=$X;}if($Ki){if(!$O)redirect($_);queries_redirect($_,'Item has been updated.',driver()->update($a,$O,$Jg,$Fi?0:1));if(is_ajax()){page_headers();page_messages($k);exit;}}else{$I=driver()->insert($a,$O);$qe=($I?last_id($I):0);queries_redirect($_,sprintf('Item%s has been inserted.',($qe?" $qe":"")),$I);}}}$K=null;if($Z){$M=array();foreach($m
as$C=>$l){if(isset($l["privileges"]["select"])){$ta=($_POST["clone"]&&$l["auto_increment"]?"''":convert_field($l));$M[]=($ta?"$ta AS ":"").idf_escape($C);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$k=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$m){if(!$Z){$I=driver()->select($a,array("*"),array(),array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array(driver()->primary=>"");}if($K){foreach($K
as$w=>$X){if(!$Z)$K[$w]=null;$m[$w]=array("field"=>$w,"null"=>($w!=driver()->primary),"auto_increment"=>($w==driver()->primary));}}}if($_POST["save"])$K=(array)$_POST["fields"]+($K?$K:array());edit_form($a,$m,$K,$Ki,$k);}elseif(isset($_GET["create"])){$a=$_GET["create"];$gg=driver()->partitionBy;$kg=($gg?driver()->partitionsInfo($a):array());$Pg=referencable_primary($a);$Yc=array();foreach($Pg
as$Uh=>$l)$Yc[str_replace("`","``",$Uh)."`".str_replace("`","``",$l["field"])]=$Uh;$Sf=array();$S=array();if($a!=""){$Sf=fields($a);$S=table_status1($a);if(count($S)<2)$k='No tables.';}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$k)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($K["fields"])&&!$k){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$m=array();$na=array();$Qi=false;$Wc=array();$Rf=reset($Sf);$ma=" FIRST";foreach($K["fields"]as$w=>$l){$n=$Yc[$l["type"]];$Bi=($n!==null?$Pg[$n]:$l);if($l["field"]!=""){if(!$l["generated"])$l["default"]=null;$Fg=process_field($l,$Bi);$na[]=array($l["orig"],$Fg,$ma);if(!$Rf||$Fg!==process_field($Rf,$Rf)){$m[]=array($l["orig"],$Fg,$ma);if($l["orig"]!=""||$ma)$Qi=true;}if($n!==null)$Wc[idf_escape($l["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$Yc[$l["type"]],'source'=>array($l["field"]),'target'=>array($Bi["field"]),'on_delete'=>$l["on_delete"],));$ma=" AFTER ".idf_escape($l["field"]);}elseif($l["orig"]!=""){$Qi=true;$m[]=array($l["orig"]);}if($l["orig"]!=""){$Rf=next($Sf);if(!$Rf)$ma="";}}$ig=array();if(in_array($K["partition_by"],$gg)){foreach($K
as$w=>$X){if(preg_match('~^partition~',$w))$ig[$w]=$X;}foreach($ig["partition_names"]as$w=>$C){if($C==""){unset($ig["partition_names"][$w]);unset($ig["partition_values"][$w]);}}$ig["partition_names"]=array_values($ig["partition_names"]);$ig["partition_values"]=array_values($ig["partition_values"]);if($ig==$kg)$ig=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$ig=null;$B='Table has been altered.';if($a==""){cookie("adminer_engine",$K["Engine"]);$B='Table has been created.';}$C=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($C),$B,alter_table($a,$C,(JUSH=="sqlite"&&($Qi||$Wc)?$na:$m),$Wc,($K["Comment"]!=$S["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$S["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$S["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$ig));}}page_header(($a!=""?'Alter table':'Create table'),$k,array("table"=>$a),h($a));if(!$_POST){$Ci=driver()->types();$K=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($Ci["int"])?"int":(isset($Ci["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$K=$S;$K["name"]=$a;$K["fields"]=array();if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($Sf
as$l){$l["generated"]=$l["generated"]?:(isset($l["default"])?"DEFAULT":"");$K["fields"][]=$l;}if($gg){$K+=$kg;$K["partition_names"][]="";$K["partition_values"][]="";}}}$cb=collations();if(is_array(reset($cb)))$cb=call_user_func_array('array_merge',array_values($cb));$nc=driver()->engines();foreach($nc
as$mc){if(!strcasecmp($mc,$K["Engine"])){$K["Engine"]=$mc;break;}}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($K["name"])."' autocapitalize='off'>\n",($nc?html_select("Engine",array(""=>"(".'engine'.")")+$nc,$K["Engine"]).on_help("event.target.value",1).script("qsl('select').onchange = helpClose;")."\n":"");if($cb)echo"<datalist id='collations'>".optionlist($cb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($K["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$cb,"TABLE",$Yc);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($K["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',"columnShow(this.checked, 5)","jsonly");$ib=($_POST?$_POST["comments"]:get_setting("comments"));echo(support("comment")?checkbox("comments",1,$ib,'Comment',"editingCommentsClick(this, true);","jsonly").' '.(preg_match('~\n~',$K["Comment"])?"<textarea name='Comment' rows='2' cols='20'".($ib?"":" class='hidden'").">".h($K["Comment"])."</textarea>":'<input name="Comment" value="'.h($K["Comment"]).'" data-maxlength="'.(min_version(5.5)?2048:60).'"'.($ib?"":" class='hidden'").'>'):''),'<p>
<input type="submit" value="Save">
';}echo'
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));if($gg&&(JUSH=='sql'||$a=="")){$hg=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset("partition",'Partition by',$K["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$gg),$K["partition_by"]).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')",1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($K["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($hg||!$K["partition_by"]?" hidden":"")."' value='".h($K["partitions"])."'>\n","<table id='partition-table'".($hg?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."</thead>\n";foreach($K["partition_names"]as$w=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($w==count($K["partition_names"])-1?script("qsl('input').oninput = partitionNameChange;"):''),'<td><input name="partition_values[]" value="'.h(idx($K["partition_values"],$w)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$Md=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$Kd=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$Md[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$Md[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$S["Engine"]))$Md[]="VECTOR";$v=indexes($a);$m=fields($a);$_g=array();if(JUSH=="mongo"){$_g=$v["_id_"];unset($Md[0]);unset($v["_id_"]);}$K=$_POST;if($K)save_settings(array("index_options"=>$K["options"]));if($_POST&&!$k&&!$_POST["add"]&&!$_POST["drop_col"]){$pa=array();foreach($K["indexes"]as$u){$C=$u["name"];if(in_array($u["type"],$Md)){$d=array();$xe=array();$Pb=array();$Ld=(support("partial_indexes")?$u["partial"]:"");$Jd=(in_array($u["algorithm"],$Kd)?$u["algorithm"]:"");$O=array();ksort($u["columns"]);foreach($u["columns"]as$w=>$c){if($c!=""){$x=idx($u["lengths"],$w);$Nb=idx($u["descs"],$w);$O[]=($m[$c]?idf_escape($c):$c).($x?"(".(+$x).")":"").($Nb?" DESC":"");$d[]=$c;$xe[]=($x?:null);$Pb[]=$Nb;}}$zc=$v[$C];if($zc){ksort($zc["columns"]);ksort($zc["lengths"]);ksort($zc["descs"]);if($u["type"]==$zc["type"]&&array_values($zc["columns"])===$d&&(!$zc["lengths"]||array_values($zc["lengths"])===$xe)&&array_values($zc["descs"])===$Pb&&$zc["partial"]==$Ld&&(!$Kd||$zc["algorithm"]==$Jd)){unset($v[$C]);continue;}}if($d)$pa[]=array($u["type"],$C,$O,$Jd,$Ld);}}foreach($v
as$C=>$zc)$pa[]=array($zc["type"],$C,"DROP");if(!$pa)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),'Indexes have been altered.',alter_indexes($a,$pa));}page_header('Indexes',$k,array("table"=>$a),h($a));$Mc=array_keys($m);if($_POST["add"]){foreach($K["indexes"]as$w=>$u){if($u["columns"][count($u["columns"])]!="")$K["indexes"][$w]["columns"][]="";}$u=end($K["indexes"]);if($u["type"]||array_filter($u["columns"],'strlen'))$K["indexes"][]=array("columns"=>array(1=>""));}if(!$K){foreach($v
as$w=>$u){$v[$w]["name"]=$w;$v[$w]["columns"][]="";}$v[]=array("columns"=>array(1=>""));$K["indexes"]=$v;}$xe=(JUSH=="sql"||JUSH=="mssql");$wh=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';$Dd=" class='idxopts".($wh?"":" hidden")."'";if($Kd)echo"<th id='label-algorithm'$Dd>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/',));echo'<th><input type="submit" class="wayoff">','Columns'.($xe?"<span$Dd> (".'length'.")</span>":"");if($xe||support("descidx"))echo
checkbox("options",1,$wh,'Options',"indexOptionsShow(this.checked)","jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Dd>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
</thead>
';if($_g){echo"<tr><td>PRIMARY<td>";foreach($_g["columns"]as$w=>$c)echo
select_input(" disabled",$Mc,$c),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$fe=1;foreach($K["indexes"]as$u){if(!$_POST["drop_col"]||$fe!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$fe][type]",array(-1=>"")+$Md,$u["type"],($fe==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type");if($Kd)echo"<td$Dd>".html_select("indexes[$fe][algorithm]",array_merge(array(""),$Kd),$u['algorithm'],"label-algorithm");echo"<td>";ksort($u["columns"]);$r=1;foreach($u["columns"]as$w=>$c){echo"<span>".select_input(" name='indexes[$fe][columns][$r]' title='".'Column'."'",($m&&($c==""||$m[$c])?array_combine($Mc,$Mc):array()),$c,"partial(".($r==count($u["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(JUSH=="sql"?"":$_GET["indexes"]."_")."')"),"<span$Dd>",($xe?"<input type='number' name='indexes[$fe][lengths][$r]' class='size' value='".h(idx($u["lengths"],$w))."' title='".'Length'."'>":""),(support("descidx")?checkbox("indexes[$fe][descs][$r]",1,idx($u["descs"],$w),'descending'):""),"</span> </span>";$r++;}echo"<td><input name='indexes[$fe][name]' value='".h($u["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Dd><input name='indexes[$fe][partial]' value='".h($u["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$fe]","x",'Remove').script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");}$fe++;}echo'</table>
</div>
<p>
<input type="submit" value="Save">
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!$k&&!$_POST["add"]){$C=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$C){if(DB!=""){$_GET["db"]=$C;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($C),'Database has been renamed.',rename_database($C,$K["collation"]));}else{$h=explode("\n",str_replace("\r","",$C));$Mh=true;$pe="";foreach($h
as$i){if(count($h)==1||$i!=""){if(!create_database($i,$K["collation"]))$Mh=false;$pe=$i;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($pe),'Database has been created.',$Mh);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($C).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$k,array(),h(DB));$cb=collations();$C=DB;if($_POST)$C=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,$cb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$hd){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$hd,$A)&&$A[1]){$C=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($C,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($C).'</textarea><br>':'<input name="name" autofocus value="'.h($C).'" data-maxlength="64" autocapitalize="off">')."\n".($cb?html_select("collation",array(""=>"(".'collation'.")")+$cb,$K["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",)):""),'<input type="submit" value="Save">
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',DB))."\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$k);$bh=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Gd=array();$Wf=array();foreach($bh["fields"]as$r=>$l){if(substr($l["inout"],-3)=="OUT"&&JUSH=='sql')$Wf[$r]="@".idf_escape($l["field"])." AS ".idf_escape($l["field"]);if(!$l["inout"]||substr($l["inout"],0,2)=="IN")$Gd[]=$r;}if(!$k&&$_POST){$Na=array();foreach($bh["fields"]as$w=>$l){$X="";if(in_array($w,$Gd)){$X=process_input($l);if($X===false)$X="''";if(isset($Wf[$w]))connection()->query("SET @".idf_escape($l["field"])." = $X");}if(isset($Wf[$w]))$Na[]="@".idf_escape($l["field"]);elseif(in_array($w,$Gd))$Na[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($bh["returns"],"type")=="record"?"* FROM ":"").table($ba)."(".implode(", ",$Na).")";$Hh=microtime(true);$I=connection()->multi_query($H);$ka=connection()->affected_rows;echo
adminer()->selectQuery($H,$Hh,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$f=connect();if($f)$f->select_db(DB);do{$I=connection()->store_result();if(is_object($I))print_select_result($I,$f);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$ka)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($Wf)print_select_result(connection()->query("SELECT ".implode(", ",$Wf)));}}echo'
<form action="" method="post">
';if($Gd){echo"<table class='layout'>\n";foreach($Gd
as$w){$l=$bh["fields"][$w];$C=$l["field"];echo"<tr><th>".adminer()->fieldName($l);$Y=idx($_POST["fields"],$C);if($Y!=""){if($l["type"]=="set")$Y=implode(",",$Y);}input($l,$Y,idx($_POST["function"],$C,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type="submit" value="Call">
',input_token(),'</form>

<pre>
';function
pre_tr($eh){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($eh))));}$R='(\+--[-+]+\+\n)';$K='(\| .* \|\n)';echo
preg_replace_callback("~^$R?$K$R?($K*)$R?~m",function($A){$Sc=pre_tr($A[2]);return"<table>\n".($A[1]?"<thead>$Sc</thead>\n":$Sc).pre_tr($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($bh['comment']))));echo'</pre>
';}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$C=$_GET["name"];$K=$_POST;if($_POST&&!$k&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$ci=array();foreach($K["source"]as$w=>$X)$ci[$w]=$K["target"][$w];$K["target"]=$ci;}if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(" $C"=>($K["drop"]?"":" ".format_foreign_key($K))));else{$pa="ALTER TABLE ".table($a);$I=($C==""||queries("$pa DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($C)));if(!$K["drop"])$I=queries("$pa ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Foreign key has been dropped.':($C!=""?'Foreign key has been altered.':'Foreign key has been created.')),$I);if(!$K["drop"])$k='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header('Foreign key',$k,array("table"=>$a),h($a));if($_POST){ksort($K["source"]);if($_POST["change"]||$_POST["change-js"])$K["target"]=array();else$K["source"][]="";}elseif($C!=""){$Yc=foreign_keys($a);$K=$Yc[$C];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=array("");}echo'
<form action="" method="post">
';$Ah=array_keys(fields($a));if($K["db"]!="")connection()->select_db($K["db"]);if($K["ns"]!=""){$Tf=get_schema();set_schema($K["ns"]);}$Og=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$ci=array_keys(fields(in_array($K["table"],$Og)?$K["table"]:reset($Og)));$Ef="this.form['change-js'].value = '1'; this.form.submit();";echo"<p><label>".'Target table'.": ".html_select("table",$Og,$K["table"],$Ef)."</label>\n";if(JUSH!="sqlite"){$Fb=array();foreach(adminer()->databases()as$i){if(!information_schema($i))$Fb[]=$i;}echo"<label>".'DB'.": ".html_select("db",$Fb,$K["db"]!=""?$K["db"]:$_GET["db"],$Ef)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';$fe=0;foreach($K["source"]as$w=>$X){echo"<tr>","<td>".html_select("source[".(+$w)."]",array(-1=>"")+$Ah,$X,($fe==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$w)."]",$ci,idx($K["target"],$w),"","label-target");$fe++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$K["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$K["on_update"]),'</label>
',(DRIVER==='pgsql'?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$K["deferrable"]).' ':''),doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",)),'<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';if($C!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$C));echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$Uf="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$Uf=strtoupper($P["Engine"]);}if($_POST&&!$k){$C=trim($K["name"]);$ta=" AS\n$K[select]";$_=ME."table=".urlencode($C);$B='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$C&&JUSH!="sqlite"&&$U=="VIEW"&&$Uf=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($C).$ta,$_,$B);else{$ei=$C."_adminer_".uniqid();drop_create("DROP $Uf ".table($a),"CREATE $U ".table($C).$ta,"DROP $U ".table($C),"CREATE $U ".table($ei).$ta,"DROP $U ".table($ei),($_POST["drop"]?substr(ME,0,-1):$_),'View has been dropped.',$B,'View has been created.',$a,$C);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($Uf!="VIEW");if(!$k)$k=error();}page_header(($a!=""?'Alter view':'Create view'),$k,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$K["materialized"],'Materialized view'):""),'<p>';textarea("select",$K["select"]);echo'<p>
<input type="submit" value="Save">
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$Wd=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$Ih=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$K=$_POST;if($_POST&&!$k){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($K["INTERVAL_FIELD"],$Wd)&&isset($Ih[$K["STATUS"]])){$fh="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$fh.($aa!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$fh)."\n".$Ih[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$k);if(!$K&&$aa!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$K=reset($L);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($K["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$K[EXECUTE_AT]$K[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($K["ENDS"]),'">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="',h($K["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$Wd,$K["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$Ih,$K["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($K["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo'<p>
<input type="submit" value="Save">
';if($aa!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$aa));echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$bh=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])&&!$k){foreach($K["fields"]as$w=>$l){if($l["field"]=="")unset($K["fields"][$w]);}$_f=routine_id($ba,routine($_GET["procedure"],$bh));$nf=routine_id($K["name"],$K);$g=create_routine($bh,$K);$_=substr(ME,0,-1);$B='Routine has been altered.';if(!$_POST["drop"]&&$_f==$nf&&connection()->flavor!="mysql")query_redirect(substr_replace($g,' OR REPLACE',6,0),$_,$B);else{$ei="$K[name]_adminer_".uniqid();drop_create("DROP $bh $_f",$g,"DROP $bh $nf",create_routine($bh,array("name"=>$ei)+$K),"DROP $bh ".routine_id($ei,$K),$_,'Routine has been dropped.',$B,'Routine has been created.',$ba,$K["name"]);}}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$k);if(!$_POST){if($ba=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$bh);$K["name"]=$ba;}}$cb=get_vals("SHOW CHARACTER SET");sort($cb);$ch=routine_languages();echo($cb?"<datalist id='collations'>".optionlist($cb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',($ch?"<label>".'Language'.": ".html_select("language",$ch,$K["language"])."</label>\n":""),'<input type="submit" value="Save">
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($K["fields"],$cb,$bh);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$K["returns"],$cb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$K["definition"],20);echo'<p>
<input type="submit" value="Save">
';if($ba!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$ba));echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$C=$_GET["name"];$K=$_POST;if($K&&!$k){if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(),"",array(),"$C",($K["drop"]?"":$K["clause"]));else{$I=($C==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($C)));if(!$K["drop"])$I=queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Check has been dropped.':($C!=""?'Check has been altered.':'Check has been created.')),$I);}page_header(($C!=""?'Alter check'.": ".h($C):'Create check'),$k,array("table"=>$a));if(!$K){$Ua=driver()->checkConstraints($a);$K=array("name"=>$C,"clause"=>$Ua[$C]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($K["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",),"?"),'<p>';textarea("clause",$K["clause"]);echo'<p><input type="submit" value="Save">
';if($C!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$C));echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$C="$_GET[name]";$zi=trigger_options();$K=(array)trigger($C,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$k&&in_array($_POST["Timing"],$zi["Timing"])&&in_array($_POST["Event"],$zi["Event"])&&in_array($_POST["Type"],$zi["Type"])){$Cf=" ON ".table($a);$ac="DROP TRIGGER ".idf_escape($C).(JUSH=="pgsql"?$Cf:"");$_=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ac,$_,'Trigger has been dropped.');else{if($C!="")queries($ac);queries_redirect($_,($C!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Cf,$_POST)));if($C!="")queries(create_trigger($Cf,$K+array("Type"=>reset($zi["Type"]))));}}$K=$_POST;}page_header(($C!=""?'Alter trigger'.": ".h($C):'Create trigger'),$k,array("table"=>$a));echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>',html_select("Timing",$zi["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>Event<td>',html_select("Event",$zi["Event"],$K["Event"],"this.form['Timing'].onchange();"),(in_array("UPDATE OF",$zi["Event"])?" <input name='Of' value='".h($K["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$zi["Type"],$K["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($K["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';textarea("Statement",$K["Statement"]);echo'<p>
<input type="submit" value="Save">
';if($C!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$C));echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$da=$_GET["user"];$Dg=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$pb)$Dg[$pb=="File access on server"?"Server Admin":$pb][$K["Privilege"]]=$K["Comment"];}unset($Dg["Server Admin"]["Usage"]);foreach($Dg["Tables"]as$w=>$X)unset($Dg["Databases"][$w]);$mf=array();if($_POST){foreach($_POST["objects"]as$w=>$X)$mf[$X]=(array)$mf[$X]+idx($_POST["grants"],$w,array());}$id=array();if(isset($_GET["host"])&&($I=connection()->query("SHOW GRANTS FOR ".q($da)."@".q($_GET["host"])))){while($K=$I->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Ie,PREG_SET_ORDER)){foreach($Ie
as$X){if($X[1]!="USAGE")$id["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$id["$A[2]$X[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$k){$Bf=(isset($_GET["host"])?q($da)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Bf",ME."privileges=",'User has been dropped.');else{$pf=q($_POST["user"])."@".q($_POST["host"]);$lg=$_POST["pass"];$ub=false;$I=true;if($Bf!=$pf){$ub=queries("CREATE USER $pf IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($lg));$I=$ub;}elseif($lg!="")$I=queries("SET PASSWORD FOR $pf = ".(min_version(8,99)||$_POST["hashed"]?q($lg):"PASSWORD(".q($lg).")"));if($I){$Yg=array();foreach($mf
as$wf=>$hd){if(isset($_GET["grant"]))$hd=array_filter($hd);$hd=array_keys($hd);if(isset($_GET["grant"]))$Yg=array_diff(array_keys(array_filter($mf[$wf],'strlen')),$hd);elseif($Bf==$pf){$zf=array_keys((array)$id[$wf]);$Yg=array_diff($zf,$hd);$hd=array_diff($hd,$zf);unset($id[$wf]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$wf,$A)&&(!grant("REVOKE",$Yg,$A[2]," ON $A[1] FROM $pf")||!grant("GRANT",$hd,$A[2]," ON $A[1] TO $pf"))){$I=false;break;}}}if($I&&isset($_GET["host"])){if($Bf!=$pf)queries("DROP USER $Bf");elseif(!isset($_GET["grant"])){foreach($id
as$wf=>$Yg){if(preg_match('~^(.+)(\(.*\))?$~U',$wf,$A))grant("REVOKE",array_keys($Yg),$A[2]," ON $A[1] FROM $pf");}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),$I);if($ub)connection()->query("DROP USER $pf");}}page_header((isset($_GET["host"])?'Username'.": ".h("$da@$_GET[host]"):'Create user'),$k,array("privileges"=>array('','Privileges')));$K=$_POST;if($K)$id=$mf;else{$K=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$id[(DB==""||$id?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($K["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($K["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($K["pass"]),'" autocomplete="new-password">
',($K["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$K["hashed"],'Hashed',"typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$r=0;foreach($id
as$wf=>$hd){echo'<th>'.($wf!="*.*"?"<input name='objects[$r]' value='".h($wf)."' size='10' autocapitalize='off'>":input_hidden("objects[$r]","*.*")."*.*");$r++;}echo"</thead>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Procedures"=>'Routine',)as$pb=>$Nb){foreach((array)$Dg[$pb]as$Cg=>$gb){echo"<tr><td".($Nb?">$Nb<td":" colspan='2'").' lang="en" title="'.h($gb).'">'.h($Cg);$r=0;foreach($id
as$wf=>$hd){$C="'grants[$r][".h(strtoupper($Cg))."]'";$Y=$hd[strtoupper($Cg)];if($pb=="Server Admin"&&$wf!=(isset($id["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$C><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$C value='1'".($Y?" checked":"").($Cg=="All privileges"?" id='grants-$r-all'>":">".($Cg=="Grant option"?"":script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$r-all'); };"))),"</label>";$r++;}}}echo"</table>\n",'<p>
<input type="submit" value="Save">
';if(isset($_GET["host"]))echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',"$da@$_GET[host]"));echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$k){$le=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$le++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$le),$le||!$_POST["kill"]);}}page_header('Process list',$k);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");$r=-1;foreach(adminer()->processList()as$r=>$K){if(!$r){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$w=>$X)echo"<th>$w".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($w),));echo"</thead>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[JUSH=="sql"?"Id":"pid"],0):"");foreach($K
as$w=>$X)echo"<td>".($X!=""&&((JUSH=="sql"&&$w=="Info"&&preg_match("~Query|Killed~",$K["Command"]))||(JUSH=="pgsql"&&$w=="query")||(JUSH=="oracle"&&$w=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($X)."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.'Clone'.'</a>'.' <a href="" class="jsonly copy">🗐</a>':h($X));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo($r+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$v=indexes($a);$m=fields($a);$Yc=column_foreign_keys($a);$yf=$S["Oid"];$ja=get_settings("adminer_import");$Zg=array();$d=array();$kh=array();$Mf=array();$hi="";foreach($m
as$w=>$l){$C=adminer()->fieldName($l);$kf=html_entity_decode(strip_tags($C),ENT_QUOTES);if(isset($l["privileges"]["select"])&&$C!=""){$d[$w]=$kf;if(is_shortable($l))$hi=adminer()->selectLengthProcess();}if(isset($l["privileges"]["where"])&&$C!="")$kh[$w]=$kf;if(isset($l["privileges"]["order"])&&$C!="")$Mf[$w]=$kf;$Zg+=$l["privileges"];}list($M,$q)=adminer()->selectColumnsProcess($d,$v);$M=array_unique($M);$q=array_unique($q);$ae=count($q)<count($M);$Z=adminer()->selectSearchProcess($m,$v);$D=adminer()->selectOrderProcess($m,$v);$y=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Gi=>$K){$ta=convert_field($m[key($K)]);$M=array($ta?:idf_escape(key($K)));$Z[]=where_check($Gi,$m);$J=driver()->select($a,$M,$Z,$M);if($J)echo
first($J->fetch_row());}exit;}$_g=$Ii=array();foreach($v
as$u){if($u["type"]=="PRIMARY"){$_g=array_flip($u["columns"]);$Ii=($M?$_g:array());foreach($Ii
as$w=>$X){if(in_array(idf_escape($w),$M))unset($Ii[$w]);}break;}}if($yf&&!$_g){$_g=$Ii=array($yf=>0);$v[]=array("type"=>"PRIMARY","columns"=>array($yf));}if($_POST&&!$k){$ij=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Ua=array();foreach($_POST["check"]as$Ra)$Ua[]=where_check($Ra,$m);$ij[]="((".implode(") OR (",$Ua)."))";}$ij=($ij?"\nWHERE ".implode(" AND ",$ij):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$cd=($M?implode(", ",$M):"*").convert_fields($d,$m,$M)."\nFROM ".table($a);$kd=($q&&$ae?"\nGROUP BY ".implode(", ",$q):"").($D?"\nORDER BY ".implode(", ",$D):"");$H="SELECT $cd$ij$kd";if(is_array($_POST["check"])&&!$_g){$Ei=array();foreach($_POST["check"]as$X)$Ei[]="(SELECT".limit($cd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$m).$kd,1).")";$H=implode(" UNION ALL ",$Ei);}adminer()->dumpData($a,"table",$H);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$Yc)){if($_POST["save"]||$_POST["delete"]){$I=true;$ka=0;$O=array();if(!$_POST["delete"]){foreach($m
as$C=>$X){$t=bracket_escape($C);if(isset($_POST["fields"][$t])||$_FILES["fields-$t"]){$X=process_input($m[$C]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($C)]=($X!==false?$X:idf_escape($C));}}}if($_POST["delete"]||$O){$H=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($_g&&is_array($_POST["check"]))||$ae){$I=($_POST["delete"]?driver()->delete($a,$ij):($_POST["clone"]?queries("INSERT $H$ij".driver()->insertReturning($a)):driver()->update($a,$O,$ij)));$ka=connection()->affected_rows;if(is_object($I))$ka+=$I->num_rows;}else{foreach((array)$_POST["check"]as$X){$hj="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$m);$I=($_POST["delete"]?driver()->delete($a,$hj,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$hj)):driver()->update($a,$O,$hj,1)));if(!$I)break;$ka+=connection()->affected_rows;}}}$B=lang_format(array('%d item has been affected.','%d items have been affected.'),$ka);if($_POST["clone"]&&$I&&$ka==1){$qe=last_id($I);if($qe)$B=sprintf('Item%s has been inserted.'," $qe");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$B,$I);if(!$_POST["delete"]){$wg=(array)$_POST["fields"];edit_form($a,array_intersect_key($m,$wg),$wg,!$_POST["clone"],$k);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$k='Ctrl+click on a value to modify it.';else{$I=true;$ka=0;foreach($_POST["val"]as$Gi=>$K){$O=array();foreach($K
as$w=>$X){$w=bracket_escape($w,true);$O[idf_escape($w)]=(preg_match('~char|text~',$m[$w]["type"])||$X!=""?adminer()->processInput($m[$w],$X):"NULL");}$I=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Gi,$m),($ae||$_g?0:1)," ");if(!$I)break;$ka+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$ka),$I);}}elseif(!is_string($Nc=get_file("csv_file",true)))$k=upload_error($Nc);elseif(!preg_match('~~u',$Nc))$k='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$ja["output"],"format"=>$_POST["separator"]),"adminer_import");$I=true;$db=array_keys($m);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Nc,$Ie);$ka=count($Ie[0]);driver()->begin();$qh=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Ie[0]as$w=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$qh]*)$qh~",$X.$qh,$Je);if(!$w&&!array_diff($Je[1],$db)){$db=$Je[1];$ka--;}else{$O=array();foreach($Je[1]as$r=>$Za)$O[idf_escape($db[$r])]=($Za==""&&$m[$db[$r]]["null"]?"NULL":q(preg_match('~^".*"$~s',$Za)?str_replace('""','"',substr($Za,1,-1)):$Za));$L[]=$O;}}$I=(!$L||driver()->insertUpdate($a,$L,$_g));if($I)driver()->commit();queries_redirect(remove_from_uri("page"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$ka),$I);driver()->rollback();}}}$Uh=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $Uh",$k);$O=null;if(isset($Zg["insert"])||!support("table")){$bg=array();foreach((array)$_GET["where"]as$X){if(isset($Yc[$X["col"]])&&count($Yc[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$bg["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$bg?"&".http_build_query($bg):"";}adminer()->selectLinks($S,$O);if(!$d&&support("table"))echo"<p class='error'>".'Unable to select the table'.($m?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$d);adminer()->selectSearchPrint($Z,$kh,$v);adminer()->selectOrderPrint($D,$Mf,$v);adminer()->selectLimitPrint($y);adminer()->selectLengthPrint($hi);adminer()->selectActionPrint($v);echo"</form>\n";$E=$_GET["page"];$bd=null;if($E=="last"){$bd=get_val(count_rows($a,$Z,$ae,$q));$E=floor(max(0,intval($bd)-1)/$y);}$lh=$M;$jd=$q;if(!$lh){$lh[]="*";$qb=convert_fields($d,$m,$M);if($qb)$lh[]=substr($qb,2);}foreach($M
as$w=>$X){$l=$m[idf_unescape($X)];if($l&&($ta=convert_field($l)))$lh[$w]="$ta AS $X";}if(!$ae&&$Ii){foreach($Ii
as$w=>$X){$lh[]=idf_escape($w);if($jd)$jd[]=idf_escape($w);}}$I=driver()->select($a,$lh,$Z,$jd,$D,$y,$E,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$E)$I->seek($y*$E);$kc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($E&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$y&&$q&&$ae&&JUSH=="sql")$bd=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ca=adminer()->backwardKeys($a,$Uh);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$q&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$lf=array();$ed=array();reset($M);$Lg=1;foreach($L[0]as$w=>$X){if(!isset($Ii[$w])){$X=idx($_GET["columns"],key($M))?:array();$l=$m[$M?($X?$X["col"]:current($M)):$w];$C=($l?adminer()->fieldName($l,$Lg):($X["fun"]?"*":h($w)));if($C!=""){$Lg++;$lf[$w]=$C;$c=idf_escape($w);$zd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($w);$Nb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($w))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$dd=apply_sql_function($X["fun"],$C);$_h=isset($l["privileges"]["order"])||$dd!=$C;echo($_h?"<a href='".h($zd.($D[0]==$c||$D[0]==$w?$Nb:''))."'>$dd</a>":$dd);$Ue=($_h?"<a href='".h($zd.$Nb)."' title='".'descending'."' class='text'> ↓</a>":'');if(!$X["fun"]&&isset($l["privileges"]["where"])){$Ue
.='<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>';$Ue
.=script("qsl('a').onclick = partial(selectSearch, '".js_escape($w)."');");}echo($Ue?"<span class='column hidden'>$Ue</span>":"");}$ed[$w]=$X["fun"];next($M);}}$xe=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$w=>$X)$xe[$w]=max($xe[$w],min(40,strlen(utf8_decode($X))));}}echo($Ca?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$Yc)as$jf=>$K){$Fi=unique_array($L[$jf],$v);if(!$Fi){$Fi=array();reset($M);foreach($L[$jf]as$w=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Fi[$w]=$X;next($M);}}$Gi="";foreach($Fi
as$w=>$X){$l=(array)$m[$w];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$l["type"])&&strlen($X)>64){$w=(strpos($w,'(')?$w:idf_escape($w));$w="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$l["collation"])?$w:"CONVERT($w USING ".charset(connection()).")").")";$X=md5($X);}$Gi
.="&".($X!==null?urlencode("where[".bracket_escape($w)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($w));}echo"<tr>".(!$q&&$M?"":"<td>".checkbox("check[]",substr($Gi,1),in_array(substr($Gi,1),(array)$_POST["check"])).($ae||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$Gi)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$w=>$X){if(isset($lf[$w])){$c=current($M);$l=(array)$m[$w];if($X!=""&&(!isset($kc[$w])||$kc[$w]!=""))$kc[$w]=(is_mail($X)?$lf[$w]:"");$z="";if(is_blob($l)&&$X!="")$z=ME.'download='.urlencode($a).'&field='.urlencode($w).$Gi;if(!$z&&$X!==null){foreach((array)$Yc[$w]as$n){if(count($Yc[$w])==1||end($n["source"])==$w){$z="";foreach($n["source"]as$r=>$Ah)$z
.=where_link($r,$n["target"][$r],$L[$jf][$Ah]);$z=($n["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($n["db"]),ME):ME).'select='.urlencode($n["table"]).$z;if($n["ns"])$z=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($n["ns"]),$z);if(count($n["source"])==1)break;}}}if($c=="COUNT(*)"){$z=ME."select=".urlencode($a);$r=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Fi))$z
.=where_link($r++,$W["col"],$W["val"],$W["op"]);}foreach($Fi
as$ie=>$W)$z
.=where_link($r++,$ie,$W);}$_d=select_value($X,$z,$l,$hi);$s=h("val[$Gi][".bracket_escape($w)."]");$xg=idx(idx($_POST["val"],$Gi),bracket_escape($w));$Ki=idx($l["privileges"],"update");$gc=!is_array($K[$w])&&is_utf8($_d)&&$L[$jf][$w]==$K[$w]&&!$ed[$w]&&!$l["generated"]&&$Ki;$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$c,$A)?$m[idf_unescape($A[2])]["type"]:$l["type"]);$gi=preg_match('~text|json|lob~',$U);$be=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$c);echo"<td id='$s'".($be&&($X===null||is_numeric(strip_tags($_d))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$gc&&$X!==null)||$xg!==null){$nd=h($xg!==null?$xg:$K[$w]);echo">".($gi?"<textarea name='$s' cols='30' rows='".(substr_count($K[$w],"\n")+1)."'>$nd</textarea>":"<input name='$s' value='$nd' size='$xe[$w]'>");}else{$Ee=strpos($_d,"<i>…</i>");echo($Ki?" data-text='".($Ee?2:($gi?1:0))."'".($gc?"":" data-warning='".h('Use edit link to modify this value.')."'"):"").">$_d";}}next($M);}if($Ca)echo"<td>";adminer()->backwardKeysPrint($Ca,$L[$jf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$E){$xc=true;if($_GET["page"]!="last"){if(!$y||(count($L)<$y&&($L||!$E)))$bd=($E?$E*$y:0)+count($L);elseif(JUSH!="sql"||!$ae){$bd=($ae?false:found_rows($S,$Z));if(intval($bd)<max(1e4,2*($E+1)*$y))$bd=first(slow_query(count_rows($a,$Z,$ae,$q)));elseif(JUSH=='sql'||JUSH=='pgsql')$xc=false;}}$Zf=($y&&($bd===false||$bd>$y||$E));if($Zf)echo(($bd===false?count($L)+1:$bd-$E*$y)>$y?'<p><a href="'.h(remove_from_uri("page|next").($_GET["next"]?"&next=".urlencode($_GET["next"]):"")."&page=".($E+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $y, '".'Loading'."…');",""):''),"\n";echo"<div class='footer'><div>\n";if($Zf){$Ne=($bd===false?$E+($L?(count($L)>=$y?2:1):0):floor(($bd-1)/$y));echo"<fieldset>";if(JUSH!="simpledb"&&JUSH!="redis"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($E+1)."')); return false; };"),pagination(0,$E).($E>5?" …":"");for($r=max(1,$E-4);$r<min($Ne,$E+5);$r++)echo
pagination($r,$E);if($Ne>0)echo($E+5<$Ne?" …":""),($xc&&$bd!==false?pagination($Ne,$E):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Ne'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$E).($E>1?" …":""),($E?pagination($E,$E):""),($Ne>$E?pagination($E+1,$E).($Ne>$E+1?" …":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$Rb=($xc?"":"~ ").$bd;$Ff="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Rb' : checked); selectCount('selected2', this.checked || !checked ? '$Rb' : checked);";echo
checkbox("all",1,0,($bd!==false?($xc?"":"~ ").lang_format(array('%d row','%d rows'),$bd):""),$Ff)."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';$Zc=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$c){if($c["fun"]){unset($Zc['sql']);break;}}if($Zc){print_fieldset("export",'Export'." <span id='selected2'></span>");$Xf=adminer()->dumpOutput();echo($Xf?html_select("output",$Xf,$ja["output"])." ":""),html_select("format",$Zc,$ja["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($kc,'strlen'),$d);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",file_input("<input type='file' name='csv_file'> ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$ja["format"])." <input type='submit' name='import' value='".'Import'."'>"),"</span>";echo
input_token(),"</form>\n",(!$q&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$Yi=($P?adminer()->showStatus():adminer()->showVariables());if(!$Yi)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($Yi
as$K){echo"<tr>";$w=array_shift($K);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($w)."</code>";foreach($K
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Ph=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$C=>$S){json_row("Comment-$C",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$w)json_row("$w-$C",h($S[$w]));foreach($Ph+array("Auto_increment"=>0,"Rows"=>0)as$w=>$X){if($S[$w]!=""){$X=format_number($S[$w]);if($X>=0)json_row("$w-$C",($w=="Rows"&&$X&&$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?"~ $X":$X));if(isset($Ph[$w]))$Ph[$w]+=($S["Engine"]!="InnoDB"||$w!="Data_free"?$S[$w]:0);}elseif(array_key_exists($w,$S))json_row("$w-$C","?");}}}foreach($Ph
as$w=>$X)json_row("sum-$w",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases())as$i=>$X){json_row("tables-$i",$X);json_row("size-$i",db_size($i));}json_row("");}exit;}else{$ai=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($ai&&!$k&&!$_POST["search"]){$I=true;$B="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$B='Tables have been truncated.';}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$B='Tables have been moved.';}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$B='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$B='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$K)$B
.="<b>".h($R)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$I=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),$_POST["tables"]));$B='Tables have been optimized.';}elseif(!$_POST["tables"])$B='No tables.';elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($K=$I->fetch_assoc())$B
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$B,$I);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$k,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$D=$_GET["order"];echo"<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Zh=($D?table_status():tables_list());if(!$Zh)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);",""),'<th><a href="'.h(substr(ME,0,-1)).'">'.'Table'.'</a>';$d=array("Engine"=>array('Engine'.doc_link(array('sql'=>'storage-engines.html'))));if(collations())$d["Collation"]=array('Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$d["Data_length"]=array('Data Length'.doc_link(array('sql'=>'show-table-status.html',)),"create",'Alter table');if(support('indexes'))$d["Index_length"]=array('Index Length'.doc_link(array('sql'=>'show-table-status.html',)),"indexes",'Alter indexes');$d["Data_free"]=array('Data Free'.doc_link(array('sql'=>'show-table-status.html')),"edit",'New item');if(function_exists('Adminer\alter_table'))$d["Auto_increment"]=array('Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",'Alter table');$d["Rows"]=array('Rows'.doc_link(array('sql'=>'show-table-status.html',)),"select",'Select data');if(support("comment"))$d["Comment"]=array('Comment'.doc_link(array('sql'=>'show-table-status.html',)));foreach($d
as$w=>$c)echo"<th><a href='".h(ME)."order=$w'>$c[0]</a>";echo"</thead>\n";if($D){uasort($Zh,function($fa,$_a)use($D){$J=($fa[$D]<$_a[$D]?-1:($fa[$D]>$_a[$D]?1:0));return(in_array($D,array('Engine','Collation','Comment'))?$J:-$J);});}$T=0;foreach($Zh
as$C=>$P){$bj=($D?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$P=($D?$P:array('Engine'=>$P));$s=h("Table-".$C);echo'<tr><td>'.checkbox(($bj?"views[]":"tables[]"),$C,in_array("$C",$ai,true),"","","",$s),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".urlencode($C)."' title='".'Show structure'."' id='$s'>".h($C).'</a>':h($C));if($bj&&!preg_match('~materialized~i',$P['Engine'])){$li='View';echo'<td colspan="'.(count($d)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($C)."' title='".'Alter view'."'>$li</a>":$li),'<td align="right"><a href="'.h(ME)."select=".urlencode($C).'" title="'.'Select data'.'">?</a>';if(support("comment"))echo'<td>'.h($P['Comment']);}else{foreach($d
as$w=>$c){$s=" id='$w-".h($C)."'";$X=idx($P,$w,'?');echo($c[1]?"<td align='right'><a href='".h(ME."$c[1]=").urlencode($C)."'$s title='$c[2]'>".(is_numeric($X)?($X<0?'?':($w=="Rows"&&$X&&$P["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?'~ ':'').format_number($X)):$X)."</a>":"<td id='$w-".h($C)."'>".h($X));}$T++;}echo"\n";}echo"<tr><td><th>".sprintf('%d in total',count($Zh)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');foreach(array("Data_length","Index_length","Data_free")as$w)echo($d[$w]?"<td align='right' id='sum-$w'>":"");echo"\n","</table>\n",($D?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$Vi="<input type='submit' value='".'Vacuum'."'> ".on_help("'VACUUM'");$If="<input type='submit' name='optimize' value='".'Optimize'."'> ".on_help(JUSH=="sql"?"'OPTIMIZE TABLE'":"'VACUUM ANALYZE'");$Ag=(JUSH=="sqlite"?$Vi."<input type='submit' name='check' value='".'Check'."'> ".on_help("'PRAGMA integrity_check'"):(JUSH=="pgsql"?$Vi.$If:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'> ".on_help("'ANALYZE TABLE'").$If."<input type='submit' name='check' value='".'Check'."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".'Repair'."'> ".on_help("'REPAIR TABLE'"):""))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".'Truncate'."'> ".on_help(JUSH=="sqlite"?"'DELETE'":"'TRUNCATE".(JUSH=="pgsql"?"'":" TABLE'")).confirm():"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".'Drop'."'>".on_help("'DROP TABLE'").confirm():"");echo($Ag?"<div class='footer'><div>\n<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>$Ag\n</div></fieldset>\n":"");$h=(support("scheme")?adminer()->schemas():adminer()->databases());$jh="";if(count($h)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".'Move to other database'." <span id='selected3'></span></legend><div>";$i=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($h?html_select("target",$h,$i):'<input name="target" value="'.h($i).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";$jh=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo"<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $T);":"")."$jh }"),input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links'><a href='".h(ME)."create='>".'Create table'."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":"");if(support("routine")){echo"<h3 id='routines'>".'Routines'."</h3>\n";$dh=routines();if($dh){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td></thead>\n";foreach($dh
as$K){$C=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$C).'">'.h($K["ROUTINE_NAME"]).'</a>','<td>'.h($K["ROUTINE_TYPE"]),'<td>'.h($K["DTD_IDENTIFIER"]),'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$C).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n";}if(support("event")){echo"<h3 id='events'>".'Events'."</h3>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?'At given time'."<td>".$K["Execute at"]:'Every'." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$vc=get_val("SELECT @@event_scheduler");if($vc&&$vc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($vc)."\n";}echo'<p class="links"><a href="'.h(ME).'event=">'.'Create event'."</a>\n";}}}}page_footer();