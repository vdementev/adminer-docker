<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 5.4.1
*/namespace
Adminer;const
VERSION="5.4.1";error_reporting(24575);set_error_handler(function($Cc,$Ec){return!!preg_match('~^Undefined (array key|offset|index)~',$Ec);},E_WARNING|E_NOTICE);$ad=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($ad||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$tj=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($tj)$$X=$tj;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Fb=adminer()->credentials();$J=Driver::connect($Fb[0],$Fb[1],$Fb[2]);return(is_object($J)?$J:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Ie=substr($u,-1);return
str_replace($Ie.$Ie,$Ie,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
escape_string($X){return
substr(q($X),1,-1);}function
idx($va,$x,$k=null){return($va&&array_key_exists($x,$va)?$va[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$ah,$ad=false){if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){while(list($x,$X)=each($ah)){foreach($X
as$Ae=>$W){unset($ah[$x][$Ae]);if(is_array($W)){$ah[$x][stripslashes($Ae)]=$W;$ah[]=&$ah[$x][stripslashes($Ae)];}else$ah[$x][stripslashes($Ae)]=($ad?$W:stripslashes($W));}}}}function
bracket_escape($u,$Ca=false){static$cj=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($u,($Ca?array_flip($cj):$cj));}function
min_version($Lj,$We="",$g=null){$g=connection($g);$Vh=$g->server_info;if($We&&preg_match('~([\d.]+)-MariaDB~',$Vh,$A)){$Vh=$A[1];$Lj=$We;}return$Lj&&version_compare($Vh,$Lj)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_bool($ke){$X=ini_get($ke);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($ke){$X=ini_get($ke);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Kj,$N,$V,$F){$_SESSION["pwds"][$Kj][$N][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
get_val($H,$m=0,$tb=null){$tb=connection($tb);$I=$tb->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return($K?$K[$m]:false);}function
get_vals($H,$d=0){$J=array();$I=connection()->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$d];}return$J;}function
get_key_vals($H,$g=null,$Yh=true){$g=connection($g);$J=array();$I=$g->query($H);if(is_object($I)){while($K=$I->fetch_row()){if($Yh)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$g=null,$l="<p class='error'>"){$tb=connection($g);$J=array();$I=$tb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.error()."\n";return$J;}function
unique_array($K,array$w){foreach($w
as$v){if(preg_match("~PRIMARY|UNIQUE~",$v["type"])){$J=array();foreach($v["columns"]as$x){if(!isset($K[$x]))continue
2;$J[$x]=$K[$x];}return$J;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$J=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$Xc=$m["type"];$J[]=$d.(JUSH=="sql"&&$Xc=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^json~',$Xc)?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Xc,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X))))));if(JUSH=="sql"&&preg_match('~char|text~',$Xc)&&preg_match("~[^ -@]~",$X))$J[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$J[]=escape_key($x)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,array$n=array()){parse_str($X,$Wa);remove_slashes(array(&$Wa));return
where($Wa,$n);}function
where_link($s,$d,$Y,$Xf="="){return"&where%5B$s%5D%5Bcol%5D=".urlencode($d)."&where%5B$s%5D%5Bop%5D=".urlencode(($Y!==null?$Xf:"IS NULL"))."&where%5B$s%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$e,array$n,array$M=array()){$J="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$wa=convert_field($n[$x]);if($wa)$J
.=", $wa AS ".idf_escape($x);}return$J;}function
cookie($B,$Y,$Pe=2592000){header("Set-Cookie: $B=".urlencode($Y).($Pe?"; expires=".gmdate("D, d M Y H:i:s",time()+$Pe)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_settings($Bb){parse_str($_COOKIE[$Bb],$Zh);return$Zh;}function
get_setting($x,$Bb="adminer_settings",$k=null){return
idx(get_settings($Bb),$x,$k);}function
save_settings(array$Zh,$Bb="adminer_settings"){$Y=http_build_query($Zh+get_settings($Bb));cookie($Bb,$Y);$_COOKIE[$Bb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==1))session_start();}function
stop_session($id=false){$Cj=ini_bool("session.use_cookies");if(!$Cj||$id){session_write_close();if($Cj&&@ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Kj,$N,$V,$j=null){$zj=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($Kj=='mssql'||$Kj=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$zj,$A);return"$A[1]?".(sid()?SID."&":"").($Kj!="server"||$N!=""?urlencode($Kj)."=".urlencode($N)."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($j!=""?"&db=".urlencode($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Se,$lf=null){if($lf!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Se!==null?$Se:$_SERVER["REQUEST_URI"]))][]=$lf;}if($Se!==null){if($Se=="")$Se=".";header("Location: $Se");exit;}}function
query_redirect($H,$Se,$lf,$jh=true,$Jc=true,$Sc=false,$Pi=""){if($Jc){$oi=microtime(true);$Sc=!connection()->query($H);$Pi=format_time($oi);}$ii=($H?adminer()->messageQuery($H,$Pi,$Sc):"");if($Sc){adminer()->error
.=error().$ii.script("messagesPrint();")."<br>";return
false;}if($jh)redirect($Se,$lf.$ii);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
connection()->query($H);}function
apply_queries($H,array$T,$Fc='Adminer\table'){foreach($T
as$R){if(!queries("$H ".$Fc($R)))return
false;}return
true;}function
queries_redirect($Se,$lf,$jh){$eh=implode("\n",Queries::$queries);$Pi=format_time(Queries::$start);return
query_redirect($eh,$Se,$lf,$jh,false,!$jh,$Pi);}function
format_time($oi){return
sprintf('%.3f s',max(0,microtime(true)-$oi));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($ug=""){return
substr(preg_replace("~(?<=[?&])($ug".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($x,$Rb=false,$Xb=""){$Zc=$_FILES[$x];if(!$Zc)return
null;foreach($Zc
as$x=>$X)$Zc[$x]=(array)$X;$J='';foreach($Zc["error"]as$x=>$l){if($l)return$l;$B=$Zc["name"][$x];$Xi=$Zc["tmp_name"][$x];$yb=file_get_contents($Rb&&preg_match('~\.gz$~',$B)?"compress.zlib://$Xi":$Xi);if($Rb){$oi=substr($yb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$oi))$yb=iconv("utf-16","utf-8",$yb);elseif($oi=="\xEF\xBB\xBF")$yb=substr($yb,3);}$J
.=$yb;if($Xb)$J
.=(preg_match("($Xb\\s*\$)",$yb)?"":$Xb)."\n\n";}return$J;}function
upload_error($l){$gf=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($gf?" ".sprintf('Maximum allowed file size is %sB.',$gf):""):'File does not exist.');}function
repeat_pattern($Gg,$y){return
str_repeat("$Gg{0,65535}",$y/65535)."$Gg{0,".($y%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Tc=false){$J=table_status($R,$Tc);return($J?reset($J):array("Name"=>$R));}function
column_foreign_keys($R){$J=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$J[$X][]=$p;}return$J;}function
fields_from_edit(){$J=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$B=bracket_escape($x,true);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>1,"auto_increment"=>($x==driver()->primary),);}return$J;}function
dump_headers($Qd,$wf=false){$J=adminer()->dumpHeaders($Qd,$wf);$qg=$_POST["output"];if($qg!="text")header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Qd).".$J".($qg!="file"&&preg_match('~^[0-9a-z]+$~',$qg)?".$qg":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$J;}function
dump_csv(array$K){foreach($K
as$x=>$X){if(preg_match('~["\n,;\t]|^0.|\.\d*0$~',$X)||$X==="")$K[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$o=@tempnam("","");if(!$o)return'';$J=dirname($o);unlink($o);}}return$J;}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;@chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$Lb){rewind($q);fwrite($q,$Lb);ftruncate($q,strlen($Lb));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$va){return
reset($va);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$J=stream_get_contents($q);if(!$J){$J=rand_string();file_write_unlock($q,$J);}else
file_unlock($q);return$J;}function
rand_string(){return
md5(uniqid(strval(mt_rand()),true));}function
select_value($X,$_,array$m,$Oi){if(is_array($X)){$J="";foreach($X
as$Ae=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($Ae):"")."<td>".select_value($W,$_,$m,$Oi);return"<table>$J</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=adminer()->editVal($X,$m);if($J!==null){if(!is_utf8($J))$J="\0";elseif($Oi!=""&&is_shortable($m))$J=shorten_utf8($J,max(0,+$Oi));else$J=h($J);}return
adminer()->selectVal($J,$_,$m,$X);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($tc){$xa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Gg="$xa+(\\.$xa+)*@($gc?\\.)+$gc";return
is_string($tc)&&preg_match("(^$Gg(,\\s*$Gg)*\$)i",$tc);}function
is_url($Q){$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($gc?\\.)+$gc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea|hstore~',$m["type"]);}function
host_port($N){return(preg_match('~^(\[(.+)]|([^:]+)):([^:]+)$~',$N,$A)?array($A[2].$A[3],$A[4]):array($N,''));}function
count_rows($R,array$Z,$ue,array$wd){$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($ue&&(JUSH=="sql"||count($wd)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$wd).")$H":"SELECT COUNT(*)".($ue?" FROM (SELECT 1$H GROUP BY ".implode(", ",$wd).") x":$H));}function
slow_query($H){$j=adminer()->database();$Qi=adminer()->queryTimeout();$di=driver()->slowQuery($H,$Qi);$g=null;if(!$di&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$De=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$De&token=".get_token()."'); }, 1000 * $Qi);");}}ob_flush();flush();$J=@get_key_vals(($di?:$H),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$hh=rand(1,1e6);return($hh^$_SESSION["token"]).":$hh";}function
verify_token(){list($Yi,$hh)=explode(":",$_POST["token"]);return($hh^$_SESSION["token"])==$Yi;}function
lzw_decompress($Ia){$cc=256;$Ja=8;$gb=array();$uh=0;$vh=0;for($s=0;$s<strlen($Ia);$s++){$uh=($uh<<8)+ord($Ia[$s]);$vh+=8;if($vh>=$Ja){$vh-=$Ja;$gb[]=$uh>>$vh;$uh&=(1<<$vh)-1;$cc++;if($cc>>$Ja)$Ja++;}}$bc=range("\0","\xFF");$J="";$Uj="";foreach($gb
as$s=>$fb){$sc=$bc[$fb];if(!isset($sc))$sc=$Uj.$Uj[0];$J
.=$sc;if($s)$bc[]=$Uj.$sc[0];$Uj=$sc;}return$J;}function
script($fi,$bj="\n"){return"<script".nonce().">$fi</script>$bj";}function
script_src($_j,$Ub=false){return"<script src='".h($_j)."'".nonce().($Ub?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($B,$Y=""){return"<input type='hidden' name='".h($B)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Za,$Fe="",$Wf="",$db="",$He=""){$J="<input type='checkbox' name='$B' value='".h($Y)."'".($Za?" checked":"").($He?" aria-labelledby='$He'":"").">".($Wf?script("qsl('input').onclick = function () { $Wf };",""):"");return($Fe!=""||$db?"<label".($db?" class='$db'":"").">$J".h($Fe)."</label>":$J);}function
optionlist($bg,$Nh=null,$Dj=false){$J="";foreach($bg
as$Ae=>$W){$cg=array($Ae=>$W);if(is_array($W)){$J
.='<optgroup label="'.h($Ae).'">';$cg=$W;}foreach($cg
as$x=>$X)$J
.='<option'.($Dj||is_string($x)?' value="'.h($x).'"':'').($Nh!==null&&($Dj||is_string($x)?(string)$x:$X)===$Nh?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($B,array$bg,$Y="",$Vf="",$He=""){static$Fe=0;$Ge="";if(!$He&&substr($bg[""],0,1)=="("){$Fe++;$He="label-$Fe";$Ge="<option value='' id='$He'>".h($bg[""]);unset($bg[""]);}return"<select name='".h($B)."'".($He?" aria-labelledby='$He'":"").">".$Ge.optionlist($bg,$Y)."</select>".($Vf?script("qsl('select').onchange = function () { $Vf };",""):"");}function
html_radios($B,array$bg,$Y="",$Rh=""){$J="";foreach($bg
as$x=>$X)$J
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$Rh";return$J;}function
confirm($lf="",$Oh="qsl('input')"){return
script("$Oh.onclick = () => confirm('".($lf?js_escape($lf):'Are you sure?')."');","");}function
print_fieldset($t,$Ne,$Oj=false){echo"<fieldset><legend>","<a href='#fieldset-$t'>$Ne</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$t');",""),"</legend>","<div id='fieldset-$t'".($Oj?"":" class='hidden'").">\n";}function
bold($La,$db=""){return($La?" class='active $db'":($db?" class='$db'":""));}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
pagination($D,$Ib){return" ".($D==$Ib?$D+1:'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
hidden_fields(array$ah,array$Ud=array(),$Sg=''){$J=false;foreach($ah
as$x=>$X){if(!in_array($x,$Ud)){if(is_array($X))hidden_fields($X,array(),$x);else{$J=true;echo
input_hidden(($Sg?$Sg."[$x]":$x),$X);}}}return$J;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),(SERVER!==null?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
file_input($me){$bf="max_file_uploads";$cf=ini_get($bf);$xj="upload_max_filesize";$yj=ini_get($xj);return(ini_bool("file_uploads")?$me.script("qsl('input[type=\"file\"]').onchange = partialArg(fileChange, "."$cf, '".sprintf('Increase %s.',"$bf = $cf")."', ".ini_bytes("upload_max_filesize").", '".sprintf('Increase %s.',"$xj = $yj")."')"):'File uploads are disabled.');}function
enum_input($U,$ya,array$m,$Y,$wc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Ze);$Sg=($m["type"]=="enum"?"val-":"");$Za=(is_array($Y)?in_array("null",$Y):$Y===null);$J=($m["null"]&&$Sg?"<label><input type='$U'$ya value='null'".($Za?" checked":"")."><i>$wc</i></label>":"");foreach($Ze[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$Za=(is_array($Y)?in_array($Sg.$X,$Y):$Y===$X);$J
.=" <label><input type='$U'$ya value='".h($Sg.$X)."'".($Za?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$J;}function
input(array$m,$Y,$r,$Ba=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r){$Y=json_encode($Y,128|64|256);$r="json";}$th=(JUSH=="mssql"&&$m["auto_increment"]);if($th&&!$_POST["save"])$r=null;$rd=(isset($_GET["select"])||$th?array("orig"=>'original'):array())+adminer()->editFunctions($m);$Bc=driver()->enumLength($m);if($Bc){$m["type"]="enum";$m["length"]=$Bc;}$dc=stripos($m["default"],"GENERATED ALWAYS AS ")===0?" disabled=''":"";$ya=" name='fields[$B]".($m["type"]=="enum"||$m["type"]=="set"?"[]":"")."'$dc".($Ba?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($rd[""])."<td>".adminer()->editInput($R,$m,$ya,$Y);else{$Dd=(in_array($r,$rd)||isset($rd[$r]));echo(count($rd)>1?"<select name='function[$B]'$dc>".optionlist($rd,$r===null||$Dd?$r:"")."</select>".on_help("event.target.value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($rd))).'<td>';$me=adminer()->editInput($R,$m,$ya,$Y);if($me!="")echo$me;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$ya value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$ya value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$ya,$m,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($r=="json"||preg_match('~^jsonb?$~',$m["type"]))echo"<textarea$ya cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';elseif(($Mi=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($Mi&&JUSH!="sqlite")$ya
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$ya
.=" cols='30' rows='$L'";}echo"<textarea$ya>".h($Y).'</textarea>';}else{$nj=driver()->types();$if=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A)?((preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0)):($nj[$m["type"]]?$nj[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$if+=7;echo"<input".((!$Dd||$r==="")&&preg_match('~(?<!o)int(?!er)~',$m["type"])&&!preg_match('~\[\]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($if?" data-maxlength='$if'":"").(preg_match('~char|binary~',$m["type"])&&$if>20?" size='".($if>99?60:40)."'":"")."$ya>";}echo
adminer()->editHint($R,$m,$Y);$bd=0;foreach($rd
as$x=>$X){if($x===""||!$X)break;$bd++;}if($bd&&count($rd)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $bd);");}}function
process_input(array$m){if(stripos($m["default"],"GENERATED ALWAYS AS ")===0)return;$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);$Y=idx($_POST["fields"],$u);if($m["type"]=="enum"||driver()->enumLength($m)){$Y=$Y[0];if($Y=="orig")return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($m["auto_increment"]&&$Y=="")return
null;if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$r="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(is_blob($m)&&ini_bool("file_uploads")){$Zc=get_file("fields-$u");if(!is_string($Zc))return
false;return
driver()->quoteBinary($Zc);}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Qh="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=adminer()->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Wg="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$Qh<li>".($I?$Wg:"<p class='error'>$Wg: ".error())."\n";$Qh="";}}}echo($Qh?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($mb,$bi=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $mb, $bi) }, onmouseout: helpMouseout});","");}function
edit_form($R,array$n,$K,$wj,$l=''){$_i=adminer()->tableName(table_status1($R,true));page_header(($wj?'Edit':'Insert'),$l,array("select"=>array($R,$_i)),$_i);adminer()->editRowPrint($R,$n,$K,$wj);if($K===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout'>".script("qsl('table').onkeydown = editingKeydown;");$Ba=!$_POST;foreach($n
as$B=>$m){echo"<tr><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$qh))$k=$qh[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($K!==null?($K[$B]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($K[$B])?implode(",",$K[$B]):(is_bool($K[$B])?+$K[$B]:$K[$B])):(!$wj&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);$r=($_POST["save"]?idx($_POST["function"],$B,""):($wj&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$wj&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ba!==false)$Ba=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ba);if($Ba)$Ba=false;echo"\n";}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($n){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($wj?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($wj?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."…', this); };"):"");}echo($wj?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$ui=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$ui.(isset($A[2])?"":"<i>…</i>");}function
icon($Pd,$B,$Od,$Si){return"<button type='submit' name='$B' title='".h($Si)."' class='icon icon-$Pd'><span>$Od</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}@ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M��h��g�б���\"P�i��m��cQCa��	2ó��d<��f�a��:;NB�q�R;1Lf�9��u7&)�l;3�����J/��CQX�r2M�a�i0���)��e:LuÝh�-9��23l��i7��m�Zw4���њ<-��̴�!�U,��Fé�vt2��S,��a�҇F�VX�a�Nq�)�-���ǜh�:n5���9�Y�;j��-��_�9kr��ٓ;.�tTq�o�0�����{��y��\r�Hn��GS��Zh��;�i^�ux�WΒC@����k��=��b����/A��0�+�(���l���\\��x�:\r��b8\0�0!\0F�\nB�͎�(�3�\r\\�����Ȅa���'I�|�(i�\n�\r���4O�g@�4�C��@@�!�QB��	°�c��¯�q,\r1Eh��&2PZ���iG�H9G�\"v���������4r����D�R�\n�pJ�-A�|/.�c�Du�����:,��=��R�]U5�mV�k�LLQ@-\\����@9��%�S�r���MPD��Ia\r�(YY\\�@X�p��:��p�l�LC �������O,\r�2]7�?m06�p�T��aҥC�;_˗�yȴd�>��bn���n�ܣ3�X���8\r�[ˀ-)�i>V[Y�y&L3�#�X|�	�X�\\ù`�C���#��H��2�2.#���Z�`�<��s����Ò��\0u�h־��M��_\niZeO/CӒ_�`3���1>�=��k3����R/;�/d��\0�����ڵm���7/���A�X�������q.�s�L��� :\$�F�������w�8�߾~�H�j��\"�����Գ7gS���FL�ί�Q�_��O'W��]c=�5�1X~7;��i��\r�*\n��JS1Z���������c���t��A�V�86f�d�y;Y�]��zI�p�����c�3�Y�]}@�\$.+�1�'>Z�cpd���GL��#k�8Pz�Y�Au�v�]s9���_Aq���:���\nK�hB�;���XbAHq,��CI�`����j�S[ˌ�1�V�r���;�p�B��)#鐉;4�H��/*�<�3L��;lf�\n�s\$K`�}��Ք���7�jx`d�%j]��4��Y��HbY��J`�GG��.��K��f�I�)2�Mfָ�X�RC��̱V,���~g\0���g6�:�[j�1H�:AlIq�u3\"���q��|8<9s'�Q]J�|�\0�`p���jf�O�b�����q��\$����1J�>R�H(ǔq\n#r����@�e(y�VJ�0�Q҈��6�P�[C:�G伞���4���^����PZ��\\���(\n��)�~���9R%�Sj�{��7�0�_��s	z|8�H�	\"@�#9DVL�\$H5�WJ@��z�a�J �^	�)�2\nQv��]�������j (A���BB05�6�b˰][��k�A�wvkg�ƴ���+k[jm�zc�}�MyDZi�\$5e��ʷ���	�A��CY%.W�b*뮼�.���q/%}B�X���ZV337�ʻa�������wW[�L�Q�޲�_��2`�1I�i,�曣�Mf&(s-����Aİ�*��Dw��TN�ɻ�jX\$�x�+;���F�93�JkS;���qR{>l�;B1A�I�b)��(6��r�\r�\rڇ����Z�R^SOy/��M#��9{k���v\"�KC�J��rEo\0��\\,�|�fa͚��hI��/o�4�k^p�1H�^����phǡV�vox@�`�g�&�(����;��~Ǎz�6�8�*���5����E���p����Ә���3��ņg��rD�L�)4g{���峩�L��&�>脻����Z�7�\0��̊@�����ff�RVh֝��I�ۈ���r�w)����=x^�,k��2��ݓj�b�l0u�\"�fp��1�RI��z[]�w�pN6dI�z���n.7X{;��3��-I	����7pjÝ�R�#�,�_-���[�>3�\\���Wq�q�J֘�uh���FbL�K���yVľ����ѕ�����V���f{K}S��ޝ��M���̀��.M�\\�ix�b���1�+�α?<�3�~H��\$�\\�2�\$� e�6t�Ö�\$s���x��x���C�nSkV��=z6����'æ�Na��ָh��������R�噣8g�����w:_�����ҒIRKÝ�.�nkVU+dwj��%�`#,{�醳����Y����(oվ��.�c�0g�DXOk�7��K��l��hx;�؏ ݃L��\$09*�9 �hNr�M�.>\0�rP9�\$�g	\0\$\\F�*�d'��L�:�b���4�2����9��@�Hnb�-��E #Ĝ����rPY�� t� �\n�5.�����\$op�l�X\n@`\r��	��\r���� � ���	������ �	@�@�\n � �	\0j@�Q@�1\r��@� �	\$p	 V\0�``\n\0�\n �\n@�'����\n\0`\r����	��\r���\0�r����	\0�`�	���{	,�\"��^P�0�\n��4�\n0���.0�p���\rp�\r��p���p��q�Q0�%���1Q8\n �\0�k�ȼ\0^���\0`��@���>\n�o1w�,Y	h*=����P�:іV��и.q����\r�\r�p���1��Q	��1� �`��/17����\r�^��\"y`�\n�� �#��\0�	 p\n��\n��`� �r �Q��b�1��3\n��#��#�1�\$q�\$ѱ%0�%q�%��&�&q� �&�'1�\rR}16	 �@b\r`�`�\r��	�����d���	j\n�``��\n��`dcсP��,�1R��\$�rI�O �	Q	�Y32b1�&��01��� �� f��\0�\0���f�\0j\n�f`�	 �\n`�@�\$n=`�\0��v nI�\$�P(�d'�����g�6��-��-�C7R��� �	4��-1�&��2t\r�\"\n 	H*@�	�`\n � �	��l�2�,z\r�~� �\r�F�th�������m����z�~�\0]G�F\\��I�\\��}It�C\n�T�}���IEJ\rx����>�Mp��IH�~��fht��.b��xYE��iK��oj�\n���L��tr�.�~d�H�2U4�G�\\A��4��uPt����谐����L/�P�	\"G!R��Mt�O-��<#�APuI��R�\$�c���D�Ɗ����-��G�O`Pv�^W@tH;Q��Rę�\$��gK�F<\rR*\$4���'�����[��I��Um��h:+��5@/�l�I���2���^�\0OD�����\rR'�\r�TЭ[����Ī��MC�M�Z4�E B\"�`���euN�,䙬�]��t�\r�`�@h��*\r�.V��%�!MBlPF��\"��&�/@�v\\C��:mMgn����i8�I2\rp�vj����+Z mT�ue��fv>f�И�`DU[ZT�V�C�T�\r��Uv�k�^���L��b/�K�Sev2�ubv�OVD��Im�\$�%�X?ud�!W�|,\r�+�cnUe�Z��ʖ����-~X��������BGd�\$i��Mv!t#L�3o�UI�O�u?ZweR���cw�.�`ȡi��\rb�%�b���H�\"\"\"h�_\$b@�z��\0f\"��rW��*��B|\$\$�B�נ\"@r��(\r`� �C���(0&�.`�Nk9B\n&#(���@䂯��d��^����� �@�`�I-{�0��\n�B�{�4sG{��;z��b�{ �{b�ׯ�){B��xK���Ň5=cڪ��y��&�J�Pr�I/��� \0��V\r�׉��=����N\\ئ=�K��}XV�x�����إ�ˋx��d�Պی*H'�δ��{X�=��=\0�8�\0����[ɫ�J��t��O�e����ɋ��\r�����DX���Ň��}�z������)�y'��'��я�I��(�[�l(5�`f\\�`���e�.lY(�=z�ה!�Y%h��O�+����`ٙ\"e� ��ė���K������������ߚ�#�S��E�I�Y����.H�JtG���`��H�J5���5��~ ��6C��h����XDz\n�x��ysh���FK�c�zj�Z�Y8(��%�|y�I��ߑ؃���e��Y�X���u�� ��i�]��c���M��;�ȧ���>ǡ��Q�T����� [~W�~��c݂z�����z�����\r�:  \0�rY��x)��!��ɡ�K��+�z!��ӀC+����ٮ�ï:ݎ�������Zg��~z4f��	�:����s�Ӫ��+��x�%����=��G��I�f3?������+Y��q�@��G���y��o��Ѵ�p\r�~�{W���[����y�:\0�\\���;e�ۡ�YI\"��zdk�Z�|[u��u��+�׹9q��nR ˮ�B����ׁz|\r�ᤄ��k�^��[1��%�.��pA�2<��=�ء��\$�;�5�)��m��!���XX���Y�x�5vT\\�Q�%:��>��ɛ�;��e�|/���y����W��xנ|g������C��\\�����<��9z\\�#�.FV;8��N�X7����\"8&d5�P�4Gj?�\0�?\"=���HER");}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:M��h��g���h0�LЁ�d91�S!��	�F�!��\"-6N����bd�Gg���:;Nr�)��c7�\r�(H�b81��s9���k\r�c)�m8�O��VA��c1��c34Of*��-�P��1��r41��6��d2�ց���o���#3���B�f#	��g9Φ�،fc\r�I���b6E�C&��,�bu��m7a�V���s��#m!��h��r���v\\3\rL:SA��dk5�n������aF��3��e6fS��y���r!�L��-�K,�3L�@��J��˲�*J��쵣����	������b�c��9���9���@����H�8��\\���6>�`�Ŏ��;�A��<T�'�p&q�qE��4�\rl���h�<5#p��R �#I��%��fBI��ܲ��>�ʫ29<��C�j2��7j��8j��c(n���?(a\0�@�5*3:δ�6����0��-�A�lL��P�4@�ɰ�\$�H�4�n31��1�t�0��͙9���WO!�r��������H����9�Q��96�F���<�7�\r�-xC\n ��@�������:\$i�ضm���4�Kid��{\n6\r���xhˋ�#^'4V�@a��<�#h0�S�-�c��9�+p���a�2�cy�h�BO\$��9�w�iX�ɔ�VY9�*r�Htm	�@b��|@�/��l�\$z���+�%p2l���.�������7�;�&{��m��X�C<l9��6x9�m�������7R��0\\�4��P�)A�o��x���q�O#����f[;��6~P�\r�a��T�GT0���u�ޟ���\n3�\\ \\ʎ�J�ud�CG���PZ�>����d8�Ҩ������C?V��dL��L.(ti���>�,�֜�R+9i��ޞC\$��#\"�AC�hV�b\n��6�T2�ew�\nf��6m	!1'c��;��*eLRn\r�G\$�2S\$��0���a�'�l6�&�~A�d\$�J�\$s� �ȃB4���j�.�RC̔�Q�j�\"7\n�Xs!�6=�BȀ}");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("':�̢���i1��1��	4������Q6a&��:OAI��e:NF�D|�!���Cy��m2��\"���r<�̱���/C�#����:DbqSe�J�˦Cܺ\n\n��ǱS\rZ��H\$RAܞS+XKvtd�g:��6��EvXŞ�j��mҩej�2�M�����B��&ʮ�L�C�3���Q0�L��-x�\n��D���yNa�Pn:�����s��͐�(�cL��/���(�5{���Qy4��g-�����i4ڃf��(��bU���k��o7�&�ä�*ACb����`.����\r����������\n��Ch�<\r)`�إ`�7�Cʒ���Z���X�<�Q�1X���@�0dp9EQ�f����F�\r��!���(h��)��\np'#Č��H�(i*�r��&<#��7K��~�# ��A:N6�����l�,�\r��JP�3�!@�2>Cr���h�N��]�(a0M3�2��6��U��E2'!<��#3R�<�����X���CH�7�#n�+��a\$!��2��P�0�.�wd�r:Y����E��!]�<��j��@�\\�pl�_\r�Z���ғ�TͩZ�s�3\"�~9���j��P�)Q�YbݕD�Yc��`��z�c��Ѩ��'�#t�BOh�*2��<ŒO�fg-Z����#��8a�^��+r2b��\\��~0�������W����n��p!#�`��Z��6�1�2��@�ky��9\r��B3�pޅ�6��<�!p�G�9�n�o�6s��#F�3���bA��6�9���Z�#��6��%?�s��\"��|؂�)�b�Jc\r����N�s��ih8����ݟ�:�;��H�ތ�u�I5�@�1��A�PaH^\$H�v��@ÛL~���b9�'�����S?P�-���0�C�\nR�m�4���ȓ:���Ը�2��4��h(k\njI��6\"�EY�#��W�r�\r��G8�@t���Xԓ��BS\nc0�k�C I\rʰ<u`A!�)��2��C�\0=��� ���P�1�ӢK!�!��p�Is�,6�d���i1+����k���<��^�	�\n��20�Fԉ_\$�)f\0��C8E^��/3W!א)�u�*���&\$�2�Y\n�]��Ek�DV�\$�J���xTse!�RY� R��`=L���ޫ\nl_.!�V!�\r\nH�k��\$א`{1	|�����i<jRrPTG|��w�4b�\r���4d�,�E��6���<�h[N�q@Oi�>'ѩ\r����;�]#��}�0�ASI�Jd�A/Q����⸵�@t\r�UG��_G�<��<y-I�z򄤝�\"�P��B\0������q`��vA��a̡J�R�ʮ)��JB.�T��L��y����Cpp�\0(7�cYY�a��M��1�em4�c��r��S)o����p�C!I���Sb�0m��(d�EH����߳�X���/���P���y�X��85��\$+�֖���gd�����y��ϝ�J��� �lE��ur�,dCX�}e������m�]��2�̽�(-z����Z��;I��\\�) ,�\n�>�)����\rVS\njx*w`ⴷSFi��d��,���Z�JFM}Њ ��\\Z�P��`�z�Z�E]�d��ɟO�cmԁ]� ������%�\"w4��\n\$��zV�SQD�:�6���G�wM��S0B�-s��)�Z�c|�^R��E�8kM���s�d�ka�)h%\"P��0nn��/��#;��g\rd��8��F<3\$�,�P);<4`��<2\n����@w-��͗A�0�����Lr�Yh�XC�a�>��t��L��2�yto;2��Q��t��frm�:��A�����AN��\\\"k�5oV�Ƀ=��t�7r1�p�Av\\+�9���{��^(i��f�=�r����u���t�]y�ޅ��C���������gi�vf���+�Ø|��;�����]�~��|\re��쿓�݂�'�����������	�\0+W��co�w6wd Su�j�3@���0!��\n .w�m[8x<��cM�\n9���'a���1>���[���d��ux��<\"Y�c��B!i���w�}��5U�k�����]������{�IךR����=f W~�]�(bea�'ub�m�>�)\$��P��-��6��R*IGu#ƕUK�AX�t�(�`_��\"���p� &U���I��]��YG6P�]Ar!b� *ЙJ�o��ӯ�������v��*���!�~_���4B���_~RB�iK����`�&J�\0���N\0�\$�����C�K �S���jZ�����0pvMJ�bN`L��e�/`RO.0P�82`�	����d Gx�bP�-(@ɸ�@�4�H%<&���Z����p����%\0�p��Є���	��	��/\"��J��\ns��_��\r��g�`��!k�pX	��:�v��6p\$�'���RUeZ��d\$�\nL�B���.�d�n����tm�>v�j��)�	M�\r\0�.�ʊH��\"�5�*!e�ZJ�����f(dc��(x��jg\0\\������ Z@���|`^��r)<�(������)������@Yk�m��l3Qyс@���ѐf��Pn�����T��N�mR�q���Vmv�N֍�|�ШZ��Ȇ�(Yp��\"�4Ǩ���&��%�l�P`Ā�Xx bbd�r0Fr5�<�C��z���6�he!��\rdz���K;�t��\n�͠�HƋQ�\$Q�Enn�n\r���#�T\$��ˈ(ȟѩ|c�,�-�#��\r���J�{d�E\n\$��Br�iT��+�2PED�Be�}&%Rf��\n��^�C��Z�Z RV��A,�;���<���\0O1���c^\r%�\r ��`�n\0y1��.��\r�ĂK1�M3H�\r\"�0\0NkX�Pr��{3 �}	\nS�d��ڗ�x.Z�RT�wS;53 .�s4sO3F��2�S~YFpZs�'�@ّOqR4\n�6q6@Dh�6��7vE�l\"�^;-�(�&�b*�*��.! �\r�!#�x'G\"�͆w��\"�� �2!\"R(v�X��|\"D�v��)@�,�zm�A�wT@��  �\n����ЫhдID�P\$m>�\r&`�>�4��A#*�#�<�w\$T{\$�4@��dӴRem6�-#Dd�%E�DT\\�\$)@��WC�(t�\"M��#@�TF�\r,g�\rP8�~��֣J��c����ĹƂ� ʎ\"�L�Z��\r+P4�=���S�T�A)�0\"�CDh�M\n�%F�p���|�fLNlFtDmH����5�=H�\n��ļ4���\$�K�6\rbZ�\r\"pEQ%�wJ��V0��M%�l\"h�PF�A��A㌮�/G�6�h6]5�\$�f�S�CLiRT?R���C����HU�Z��YbF�/�.�Z�\"\"^�y�6R�G ���n��܌�\$���\\&O�(v^ �KU�Ѯ��am�(\r������\$_��%�+KTt��.ٖ36\n�c��:�@6 �jP�AQ�F�/S�k\"<4A�gA�aU�\$'����f��QO\"�k~�S;����.��:��k��9�����e]`n���-7��;��+V��8W��2H�U��YlB�v��⯎�Ԇ����	����p���l�m\0�4B�)�X�\0��Q�qFSq�4��nFx+p��E�Sov�GW7o�w�KRW�\r4`|cq�e7,�19�u��u�cq�\"LC�t�h�)�\r��J�\\�W@�	�|D#S\r�%�5l�!%+�+�^�k^ʙ`/�7��(z*񘋀���E��{�S(W��-�Xė0V��0�����=��a	~�fB�˕2Q���ru mC�����t�r(\0Q!K;xN�W������?b<�@�`�X,��`0e�ƂN'�����&~��t��u�\"| �i� �B� 7�R�� ��lSu��8A��dF%(�������?3@A-oQ�ź@|~�K���^@x��b��~�D�@س�����TN�Z�C�	W���ix<\0P|��\n\0�\n`�����\"&?st|ï�w�%����md�u�N�^8�[t�9��B\$�������'\">U�~�98����ÔF�f ���u����/)9����\0��A�z\"FWAx�\$'�jG�(\"� �s%T��H����e,	M�7�b� ǅ�a� ˓�ƃ�&wY�φ3���� /�\rϖ�����{�\"�ݜp{%4b��`팤��~n��E3	������9��3X�d���ՏZ��9�'��@����l�f����Q�bP�*G�o���`8������A��B|�z	@�	��b�Zn_�h�'ѢF\$f���`��HdDd�H%4\rs�AjLR�'��f�9g I��,R\\����>\n��H[�\"���\rӁ����L�,%�FLl8gzL�<0k�o\$�k��`��KP�v�@d�'V�:V��M�%���@�6�<\r��T���LE��NԀS#�.�[�x4�a�̭�LL����\n@��\0۫tٲ�\n^F�������5`� R��7�lL�u�(��d���� �\r�Bf/uCf�4�cҞ B���_�nL�\0� \$��aYƦ���~�Uk�v�e�˥�˲\0�Z�aZ����Xأ��|C�q��/<}س���ú���� Z��*�w\nO��z`�5��18�c����������I�Q2Ys�K�����\n�\\��\"�� ð�c��*�B����.�R1<3+���*�S�[�4�m쭛:R�h��ITdev�I�H���-Zw\\�%n�56�\n�W�i�\$�ōow��+�����r��&Jq+�}�D����j��d��?�U%BBe�/M��Nm=τ�U��b\$HRf�wb|��x d�2�NiS���g�@�q@��>�Sv�������|�kr�x��\0{�R�=F������#r��8	��Z�v�8*ʳ�{2S�+;S���Ө�+yL\$\"_��B�8��\"E�%������\n����p�p''�p��wUҪ\"8бI\\ @���ʾ �Ln���R�#M�D��q�LN��\n\\��̎\$`~@`\0u�~^@��l�-{5�,@bru�o[�����}�/�y.�� {�6q��R�p��\$�+1�3����+��O!D)����\nu�<��,����=�Jd�+}��d#�0ɞc��3U3�EY���\r��tj5ҥ7�e��wׄǡ���^��q߂�9�<\$}k���RI-���+'_Ne?S�R�hd*X�4��c}��\"@��vi>;5>Dn� �\r��)bN�uP@Y�G<��6i�#PB2A�-�0d0+���gK����?�n���d�d�O������c�i<����0\0�\\����g����ꡖ��NTi'����;i�mj�܈�����u�J+�V~����'ol`����\",�������F��	��{C�����T a�NEۃQ�p� p��+?�\n�>�'l��* t�Kάp�(YC\n-q̔0�\"*ɕ�,#���7��\"%�+q���B��=�i.@�x7:�%GcYI��0*��Ðk�ۈ�\\����Q_{����#��\r�{H�[p� >7�ch�n����.����S|&J�MǾ8��m�Oh���	��qJ&�a�ݢ�'�.b�Op��\$�����D@�C�HB�	��&�ݡ|\$Ԭ-6��+�+ �����p��ଡAC\r�ɓ��/�0�����M��iZ�nE�͢j*>��!Ңu%��g�0���@��5}r��+3�%��-m��G�<���T;0�����DV�d�g�9'lM��H�� F@�P��un�tFB%�M�t'�G�2��@2�<�e��;�`��=LX�2���X�}oc.L�+�xӎ�&D�a����ɫ�F2\ngL�E��.\\xSL�x�;lw�D=0_QV,a 5�+L��+�|\$�i�jZ\n��D�E�,B�t\\�'H0����R~(\\\"��:��n*���(��o�1w��Q��r���E�te�F��\$�Sђ]�\rL�yF���\\B�i�h��hd��&ᚇh;fo��B-y`���0��J�lP�xao�\$�Xq�,(���C*	��:�/����HG\"��c��C���Q�\nF�Ԅ�#�8�F:У\0��Ok��D��])�ϚtT8L�𒨔�n�`���|�HJ���� �� \"�6�{����?=I<HGc ŤF�@�,C ��@j�\$L���(�nEʑP��jb�n�Α���W� \r�Lq����sPH�ꉝz\\V\$k�ҏtr5�,��l����<�'\0^S02�0f -5\"ac�\"3U�p��\"ܘ�%��\0'Zt\"96��9_ @Z{�0I��D�ZE@��N�h`�\"�`�\0�����ɹ(G�H��Ch� �I��f`@ZD�\$)�K�;Z��\0�/�C�T>r_R@O�`1r�TҨIb\0�*�8�����h\$�_�p�Rĕ\$��Ni^ʪP/O)��.ŹT6�\\�ٔ@T���rą`)���T=�n\0��2��e�+�9ʢ\\��@�����>�PH�1	�y#���r�<�a�e�K��/�c�M@_.\09ˈ��������B����0i���a�\n��de�a�%|S2�����#����n��D�\$/�+E�d����_2P��\$s,ok�#�<�	�A�đr{B���A-Q4Ҥ�\n�\ry�!�b䱎���O��@ɬ��k�� �\"�r��*�݇��Y��/��ȑ a0��%�.gE~��&� 89����#@M_ ���7K䃸J`�X)�B\$�(	:�g��n*�|�M6PZ��Ht�Jtq�Cx�[ڼ����l=\n���U3�f\\̔J�P	,�:�}TA�SYH(�\n���I�ٲ�!t(2U\"�\\�X�^s�	��a!�\nPr��`�X3fnb�����J���&�z�zQSf ���t�!T?�9%�(Q��B�}6B�kP\0�>�g�&~fhU�r��,� p5Hi��p����qɚ�g�V�V��Og�WEJ8�0G��ak���@N NM��U�UxȪ��S�x	��	�K�@c�1y�VlϠ��C����2Q^rP6|�I^M�,�j%d�`ܫ��F��\\#%�|�C����7싢�G�TN�����i��H���Q�O���C�yB��\$�%T���*�>z\r�MM Kp� ��J7O۷�4�%�\$�p����4������͂��EҪ\"T��\0O�\0��@>	r�O�]���x�}^�I��@� źqn��0�Bb�ȵ�I�(�M/�;���}RN\n�C�<�b�PԵu?�=Pe�C����L^'�S��?}4)��S-���1\r5S�OE�SF����AOR+�ޙ+v��5�&C)ِ��KSDB߳N|E\rc�U�Yʾ���V���?H�)実+sF��k�LPW-�,�U:�&��t{��Vo���J�l'��W�e74X�n GF�'���`��Cc��%Il�j�u6����v�U��Z�\0*���Nԟ#��(���n�-;|��4�]X���y'����;��Z���) s9����%��R+\$��	��Q��(\"�_kX��������\nM#���\"!p~:�*����\$�3O������6�+���\nB�{1��|H�K<[`3��#��F@��ǐ! |�؊\0��>�����[nrMM�+��mO_�2��Ȇ�\0�e^	�7Z�&�B�J褓h7QO%rf�p��΁�֞�m�ب�Ç�4E�l���+���V��i�N S�Z�Wt�2W�[;��v\"%��\$^�-(I\$��S@R-&�T�z��k(��	�%R8�uY\0[9-���(�)E��8�=^����G�5#����)�1V��b\r]�Ne;&�Y�`r��I��Pݱ���ֲ��\0�@P�7���0H���؍R�x�\0000C|�n=��`��TT��\rEhON���'��&�tc�K ��ܕU5��������P3\\��2\"\0y�5�V]���6>�U!��@�hu��(�\"E%07B��6��d�HN������ij';@��e�MzlSfjKY�֍���-uh��H���smL@��\"r�j���j'l7	�(u�u��E��e�a�@�+�K�:ӕ�%n�z�V���;�[�_Vz_��E���8�<�Sb�������6g��:c����7\n����%Q�� K�7�ܮB����w�u�5��0��֚���y�ncnK����T8�ʙ�s��W=+�=K\n_[p�G���C5����'�D\"��M<\":|Mq4���f�s�x	�qlͰ��QP��aOY�E=���6nT떒�Bt�h�C\0p��@n��D(a�P�\"���'ZN��۬��\r�LNX�g��<!w�����[��B)��)~���c�x��v�i¦�q�����a�@K��7s�EQdý��k����?\"�3�-\"U��|������|21D>߳�]­&���\\h�TƳ5�\0`Tz���s -�N����\"�f��N�LU�]n(D�(��&%\"�e\\��O��N�Inۿ��\0����ƕ���@����V�|R�MYC�T����b�UH�p)���S�s� q�i���`Z5vt坉�*�OO\n�(�����F��58�!ax@�{^P����?���eh}\\�j^2�L�,6�.�N	K�%����u���ip��!?�l��� -5�w���K\"V��\\�Is��2!��\$4�5v\n�����gr��N��}��;��������W%D(pWa�\0�v'��6��V��ƿ0W��E4�EUl�8�LD��E�<kO��H��DU�	`vS��L��!DTMbnWV��Cd��)Ze蟀���:�2�d8��K�ބ�4�-G�b;wQW�30\r�f\0�,�`Qhl�֍�0�P��0h@\\�r�8��T���⛜�1�`�&���w�X�>�F?��|P�*�M�qZѯ��}��0k`��#�իc�'[�ֱˍ|s�IJ��\r����<OaƼ@�W��u�T��:��E^������!k�����a\$�>5��u_��KcCQ�r-ъ�'\r�iC������@8�S�PS�_Xgl�%�	�n1r.<�w_aɺĳ�Gh�4\n�W�Z��aBn,\\\0���DU�\nbbZ'���72���r�¢��}�Y>/�w\\Y�`^7J�j�S�������S.��o%�Jg\0GD,���>7���R�0������3��6�%i\0S�^L��A��\ri��O<���a phv[�{���\0�E�^x�ܼg�YzW�yG�a��:(�>C�����e\0���])�3yts_a�7�+��B��C�eT��f�o�P����2E�C��v�>�w�l�z�*p�Y����q�����Q�p\nv[|q�ҨE[�Xi���=�z(	�M�n�]7F\r��Cs4|-} ���Ŀ(NU�?,��څ��������q	��p�q~��� ��F��%�88��靦��\$�ް�[���r�o!3��(����g���ץpJ!���q�Z�v?��c���L��7��6��\$�m���q��8l!��5�C�;Q,��d�sF�-O��fÈ�\$���6�%U�C��f\"��e(j�\rMt�F����R�x;n�B\$��SS�x'��G��陊M�	��4ͬ'k��~��#9e��Y���~��뭈;f�+�j�K�9p���M�'X�/rt�\0�\\�J%Q���R�\rвO3�|�寚���ϱ�4��xF���s5E�Ԑ;ԒWR��JX�ʶ�J�\$��wzO��&ǵ��z�k�S�\n�\nNUP���.��0���bdk��P���	G6�+B�z�1ΎhQ>sHv�����Q�٠E�p��M��)��\n�\\�ў�Pz���.s��� g��)a~��ȥ�!(!�G�hr[�*�����բ�`��~�\"!�O���5�G3Ş*qkgB�,\$���**1�c.�n	8��\$d���VSne�MiZ���7žg�A�5�����\n�`�,�2��a�ү��mMkʻ��ɯ��/-��6�@?#`��)�Ԁ�ha���)Vc�]�_=�Rz\\�VR��=�ط�(-�ot�\$ܥ�\n���dSm�y��fө�N\r�m(t;D���p�2�ݶ��ZRl)�9M̛�,/��Yix��kя)�.�2@S^���u���d�6�!��>VB�� x<��Kt06���@��\nG�A�P�(��NbD��K\n�\"��cN��\ră.p���'2L��d�ꟲ���\\Ly�A=	��D��m3�%�@��������8�qbSP\"�ޢ�Ʈ/�Dz�C&�O��\0007f��D^1�X��/��,\n��v�Wx%f)��' �D�dQ@��I(ҋ7Y��|���A�Q��D��ڠe 8ׇ7k)_ �@\"\"��%�}�	�(��1�1؍�\r����e���?-ɵH��&�����\rL���'�eۮ0�T�]��C!�emNz�	Uz���Ɉ���S�ܜaf�7�M�^C�D���(_������#\"�dr5�9���81��hf�ȭ�a_�×tZX\0�U����{2nn]��;FR��!�}>s�Hi��y#���?\"Ť�����>{���/?7�F��Y����?Aj��.�U�!5`H��\$r\0��'\n�\":.��dԂٙƪ�q�Rխoh��>���{��1��+�>����t��k�%-D�=9�}�C@�8cm�Hr���W�n��\0Ď<(�RR�8����YV��`�pp�.U�e_`����^���쵛n^�_�R|�r΅p�7/!M5���|���\n�&�F��VVz��O�A�~ш|ƛ��4NȒ��Ք��g�yh-���\nN\"r\"���Gc�s����D�'�Xo٧���O�{��{Y{��E�=T�e�Z������{\";�H��Xz�t��w�*-����U���w�-��\"��<A^�O��T �]�D?:���������<��p�q�[���,)�&`�{xKI�I`�`��c��0����D�y8���qC��Y��CF���J���nk�[�8����:\n^�ց��T�!X*M�<�5`\0��6A�2o�P.��a�AH��#x[�����▞�� '�o@��O0^���h|�P�=+�)�d[����X-��W�!����Æ�/:\"�0k#XǞ<����h�CG�ݠ@F�(�k����l�&H�F0OSz���w�Q��3���z|+��\r9b�T�}'ܬwA�\r�nF�����!�g0�lp��l�1�+�|�h�kz��i&��u�D�{K��\\���\$t(�;���ì��H�r|Bw�D3[M�!:(�{�Z��(|-�Hy0�^�'׽�}�*����NK������5KU���jM�\"��w�]%���{1q��z���)]�Ů[k�\0O4������UF�\0�c���mZEGt�sDQZ�)n;7�<�qhlXx�I��^�V��&�ͷ�C�`,ɑ%��1\"@1�|�)�R�k��V��}S,�#!��G���]��Ex���YT��<%�Qѿ�@�����m���Jc��B��B i����G��f2����cD��nէ�=J���I_�������'����iA�&,��{��c��4��oV�%�d�2�x�e���#s_U�H�ՉW�!  =۷�O�<(y\0�.��G�'�\r����57�pV�(�þ:��}�RRHHy[��	����� 1����O\")��L�l���1�������������+<~�	\0���s���?�B@��d�����?n��~�&LЄ��?���@:@;��y���Q�>�����f���:\0�t�+j�sz�K�,b^�p���HX�?�P�\\D�?v\"�����\"�&� ?�����t��`�V?�\0���J�wC1O���#�Ɛ�*	��@̿�\0���Ƈ���/#8\"�O�\"�\0���6�Nc�ä�[�p@C�h\0{\0	�pDO��Ft��H/!h@��L�;�@���w���I��~C�ˀ¸)�E��4+���)���Eb�?]�d��\$�<���`o������?}�8�b���/�J���o#��IV,Ac��3�Xa ��o�xi����\"椌CU���D�k�YȊ�}�\n\r\0,G�\0�|q�� �.Ŋ���N�q�pN�Д�jBO\$|C�p}��4`����\\*4��bA���+�D_������X�\$�����@��6\n\0\$�~ˣ�\0��Jb݅��� U�p�X�iD\"�ێ��lg�t'���� �+x�<���N��51e���0`��B8q�\"O- 	C!�Қ�mɵ����*��f@#�6�ZЛ9���ZR�ǁ������	HZL� e����9�9�� T n��?xX\$0��%\0002�\n�y�!��e�:\$�QssA��nxK���l1'��Nz!p���.Ṇ�c�p���1@��)m�:@P�\0�1\n�(CR�5D(���P�1#	�d7�+\n��Bu��ha�M	a�\0�>�1W���\0a�4 s�-ׂ'�jp���\nJmQ����)�");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("v0��F����==��FS	��_6MƳ���r:�E�CI��o:�C��Xc��\r�؄J(:=�E���a28�x�?�'�i�SANN���xs�NB��Vl0���S	��Ul�(D|҄��P��>�E�㩶yHch��-3Eb�� �b��pE�p�9.����~\n�?Kb�iw|�`��d.�x8EN��!��2��3���\r���Y���y6GFmY�8o7\n\r�0�<d4�E'�\n#�\r���.�C!�^t�(��bqH��.���s���2�N�q٤�9��#{�c�����3nӸ2��r�:<�+�9�CȨ���\n<�\r`��/b�\\���!�H�2SڙF#8Ј�I�78�K��*ں�!���鎑��+��:+���&�2|�:��9���:��A,I��v4Ǣ�ꆌ��P-�\nҸ����%>(�c(P����74c8X��`X���:\r��3�� �KIAHH��s�\"N�8R�0HY5G�D�W(���3���Ut���  P�9M����Vd�?�4\rC�P��bؼ2*b�3�T`��n�VM�sb��0]pG�%n�\\�E�]�8ߋ�h�7��E`���@PI�jV��T��z�\rC+���R8\r�\0a�Rؾ7��0�����l_�2dYAxPZA���@y��A�R��T �o��^CK~c����⊰{}c����Z.���~�!�`���@C�.���ޒ.�������y��\n�l��9wt\\C\$pըp��8�/�媤eyn_��������H�!fwZ��%h����c5~[�H{\$��\n��\r!��4��n���n6͊�cH����J.6�|`ӛ�;.�ް[����p����W�ݪ��>��\\���hW��Z����O��7P���xA�pUW�)������!�/�p�i�[�����~�X�\nR�����\$�8?BE�y!c�P�C��5.\nH�]=�y*\$��s����t�`��5��7a�\r\0�5�j��-g������\0�ͤ#���oA�����\"p�;��\nH<�������m!������dÙ�K�>+d�=�p)�pP	#�|�<)�70���-�����(ek��9H��E��9���������.��N�䔒�J� �hL>e<ۿ�C�`K��xVA�� �a�P�A9W�I�y�4Wj�p�W����d�ER�2�ip#)�������CD?�r�u���xs��|ϸ�AX+?��l��<H�&������T#�|�РQ�b �-\$�}Ah�:t0�P��D�9!9Sm��H�i\ro}���ƪ�P_�E�a��x�f��u��{�Ӳv��<)�/#�QC*ܪ\0�rNir��t�GNo�w>�����M�Ӽ�� DJ��Cv`�`N�a@]�(�U ��S5{��=����9N����8z��3�^<��	��	��X�c�\n=@��s�3&�ꚠ�d����Aj%\r��y\\{<#�	U��g�R`��^��K4l�!�t���{�\0��W�&��|-���U��/7yU��C�����X��R�6u�H���V�u|I�V��\nq<鼇*p��)�����&N��q��/�Rل\nV	�8���������3�<;������}_����ph\r��� ӊpt�9#%<��2i�d3�R��s�\n���kOf����9pA�\n��9� ���� I��Y����C�c,U���2�^�\0�0\$�N��qsJ�+d�*�@1:u���������kΆ�!�4;�@z�Z��&��d\n3\$����ݠC�]����Q��BVwp�.K�\\άԌ\$9�i<2Zp:a�`U�����S�3���|T!�&P���,c=��0�=���N���d�뛭6n�ZyiTTJ��w��eS�u�'�n�m틸I�n\r;��ݔ���*)A��i���1�yQ�\r�_8?�՞��7�6�����l1�ǽ����{��������c����vr����{\\��.�,ۼ��e�v��k�ۛe�~L�^��7����\n�@.s����8t�}ɘ8�C�-�ѻ�-��4�I�dO{s�ջ8��[˵�f�;}Q����s^ݹ�Q�2[�(@�\nL\n�)���(A�a�\" ��	�&�P��@O\n師0�(M&�}�'�! �0�{6���}���k�ʘ@;�px6��zg�|+����D���+��Ϥ�yJ��L#�}��~��*/}����4���|�Aw���<���wO����X\0�������~����\r�ڏ�ޏ�����Z���*��\n�ϧ\0v�0 �����*���/�hD�?O�\rn���B�PF�o����0\\�`�0f��k��r�O�H�p���h��x�pq��֐P�T�b�����OP�į�8揢���P��O�o�.��0�·\0�\r��	������PE�K����͙\rP)\r���o��T��v �\r�Dܯ���o�����M�A(XhC�L&��\"h\r,�N�^qKkb���\"��	��}qy�\"��R�`���\0�������n�+���\rn���qH�HL�\0V�%��F: ؎���\$\r���f鬶јj�B�m�Qm�G\\�蕱���nk��%\"V��d��k��@� ��!2+6��%�� �~���Ğ%� r.�R[� 2?\"̹#\0�Ԁw\$�U%�#!%�)\$�	\$L�mA-W��{@ܷ��#�_&��x���]\$S'\0�\r���g�@m�0�`d�f�`G&L\0�':x�jx�*о�D�L�����������(��q����,&����l�Nt*�\n��	 �%f(����о�kZ�	���%i�n\".��Ļ�氮�~\0�U@��d��4��'r�\rn#`��2H� ��g�6�&��v�����'�\rr��S^�\$�@��Xf>΃k6�r7`\\	�5�V�'W5�\rdTb@E�2`P( B'�����0��/��w␑s����&r.SVsє9�JJ�x&�8������v��!`z4\$k�\0��x�7pI� өA�9�;����\r�~��4��>~'�\nP��s0P��QA+/7`WO���G1�Fp暴\n|�\0P�G�Gt�I\"T�iG�O@��F�V~G荔2�\$��%��96�,7L����LSoL�h��P5ʼ�У\0����P��\r�\$=�%�nUjXU���k�ϋ�N\0��\r��)F�*h�@�k�B���5\$��56Lbs|Mo8+8\"�:��G4�ON�S5��#j�\"�Nn��c�Jt�T�%(D�U�S�]M�j\$TK`�5��o@�������rYSNR1ER�\r�����E��Xr�NJ�7��b��gTUx�M�5�*�0r�:3��	��	�2i��1Q���k�F��0��YZst�e����c\n:oH�FE��xu���#��4�S#	 	\$�t?��E(p��(�R\"|eB�X���8	4�>\r/�<�\0E,^�D.��E{5��a�܆*��\r��Z��g�|��~�\r:moc��9���J�v*���B��7rT�&��nlH���PV�6��mDw�)m��\r��CV�w���\$�u�S���wS`AD��L�S6q�k��)Jkl�'L�hB9h�� Jimn<\0�  �<�\0�[��:\0�K(���~����s\0�K̒��Y'ʈg�a��O������(��]v�:�&!`�P��xV^w����n�Ĺ��7\0�&�g|B\0(����*,��ľ�²d��7⛬t��z�w�z�\n�E\",\0�\"fb�\$B�(�h(�4ժ5b?�΍w��q|@Ƙ+���؁�޶���&Ɋ�~Nⴎ��ח�N6<u�FxWQ��^�^���;P.#/����|Wȃ8k.��/7K/w�Ql�8�~Qψ��\\1�\\���&\"ئWR��/�)|��A5r��eE�@��k��\0O��wK&�f��\"'Lm����l@��ۄPZ�����7����\r�#�o��x�`]��b̄NzZ@�0NR�,�x[P����c���8z�X�\r�?��Ǎ�?�9�2�x�}�L��F'LP�yzð\\ƙǌT�� Ť��i�N��ǀ����Tx%�xau�cw��#l,��\"�P���b�*���g�#Zud��,5\$�D��3]�؛?�h~�0\n�y�N7�b�����z�\0�a5q���k�p��v����Q����,D�[��A\\E�yK�yP#U��Zk��&)��E�9q������\"�7�����!���[��Q��Md۔�uQ�J#\$o��]�jۥ�g��O�\n�XD��6�꣢�e�����X�Z�����:���E�:O��U��b�z]�7s�����D��c��0�`�?��\\�S{�y����S�ih�z�Ei�ij&��׫e'�k����X�y f6V-Z�WewŊ;G�\$���{S���K���7	�1n��>@�iz��z�w�9����{�x;���\0���\nI�����yk���[���7{�޻8-~���w�,[lȌ�@Ϸ���VԘ+��Ӌ���ؿ���j��c�ؤ���\\qǊ����Y����'���z��Y���ݻ���˜���?a�A�:�Q٭���(��} �\n��y�#S�y\0�[��?����/�����]������M�y�{ˣ�9��=P�ϫ�O��Ls\\sWD��ػ�˱�|7��jN-�E�˕+�`u�Ƽ�\rM}��~���I���~i�ڴ���|�lv��}�Y��L1�l>\r�������9��,o�Y��9�}�����Sgg�����銼����:��u)���E��̀C���R%���~|�~�w���0]�|��\\��yϙ��y�\\��ج7й���e�,m��u���7��(T],w�θfU=����TRW6�<���Kֽ���g�;����||1�\0Qy�\"9�vb\$5�mw��Ά�o��\r\0xb�kH��|�ɚ �Z\r�h��Wʜ\\��Ա���.��3U�\r˽ؘ\r���>?2)�᩟�/�=��5��0@ƅH�~<�н�x���_��/˾3�~I+~l~�H�Y��{����Y�^]�^a�e�^h���^r+>C���bB�,����2/L�����R�#m�RKI�K�'픕E�W�1�]F�z�_]�T��%4̔\0�V=�4�;\$T� �枍{��?��לּԞ�3��n\r�z ��X?c�p�\n?�#��a�d���X�\n��:z��-�^X�!��`�:\0���y,Dl��J`��A)h�U�������+������5+�����~_���������+<�b]<m5�~'������]��')�ެ��ܺ/���P��r�4�o�{��_�ng��HF�pBs�H�1)��b��b��?�톼\"[�C<�U~<0��y�:�G�@}脬z��޺�w)}��[ꖞ���<8�&�X\"`�B�Ww��{��k��U������.���E;�=�pQɢ��R)t\0;��Լ��*��J�C^ �d��,�+d-��~�*��xpn��@��A�?�Qh{䄳'A5�P{dX�`�H+���sS���kX/��E(3=�!00�4��\rjł�Za����>�m���4����?og3xƕ�JW\$�EQ���^&��\nQE���h��j���qC�N��Ơ,y��H���β\$'@\n��;\0\\]�ϛв(�\n6ar�ǩ�u�P�/�;P�#q1���\n�PB.�6�����`\n�Fٰ�͒W�������3db�ZU��֜�=����x�a�@�=����f���Z��;B�k謀�����mJ��N�g�^���p�r����ٲ�(Ilc�������p*���A��O��U�7\\D<T���f+�TH��Ϡ`�R���Zq�[`of\\���\"�πx�|E��f�����ŰP/�S\"�_�8�-C�F�]\"j�h��F�29��!E����b[�����E�*���M�x�\0�`9�DU_�t����юq�^��(����j!���tX�'��E�_ػ�M��Qd^b��|��,�{4\\M�X�Ff�-�kN`7,���BJG5�&�*1L��4	#��-�����`'\n�L?\0)�|�r	X���|���e\nJ9@ʬ��ȥ�6q�X\"�qE�	Pm�¢N��Җ7�}	��<I\n�A�͌j��u���L+F��'��CZ�d&Rn�cI��l�\$����\"�)|7�4hCvcs��}�s���G0~#f��e�B����.��r�O!<]/�d�[A\$��)�J�P���\0Y%��F`&B����vM�II�P�*7��֐2��&l��Xo�.\0�KZ��Bq&<J�p	��e�i;\r��0��PB��H��M���L��İ=�T��X��c1&y-I�6fN�|���&yR�n0r�	�%V����RKR�d��H�� ��A���Y\n��<Jĺ���L����'�~V \"����l!d��'�`��q���>Iit3:Lɲ\\s%�ͪ�E@HC�����\nf\"����@ 1�1 l�n͆�������/X\\�DK �^-�n�|�\"�\n��8@�{�)P��(P(��s f y0��M��@�\0&b�QX�]3	�8���<��#11<�.b����f*p'<�4���)1�\0��)��n�~cȁT�S���tI�11�(\0�P,��d\"=��@�6��\0��w\\�fzY�L�n(���O}5	���W=����2Y�͖e@Ol܀�7I�N�mX\0���N:n���B��\0�k�|��,p>Nxn�xh��5�Θ	�G�d'��3�M�S\$H��1i�N�0�݀8��Mv�ĝ\0P�\\��NH�\0|9�@\0!d�H�NɥL�\nS؞�؀��*MQu�@&�7i�8򓖜��)1\0#Ljr�3\\��9HK��d�?�hg�:	�Ozvs�������O�|\0F4���>��ϾpS�|��<*LBw)�<�?9��@	3���+7�ϲes��\0@��Ђy�\$��\n(#B�'�R�ӫ���5Ci�Р4:��C� о}4D���(i<j�P�Q���\0AD���f��%����>��L���4�T��@�I�O��X���X �(�&l�')}\$�eI�f�N_% �4��i�\\���Uh�C�=D�u�����'@��v��8dB�-%(�T�%�7��㖨�f\n�X\0m��@C��0��I��\rɽ�w<�Q��hS0�9@��I,t�')˦\0J7��\r���\0�!��ƷW1\0���~�_��\r�2\nf܊����@QK�9\r���\rXi{/�~�������2Z_������2'*o����	Uس��\0�{�e(\$���i�M�4T4�4�}6)�����mV}A�3Q\0��l��/=@QZ�:�k�N���|Q��&��4J���R*iSP��5��\n���t@���_�)��QI�MXo�ޠ�k19B7�=����\0�ɷ̆l�|����[aa�.�Ԩ��\n\0�49�Βv@G����PO'�ZH�X'VZ@T��n���g�7�>�l3c�D���XZ��fj�Y��_�mX)ʀ�zG������\"P2|\0N�j�X�����{�\0�0d�Tl�� \nq;�߁:bS����hfy���)�Q+jSCQ����yS����0�H�q�`	��`�F��l�pT+�y��r�jZ�K�c���WmA�:��y�5�\0P&����zW��Z�)D�	T�vD�V��3V���F�ȭ�Rj֭��p�v�5�)���'X&@.��C@�`�pT��lSw_��	��#��:!/�5�rr��r��;�F�&�M@�\\C\0\"�\$��(T�X+���\$t+�r��84Xf��I���d�#&��cI�P��Z����l�̱(l��Z�����6^����3��|��s�\\�=��E��r�����3���w+�(�,� �c�����^�|�:`�h[�Uah�t��Z���Զ�O;��qy�v�\\��A^����x!�j2VդմE��d�0�ر�ְ4H����Y�Hz ��0+���Rj����f_k����AJ�j��[��,U\\jX�X�=���ZDw5uˤ��՟n�	%'��}�&�p&� )����q�X��\0+_9�C)�Iۊ)�R��짇`ĵ��@�/!+UAf�����\0R�=�A�%�r3{�\0`%z0�\$�>Ѹ��=�h��]/�6����4\0i�_2�U���e���;:J�Nu�V|�@�	���G�hU�=Qh'�(T>,�n�?#��ts���f�=c�Vvu`�U'X)�M���Q��p�p7פ!a��J�l�0@ZF�E��=ClJd�������uAJ�tȪp��0��W��Uw���Ɓ����Fa\ni�ݻX��J*���o*6����k�8�N��[*�/�u�MCUMaJ�޲�V!���U�!+�Ŭ�p�xh��<@B�����] ;���  �u����_2�R�L���:�߈	�4�.f1�@b�%\0���!{�=Mۿ�|��`�x�	\nтo�!p)_�t�Ⱦ����#��p�a����i\\���3D���.��񶕁Y�2�x�F�g�넞�8'(�0BJ��@b�Z�n	p\"Ee9�����J�0X3���b�\r; �S�1[y�=(73��	Ñ��2����*��l0��!V�lr�Z@<�����T��Km��XiF\nU��?fT�\$i8GS)L\$�8B�iD!\\B#<4aT���+�@�-�7\\��x6�p����?�\r��N/黰�%L+`�h�t��<W�>�{��~(@������R�06ǞP+��{Esö\$�*��b�	�&�#��[X̯����&�����b���n���S��U���l�,0G~�}��cUf'dCs<m\r;�<���*4������~�ǉoam4�]/�0��2c�Fxw�H;R��qﵾ&	kX�?AIƠ�\">����x�?��,P��b�iū�)c<\\+�+�^n3�ő���|N'!+PG�N5�T����BK����!�1\":�2bP�,�Fy*��NÓ<a[&�3���t閇7��\$\\�qߔ 2ecIn�T�y�2�c_	@\nu�p ��x��+��X�Uq��<�A.��Kʕ�ʎ!2�?�8�fr˗8��\r8(��p^�!����!�Y�=q>��\r�v-πٗ��	�1��g�f,��[�,e'ZX:2\\H�������y<�1)[α�;��D|#�H@����LS�3��>;��]2X�vj�.GE�Bi+d�%���,Qr%Ц¶*��I����5`�t�-�s��b�8E�۾���e\0=�2�/���Yq9-eZ���1\\���^�U����`&g�WJ��Y�hK]8W@;�p��#���#B�ynqĕ��\$u���Y��!�\$���)(rX@/+�L8�O^�ʔp6,���Ѱw�<%MS�S=Z%��W���\r�\nHy/�2+e��1�E��ɣ\\�Uw	(p\n-���I��S�E��ZiI@1	����`��\$�44����8��>\0���i�M��ӈ4��Q��j�Y��y�p#�x�`����m'�Zڂ6��za�S�i�&��ʒR�>z�\n����{Ti�P:�����j�Zj�T�t�R���@:����ޭ5��h�j{\r�f��r��\"�x��|�cx�?�r��k��p���.�r��>tq�C���	k5h��a��\n�U:y����xW8�k���)3�!ҋk�^�t�}���-x5�^��B(q@��Qd]ƴCr�\"kw[&��u�s��W:�ꕝN�@����d�����=���+Z9��N��������@�m���{-%>�H�����R0*�7K/<~���,js���n��P\09.�͵����Sj\n��74�ݱ,�\$;E����-���m�\0*Ȼv��7�c;u&v�ֲ�37�ء�y(��t��n;J���A���G4�hf����R��@5�)V{[�Y��m�b�����6��1��p�J�6�����;[�.�Ŋ[r���b9�V��0���\rw݀��C���w���VT��&=�,�h��zH��)���8���E�sI�t<@e+0y��nj�T���Ʈ�w��~�d�J��σ��@�)c��+h�,���ث8p��L K��:Q�A��og���1�o���?I�Z.�?�=~�����n����kF�!n%/�E�t0'̔�P<Ƶ�G�qP䴓F��xA�q�����⃫vn�`,��cW�{�9K��߇{|�+s�<��4Z+צ�6�P��PL������(L=�ծ��jf�h��>)�A�혠�q��pK̆�����Ҡ�~�6d0���Y�#y�}�tO��R��CS�_�燜���|bHw�s�O%U��w�p��N򈍜���Y]�����U\"rM�t���\0jxoW�D���[[�M� �y��T��8��@�9��h����!����̋r`����\\/�4�u{�d�8Sǡ�sb�\"� ����i�;��ji�ǿ�k�j}v�i�74߽�J��9=՗54�0'�?���(�7��qg��� t	��_����[����z�ӌ\\w�_>s���_����g\0�����V�|\$�p��-��Bs�X܇�.���;��3����g���PCD���Gy1���j\0y=M˞;F��m(�oD7y�k����b�o�=�!:�.��%C�%�t߿���Xm\$��6&�P�bj���T�u�*�Tx�\n�d5����Νt^d�(S|���-q�������\0����(tXYQ!H�F�k����0t�����4H|��oNo��N��%�\\��w\"0��Bq��\$[玙��f�|q����7~Ey���X���q�ר>|� Ob*�\n���Im�c�EЮ��e��6e���v˟L���nɩ�Kxx~a��ǜ�f)9�˟]F�!�s�I�iN�h~�Ӕ���R����.������GF������8��/�zdC�f�6-�#g|���t��;���4�TV�)�kV�����/y��C������9��07h@����).Hq��E���N}��K�+��Y�r�\nb3@��K1 �)�l�A˧�=#��HiL���ʄ5�o�A�������B>Y�@\n1H��!+��ȣs�0�GH~^7�ـ����QrI�8���\0Ì�`��\nw�=0A�y�[Q�8H��O���g m���#ʮukHB����#�o�uf�oݐ�k����^!��p{�}�����4Iv������?x{���CY�-�ICמ���Ȓ>0��l\r��\0��|Q�1��5L�/���j��3;�Lﴷ�^�{�U�n(}����b��W��١��+�>��'�����{WsC~qM;P��R�v̢�Ɗ�:p���Q���G�� 7��a�;���_�z���)|���:�g\0Y�*�/kė\n��>U��0�x�H@�-=\"0H^U��E+�x+��#�;���1��k�y������Th�:G�&�-�!qs�3^|���xW�-l��!׸��F��X��t]��BXY;Q�L�������0cI�oj��A�Q�����L��GG��%\$(wҹ�Eh�XK�a�����o��b����5��������sA���t/\r�ݒ`�w�7<MP��*yY�h>P�r��=zjW01�g�dl�iD/�}^V�\"b��>������X���Rn����r�.0�����̙9@���� ���ۮȷ�;�&�^�2��hYXh�(���b��\0�؀�/�\0�l�:0��܂�?���t%�> ���CG4@���@�E�<��� �h	O��0K�\0�@r�[�\"���)�A�oX�4�z� �NR��̃�`��j�k����P���� �]O�l����2\n��*�b�5Dn����2��(�\$��<)�Hac:�ϋ�/�8�i:�n6:�0;�<1�LP\$ أ�Y���\$�����:0�����������jI�P�\n�rL!w������N\0�>~/`4�+\0��<��^RX�U�6���:\0��bN�莌*�.�N��pxp_�� 8\0Xo�Kb蘖�|�l\0Ɩ���)\0��P���:<pl�\n�@�A�SPP���ƚ�\\�� A��03\0006 ��(�.���pv�}��9�z������@N\$ņ?5�ㅟ�i+Av�8`��y�� ���\n;� � ��V���p�߀�\"��j���E=�x�0d\$�P��V�	x�X����g\\?\0ePaAJ/`�pS��LЙ�	�(PYBq��ОA�!.�b�Vs���\$�	|Pf%gzT�A���k���0l%.��l�5I��+�8I+������BH�*�p�Q\n��G^B�	rLPUB�q��hB����nB�,b��4����Y�`��	|#`.B��.��B��\$�6!�*�s\$�#<�B%�����Q�*�e	�N���rL��\0�N1!i+\0��Ѥ�\"�60bCgaN��\rPUCq�/P�\$BNIB��,%#�-�\r�+e��h&p�º/d+����P�C-�B�D;D�C}<BB��:0�Ï�\rP�CP���Bi�1���r���	�'�����c[��\r?P�*?��³�+p�A�,1Q\0�L@�qDh.���Y�P��������`0�@���6Q�b\n\r�ޕ�0��\r\$1��@�����=ĺB֔�� -�t�X��B��C1��,�+BI�����%���	��ֳ(���KT\0��F@�/�7X�\nD��`���`�[Ε�p�D��L��D��Q\0\0��N`3�^�\n@��%�	9��������\0���[� �	�L����MA믤Q�2Q8)��HW�GD�b�%\np�	�S������(�#�t�����D�HQq[�f�]�\\'(B�@�^�(CCv���V�[Ř`(�^E��Zc!�7�ÑE�*�Y1mEu�Y�_E��\\`�E�c,[1e���]�`E��\"䆬86 ��z�q�]�hŀx�@����OE��\$O�6�}�Qq=����!\nŐ<b�:�Q�c��O���'�b��\n�T��(|QqF��R`&E*1�R�!L^�f���`�`ņ�c\0^H�!���yg�|F�\r�bF���l�<Ɛ�cqeF��i�pF��hϖ\0��]Q���hqbƨ4O #\$=\$g��F��k�F�p.�<\0��k`���n��Ʀ��O(J ��[q�F�j1�F�4�\\(���3\\Tg�D-�T�CA�+ \r�7���M���x��	\0Z	R�\0005��p\r1�E\n�V�I�(;R��~[>`3�6��rp��	�%���-��Ж\0�	dCñ�(9����A�x�@2���!���*`\0002ǲ~8S���	P��Aڏ�/ �#揩�-�8�1��n��t�*\0�#O���0=0	�'\0d��	��( x�G�  \$��\0�H��(�2�\n��3��f��n�� 7�\ñ`7G�F@>H.�5 >?��B>�<�z�\$`��>0�R��tY�����+ �F��Q΃� �l@>\0��̅1�0��\$V�ƴK�&\0���@0� �H�>4��o\0006���q��8�1�P=9�\n���1�7�\0��D*�<���#H�\"1|�#��!K3�=~=nm�H��,��#�,{I#�t���~)� ֓\r b6�I�!1g�E�/�r�\$���)\0�����6�̎'�0v3g@��\\�@\0!� �3!4��HdL�f�_��9r�,[�x�eb�f �HHhpf1~�D%,��a)�0�֥�%<�N���c�>�& K!K0�-��;��H0���&�܏ଛ.�|#���.�@3��!K�\0002\0�!�07�ʿ�eJ9h��\\=d�T��\\\0�Q�DI�d�\r(II 8�Dr�Z~(;b�J8��C��U!�RH�\r�� 3I��ė�_���A�N#�32���1�@��{��@�J+#�=R}��2�-�~H.�!`:���T��^�!K��)�Jл(\\�-\"#���L0	�tʃ)d�/!�r��]8��ы�.SKҐ\0�H\\wG�:�)�G��ʘ����5+�N	���N�\r��J�����t�0&�(\n�a��!I�s�!!d�1��0���D�	2:�5����3)�W�J%����:0���˂�#Z*�H�-k�״�\r�R��<-|Z��2�L���\0��dx�K�Aty2v��L�A8�\0�K��3�+\\������{e����뼠�����!ֲ���\$�g�I�.���O#��򝂵)�>�\$g!P؀6KF�#�q\n�W�P2N�zS{)|�/'�����(k����fQ*��JdJ���>�����!0�2�\0�0�Rbr��4���yJ���\r <�^�H@���S���d��WlT���\\�T���T�F;q!�MC�x!\$���9�_�Ay?���z�2|�I|��.��2�)*c�BR��+����2ԭ`K�2�\\R�˛+�B�0˙L��4��3d��)���(��G,��\"�̈��Ғ3x�S?L�3<�SAM.t���2�.�� !+�%8!�J�H��@��K%�����4���A��3���N�*d�,�M4�/ſ�Zc��O1�\r�\0\0�(����M</���Q��5̭38J75��S_=�5ܩA�-�0�:)~HR�Qt�͑\0\rsdGsdL�.���[6@��5��6�2�M�̒�-/��	\0c�L�Φ����M����e��7x�q�\0003�b�4���4`1.j�@�H46\$W�6\0�\r���i�\n����y\0�8h;��>���Q_<40?RH10�G�(L�fQ5/��xX����s���bL�`<��8{�r���S��ɭ9@B���8x6�'��N\n��R�Qf`���8|�\n'��ԟ�6T(2^A*/�'0E�t�*�x�ɇ#d�g�H���RH�(B��U#`\n�\$��c��\0(#�9�2\r>����\n�`Cr�K�HP �����eP7��\$�8҆�6�����Y�6�|�T��h���RЄ,4s�Ǌ*�� �K��)Ds�JV�ԟ��5�\n�MH?�/|�\0006@.L�h�˨�>��Kv�L�\"!K(\n��I�����Ov��)I ������v6Ü3��>d���!����O��f/�K�����Lv���0�	T�\nؑ,�,����ܹ�6�M�|��Od�!'U=�C/�Jd*�ǭ�F<�Ӹ��!H;�ڂ;љA~�^<aSe�2֘�=O�1� 9L{9��,:�9��,�7����-\0`�\0�`� �u:1| �e1�V�L#A��	�Ap\r���`���7)��@��,dġ��{+�����5�\0b��`���Lh�U\n�Цe\nS�Ζ}�\0��JA��!2\0�A,u\0006PKQ|3�O	PS�68\0%@0ɦ\n�L��5.ə ��\0',��<��)@���?(P5�J���g�I�\rd��9\0��T̒��)�B�LA\$c�b)��\r��X��C�O�(�\$�N�J��qHV `\"M�8�t���\r�;0�3|QC7��XLu1}3X:�bO4]���U��MaE�?T=��%0�␂C!�T��L�H��`��*P�8K�/\\��K6{�SY+���͐�����KQ6@6�V͑<�eI�P�6CS�zO�\0��P�f�����>���80�����a�\"gI,gmF���ᙔr��Id�|�F�R\$� �ȋH�!�2FܦP �HR�	�	�8��a�5����Ih.�QL�(@;�\r�Z��3%\0=)@�B�*��H�?T�\0�#�����I\r���Jp���L�JU%�eR_I�&@:R�%�&ԪRq+*��R�Y�?R\"��J�*R�3�J�iԬ�\"�,����F5&j��6�.���'KE+ԣ�&u)T�RML)T���LD~ԹR�E��SK�	Ԥ�K�1�!��L���7��D�c�K�-3�4�D����A/x%T@SV�I��S^= ��`\r���\n5#��0	R2Q�8��T�I#�%S��w��ނ�|�R�7Ӝ�/E��\0��������~0A/�\$��ӿ@82L!d������t�Y�O�|7<a�(�ʮ6�4d@\r�O�;t��A�?�Q�iIᏐ�|&�,�<w�����-�����C����\$0ۀ�+�����B1����P�\"�����*��#\0�H���E�JO����5�\0\r���]O�첈�^\rxz�:�^	��_(���6M'%[�-i�]F�#ғ��N����\0ĕ�LN�4�f�#i:S�X�@4��%�[�ʙ� �=��&#e���Hc�\0�8�f  �p�4�\0�ag���/eD,Aڀ@�bE	\$P��Jj�2\0�*�:�� .����24ࣨ-P@u��=��)�\n=x )\0���򔂀�=�\n`+��H()\0#��x��&�=:ki�&��)�H+>�U��ՇChi\0�OZ~@+��x	�\"�.F?B�	�B�mA���C�[��D��mQuF�j��R/4��T�uK%�TMD�c�W!B\0�E��Г�`ɏ=���&L=�cϏB(\n̨[��0�U\n ��O�ό1�EUTu]�\rU�\\D�[���\r��G��`1��'X�d��U�T�?C��H�sV3WEcuu�!X��u�\$���,B�}e5~�U�P�1�.��'��A:>p� �è��i�\n���S��S��E\n1�\"�T@�@#��lN�1�[(ñ:���PQ�U�l4D�[%l�Є%[X�u�D�5�b��	�kH�b�D�pq�W]p@9\0[R8��Ŋ�(-R�N��.��.Up�TW%\\�*U̦)\\Ⴡ@��C�tU͟�j\"\0<WRc}uu�u]v3���Q�s���!]}��WX]�w���.�mu�<I\\ 5�׹���r�����d�h�^usM��+ 9�Eu��\nj��W��y��^��U�W[_E{�҄�H�>��W�A�~U��_�:��k]xٵ�\0b(�\\B�\$��89�\\�d��X4��Uمa�H8W�#es��^�A?�+=u��\0�`�ĵ���|\0�z)�u��p����Q�u��)�`����]xOu@6�ew���`q����9��?�� �W@h��v#	�@m�\$�X��+ <�~(�f\"�j�D���e��1X�_=rA=��W9�\\ 5��c=�E@�l��1X�̐: �^��\0�\$=bM�s�خ��FV��b�\$�Y	b8W�C�T�s6%Y ��@�(-��)�T�b���/��D�ؑd���H؜�L�`�@d	VG�=eU�������vX\ne���ZWF{��MMY9eM��Ѳu��2GN(��-(�+b���	K_fX#�f�9d��c�vnRb��%4'-�\"�\n9\$(J��W%fՙ6j@7gvr4�d/1���g��׀�[e��}Ӈc%�����c��N�J\$��aa٬N��	Y2�dS��b��Q���`\0Š�W�\r���r�,H��\r��h�rQ���\"�����i\n��F�KK�;���iP+V��iu�p�c3�]֚Z`8j\0�-�����Ei����+d]�\"ىh�	VLϼ�ŕ���f}�1��j��6��7dM��\"XYj�W@١��A\r�e%�\0���j�b�tY�:����Z������e�v��(ծ6��d�A�Z�k\r�i�E��K�6���l*�<J]hu������ �����6[#lb�;��bճv�dtt�7�Jt�A���و��8����A\nlxҖ�Z�&�!~�Ҙ����[OmH�մ~��<\0�m����Z�m��,�[Gd�u�i-�DqZL��66[�nE� �ۇe�j1�[l��6ޗJ}s�/[�me���#�m���J��v�F7n?Jt�[R�\\6�Yo��L��c`�5��o������vm�X�-��6��a�c��Zsl%�C@�[����Wa� ���[����W[Mo���g\\��/\0�o� \"��sbM��9��d�ģ�����5�8<v�#4�m�,}ن��V|Ml���S\\����im;��ᆞ���+YQrEȷ%��rj�7ۯa}���Z�MrW\"ܝs�3�؊(�B�4��q�76q�Co�*��+q�����hs�v<��oՔW-��r�7?ܧt\r� �صt-��>tH�)�rJ7\"@w]Ŭ7IY�pmҷ7XYt\nG��]\rp\r�Q\n8\n�2�ѰTezP�\0�=�v�NE�#�ܽu�̗E�iu-��N��s��^�.�}��Cܕd˂2�]1f��.g�r0�4���x���>WvE�7.]�j��U�\\k4�q�!��t��wI��F]pwq�5w)\0����q��p\0\"\0_az]^�7v]�[-ܗ<��vޖL7�c]�a7?\$�-�7;^w5�v�]=va�g�v��	k\$w���;Z�!��V�ql@;V�0�l�\\�[�v-���`X �xW�_��\"��n� �<^uw���^ew=ӷ�]��r�u�p��*�u�r�Z[]����G�>é5C�̻���(0*ɒ����V0W��	�����(\nՂ�[z��`)^�Zkɱ'�#��\n\n^�()�����BiW� �\n�\n�ݦ ��7���<��'�@	��^�:�3\0*�@���\nЏJ���&h\n���|� ��z��\0)_\n=젠_D=h\n5_Z���`*\0���3�.��(\n�'����!h'�(�`«><� -\0�{zi /��L8�`/�:�I�Uv�@Z]��<����_{�0���~=�`�m{,7�E{=�+�c~�l����}5�W�_�X��&\$Jk���1m�7��O{%��8hx`\"�������'{}�7�^袍��Ѐt��@\$��������z�\n���Vi�߱{�i�&J��W���|�\n	�UZ��w�_�NW��(���:_R�R��3}���`&\n�iAh0��\0�	�|E�S����_%���_2֠�7�߶8�����|{8%_Y}pc�\0�}��k�_k���_s}����_~Ώ?�`	׻�{����L�pf8;\0��^8\0����_�E`�\"b<�w��ߍ~@	��_�-��Jߣ�̀��_���-��:fW�_�~M�i�a1~~w��=�&�_?�Zo���uU��7�|�XJ�aUrb�d��pZ	�ը<�w��Й0f#�U{~@&_��	�*�����Ԁ�{zw�_3�`�#��\$(0�gU��PX?��Rk`>+\$.8g�Æ��x���lI�a���8[�{��X8a����I�����\n�\r�c~��aͅ�Wx�?�\r�8&�L>!��^��|�ڏt=�a�`=��؍߫ %�����d	��M}x\nW��+|-� ��U��x	i�aC����W�a�-�s�߯�����'f(Z�'}����צ\"^'�b���\nxkb�=5����h�dCݏz=6\$C؀�&+���~�%8�\0W�`���'����`�v&�Y�����\n&0<�cͨ�<�	 !M���\0���,8�bɊ�\$�ď?E�X�c\n����U��&Aha�10����8���V�F2ع�e�NI�bq��3��>��\0V�..�؀W���ɗ�b���#a�=B��Ϩ[�P��*=�	��*���踂`7���q+H� 	`\$�U�5k^�=�Z	�b�=���}�X�c��.(�����d�_3�)��_�3��C�b�\nF1���O��,���Q���yc���.�#�c���	���.A��`>���5��L�F9�:<�+u��ߋ\"����V�[(=>X%Ҍ=H\nS9a��x�I�vE �a[ &2��dh=PZVU]U�#��d^\n����7�FG�\"&���T�&������F?���\0&Hy)b��K9)�w;P\n�\$d���#��bˉ&J��g�vK#�=�nLX�㍓H*ا^ۀ�� *'K����ቍn�,�`�:�:\0V�	)�&!|�Pز���VO����-�ԧ}��{��b	��P�Ee#��O��cg��P�+�Zrt�(O{��r����ByL�N��2�2�(�&U	����\0X�぀��k��M��f5]���VXUU\\<�p��aH�#\0�=�Z���XvW	��,R�`���)�f���6Ucᄝ0��,�b���ըf'`%ᛔ���e݀�^8\0���^����.\nX__���P\0)��.B@�d�va9)'���N�����O��+~�+��+��XA��-��	�j3��	��M��aX��]�\"f)ـ�>y:�L�\n�Ņ����4�V��X��_��p��0=-�@§z��UY�b���\0����ȸx\n�&�jX�&���*ɋ_r{R7�K��i��+q3�kw����8f9��{�6]�a�����V�l�b���>jy�f��y���<��)���(\nkf�j�ԫ@�X \0Q~���@��F8C��L0�@*\0��`���^�~r�x�gD�6t�T�|�x��# >u���	�e)YϪǝ�u�����Jky���2���&��eU�{�M�;���i��U��L=�u#�g�X*t���+�0׾ȁ��	 '�����UY�^)5VՓ��*X�-U�a\"�ǆ���8`�}a�ו�U�{g����d�n-8�\0���t9��<�L��d��h� ,��=I0�h�8���\"��:���8	ԣc��3h1�\0i���z�8���6�c��d���8��1�hi�ֆ��'���X�9�|��̏_��a��K���)�߯Xn�9��Z0�5�\0��ƈx�\0�<���������\nX���.U��W�ic��:�:�������	����Q&����\nZ\r�<��Y���փ������9Vg��R踶����b��Z��b���*��I�G���k�����c�.���rmP\0�3�Y\0\"'���2��Uj�I�iI���9���]W`+��L>��-c�<��|\0���Z煥�\0��e�{�󀉎N���H�����=h+�&��@�1��d�S^��h�y=fW���Ua3~��XN�;�B���+F�O�va\"M��wg턆cȀ����G'����Gi�b)���@�XC�f<�\0������``�ڴ:�c�<��p�6<Ɗc�a�X^Y�c�=-ax����CRK�Z<ƛ��cf%�?�����n,نg種��>h/�x��H筑Vye���YWVZ��}l���\0��-��j���O8�_[�0\nY\\�Q�~��#���n�Z����>wy�৪��Y_[���X\r궚ޫ�|egUj�:�Ս��sˢa��\nZ&ȭ\r`��.����I�`{��.�n冭��\n�q�ƖW�d.���)��a��`>g����X��}�8򸋀��(�\n�.͛v{�T�7���,�3��d��g�~0�ؔ��	<�{�<}�3��\$�ϔ)�8�@�R34�:���䵥]��~6U!<�@�jZ� ֺS���^��ӈ��B�~ 5��ɳ,��S�,(�M���9p 3��X�/Z��u'�:�F�p\\�)|ƣ�X\r�<�Y8Xt脕!`6 4��.��6���+Z�P۰�n���<�0���&�b�w�0>��Sr<�@6�d\n��z�lO��FRlO�;H5�ڝ*��L^x0�>g6�s�/�q��Ye\0��@<�z=\$�Cئ���\n`+'S��9��X�8�_/���i�j��	��h7�5Y\0&�c��w���a�֡�E�ɟ�Xyfu��b{5�ͳvG�jg�n�Xd��\n�{��lǫ�͙���~��V����	?�3�����W}���R	Ch0�>R�=� 7����9�x��>S�\"ӂ4z����Ҵ0f\"`�1��:愸x:���x;Z�d�֔��e��K�;��@�g�TML��7N3R�k���N(i�F�;)�Orn:Ӷ�\"�>�^#�e;n�!Q\0�/ԆfU\0\"�@Rp6����B�m��0��	���!�Y�r/T��t�1�V�eOQzBr�9,�@9m�!��t���11;�\"cmMu,S��<ŮW\"�yv�K)V��m�V,��b��k�\"�1���hx���F&����9�t��?�܄Iq����\0g�5�MK�!,��?��!PSQG�e�@��a:�b��Hd@(: ��Dd��FJ1Ḣ 8�w&��@⍅'i�VmL� E/�[R�R˻Z�n�fՏ��ٴ_�G�hl�bŇ�����l�.����3%I�[c6�� 3k�,�ID���X%��JSox�v��K�B�q[iD��a�n�O@�۱m��/�|�iK�2RjfV�r��ZEJ�n�g\0_�H�;PHD�n�-��i�Q��a�)�)�Pg���L6����%l��k�:��s�k�1��S�m�NF���o�(��SJIe:��ԺJ��=l���F���)ﲋ\$x��ͣ�>��Æ9�X��o�<�'��mHf!���D;�\0�f�SA��r/�;V�l<�e�������ػ2J�A\$��k�U������Y����� �	d�\0׻|pP�!f���Z\r@����O�'_x6;\\4�%�Z6[�6��t���KŰ#�u1|�2�XOo&�6~��Dq��O<�<�:Ӷ|���p%%�ֳR�&\r�*oʡx\0C[ʸ#���:lpw��\$KL��;sh�`�aRn�z�;ϔ�;��|8L����=OEǥH�* )�-�T�/⛲_�H._%�����H���7TH�SD5>���S�_��cr~�y�E�\0�*�^���,͎�FS��=�\0�#�>��@c�E���MA7o�\r�R��q�{S����մ1�'����'m����{p0f2/���<m:-HƸ�%N'[P�wd��f��\n�5<t��	<n��5�F3��+�b���*�q�ed��ƫ0��@�S;�鞒d�����:�;=>؁=���#������N�R2�`�F��d�\"��wQݵUN����R������utvӡ\r�'�K#�����O\0ɰ씑��r��'�r�H�.|��;O�K.8��L��܄�HT�|sr��̃IMr��/4<䏦�<�r��Ɩ0�5n����ln,�l?�N�ࢃa�f�ܠ��!f����o~�|���@2/���<�����%|���1~�iL�	_1 5�'�}���r�!�1�6��Eo2��s��.���� \r��r���7<����2<�sn;r5*O��lLD�F@�l[͜�;�]ͦ�HaGw7�5�Y�̚���5H��\$�A��\\<t|�A�RQD��!D\\��I�qv@�}�����_��lh�(����SK�i�)|iwT���μ�O>�,a����>T���4|�s�d`#y\nu�,���\rη@A�s�8�=��t�d��Y�w&�		N�B�h�R��2�t %��q,T����wM��[!O=��x��NHJ@�C6�F���~�a\n.�G�q�\"'(���q�\r8CvhJ`9\0�/���|t�&w@�&��+�\0����]���<bt���?�DqO:����</a��X�!�8��YE�U�E��l��7G=��aL�t���@1^����><�ЇQ�X����܀	\0�}?CwԜ�]�=�EW����\r�������NR�+�N��#�]?�1�oG���}.wG����}%�6Q�����#��@.��h;N� <P��䐠1��8�t��⡼�u�πI��M��!]tU֨D�7q&0ܛT��z���\0�qH؇tI�|`6lIe�R�����M����^�.��5��u�e^�M���_\"���7_�'o+��0��s��Q�?�F������H�݋u�XR+�J̢O�\\�\r�7`�N��|Et��H��w�Y�_�ݻOa#b@��G�k��N��0�� S����U��������h7=\$�_�0c�x��7e2qtD�p��0��u�m�b���ر*]�^蝍%�7N�?�l/P�Z��wO=���gnV����ŝ� v�eթwn1a�^]��]<p\\�ޜ������&AzX4#�ݯ7د_=��k`Ћ}�!E�������^���Kطt2_�Cط\\IX3ط��ѝ:%՝�w/P�Q�.ę��Gݬ�A?�[��ݓ�'�wd\n9p)�Ƀ�r7vj[����@!��w^�R1�ڤ7[�����e;���q�m[�wkش����/5٢r�, ?b�']��r��[\r�/5�ݰ�ׯ�r�.��%�U�Zv-�l�6~��B�C��������G�h����S �ougM���p,�O?������D�d�mw�?]�u!� R�4�V�_!�xIw��^�H9_=>�}�0��ނ�)��=�H��O_=���dء���펝� ��d� 3��]�������_6:x�Dx7B��Ӽ\0�]�/�=�vI�J<A���X΁,M��e��;G\0002x|&\n�tS�����c4u��}��pv]\"9x_Ob�'S�K���(�K�a�D��X���Sm�Ǎ煇��3���_<;��T��M�����M�\rHo��*��_������-ޘR���'N�U:��׬��H�ڪ�%\0�5�g-t�\ri�k5���\n���`�Hs��ͯis�~qOo�\r�^m>\r��O�/f�&?w���	��r�3*}�t󵉏�<���wO����\\e�V&d%}!.����{J��fם&Fx�8+R���:�������,x���\r�^���2u��V]�qOs]��x=#C�I}��e���1bҚ�o�t�J�I�wP�\$v��<Fp�<���yN�\\�\0�=����+5��Pl��\nS�ȳ���Ӿ�\\6탿v��i��9q4�g#��|���Ԥv�z�K�޽��t�/�/��5���j_I��o%!7I��t��Q���k�\n�M�wLM���pu!0K��\n/W����]ڐ���[�﮴jz����)��}\\X�)�O���>}��S���8�@\0�1�G�{?��yU��a�G��/�/�K�~�K���-��O_7�}�*�Oa��{z%���/��,Ե�\0#�S���=�`�7��aZL����{�H^�>�p��7EO��\"�����/���{���=TN�뽏�]��G.Ԡ���W�������?]�O߿vLVFʝ�{�����J����Tmio�����@��xO�?z�/0O�Y�|.P�b|+ۏ����\n�JaW�/��+!���?���\\V�_�W��|��=�����O��ʆ\\�F�hab��oC��@��Oq�|��\nz��O�2��j=E�Q�I��������v07�4͈ͮ7�=�z����2�M���=)��M7[�!����j���J	��}�^�w�����m�hz�x9�{�ݶM����A��|��������>o�)�0����\"�Bx}40�K�(�\rmݹykߧ�1|�OnP�t�謔�{������Cq�ۏ�\\d�1ҟ�a.u���]t�7�cn��Y�DEV��d��5(}�	g׽u\r������<T��\\���\$�cԫ�*T����֜;�[���q{���]U}ն	�_t�����5ud���kmq_޾7�K���>�+y{��&�a?�>N,��5�Q���^��}�ѡ.}aEr>��+�̧@������n���?ce|X}'o��c�e_�vo��{��}-�x�~S�_���=�wn\0;�=��|��'�8�����']��{CÏ��n�?��[�uI�ֱ4�)~���=�uM���=��1�]>F\$�sR������O���\r���Mw{�/I��]��?��5�����N���������6�u���R��yJ�� t��Z\"��p�t�������V_ȩ����f�sۇo�z�����f�ɹ���7̫���fV��AY\$���|��z��[n�P;��[�\0��[��/��eCҝ���O)w��U�BV�W�\\�����p�T��]����_���	�G��#�_v}��5^��M�/������\n�D���d��K�\0B��\$��Qz��j ����MD)5�đ�4�!V���\"�X���U��\$�\\!W:�2Oʦ�p��!V\r9b��ʰ	���U��1J\$�.��萧 ���p�@.�|����\0T��Y��)����%؈V\0���J��f��s\0R�%�f�p����4\n\0��G���V�����j��1�A�q\n�eȢA��tm4�����O)�Ay��?\r[�^tTD��@^G�y�H��ה@i%����\r�Y�8��+ʸ�_�(٠E,dW<@W���Ar�H_�\$��;|	Q2K��X�-��B�˵w��P�CYD�2�%��WT��]2s��o�Z��T�@)jurKo�-�\\L`��'Z�/\0���n�w�s���a��z�z���nk h�lp�Ua�K�W�6	\r���\n�i��� o��rV������9``:��z(.h��#-���<�N���l���[P�tK5E��Zx��d�p\n��+�%�\0	@Utix����X�p�j�7�aO�щZ�h�`NU��Ł�)�4Ҳ�È��Qk(��M� �Yb��-�D�\"�����8%@	�G�����.d�b����.\n�z�p�W�9�g&˰�C���\nв^g���i7��-XїV\0��\n�{ӆ,m��5�j�1Uk�\n����_���`-,���5e4����y�_\n�ق2���й�3����ɓ@\$l�����Fa�ߠ�2 f����\"�,Ba�-0�'Fȍ�.h-�iW�~U���� DҊ��`��ќ!V84̒�\r��+I���5�&L/X�3_��%�{��E�Z�����%�s(�6�MQ�9���q�Ytf��غ��&\\M��:86\rั�_A~X\n\n�X�����M!��5�̑��1p����<v-eg��5�����Uk3�\0�%?���<���-</�d��<�y@�M��0-&��� V�h	��\0��|�c?h6c`���+s�\r�&!0���gj_�=�3��P� �Ã�ٔ�CE3P{ج�B\\&�l(7@�Q������\r��'0���B'b�eXl������A�'N2{��m=�x��c��*�>�	l�Zg4Gg��\r�)�\0	��\0AP��A��%���j\0\"�Gg�	���F/��:��d��y��yH6����kf2���dfK�r`�B=��ź{=F~̗��b�aUt�}�!B��	�&�<̗�D2^f[���%�?F	�/�c���� ���Y/B	���\0(AmB�?���\rt*�plȡY\0+�4�Y��!�V��I�B#�w��'�EI��\\��^&�?(M\"�Ä���z��ZZ�0\0M�j�\r��-HW!4BC_�	 Y;8I���%i����1��a����'z�]�;�7L�	��e*��霢��+�la73�����/�_p�X�A�c������na}���r�(�:����AمU��1&&́a�'F��3��n�K�/`�����S�X9A�cu\n�1�_p��1�c?\n���U��a����(���\0�h���B�cu\n�3&2lL��G����\\4xX�a!d��j����4�V�g�)�Ն�	��-�6з�!�+��	\nk�7�#Xޯ�6Lݍ�ZF�m2��(V���\"�E�uX2�\0c�Uų�5&;�,X\0Kl��`=32VB���`�gjz�4�� �	x׶�*��\n~�y���Z��h_t�5�{/`�\$*�^�	Q�\"X��4�d��h֤!��L��<��f�V@��v�e���[b5��!�G��X^0)����<&H����/����D�0 A.7l���F��T����!9�K򧑾�~��L҂�\0�My�\\=�*[�0.�wZቚ�Zke�3Wf�U�+8�Ud��2�_�����W�-��SP�Y�P����F*mH	,+6fR��Y�3a'�#<�a\n�UWB	L��=0�ɼ\$�'Rȭ|TVH��I�A�Oz���v}L����4��ن�?v��4�g�N���%V.�\0�D;lz�`<�	؈����DKgQR\"s9�WQY�4lzν��t����h��uz�&�c����&\$���cD��!�04^�4M!�35�%Wh�lbZ]���?�b��Z{�'��1�\"�>�'٣��iΕt�#\$h@IWZR��.�Ҥ�YA&��ҳ�_\n�ɝ�E�eL.Y�ćH��,�3N�tk�Z|'i���\"f 90H'���z���0�Z��)'�ԮLk͏������Ҳ�BF�a1O\0��+ V&��	�/�*��\0��XfW���0��]��[x?M)��2�F�!�f��iI�\n�ܤ,Ԡ��ۙ<�0��,X]��Λ�\$t��B#t�խ��SHh�������~\\\\*J)�n�D�N�DѼ�nT���bA��	R���!{u���)�^��mz�E!�nt�f�[{6�q֤���yF�ƀ���/�\n���\r�B:<I\"�\\*�M�hC�[o>�f #�p�n	C�K�	q<:`6ߎ�EK4\$,ة��@���wUQ�4T\0�m�Ԉ�\\ZD1ٸ3�%>`J���1o��q�{/�|��kr�c�k��j|�q�oX��&�aF��Y�%4�~+\$>��;^��|[1�w8�����vz��C�� �@�7�o�:�	H��!���Ŕ�4�`\r�JaD���|\"�M�XPp���ܻ؋N�Y�hX��O��R�25��;8){�(�>0إ*A��\n)����L�-�ba/w��0����JG8.C\"s�1m��@*�T����\$�w>���L�!�M��}��6���W�U�����,l=1!Qh����/�-���i����\n��<_�6�GF�Fp��:����b�6-�܉*����7[V��qH��:G��΁FFqJ���\nJR��-[�'�Ip\$)%Ìd��I��v�d�� 鉣�q@�LS�ɇ��/� E�p��1�ɀH���SN�y�dM�#F=Yړ���&�i_�Q\$߱�Z���A��ddrƼld\"�~�!�;n��e�{�ЎQ�ޠ�j	��q���F�ΌsFrwR�ͶR���q�Q��q��**�pJnL�7 Y���'�����Fn؆�,^G�F\\�(��{V���s������;L�ۉhR��ӂ��K��J��5җ���#C;>�Sv5[���#O�~����TX���e��@�)��4����ݬV��.�8LTU�����F����Lف�cdF�|�8�l��ci��T~��6�k���Uqn�4|n7��jR�Eo2��!�n��)�\0�����!g4@c�6������',o����=6��B8����^�=�m�20�����KxF!��TȦ�qX�Qu!�N����dh�)��?jtB�2*(�.�����RAH,Z�'�QB��&\0֛�9������݆��v�4çx������mʞ:=x�nޫA&����:[�gbU�t�QtN��:Z�n1Q^��[Cv�pS�(禮���I�8X(����*������TQ���#��ݎg�/��8�\"_�iq;��G����?�ҋ���w��n\$c�>P��m�����U㽀d��qۤx�I���G������y���̟��i	�PQ�9�����Ys����d\0�]<Ǳ��)���L�c�AX��/[p%D@J��u|ziA;������,u����\\|�������~CB>�8�`K#�ƅSPۉzO�Q�\0���W*��M~E�\nc�Ǹ#��>��t�1�c�G��	I�}����R��x�5������S�T*XM\"n;J\\�L��+\$�X¨̭��{1k��IJ\$\"Z@��u����8_{��u�k�.��t�ݏ��>���2	������d�PS QNg�v����0�s��jd��x��_o��v\\��mĒ��,FQ�����|v��^�SY	� c\$�|v�}�m��2ǻ�m��Z9&������-��B.�2H�\0�=<ؐ�����Dd3�bn�&�l����J����m>���q#� ��H\\j	4�@x�C�C�z�Sl�q�I�?�\r	'�����6GtG!J\\^Dkŧ�@�b����T�u'�(ȉ>�����ܪj�R?��HQ���2˴�ڟU'�]��Rl���/+�1�/O֞�F������%}���:iG&��Z�\0n�	��`)`a���\"fQԋ�w�6��~*�#�1�8�'�n����x/dc��`�WQ>���Ї�����#�K���N�\$N�!O(��@G��y���E�\$)F[{!������T #�J�|�����A�X%��o�޼�w|�����Q4n��`�ᑨ�Q�c�eu�s�2���#\\�g�Ij��Gjj+Tv�ׯDc�I*x��0������2\$�}j��?[�t�q�_\"�h%�� ;�W�\n���#Dj&��L�D���t���S#F@��'�N�K��p��E���m�\$cIQ��ۍ�����/MR,�z�a�4�����b��O���R{���)�Ly�`s��0E#܊��������)2��?�tK|����gO�`�:E��&f=��h�\0RR;uvr��۶�#o�\$�9I�g#RKd{��]��y���ك^Y.����I���M;��\0�'P^�<#�6�*����|�����nGYI�S��X����tK�N��{2��Hb�UD(ޱ�����3�ii!o��MID|�����Rzޑ'w��m��G 5)�O?�Zq�P+���\nF%�?W[\"�Ph���W�\$I�'���?��TJ�,�nJ��G�G������\$؟�@��*����T��`�R���s�	�Q�G8��eF˔T���\$y/�]�^\0��R3��oi�#�))F���@n�e)/Y=#RR����+`c7ȆT��`�?��iB��F�v������� �%'�Q8�@/�*����^��4����ؤz�	=\$�}\0�M�<Z�O'�\$��mB���0NA\r>�����\$�gJod�+��M�dr��2��ۏ�(�a�Z�S����1q9�?좉BNMRtʨ�@����_�U���8\$�BX�b?̫a�ͯ\0�>�uB��U�	W�n\0�%�K �>R�HN��4:�I��8}\"x!u�G�I=��)	�c�w�Ý��U���e��(�D}@��J���4c�L�1�l�v�?W|�T��^�����e�BiώP�ٓ�)�O���.�S�X-'�0Д�����N� .Xk�b\r�_��z|�aאּ�b�#����v@�NX��t��#%���ƃ�Tz�[����B��p�z�2Vd��B!�Vh�uT�IJ�U|x�H\0BF��Bπ@�\\\n9�FfI �������X�!\\��c,�Z!-C�te�	薸�Ԕ��	��՟�(U�P�ҴYjRї�+LD�\\�H\0j�զ�\\V�,�Z�ȢFB�2V�xx�Q7PQC�|%�-�q`�H��ˋ��'r� V�Z]�K�v�)_\\�)q����,��6o�. uv�垊�Ww�	\\��݋%Q�˓�0,��vr��m;�e.y�\\v�'��t^?-��9�aI���it#���q2���;>�{�[��������4�������+�e��@^�-N^0�y��`��^���≯)i��2���8�HE�-�B`c\0N%��6{�Ș��X�9��0�il���1�&p��|%�	�p��C��6�1�[\"u���C\\e�V��Xwl��4�&2��\0XI���/2�e��3\0��15W�A�a\0�aT6V��0�a0�`�Y�Lf0�U\\�i��0�rLZXB3�`y0��z�6k� ѱИ.N��Y:�0sX�3ЈA���4 �,eY�3�(Rρ�����L\$����,H�l��kgsR\"�I�`�o\"0]���#Cƃ1S4:hp�	��6�e�b41�e���U؍��Z3�h��\"4G�ZWW��(V��1V6�!YT5A�}��k+���&�9D�.�U}�;��-*��i�&\$�U�~q'bPD�(���ZF�1*	����=�Pr�q,Y�5g��zc�Q��]C���d�Z&Rv����0æV��#��\r�j5�eP]��CX���˰�+6�	��Y�������HH<a��j�K\"�T��PX�����?aA\n\$ę�1�&�i�;)7�H�.2'+iT��.\"��l\nڡȤV�cO3��6�mhD/3n4�|��>XHR�}&so�Ei\rRׁ+l%!�p��\$���7N���0�^p�M۠O�E-z�g�%���1�!k�G�U�ǧs��\0lz�9��B.K�/��^=�M7f�Lߵ�rF�|	lї�����)O��ZR#�7r#�L��0)���'����\"�^aԕ�S*S�/��JMbu��q�!��W��	��sҔ&F��ё���Ɲ�8��3����(�9|��fQk����*��p��5���	t*��ͩ�=3�w�\nZO�������S\\�Q�5���N�h����#B-�M+�K�ӕ�+K\$=�v�6���9!�K[NH'ZX�VOD�;��&�M��B�FnTf��o�ҝK�0�N����U2�-����D�����귎��Q�n\$�e%�@��-�\0,\0��Չ��%�-ZYt)f*��+>B�8f*�E���wKQ��8N<�B\\���Kv\\;7��tM��2�oLG�N��e�2�G@�V���(��@@\0001\0n����0�\0�4\0g8���@\0��pZ8���P@\r�N\$\0l\0��Yǳ���+��8��yǦ�\0�4��\0���ǳ�@NP��\0�q���S�'�@��ml����@N6�F\0�q�i�ӌ@�7\0l\0�q|�Ā@\0007Z�9p���s��=Nr��9���9����/�7��9��9�S��.�Z�o8���s�g?N.\0s9�q4�\0S�'G�P��:.t|�Y��'N.\0m8�Zo���8N:��8�s�@\r�6�M�A:�ud��3�g*Nk�I9���ד�'GNg\0`\0�����gΠ��8�t��03��RN8\0d\0�r���Π'aN*��:�ry�S�'4\0000\0k;:s�s��s�g_Nh��;vq��y�Ӝ�,N���:Ns���`\rрc�6\0�st�#�3��(�Ŝ�:jw������Nǜ�9�s���ͳ�'8Ρ��\0�u��ͳ�H��~�i;�r��Y�Ӿ�N��\r:�r��i���Na�\n\0�y\\�Y�ǧ7O	�-;w��iـ\rg��b\0j�sT�ٓ�g#�̞9t���\0@N��:JryS	�ĀN.�+8�ڀI��۝���;�r���@\r��N˝�:bst�	ᓓ'�S�W9�s��ޓ֧fNC��;�s��ڳ��0�J�M=jr���s��O{6�=�r�I��\0�9��9�z8 ��'DNܞ�9�t����S���OW��;|��i瓛�p�E��;yl��� \r'�N�\0e>�s��y���'Sτ�k<zx��yɳ�'�Ov��<�}�������џg:F|��i܀g�N�a>Jr<���i��IO��O<�{��Iœ��iN:�m<js����'n-t��9V|T�i�䧴N��#9�������g���	>�q��y��'�����9Bw4���S����3��;�{D�ݳ�'��T�[9y�\0��3��'�Z�c9V~��)�-��NI�%>:u������iN��m<D�!�����O\\��:�s��iٳ�'6�-��:v��3ߧq%�h\0�}�9��'�P@��<�{H\n���P4�A:y�\0�˳�@�d�k=�t��	�3�g�O���?Nu��Z	S���Q=�y����FN���?�r��9�s��Yι��>&s��9�����Q�}:xڄ�%4�c��};�u����3���t��=vMYʳ�H��D\0�:Jr���S��/P��i:^���߳�ͭλ��;~z-�Ӵ�0��:�q�����(\$N4�U<J��9�3��/Oơq=j~�	yɳ�'��L�U;<ڄ���ӝ�rO���:*����ͳ᧘()��>��������XУ��=�u�Z4�&����:t�\0��hSO���B\"���y˴5'�φ��9:{�\n\0����p��@sT���S�g8N��-?Vv<�ZS�Ρ�Y@r�Y�3�'�P|��>6��\r�蓽'oϪ�WCRz�����h�O\0�M9�s|�y�S��P\0m?:���)ʴ2��j��@�}������zN➛9�v��\n��@N�?�D،��	��-gP7�!@�q])̔='�P��;~e\0��s�'U�؟�Brv�y�'��N���9by4�\n�֧�n�9BZq��*S�g�7�S9��-\0I�'/Η�;9�{D��S��&O��WD�|T�j\"3רP�*a;��4'7�d��8����3�gѓ�=2r9��3�h�Q%��Frs�\0��tD��ώ�aB�x��	����N0��8��-	��_�K�4�Y>ޅ��Z,�k(Uя�D��\r�0��hQ��E:���S\n3���X�=	(9����O;�#;r���Z\0�g�Ы�'D����۔_��Oh�YGj��͓��,Nn�O=bx]9�TUPQ��@6q\$�z~�I���@^��	i��(�����Dʀ})�4d)P��TT����4^��\r��<���)�>�N��FΉ-!:4N�P�.�>�{����(�w��C���I�3�'&R!�cF����S��}O���>*���Y��\rh�Η�X�q�\rZS�g�N;�IE��=!�3t��:PH��D��U��t=�V#�M���r\rY�TK�\0�ԡo8�y����S�g�N��WG�m�TC�`P#��E�~}	ZM�>�K�z��Cr}\$�9�s���O%��9�khJQӤg�Q/��@�������P��#Hʍ���\n�D�PK�5F��5�\0%'�Q٠E�s���O���S����>V�m�;Th���^��B�	-��4<��Qs�)>� Iͳ��PB�};���&����NL�gD�my�t\\����K\"v���S�IiL�2��@BsE	�������U��:�tU�T�(��M��C�����S�)k����=�sE)Y۔b�vR��:*|��3�)=�ˢ�:2|��\$��'�P�U=:y����i9Q��L\"��\nt((|҂��;\"�uU�sR�\0���z�<����/mbʱ�Pze�1XG/�f��b�7)�T�YZ��'~�}���^,O�:��b8�J��i�Y�	�b��ى�5i��Y�L\"���V�\"�5�����c���R@Jl�����4<��)����SiMn�;�n���C�f����:�S b�l� �5�/)�LB`��	���U~�y����@�S��1%\\�8Jl�����a	Z��4�Z֩�1_���&:6�-=��#�P�^�SJ:n�\r)��^_�	y��7�~��W�t]*��tf�\"�mINm�4�i��`���lw��<��]�p��k��4~]PZi�4%����L2F�rD�	�0�&KL�,���p�3�V�-X->�6S������V�<85��Z��i�312e�ۃ�TG�������Q��KbJP3nt��µ�Qb��4��D�ܱ���Wk��f+�Y�SaU��͋yv��p�\0¯�&;P��16.\r����jb�b�����;\0\0I��ٕ��4`�ǡ�T=&�Lf�����͚�6\0�\0�EmDJ��р)��'���\$83;,B���[`�3��8�i����S�`�n�����ٻ�\0����V��\"��\0sk�1����@״����O��s_U������f���/�af��W�G�~���}5�m#يӧ�J&\"��Hڍ\0aj\rT��MPҤ�jw�#ؙ6c�C!�וּ�ޯ}�#M>��\n:���!�SV�_Rf��R�����]ße����3U&�T��by`�U8�OC����˖\rLBx�3i�3f&�J2'�\n�j;i����<�}\0�C'8˧�@�+J�,�nC���0�DW���\$ǥ>w�V�XVS��|��h��M� V\"��I�'ĩ:Vh��6֎,��/�f���\n�3Val^���kMO����Y-/Z��)b��\rM<	�0����&a�ڣ���\0M_?Q�'p���K�C�qTŋp ����ߴv�V��{TZi5O�ؗFf!U~�C�eURC��H	\n%��ShUU*����.���EM)�ǵC��R�����X�5���P��\$&��\r\n��0��QU�K7�UJ*�/jb'�,(�u?���_�U��W�k�Qj�4i����<�g\n��f��U8^�0���XI�[���]��M5��SxSJ�Zɰ<��U��f���Ǫ��h�CRU��9��Ъ���DfpMj�#46j�����d`��=Y6����ab���V�[dUp�U��7U�\\�D�V��U�b��q�MU��W�1�ąsV֭T*����ߩ���TҝK1��5e�2�Y&�́�[�PQ&\0�C��V~�SrcQ&%��dL.v��Q��d	�eX�^p�!3U\"��n�B��T�a}��cT��\r`ڒ�x@�ZcuT��Ǝ��*�B���WV�4/�uQ�L�����]bZh�SI��Ճ�UF\n�UB|U�f�X�UU��{깕WZ��լ��f�Ջ=V��c�y*ƌ�j�S��oY\n��-\n�l�+������d�<5a��}ewM��}Z:�ua���'���b���V�b�0���7Y��efj�5ej\"�(�EW���/�U*� �/�VY­=e����k0ց[V��Z�a}կ�C0��'rc���26�q�� ڷ��YC�!f�\r�i��գ�Z·�IW��ug\n�lMj��ͬ�Z���Z�A�j�Ut�_Wb�U3��թ����6�a:v�+UU�&������0�kd��0\r��g\n����Z����Y��g\n���ڗV\0&V6\r�Rj�5�k�쫠��{Oxw��+aT����\n\r�B�2m<*�A��1T�R��Õ�؎SF��T�\r�bzxK�+xA���U:�p\n�T��V3�\r�o��c��Ӭ{Zڷ5q:�����VD�EMjqZ\n�����VQ������f��u�Y\"U~��\\f�S�u�d��Vb�eV\"�;�䕠�7�;��0���gJ�0٫;�F�a³�Y�������QY��%t��u\n3����euh7U���֑�h�ƥ�if~��+�֚\rZq|K\"i,�	ռ��V���u��5�kQ�C��\\���\\��uݪ�V�eF��qzʕ�+�֓��]�i�k,+��3��[�5t:�U�*��C��]⶜\n��,*�SB�k[n�m�5��W������3�y�CkqC�h6̙|%`��������}��@��@\$׺�-X:�sOv\0pw�W��FV9�\r3��0J�W�&�]|\rDP�H���\0V*�0������昬���0*���֌�`W���N܊�\r�~Wӕ�̪���R���9:��5�̱Xa�~�;p�-����&�_8��S�Ɉ�O{N�e{�?V��\0��N���\rp\nm��aW�g�O�݀�\0L�ɜ�\rg�`v���x,Y���Y`�����)��5�ZΡ~�}y��Y�/��\0��l��1f�m�C�38'}_&��:9�13, ���.�N�	4�����@��inO@y�`�X��U�=����Xl�G�aP<�<�\n��hTC��Oś�v̖k�B�����u{�} ���ݬXOf������l;�Y��aګ9=�u��*X�&�[ɢ�|���,@�Y+b\"��>���I쪳ay\\)Uu9\nz�j���&�`�uL��Q,����	a��US\n�v#lH���;b6�Պ���ZX�b�Oձ儦1l\\���8�~��&��5��S�z�޺�P�0��4���2�;)�~\\X���g~Um�u��-��k�X۰�ca�Yv�PplqX�b���°V-�:J���g%T<�t�V��Uf&L���O�5``��H_�c������S�WϨFM��؈�bcӺ�v��}�vs��	��x���rȓ3ڋ5I�l�\$�*��B��n@1p'Oa���9�'l���h�Pm�2{6�4�(mOf��CV�l���e,�z��UVuY�T?�J^�+:0���\"0'��c��U�(�W���Th7X>���eW�}I�5���eX�1��j�YY�zN�\r�{#vZ�kA��\0��\r�T�5��=��M�ɣ���U��Ů^�L	%��:�;Z�f\$&�H��L\n*9U�h9_��������Սk[a|A[�a��Uh�vU�\r\"�6*�T�D�e�Rr�3�s�\r�@���RLJ�}��5V7��Y��=e��u��\n�;,�ٻ�`� ��7�o�)Q���y�\n��N2~Fj�=��}�-���T�^�gJ�\"@J��OZ��\nj@i��;�2+�Aܲ*����{�;�ꬳC:� ��L\"k�\0+�D��=��[L��ճ����d�\r�d̉�2oe�`��=���wjԬ`2�q5���B�E�\nbsR��=T:�Pl�Eĸ�~�N��Kf��YY�Yʦi`�{�-f�bl��v�oc|=t ��gְ��A�L�rV�+�ٿ�jM��;X�����AY���;[H��\rp�ͫ-g����Y�m1Ʊ�_��Tz�9X��a�������w��@+2��c^\". \n�f��`{]����cج�s�`�����O\0�3n^Ӧ��J2m���fڂ&�ξ��[D�-���Aj�-�X�֎�t����U��5��Cj��g%S���bc����ډ��1\n��;�b�Y�V~a1j�ݝP��v08`L��햒dw*|��i�˵���E�k�VY��(�Ȣ06 �@!�M�kͬ���-\"�G��\0��(PSQ�W3�s@0ŭU~�9\$�#���A:;*Yh���*ٛ�-n[�ls�Ƞ� �!5 1���ɀ.#����3ֿ��E�<�J\\'0��l��\"�4����P̕�0?\0001�d\0^\n����Ĵ�g<\0\\���TV@���Y&�4�%�;\"]m���\n�l�r��¼���/\0kl�	���e!K�[8�Ml�)x{g��-�\08��l�٨P6��[@��\0��b�ཇa�c�Q6AT	)Ԑ���[X��mX7m����-��T�omn�ʹ�e6�-��h�m����KnQm�[S��m���Koa��{�m��-��Ֆ�m�[D�mhYm��mA{)\$@��m�ڐ�I��ې��I!e�[k��-�ۚ�nJ����I�m��o�m����Iѭ�ۗ��n��\r�j�-��R�nV�0��V�-�۟��nf�-�I�V�m��]��m��-��\$��-�[·Qo&܌�+u��-��R��n��ISoV��[ڷon�ލ��{��7ۄ��m��-��qV�m�����mv�-�K}�����ȷ�o��]��|�Э��巧o�ڽ�[z����o��p�M�k{���\0����5��|g�[���pZvE������\0[a�7o�ܵ��ev��\r��'m������6��� �sD����˅\0In\\2�Yn��;y�	��\0��D�����e4L���op�5�9��\n�����r���4����pf⽿ˁ��(�A�mq*���S���&[D�1p��4�ۆ7\n�%O���o���	�7�-\\O��<�������2n2�]��q����;�t2n�z\0�C&����v�m��ɸ�q��8[�6�hd�E� mB�E�{�v�.L\\��?rb�]�K��#n+\\��qrrv=���W\$g\\���r��������!r������(m�\\��n��ț��-nܫ�kr�:��k6�.e[_��s6�-�ۘW3nb��Yo>ܭ�ۏ73nK-�Ycm>�e�{o�3�q\\ ��qJ�}�{�78�i�⹫s��E�{�w8�n�ֹ�r��M�zHw8n{���q��U�뜗=�v����s��ϫ�w=�\"�#\0�9�t�0�C����8�vL�k�S�.~,���p������n��i��t�Ҍ�ۥ�(��+w<.�p:7Lg8\\���>*��;�7H��N.�ss����ۧ7A��	��t.���k��L.�N:��t�]��ӹ��D��u�ԋ���.�No��t��櫫�T.o�d��t���{��V.�N?��u*���{������<�M9b��+��Z.��V��u��L⻮w\\.�NU�i8��=�k�3����z�u��]��w`.�Nh�v�1������݊�Y:J�����d.�ݒ�?vR�|�˳wf\r(�/�b���dk�g\\��el��˰\$�i\0��@�iv�q��K�&�@�]�&\0��ݝ�k�W8��]Ĝ�v��E͕�j\nn�]���wB�5�k� ��]ƻ{s���+�wp���e�_>��=�Y�3����ٻ�wnt��{�ӌn�]仏Gj�{s��7Y.�P@��v��ݹ�w~n�Ҳ�_t���������]Ğ�x�{��v�(��u2�L���k�^��x6�l��×�g����\0�� �Ht��{]���tV�H��s��GO�Ox�\ru\$:��gl^�uz�����X� � ��u��\r��W�n��n��x��뮗���]���yJ�T�+��-�ۻyJ�,�[��c/O��xFr�;�wf�]���yV���k���.��0��w���ի���.��T��<���� ˷n.�Q;x\0�=���p��z&\0v�\$;�7xV]�C�czV�}��W�n\nޚ��zz�T�kӗ����C�#w���[�w�oG��5w��}ީ̷�'�}��z�� e��6� ��ps�k�_]pK]k��6��Ζ}t9���l�x��[�\r���l:�WWC\0�Ou�\rsXe0~�OA�_�]��2�s��WC��T\r�H�u�X�\0Kk']�Z��\0ض�]��V���{XBu��}�{����f���\$�x�y_J�tLj�L(���Y�%[2��t�7V��W�g����F���I!�P�Y]���%\nv�	*TBZ.�Wš):Zf��%��YUV��Z�7��:VAf�h*�����C�l�U\\���ʅU|ogׄ�)\\��;{ڵ|ok�X��M���Ě�7�a���`�\nh�\$)���or1k�_\n�{!�����>���{��4��@\n�рD�V���^K�5��R��e�Vƥ�wc�p��2^��0���5K�7�a4Ww��f��-�p\n�a\\�ƫ�W��d-!�E*�_��}.��-�d۪�XG��ɂ��8�rʲ^0��ZJ��]��7�X��p�!Tf�\r�h`�͗�X��|겫+V���Y\r�y�k}�mmJ�1%�W�>�s�鷶a�1��~����K۷�:Vv�+{rd�FJ5_�0�����!?*�ڬy�9�Z��D���o�����\n�P���������N8[���!U筑Y����v��e���&`��F�o�2��0��7g,u-KXꇈ!��161����6�Ρ�j���,�_��CQ���E{�\r�چT���z��L[��3�k�{f���BRk� ĳ�r�¨d�!�Ynó�ES�'h<fթ�V��\nt����`�)ml�z��%Qƣj�����\$=���zi�i/4�[	Ŋ�Jj�2Z��.�����o�<�s6�E+�_�w��HZ}�Ύ�0;�0������-����`mޟe����#����`�r��<��jp�٭g6�� HP&�&\0005��q<�\\#~	�ͥ�-����3#����\$�2�_����`(���S��\0�'8�� �.�Q`�p�K��̇j&0N��~���a�\$�*\"���Rl0�����q��p������P7�Ւz�\0�`3���^�`}��%�ۺy�`x�:�\$�PUS6�x<F�`�+�~=AV�\0=6߃��{��Υ��u_\"��r���\0¤l����N@(�D\rp�1+��@UF�7/Mb���.\$�{��C0�`�L�6�\$.�K�;]����m@gk�a�`��P�	�w� M�T�6�s�ۻP�p6[�^�p\0/�8�-�N���\n�l&AE⚺�!\$�G\n�\$����#��AT�L��#���}v酌���E��i��0;�,,�p�F�,Y��@?�^[�H3��ø�.�J��H9�_\n���*T�}HFZp��f����ba��j�Q�� 0�ڟ t~tX����xd������;�h���06E�ӆl���g�A��-���C\r���5���Oa��d��`�'6�k��\\��	x��8-F�Vp��ab��e@�ɪo�Q�0E�f\"B�;�*nA�m�*p,;�u@�a�6R�qd����SL.l=cf��,������Qf���!�-�g��2�d���ͣ&Sw;`iN`��v��\0�\"�R� ���v��a���!6eHQOO0��>�!ݾ4X�b���+`�{���;uwb�R�ȚT;�ߎ��CP�V#�4)]\r��7��l�c�db�|B�>�ą���K��Hx��bď��\r��@���ėl�B�%,8��bY���I��%Kҍ��3Ġ�	�a���/q!��q\r�h2^&�����bt\0��Q[+�8Êpȍ|��Y�N'�\0��A6aa���,x%�\$8hA��y���q�nR�AM@������R��qC�\"\n�}&��b��w����tptv�i�b�@Ő�|��<W�B�f�`��?%'tW�/���d�m�����LOb�q`H��5����#���]�F\r.��+�*��oB�8E�Ũݣ>W��~1O����+<��#�����\0��0��3غ��`X\r@��00�]�y���pD�ë`apR���[���+uP �@;:�-γ�\0f8�e�c�lU�v0�e6\0�bܵ��=���b���Ӈ4��х��(�\$0y�G��0<�d@��+#�AK�o�#.H1���j��P�g/�Q�G��S5%V�Ҏb�t����\0Nׂ�TLJܱ��Ɖ8J�p�pEm���r��5`9�M��C\0_��'�\$��#��E�!\\��a�)��s���ې���1��\0����r�n��q��e����7�\n�Nt#Yf`�����@Fq�����L�z4Լo��%������[�Z��Ď\"�g���q�MIV靿�f)<]���o!'ʗ@V�Ϧ�\0");}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo"�PNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6��\0\0\0000PLTE\0\0\0���+NvYt�s���������������su�IJ����/.�������C��\0\0\0tRNS\0@��f\0\0\0	pHYs\0\0\0\0\0��\0\0�IDAT8�Ք�N�@��E��l϶��p6�G.\$=���>��	w5r}�z7�>��P�#\$��K�j�7��ݶ����?4m�����t&�~�3!0�0��^��Af0�\"��,��*��4���o�E���X(*Y��	6	�PcOW���܊m��r�0�~/��L�\rXj#�m���j�C�]G�m�\0�}���ߑu�A9�X�\n��8�V�Y�+�D#�iq�nKQ8J�1Q6��Y0�`��P�bQ�\\h�~>�:pSɀ������GE�Q=�I�{�*�3�2�7�\ne�L�B�~�/R(\$�)�� ��HQn�i�6J�	<��-.�w�ɪj�Vm���m�?S�H��v����Ʃ��\0��^�q��)���]��U�92�,;�Ǎ�'p���!X˃����L�D.�tæ��/w����R��	w�d��r2�Ƥ�4[=�E5�S+�c\0\0\0\0IEND�B`�";}exit;}if($_GET["script"]=="version"){$o=get_temp_dir()."/adminer.version";@unlink($o);$q=file_open_lock($o);if($q)file_write_unlock($q,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));@ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",HTTPS,true);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$ad);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("precision",'15');function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=$u;return
call_user_func_array('Adminer\lang_format',$ua);}function
lang_format($dj,$Jf=null){if(is_array($dj)){$Ng=($Jf==1?0:1);$dj=$dj[$Ng];}$dj=str_replace("'",'’',$dj);$ua=func_get_args();array_shift($ua);$md=str_replace("%d","%s",$dj);if($md!=$dj)$ua[0]=format_number($Jf);return
vsprintf($md,$ua);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$F);abstract
function
quote($Q);abstract
function
select_db($Nb);abstract
function
query($H,$oj=false);function
multi_query($H){return$this->multi=$this->query($H);}function
store_result(){return$this->multi;}function
next_result(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($nc,$V,$F,array$bg=array()){$bg[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$bg[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($nc,$V,$F,$bg);}catch(\Exception$Hc){return$Hc->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($H,$oj=false){$I=$this->pdo->query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
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
fetch_array($tf){$J=$this->fetch($tf);return($J?array_map(array($this,'unresource'),$J):$J);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$U=$K->pdo_type;$K->type=($U==\PDO::PARAM_INT?0:15);$K->charsetnr=($U==\PDO::PARAM_LOB||(isset($K->flags)&&in_array("blob",(array)$K->flags))?63:0);return$K;}function
seek($C){for($s=0;$s<$C;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();static
function
connect($N,$V,$F){$f=new
Db;return($f->attach($N,$V,$F)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$wd,array$dg=array(),$z=1,$D=0,$Wg=false){$ue=(count($wd)<count($M));$H=adminer()->selectQueryBuild($M,$Z,$wd,$dg,$z,$D);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):""),$z,($D?$z*$D:0),"\n");$oi=microtime(true);$J=$this->conn->query($H);if($Wg)echo
adminer()->selectQuery($H,$oi,!$J);return$J;}function
delete($R,$fh,$z=0){$H="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$H,$fh):" $H$fh"));}function
update($R,array$O,$fh,$z=0,$Rh="\n"){$Ij=array();foreach($O
as$x=>$X)$Ij[]="$x = $X";$H=table($R)." SET$Rh".implode(",$Rh",$Ij);return
queries("UPDATE".($z?limit1($R,$H,$fh,$Rh):" $H$fh"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$L,array$G){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$Qi){}function
convertSearch($u,array$X,array$m){return$u;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($Dh){return
q($Dh);}function
warnings(){}function
tableHelp($B,$ye=false){}function
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
indexAlgorithms(array$yi){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R)."
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'",$this->conn);}function
allFields(){$J=array();if(DB!=""){foreach(get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(JUSH=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->conn)as$K){$K["null"]=($K["nullable"]=="YES");$J[$K["tab"]][]=$K;}}return$J;}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$F){$this->link=new
\SQLite3($o);$Lj=$this->link->version();$this->server_info=$Lj["versionString"];return'';}function
query($H,$oj=false){$I=@$this->link->query($H);$this->error="";if(!$I){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Result($I);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".first(unpack('H*',$Q))."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__destruct(){$this->result->finalize();}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$F){return$this->dsn(DRIVER.":$o","","");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$F){parent::attach($o,$V,$F);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){if(is_readable($o)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a"))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){if($F!="")return'Database does not support password.';return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");}function
structuredTypes(){return
array_keys($this->types[0]);}function
insertUpdate($R,array$L,array$G){$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Ij));}function
tableHelp($B,$ye=false){if($B=="sqlite_sequence")return"fileformat2.html#seqtab";if($B=="sqlite_master")return"fileformat2.html#$B";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$Ze);return
array_combine($Ze[2],$Ze[2]);}function
allFields(){$J=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$J[$R][]=$m;}return$J;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
array();}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($H,$Z,1,0,$Rh):" $H WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$Rh."LIMIT 1)");}function
db_collation($j,$jb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($i){return
array();}function
table_status($B=""){$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){$K["Rows"]=get_val("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$J=array();$G="";foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$K){$B=$K["name"];$U=strtolower($K["type"]);$k=$K["dflt_value"];$J[$B]=array("field"=>$B,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($G!="")$J[$G]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$B]["auto_increment"]=true;$G=$B;}}$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($J[$B])$J[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));$J[$B]["default"]=$A[3];$J[$B]["generated"]=strtoupper($A[4]);}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$ii,$A)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$J[""]["columns"][]=idf_unescape($A[2]).$A[4];$J[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$J){foreach(fields($R)as$B=>$m){if($m["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$mi=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$K){$B=$K["name"];$v=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$Ch){$v["columns"][]=$Ch["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$mi[$B],$qh)){preg_match_all('/("[^"]*+")+( DESC)?/',$qh[2],$Ze);foreach($Ze[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$J[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$J[""]["columns"]||$v["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$B))$J[$B]=$v;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$p=&$J[$K["id"]];if(!$p)$p=$K;$p["source"][]=$K["from"];$p["target"][]=$K["to"];}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Pc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Pc)\$~",$B)){connection()->error=sprintf('Please use one of the extensions %s.',str_replace("|",", ",$Pc));return
false;}return
true;}function
create_database($j,$c){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$Hc){connection()->error=$Hc->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases($i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($B,$c){if(!check_sqlite_name($B))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$Bj=($R==""||$jd);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$Bj=true;break;}}$b=array();$og=array();foreach($n
as$m){if($m[1]){$b[]=($Bj?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$og[$m[0]]=$m[1][0];}}if(!$Bj){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$b,$og,$jd,$_a))return
false;if($_a){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $_a)");queries("COMMIT");}return
true;}function
recreate_table($R,$B,array$n,array$og,array$jd,$_a="",$w=array(),$jc="",$ja=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$og[$x]=idf_escape($x);}}$Vg=false;foreach($n
as$m){if($m[6])$Vg=true;}$lc=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$lc[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$Be=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$og[$d])continue
2;$e[]=$og[$d].($v["descs"][$x]?" DESC":"");}if(!$lc[$Be]){if($v["type"]!="PRIMARY"||!$Vg)$w[]=array($v["type"],$Be,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$jd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$Be=>$p){foreach($p["source"]as$x=>$d){if(!$og[$d])continue
2;$p["source"][$x]=idf_unescape($og[$d]);}if(!isset($jd[" $Be"]))$jd[]=" ".format_foreign_key($p);}queries("BEGIN");}$Ua=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($og[array_search($m[0],$og)]);$Ua[]="  ".implode($m);}$Ua=array_merge($Ua,array_filter($jd));foreach(driver()->checkConstraints($R)as$Wa){if($Wa!=$jc)$Ua[]="  CHECK ($Wa)";}if($ja)$Ua[]="  CHECK ($ja)";$Ki=($R==$B?"adminer_$B":$B);if(!queries("CREATE TABLE ".table($Ki)." (\n".implode(",\n",$Ua)."\n)"))return
false;if($R!=""){if($og&&!queries("INSERT INTO ".table($Ki)." (".implode(", ",$og).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($og)))." FROM ".table($R)))return
false;$kj=array();foreach(triggers($R)as$ij=>$Ri){$hj=trigger($ij,$R);$kj[]="CREATE TRIGGER ".idf_escape($ij)." ".implode(" ",$Ri)." ON ".table($B)."\n$hj[Statement]";}$_a=$_a?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$B&&!queries("ALTER TABLE ".table($Ki)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($_a)queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));foreach($kj
as$hj){if(!queries($hj))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$B,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$G){if($G[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Nj,$Ii){return
false;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$jj=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$jj["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$Lf=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($Lf?" OF":""),"Of"=>idf_unescape($Lf),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($R){$J=array();$jj=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$jj["Timing"]).')\s*(.*?)\s+ON\b~i',$K["sql"],$A);$J[$K["name"]]=array($A[1],$A[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id($I){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain($f,$H){return$f->query("EXPLAIN QUERY PLAN $H");}function
found_rows($S,$Z){}function
types(){return
array();}function
create_sql($R,$_a,$si){$J=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B=='')continue;$J
.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($Nb,$si=""){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$J=array();foreach(get_rows("PRAGMA pragma_list")as$K){$B=$K["name"];if($B!="pragma_list"&&$B!="compile_options"){$J[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$K)$J[$B][1].=implode(", ",$K)."\n";}}return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$ag)$J[]=explode("=",$ag,2)+array('','');return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$Uc);}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$j=adminer()->database();set_error_handler(array($this,'_error'));list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$this->string="host='$Md'".($Mg?" port='$Mg'":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($F,"'\\")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$this->string
.=" sslmode='".$ni["mode"]."'";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($Nb){if($Nb==adminer()->database())return$this->database;$J=@pg_connect("$this->string dbname='".addcslashes($Nb,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->link=$J;return$J;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($H,$oj=false){$I=@pg_query($this->link,$H);$this->error="";if(!$I){$this->error=pg_last_error($this->link);$J=false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);$J=true;}else$J=new
Result($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
warnings(){return
h(pg_last_notice($this->link));}function
copyFrom($R,array$L){$this->error='';set_error_handler(function($Cc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$J=pg_copy_from($this->link,$R,$L);restore_error_handler();return$J;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->orgtable=pg_field_table($this->result,$d);$J->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$J->type=(preg_match(number_type(),$U)?0:15);$J->charsetnr=($U=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->result);}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$F){$j=adminer()->database();list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$nc="pgsql:host='$Md'".($Mg?" port='$Mg'":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$nc
.=" sslmode='".$ni["mode"]."'";return$this->dsn($nc,$V,$F);}function
select_db($Nb){return(adminer()->database()==$Nb);}function
query($H,$oj=false){$J=parent::query($H,$oj);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){}function
copyFrom($R,array$L){$J=$this->pdo->pgsqlCopyFromArray($R,$L);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$J;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($H){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$H),$A)){$L=explode("\n",$A[2]);$this->affected_rows=count($L);return$this->copyFrom($A[1],$L);}return
parent::multi_query($H);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f))return$f;$Lj=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Lj)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Lj);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f))$this->generated=array("STORED");$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$zc=$this->types['User types'][$m["type"]];return($zc?type_values($zc):"");}function
setUserTypes($nj){$this->types['User types']=array_flip($nj);}function
insertReturning($R){$_a=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($_a)==1?" RETURNING ".idf_escape(key($_a)):"");}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
slowQuery($H,$Qi){$this->conn->query("SET statement_timeout = ".(1000*$Qi));$this->conn->timeout=1000*$Qi;return$H;}function
convertSearch($u,array$X,array$m){$Ni="char|text";if(strpos($X["op"],"LIKE")===false)$Ni
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$Ni~",$m["type"])?$u:"CAST($u AS text)");}function
quoteBinary($Dh){return"'\\x".bin2hex($Dh)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$ye=false){$Re=array("information_schema"=>"infoschema","pg_catalog"=>($ye?"view":"catalog"),);$_=$Re[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($R){return
get_vals("SELECT relname FROM pg_class JOIN pg_inherits ON inhparent = oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 1");}function
inheritedTables($R){return
get_vals("SELECT relname FROM pg_inherits JOIN pg_class ON inhrelid = oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 1");}function
partitionsInfo($R){$K=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($K){$ya=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $K[partrelid] AND attnum IN (".str_replace(" ",", ",$K["partattrs"]).")");$Oa=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Oa[$K["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$ya)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$yi){static$J=array();if(!$J)$J=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$J;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Qa;if($Qa===null)$Qa=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Qa;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)?limit($H,$Z,1,0,$Rh):" $H".(is_view(table_status1($R))?$Z:$Rh."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$Rh."LIMIT 1)"));}function
db_collation($j,$jb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$H="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$H
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$H
.="
ORDER BY 1";return
get_key_vals($H);}function
count_tables($i){$J=array();foreach($i
as$j){if(connection()->select_db($j))$J[$j]=count(tables_list());}return$J;}function
table_status($B=""){static$Fd;if($Fd===null)$Fd=get_val("SELECT 'pg_table_size'::regproc");$J=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Fd?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".(min_version(10)?"relispartition::int AS partition,":"")."
	current_schema() AS nspname
FROM pg_class c
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$ra=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);list(,$U,$y,$K["length"],$ka,$va)=$A;$K["length"].=$va;$Ya=$U.$ka;if(isset($ra[$Ya])){$K["type"]=$ra[$Ya];$K["full_type"]=$K["type"].$y.$va;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$y.$ka.$va;}if(in_array($K['attidentity'],array('a','d')))$K['default']='GENERATED '.($K['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$K["generated"]=($K["attgenerated"]=="s"?"STORED":"");$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['attidentity']||preg_match('~^nextval\(~i',$K["default"])||preg_match('~^unique_rowid\(~',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(preg_match('~(.+)::[^,)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$J[$K["field"]]=$K;}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$Ai=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Ai AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname, pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Ai
ORDER BY indisprimary DESC, indisunique DESC",$g)as$K){$rh=$K["relname"];$J[$rh]["type"]=($K["partial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$rh]["columns"]=array();$J[$rh]["descs"]=array();$J[$rh]["algorithm"]=$K["amname"];$J[$rh]["partial"]=$K["partial"];$ee=preg_split('~(?<=\)), (?=\()~',$K["indexpr"]);foreach(explode(" ",$K["indkey"])as$fe)$J[$rh]["columns"][]=($fe?$e[$fe]:array_shift($ee));foreach(explode(" ",$K["indoption"])as$ge)$J[$rh]["descs"][]=(intval($ge)&1?'1':null);$J[$rh]["lengths"]=array();}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$A)){$K['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$Xe)){$K['ns']=idf_unescape($Xe[2]);$K['table']=idf_unescape($Xe[4]);}$K['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$K['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="information_schema";}function
error(){$J=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($J);}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" ENCODING ".idf_escape($c):""));}function
drop_databases($i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$c){connection()->close();return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$eh=array();if($R!=""&&$R!=$B)$eh[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);$Sh="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$Hj=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$eh[]="ALTER TABLE ".table($B)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$Th=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) STORED~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($Th).")":"DROP DEFAULT"));if(isset($X[6]))$Sh="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Th)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$Hj!="")$eh[]="COMMENT ON COLUMN ".table($B).".$X[0] IS ".($Hj!=""?substr($Hj,9):"''");}}$b=array_merge($b,$jd);if($R==""){$P="";if($E){$eb=(connection()->flavor=='cockroach');$P=" PARTITION BY $E[partition_by]($E[partition])";if($E["partition_by"]=='HASH'){$Cg=+$E["partitions"];for($s=0;$s<$Cg;$s++)$eh[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $Cg, REMAINDER $s)";}else{$Ug="MINVALUE";foreach($E["partition_names"]as$s=>$X){$Y=$E["partition_values"][$s];$zg=" VALUES ".($E["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Ug) TO ($Y)");if($eb)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$zg";else$eh[]="CREATE TABLE ".idf_escape($B."_$X")." PARTITION OF ".idf_escape($B)." FOR$zg";$Ug=$Y;}$P
.=($eb?"\n)":"");}}array_unshift($eh,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($eh,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($Sh)array_unshift($eh,$Sh);if($ob!==null)$eh[]="COMMENT ON TABLE ".table($B)." IS ".q($ob);foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
alter_indexes($R,$b){$h=array();$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($eh,"ALTER TABLE ".table($R).implode(",",$h));if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_views($Nj){return
drop_tables($Nj);}function
drop_tables($T){foreach($T
as$R){$P=table_status1($R);if(!queries("DROP ".strtoupper($P["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Nj,$Ii){foreach(array_merge($T,$Nj)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Ii)))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$K)$e[]=$K["event_object_column"];$J=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$K){if($e&&$K["Event"]=="UPDATE")$K["Event"].=" OF";$K["Of"]=implode(", ",$e);if($J)$K["Event"].=" OR $J[Event]";$J=$K;}return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$K){$hj=trigger($K["trigger_name"],$R);$J[$hj["Trigger"]]=array($hj["Timing"],$hj["Event"]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$J=idx($L,0,array());$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT COALESCE(parameter_name, ordinal_position::text) AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($B).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,$K){$J=array();foreach($K["fields"]as$m){$y=$m["length"];$J[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$J).")";}function
last_id($I){$K=(is_object($I)?$I->fetch_row():array());return($K?$K[0]:0);}function
explain($f,$H){return$f->query("EXPLAIN $H");}function
found_rows($S,$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$qh))return$qh[1];}function
types(){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".driver()->nsOid."
AND typtype IN ('b','d','e')
AND typelem = 0");}function
type_values($t){$Bc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Bc?"'".implode("', '",array_map('addslashes',$Bc))."'":"");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
get_val("SELECT current_schema()");}function
set_schema($Fh,$g=null){if(!$g)$g=connection();$J=$g->query("SET search_path TO ".idf_escape($Fh));driver()->setUserTypes(types());return$J;}function
foreign_keys_sql($R){$J="";$P=table_status1($R);$fd=foreign_keys($R);ksort($fd);foreach($fd
as$ed=>$dd)$J
.="ALTER TABLE ONLY ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($ed)." $dd[definition] ".($dd['deferrable']?'DEFERRABLE':'NOT DEFERRABLE').";\n";return($J?"$J\n":$J);}function
create_sql($R,$_a,$si){$wh=array();$Uh=array();$P=table_status1($R);if(is_view($P)){$Mj=view($R);return
rtrim("CREATE VIEW ".idf_escape($R)." AS $Mj[select]",";");}$n=fields($R);if(count($P)<2||empty($n))return
false;$J="CREATE TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." (\n    ";foreach($n
as$m){$xg=idf_escape($m['field']).' '.$m['full_type'].default_value($m).($m['null']?"":" NOT NULL");$wh[]=$xg;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$Ze)){$Th=$Ze[1];$hi=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Th)):"SELECT * FROM $Th"),null,"-- "));$Uh[]=($si=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Th;\n":"")."CREATE SEQUENCE $Th INCREMENT $hi[increment_by] MINVALUE $hi[min_value] MAXVALUE $hi[max_value]".($_a&&$hi['last_value']?" START ".($hi["last_value"]+1):"")." CACHE $hi[cache_value];";}}if(!empty($Uh))$J=implode("\n\n",$Uh)."\n\n$J";$G="";foreach(indexes($R)as$ce=>$v){if($v['type']=='PRIMARY'){$G=$ce;$wh[]="CONSTRAINT ".idf_escape($ce)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$ub=>$wb)$wh[]="CONSTRAINT ".idf_escape($ub)." CHECK $wb";$J
.=implode(",\n    ",$wh)."\n)";$zg=driver()->partitionsInfo($P['Name']);if($zg)$J
.="\nPARTITION BY $zg[partition_by]($zg[partition])";$J
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";if($P['Comment'])$J
.="\n\nCOMMENT ON TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$Wc=>$m){if($m['comment'])$J
.="\n\nCOMMENT ON COLUMN ".idf_escape($P['nspname']).".".idf_escape($P['Name']).".".idf_escape($Wc)." IS ".q($m['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($G?" AND indexname != ".q($G):""),null,"-- ")as$K)$J
.="\n\n$K[indexdef];";return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$P=table_status1($R);$J="";foreach(triggers($R)as$gj=>$fj){$hj=trigger($gj,$P['Name']);$J
.="\nCREATE TRIGGER ".idf_escape($hj['Trigger'])." $hj[Timing] $hj[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $hj[Type] $hj[Statement];;\n";}return$J;}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)){if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="CREATE DATABASE $B;\n";}return"$J\\connect $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Uc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("oracle","Oracle (beta)");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$this->link=@oci_new_connect($V,$F,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return$l["message"];}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($Nb){$this->_current_db=$Nb;return
true;}function
query($H,$oj=false){$I=oci_parse($this->link,$H);$this->error="";if(!$I){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Result($I);$this->affected_rows=oci_num_rows($I);oci_free_statement($I);}return$J;}function
timeout($uf){return
oci_set_call_timeout($this->link,$uf);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$K[$x]=$X->load();}return$K;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->name=oci_field_name($this->result,$d);$J->type=oci_field_type($this->result,$d);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->result);}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$F){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$F);}function
select_db($Nb){$this->_current_db=$Nb;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($C?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $H$Z) t WHERE rownum <= ".($z+$C).") WHERE rnum > $C":($z?" * FROM (SELECT $H$Z) WHERE rownum <= ".($z+$C):" $H$Z"));}function
limit1($R,$H,$Z,$Rh="\n"){return" $H$Z";}function
db_collation($j,$jb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;unset(connection()->_current_db);return$j;}function
where_owner($Sg,$rg="owner"){if(!$_GET["ns"])return'';return"$Sg$rg = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$rg=where_owner('');return"(SELECT $e FROM all_views WHERE ".($rg?:"rownum < 0").")";}function
tables_list(){$Mj=views_table("view_name");$rg=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$rg
UNION SELECT view_name, 'view' FROM $Mj
ORDER BY 1");}function
count_tables($i){$J=array();foreach($i
as$j)$J[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$J;}function
table_status($B=""){$J=array();$Kh=q($B);$j=get_current_db();$Mj=views_table("view_name");$rg=where_owner(" AND ");foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q($j).$rg.($B!=""?" AND table_name = $Kh":"")."
UNION SELECT view_name, 'view', 0, 0 FROM $Mj".($B!=""?" WHERE view_name = $Kh":"")."
ORDER BY 1")as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();$rg=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$rg ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$y="$K[DATA_PRECISION],$K[DATA_SCALE]";if($y==",")$y=$K["CHAR_COL_DECL_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),);}return$J;}function
indexes($R,$g=null){$J=array();$rg=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$rg
ORDER BY ac.constraint_type, aic.column_position",$g)as$K){$ce=$K["INDEX_NAME"];$lb=$K["DATA_DEFAULT"];$lb=($lb?trim($lb,'"'):$K["COLUMN_NAME"]);$J[$ce]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$ce]["columns"][]=$lb;$J[$ce]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$ce]["descs"][]=($K["DESCEND"]&&$K["DESCEND"]=="DESC"?'1':null);}return$J;}function
view($B){$Mj=views_table("view_name, text");$L=get_rows('SELECT text "select" FROM '.$Mj.' WHERE view_name = '.q($B));return
reset($L);}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain($f,$H){$f->query("EXPLAIN PLAN FOR $H");return$f->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=$ic=array();$kg=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$jg=$kg[$m[0]];if($X&&$jg){$Nf=process_field($jg,$jg);if($X[2]==$Nf[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$ic[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$ic).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
alter_indexes($R,$b){$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($eh,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
foreign_keys($R){$J=array();$H="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($H)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id($I){return
0;}function
schemas(){$J=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($J?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Hh,$g=null){if(!$g)$g=connection();return$g->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Hh));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$J=array();$L=get_rows('SELECT * FROM v$instance');foreach(reset($L)as$x=>$X)$J[]=array($x,$X);return$J;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$Uc);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$F){$vb=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");$ni=adminer()->connectSsl();if(isset($ni["Encrypt"]))$vb["Encrypt"]=$ni["Encrypt"];if(isset($ni["TrustServerCertificate"]))$vb["TrustServerCertificate"]=$ni["TrustServerCertificate"];$j=adminer()->database();if($j!="")$vb["Database"]=$j;list($Md,$Mg)=host_port($N);$this->link=@sqlsrv_connect($Md.($Mg?",$Mg":""),$vb);if($this->link){$he=sqlsrv_server_info($this->link);$this->server_info=$he['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$pj=strlen($Q)!=strlen(utf8_decode($Q));return($pj?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($Nb){return$this->query(use_sql($Nb));}function
query($H,$oj=false){$I=sqlsrv_query($this->link,$H);$this->error="";if(!$I){$this->get_error();return
false;}return$this->store_result($I);}function
multi_query($H){$this->result=sqlsrv_query($this->link,$H);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->result?!!sqlsrv_next_result($this->result):false;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'DateTime'))$K[$x]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$J=new
\stdClass;$J->name=$m["Name"];$J->type=($m["Type"]==1?254:15);$J->charsetnr=0;return$J;}function
seek($C){for($s=0;$s<$C;$s++)sqlsrv_fetch($this->result);}function
__destruct(){sqlsrv_free_stmt($this->result);}}function
last_id($I){return
get_val("SELECT SCOPE_IDENTITY()");}function
explain($f,$H){$f->query("SET SHOWPLAN_ALL ON");$J=$f->query($H);$f->query("SET SHOWPLAN_ALL OFF");return$J;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($Nb){return$this->query(use_sql($Nb));}function
lastInsertId(){return$this->pdo->lastInsertId();}}function
last_id($I){return
connection()->lastInsertId();}function
explain($f,$H){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("sqlsrv:Server=$Md".($Mg?",$Mg":""),$V,$F);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("dblib:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$F){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$F);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$L,array$G){$n=fields($R);$wj=array();$Z=array();$O=reset($L);$e="c".implode(", c",range(1,count($O)));$Pa=0;$ne=array();foreach($O
as$x=>$X){$Pa++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$ne[$x]="c$Pa";if(isset($G[$B]))$Z[]="$x = c$Pa";else$wj[]="$x = c$Pa";}$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";if($Z){$Rd=queries("SET IDENTITY_INSERT ".table($R)." ON");$J=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$Ij)."\n) AS source ($e) ON ".implode(" AND ",$Z).($wj?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$wj):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Rd?$O:$ne)).") VALUES (".($Rd?$e:implode(", ",$ne)).");");if($Rd)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$J=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$Ij));return$J;}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($B,$ye=false){$Re=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Re[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($z?" TOP (".($z+$C).")":"")." $H$Z";}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,$jb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($i){$J=array();foreach($i
as$j){connection()->select_db($j);$J[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($B=""){$J=array();foreach(get_rows("SELECT ao.name AS Name, ao.type_desc AS Engine, (SELECT value FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$qb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$J=array();$zi=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($zi))as$K){$U=$K["type"];$y=(preg_match("~char|binary~",$U)?intval($K["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$K["default"],$A)?str_replace("''","'",$A[1]):$K["default"]),"default_constraint"=>$K["default_constraint"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["is_primary_key"],"comment"=>$qb[$K["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($zi))as$K){$J[$K["name"]]["generated"]=($K["is_persisted"]?"PERSISTED":"VIRTUAL");$J[$K["name"]]["default"]=$K["definition"];}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$K){$B=$K["name"];$J[$B]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$B]["lengths"]=array();$J[$B]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$B]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$c)$J[preg_replace('~_.*~','',$c)][]=$c;return$J;}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$c)?" COLLATE $c":""));}function
drop_databases($i){return
queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$c){if(preg_match('~^[a-z0-9_]+$~i',$c))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $c");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$qb=array();$kg=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$qb[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($jd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$jg=$kg[$m[0]];if(default_value($jg)!=$k){if($jg["default"]!==null)$b["DROP"][]=" ".idf_escape($jg["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R=="")return
queries("CREATE TABLE ".table($B)." (".implode(",",(array)$b["ADD"])."\n)");if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($jd)$b[""]=$jd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$X)))return
false;}foreach($qb
as$x=>$X){$ob=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $ob,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$ic=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$ic[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$ic)));}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();$Uf=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q(get_schema()))as$K){$p=&$J[$K["FK_NAME"]];$p["db"]=$K["PKTABLE_QUALIFIER"];$p["ns"]=$K["PKTABLE_OWNER"];$p["table"]=$K["PKTABLE_NAME"];$p["on_update"]=$Uf[$K["UPDATE_RULE"]];$p["on_delete"]=$Uf[$K["DELETE_RULE"]];$p["source"][]=$K["FKCOLUMN_NAME"];$p["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables($T,$Nj,$Ii){return
apply_queries("ALTER SCHEMA ".idf_escape($Ii)." TRANSFER",array_merge($T,$Nj));}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$J["text"]);return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$K)$J[$K["name"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($Fh){$_GET["ns"]=$Fh;return
true;}function
create_sql($R,$_a,$si){if(is_view(table_status1($R))){$Mj=view($R);return"CREATE VIEW ".table($R)." AS $Mj[select]";}$n=array();$G=false;foreach(fields($R)as$B=>$m){$X=process_field($m,$m);if($X[6])$G=true;$n[]=implode("",$X);}foreach(indexes($R)as$B=>$v){if(!$G||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$B=>$Wa)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($Wa)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$jd)$n[]=ltrim(format_foreign_key($jd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($Nb,$si=""){return"USE ".idf_escape($Nb);}function
trigger_sql($R){$J="";foreach(triggers($R)as$B=>$hj)$J
.=create_trigger(" ON ".table($R),trigger($B,$R)).";";return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|comment|columns|database|drop_col|dump|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$Uc);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($hd=true){return
get_databases($hd);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){return
schemas();}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Gb){return$Gb;}function
head($Kb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$J=array();foreach(array("","-dark")as$tf){$o="adminer$tf.css";if(file_exists($o)){$Zc=file_get_contents($o);$J["$o?v=".crc32($Zc)]=($tf?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Zc)?'':'light'));}}return$J;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,"loginDriver(this);")),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>','<input name="auth[server]" value="'.h(SERVER).'" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("const authDriver = qs('#username').form['auth[driver]']; authDriver && authDriver.onchange();")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($B,$Hd,$Y){return$Hd.$Y."\n";}function
login($Te,$F){if($F=="")return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$yi){return
h($yi["Name"]);}function
fieldName(array$m,$dg=0){$U=$m["full_type"];$ob=$m["comment"];return'<span title="'.h($U.($ob!=""?($U?": ":"").$ob:'')).'">'.h($m["field"]).'</span>';}function
selectLinks(array$yi,$O=""){$B=$yi["Name"];echo'<p class="links">';$Re=array("select"=>'Select data');if(support("table")||support("indexes"))$Re["table"]='Show structure';$ye=false;if(support("table")){$ye=is_view($yi);if(!$ye)$Re["create"]='Alter table';elseif(support("view"))$Re["view"]='Alter view';}if($O!==null)$Re["edit"]='New item';foreach($Re
as$x=>$X)echo" <a href='".h(ME)."$x=".urlencode($B).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$ye)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$xi){return
array();}function
backwardKeysPrint(array$Da,array$K){}function
selectQuery($H,$oi,$Sc=false){$J="</p>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings";$J=", <a href='#$t'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."$J<div id='$t' class='hidden'>\n$Qj</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$H))."</code> <span class='time'>(".format_time($oi).")</span>".(support("sql")?" <a href='".h(ME)."sql=".urlencode($H)."'>".'Edit'."</a>":"").$J;}function
sqlCommandQuery($H){return
shorten_utf8(trim($H),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$L,array$kd){return$L;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$ng){$J=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~json~',$m["type"])?"<code class='jush-js'>$X</code>":$X)));if(is_blob($m)&&!is_utf8($X))$J="<i>".lang_format(array('%d byte','%d bytes'),strlen($ng))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$J</a>":$J);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$yi=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."</thead>\n";$ri=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$c=h($m["collation"]);echo"<td><span title='$c'>".(in_array($U,(array)$ri['User types'])?"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>":$U.($c&&isset($yi["Collation"])&&$c!=$yi["Collation"]?" $c":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":"");$k=h($m["default"]);echo(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>$k</code>":$k)."</b>]</span>":""),(support("comment")?"<td>".h($m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$yi){$yg=false;foreach($w
as$B=>$v)$yg|=!!$v["partial"];echo"<table>\n";$Sb=first(driver()->indexAlgorithms($yi));foreach($w
as$B=>$v){ksort($v["columns"]);$Wg=array();foreach($v["columns"]as$x=>$X)$Wg[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".$v["lengths"][$x].")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>$v[type]".($Sb&&$v['algorithm']!=$Sb?" ($v[algorithm])":""),"<td>".implode(", ",$Wg);if($yg)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]'",$e,$X["col"],($x!==""?"selectFieldChange":"selectAddRow"));echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'",1).script("qsl('select').onchange = function () { helpClose();".($x!==""?"":" qsl('select, input', this.parentNode).onchange();")." };","")."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."'>",script("qsl('input').oninput = selectFieldChange;",""),checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"),"</div>\n";}$Ta="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],adminer()->operators())))echo"<div>".select_input(" name='where[$s][col]'",$e,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".'anywhere'.")"),html_select("where[$s][op]",adminer()->operators(),$X["op"],$Ta),"<input type='search' name='where[$s][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Ta }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});",""),"</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$dg,array$e,array$w){print_fieldset("sort",'Sort',$dg);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]'",$e,$X,"selectFieldChange"),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]'",$e,"","selectAddRow"),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".intval($z)."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
selectLengthPrint($Oi){if($Oi!==null)echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Oi)."'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Jb=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Jb)$e[$Jb]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$uc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$wd=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$wd[]=$M[$x];}}return
array($M,$wd);}function
selectSearchProcess(array$n,array$w){$J=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$J[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$x=>$X){$hb=$X["col"];if("$hb$X[val]"!=""&&in_array($X["op"],adminer()->operators())){$sb=array();foreach(($hb!=""?array($hb=>$n[$hb]):$n)as$B=>$m){$Sg="";$rb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Wd=process_length($X["val"]);$rb
.=" ".($Wd!=""?$Wd:"(NULL)");}elseif($X["op"]=="SQL")$rb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$rb=" $A[1] ".adminer()->processInput($m,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$Sg="$X[op](".q($X["val"]).", ";$rb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$rb
.=" ".adminer()->processInput($m,$X["val"]);if($hb!=""||(isset($m["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$m["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$m["type"]))&&(!preg_match('~date|timestamp~',$m["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$sb[]=$Sg.driver()->convertSearch(idf_escape($B),$X,$m).$rb;}$J[]=(count($sb)==1?$sb[0]:($sb?"(".implode(" OR ",$sb).")":"1 = 0"));}}return$J;}function
selectOrderProcess(array$n,array$w){$J=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC":"");}return$J;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$kd){return
false;}function
selectQueryBuild(array$M,array$Z,array$wd,array$dg,$z,$D){return"";}function
messageQuery($H,$Pi,$Sc=false){restart_session();$Jd=&get_session("queries");if(!idx($Jd,$_GET["db"]))$Jd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$Jd[$_GET["db"]][]=array($H,time(),$Pi);$ki="sql-".count($Jd[$_GET["db"]]);$J="<a href='#$ki' class='toggle'>".'SQL command'."</a> <a href='' class='jsonly copy'>🗐</a>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings-".count($Jd[$_GET["db"]]);$J="<a href='#$t' class='toggle'>".'Warnings'."</a>, $J<div id='$t' class='hidden'>\n$Qj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$ki' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1000)."</code></pre>".($Pi?" <span class='time'>($Pi)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$n,$K,$wj){}function
editFunctions(array$m){$J=($m["null"]?"NULL/":"");$wj=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$rd){if(!$x||(!isset($_GET["call"])&&$wj)){foreach($rd
as$Gg=>$X){if(!$Gg||preg_match("~$Gg~",$m["type"]))$J
.="/$X";}}if($x&&$rd&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$J
.="/SQL";}if($m["auto_increment"]&&!$wj)$J='Auto Increment';return
explode("/",$J);}function
editInput($R,array$m,$ya,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$ya value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$ya,$m,$Y,"NULL");return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$B=$m["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$J="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$J=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$J=idf_escape($B)." $r $J";elseif(preg_match('~^[+-] interval$~',$r))$J=idf_escape($B)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$J="$r(".idf_escape($B).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$J="$r($J)";return
unconvert_field($m,$J);}function
dumpOutput(){$J=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$J['gz']='gzip';return$J;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($j){}function
dumpTable($R,$si,$ye=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($si)dump_csv(array_keys(fields($R)));}else{if($ye==2){$n=array();foreach(fields($R)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$si);set_utf8mb4($h);if($si&&$h){if($si=="DROP+CREATE"||$ye==1)echo"DROP ".($ye==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($ye==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$si,$H){if($si){$df=(JUSH=="sqlite"?0:1048576);$n=array();$Sd=false;if($_POST["format"]=="sql"){if($si=="TRUNCATE+INSERT")echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Sd=true;break;}}}}$I=connection()->query($H,1);if($I){$ne="";$Na="";$Ce=array();$sd=array();$ui="";$Vc=($R!=''?'fetch_assoc':'fetch_row');$Cb=0;while($K=$I->$Vc()){if(!$Ce){$Ij=array();foreach($K
as$X){$m=$I->fetch_field();if(idx($n[$m->name],'generated')){$sd[$m->name]=true;continue;}$Ce[]=$m->name;$x=idf_escape($m->name);$Ij[]="$x = VALUES($x)";}$ui=($si=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij):"").";\n";}if($_POST["format"]!="sql"){if($si=="table"){dump_csv($Ce);$si="INSERT";}dump_csv($K);}else{if(!$ne)$ne="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$Ce)).") VALUES";foreach($K
as$x=>$X){if($sd[$x]){unset($K[$x]);continue;}$m=$n[$x];$K[$x]=($X!==null?unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:q(($X===false?0:$X))):"NULL");}$Dh=($df?"\n":" ")."(".implode(",\t",$K).")";if(!$Na)$Na=$ne.$Dh;elseif(JUSH=='mssql'?$Cb%1000!=0:strlen($Na)+4+strlen($Dh)+strlen($ui)<$df)$Na
.=",$Dh";else{echo$Na.$ui;$Na=$ne.$Dh;}}$Cb++;}if($Na)echo$Na.$ui;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Sd)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Qd){return
friendly_url($Qd!=""?$Qd:(SERVER?:"localhost"));}function
dumpHeaders($Qd,$wf=false){$qg=$_POST["output"];$Nc=(preg_match('~sql~',$_POST["format"])?"sql":($wf?"tar":"csv"));header("Content-Type: ".($qg=="gz"?"application/x-gzip":($Nc=="tar"?"application/x-tar":($Nc=="sql"||$qg!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($qg=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Nc;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($sf){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Df=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Df)<0?h($Df):"")."</a>","</span></h1>\n";if($sf=="auth"){$qg="";foreach((array)$_SESSION["pwds"]as$Kj=>$Wh){foreach($Wh
as$N=>$Fj){$B=h(get_setting("vendor-$Kj-$N")?:get_driver($Kj));foreach($Fj
as$V=>$F){if($F!==null){$Qb=$_SESSION["db"][$Kj][$N][$V];foreach(($Qb?array_keys($Qb):array(""))as$j)$qg
.="<li><a href='".h(auth_url($Kj,$N,$V,$j))."'>($B) ".h("$V@".($N!=""?adminer()->serverName($N):"").($j!=""?" - $j":""))."</a>\n";}}}}if($qg)echo"<ul id='logins'>\n$qg</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");}else{$T=array();if($_GET["ns"]!==""&&!$sf&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($sf);$ia=array();if(DB==""||!$sf){if(support("sql")){$ia[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ia[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ia[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Xd=$_GET["ns"]!==""&&!$sf&&DB!="";if($Xd)$ia[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";echo($ia?"<p class='links'>\n".implode("\n",$ia)."\n":"");if($Xd){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=5.4.1",true);if(support("sql")){echo"<script".nonce().">\n";if($T){$Re=array();foreach($T
as$R=>$U)$Re[]=preg_quote($R,'/');echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b('.implode('|',$Re).')\b/g',false);if(support('routine')){foreach(routines()as$K)json_row(js_escape(ME).'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&','/\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$Ei=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$Ei[$R][]=$m["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Ei)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".preg_replace('~^(\d\.?\d).*~s','\1',connection()->server_info)."', '".connection()->flavor."');");}function
databasesPrint($sf){$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$Ob=script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB).$Ob:"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($sf!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"])."$Ob</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
tablesPrint(array$T){echo"<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$R=>$P){$R="$R";$B=adminer()->tableName($P);if($B!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$error='';private$hooks=array();function
__construct($Lg){if($Lg===null){$Lg=array();$Ha="adminer-plugins";if(is_dir($Ha)){foreach(glob("$Ha/*.php")as$o)$Yd=include_once"./$o";}$Id=" href='https://www.adminer.org/plugins/#use'".target_blank();if(file_exists("$Ha.php")){$Yd=include_once"./$Ha.php";if(is_array($Yd)){foreach($Yd
as$Kg)$Lg[get_class($Kg)]=$Kg;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Ha.php</b>",$Id)."<br>";}foreach(get_declared_classes()as$db){if(!$Lg[$db]&&preg_match('~^Adminer\w~i',$db)){$oh=new
\ReflectionClass($db);$xb=$oh->getConstructor();if($xb&&$xb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$Id,"<b>$db</b>","<b>$Ha.php</b>")."<br>";else$Lg[$db]=new$db;}}}$this->plugins=$Lg;$la=new
Adminer;$Lg[]=$la;$oh=new
\ReflectionObject($la);foreach($oh->getMethods()as$qf){foreach($Lg
as$Kg){$B=$qf->getName();if(method_exists($Kg,$B))$this->hooks[$B][]=$Kg;}}}function
__call($B,array$vg){$ua=array();foreach($vg
as$x=>$X)$ua[]=&$vg[$x];$J=null;foreach($this->hooks[$B]as$Kg){$Y=call_user_func_array(array($Kg,$B),$ua);if($Y!==null){if(!self::$append[$B])return$Y;$J=$Y+(array)$J;}}return$J;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$ua);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Md,$Mg)=host_port($N);$ni=adminer()->connectSsl();if($ni)$this->ssl_set($ni['key'],$ni['cert'],$ni['ca'],'','');$J=@$this->real_connect(($N!=""?$Md:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Mg)?intval($Mg):ini_get("mysqli.default_port")),(is_numeric($Mg)?null:$Mg),($ni?($ni['verify']!==false?2048:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($J?'':$this->error);}function
set_charset($Va){if(parent::set_charset($Va))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Va");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$F){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$F!=""?$F:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($Va){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Va,$this->link))return
true;mysql_set_charset('utf8',$this->link);}return$this->query("SET NAMES $Va");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Nb){return
mysql_select_db($Nb,$this->link);}function
query($H,$oj=false){$I=@($oj?mysql_unbuffered_query($H,$this->link):mysql_query($H,$this->link));$this->error="";if(!$I){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
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
fetch_field(){$J=mysql_fetch_field($this->result,$this->offset++);$J->orgtable=$J->table;$J->charsetnr=($J->blob?63:0);return$J;}function
__destruct(){mysql_free_result($this->result);}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$F){$bg=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$ni=adminer()->connectSsl();if($ni){if($ni['key'])$bg[\PDO::MYSQL_ATTR_SSL_KEY]=$ni['key'];if($ni['cert'])$bg[\PDO::MYSQL_ATTR_SSL_CERT]=$ni['cert'];if($ni['ca'])$bg[\PDO::MYSQL_ATTR_SSL_CA]=$ni['ca'];if(isset($ni['verify']))$bg[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$ni['verify'];}list($Md,$Mg)=host_port($N);return$this->dsn("mysql:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F,$bg);}function
set_charset($Va){return$this->query("SET NAMES $Va");}function
select_db($Nb){return$this->query("USE ".idf_escape($Nb));}function
query($H,$oj=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$oj);return
parent::query($H,$oj);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($Dh=iconv("windows-1250","utf-8",$f))>strlen($f))$f=$Dh;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version(9,'',$f)){$this->types['Numbers']["vector"]=16383;$this->insertFunctions['vector']='string_to_vector';}if(min_version(5.1,'',$f))$this->partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):(preg_match("~geometry|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":"")));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$L,array$G){$e=array_keys(reset($L));$Sg="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$Ij=array();foreach($e
as$x)$Ij[$x]="$x = VALUES($x)";$ui="\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij);$Ij=array();$y=0;foreach($L
as$O){$Y="(".implode(", ",$O).")";if($Ij&&(strlen($Sg)+$y+strlen($Y)+strlen($ui)>1e6)){if(!queries($Sg.implode(",\n",$Ij).$ui))return
false;$Ij=array();$y=0;}$Ij[]=$Y;$y+=strlen($Y)+2;}return
queries($Sg.implode(",\n",$Ij).$ui);}function
slowQuery($H,$Qi){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Qi FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Qi*1000).") */ $A[2]";}}function
convertSearch($u,array$X,array$m){return(preg_match('~char|text|enum|set~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u);}function
warnings(){$I=$this->conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();print_select_result($I);return
ob_get_clean();}}function
tableHelp($B,$ye=false){$Ve=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower("information-schema-".($Ve?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="mysql")return($Ve?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($R){$pd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$I=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $pd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=array();list($J["partition_by"],$J["partition"],$J["partitions"])=$I->fetch_row();$Cg=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $pd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$J["partition_names"]=array_keys($Cg);$J["partition_values"]=array_values($Cg);return$J;}function
hasCStyleEscapes(){static$Qa;if($Qa===null){$li=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Qa=(strpos($li,'NO_BACKSLASH_ESCAPES')===false);}return$Qa;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
indexAlgorithms(array$yi){return(preg_match('~^(MEMORY|NDB)$~',$yi["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($hd){$J=get_session("dbs");if($J===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$J=($hd?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,array$jb){$J=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$J=$jb[$A[1]][-1];return$J;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$J=array();foreach($i
as$j)$J[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$J;}function
table_status($B="",$Tc=false){$J=array();foreach(get_rows($Tc?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name"):"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($B!="")$K["Name"]=$B;$J[$K["Name"]]=$K;}return$J;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
fields($R){$Ve=(connection()->flavor=='maria');$J=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$K){$m=$K["COLUMN_NAME"];$U=$K["COLUMN_TYPE"];$td=$K["GENERATION_EXPRESSION"];$Qc=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Qc,$sd);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Ye);$k=$K["COLUMN_DEFAULT"];if($k!=""){$xe=preg_match('~text|json~',$Ye[1]);if(!$Ve&&$xe)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($Ve||$xe){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$Ve&&preg_match('~binary~',$Ye[1])&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$J[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$Ye[1],"length"=>$Ye[2],"unsigned"=>ltrim($Ye[3].$Ye[4]),"default"=>($sd?($Ve?$td:stripslashes($td)):$k),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Qc=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Qc,$A)?$A[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$K[PRIVILEGES],where,order")),"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($sd[1]=="PERSISTENT"?"STORED":$sd[1]),);}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$K){$B=$K["Key_name"];$J[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$B]["columns"][]=$K["Column_name"];$J[$B]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$B]["descs"][]=null;$J[$B]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($R){static$Gg='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=array();$Db=get_val("SHOW CREATE TABLE ".table($R),1);if($Db){preg_match_all("~CONSTRAINT ($Gg) FOREIGN KEY ?\\(((?:$Gg,? ?)+)\\) REFERENCES ($Gg)(?:\\.($Gg))? \\(((?:$Gg,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Db,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){preg_match_all("~$Gg~",$A[2],$fi);preg_match_all("~$Gg~",$A[5],$Ii);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$fi[0]),"target"=>array_map('Adminer\idf_unescape',$Ii[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$x=>$X)sort($J[$x]);return$J;}function
information_schema($j){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" COLLATE ".q($c):""));}function
drop_databases(array$i){$J=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($B,$c){$J=false;if(create_database($B,$c)){$T=array();$Nj=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$Nj[]=$R;else$T[]=$R;}$J=(!$T&&!$Nj)||move_tables($T,$Nj,$B);drop_databases($J?array(DB):array());}return$J;}function
auto_increment(){$Aa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Aa="";break;}if($v["type"]=="PRIMARY")$Aa=" UNIQUE";}}return" AUTO_INCREMENT$Aa";}function
alter_table($R,$B,array$n,array$jd,$ob,$xc,$c,$_a,$E){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$jd);$P=($ob!==null?" COMMENT=".q($ob):"").($xc?" ENGINE=".q($xc):"").($c?" COLLATE ".q($c):"").($_a!=""?" AUTO_INCREMENT=$_a":"");if($E){$Cg=array();if($E["partition_by"]=='RANGE'||$E["partition_by"]=='LIST'){foreach($E["partition_names"]as$x=>$X){$Y=$E["partition_values"][$x];$Cg[]="\n  PARTITION ".idf_escape($X)." VALUES ".($E["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $E[partition_by]($E[partition])";if($Cg)$P
.=" (".implode(",",$Cg)."\n)";elseif($E["partitions"])$P
.=" PARTITIONS ".(+$E["partitions"]);}elseif($E===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");if($R!=$B)$b[]="RENAME TO ".table($B);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$Ua=array();foreach($b
as$X)$Ua[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Ua));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$Nj,$Ii){$sh=array();foreach($T
as$R)$sh[]=table($R)." TO ".idf_escape($Ii).".".table($R);if(!$sh||queries("RENAME TABLE ".implode(", ",$sh))){$Wb=array();foreach($Nj
as$R)$Wb[table($R)]=view($R);connection()->select_db($Ii);$j=idf_escape(DB);foreach($Wb
as$B=>$Mj){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$Mj["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$Nj,$Ii){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$hj=$K["Trigger"];if(!queries("CREATE TRIGGER ".($Ii==DB?idf_escape("copy_$hj"):idf_escape($Ii).".".idf_escape($hj))." $K[Timing] $K[Event] ON $B FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Nj
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));$Mj=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Mj[select]"))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$U){$ra=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$zc=driver()->enumLength;$mj="((".implode("|",array_merge(array_keys(driver()->types()),$ra)).")\\b(?:\\s*\\(((?:[^'\")]|$zc)++)\\))?"."\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?(?:\\s*COLLATE\\s*['\"]?[^'\"\\s,]+['\"]?)?";$Gg="$gi*(".($U=="FUNCTION"?"":driver()->inout).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$mj";$h=get_val("SHOW CREATE $U ".idf_escape($B),2);preg_match("~\\(((?:$Gg\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$mj\\s+":"")."(.*)~is",$h,$A);$n=array();preg_match_all("~$Gg\\s*,?~is",$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$ug)$n[]=array("field"=>str_replace("``","`",$ug[2]).$ug[3],"type"=>strtolower($ug[5]),"length"=>preg_replace_callback("~$zc~s",'Adminer\normalize_enum',$ug[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$ug[8] $ug[7]"))),"null"=>true,"full_type"=>$ug[4],"inout"=>strtoupper($ug[1]),"collation"=>strtolower($ug[9]),);return
array("fields"=>$n,"comment"=>get_val("SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = ".q($B)),)+($U!="FUNCTION"?array("definition"=>$A[11]):array("returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",));}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($B,array$K){return
idf_escape($B);}function
last_id($I){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$H){return$f->query("EXPLAIN ".(min_version(5.1)&&!min_version(5.7)?"PARTITIONS ":"").$H);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$_a,$si){$J=get_val("SHOW CREATE TABLE ".table($R),1);if(!$_a)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)&&($h=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($h);if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="$h;\n";}return$J."USE $B";}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){if(preg_match("~binary~",$m["type"]))return"HEX(".idf_escape($m["field"]).")";if($m["type"]=="bit")return"BIN(".idf_escape($m["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$J){if(preg_match("~binary~",$m["type"]))$J="UNHEX($J)";if($m["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"])){$Sg=(min_version(8)?"ST_":"");$J=$Sg."GeomFromText($J, $Sg"."SRID($m[field]))";}return$J;}function
support($Uc){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(5.1)?'|event':'').(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').')$~',$Uc);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types(){return
array();}function
type_values($t){return"";}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Fh,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));function
page_header($Si,$l="",$Ma=array(),$Ti=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Ui=$Si.($Ti!=""?": $Ti":"");$Vi=strip_tags($Ui.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$Vi,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=5.4.1"),'">
';$Hb=adminer()->css();if(is_int(key($Hb)))$Hb=array_fill_keys($Hb,'light');$Ed=in_array('light',$Hb)||in_array('',$Hb);$Cd=in_array('dark',$Hb)||in_array('',$Hb);$Kb=($Ed?($Cd?null:false):($Cd?:null));$jf=" media='(prefers-color-scheme: dark)'";if($Kb!==false)echo"<link rel='stylesheet'".($Kb?"":$jf)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=5.4.1")."'>\n";echo"<meta name='color-scheme' content='".($Kb===null?"light dark":($Kb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=5.4.1");if(adminer()->head($Kb))echo"<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."'>\n";foreach($Hb
as$_j=>$tf){$ya=($tf=='dark'&&!$Kb?$jf:($tf=='light'&&$Cd?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$ya href='".h($_j)."'>\n";}echo"\n<body class='".'ltr'." nojs";adminer()->bodyClass();echo"'>\n";$o=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($o)&&filemtime($o)+86400>time()){$Lj=unserialize(file_get_contents($o));$ch="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Lj["version"],base64_decode($Lj["signature"]),$ch)==1)$_COOKIE["adminer_version"]=$Lj["version"];}echo
script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick".(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '".VERSION."', '".js_escape(ME)."', '".get_token()."')")."});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ma!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ma===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ma)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> » ';if(is_array($Ma)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Ma
as$x=>$X){$Yb=(is_array($X)?$X[1]:h($X));if($Yb!="")echo"<a href='".h(ME."$x=").urlencode(is_array($X)?$X[0]:$X)."'>$Yb</a> » ";}}echo"$Si\n";}}echo"<h2>$Ui</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Gb){$Gd=array();foreach($Gb
as$x=>$X)$Gd[]="$x $X";header("Content-Security-Policy: ".implode("; ",$Gd));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Ff;if(!$Ff)$Ff=base64_encode(rand_string());return$Ff;}function
page_messages($l){$zj=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$pf=idx($_SESSION["messages"],$zj);if($pf){echo"<div class='message'>".implode("</div>\n<div class='message'>",$pf)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$zj]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($sf=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($sf);echo"</div>\n";if($sf!="auth")echo'<form action="" method="post">
<p class="logout">
<span>',h($_GET["username"])."\n",'</span>
<input type="submit" name="logout" value="Logout" id="logout">
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($yf){while($yf>=2147483648)$yf-=4294967296;while($yf<=-2147483649)$yf+=4294967296;return(int)$yf;}function
long2str(array$W,$Pj){$Dh='';foreach($W
as$X)$Dh
.=pack('V',$X);if($Pj)return
substr($Dh,0,end($W));return$Dh;}function
str2long($Dh,$Pj){$W=array_values(unpack('V*',str_pad($Dh,4*ceil(strlen($Dh)/4),"\0")));if($Pj)$W[]=strlen($Dh);return$W;}function
xxtea_mx($Wj,$Vj,$vi,$Ae){return
int32((($Wj>>5&0x7FFFFFF)^$Vj<<2)+(($Vj>>3&0x1FFFFFFF)^$Wj<<4))^int32(($vi^$Vj)+($Ae^$Wj));}function
encrypt_string($qi,$x){if($qi=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,true);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=0;while($dh-->0){$vi=int32($vi+0x9E3779B9);$oc=$vi>>2&3;for($sg=0;$sg<$yf;$sg++){$Vj=$W[$sg+1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$sg]+$xf);$W[$sg]=$Wj;}$Vj=$W[0];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$yf]+$xf);$W[$yf]=$Wj;}return
long2str($W,false);}function
decrypt_string($qi,$x){if($qi=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,false);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=int32($dh*0x9E3779B9);while($vi){$oc=$vi>>2&3;for($sg=$yf;$sg>0;$sg--){$Wj=$W[$sg-1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[$sg]-$xf);$W[$sg]=$Vj;}$Wj=$W[$yf];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[0]-$xf);$W[0]=$Vj;$vi=int32($vi-0x9E3779B9);}return
long2str($W,true);}$Ig=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Ig[$x]=$X;}}function
add_invalid_login(){$Fa=get_temp_dir()."/adminer.invalid";foreach(glob("$Fa*")?:array($Fa)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$Fa-".rand_string());if(!$q)return;$se=unserialize(stream_get_contents($q));$Pi=time();if($se){foreach($se
as$te=>$X){if($X[0]<$Pi)unset($se[$te]);}}$re=&$se[adminer()->bruteForceKey()];if(!$re)$re=array($Pi+30*60,0);$re[1]++;file_write_unlock($q,serialize($se));}function
check_invalid_login(array&$Ig){$se=array();foreach(glob(get_temp_dir()."/adminer.invalid*")as$o){$q=file_open_lock($o);if($q){$se=unserialize(stream_get_contents($q));file_unlock($q);break;}}$re=idx($se,adminer()->bruteForceKey(),array());$Ef=($re[1]>29?$re[0]-time():0);if($Ef>0)auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Ef/60)),$Ig);}$za=$_POST["auth"];if($za){session_regenerate_id();$Kj=$za["driver"];$N=$za["server"];$V=$za["username"];$F=(string)$za["password"];$j=$za["db"];set_password($Kj,$N,$V,$F);$_SESSION["db"][$Kj][$N][$V][$j]=true;if($za["permanent"]){$x=implode("-",array_map('base64_encode',array($Kj,$N,$V,$j)));$Xg=adminer()->permanentLogin(true);$Ig[$x]="$x:".base64_encode($Xg?encrypt_string($F,$Xg):"");cookie("adminer_permanent",implode(" ",$Ig));}if(count($_POST)==1||DRIVER!=$Kj||SERVER!=$N||$_GET["username"]!==$V||DB!=$j)redirect(auth_url($Kj,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Ig);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Ig&&!$_SESSION["pwds"]){session_regenerate_id();$Xg=adminer()->permanentLogin();foreach($Ig
as$x=>$X){list(,$cb)=explode(":",$X);list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($Kj,$N,$V,decrypt_string(base64_decode($cb),$Xg));$_SESSION["db"][$Kj][$N][$V][$j]=true;}}function
unset_permanent(array&$Ig){foreach($Ig
as$x=>$X){list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($Kj==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($Ig[$x]);}cookie("adminer_permanent",implode(" ",$Ig));}function
auth_error($l,array&$Ig){$Xh=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Xh]||$_GET[$Xh])&&!$_SESSION["token"])$l='Session expired, please login again.';else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($Ig);}}if(!$_COOKIE[$Xh]&&$_GET[$Xh]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$vg=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$vg["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Ig);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){list(,$Mg)=host_port(SERVER);if(preg_match('~^\s*([-+]?\d+)~',$Mg,$A)&&($A[1]<1024||$A[1]>65535))auth_error('Connecting to privileged ports is not allowed.',$Ig);check_invalid_login($Ig);$Fb=adminer()->credentials();$f=Driver::connect($Fb[0],$Fb[1],$Fb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Te=null;if(!is_object($f)||($Te=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Te)?$Te:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($l,$Ig);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($za&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){$ke="max_input_vars";$hf=ini_get($ke);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$x){$X=ini_get($x);if($X&&(!$hf||$X<$hf)){$ke=$x;$hf=$X;}}}$l=(!$_POST["token"]&&$hf?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$ke'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$l=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$l
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($I,$g=null,array$hg=array(),$z=0){$Re=array();$w=array();$e=array();$Ka=array();$nj=array();$J=array();for($s=0;(!$z||$s<$z)&&($K=$I->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($ze=0;$ze<count($K);$ze++){$m=$I->fetch_field();$B=$m->name;$gg=(isset($m->orgtable)?$m->orgtable:"");$fg=(isset($m->orgname)?$m->orgname:$B);if($hg&&JUSH=="sql")$Re[$ze]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($gg!=""){if(isset($m->table))$J[$m->table]=$gg;if(!isset($w[$gg])){$w[$gg]=array();foreach(indexes($gg,$g)as$v){if($v["type"]=="PRIMARY"){$w[$gg]=array_flip($v["columns"]);break;}}$e[$gg]=$w[$gg];}if(isset($e[$gg][$fg])){unset($e[$gg][$fg]);$w[$gg][$fg]=$ze;$Re[$ze]=$gg;}}if($m->charsetnr==63)$Ka[$ze]=true;$nj[$ze]=$m->type;echo"<th".($gg!=""||$m->name!=$fg?" title='".h(($gg!=""?"$gg.":"").$fg)."'":"").">".h($B).($hg?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$x=>$X){$_="";if(isset($Re[$x])&&!$e[$Re[$x]]){if($hg&&JUSH=="sql"){$R=$K[array_search("table=",$Re)];$_=ME.$Re[$x].urlencode($hg[$R]!=""?$hg[$R]:$R);}else{$_=ME."edit=".urlencode($Re[$x]);foreach($w[$Re[$x]]as$hb=>$ze){if($K[$ze]===null){$_="";break;}$_
.="&where".urlencode("[".bracket_escape($hb)."]")."=".urlencode($K[$ze]);}}}elseif(is_url($X))$_=$X;if($X===null)$X="<i>NULL</i>";elseif($Ka[$x]&&!is_utf8($X))$X="<i>".lang_format(array('%d byte','%d bytes'),strlen($X))."</i>";else{$X=h($X);if($nj[$x]==254)$X="<code>$X</code>";}if($_)$X="<a href='".h($_)."'".(is_url($_)?target_blank():'').">$X</a>";echo"<td".($nj[$x]<=9||$nj[$x]==246?" class='number'":"").">$X";}}echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$J;}function
referencable_primary($Ph){$J=array();foreach(table_status('',true)as$_i=>$R){if($_i!=$Ph&&fk_support($R)){foreach(fields($_i)as$m){if($m["primary"]){if($J[$_i]){unset($J[$_i]);break;}$J[$_i]=$m;}}}}return$J;}function
textarea($B,$Y,$L=10,$kb=80){echo"<textarea name='".h($B)."' rows='$L' cols='$kb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($ya,array$bg,$Y="",$Vf="",$Jg=""){$Hi=($bg?"select":"input");return"<$Hi$ya".($bg?"><option value=''>$Jg".optionlist($bg,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Jg'>").($Vf?script("qsl('$Hi').onchange = $Vf;",""):"");}function
json_row($x,$X=null,$Fc=true){static$bd=true;if($bd)echo"{";if($x!=""){echo($bd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($Fc?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$bd=false;}else{echo"\n}\n";$bd=true;}}function
edit_type($x,array$m,array$jb,array$ld=array(),array$Rc=array()){$U=$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'>";if($U&&!array_key_exists($U,driver()->types())&&!isset($ld[$U])&&!in_array($U,$Rc))$Rc[]=$U;$ri=driver()->structuredTypes();if($ld)$ri['Foreign keys']=$ld;echo
optionlist(array_merge($Rc,$ri),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($jb?"<input list='collations' name='".h($x)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'")." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($ld?"<select name='".h($x)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
process_length($y){$Ac=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Ac(?:\\s*,\\s*$Ac)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Ac~",$y,$Ze)?"(".implode(",",$Ze[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_type(array$m,$ib="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~char|text|enum|set~',$m["type"])&&$m["collation"]?" $ib ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$lj){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($lj),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){$k=$m["default"];$sd=$m["generated"];return($k===null?"":(in_array($sd,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($sd=="VIRTUAL"?"":" $sd")."":" GENERATED ALWAYS AS ($k) $sd"):" DEFAULT ".(!preg_match('~^GENERATED ~i',$k)&&(preg_match('~char|binary|text|json|enum|set~',$m["type"])||preg_match('~^(?![a-z])~i',$k))?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}function
edit_fields(array$n,array$jb,$U="TABLE",array$ld=array()){$n=array_values($n);$Tb=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$pb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$Tb>".'Default value',(support("comment")?"<td id='label-comment'$pb>".'Comment':"");echo"<td>".icon("plus","add[".(support("move_col")?0:count($n))."]","+",'Add next'),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");foreach($n
as$s=>$m){$s++;$ig=$m[($_POST?"orig":"field")];$ec=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$ig=="");echo"<tr".($ec?"":" style='display: none;'").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>";if($ec)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$ig);edit_type("fields[$s]",$m,$jb,$ld);if($U=="TABLE")echo"<td>".checkbox("fields[$s][null]",1,$m["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Tb>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default")),"<input name='fields[$s][default]' value='".h($m["default"])."' aria-labelledby='label-default'>",(support("comment")?"<td$pb><input name='fields[$s][comment]' value='".h($m["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ".icon("up","up[$s]","↑",'Move up')." ".icon("down","down[$s]","↓",'Move down')." ":""),($ig==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){$C=0;if($_POST["up"]){$Ie=0;foreach($n
as$x=>$m){if(key($_POST["up"])==$x){unset($n[$x]);array_splice($n,$Ie,0,array($m));break;}if(isset($m["field"]))$Ie=$C;$C++;}}elseif($_POST["down"]){$nd=false;foreach($n
as$x=>$m){if(isset($m["field"])&&$nd){unset($n[key($_POST["down"])]);array_splice($n,$C,0,array($nd));break;}if(key($_POST["down"])==$x)$nd=$m;$C++;}}elseif($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum(array$A){$X=$A[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($ud,array$Zg,$e,$Sf){if(!$Zg)return
true;if($Zg==array("ALL PRIVILEGES","GRANT OPTION"))return($ud=="GRANT"?queries("$ud ALL PRIVILEGES$Sf WITH GRANT OPTION"):queries("$ud ALL PRIVILEGES$Sf")&&queries("$ud GRANT OPTION$Sf"));return
queries("$ud ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Zg).$e).$Sf);}function
drop_create($ic,$h,$kc,$Li,$mc,$Se,$of,$mf,$nf,$Pf,$Bf){if($_POST["drop"])query_redirect($ic,$Se,$of);elseif($Pf=="")query_redirect($h,$Se,$nf);elseif($Pf!=$Bf){$Eb=queries($h);queries_redirect($Se,$mf,$Eb&&queries($ic));if($Eb)queries($kc);}else
queries_redirect($Se,$mf,queries($Li)&&queries($mc)&&queries($ic)&&queries($h));}function
create_trigger($Sf,array$K){$Ri=" $K[Timing] $K[Event]".(preg_match('~ OF~',$K["Event"])?" $K[Of]":"");return"CREATE TRIGGER ".idf_escape($K["Trigger"]).(JUSH=="mssql"?$Sf.$Ri:$Ri.$Sf).rtrim(" $K[Type]\n$K[Statement]",";").";";}function
create_routine($_h,array$K){$O=array();$n=(array)$K["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,"CHARACTER SET");}$Vb=rtrim($K["definition"],";");return"CREATE $_h ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($_h=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(JUSH=="pgsql"?" AS ".q($Vb):"\n$Vb;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key(array$p){$j=$p["db"];$Gf=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($Gf!=""&&$Gf!=$_GET["ns"]?idf_escape($Gf).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"");}function
tar_file($o,$Wi){$J=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Wi->size),decoct(time()));$bb=8*32;for($s=0;$s<strlen($J);$s++)$bb+=ord($J[$s]);$J
.=sprintf("%06o",$bb)."\0 ";echo$J,str_repeat("\0",512-strlen($J));$Wi->send();echo
str_repeat("\0",511-($Wi->size+511)%512);}function
doc_link(array$Fg,$Mi="<sup>?</sup>"){$Vh=connection()->server_info;$Lj=preg_replace('~^(\d\.?\d).*~s','\1',$Vh);$Aj=array('sql'=>"https://dev.mysql.com/doc/refman/$Lj/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Lj)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$Vh)."&id=",);if(connection()->flavor=='maria'){$Aj['sql']="https://mariadb.com/kb/en/";$Fg['sql']=(isset($Fg['mariadb'])?$Fg['mariadb']:str_replace(".html","/",$Fg['sql']));}return($Fg[JUSH]?"<a href='".h($Aj[JUSH].$Fg[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$Lj":""))."'".target_blank().">$Mi</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$J=0;foreach(table_status()as$S)$J+=$S["Data_length"]+$S["Index_length"];return
format_number($J);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Hh=support("scheme");$jb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<thead><tr>".(support("database")?"<td>":"")."<th>".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'>".'Compute'."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$zh=h(ME)."db=".urlencode($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$zh' id='$t'>".h($j)."</a>";$c=h(db_collation($j,$jb));echo"<td>".(support("database")?"<a href='$zh".($Hh?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$c</a>":$c),"<td align='right'><a href='$zh&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n".input_hidden("all").script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".'Drop'."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}if(!empty(adminer()->plugins)){echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach(adminer()->plugins
as$Kg){$Zb=(method_exists($Kg,'description')?$Kg->description():"");if(!$Zb){$oh=new
\ReflectionObject($Kg);if(preg_match('~^/[\s*]+(.+)~',$oh->getDocComment(),$A))$Zb=$A[1];}$Ih=(method_exists($Kg,'screenshot')?$Kg->screenshot():"");echo"<li><b>".get_class($Kg)."</b>".h($Zb?": $Zb":"").($Ih?" (<a href='".h($Ih)."'".target_blank().">".'screenshot'."</a>)":"")."\n";}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.": ".h($_GET["ns"]),'Invalid schema.',true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size;function
__construct(){$this->handler=tmpfile();}function
write($zb){$this->size+=strlen($zb);fwrite($this->handler,$zb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=driver()->select($a,$M,array(where($_GET,$n)),$M);$K=($I?$I->fetch_row():array());echo
driver()->value($K[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=error()?:'No tables.';$S=table_status1($a);$B=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($B!=""?$B:h($a)),$l);$yh=array();foreach($n
as$x=>$m)$yh+=$m["privileges"];adminer()->selectLinks($S,(isset($yh["insert"])||!support("table")?"":null));$ob=$S["Comment"];if($ob!="")echo"<p class='nowrap'>".'Comment'.": ".h($ob)."\n";if($n)adminer()->tableStructurePrint($n,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$R)echo"<li><a href='".h(ME."table=".urlencode($R))."'>".h($R)."</a>";echo"</ul>\n";}$je=driver()->inheritsFrom($a);if($je){echo"<h3>".'Inherits from'."</h3>\n";tables_links($je);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.'Alter indexes'."</a>\n";}if(!is_view($S)){if(fk_support($S)){echo"<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$ld=foreign_keys($a);if($ld){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td></thead>\n";foreach($ld
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".urlencode($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.'Add foreign key'."</a>\n";}if(support("check")){echo"<h3 id='checks'>".'Checks'."</h3>\n";$Xa=driver()->checkConstraints($a);if($Xa){echo"<table>\n";foreach($Xa
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.'Create check'."</a>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<h3 id='triggers'>".'Triggers'."</h3>\n";$kj=triggers($a);if($kj){echo"<table>\n";foreach($kj
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.'Add trigger'."</a>\n";}$ie=driver()->inheritedTables($a);if($ie){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$zg=driver()->partitionsInfo($a);if($zg)echo"<p><code class='jush-".JUSH."'>BY ".h("$zg[partition_by]($zg[partition])")."</code>\n";tables_links($ie);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$Bi=array();$Ci=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Ze,PREG_SET_ORDER);foreach($Ze
as$s=>$A){$Bi[$A[1]]=array($A[2],$A[3]);$Ci[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$Zi=0;$Ga=-1;$Fh=array();$nh=array();$Me=array();$sa=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$Ng=0;$Fh[$R]["fields"]=array();foreach($sa[$R]as$m){$Ng+=1.25;$m["pos"]=$Ng;$Fh[$R]["fields"][$m["field"]]=$m;}$Fh[$R]["pos"]=($Bi[$R]?:array($Zi,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$Ke=$Ga;if(idx($Bi[$R],1)||idx($Bi[$X["table"]],1))$Ke=min(idx($Bi[$R],1,0),idx($Bi[$X["table"]],1,0))-1;else$Ga-=.1;while($Me[(string)$Ke])$Ke-=.0001;$Fh[$R]["references"][$X["table"]][(string)$Ke]=array($X["source"],$X["target"]);$nh[$X["table"]][$R][(string)$Ke]=$X["target"];$Me[(string)$Ke]=true;}}$Zi=max($Zi,$Fh[$R]["pos"][0]+2.5+$Ng);}echo'<div id="schema" style="height: ',$Zi,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(",",$Ci)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$Zi,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';foreach($Fh
as$B=>$R){echo"<div class='table' style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($B).'"><b>'.h($B)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($kh[0]as$fi)echo"\n<div class='references' title='".h($Ji)."' id='refs$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$fi]["pos"]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$Le)."em;'></div></div>";}}foreach((array)$nh[$B]as$Ji=>$ph){foreach($ph
as$Ke=>$e){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($e
as$Ii)echo"\n<div class='references arrow' title='".h($Ji)."' id='refd$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$Ii]["pos"]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Le)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($Fh
as$B=>$R){foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$rf=$Zi;$ff=-10;foreach($kh[0]as$x=>$fi){$Og=$R["pos"][0]+$R["fields"][$fi]["pos"];$Pg=$Fh[$Ji]["pos"][0]+$Fh[$Ji]["fields"][$kh[1][$x]]["pos"];$rf=min($rf,$Og,$Pg);$ff=max($ff,$Og,$Pg);}echo"<div class='references' id='refl$Ke' style='left: $Ke"."em; top: $rf"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($ff-$rf)."em;'></div></div>\n";}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".urlencode($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){save_settings(array_intersect_key($_POST,array_flip(array("output","format","db_style","types","routines","events","table_style","auto_increment","triggers","data_style"))),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Nc=dump_headers((count($T)==1?key($T):DB),(DB==""||count($T)>1));$we=preg_match('~sql~',$_POST["format"]);if($we){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$si=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($we){if($si)echo
use_sql($j,$si).";\n\n";$pg="";if($_POST["types"]){foreach(types()as$t=>$U){$Bc=type_values($t);if($Bc)$pg
.=($si!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($Bc);\n\n";else$pg
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$B=$K["ROUTINE_NAME"];$_h=$K["ROUTINE_TYPE"];$h=create_routine($_h,array("name"=>$B)+routine($K["SPECIFIC_NAME"],$_h));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP $_h IF EXISTS ".idf_escape($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$h;;\n\n";}}echo($pg&&JUSH=='sql'?"DELIMITER ;;\n\n$pg"."DELIMITER ;\n\n":$pg);}if($_POST["table_style"]||$_POST["data_style"]){$Nj=array();foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));$Lb=(DB==""||in_array($B,(array)$_POST["data"]));if($R||$Lb){$Wi=null;if($Nc=="tar"){$Wi=new
TmpFile;ob_start(array($Wi,'write'),1e5);}adminer()->dumpTable($B,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$Nj[]=$B;elseif($Lb){$n=fields($B);adminer()->dumpData($B,$_POST["data_style"],"SELECT *".convert_fields($n,$n)." FROM ".table($B));}if($we&&$_POST["triggers"]&&$R&&($kj=trigger_sql($B)))echo"\nDELIMITER ;;\n$kj\nDELIMITER ;\n";if($Nc=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$Wi);}elseif($we)echo"\n";}}if(function_exists('Adminer\foreign_keys_sql')){foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));if($R&&!is_view($S))echo
foreign_keys_sql($B);}}foreach($Nj
as$Mj)adminer()->dumpTable($Mj,$_POST["table_style"],1);if($Nc=="tar")echo
pack("x512");}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$Pb=array('','USE','DROP+CREATE','CREATE');$Di=array('','DROP+CREATE','CREATE');$Mb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Mb[]='INSERT+UPDATE';$K=get_settings("adminer_export");if(!$K)$K=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");if(!isset($K["events"])){$K["routines"]=$K["events"]=($_GET["dump"]=="");$K["triggers"]=$K["table_style"];}echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$K["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$K["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$Pb,$K["db_style"]).(support("type")?checkbox("types",1,$K["types"],'User types'):"").(support("routine")?checkbox("routines",1,$K["routines"],'Routines'):"").(support("event")?checkbox("events",1,$K["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$Di,$K["table_style"]).checkbox("auto_increment",1,$K["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$K["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Mb,$K["data_style"]),'</table>
<p><input type="submit" value="Export">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");$Tg=array();if(DB!=""){$Za=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Za>".'Tables'."</label>".script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$Za></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Nj="";$Fi=tables_list();foreach($Fi
as$B=>$U){$Sg=preg_replace('~_.*~','',$B);$Za=($a==""||$a==(substr($a,-1)=="%"?"$Sg%":$B));$Wg="<tr><td>".checkbox("tables[]",$B,$Za,$B,"","block");if($U!==null&&!preg_match('~table~i',$U))$Nj
.="$Wg\n";else
echo"$Wg<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Za)."</label>\n";$Tg[$Sg]++;}echo$Nj;if($Fi)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-databases'".($a==""?" checked":"").">".'Database'."</label>",script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);",""),"</thead>\n";$i=adminer()->databases();if($i){foreach($i
as$j){if(!information_schema($j)){$Sg=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Sg%",$j,"","block")."\n";$Tg[$Sg]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$bd=true;foreach($Tg
as$x=>$X){if($x!=""&&$X>1){echo($bd?"<p>":" ")."<a href='".h(ME)."dump=".urlencode("$x%")."'>".h($x)."</a>";$bd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$I=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$ud=$I;if(!$I)$I=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($ud?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<th></thead>\n";while($K=$I->fetch_assoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.'Edit'."</a>\n";if(!$ud||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$Kd=&get_session("queries");$Jd=&$Kd[DB];if(!$l&&$_POST["clear"]){$Jd=array();redirect(remove_from_uri("history"));}stop_session();page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Qe='--'.(JUSH=='sql'?' ':'');if(!$l&&$_POST){$q=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$ji=adminer()->importServerPath();$q=@fopen((file_exists($ji)?$ji:"compress.zlib://$ji.gz"),"rb");$H=($q?fread($q,1e6):false);}else$H=get_file("sql_file",true,";");if(is_string($H)){if(function_exists('memory_get_usage')&&($kf=ini_bytes("memory_limit"))!="-1")@ini_set("memory_limit",max($kf,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$dh=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Jd||first(end($Jd))!=$dh){restart_session();$Jd[]=array($dh,time());set_session("queries",$Kd);stop_session();}}$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$Qe)[^\n]*\n?|--\r?\n)";$Xb=";";$C=0;$wc=true;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$nb=0;$Dc=array();$wg='[\'"'.(JUSH=="sql"?'`#':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Qe.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$aj=microtime(true);$ma=get_settings("adminer_import");while($H!=""){if(!$C&&preg_match("~^$gi*+DELIMITER\\s+(\\S+)~i",$H,$A)){$Xb=preg_quote($A[1]);$H=substr($H,strlen($A[0]));}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^($gi*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$A)){$Xb="\n\\\\\\.\r?\n";$C=strlen($A[0]);}else{preg_match("($Xb\\s*|$wg)",$H,$A,PREG_OFFSET_CAPTURE,$C);list($nd,$Ng)=$A[0];if(!$nd&&$q&&!feof($q))$H
.=fread($q,1e5);else{if(!$nd&&rtrim($H)=="")break;$C=$Ng+strlen($nd);if($nd&&!preg_match("(^$Xb)",$nd)){$Ra=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Ng>0&&strtolower($H[$Ng-1])=="e"));$Gg=($nd=='/*'?'\*/':($nd=='['?']':(preg_match("~^$Qe|^#~",$nd)?"\n":preg_quote($nd).($Ra?'|\\\\.':''))));while(preg_match("($Gg|\$)s",$H,$A,PREG_OFFSET_CAPTURE,$C)){$Dh=$A[0][0];if(!$Dh&&$q&&!feof($q))$H
.=fread($q,1e5);else{$C=$A[0][1]+strlen($Dh);if(!$Dh||$Dh[0]!="\\")break;}}}else{$wc=false;$dh=substr($H,0,$Ng+($Xb[0]=="\n"?3:0));$nb++;$Wg="<pre id='sql-$nb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($dh)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$gi*+ATTACH\\b~i",$dh,$A)){echo$Wg,"<p class='error'>".'ATTACH queries are not supported.'."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Wg;ob_flush();flush();}$oi=microtime(true);if(connection()->multi_query($dh)&&$g&&preg_match("~^$gi*+USE\\b~i",$dh))$g->query($dh);do{$I=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Wg:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break
2;}else{$Pi=" <span class='time'>(".format_time($oi).")</span>".(strlen($dh)<1000?" <a href='".h(ME)."sql=".urlencode(trim($dh))."'>".'Edit'."</a>":"");$oa=connection()->affected_rows;$Qj=($_POST["only_errors"]?"":driver()->warnings());$Rj="warnings-$nb";if($Qj)$Pi
.=", <a href='#$Rj'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$Rj');","");$Lc=null;$hg=null;$Mc="explain-$nb";if(is_object($I)){$z=$_POST["limit"];$hg=print_select_result($I,$g,array(),$z);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$If=$I->num_rows;echo"<p class='sql-footer'>".($If?($z&&$If>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$If):""),$Pi;if($g&&preg_match("~^($gi|\\()*+SELECT\\b~i",$dh)&&($Lc=explain($g,$dh)))echo", <a href='#$Mc'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Mc');","");$t="export-$nb";echo", <a href='#$t'>".'Export'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."<span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ma["output"])." ".html_select("format",adminer()->dumpFormat(),$ma["format"]).input_hidden("query",$dh)."<input type='submit' name='export' value='".'Export'."'>".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$gi*+(CREATE|DROP|ALTER)$gi++(DATABASE|SCHEMA)\\b~i",$dh)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$oa)."$Pi\n";}echo($Qj?"<div id='$Rj' class='hidden'>\n$Qj</div>\n":"");if($Lc){echo"<div id='$Mc' class='hidden explain'>\n";print_select_result($Lc,$g,$hg);echo"</div>\n";}}$oi=microtime(true);}while(connection()->next_result());}$H=substr($H,$C);$C=0;}}}}if($wc)echo"<p class='message'>".'No commands to execute.'."\n";elseif($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$nb-count($Dc))," <span class='time'>(".format_time($aj).")</span>\n";elseif($Dc&&$nb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Dc)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form">
';$Jc="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$dh=$_GET["sql"];if($_POST)$dh=$_POST["query"];elseif($_GET["history"]=="all")$dh=$Jd;elseif($_GET["history"]!="")$dh=idx($Jd[$_GET["history"]],0);echo"<p>";textarea("query",$dh,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"<p>";adminer()->sqlPrintAfter();echo"$Jc\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$_d=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",file_input("SQL$_d: <input type='file' name='sql_file[]' multiple>\n$Jc"),"</div></fieldset>\n";$Vd=adminer()->importServerPath();if($Vd)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Vd)."$_d</code>"),' <input type="submit" name="webfile" value="'.'Run file'.'">',"</div></fieldset>\n";echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Jd){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($Jd);$X;$X=prev($Jd)){$x=key($Jd);list($dh,$Pi,$rc)=$X;echo'<a href="'.h(ME."sql=&history=$x").'">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Pi)."'>".@date("H:i:s",$Pi)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$Qe).*~m",'',$dh)))),80,"</code>").($rc?" <span class='time'>($rc)</span>":"")."<br>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$wj=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if(!isset($m["privileges"][$wj?"update":"insert"])||adminer()->fieldName($m)==""||$m["generated"])unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$Se=$_POST["referer"];if($_POST["insert"])$Se=($wj?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Se))$Se=ME."select=".urlencode($a);$w=indexes($a);$rj=unique_array($_GET["where"],$w);$gh="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Se,'Item has been deleted.',driver()->delete($a,$gh,$rj?0:1));else{$O=array();foreach($n
as$B=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($B)]=$X;}if($wj){if(!$O)redirect($Se);queries_redirect($Se,'Item has been updated.',driver()->update($a,$O,$gh,$rj?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$I=driver()->insert($a,$O);$Je=($I?last_id($I):0);queries_redirect($Se,sprintf('Item%s has been inserted.',($Je?" $Je":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$wa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$M[]=($wa?"$wa AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$l=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$n){if(!$Z){$I=driver()->select($a,array("*"),array(),array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array(driver()->primary=>"");}if($K){foreach($K
as$x=>$X){if(!$Z)$K[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}edit_form($a,$n,$K,$wj,$l);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Ag=driver()->partitionBy;$Dg=($Ag?driver()->partitionsInfo($a):array());$mh=referencable_primary($a);$ld=array();foreach($mh
as$_i=>$m)$ld[str_replace("`","``",$_i)."`".str_replace("`","``",$m["field"])]=$_i;$kg=array();$S=array();if($a!=""){$kg=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($K["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$sa=array();$Bj=false;$jd=array();$jg=reset($kg);$qa=" FIRST";foreach($K["fields"]as$x=>$m){$p=$ld[$m["type"]];$lj=($p!==null?$mh[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$bh=process_field($m,$lj);$sa[]=array($m["orig"],$bh,$qa);if(!$jg||$bh!==process_field($jg,$jg)){$n[]=array($m["orig"],$bh,$qa);if($m["orig"]!=""||$qa)$Bj=true;}if($p!==null)$jd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$ld[$m["type"]],'source'=>array($m["field"]),'target'=>array($lj["field"]),'on_delete'=>$m["on_delete"],));$qa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$Bj=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$jg=next($kg);if(!$jg)$qa="";}}$E=array();if(in_array($K["partition_by"],$Ag)){foreach($K
as$x=>$X){if(preg_match('~^partition~',$x))$E[$x]=$X;}foreach($E["partition_names"]as$x=>$B){if($B==""){unset($E["partition_names"][$x]);unset($E["partition_values"][$x]);}}$E["partition_names"]=array_values($E["partition_names"]);$E["partition_values"]=array_values($E["partition_values"]);if($E==$Dg)$E=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$E=null;$lf='Table has been altered.';if($a==""){cookie("adminer_engine",$K["Engine"]);$lf='Table has been created.';}$B=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($B),$lf,alter_table($a,$B,(JUSH=="sqlite"&&($Bj||$jd)?$sa:$n),$jd,($K["Comment"]!=$S["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$S["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$S["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$E));}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$nj=driver()->types();$K=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($nj["int"])?"int":(isset($nj["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$K=$S;$K["name"]=$a;$K["fields"]=array();if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($kg
as$m){$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$K["fields"][]=$m;}if($Ag){$K+=$Dg;$K["partition_names"][]="";$K["partition_values"][]="";}}}$jb=collations();if(is_array(reset($jb)))$jb=call_user_func_array('array_merge',array_values($jb));$yc=driver()->engines();foreach($yc
as$xc){if(!strcasecmp($xc,$K["Engine"])){$K["Engine"]=$xc;break;}}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($K["name"])."' autocapitalize='off'>\n",($yc?html_select("Engine",array(""=>"(".'engine'.")")+$yc,$K["Engine"]).on_help("event.target.value",1).script("qsl('select').onchange = helpClose;")."\n":"");if($jb)echo"<datalist id='collations'>".optionlist($jb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($K["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$jb,"TABLE",$ld);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($K["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',"columnShow(this.checked, 5)","jsonly");$qb=($_POST?$_POST["comments"]:get_setting("comments"));echo(support("comment")?checkbox("comments",1,$qb,'Comment',"editingCommentsClick(this, true);","jsonly").' '.(preg_match('~\n~',$K["Comment"])?"<textarea name='Comment' rows='2' cols='20'".($qb?"":" class='hidden'").">".h($K["Comment"])."</textarea>":'<input name="Comment" value="'.h($K["Comment"]).'" data-maxlength="'.(min_version(5.5)?2048:60).'"'.($qb?"":" class='hidden'").'>'):''),'<p>
<input type="submit" value="Save">
';}echo'
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));if($Ag&&(JUSH=='sql'||$a=="")){$Bg=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset("partition",'Partition by',$K["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Ag),$K["partition_by"]).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')",1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($K["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($Bg||!$K["partition_by"]?" hidden":"")."' value='".h($K["partitions"])."'>\n","<table id='partition-table'".($Bg?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."</thead>\n";foreach($K["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($x==count($K["partition_names"])-1?script("qsl('input').oninput = partitionNameChange;"):''),'<td><input name="partition_values[]" value="'.h(idx($K["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$de=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$ae=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="SPATIAL";$w=indexes($a);$n=fields($a);$G=array();if(JUSH=="mongo"){$G=$w["_id_"];unset($de[0]);unset($w["_id_"]);}$K=$_POST;if($K)save_settings(array("index_options"=>$K["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($K["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$de)){$e=array();$Oe=array();$ac=array();$be=(support("partial_indexes")?$v["partial"]:"");$Zd=(in_array($v["algorithm"],$ae)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$Yb=idx($v["descs"],$x);$O[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Yb?" DESC":"");$e[]=$d;$Oe[]=($y?:null);$ac[]=$Yb;}}$Kc=$w[$B];if($Kc){ksort($Kc["columns"]);ksort($Kc["lengths"]);ksort($Kc["descs"]);if($v["type"]==$Kc["type"]&&array_values($Kc["columns"])===$e&&(!$Kc["lengths"]||array_values($Kc["lengths"])===$Oe)&&array_values($Kc["descs"])===$ac&&$Kc["partial"]==$be&&(!$ae||$Kc["algorithm"]==$Zd)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$O,$Zd,$be);}}foreach($w
as$B=>$Kc)$b[]=array($Kc["type"],$B,"DROP");if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$Yc=array_keys($n);if($_POST["add"]){foreach($K["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$K["indexes"][$x]["columns"][]="";}$v=end($K["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$K["indexes"][]=array("columns"=>array(1=>""));}if(!$K){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$K["indexes"]=$w;}$Oe=(JUSH=="sql"||JUSH=="mssql");$ai=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';$Td=" class='idxopts".($ai?"":" hidden")."'";if($ae)echo"<th id='label-algorithm'$Td>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" class="wayoff">','Columns'.($Oe?"<span$Td> (".'length'.")</span>":"");if($Oe||support("descidx"))echo
checkbox("options",1,$ai,'Options',"indexOptionsShow(this.checked)","jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Td>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
</thead>
';if($G){echo"<tr><td>PRIMARY<td>";foreach($G["columns"]as$x=>$d)echo
select_input(" disabled",$Yc,$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$ze=1;foreach($K["indexes"]as$v){if(!$_POST["drop_col"]||$ze!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$ze][type]",array(-1=>"")+$de,$v["type"],($ze==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type");if($ae)echo"<td$Td>".html_select("indexes[$ze][algorithm]",array_merge(array(""),$ae),$v['algorithm'],"label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$ze][columns][$s]' title='".'Column'."'",($n&&($d==""||$n[$d])?array_combine($Yc,$Yc):array()),$d,"partial(".($s==count($v["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(JUSH=="sql"?"":$_GET["indexes"]."_")."')"),"<span$Td>",($Oe?"<input type='number' name='indexes[$ze][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":""),(support("descidx")?checkbox("indexes[$ze][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"</span> </span>";$s++;}echo"<td><input name='indexes[$ze][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Td><input name='indexes[$ze][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$ze]","x",'Remove').script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");}$ze++;}echo'</table>
</div>
<p>
<input type="submit" value="Save">
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),'Database has been renamed.',rename_database($B,$K["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$ti=true;$Ie="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,$K["collation"]))$ti=false;$Ie=$j;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Ie),'Database has been created.',$ti);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$jb=collations();$B=DB;if($_POST)$B=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,$jb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$ud){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$ud,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n".($jb?html_select("collation",array(""=>"(".'collation'.")")+$jb,$K["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):""),'<input type="submit" value="Save">
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',DB))."\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$K=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$B=trim($K["name"]);$_
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,'Schema has been created.');elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$K)$K["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$_GET["ns"]))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$_h=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Wd=array();$pg=array();foreach($_h["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$pg[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Wd[]=$s;}if(!$l&&$_POST){$Sa=array();foreach($_h["fields"]as$x=>$m){$X="";if(in_array($x,$Wd)){$X=process_input($m);if($X===false)$X="''";if(isset($pg[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($pg[$x]))$Sa[]="@".idf_escape($m["field"]);elseif(in_array($x,$Wd))$Sa[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($_h["returns"]["type"]=="record"?"* FROM ":"").table($ba)."(".implode(", ",$Sa).")";$oi=microtime(true);$I=connection()->multi_query($H);$oa=connection()->affected_rows;echo
adminer()->selectQuery($H,$oi,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$I=connection()->store_result();if(is_object($I))print_select_result($I,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$oa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($pg)print_select_result(connection()->query("SELECT ".implode(", ",$pg)));}}echo'
<form action="" method="post">
';if($Wd){echo"<table class='layout'>\n";foreach($Wd
as$x){$m=$_h["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$B);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type="submit" value="Call">
',input_token(),'</form>

<pre>
';function
pre_tr($Dh){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($Dh))));}$R='(\+--[-+]+\+\n)';$K='(\| .* \|\n)';echo
preg_replace_callback("~^$R?$K$R?($K*)$R?~m",function($A){$cd=pre_tr($A[2]);return"<table>\n".($A[1]?"<thead>$cd</thead>\n":$cd).pre_tr($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($_h['comment']))));echo'</pre>
';}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$K=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$Ii=array();foreach($K["source"]as$x=>$X)$Ii[$x]=$K["target"][$x];$K["target"]=$Ii;}if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(" $B"=>($K["drop"]?"":" ".format_foreign_key($K))));else{$b="ALTER TABLE ".table($a);$I=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$K["drop"])$I=queries("$b ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Foreign key has been dropped.':($B!=""?'Foreign key has been altered.':'Foreign key has been created.')),$I);if(!$K["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header('Foreign key',$l,array("table"=>$a),h($a));if($_POST){ksort($K["source"]);if($_POST["add"])$K["source"][]="";elseif($_POST["change"]||$_POST["change-js"])$K["target"]=array();}elseif($B!=""){$ld=foreign_keys($a);$K=$ld[$B];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=array("");}echo'
<form action="" method="post">
';$fi=array_keys(fields($a));if($K["db"]!="")connection()->select_db($K["db"]);if($K["ns"]!=""){$lg=get_schema();set_schema($K["ns"]);}$lh=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$Ii=array_keys(fields(in_array($K["table"],$lh)?$K["table"]:reset($lh)));$Vf="this.form['change-js'].value = '1'; this.form.submit();";echo"<p><label>".'Target table'.": ".html_select("table",$lh,$K["table"],$Vf)."</label>\n";if(support("scheme")){$Gh=array_filter(adminer()->schemas(),function($Fh){return!preg_match('~^information_schema$~i',$Fh);});echo"<label>".'Schema'.": ".html_select("ns",$Gh,$K["ns"]!=""?$K["ns"]:$_GET["ns"],$Vf)."</label>";if($K["ns"]!="")set_schema($lg);}elseif(JUSH!="sqlite"){$Qb=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$Qb[]=$j;}echo"<label>".'DB'.": ".html_select("db",$Qb,$K["db"]!=""?$K["db"]:$_GET["db"],$Vf)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';$ze=0;foreach($K["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$fi,$X,($ze==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Ii,idx($K["target"],$x),"","label-target");$ze++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$K["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$K["on_update"]),'</label>
',doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$mg="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$mg=strtoupper($P["Engine"]);}if($_POST&&!$l){$B=trim($K["name"]);$wa=" AS\n$K[select]";$Se=ME."table=".urlencode($B);$lf='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$U=="VIEW"&&$mg=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$wa,$Se,$lf);else{$Ki=$B."_adminer_".uniqid();drop_create("DROP $mg ".table($a),"CREATE $U ".table($B).$wa,"DROP $U ".table($B),"CREATE $U ".table($Ki).$wa,"DROP $U ".table($Ki),($_POST["drop"]?substr(ME,0,-1):$Se),'View has been dropped.',$lf,'View has been created.',$a,$B);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($mg!="VIEW");if(!$l)$l=error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$K["materialized"],'Materialized view'):""),'<p>';textarea("select",$K["select"]);echo'<p>
<input type="submit" value="Save">
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$qe=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$pi=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$K=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($K["INTERVAL_FIELD"],$qe)&&isset($pi[$K["STATUS"]])){$Eh="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Eh.($aa!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Eh)."\n".$pi[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$K&&$aa!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$K=reset($L);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($K["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$K[EXECUTE_AT]$K[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($K["ENDS"]),'">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="',h($K["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$qe,$K["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$pi,$K["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($K["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo'<p>
<input type="submit" value="Save">
';if($aa!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$aa));echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$_h=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])&&!$l){$ig=routine($_GET["procedure"],$_h);$Ki="$K[name]_adminer_".uniqid();foreach($K["fields"]as$x=>$m){if($m["field"]=="")unset($K["fields"][$x]);}drop_create("DROP $_h ".routine_id($ba,$ig),create_routine($_h,$K),"DROP $_h ".routine_id($K["name"],$K),create_routine($_h,array("name"=>$Ki)+$K),"DROP $_h ".routine_id($Ki,$K),substr(ME,0,-1),'Routine has been dropped.','Routine has been altered.','Routine has been created.',$ba,$K["name"]);}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$_h);$K["name"]=$ba;}}$jb=get_vals("SHOW CHARACTER SET");sort($jb);$Ah=routine_languages();echo($jb?"<datalist id='collations'>".optionlist($jb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',($Ah?"<label>".'Language'.": ".html_select("language",$Ah,$K["language"])."</label>\n":""),'<input type="submit" value="Save">
<div class="scrollable">
<table class="nowrap">
';edit_fields($K["fields"],$jb,$_h);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$K["returns"],$jb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$K["definition"],20);echo'<p>
<input type="submit" value="Save">
';if($ba!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$ba));echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($K["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,'Sequence has been created.');elseif($da!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($B),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$K)$K["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$da))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){$ea=$_GET["type"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ea),$_,'Type has been dropped.');else
query_redirect("CREATE TYPE ".idf_escape(trim($K["name"]))." $K[as]",$_,'Type has been created.');}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$K)$K["as"]="AS ";echo'
<form action="" method="post">
<p>
';if($ea!=""){$nj=driver()->types();$Bc=type_values($nj[$ea]);if($Bc)echo"<code class='jush-".JUSH."'>ENUM (".h($Bc).")</code>\n<p>";echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$ea))."\n";}else{echo'Name'.": <input name='name' value='".h($K['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"datatype-enum.html",),"?");textarea("as",$K["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$K=$_POST;if($K&&!$l){if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($K["drop"]?"":$K["clause"]));else{$I=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$K["drop"])$I=queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Check has been dropped.':($B!=""?'Check has been altered.':'Check has been created.')),$I);}page_header(($B!=""?'Alter check'.": ".h($B):'Create check'),$l,array("table"=>$a));if(!$K){$ab=driver()->checkConstraints($a);$K=array("name"=>$B,"clause"=>$ab[$B]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($K["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$K["clause"]);echo'<p><input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$jj=trigger_options();$K=(array)trigger($B,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$jj["Timing"])&&in_array($_POST["Event"],$jj["Event"])&&in_array($_POST["Type"],$jj["Type"])){$Sf=" ON ".table($a);$ic="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Sf:"");$Se=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ic,$Se,'Trigger has been dropped.');else{if($B!="")queries($ic);queries_redirect($Se,($B!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Sf,$_POST)));if($B!="")queries(create_trigger($Sf,$K+array("Type"=>reset($jj["Type"]))));}}$K=$_POST;}page_header(($B!=""?'Alter trigger'.": ".h($B):'Create trigger'),$l,array("table"=>$a));echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>',html_select("Timing",$jj["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>Event<td>',html_select("Event",$jj["Event"],$K["Event"],"this.form['Timing'].onchange();"),(in_array("UPDATE OF",$jj["Event"])?" <input name='Of' value='".h($K["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$jj["Type"],$K["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($K["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';textarea("Statement",$K["Statement"]);echo'<p>
<input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$fa=$_GET["user"];$Zg=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$_b)$Zg[$_b][$K["Privilege"]]=$K["Comment"];}$Zg["Server Admin"]+=$Zg["File access on server"];$Zg["Databases"]["Create routine"]=$Zg["Procedures"]["Create routine"];unset($Zg["Procedures"]["Create routine"]);$Zg["Columns"]=array();foreach(array("Select","Insert","Update","References")as$X)$Zg["Columns"][$X]=$Zg["Tables"][$X];unset($Zg["Server Admin"]["Usage"]);foreach($Zg["Tables"]as$x=>$X)unset($Zg["Databases"][$x]);$Af=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$Af[$X]=(array)$Af[$X]+idx($_POST["grants"],$x,array());}$vd=array();$Qf="";if(isset($_GET["host"])&&($I=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($K=$I->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Ze,PREG_SET_ORDER)){foreach($Ze
as$X){if($X[1]!="USAGE")$vd["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$vd["$A[2]$X[2]"]["GRANT OPTION"]=true;}}if(preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~",$K[0],$A))$Qf=$A[1];}}if($_POST&&!$l){$Rf=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Rf",ME."privileges=",'User has been dropped.');else{$Cf=q($_POST["user"])."@".q($_POST["host"]);$Eg=$_POST["pass"];if($Eg!=''&&!$_POST["hashed"]&&!min_version(8)){$Eg=get_val("SELECT PASSWORD(".q($Eg).")");$l=!$Eg;}$Eb=false;if(!$l){if($Rf!=$Cf){$Eb=queries((min_version(5)?"CREATE USER":"GRANT USAGE ON *.* TO")." $Cf IDENTIFIED BY ".(min_version(8)?"":"PASSWORD ").q($Eg));$l=!$Eb;}elseif($Eg!=$Qf)queries("SET PASSWORD FOR $Cf = ".q($Eg));}if(!$l){$xh=array();foreach($Af
as$Kf=>$ud){if(isset($_GET["grant"]))$ud=array_filter($ud);$ud=array_keys($ud);if(isset($_GET["grant"]))$xh=array_diff(array_keys(array_filter($Af[$Kf],'strlen')),$ud);elseif($Rf==$Cf){$Of=array_keys((array)$vd[$Kf]);$xh=array_diff($Of,$ud);$ud=array_diff($ud,$Of);unset($vd[$Kf]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Kf,$A)&&(!grant("REVOKE",$xh,$A[2]," ON $A[1] FROM $Cf")||!grant("GRANT",$ud,$A[2]," ON $A[1] TO $Cf"))){$l=true;break;}}}if(!$l&&isset($_GET["host"])){if($Rf!=$Cf)queries("DROP USER $Rf");elseif(!isset($_GET["grant"])){foreach($vd
as$Kf=>$xh){if(preg_match('~^(.+)(\(.*\))?$~U',$Kf,$A))grant("REVOKE",array_keys($xh),$A[2]," ON $A[1] FROM $Cf");}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),!$l);if($Eb)connection()->query("DROP USER $Cf");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$K=$_POST;if($K)$vd=$Af;else{$K=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$K["pass"]=$Qf;if($Qf!="")$K["hashed"]=true;$vd[(DB==""||$vd?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($K["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($K["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($K["pass"]),'" autocomplete="new-password">
',($K["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8)?"":checkbox("hashed",1,$K["hashed"],'Hashed',"typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($vd
as$Kf=>$ud){echo'<th>'.($Kf!="*.*"?"<input name='objects[$s]' value='".h($Kf)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"</thead>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Columns"=>'Column',"Procedures"=>'Routine',)as$_b=>$Yb){foreach((array)$Zg[$_b]as$Yg=>$ob){echo"<tr><td".($Yb?">$Yb<td":" colspan='2'").' lang="en" title="'.h($ob).'">'.h($Yg);$s=0;foreach($vd
as$Kf=>$ud){$B="'grants[$s][".h(strtoupper($Yg))."]'";$Y=$ud[strtoupper($Yg)];if($_b=="Server Admin"&&$Kf!=(isset($vd["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($Y?" checked":"").($Yg=="All privileges"?" id='grants-$s-all'>":">".($Yg=="Grant option"?"":script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$s-all'); };"))),"</label>";$s++;}}}echo"</table>\n",'<p>
<input type="submit" value="Save">
';if(isset($_GET["host"]))echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',"$fa@$_GET[host]"));echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Ee=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$Ee++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$Ee),$Ee||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");$s=-1;foreach(adminer()->processList()as$s=>$K){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"</thead>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[JUSH=="sql"?"Id":"pid"],0):"");foreach($K
as$x=>$X)echo"<td>".((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$K["Command"])&&$X!="")||(JUSH=="pgsql"&&$x=="current_query"&&$X!="<IDLE>")||(JUSH=="oracle"&&$x=="sql_text"&&$X!="")?"<code class='jush-".JUSH."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.'Clone'.'</a>':h($X));echo"\n";}echo'</table>
</div>
<p>
';if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$ld=column_foreign_keys($a);$Mf=$S["Oid"];$na=get_settings("adminer_import");$yh=array();$e=array();$Lh=array();$eg=array();$Oi="";foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$zf=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$zf;if(is_shortable($m))$Oi=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$Lh[$x]=$zf;if(isset($m["privileges"]["order"])&&$B!="")$eg[$x]=$zf;$yh+=$m["privileges"];}list($M,$wd)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$wd=array_unique($wd);$ue=count($wd)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$dg=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$sj=>$K){$wa=convert_field($n[key($K)]);$M=array($wa?:idf_escape(key($K)));$Z[]=where_check($sj,$n);$J=driver()->select($a,$M,$Z,$M);if($J)echo
first($J->fetch_row());}exit;}$G=$uj=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$G=array_flip($v["columns"]);$uj=($M?$G:array());foreach($uj
as$x=>$X){if(in_array(idf_escape($x),$M))unset($uj[$x]);}break;}}if($Mf&&!$G){$G=$uj=array($Mf=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Mf));}if($_POST&&!$l){$Tj=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ab=array();foreach($_POST["check"]as$Wa)$ab[]=where_check($Wa,$n);$Tj[]="((".implode(") OR (",$ab)."))";}$Tj=($Tj?"\nWHERE ".implode(" AND ",$Tj):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$pd=($M?implode(", ",$M):"*").convert_fields($e,$n,$M)."\nFROM ".table($a);$yd=($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):"");$H="SELECT $pd$Tj$yd";if(is_array($_POST["check"])&&!$G){$qj=array();foreach($_POST["check"]as$X)$qj[]="(SELECT".limit($pd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$yd,1).")";$H=implode(" UNION ALL ",$qj);}adminer()->dumpData($a,"table",$H);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$ld)){if($_POST["save"]||$_POST["delete"]){$I=true;$oa=0;$O=array();if(!$_POST["delete"]){foreach($_POST["fields"]as$B=>$X){$X=process_input($n[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$O){$H=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($G&&is_array($_POST["check"]))||$ue){$I=($_POST["delete"]?driver()->delete($a,$Tj):($_POST["clone"]?queries("INSERT $H$Tj".driver()->insertReturning($a)):driver()->update($a,$O,$Tj)));$oa=connection()->affected_rows;if(is_object($I))$oa+=$I->num_rows;}else{foreach((array)$_POST["check"]as$X){$Sj="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$I=($_POST["delete"]?driver()->delete($a,$Sj,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Sj)):driver()->update($a,$O,$Sj,1)));if(!$I)break;$oa+=connection()->affected_rows;}}}$lf=lang_format(array('%d item has been affected.','%d items have been affected.'),$oa);if($_POST["clone"]&&$I&&$oa==1){$Je=last_id($I);if($Je)$lf=sprintf('Item%s has been inserted.'," $Je");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$lf,$I);if(!$_POST["delete"]){$Qg=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$Qg),$Qg,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$l='Ctrl+click on a value to modify it.';else{$I=true;$oa=0;foreach($_POST["val"]as$sj=>$K){$O=array();foreach($K
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$I=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($sj,$n),($ue||$G?0:1)," ");if(!$I)break;$oa+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$oa),$I);}}elseif(!is_string($Zc=get_file("csv_file",true)))$l=upload_error($Zc);elseif(!preg_match('~~u',$Zc))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$na["output"],"format"=>$_POST["separator"]),"adminer_import");$I=true;$kb=array_keys($n);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Zc,$Ze);$oa=count($Ze[0]);driver()->begin();$Rh=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Ze[0]as$x=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Rh]*)$Rh~",$X.$Rh,$af);if(!$x&&!array_diff($af[1],$kb)){$kb=$af[1];$oa--;}else{$O=array();foreach($af[1]as$s=>$hb)$O[idf_escape($kb[$s])]=($hb==""&&$n[$kb[$s]]["null"]?"NULL":q(preg_match('~^".*"$~s',$hb)?str_replace('""','"',substr($hb,1,-1)):$hb));$L[]=$O;}}$I=(!$L||driver()->insertUpdate($a,$L,$G));if($I)driver()->commit();queries_redirect(remove_from_uri("page"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$oa),$I);driver()->rollback();}}}$_i=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $_i",$l);$O=null;if(isset($yh["insert"])||!support("table")){$vg=array();foreach((array)$_GET["where"]as$X){if(isset($ld[$X["col"]])&&count($ld[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$vg["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$vg?"&".http_build_query($vg):"";}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$Lh,$w);adminer()->selectOrderPrint($dg,$eg,$w);adminer()->selectLimitPrint($z);adminer()->selectLengthPrint($Oi);adminer()->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];$od=null;if($D=="last"){$od=get_val(count_rows($a,$Z,$ue,$wd));$D=floor(max(0,intval($od)-1)/$z);}$Mh=$M;$xd=$wd;if(!$Mh){$Mh[]="*";$Ab=convert_fields($e,$n,$M);if($Ab)$Mh[]=substr($Ab,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($wa=convert_field($m)))$Mh[$x]="$wa AS $X";}if(!$ue&&$uj){foreach($uj
as$x=>$X){$Mh[]=idf_escape($x);if($xd)$xd[]=idf_escape($x);}}$I=driver()->select($a,$Mh,$Z,$xd,$dg,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$D)$I->seek($z*$D);$vc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql")$od=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ea=adminer()->backwardKeys($a,$_i);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$wd&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$_f=array();$rd=array();reset($M);$ih=1;foreach($L[0]as$x=>$X){if(!isset($uj[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$B=($m?adminer()->fieldName($m,$ih):($X["fun"]?"*":h($x)));if($B!=""){$ih++;$_f[$x]=$B;$d=idf_escape($x);$Nd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$Yb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$qd=apply_sql_function($X["fun"],$B);$ei=isset($m["privileges"]["order"])||$qd;echo($ei?"<a href='".h($Nd.($dg[0]==$d||$dg[0]==$x?$Yb:''))."'>$qd</a>":$qd),"<span class='column hidden'>";if($ei)echo"<a href='".h($Nd.$Yb)."' title='".'descending'."' class='text'> ↓</a>";if(!$X["fun"]&&isset($m["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");echo"</span>";}$rd[$x]=$X["fun"];next($M);}}$Oe=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$x=>$X)$Oe[$x]=max($Oe[$x],min(40,strlen(utf8_decode($X))));}}echo($Ea?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$ld)as$yf=>$K){$rj=unique_array($L[$yf],$w);if(!$rj){$rj=array();reset($M);foreach($L[$yf]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$rj[$x]=$X;next($M);}}$sj="";foreach($rj
as$x=>$X){$m=(array)$n[$x];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$m["type"])&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($X);}$sj
.="&".($X!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($x));}echo"<tr>".(!$wd&&$M?"":"<td>".checkbox("check[]",substr($sj,1),in_array(substr($sj,1),(array)$_POST["check"])).($ue||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$sj)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$x=>$X){if(isset($_f[$x])){$d=current($M);$m=(array)$n[$x];$X=driver()->value($X,$m);if($X!=""&&(!isset($vc[$x])||$vc[$x]!=""))$vc[$x]=(is_mail($X)?$_f[$x]:"");$_="";if(is_blob($m)&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($x).$sj;if(!$_&&$X!==null){foreach((array)$ld[$x]as$p){if(count($ld[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$fi)$_
.=where_link($s,$p["target"][$s],$L[$yf][$fi]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$rj))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($rj
as$Ae=>$W)$_
.=where_link($s++,$Ae,$W);}$Od=select_value($X,$_,$m,$Oi);$t=h("val[$sj][".bracket_escape($x)."]");$Rg=idx(idx($_POST["val"],$sj),bracket_escape($x));$qc=!is_array($K[$x])&&is_utf8($Od)&&$L[$yf][$x]==$K[$x]&&!$rd[$x]&&!$m["generated"];$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Mi=preg_match('~text|json|lob~',$U);$ve=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($ve&&($X===null||is_numeric(strip_tags($Od))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$qc&&$X!==null)||$Rg!==null){$Ad=h($Rg!==null?$Rg:$K[$x]);echo">".($Mi?"<textarea name='$t' cols='30' rows='".(substr_count($K[$x],"\n")+1)."'>$Ad</textarea>":"<input name='$t' value='$Ad' size='$Oe[$x]'>");}else{$Ue=strpos($Od,"<i>…</i>");echo" data-text='".($Ue?2:($Mi?1:0))."'".($qc?"":" data-warning='".h('Use edit link to modify this value.')."'").">$Od";}}next($M);}if($Ea)echo"<td>";adminer()->backwardKeysPrint($Ea,$L[$yf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Ic=true;if($_GET["page"]!="last"){if(!$z||(count($L)<$z&&($L||!$D)))$od=($D?$D*$z:0)+count($L);elseif(JUSH!="sql"||!$ue){$od=($ue?false:found_rows($S,$Z));if(intval($od)<max(1e4,2*($D+1)*$z))$od=first(slow_query(count_rows($a,$Z,$ue,$wd)));else$Ic=false;}}$tg=($z&&($od===false||$od>$z||$D));if($tg)echo(($od===false?count($L)+1:$od-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $z, '".'Loading'."…');",""):''),"\n";echo"<div class='footer'><div>\n";if($tg){$ef=($od===false?$D+(count($L)>=$z?2:1):floor(($od-1)/$z));echo"<fieldset>";if(JUSH!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($ef,$D+5);$s++)echo
pagination($s,$D);if($ef>0)echo($D+5<$ef?" …":""),($Ic&&$od!==false?pagination($ef,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$ef'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($ef>$D?pagination($D+1,$D).($ef>$D+1?" …":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$fc=($Ic?"":"~ ").$od;$Wf="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$fc' : checked); selectCount('selected2', this.checked || !checked ? '$fc' : checked);";echo
checkbox("all",1,0,($od!==false?($Ic?"":"~ ").lang_format(array('%d row','%d rows'),$od):""),$Wf)."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';$md=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($md['sql']);break;}}if($md){print_fieldset("export",'Export'." <span id='selected2'></span>");$qg=adminer()->dumpOutput();echo($qg?html_select("output",$qg,$na["output"])." ":""),html_select("format",$md,$na["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($vc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",file_input("<input type='file' name='csv_file'> ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$na["format"])." <input type='submit' name='import' value='".'Import'."'>"),"</span>";echo
input_token(),"</form>\n",(!$wd&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$Jj=($P?show_status():show_variables());if(!$Jj)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($Jj
as$K){echo"<tr>";$x=array_shift($K);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($K
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$wi=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$S){json_row("Comment-$B",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($S[$x]));foreach($wi+array("Auto_increment"=>0,"Rows"=>0)as$x=>$X){if($S[$x]!=""){$X=format_number($S[$x]);if($X>=0)json_row("$x-$B",($x=="Rows"&&$X&&$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?"~ $X":$X));if(isset($wi[$x]))$wi[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}elseif(array_key_exists($x,$S))json_row("$x-$B","?");}}}foreach($wi
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases())as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{$Gi=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Gi&&!$l&&!$_POST["search"]){$I=true;$lf="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$lf='Tables have been truncated.';}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been moved.';}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$lf='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$K)$lf
.="<b>".h($R)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$I=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?"":" ANALYZE"),$_POST["tables"]));$lf='Tables have been optimized.';}elseif(!$_POST["tables"])$lf='No tables.';elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($K=$I->fetch_assoc())$lf
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect(substr(ME,0,-1),$lf,$I);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){echo"<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Fi=tables_list();if(!$Fi)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);",""),'<th>'.'Table','<td>'.'Engine'.doc_link(array('sql'=>'storage-engines.html')),'<td>'.'Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')),'<td>'.'Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),'<td>'.'Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),'<td>'.'Data Free'.doc_link(array('sql'=>'show-table-status.html')),'<td>'.'Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),'<td>'.'Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),(support("comment")?'<td>'.'Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')):''),"</thead>\n";$T=0;foreach($Fi
as$B=>$U){$Mj=($U!==null&&!preg_match('~table|sequence~i',$U));$t=h("Table-".$B);echo'<tr><td>'.checkbox(($Mj?"views[]":"tables[]"),$B,in_array("$B",$Gi,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".urlencode($B)."' title='".'Show structure'."' id='$t'>".h($B).'</a>':h($B));if($Mj&&!preg_match('~materialized~i',$U)){$Si='View';echo'<td colspan="6">'.(support("view")?"<a href='".h(ME)."view=".urlencode($B)."' title='".'Alter view'."'>$Si</a>":$Si),'<td align="right"><a href="'.h(ME)."select=".urlencode($B).'" title="'.'Select data'.'">?</a>';}else{foreach(array("Engine"=>array(),"Collation"=>array(),"Data_length"=>array("create",'Alter table'),"Index_length"=>array("indexes",'Alter indexes'),"Data_free"=>array("edit",'New item'),"Auto_increment"=>array("auto_increment=1&create",'Alter table'),"Rows"=>array("select",'Select data'),)as$x=>$_){$t=" id='$x-".h($B)."'";echo($_?"<td align='right'>".(support("table")||$x=="Rows"||(support("indexes")&&$x!="Data_length")?"<a href='".h(ME."$_[0]=").urlencode($B)."'$t title='$_[1]'>?</a>":"<span$t>?</span>"):"<td id='$x-".h($B)."'>");}$T++;}echo(support("comment")?"<td id='Comment-".h($B)."'>":""),"\n";}echo"<tr><td><th>".sprintf('%d in total',count($Fi)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),"<td>".h(db_collation(DB,collations()));foreach(array("Data_length","Index_length","Data_free")as$x)echo"<td align='right' id='sum-$x'>";echo"\n","</table>\n",script("ajaxSetHtml('".js_escape(ME)."script=db');"),"</div>\n";if(!information_schema(DB)){echo"<div class='footer'><div>\n";$Gj="<input type='submit' value='".'Vacuum'."'> ".on_help("'VACUUM'");$Zf="<input type='submit' name='optimize' value='".'Optimize'."'> ".on_help(JUSH=="sql"?"'OPTIMIZE TABLE'":"'VACUUM OPTIMIZE'");echo"<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>".(JUSH=="sqlite"?$Gj."<input type='submit' name='check' value='".'Check'."'> ".on_help("'PRAGMA integrity_check'"):(JUSH=="pgsql"?$Gj.$Zf:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'> ".on_help("'ANALYZE TABLE'").$Zf."<input type='submit' name='check' value='".'Check'."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".'Repair'."'> ".on_help("'REPAIR TABLE'"):"")))."<input type='submit' name='truncate' value='".'Truncate'."'> ".on_help(JUSH=="sqlite"?"'DELETE'":"'TRUNCATE".(JUSH=="pgsql"?"'":" TABLE'")).confirm()."<input type='submit' name='drop' value='".'Drop'."'>".on_help("'DROP TABLE'").confirm()."\n";$i=(support("scheme")?adminer()->schemas():adminer()->databases());echo"</div></fieldset>\n";$Jh="";if(count($i)!=1&&JUSH!="sqlite"){echo"<fieldset><legend>".'Move to other database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";$Jh=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo"<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $T);":"")."$Jh }"),input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo"<p class='links'><a href='".h(ME)."create='>".'Create table'."</a>\n",(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":"");if(support("routine")){echo"<h3 id='routines'>".'Routines'."</h3>\n";$Bh=routines();if($Bh){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td></thead>\n";foreach($Bh
as$K){$B=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.h($K["ROUTINE_NAME"]).'</a>','<td>'.h($K["ROUTINE_TYPE"]),'<td>'.h($K["DTD_IDENTIFIER"]),'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n";}if(support("sequence")){echo"<h3 id='sequences'>".'Sequences'."</h3>\n";$Uh=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Uh){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Uh
as$X)echo"<tr><th><a href='".h(ME)."sequence=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n";}if(support("type")){echo"<h3 id='user-types'>".'User types'."</h3>\n";$Ej=types();if($Ej){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Ej
as$X)echo"<tr><th><a href='".h(ME)."type=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."type='>".'Create type'."</a>\n";}if(support("event")){echo"<h3 id='events'>".'Events'."</h3>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?'At given time'."<td>".$K["Execute at"]:'Every'." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$Gc=get_val("SELECT @@event_scheduler");if($Gc&&$Gc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Gc)."\n";}echo'<p class="links"><a href="'.h(ME).'event=">'.'Create event'."</a>\n";}}}}page_footer();