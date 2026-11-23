<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.3.1
*/namespace
Adminer;if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];const
VERSION="6.3.1";error_reporting(24575);set_error_handler(function($cd,$fd){return!!preg_match('~^Undefined (array key|offset|index)~',$fd);},E_WARNING|E_NOTICE);if(!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"))||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$X){$Vl=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Vl)$$X=$Vl;}}$_COOKIE=array_filter($_COOKIE,'is_scalar');if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($Bb=null){return($Bb?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Pb=adminer()->credentials();$I=Driver::connect($Pb[0],$Pb[1],$Pb[2]);return(is_object($I)?$I:null);}function
idf_unescape($s){if(!preg_match('~^[`\'"[]~',$s))return$s;$Gf=substr($s,-1);return
str_replace($Gf.$Gf,$Gf,substr($s,1,-1));}function
q($Q){return
connection()->quote($Q);}function
idx($za,$v,$h=null){return($za&&array_key_exists($v,$za)?$za[$v]:$h);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|number|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
text_type(){return'char|text'.(JUSH=="sql"?'|enum|set':'');}function
is_user_type($U){return
in_array($U,idx(driver()->structuredTypes(),'User types',[]));}function
full_type_sql(array$j){$U=$j["type"];return(is_user_type($U)?idf_escape($U).substr($j["full_type"],strlen($U)):$j["full_type"]);}function
is_searchable(array$j,array$X){if(!isset($j["privileges"]["where"]))return
false;if(preg_match('~NULL$~',$X["op"]))return
true;$U=$j["type"];$Kj=$X["val"];$Pa='binary$|bytea|raw|image|bfile|^vector$'.(JUSH=="mssql"?'|^timestamp$':'|^bit').(JUSH=="oracle"?'|^blob|^long|rowid':'');if(preg_match("~$Pa~",$U))return
false;if(preg_match(number_type(),$U)){$hh='-?\d+(\.\d+)?';$Jg='';if(preg_match('~IN$~',$X["op"])){$Kj=preg_replace('~^\s*\(\s*(.*?)\s*\)\s*$~s','\1',$Kj);$Jg="( *, *$hh)*";}elseif($X["op"]=="BETWEEN")$Jg="\\s+AND\\s+$hh";return(bool)preg_match('~^'.$hh.$Jg.'$~i',$Kj);}if(preg_match('~^(small)?date|^timestamp~',$U))return(bool)preg_match('~^\d+-\d+-\d+~',$Kj);if(preg_match('~^time~',$U))return(bool)preg_match('~^\d+:\d+~',$Kj);if(preg_match('~^bool~',$U)||(JUSH=="mssql"&&$U=="bit"))return(bool)preg_match('~^(t|f|true|false|[01])$~i',$Kj);return
true;}function
bracket_escape($s,$Ia=false){static$Dl=[':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5'];return
strtr($s,($Ia?array_flip($Dl):$Dl));}function
url_escape($Q){static$Dl=[];if(!$Dl){$Dl=[' '=>'+'];foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$cb)$Dl[$cb]=sprintf('%%%02X',ord($cb));for($q=0;$q<256;$q++){if($q<32||$q>126)$Dl[chr($q)]=sprintf('%%%02X',$q);}}return
strtr((string)$Q,$Dl);}function
min_version($vm,$bg="",$Bb=null){$Bb=connection($Bb);$bk=$Bb->server_info;if($bg&&preg_match('~([\d.]+)-MariaDB~',$bk,$_)){$bk=$_[1];$vm=$bg;}return$vm&&version_compare($bk,$vm)>=0;}function
charset(Db$e){return(min_version("5.5.3",0,$e)?"utf8mb4":"utf8");}function
ini_set($Ch,$Y){return(function_exists('ini_set')?\ini_set($Ch,$Y):false);}function
ini_bool($af){$X=ini_get($af);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($af){$X=ini_get($af);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
max_input_vars($J,$Qh){$eg=(int)ini_get("max_input_vars");return($eg?(int)floor(($eg-$Qh)/$J):0);}function
max_input_vars_error(){$af="max_input_vars";return
sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"<b>$af = ".ini_get($af)."</b>");}function
sid(){static$I;if($I===null)$I=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$I;}function
set_password($um,$N,$V,$E){if(is_string($E)&&!$_COOKIE["adminer_key"]){$Yh=session_get_cookie_params();$_COOKIE["adminer_key"]=rand_string();cookie("adminer_key",$_COOKIE["adminer_key"],$Yh["lifetime"]);}$_SESSION["pwds"][$um][$N][$V]=(is_string($E)?[encrypt_string($E,$_COOKIE["adminer_key"])]:$E);}function
get_password(){$I=get_session("pwds");if(is_array($I))$I=($_COOKIE["adminer_key"]?decrypt_string($I[0],$_COOKIE["adminer_key"]):false);return$I;}function
get_val($G,$j=0,$Ab=null){$Ab=connection($Ab);$H=$Ab->query($G);if(!is_object($H))return
false;$J=$H->fetch_row();return($J?$J[$j]:false);}function
get_vals($G,$c=0){$I=[];$H=connection()->query($G);if(is_object($H)){while($J=$H->fetch_row())$I[]=$J[$c];}return$I;}function
get_key_vals($G,$Bb=null,$fk=true){$Bb=connection($Bb);$I=[];$H=$Bb->query($G);if(is_object($H)){while($J=$H->fetch_row()){if($fk)$I[$J[0]]=$J[1];else$I[]=$J[0];}}return$I;}function
get_rows($G,$Bb=null,$i="<p class='error'>"){$Ab=connection($Bb);$I=[];$H=$Ab->query($G);if(is_object($H)){while($J=$H->fetch_assoc())$I[]=$J;}elseif(!$H&&!$Bb&&$i&&(defined('Adminer\PAGE_HEADER')||$i=="-- "))echo$i.adminer()->error()."\n";return$I;}function
unique_array($J,array$u){foreach($u
as$t){if(preg_match("~^(PRIMARY|UNIQUE)$~",$t["type"])&&!$t["partial"]&&!$t["invalid"]){$I=[];foreach($t["columns"]as$v){if(!isset($J[$v]))continue
2;$I[$v]=$J[$v];}return$I;}}}function
where_function($ae,$c,array$j){if($ae=="md5")return
driver()->md5($c,$j)?:$c;return(in_array($ae,driver()->functions)||in_array($ae,driver()->grouping)?apply_sql_function($ae,$c):$c);}function
where(array$Z,array$k=[]){$I=[];foreach((array)$Z["where"]as$v=>$X){$v=bracket_escape($v,true);$c=idf_escape($v);$j=idx($k,$v,[]);$Cd=$j["type"];$nf=$j&&(is_blob($j)||preg_match('~binary~',$Cd));$I[]=$c.($nf&&!is_utf8($X)?" = ".driver()->quoteBinary($X):(JUSH=="sql"&&$Cd=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$j["full_type"])?"::jsonb = ".q($X)."::jsonb":(JUSH=="pgsql"&&preg_match('~\.~',$Cd)?"::text = ".q($X):(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Cd,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($j,q($X))))))));if(JUSH=="sql"&&preg_match('~char|text~',$Cd)&&preg_match("~[^ -@]~",$X))$I[]="$c = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$v)$I[]=idf_escape($v)." IS NULL";foreach((array)$Z["col"]as$q=>$pb){$X=idx($Z["val"],$q);$I[]=where_function(idx($Z["fun"],$q),idf_escape($pb),idx($k,$pb,[])).($X!==null?" = ".q($X):" IS NULL");}return
implode(" AND ",$I);}function
where_columns(array$k){$I=[];foreach((array)$_GET["null"]as$v)$I[$v]=true;foreach(array_keys((array)$_GET["where"])as$v)$I[bracket_escape($v,true)]=true;foreach((array)$_GET["col"]as$pb)$I[$pb]=true;return
array_intersect_key($I,$k);}function
where_check($X,array$k=[]){parse_str($X,$fb);return
where($fb,$k);}function
where_link($q,$c,$Y,$_h="="){$xh=($Y!==null?$_h:"IS NULL");return"&where[$q][col]=".url_escape($c).($xh!=first(adminer()->operators())?"&where[$q][op]=".url_escape($xh):"")."&where[$q][val]=".url_escape($Y);}function
convert_fields(array$d,array$k,array$M=[]){$I="";foreach($d
as$v=>$X){if($M&&!in_array(idf_escape($v),$M))continue;$_a=convert_field($k[$v]);if($_a)$I
.=", $_a AS ".idf_escape($v);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($A,$Y,$Qf=2592000){header("Set-Cookie: $A=".rawurlencode($Y).($Qf?"; expires=".gmdate("D, d M Y H:i:s",time()+$Qf)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($A=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
php_error_text($i){return(ini_bool("html_errors")?html_entity_decode(strip_tags($i)):$i);}function
php_errors($Ya){$ed=[];set_error_handler(function($cd,$i)use(&$ed){$ed[]=preg_replace('~^\w+\([^)]*\)( \[[^]]*])?:\s*~','',php_error_text($i));return
true;},E_WARNING|E_NOTICE);$Ya();restore_error_handler();return$ed;}function
get_url($em,$Gb){$ed=php_errors(function()use($em,$Gb,&$I,&$we){$http_response_header=null;$I=file_get_contents($em,false,$Gb);$we=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);});return[$I,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($we,0,''),$_)?$_[1]:''),(array)$we,($I===false?implode("\n",$ed):''),];}function
json_decode_exact($vf){$vf=str_replace(['\\\\','\\"'],['\\u005c','\\u0022'],$vf);$vf=preg_replace('~"(\\\\u0001[^"]*+")|"[^"]*+"(*SKIP)(*FAIL)~','"\\\\u0001$1',$vf);return
json_decode(preg_replace('~"[^"]*+"(*SKIP)(*FAIL)|-?\d[-+.\deE]*+~','"\\\\u0001$0"',$vf));}function
json_scalar($X){return(is_string($X)&&substr($X,0,1)=="\1"?substr($X,1):$X);}function
json_encode_exact($X,$Ld=0){$vf=str_replace(['\\\\','\\"'],["\2","\3"],json_encode($X,$Ld));$vf=preg_replace('~"\\\\u0001(-?\d[^"\\\\]*)"|(")\\\\u0001(\\\\u0001[^"]*+")|"[^"]*+"(*SKIP)(*FAIL)~','$1$2$3',$vf);return
str_replace(["\2","\3"],['\\\\','\\"'],$vf);}function
get_settings($Jb){parse_str($_COOKIE[$Jb],$gk);return$gk;}function
get_setting($v,$Jb="adminer_settings",$h=null){return
idx(get_settings($Jb),$v,$h);}function
save_settings(array$gk,$Jb="adminer_settings"){$Y=http_build_query($gk+get_settings($Jb));cookie($Jb,$Y);$_COOKIE[$Jb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Od=false){$hm=ini_bool("session.use_cookies");if(!$hm||$Od){session_write_close();if($hm&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($v){return$_SESSION[$v][DRIVER][SERVER][$_GET["username"]];}function
set_session($v,$X){$_SESSION[$v][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($um,$N,$V,$g=null){$dm=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($g!==null?"db|":"").($um=='mssql'||$um=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$dm,$_);return"$_[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($um!="server"||$N!=""?url_escape($um)."=".url_escape($N)."&":"")."username=".url_escape($V).($g!=""?"&db=".url_escape($g):"").($_[2]?"&$_[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($z,$ug=null,$P=303){if($ug!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($z!==null?$z:$_SERVER["REQUEST_URI"]))][]=$ug;}if($z!==null){if($z=="")$z=".";header("Location: $z",true,$P);exit;}}function
query_redirect($G,$z,$ug,$bj=true,$md=true,$xd=false,$rl=""){if($md){$Bk=microtime(true);$xd=!connection()->query($G);$rl=format_time($Bk);}$tk=($G?adminer()->messageQuery($G,$rl,$xd):"");if($xd){adminer()->error
.=adminer()->error().$tk.script("messagesPrint();")."<br>";return
false;}if($bj)redirect($z,$ug.$tk);return
true;}class
Queries{static$queries=[];static$start=0;}function
remember_query($G){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$G:(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";");}function
queries($G){remember_query($G);return
connection()->query($G);}function
apply_queries($G,array$T,$gd='Adminer\table'){foreach($T
as$R){if(!queries("$G ".$gd($R)))return
false;}return
true;}function
queries_redirect($z,$ug,$bj){$Ti=implode("\n",Queries::$queries);$rl=format_time(Queries::$start);return
query_redirect($Ti,$z,$ug,$bj,false,!$bj,$rl);}function
format_time($Bk){return
sprintf('%.3f s',max(0,microtime(true)-$Bk));}function
relative_uri($dm=''){return
preg_replace_callback('~^[^?]*~',function($_){return
str_replace(":","%3A",$_[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',($dm?:$_SERVER["REQUEST_URI"])));}function
remove_from_uri($Wh=""){return
substr(preg_replace("~(?<=[?&])($Wh".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($A,$ec=false){$Ed=$_FILES[$A];if(!$Ed)return
null;foreach($Ed
as$v=>$X)$Ed[$v]=(array)$X;$I=[];foreach($Ed["error"]as$v=>$i){if($i)return$i;$l=$Ed["name"][$v];$zl=$Ed["tmp_name"][$v];$Eb=file_get_contents($ec&&preg_match('~\.gz$~',$l)?"compress.zlib://$zl":$zl);if($ec){$Bk=substr($Eb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Bk))$Eb=iconv("utf-16","utf-8",$Eb);elseif($Bk=="\xEF\xBB\xBF")$Eb=substr($Eb,3);}$I[]=[$l,$Eb];}return$I;}function
get_file($v,$ec=false,$mc=""){$Hd=get_files($v,$ec);if(!is_array($Hd))return$Hd;$I='';foreach($Hd
as$Ed){$Eb=$Ed[1];$I
.=$Eb;if($mc)$I
.=(preg_match("($mc\\s*\$)",$Eb)?"":$mc)."\n\n";}return$I;}function
upload_error($i){$mg=($i==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($i?'Unable to upload a file.'.($mg?" ".sprintf('Maximum allowed file size is %sB.',$mg):""):'File does not exist.');}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
utf8_length($X){return
strlen(preg_replace('~[\x80-\xBF]~','',$X));}function
format_number($X){preg_match('~^#+([^#0]+)(?:(#+)\1)?(#*0)$~u','#,##0',$_);$kk=strlen($_[3]);$I=number_format($X,0,".","");$I=preg_replace('~\B(?=(\d{'.(strlen($_[2])?:$kk).'})*\d{'.$kk.'}$)~',$_[1],$I);return
strtr($I,preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$S,$v){$X=idx($S,$v,'?');if(!is_numeric($X))return
h($X);if($X<0)return'?';$wa=($v=="Rows"&&(JUSH=="sqlite"||$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));$X=in_array($v,["Data_length","Index_length","Data_free"])?adminer()->formatSizeValue($X):format_number($X);return($wa?"~ ":"").$X;}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$yd=false){$I=table_status($R,$yd);return($I?reset($I):["Name"=>$R]);}function
column_foreign_keys($R){$I=[];foreach(adminer()->foreignKeys($R)as$m){foreach($m["source"]as$X)$I[$X][]=$m;}return$I;}function
fields_from_edit(){$I=[];foreach((array)$_POST["field_keys"]as$v=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$v];$_POST["fields"][$X]=$_POST["field_vals"][$v];}}foreach((array)$_POST["fields"]as$v=>$X){$A=bracket_escape($v,true);$I[$A]=["field"=>$A,"full_type"=>"","type"=>"","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($A==driver()->primary),];}return$I;}function
dump_headers($Je,$Mg=false){$I=adminer()->dumpHeaders($Je,$Mg);$Sh=$_POST["output"];if($Sh!="text"||$I=="tar"){$zb=($Sh!="text"&&$Sh!="file"&&preg_match('~^[0-9a-z]+$~',$Sh)?".$Sh":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Je).".$I$zb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$I;}function
dump_csv(array$J){$Ll=$_POST["format"]=="tsv";foreach($J
as$v=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Ll?'\t':'[,;]|^$').'~',$X))$J[$v]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($Ll?"\t":";")),$J)."\r\n";}function
parse_csv($Sb,$Vj){$I=[];preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Sb,$cg);foreach($cg[0]as$J){preg_match_all("~((?>\"[^\"]*\")+|[^$Vj]*)$Vj~",$J.$Vj,$dg);$I[]=$dg[1];}return$I;}function
csv_value($X){return(preg_match('~^".*"$~s',$X)?idf_unescape($X):$X);}function
apply_sql_function($o,$c){return($o?($o=="unixepoch"?"DATETIME($c, '$o')":($o=="count distinct"?"COUNT(DISTINCT ":strtoupper("$o("))."$c)"):$c);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($l){if(is_link($l))return;$n=@fopen($l,"c+");if(!$n)return;@chmod($l,0660);if(!flock($n,LOCK_EX)){fclose($n);return;}return$n;}function
file_write_unlock($n,$Wb){rewind($n);fwrite($n,$Wb);ftruncate($n,strlen($Wb));file_unlock($n);}function
file_unlock($n){flock($n,LOCK_UN);fclose($n);}function
first(array$za){return
reset($za);}function
password_file($Mb){$l=get_temp_dir()."/adminer.key";if(!$Mb&&!file_exists($l))return'';$n=file_open_lock($l);if(!$n)return'';$I=stream_get_contents($n);if(!$I){$I=rand_string();file_write_unlock($n,$I);}else
file_unlock($n);return$I;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($X,$y,array$j,$pl,array$qi=[]){if(is_array($X)){$I="";if(array_filter($X,'is_array')==array_values($X)){$_f=[];foreach($X
as$W)$_f+=array_fill_keys(array_keys($W),null);foreach(array_keys($_f)as$yf)$I
.="<th>".h($yf);foreach($X
as$W){$I
.="<tr>";foreach(array_merge($_f,$W)as$om)$I
.="<td>".select_value($om,$y,$j,$pl,$qi);}}else{foreach($X
as$yf=>$W)$I
.="<tr>".($X!=array_values($X)?"<th>".h($yf):"")."<td>".select_value($W,$y,$j,$pl,$qi);}return"<table>$I</table>";}if(!$y)$y=adminer()->selectLink($X,$j);if($y===null){if(is_mail($X))$y="mailto:$X";if(is_url($X))$y=$X;}$X=driver()->value($X,$j);$I=adminer()->editVal($X,$j);if($I!==null){if(!is_utf8($I))$I="\0";elseif($pl!=""&&is_shortable($j))$I=shorten_utf8($I,max(0,+$pl),"",$qi);else$I=highlight_matches($I,$qi);}return
adminer()->selectVal($I,$y,$j,$X);}function
is_blob(array$j){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$j["type"])&&!in_array($j["type"],idx(driver()->structuredTypes(),'User types',[]));}function
is_identity_always(array$j){return$j["auto_increment"]&&(JUSH=="mssql"||$j["default"]=="GENERATED ALWAYS AS IDENTITY");}function
is_mail($Rc){$Ba='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$Dc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$pi="$Ba+(\\.$Ba+)*@($Dc?\\.)+$Dc";return
is_string($Rc)&&preg_match("(^$pi(,\\s*$pi)*\$)i",$Rc);}function
is_url($Q){$Dc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($Dc?\\.)+$Dc(:\\d+)?(/\\S*)?(\\?\\S*)?(#\\S*)?\$~iD",$Q);}function
is_ipv6($ia){$p='[\da-f]{1,4}';$mf='\d{1,3}(\.\d{1,3}){3}';return(bool)preg_match("~^(($p:){7}$p|($p:){6}$mf|(($p:)*$p)?::(($p:)*($p|$mf))?)$~iD",$ia);}function
is_shortable(array$j){return!preg_match('~'.number_type().'|date|time|year~',$j["type"]);}function
url_host($Fe){return(strpos($Fe,":")!==false?"[$Fe]":$Fe);}function
server_parts(array$ji){return["scheme"=>(string)$ji["scheme"],"host"=>(string)$ji["host"],"port"=>(string)$ji["port"],"socket"=>(string)$ji["socket"],"path"=>(string)$ji["path"],];}function
parse_server($N){if($N=="")return
server_parts([]);if($N[0]==":"&&!is_ipv6($N)){$oj=substr($N,1);if(preg_match('~^\d+$~D',$oj))return
server_parts(["port"=>$oj]);return(preg_match('~^/[-\w.:/]*$~D',$oj)?server_parts(["socket"=>$oj]):null);}$Ij="";if(preg_match('~^([-+.\w]+)://~',$N,$_)){$Ij=strtolower($_[1]);$N=substr($N,strlen($_[0]));}if(preg_match('~^\[(.+)](:(\d+))?(/[-\w./]*)?$~D',$N,$_))return(is_ipv6($_[1])?server_parts(["scheme"=>$Ij,"host"=>$_[1],"port"=>$_[3],"path"=>$_[4]]):null);if(is_ipv6($N))return
server_parts(["scheme"=>$Ij,"host"=>$N]);if(preg_match('~^(/[-\w./]*)(:(\d+))?$~D',$N,$_))return
server_parts(["scheme"=>$Ij,"host"=>$_[1],"port"=>$_[3]]);return(preg_match('~^([-\w.]*)(:(\d+))?(/[-\w./]*)?$~D',$N,$_)?server_parts(["scheme"=>$Ij,"host"=>$_[1],"port"=>$_[3],"path"=>$_[4]]):null);}function
count_rows($R,array$Z,$of,array$p){$G=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($of&&(JUSH=="sql"||count($p)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$p).")$G":"SELECT COUNT(*)".($of?" FROM (SELECT 1$G GROUP BY ".implode(", ",$p).") x":$G));}function
slow_query($G){$lk=driver()->slowQuery($G,adminer()->queryTimeout());ob_flush();flush();return@get_key_vals(($lk?:$G),null,false);}function
get_token(){$Yi=rand_string();return
bin2hex($Yi^"$_SESSION[token]").":$Yi";}function
verify_token(){list($_l,$Yi)=explode(":",$_POST["token"]);$Mj=$_SESSION["token"];return
is_string($Mj)&&strlen($Yi)==strlen($Mj)&&bin2hex($Yi^$Mj)===$_l&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Q,$sc=""){$sa=array_flip(str_split(compress_alphabet()));$w=strlen($Q);$qm=($w?13*($w-1)/2-$sa[$Q[0]]:0);$Pa="";$oj=0;$pj=0;for($q=1;$q<$w;$q+=2){$oj=($oj<<13)+$sa[$Q[$q]]*93+$sa[$Q[$q+1]];$pj+=13;while($pj>=8&&$qm>=8){$pj-=8;$qm-=8;$Pa
.=chr($oj>>$pj);$oj&=(1<<$pj)-1;}}if($Pa=="")return"";if($sc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,['dictionary'=>$sc]),$Pa,ZLIB_FINISH);return($sc==""&&function_exists('gzinflate')?gzinflate($Pa):inflate($Pa,$sc));}function
inflate($Pa,$sc=""){$Nf=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$Of=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$wc=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$yc=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$I=$sc;$F=0;do{$Jd=inflate_bits($Pa,$F,1);$U=inflate_bits($Pa,$F,2);if(!$U){$F=($F+7)&~7;$w=inflate_bits($Pa,$F,16);$F+=16;$I
.=substr($Pa,$F>>3,$w);$F+=$w<<3;}else{if($U==1){$Wf=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$zc=array_fill(0,30,5);}else{$Vf=inflate_bits($Pa,$F,5)+257;$xc=inflate_bits($Pa,$F,5)+1;$C=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Ag=array_fill(0,19,0);$_g=inflate_bits($Pa,$F,4)+4;for($q=0;$q<$_g;$q++)$Ag[$C[$q]]=inflate_bits($Pa,$F,3);$Bg=inflate_table($Ag);$Pf=[];while(count($Pf)<$Vf+$xc){$Qk=inflate_symbol($Pa,$F,$Bg);if($Qk==16)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Pa,$F,2)+3,end($Pf)));elseif($Qk==17)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Pa,$F,3)+3,0));elseif($Qk==18)$Pf=array_merge($Pf,array_fill(0,inflate_bits($Pa,$F,7)+11,0));else$Pf[]=$Qk;}$Wf=array_slice($Pf,0,$Vf);$zc=array_slice($Pf,$Vf);}$Xf=inflate_table($Wf);$Ac=inflate_table($zc);while(($Qk=inflate_symbol($Pa,$F,$Xf))!=256){if($Qk<256)$I
.=chr($Qk);else{$w=$Nf[$Qk-257]+inflate_bits($Pa,$F,$Of[$Qk-257]);$_c=inflate_symbol($Pa,$F,$Ac);$oh=strlen($I)-$wc[$_c]-inflate_bits($Pa,$F,$yc[$_c]);for($q=0;$q<$w;$q++)$I
.=$I[$oh+$q];}}}}while(!$Jd);return($sc==""?$I:substr($I,strlen($sc)));}function
inflate_bits($Pa,&$F,$Lb){$I=0;for($q=0;$q<$Lb;$q++){$I+=((ord($Pa[$F>>3])>>($F&7))&1)<<$q;$F++;}return$I;}function
inflate_table(array$Pf){$R=[];$ob=0;for($Qa=1;$Qa<=max($Pf);$Qa++){foreach($Pf
as$Qk=>$w){if($w==$Qa){$R[$Qa][$ob]=$Qk;$ob++;}}$ob<<=1;}return$R;}function
inflate_symbol($Pa,&$F,array$R){$ob=0;$Qa=0;do{$ob=($ob<<1)+inflate_bits($Pa,$F,1);$Qa++;}while(!isset($R[$Qa][$ob]));return$R[$Qa][$ob];}function
script($qk,$Cl="\n"){return"<script".nonce().">$qk</script>$Cl";}function
script_src($em,$ic=false){return"<script src='".h($em)."'".nonce().($ic?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($hd,$oe,$xa=null){$ya=[];foreach(array_slice(func_get_args(),2)as$X)$ya[]=json_encode($X,JSON_UNESCAPED_UNICODE);return" data-on$hd='".str_replace(['&','<',"'"],['&amp;','&lt;','&#039;'],"$oe(".implode(", ",$ya).")")."'";}function
input_hidden($A,$Y=""){return"<input type='hidden' name='".h($A)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace(['&','<','"',"'","\0"],['&amp;','&lt;','&quot;','&#039;','&#0;'],$Q);}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($A,$Y,$hb,$Bf="",$b="",$nb="",$Df=""){$I="<input type='checkbox' name='".h($A)."' value='".h($Y)."'".($hb?" checked":"").($Bf==""&&$nb?" class='$nb'":"").($Df?" aria-labelledby='$Df'":"").$b.">";return($Bf!=""?"<label".($nb?" class='$nb'":"").">$I".h($Bf)."</label>":$I);}function
optionlist($B,$Sj=null,$im=false){$I="";foreach($B
as$yf=>$W){$Fh=[$yf=>$W];if(is_array($W)){$I
.='<optgroup label="'.h($yf).'">';$Fh=$W;}foreach($Fh
as$v=>$X)$I
.='<option'.($im||is_string($v)?' value="'.h($v).'"':'').($Sj!==null&&($im||is_string($v)?(string)$v:$X)===$Sj?' selected':'').'>'.h($X);if(is_array($W))$I
.='</optgroup>';}return$I;}function
group_system(array$Ug,$Hj=false){$I=[];$Rk=[];foreach($Ug
as$A){if($Hj?driver()->isSystem(DB,$A):driver()->isSystem($A))$Rk[]=$A;else$I[]=$A;}if($Rk)$I[sprintf('System%s','')]=$Rk;return$I;}function
html_select($A,array$B,$Y="",$b="",$Df=""){static$Bf=0;$Cf="";if(!$Df&&substr($B[""],0,1)=="("){$Bf++;$Df="label-$Bf";$Cf="<option value='' id='$Df'>".h($B[""]);unset($B[""]);}return"<select name='".h($A)."'".($Df?" aria-labelledby='$Df'":"")."$b>".$Cf.optionlist($B,$Y)."</select>";}function
html_radios($A,array$B,$Y="",$Vj=""){$I="";foreach($B
as$v=>$X)$I
.="<label><input type='radio' name='".h($A)."' value='".h($v)."'".($v==$Y?" checked":"").">".h($X)."</label>$Vj";return$I;}function
confirm($ug=""){return
on('click','confirmClick',$ug?:'Are you sure?');}function
print_fieldset($r,$Mf,$ym=false){echo"<fieldset><legend>","<a href='#fieldset-$r' class='toggle'>$Mf</a>","</legend>","<div id='fieldset-$r'".($ym?"":" class='hidden'").">\n";}function
bold($Ra,$nb=""){return($Ra?" class='active $nb'":($nb?" class='$nb'":""));}function
js_escape($Q){return
str_replace("<","\\x3C",addcslashes($Q,"\r\n'\\"));}function
js_escape_re($Q){return
addcslashes(preg_quote($Q,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$Tb){return" ".($D==$Tb?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$Oi,array$Ne=[],$Fi=''){$I=false;foreach($Oi
as$v=>$X){if(!in_array($v,$Ne)){if(is_array($X))hidden_fields($X,[],$v);else{$I=true;echo
input_hidden(($Fi?$Fi."[$v]":$v),$X);}}}return$I;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
on_upload_progress(&$cm){$cm=(ini_bool("session.upload_progress.enabled")&&ini_get("session.upload_progress.name")?rand_string():"");return($cm?on('submit','uploadProgress',ME."upload=$cm",SESSION_NAME."=$cm"):"");}function
file_input($b,$oj=""){$gg="max_file_uploads";$hg=ini_get($gg);$mg="upload_max_filesize";$ng=ini_bytes($mg);$Bi=ini_bytes("post_max_size");if($Bi&&$Bi<$ng){$mg="post_max_size";$ng=$Bi;}$og=ini_get($mg);return(ini_bool("file_uploads")?"<input type='file'$b".on('change','fileChange',(int)$hg,sprintf('Increase %s.',"$gg = $hg"),$ng,sprintf('Increase %s.',"$mg = $og")).">$oj":'File uploads are disabled.');}function
enum_input($U,$b,array$j,$Y,$Uc=""){preg_match_all("~".driver()->enumLength."~",$j["length"],$cg);$Fi=($j["type"]=="enum"?"val-":"");$hb=(is_array($Y)?in_array("null",$Y):$Y===null);$I=($j["null"]&&$Fi?"<label><input type='$U'$b value='null'".($hb?" checked":"")."><i>$Uc</i></label>":"");foreach($cg[0]as$X){$X=stripcslashes(idf_unescape($X));$hb=(is_array($Y)?in_array($Fi.$X,$Y):$Y===$X);$I
.=" <label><input type='$U'$b value='".h($Fi.$X)."'".($hb?' checked':'').'>'.h(adminer()->editVal($X,$j)).'</label>';}return$I;}function
input(array$j,$Y,$o,$Ga=false,$Zl=false){$A=h(bracket_escape($j["field"]));echo"<td class='function'>";$bd=driver()->enumLength($j);if($bd){$j["type"]="enum";$j["length"]=$bd;}$B=($j["type"]=="enum"||$j["type"]=="set");if(is_array($Y)&&!$o&&!$B)$o="json";$vf=($o=="json"||preg_match('~^jsonb?$~',$j["full_type"]));if($vf&&$Y!=''&&(JUSH!="pgsql"||$j["type"]!="json")&&(is_array($Y)||!$_POST["save"])){$Ld=JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;$Y=(is_array($Y)?json_encode($Y,$Ld):json_encode_exact(json_decode_exact($Y),$Ld));}$nj=($Zl&&is_identity_always($j));if($nj&&!$_POST["save"])$o=null;$be=(isset($_GET["select"])||$nj?["orig"=>'original']:[])+adminer()->editFunctions($j);$b=" name='fields[$A]".($B?"[]":"")."'".($Ga?" autofocus":"");echo
driver()->unconvertFunction($j)." ";$R=$_GET["edit"]?:$_GET["select"];if($j["type"]=="enum")echo
h($be[""])."<td>".adminer()->editInput($R,$j,$b,$Y);else{$qe=(in_array($o,$be)||isset($be[$o]));$Kd=0;foreach($be
as$v=>$X){if($v===""||!$X)break;$Kd++;}echo(count($be)>1?"<select name='function[$A]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($be,$o===null||$qe?$o:"")."</select>":h(reset($be)))."<td".($Kd&&count($be)>1?on('input','skipOriginal',$Kd):"").">";$cf=adminer()->editInput($R,$j,$b,$Y);if($cf!="")echo$cf;elseif(preg_match('~bool~',$j["type"]))echo"<input type='hidden'$b value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked":"")."$b value='1'>";elseif($j["type"]=="set")echo
enum_input("checkbox",$b,$j,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($j)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($vf)echo"<textarea$b cols='50' rows='12' class='jush-json'>\n".h($Y).'</textarea>';elseif(($ol=preg_match('~text|lob|memo~i',$j["type"]))||preg_match("~\n~",$Y)){if($ol&&JUSH!="sqlite")$b
.=" cols='50' rows='12'";else{$K=min(12,substr_count($Y,"\n")+1);$b
.=" cols='30' rows='$K'";}echo"<textarea$b>\n".h($Y).'</textarea>';}else{$Pl=driver()->types();$Nl=$Pl[$j["type"]];$za=preg_match('~\[]~',$j["full_type"]);if($za)$pg=0;elseif(preg_match('~date|time|year~',$j["type"])){$Ei=($j["length"]==""&&JUSH=="pgsql"?6:$j["length"]);$Vd=(preg_match('~time~',$j["type"])&&preg_match('~^[1-9]\d*$~',$Ei)?$Ei+1:0);$pg=($Nl?$Nl+$Vd:0);}elseif(!preg_match('~int|vector~',$j["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$j["length"],$_))$pg=(preg_match("~binary~",$j["type"])?2:1)*$_[1]+($_[3]?1:0)+($_[2]&&!$j["unsigned"]?1:0);else$pg=($Nl?$Nl+($j["unsigned"]?0:1):0);echo"<input".((!$qe||$o==="")&&preg_match('~^'.int_type().'$~',$j["type"])&&!$za?" type='number'":"")." value='".h($Y)."'".($pg?" data-maxlength='$pg'":"").(preg_match('~char|binary~',$j["type"])&&$pg>20?" size='".($pg>99?60:40)."'":"")."$b>";}echo
adminer()->editHint($R,$j,$Y),(count($be)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$j){$s=bracket_escape($j["field"]);$o=idx($_POST["function"],$s);if($o=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$j["on_update"])?idf_escape($j["field"]):false);if($o=="NULL")return"NULL";if(is_blob($j)&&ini_bool("file_uploads")){$Ed=get_file("fields-$s");if(!is_string($Ed))return
false;return(JUSH=="sql"?"_binary".q($Ed):driver()->quoteBinary($Ed));}$Y=idx($_POST["fields"],$s);if($Y===null){if(!isset($_FILES["fields"]["name"][$s]))return
false;$Y="";}if($j["type"]=="enum"||driver()->enumLength($j)){$Y=idx($Y,0);if($Y=="orig"||!$Y)return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($j["auto_increment"]&&$Y=="")return
null;if($j["type"]=="set")$Y=implode(",",(array)$Y);if($o=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
adminer()->processInput($j,$Y,$o);}function
jump_input(){$Ef=['select'=>'select','insert'=>'insert','search'=>(support("table")?'Search data in tables':''),'schema'=>'Schema','database'=>'Database',];$I="<span id='jump'><input type='search' form='jump-form' name='filter' value='".h($_GET["filter"])."'"." placeholder='".'Search'."' aria-label='".'Search'."' autocomplete='off' autocapitalize='off'"." role='combobox' aria-expanded='false' aria-autocomplete='list' aria-controls='jump-list'".on('input','jumpInput').on('keydown','jumpKeydown');foreach($Ef
as$v=>$X)$I
.=($X!=""?" data-$v='".h($X)."'":"");return"$I><input type='submit' form='jump-form' value='".'Search'."' class='hidden'></span> ";}function
search_tables($G,$xh=""){$_GET["where"]=[["op"=>$xh,"val"=>$G]];$Uj="<ul>\n";foreach(table_status('',true)as$R=>$S){$A=adminer()->tableName($S);if(isset($S["Engine"])&&$A!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$H=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),[],$S)),1));if(!$H||$H->fetch_row()){$Ki="<a href='".h(ME."select=".url_escape($R)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$A</a>";echo"$Uj<li>".($H?$Ki:"<p class='error'>$Ki: ".adminer()->error())."\n";$Uj="";}}}echo($Uj?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($ol,$jk=0){return
on('mouseover','helpMouseover',$ol,$jk).on('mouseout','helpMouseout');}function
on_help_value($ij="",$mj=""){return
on('mouseover','helpValueMouseover',$ij,$mj).on('mouseout','helpMouseout');}function
edit_form($R,array$k,$J,$Zl,$i='',$G='',$rl=''){$Xk=adminer()->tableName(table_status1($R,true));page_header(($Zl?'Edit':'Insert'),$i,["select"=>[$R,$Xk]],$Xk);adminer()->editRowPrint($R,$k,$J,$Zl,$G,$rl);if($J===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form method='post' enctype='multipart/form-data' id='form'>\n";$Pc=false;$Dm=($Zl&&!isset($_GET["select"])?where_columns($k):[]);$Hb=(count($Dm)!=count($k));if(!$Hb)$Dm=[];if(!$k)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Ga=!$_POST;foreach($k
as$A=>$j){echo"<tr".($Dm[$A]?on('change','whereChange'):"")."><th>".adminer()->fieldName($j);$h=idx($_GET["set"],bracket_escape($A));if($h===null){$h=$j["default"];if($j["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$h,$kj))$h=$kj[1];if(JUSH=="sql"&&preg_match('~binary~',$j["type"]))$h=bin2hex($h);}$Y=($J!==null?($j["type"]=="set"&&is_array($J[$A])?implode(",",$J[$A]):(is_bool($J[$A])?+$J[$A]:$J[$A])):(!$Zl&&$j["auto_increment"]?"":(isset($_GET["select"])?false:$h)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$j);if(($Zl&&!isset($j["privileges"]["update"]))||$j["generated"])echo"<td class='function'><td>".select_value($Y,'',$j,null);else{$Pc=true;$o=($_POST["save"]?idx($_POST["function"],bracket_escape($A),""):($Zl&&preg_match('~^CURRENT_TIMESTAMP~i',$j["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Zl&&$Y==$j["default"]&&($j["default_sql"]||preg_match('~^[\w.]+\(~',$Y)))$o="SQL";if(preg_match("~time~",$j["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$o=(in_array("now",adminer()->editFunctions($j))?"now":"current_timestamp");}if(preg_match("~date~",$j["type"])&&preg_match('~^CURRENT_DATE~i',$Y)){$Y="";$o=(in_array("now",adminer()->editFunctions($j))?"now":"current_date");}if($j["type"]=="uuid"&&$Y=="uuid()"){$Y="";$o="uuid";}if($Ga!==false)$Ga=($j["auto_increment"]||$o=="now"||$o=="uuid"?null:true);input($j,$Y,$o,$Ga,$Zl);if($Ga)$Ga=false;}}if(driver()->primary!=""&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(["null"=>isset($_GET["select"])]))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($Pc){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"])&&$Hb){$tc=($Dm&&($i!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($Zl?'Save and continue editing':'Save and insert next')."' title='Ctrl+Shift+Enter'$tc".($Zl?on('click','ajaxForm','Saving…'):"").">\n";}}echo($Zl?"<input type='submit' name='delete' value='".'Delete'."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
repeat_pattern($pi,$w){return
str_repeat("$pi{0,65535}",$w/65535)."$pi{0,".($w%65535)."}";}function
shorten_utf8($Q,$w=80,$Lk="",array$qi=[]){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$w).")($)?)u",$Q,$_))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$w).")($)?)",$Q,$_);$w=strlen(isset($_[2])?$_[1]:preg_replace('~\n[^\n]*\z~',"\n",$_[1]));return
highlight_matches($Q,$qi,$w).$Lk.(isset($_[2])?"":"<i>…</i>");}function
highlight_matches($Q,array$qi,$w=null){if($w===null)$w=strlen($Q);$I="";$F=0;if($qi&&@preg_match_all("((?|".implode("|",$qi)."))su",$Q,$cg,PREG_OFFSET_CAPTURE)){foreach($cg[0]as$_){list($ol,$Bk)=$_;if($ol!=""&&$Bk<$w){$Wc=min($Bk+strlen($ol),$w);$I
.=h(substr($Q,$F,$Bk-$F))."<mark>".h(substr($Q,$Bk,$Wc-$Bk))."</mark>";$F=$Wc;}}}return$I.h(substr($Q,$F,$w-$F));}function
icon($Ie,$A,$He,$ul,$b=""){return"<button ".($A?"type='submit' name='$A'":"draggable='true' tabindex='-1'")." title='".h($ul)."' class='icon icon-$Ie".($A?"":" jsonly")."'$b><span>$He</span></button>";}function
copy_icon(){$Kb='Copy';return"<a href='' class='jsonly icon-copy' title='$Kb'><span>$Kb</span></a>";}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string(')c(GViDWPFloQuDeeQUO^DgIF+y?4O<cX<jDcJA*[iuQ/YeF96jOuG0x[iu&n>{/Lt(peel""twnUSV4BjfRQHIaBSj#}4}^YKK<$BQMFP!+g0;Oaei]~TuoHWgJWsnlo=Q<zp
uKaG7hs1<!+Oi==O^}->/9Ea]~3synPV<Bm<c_L,Wm<:hI3>D]aslZ$SmG>H4Jg&]_G3uM_JP:Uxi;FAxIn_^J<D92mCE[jOq`wQHqqX[abvndl6
BuYT?K{gCgwCMO.[Bh>G.8O8j4cIh5,JaCXgWkoIQ!Xe%"4xCJ:#SsY.cQ)wF)[oAV@<X0jd2+]KMoD[>B4-v8{,,5LHUM34%n.ryR|:FR{0:aICtMI@=)$E^(E*:+o^~E%/JJVY<6`t<36)7mKyhI2:4Q%)Fo6^*oXn"r:NoSCF7=8WtpI91EqD&q]%mr`%zJG0Us@Lmi0lzD?Jm_.!%96.
05-6$9gJ5ax@d2@W#%4dOJ)dKrIhH<fII!d{G[[I[ajMju&sY5,h-_prPbj1%#EWXF1Gq.K+lD:sy4U_,<r&5^8e0eNI+ZclG?lfYe9UG!7:$;LSenK}0zA}Hr"r6Hk&7{RDK%2"Lqg8d<iC84Z1HzadoTQIv^Nm637v.T[>ekSylqlA2,Sd0UOW[GOnj]?I(rr3D?<fp#f77<J++3-&%V.~ZnmNJH[y]`-^0uG@G4iId}ogQQE
<?w%giV}f@vlXb
MvCbX8f:=d3Ax]qKEp-m^PAQ60knO9)+,(K`(k_*pRZtYK2oEe>T<T19
8/KE0QRqMQ2Zi{y|s_*Z*3nf*SKkX~pV^ZNE&eF+E=*;AA"3>99[b}$CN/W3C*?dXP.!6E:7&
8DN`6p1]rtfE4<_
f(P[Ux2ry0nf<PY]XTU^Ll=8vSj8kmWn;8oo8dfJYdbKvrr6d%L0F6vmh%?=RDtJ>od.Kf_;-K&lem4.[s6-U8?_qZ!30>e
#,<5F60=.DmmVj*!6Y,vLqPc2443S*-I32v==XWfqK[TF2if,l72YBe~P:GUq|
X/rlx#"KguG#c7PC`orer$85!$5Tj8oa_"6XJgGlS7}9bUBF
:+9e"$IUt8G/YUY$<R%(D.F*RHQ:R>7?7T
:;`qB5t:65dN2oNk|;n3M^iUQN&@e-?9zZ?.b`%JIj8_J)
`cMhjz
][wofYiC
gdGrH?eQrkjyw-N|Be##pH#<Lc>6W5y#!tpHi439wyIApL)uX2<QNy,BPQ4}75%o0I.;0i;.J%(pu9KQ^{&h)*yu9S-q@n7`rr$s$qf78+R|D,B"/R"Sd})z=(Ed>Q,IpF&r#&g"P}1G<Cq&*"[f?fU85I0e::"@.fa;1W7q(QoXN">i)rx,jnB>]4-}`5EFEN4C2Z&IW"dxGF(]u[$EwudHf#X>!zsbDN31mN7/?E[{/U:1;Zw/e{YysExxYZM6eH,KIC1x72,.aQ"rHSs99nJ%d
Nf7^n6N:mxZXNKlGB4T*hRa*>nL-ZO^pl/$W=|^uQr`Pd"B;
5Ztg*$}E[pbUg5NY@=se`iV;,T0NVF7p,3`HFXRCH"j%tYQJFf~)h-uKw4BIUbI,&<}AFHP4ApY.r;;
0m?fQ4<mpL)wya|rpAEHX6"EK.xg.u:X]QZ:I>Q:_e*oVAtEIG8Tw]6#ura,4q,yjoUK{2x2Q<s8V!8YX_QZgJJVf!wdV]qWh?6^^48=msu1g(B%jO@Dv/krFZ<gQ(h$6BBa%/]=1Os^zfTZMn684",4rZmBm6f-)1Q!p%D6-azWWSqrT0PgSB90R<.2nK~x
@oln1hq7O+Aq%HS^!j31w6P.*!P1Fuez2Hg2F7[wTUAMI[!7daU^NVgGj,(*sud9I%PQ:?as)ZeRjm"7P!3F7)7cfrnHqB]TUQn&*~?yBd/f*H:J]~6,#9nu3646kl/VY2-F"pVVvb/4Lp@yY>^%1i#[
R#?q]:E,DDTcKxEsah{FO5ql6##D.^U
9lP7cB+#dtG9=_ce&gubW4L;gi_v{,[Bu/ze2=6C3f0v;.gnQ+=Cqy>Wu7aS3m`=/9eotIE"WigNR6W[xiMjqP#7g)xry3KMzUQILw~5o"/"fVz]8&i4~"H@}!v^cIlk>nf(j00GV!c%w0u3N[XQ
4FCk-&%,qxg03ekJnCA/fJ*AGxq_6O8@)];9={1=b&UTTX$uBrk%#QoM:r&huE`/FzQL%NbKRzbMiAQN1Ty)!IWWFXDRh#D$91F4
aj>R#u`<W4t1Gle.GCy3hYOE$`:H]m4pBN@#9c?>~nU
@7-Ts2bRIB}*[NjBCVhMH5h.+HLiA,7hUq{H!:=di6gt-W.qJ_sHC5xy5P%LOiv>bQJ<Z?HnK;:0#8c.~brLL`#,5i"u]n~o<#llm)#vxUI2%yZZJhA<;wp.UB3bs3?6)>2ZI%qQndQ
T]5gzW2-n?gm@"p)H<w^@Fw?@2[KO<H@NPr9x6J2WkpFe#_:`sS;
GXbLGn+r)`Eo4ey<#;J%XTCwn0,>=p[>U_a"J;kW/pDY/_x_#)w-v7s`C$?Nlw!-H=ZqKs
`:vt@c?@J.4:qc5ZZgXGjTRuV45fbr?[%$Hvf$MkWC>ULwvjWX1Z9?~XGV,[XDs?o?GgmHN0_X
L^]0=@$D;[IS8u/J_#n,0"_GG[WS3rWgE=3dmk`L<$?
THTnl7Rf#X9pU5%{6FM8t
I"gBZqYQyz!f`0z#-o%5vUX*dsG.A]a_0M%"K9)Qw#e
mHtqBU79kPRdi|e-EZp{H|_&7)/
N_I.m5%,Z0o/OGXvnRm#[x6ekMvnjMM3UUP!TBn1O![*hz>"Kr1]&D
W
Z5$v%dUWG=7$;-<ECt^3+%DW=XK:
1"K0qn_2.@)LiPG_n[^PVQqCWQ`==P0+jkvK@1KknKh.ZwoeA6
~eznxnCbm?Mwo.#,*,qM37xV/pIbYBwnSQXS>oWa5xkV+W
xb92pkfK,yb9[PvzK|@1rjy2R(.Y;"x"^5:&T*TqJDr%=@qh_:UzXhcD!0WGGXZ;yEhiFKUTJ*Kk8#wWEi"Mc@aVQY;h8#n~=O$i2A6b^-g0Z?)E$qWnlMSRpkmM(mVM`cp&LSsrxAssM"o(_.&{uj1Xcp2GCsT},D0p+JVt$a2<WI
fmg7|`7/[LodN6i,FG
Q_??cJuY,CbYEVVtJx:2vlAyr)N!P)bs2PMq/jUtW.SQ5z$axfc5c)RwY&R"IqTc+FbU55Jy>*y>j
qeskK>9ywq93J2i_3{+-9H6gB(dNv:C0"!b`d2,:^XdZ[jbQ(O%~EIXlv<xqlWr2DgXucm1h/pDKu_2]8c(m!>O;3g7EaWUfEM;VJGF~86X%DB>~Kmc16H]prlDLk?X##s*OJpy/^M2(Ft=+=]M~rEWLI/(Ed`d-,gNPb3UZ#r[_MiN@I0qisyAblwd1>p(*@od#I^OxQMysaxAt^Pw2;txV)Vh}oeAll&+33dRQBmh/vH(B@rt.Lo!(^Tct`z?^C~9Q&xn2/KRZ"rh+?]s,QS>XrDPIDS)Pp?5U!K1]qjZ_7tadl)(w-8F~->nbf>=;
T*5>HOv^S*De*8<5mnd9QO})f+}L(vmCc7r2=r[wa[~;NWpr<ZYgB"O[n_H!b<Fx_N6Y^nT
1D++zt@cqt%Fpi8ok7zmFDkhpZGATQv]O##sDJ>P|&)lX6(1p5iU]AbU25gPwQ9hCX$xu7sVPdrG5@|CG*]*d7fsUy|tXV3Z<o_QFu#l=vfIBEIl,MWlNkGc%kS1NI$!>tP`r)/jwQJ0Mmz+t$uy#lFKoS>fV88!HKxp+=qR<(6ZyH>m|pRmAw,a&Z"aE6pVz/fW0DEr0qr$$sEB1i_k15[L9S>/iAS]NV-74b>Xd+.gv>>0qF6Yx6[g2_A9b;fX5Hm,Jojgkw0"rWp9iC`l<1h+z
QY"cvdCodF)?W,7v|ce>a,9:1y&E*f*$SqJE&Pm?nZQ%D#xo8SXyNj#ke]eN}tE3|AyB9K-+DA><h1(jZps!qH-wO+@ABI?jd/*<J_B,@<HcsUx56fADck+,(Ft.`h
0J1^GL;Yws*)pU`J
Y^N&##tKUjZ9}WYO~q-a*4=misg7^qV_sdlZ{,I_Kj]urbQktoM^6`BH@LttF!>Rtl)FqHveX!0T^[#A7nsg58]a:;>Iiiu+{[OQG5z9Wy_-{!3jE4(.Jl"yh/N>6bi1`xO0dK00KndG4-+IOC9-i
MtAK{J_F]^-[_K5f}*#W8s">W:?;y3u8+SZ65SeUq:K8>5EuOf6,LB,AZ/TF.7wF%E8refF-oEV/x]Uu%,[9MaYdbpKG_;BeQUW?Gd4Z:Qljb*A00N`(zi:9#0M/0sj2rAKk0W*/I$q7+RSe{:"yrN0S8:{YiJ!5KuSIqBcB(_f$YnJAoqCJHy@s#%m(6UjP3oO;K$?WZN;.eN@"suLeyu7Om:3I>rG/!Bvka;56`hT*ktV*xWXZ<H$eqt$j`ovPRn,.pNza>1#/Du5#nhVUr2h8dG-TUlGF(7[Y/TuNOA,^g4edCldg.MkqwVfB%]WD~e#+h#rwG"md(2yx<,+rk*TmtNIjfE8Lu)oyywJ.BR(b#Xdc?gTHmG":|eN@RD+AX]HhoQ"q[n#vLpg?jy>`s<Wcl.d3>PALYJ[tSZXhc?|?N=S^J0^RlZf8K:f?mFSc"n0lm`w>DN@;:rG0=["rzTT(Opk?6O}HNEYcgTP@hR=>TsK;<f]Te*nCqKRpTo2VpIOSI!+6W>k)7$5v%:#vXS^=M(qJYFR+j82YKu5YaZI*{Xz1IB^:M@=IHv
yl#HhmP|9;sbiw[b+z&^Jcx!C"(|$PL:y5eIHy(GVXQjsRN+z"WT;ln$,`qmvT
G"r@)>o7}#E');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('#OsbOb3V?!K0U*,j#-4P2Bz+35Y(O]|GIGFDsyf"RV_CWG&(<hG7poe1|](vZ=6gKb*-e*bfu`XNvNZUFTxP<kqOjr%%43Ge]*BE&fsS#$XGQs=jy?$Jqw)[=$pZs!#`BwhTS`Hx]c/
gWB0SBBXi)^FyF(MQLF,

CntAU.ADCkT^/<4hx@Sg^
Q:{DP1JO0$3OTgwkiNq>)k;xr&Qh*bS(@Bze[?PjZu!*h&UmI7<lxWc9)SaQvxR#;%$<pQAd+6,HWq@]vu3^lY1Y-Ns9y3L5H!=QP`I(rW5Hmx2spr4^XPbOa]@XKH,y^@Lq?=.-z7}qzeIF=B7yM-[7f*y?wDLK<WkFEA"t,#vW6>r<#Kf"]LY,kcXWxcX43
Ass`W9ARbvKD)]-@s[:$!rwYav_$?oR*(xrD_R;EA_gOpnVg]m(S7@|g#&qK8`wv8>Ve+uJAe<BCTZ=x251_N$KrVP!Q@9e&?&z={f4CNVgErN_i?URd=Xht|Qr-%P%uCSWYu<D86`]A3;1;d$x"Gm))J17Q
ZpWmu{G[:S:^$855HJcKZKE;S1-dlyr~:<jt
vN|yIqN`"?)$N>~l|SM3|;g7*iHMqkDXK,u=-Hto:2;IM[pN!44bS5#B},0<$3}je*,c@B7eav8x71@N{#iT(Z;#k)kUdlPj<1JMoyDm5@QZU5N:f5nPG96r~P]]M=:)mv=gMBhrpK&S2geLF@mf&uke2lkXlf8RiG~[JJkKov&yU$W
^-NPh_0Dd/,;3Lh5Xqqi+oH&|*hdQ%wk_U3(QJAG!:|Lxc<M4!tXBPGbzHyeN$$4U![h!BOH|/uZL0S@0?`M=qbV;Bk,}@3+n@Eq"FY;GKX`tx."s9E7uY,[6Rh;.v
(<8kBLH"Ke-/4^wv
dI2k`b#;@i+L}<oMML3kc
T*|N]?c(A]J!zj7Z6v?+[v"xR^qVr&HXOk_>=2=n10?w,o5J{Y;tk.Fnt^[!s$xmrAHN.V+S6?wVou8,Nt:OJ3#T6');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(')`KbOh%Z51pK,ikTs?)"H;V,IIr:rC[t5,_F@ncm`@yoj3"_:"vA_VBRR$RI?[f_[w-[fNe2X4^yyl=Tq
x8x"wGoLxJJW6("@rvP__?ee$FXD
,"hNFt[01(c0@O1M
7cgR,9];2Vqh$;omxg<Z1+I`g40]
N"NMpph0J>.4woWEa9x,H~KyMz)^$~&=7y&,)3Zi/:#LuN9`cwOcNF<?p+mWOvqk^)lzHt%*ysfUs!_&74]IKI[=7l6yD%Ojl.PWvS,{1$@RW%HFpi8pXIotu
9vqP3l
9@k@b"CyS)^]UvEFq)f"lY^5p7"nPgWQ|pw)Me6V7oIeN2NFbY<NqbStJb7Y/
.1tWaHzr=-GDr&>Go;tQ.BK^T*:x^0PT1y1DsH*Ok=QJ*/~dv*qfQbO(X(XUDZ&"5,P2;yGI)3n2<9R_CsTh0T!%bPMYYTuaTDM0`+(IC/M>Ypv60-@gK7Nie
gFAOe]e__m8_~"l^9hki:Kc(#BF92R-4~W#A5_>QMyFx"hZln3g`T;WJ(ArvB=^V5?XpDYVi6mv_S=LnA8NT2=F=2o&v{X+mYJXnTc`STxNxcIF`+p1uuxaXiV;pE7Z7}5y.LFYknDJs1Vv7h_RQvkg#>M+odggKSiS2@[g/)6IBFW^%bi^PMv/l<MSO+1#T@&^*61^B"
7K2vVu=I.w#DxX|7Q%TUVc,CdL7;*;$(Xi?QbS2vl%+,e+Y,<goAui+:-iO-h/H;?1O/W%!AwZ7*&_y@U9:1otdYJ)5B};Kg&kZpbtxOm*ubdOP/awC!L)Kd<NJ[@d&rgb+(ojc^?S};RQvMZQ8qPeTUnE}3hA4WaX$Fb6[w7#j.|M^<&a8`(p2mPhBA?brn6`gKMc[:v=CJ]3aP`;")-8Xo^yiGyc]PGjFD`N~69p$di
LlR!SStMMcS>5&VM#cXL]FCfd
bNz/a,:Zr6([8BbJu1fjKHrj9Ea(;?qaiOr1^>nWp5)8e,{xGYpdr^%qs)
cLthctKR;6(sk5h$xbP)C]
@.:.J&$k;#GpyjqU?N1s>)=]GMKF-&+TQOhc,NAfX9N:&&2d)+CccK,KG`ptmluSbEVs+AQB#W1-c&*Ut/2m8#YSB]!#39x9TEM6RY
vqDY7Xw0wvfo9Y.nT9GwVR
%+UVSjIw$&PoJ7+5IYF:Qr+m2+:M`c<a6`AB!wpXBU8_5!^$-*6%i8:6$>)>mu4N9!Px
`Ga|m:>qTYNg9(WiXNAFl7lzYn97$]p{K,@*$WyQ,"#G=biZ[_g`wxNI`ACZm#
0!/$.$kG0pg`vU"y>KXxvwLDXT+&bTDeBWg5>j~]G,z@_AcBsWM"HW,=7rnR0TrYs4_HJCaye0FVbbAZS$
h6U"E-MAI
_GR1Olah,&WSed$fUtSWa%Osh@0)C8
iScU(]c?C<mdfbb9>+{8m:|Tz7ydRYzhX%5Q]XaMm(ypvytUeJ-n1K}yd#n(qJ]y^K{sd6km2=7eBLmPGeiK<al;LuBn}^X#Ip+IIlrV-Fn=jYG*sd$RvO]:[qQLeX~"i2E,,_mlwgPXkK*0EKkccF,P!bsOE"`XDEKGgATQ?9B1g9bJc
KyME)+LwPWk]SED>4oTJ6<%WlaYdOu2;I#aX#XStlD)=@>>USXpe7oCF/Tk@?6Oel0guR/k>]Q70<Y#45&5LE<>3f]#&U0?f!`/Xw%4
jDUJpWS>28V5T=M*~C9B?,Z(_&98ini6R$;@cSF"a)}sN2&c/Dfjb9GwNih<6!lhl-:[rF.cPo4&nP29{AI$m+r4J_B3HS#cQS{J=uehbK=b|.e;f3@6_IAGrGy+G:_pXFo`^_=WoPY>AOEW~/h8F/y]bQ&dc0HND-|UVbKjWeKq?dh+Z+(hh6Liiivq4H*i~Oob;sp"&`Reqy6%(eZIDv1ymiLP
vTh@ortB=K&}0ithe#BmfgXq(WjkX
G)*fd2f_fh@Pr=3PDhsda1iLIXM&ww16Q"G6gZ;YLjs>j?,.c~J8vma;6P)koE-)y]5rmv_+qsYk[Inzada/2JtMyc>fKIc2Kqy<Jl2VS?JoG&
!cpVIMP[~c^Gm1Fe>j6DN_Wdg$KGR*>5Z+_O?UXc_e)lmnL0t6}S~(A]qDR"t%eAXlM3>,zfJ0vZpp$_dZ,,zt..wTFU~!r"s8522AgB+>YDDI4T]lnolcFU;Lo]^;Sgp4Z(c$0oW=ZOP1PNvY1^?4q;l-ovUs<,1Y^8D%Rc)5dB!&9[.EeM^1mT1)Bg1/_jv<RllYu^]hurh)VlF>>ttiu!_&B2R-#(h%D#]Sw@g28+^i|"YB!_cBEa,fz)Ckf@&`ukf+#^x1MBv31,4d7D#yCOdVLZeq{<]2w*.+$m0r#3)wd)#<KJqOvs_73
<,?Pf]r2S]OK5;/-DX<u#
Uvc,ZX&DnNq(t2,F>#0H2ak.^"Rr=:&r~(4yuU_OIHq#YaS*=?c[dBn+
vpX3=v1I!Lg%k)tyCMRvJI;dxvmcC-M0/tJQkmlu!S>joCKgbh9v/iY@S
rB
R9p".6dPEe/KZsh8dC/C?S]5d2aR#d+`mrV1JYyx]6<vtS>lkU]j#t
j(vHx:=@HVX
/Fo@E#qrOe.ZD^"+$]DC#_&FHjMqN]yr$Tl"49j5e;A5A.V0P|ZiNbS>gW-`&PPr4H=Q8I&//7z"6sxM%_NYct,ofp*P#Lmi#j;__-wIqO=na1rrw~8q!5=m,0q$#M0,,X[W%/_HR#k6JTao6`wf@(
)z(UsmzkY_@LwUW$c:OQm9BorP1]/_y1,%yj>@RU*F}bN>e==
T)}S%^43Ilb73?E`1hGL`cWhPqH6I.GZ*CvI`/N0/i`)H9A#]05.F]A9Zxy[W"zTj#U8fI5P5UGUzSGNIN?.V
Rt537;Q-b=Lv>/f*$HQw;"DtT"pl7>.FpT}j%JVv1Cz-4<<hL#1o/)Mj3i;,qogAC=qUD
qn;Hnx(Pn^.nDht&*M&k^QM.rT
]aGhkf"`3)A8Yu2L*VF[R(nhV>=A.&giZ1rC<;l8v*Q=fn,IRr83MGO,-Rl<ThcvR;QSk(J--DWLFoyg-M#n?Z+kpoG<MsC^.s,(,Y=Zhew%(Aep^L4*39Cg3q<:2(>(k7O#
l%5*swkC<83^I6JVG
~wWJK5&x|d>3HnC"A^:wKRXbH;kWg/O6t6G4>sW:
vLL!W#D6_#CyQe+;$$JaCk<"0#RWu?mJ2elz9{U%lCxXpoM[q_lSsOwr<(yJsXlDN`nZ1%lIFW,9g%<"lv_if&xG`qdAcd.df<iI)H#~04XHr>NZ/Hdx!U1c"%y!RPXl1[3,btUAxpn3jyxiM3W,wJW7lE72G}8ZuZs7K)w)4k(%hXvN"RN9/Ts%hQ<~1t#O<>SpcG%Zd-ZRj"gFN:T:U=)W8i8omD0riCX7)>BJSsRV13%NT~,YYI"p$Vm@:3.>HiPv?C(]6#HCAky71lRN34kcb1ts3eCzEDY1=Ij*kDEU()rU/:WVj1WW821:g+h<G5cui=tO_~[XQ#nzRttfvJ55=wIflTW"6#&z)zCj(VCqR"VpNnR!b@Y_-<.Rr<M#NzSCx)"K8IA#+*Y6l},YeY_WB[[5Q5riJ4Ib*+Vhj(TI0,G$.xx*:t)*fbqsL0xyG`,]UlBPhm8;nZ;qpFz)t_tWw$M~H0feaG%961uRdKm.gPrPLT-
BIqbnzD<SPvMy{M,UJy~+|d!m"y<Yb
H.p%T^I==y
=@cSDmTo`NYgVi4FC[_j]v[C9kmgi`46.IiP8hQ^DH-mh?!]h=X"yUW=Mm`@5v*;IR(Lw3cse&l%Go=$tn0
n5Rxr>Sm#:[|;Sx>F$W
P4r9&RjKP2>6P~
7&Ni7^%aWmuamqU>]y7t[NBI*xZg,i#_v.J<$#gj3;"4/A0RAq5[#7+f%&H:(ww0;!
u(BS`7jmqF0ft99g=`&*?t)BZR`e+ru-f@"<c.o,QH<UDe0xbptroB92=sl";.yMrNJ(Y<WYf?Ns*FL|x6%?j`p~x~q*%"K{;"uTA",b,8>ah-`UiT1,E$iRiuDGQeqL)#;WVe%wlh!8fIH&Dz&Yh/$zG}_($Dv)nAJ@v/cu$"_;u/i~gKNhEf.x?:x3)q7)tgnP=2)xcBs"Dd)W2ycImsMuJ->$AUd6Sr)Z]MkL>>F*G[HK*=]"i{$tV0N->a`N9`jBMbVmhDhYNZ(?b^G9#7:~1
n#T~BMH^lJN^9rmJbMJaa!G}W~K$1zP<]cyHiUC22K[_I.G&X
K"v5Ha``!gqR_d=^s6Cup6rE?Fc~#">Boj^zcoh7,Tx%wq-B/m@(I=bu&Ia=O>e.Kye:CyoTwuv25$CE(wB;]]pemQnJ:rv{u9XVlsDmqxG_Uz]912RZn060Z3gEDyo
/oFfHjt-!vr8KwQ.2H
EIcG~i[y,)d:2R
XyWo<]2
W8tShKa"Y&<)c>yx1hu7C~@UQ#WC)u;dDl:/o*kGv|^A+_ecc|iK*<KTF,2$1bQ.D~b63bdRkE2Vh?I)>_-[Qo:fEcu$W=ugI9ZL"`C7H[uK!"pQ>3D5A/T}]te0F#f:_ry7M{.$`2:2b,lYEV+Tjk[[UXlY/8<rlPC{3+9&:t[mDiY=@H#A(AR>`_,([{xF8{=]R"IaP]!$AaX_*=I;3fL4Gp!=>.h_^y#*f!/"h9BlmE_)rXPf9NN3PL:L+mc/YH
M.8Z[d3;I@daqQRjzlQC3%TFVDe_IS|E"91nmaM"p8vsovQfyiQyB:G]_E:t[=p.>LR6T,
$6TPZS_h&>%Ar-J;C}[1&R)VsK-NbSHbcIA`QKBV1V<xR,c$bM-))wY[mEUc1:]tYW_~Lc.{9_f-;5A_l]fI-/WBy}4jnRa&Q-O_WnSQFD7,4!C*VNIu/d+W7%d/?l.]$3Xe!;W_7[E:-P";TG^%>ynRbUT5MIjsij@N$hv:
mGLo_.z
d$Wu;lZ2`P<7VgZ.HO-jF]()YTW9_N<*sPStyD{j#ffW-leZvZixA0a#4`If878O6]@&YSDK}x*pX1iO>g4ITAJ6$Oh<%XvmuSUdR=705F@T++eDa@ADKe$*93g.zkH`!a(GTQ`qD*6q~TO#8N!F|b=hhj/56CCObQG1H%Ew^v]>1D~#OE/7:UWX0Me2
_(jpZFk)V)Ge%ic7%
!)U0O/1"HS(_WWwzyCyE@shKq?!#M6`;
V2UajI-"rthK%/C-)OPo59BL3N#a}8ACJQT`V%f%C7@7|-%O[n+"ol7FZQKX3UE$)(O5B4eLy`{[+M+*82Q6?WBQxQ&epu(a{L7QYijjppkv1!FyUZ1.,A
/P+t7..)glu>R>[QQF3*`k
G>7Y5c%$?gvFedsFKrvE(O)OIXz_cdMfO(W=wjyCi
y[J^@&Pk.89C.^K[XORHia!0m.+o]XyRvfH%|$M/*srk/1lwyi|AaM$oe3#efkb2fcx9;q#n;])1nrm4?Kw@qY
n5(tnE=MUK%,B6)Wd}<XnDmT,6-GEX,TdqR%ON%4bov2jOz)mh81Vz4,s/)jG4^/Up
kc$UNEf]gqH6Qu~`s6!%VLsotnndz`-sbR?X^$,9S6[FDWIvelBQOVPsVaDEC&Phmoec4cJ5hkjqV.wI3-9/=sdE,;<YmW|#/<n*]g8PT+l$Wys[ryp97<{9kPG=m8V#&.NSOy4Aia17pXwa88bNcZ.G3[Np$,N;0w:/:+]*vUxW8al^jOr9+"
C}kZ6[kTa5s,I.2*&;hZG?p)p@*3z#dNDKUdSL%sb#:vS+aK/0mbv"]7.cn~`>lU+P/J93kn^&+@*5U(uJ@E#/<B/:g]G[YG*r_a7vwXB_gC5JrBasu44c/fJ+k1jC1BMH6K@=J2>)ERXd1Cf2nb;Y&-N;_KuQ?kRlmfFgfp`0RI<u(pW3eC-Qo>p5:fCo?Za~.U7[!4jg:f#XO`cY/:PGr/&_2eQ@xl)Cy5t*J&OP<2Vz
LIPD4$u@umM#X%msUlze32_+4mJ<{JN2,%-F)s%/
.{M]H-+~IZ2Yx;1"OJvOG:rVVMc
lz2+]vVAR*M}7eKJCi.lDn-#taG:oA[n;|fR101Yt-OYn8v=KuU5Uc]tj(J~9]l-V(&S1;D~=)XEcZQdkN>Q.gTe2GQ.U%)G=qW<G6!E`KIiU{;b["FF
b83)(qqju<`+vvaW/n;fb7p$w/MedWb?:6MY?20QefZXW*U?t5*(lr+wo45DPaayGX/[RiN6@w$=WoV7$HU)L-)g(n8I5*<N@o`eN*FjPGco*o<dvMfEKiJkrxU!J5VBR1hni,fjikn9}1^uhN^-N$mrwLO_-)LDJ-^Y._Y2BmurMGhPVh3]^ALQr(I6ym(`^Rjn$AOq4U|3`
X5G[1=~!cy"bMM$K@gD69jYZ89X3XbRRn4eSh,vxtw6*jR.o7<`@K7P=<iOWdK*In=9ZT.uI(5}M-$;km.*A(RbjRCc
7TM
N!b(A.uY2Bqip?;w8]ue/8
J!`kF
SYsUbIG^cu35F]%J!0S]!gtXv@vMGj
_=e"hQj:59QZ{xVWQ!~8Mn0/)N5/z8JqqnYqKUfA-p9
xUdX~W80=kE:Gh{=SQX_L(UOU/*Ko@*TOo0_!/!4pHzFqKuM*729fIk/B:7*TVsDTFOoBt7Fm^&Ox9ib4K.[U)0$|+gb-[M9Phgk,;eHZ4(^Htr@%m4r!nlWGw%A/5,6y29W*Qm)1dU)iF<_xTOZhI~3Y`2W{,^Y1$JZ"`nU0Q!!9?Nf*
f(jte7z`B%v71&Cx$0au@jPk5`%Lh*HFG,5]H(4.#@DVmmYO8RPd8-GS7fV)g39y3<(bc
`9))[DMEs_j&;=x@pyy7qnBDwSWBtJh<yoFVP,^m>ooq4,2JG3un9/SUS%5&12,pughmTDL.9YS(G`?nW&gO?t!lG(G<Z"pC;,:XW)biV"9!T7fFzU+OFYPVJ&/r5+*1D&2"U6d<d:s]aAv9oDCMJ2n>1TPWkw,^:^ldq!6UcOc?M!oR`.u%c]F$"u^s5NqEsKHV{p+9iX7_bR`*0"slbB.i>P
]INGG#qr6BK_FvCR+,w7;Q3~qQHP@3&wu!mmk2KE-q<##
xfJN^N_[`5m.RX&&,aQS)"D)L

mE9(T>.!~N:8>jo;r!N$h@%JveqX<r;;Hs<.m:)Ei<B<0aUfa
H-tmKRB)00VYp$y]zTHLW3(72O3;htH^4L^s/0z?fo@cwfd0;%_NyfbGCaPqbiVt269fPTWJbb)>HfxmWQ5F%KOYUY~JgEmMClo%U45kDpT#$BjFMT1ya1?H,;6NVTo)e0l4-8UJ+M5MQ(]dnU5cqKzu`OAU=nr2DK]0Wb7wkJN$:Y+vJ8,h&y:hyuQ8!T6M&9}1P5F+4_$LabuFd`+3@Gd$f]O#]O>_QDl[LVq4V-B^sEOs6;3n/8_R-;c#:waY;fw_NIx3{gy8wC!%Hm61Lj01k"RdK%w`?Jgm[RqYQm%49N%%
CA5#E<C]&`WbsNdH(dc]FmL[pK=^tq0AgvR?g*wSmD)$A3`P64JQ`kWrlr]N(cX~V>g#e#8g_DyZ4-ZV>f({3N)OjX!9jb!1whhj]F)7TT:4PVE^=BO&,<k^tIY2DFWjZjW;aP7k`n8W[9OTELeFI-@Sk$u:csqW?(vAQdvlLex=)`o;YssPN~@id/m|S?F0WvaZ/6@.0T&J?>X[^6Z9&!f?)LGb">.!=dqX5-)$T2Gm%~mffvdAyw/)NgQ]f/[w(L8V3-oF$J>;[YZn2(FD9oM$i[gQ-HS|p0ldH0`]vRVgUGt,)[Q0YpF<Y-c>(Q]]?gKgy01qxyj-aC*V;0fQ5~N))3poqN1
&@Tp?]Q>wBz#9~4_f%=>Lb.n8j:WO.j4:j2tJpN5mTUWS;:yk`/M(&.B:!U.nHA?-?Px8T2VVSGOTU4WPPi{p)AB/irBXn,Pu*"=U:t]T2M4(S+pobo%+l:]Sm
TZ5fCg-p/`!ftgHcA^MePfw)p_:i>?lU|?z0*>U=A[f9B[Rg`gYpVu{4%M&)Jt-j&Lue|W^%-@btFM6%(^VM)*3ayO[!{y5-#]|PMW-.8,/qDB`K~h*ek&hK;%?&KEn
5cVonyoiZaSMl6.=D0[B4cFXZ)jw0[Py@nOZ%.QYw
c=`K!Y&g}M`^JxP54!oon`>tyHaHGDS3@8Rx0q=%)EVR^w?@dt#2r=NA0r"p9Z<np8K;eZ.A)8X4~(M9.0Q^MH#Fuo->Hck#n;g+Kco:zUy)8Y(R!Al_%-CjK&Sn9VYv+?=!U`iuoH<gX=/C_!r2Vtv:_5^dS@BX"v)`5QJ/Z_39T>_A8+L`6+sDuv$;s$J%goJ>p)$"Ay@XHf0yvYXhlhb::i[-jqv9@M;Me:-2<;r9g#/YAr_%!h!dS
J
UdvCom3#Yn.i,vC/v<9p5G^lviKFcD`:m;ir8t/8ghk6KkClcA
3~Ef2=LCa9VqE@hRk*ejM(SoVTgT8VLLV1QQteGkDDXVE1<IB8dOhwU[JonSjl=e`#`MUcHt
CNa,331q84/`fG(S0&94VF-YVpspfQ46@cNmO^JR9I5abJM5YLMYzDf^Ixt[Jtm>e<A4_@F;$CHRC!l[mM!&~fW5.a
;yf>l%+sZND*M*?@d!/HVy8|FaRk#MT$e1u|w;?]D^$$t1MUjhUcMoaS=>s=[lUgl{RHlHkP!+v[N%M9]*UgE1mkB"H:=ZE94^f0&IR&?6Z`[xR)a39,MnA,2X4_G2kst:K7xnIb)alL&d6iQzJ{n:5ht;gl%lxM*l!dB:oXm`hCESUBb7_x`_42:]5!*dRx_y7|47n%XhFe:[88<H.!UjX<Dx>1E|GNYoj/(rA`FLK0GG=2aB_fU`EB&7_5t744t`mn6[fn`DVHPW:9m4u?X5k[5{t}qCIR1=Heah
`GN[oTM)UmnJdUuL49`F:dC*CF-B1b4Ta@00cT4DP!.>Yf(rELUw:w&x"lwiVyzu>s*
GKNZ3kQ,E@m>Yp<$0waa%yv?%J"k>Nu%y`f"epVvv#dbC*x5iTYP4$LFAC)qdtOX!W@(Mb?y%o*-#VK;sp^gpabmU]mBjt)G"+8G;g+ua,R],!HPnm<=%Jj.8Hy1p@xV2in")@&VUUvN-aX&N/KRN*m+PnQY{".Q3ub5[I$k<n8Z3`5a6clQNSK::+.l=yP1$ZrpD..]4$4#:cBDclJ
*=sEwX@Z~EU(*uNGhWGE*QT`USb.:d@3$jh[x-nrm#6]rg1n&X.XFc=9I*!g?Twcib/`4!57R2X[U3c*M!%=zvI6($hba@SrVQ;_vxA]O3G2*aXm:h3@I?,oN#CW7VGrlBzY&=Zs)TlJ}%0ws/8v}>bE*X!.LOli-FV2m4`${erjZ6lTXuh=<6j.Zy1baDXr8[N)E$
]iV;WM,<;9!EfnSi(di&:V$HIW_YZ`/6;hgM?S;bc#S;S[xWG}+-_*C]%t<~++k#t83urZ)m.Gmc-yw<1O^>#_*f)]aWX&<~8Gaz8V.AN+uSTis:sD(C7cC<On>:=}-tc2W$-;R$UBqHi$BSUV;D%B1#E3;;FDrEBf$.LVLA*QO`GBpp:gVk!}iZA:"j7BU1g@W=94c_&<r|uFalNWtrwe@+1-NiZ9$[+>&Wd!O[J&GSB@d(#rIc1+nu(@fZA9H^Arb6F|D/MuOx(o4<uT+@S#JMQV2RcQ.?Jc$Y1{@@_hLy%;]((zLu^vU}
i/X.prqYan{P5H&/8PV8%Y^[y#PS!#`Pvhy#(33T@,dVs[o(z]w/<JR4R2nY*EQcZ"Tm
UTZ
!X3%KMcpYJdydNJ*=j9hp&eH`m_?`t*4u+(>Hx":3yAby/??[s"=1k1,0)srNCkuGpO(b)e9Ew*k`3(`y*]R1ZhL[b
&R$pK$uP*z!z"W^P.Y4[.h.ehdhDppcf6wMEGgOnv3G/)s+&^HMI>:DucS++5Tk-5?7@Q?8uXWmwd=iL]@6P1UvbW_0!JhI(],ekPl-Zj;+)#B=CJ
C:{<RCU4:.ZLo1O3+d{#e`CQ[=jG{SM(oUu<3NCgb]<PMRi+:S?r.qe6+J#*Z>sPz62S]!T@_k"1`T;##@_3BMlQ+K#`Fy1MbQlR~ar``R*<Uy1nDAt0-sZI6H~qRDtF=dpQ_O;4Prcd0iU^"fU&JiR5<5$x}<a%)atw^pnw[0h:s<zH_d/Z]^"<Ak9>LI(BzCp8*!dF$Z6pR@G_eW3H2rr1
4(+KykywSK,aCd<x[$Ng/o8`gzn3,Gf;1P&%i@"=PF@NTDd37=vl=?QPq-)aNx!ni,S9Lc
z.}:j.o)X_[ox5DU5RzNt-!arq1?zXy?PPpW6?/W*,J!TYnf;dX]sXHsktonxC]inw,%4hB%jB@!}+A_m>I-UkJlw/~[E2#;h28h6"[3jo]8F*<(1B1vzbSfLq(tTEOOvL"qP.oJ>m(-JTidL]P(#TxC;%F=vbB1Fq}b<Z"l?_3Ehi<Q|y`EYKM&JdwS6fsjQBM0K2*P=A7bs=SsqPDkDiaT`#L&s/V]-7,a|2MHK
6VMs~$73!t3>x`UIfQcR,*m8M7~67+pN]fI&d_(eNx*O$!2[v]e0k6k*KDw+kj)P4#XsLGZ(NbeZc"TABFYAAWttjNSY;Z%Z/SVAd-0W`szQyk,ErgR!{>#^
UJ_JU~1G`p@h,[+02.+wLba/YXXStkVLf/KT76;q%UVX^J:^JX?m-wPylRU+QMTZmYA/$0uj&(S[U84_X=G(Lt=YY{ionXikhgW]D]fP6S!9^:#F=to&)0T`P$c;INZ0O3)@O?5dkOQ&jGc>2wfElU6RuIqoUVno5A+x84vNi2onF5x75{EPVtCtNWfBU@/NCgKju^0@<n((uQUB`0RQ0jV;V?S&Zr;J<&yx:bwP9Or_&E;Q@`u<sV`s7a:B&l-J-KXaUUv:*<mP5%1(7;6Q[Eo?,;c%!mw/a)*7D~QtaWXY4mg"h*_]fCk)L*BvLE]&57nO&`cJ"LTMul9Sk@5>VmQ
<-7zWRY~/$5q`%x=mD95ab;@0O;IY+y_]s)kf:v!%[a!vZ
0gurMH1Y_3}@YB"y.GJm@C4Cv(+0!E5*OL"+U$9+ke
BPUxP*E@<bUwaw2%ML$}Wzqxsl)JxS!7
7F-MyI#__cL*.ZYf_a%aQ)RYzY0YUyK8f?Nc=Hu/+E}KOE;#tR`k__AN]ci/+(4Q3d:TQop
pZvtyePMR#$khpr6kyzb[00mP27i?ENyZ4TO%l5m];Z^qdj6`,nZKNOY}*<ET!KxkNmv|`*mVwIL{`H3;;R2^Vav.e121=t(}yn86l8K
2|%H6-h~#T53,(DvFG_v!oUzimFC:d8Fy4d8GW$`].!Y?U-/%fe5)N:J?bS~Y)--[[qBZ^_4=sA^#Tsuh$LoP}P_k^IV
qll/`7<#SU"?c&p2).v]I&OmX9kY3Yl:|fWg"db5UEwR0@-WxqQ+Vq/NE1]Qqs,*duSD$OrZYJlwk=oZ#b#vbOn-OW+Fr+766(^CSXqAJ`q2Ub1_*aS?+Dzc.b}^<@mkrd!t[am2lE!QX@c`TC=X/3v.c^9l?JL"rKSWXEcADwKNgG,8uFY=8xjb9w!bbW$T#wPx1l;w8qkxPBq^AiRHVnDm-9+(z`_&Hd>yuwo!(DY(_:`jtDl^A"?E4IW7l!LKy5ck1@so_tEbtv4V7%x-GM|TWGf["S]Y{f"5w?V-:Qp
a;ZFPi1_8;H6d#}R+ue@hBeD"j#,um$ihp%]zx])+gpuG:|?[W^`ZPVB3ecqu;JKx^svjMHF;ix/@T(rJXJZUmTvxaJR1+Sf`s%N%0-H9mn5=ejYyRfw1l-Jai/m&fg[x_ghl8-()eY2bAE`r"<fa8dS+$lL2dpH&oy&)$<Bd24=O;j1`oRs!s9^p`c%(L~E[+0e9M}tW0nna<_&jEA[I!}`^@4,c&]P$2{FhX$46loh
^S=0^s?Oip2UMGSC`iKZ*cMLJ{>m@3P}U=AKEbF-y/ONGMJ?)Wg]/-#%t*)X0HHi:X&i,gyXPu/g:v-NI^#YFRFr$sTxPqMsNPo0e;
3Ppe_`g7A[^YqrH)jD$7?,ZNhph7U,(8?^,84XppH"v4KX0mh>o+bn)6}w4;Se3L<>zB{w6v
>tvB/|%L)XOuA+ZyJXP5>3OGQ^Cp#y#{aAUrS(!"v4CScC<-oZjK`rZMv~nAGVOO$ql-j8d(gQ
h9F)Ks<_ri21EVyrcaBWqmV*GklZaVfN;?YiVE@Unc,Ae)KA^`iuWLtKE1_<@oK;YTM1?YP3G
S$UqmdfFc&$5
W7nuX[d%$`[py<aF7
4^r)p7Ux=*SY)>-(EN+|D0LgV=u>[aLZifXf!OG-K?G,,a"Fy;q|RG!L]42HbK[;4R2:T87beViZa<T4r+"W!dsPmI-#MQasmLM)>8AzBN`18Fcpiwp@6[J,b
!s%wF"B&]U"U>NyWILqaFG1N"|g>)04Gu^>"n4QL;]C%_3KJ<bFzUJ*8fg=.]_4-15s;=,d-aPN`rU5VSs[YT{j;?~*prp+cE>;;a}]uP"]/1%KJ(N`[^lEDQ%Hm5FWan;0,tp[R`QLwA,]*o1B!!0LZ2de-Q`5!Z./Qebp-mPA(t)D$lmxkxVAA[>B},!y7+^&Yz%#Y"11p=i+MU?vj)q>v
jwrh2.&XAk%jBHam"1RRoQ!HTfXQn.HjEGW+Pk;m^"Pm4nun}QR4?Aey-<BqQD`F`k|h&Z,"Hyw&D9[`njW/aNby&*V<!I9;5qDaw_*$D2EI(XeH|1Zu5+n^/cLD.y|g(H-pXc|EOg;d-EvWS5&(GOh+=$p_qN7^f-2>hue]%JNZ}Pp&wA%JNGn
hcGFxervl%8OK@Gf=u`:#a3J+W/i|ksRWk-(}_&;Csrn{F+Wu=p;/qL>h4@>}:XQZ7mVp0sxw0Jg*O)NuR
Tx+H"bdSR<s{CTtz_i
:K
Jxc!+5jm*]+%2ExN`&GnI(fZ;5"A;-Et6;gQL.dEZ{rF57(k9!b^<MDp,hn}L3BP^RHAK
9xH3aixs<CtqLB7wt[&Kiab"u|IsY`*23"sJO&XhbwnVv?3l-]mzLuUJ5vbbOB_CHJnI9W+CahsMtc#*e7P4D>OEj23YJg!M)UyEfOAi.2"6
ECf48D(W"(eM4KMXk7&&6#"b|O[1N2t3/5OI6Gt4t>Y<:c2L16FUHsgZr/t1NB"y&-zIn
d"&f}p7VGVIJeG|rn@|/=
it[F#AGF#`aueAYkce@7Jt#:&voMLs
QB/Ekc]4A)*2MXvO,YK6UrG4J9A9M_.2TS"3?g8f`i2)U/_m"S`eMu+R
Q`x`W/)iA,|2p4dBiHOd1a;yvAEByiK5ZiJl=o0,z]AG?:~F!50V?_Qe_+N0z&P-PZlX{=<+)a:UU8FSv(unsc4M]k(,Ja]%3P0+[2
/oQOVC%WNzK^e|BmL/Y6Bq]iI_rhqlQS@6tAlzXEIxIC]19fN*llbkM0kVGr[?2=v5
aSj^$Qf]1f$vO4MJt]<de=ZEiNS#0$>%uSD[lO*9MQAN)-G>33_VZ?M;A`pp)gz04$})!9rg;##dwImTD
Q--k[bb*UKwD88iUFFe)wFAPg:=lf]6uyN)N)j@31QPiub2*uY:nQ+@S8M5x)2I>Cw!R6G=ELBXE:=IWv1J*9m7__]"BCcAK;C"cxnrhc+qf![l/@5I*z"a?I/zSwbR">$q4K0gtj3v.nL>,}Cl1mSj"Qr8C0a"=pnx)F(}4xT9(7+x?.#:);EE1:W5PUH!=l,NNcd8@4F7.|p~&^FI!^bwmb;.&hPH]"R;1c-qOj=hdHcFRXGj4aIixw6UTl`cez,5Y&n]`zgIc!wB%gBh>V">muR}je$KLpI$Rgc;%!b`o8CrwH-k3hEBy&ZDtL+?OvR
(>D<R5.pP.8=Ec3<.*33yiw;qq6ZKnHbWBnpoq&g(:f5yU
/c-wY4*/GM#aQf+6M/JfY7Mpo-(%<4@!9X5+K>*sbRZM(_Ca@fb[=vz6L^:Hbmlk5eo>y;3reY`Wr?;HqPBE9[Jwkdnnm<hq3NP6
U^"&=cyG2)>oelr9(rh713i%E7Wr`JuO+U7~ikZR4dengyZyH?5yu|%
.EJ#g.^a//8c/Ixmk]!i;Ca&wI4t6Rt$_^Fh6%22dOfo7%m{sU%+nm+"C5Q`24+Va@XDA;/A/7hg[Q7qPYAMD(eDGbvq5wy@lckgc1nG1sHggCqhYJ1eT*YIx@.cIr5=sB%tq[,*fahvcF"#4
_D.7`n_!jZ)WPp(?&4_]W1s%9Xs[Vw0lj}QI,T1CHDdp/ZJ;q3g.<@+Ic+kvuDD)KV`^)mNm&2b?S9+FP0p0VhdfyXc7-SFo[uQ]<xA,,E(Y/QP%+
#<$]%i,s%dNK*yqdFo=0ukj]J?iy1`d%M%dExHYjTrg!(F&m"A)n*jcr]Uwz!mCZ18-~lK?I#Ihd!ee.,W[@.gC8o=d,c!HL*+2J#DMj*OD_QZlFjE,4=kitY@+iTp2BnD/L#]+db,snFc+32H81xb
DSc1/L!-Y3L;;q+ueSsT9"{eFwjc_H0&XiW<`.|@XCJ/bnZm61:FS3u_wDsI5,}B^<~g=$
55#kt|k&dcy7-]p=rbt;T(*w0#=ToclAROiu=02ur#Cs>,9G*)<3X@ne6a:B.:dPvG"U%5j!!`>zTm.4(
@NT7HYB)a^bc,EyELTO7ht62.TfY%VXMTGt2i]N><A(dt`e~:``CvJEykg_V@<Tpq]*x*]kv@J9`XAxPa<"[IV#yqvPSC?]*Uk3"!-gg:s3`^7q~n(?8cIhV7Vm^&m^5$4RY_!q.Zc0wh|2&n5,hJUH2VMZx.k&qw#X
i6pH)y^^74$@H2GaoGx7$qN
vCs@_w
3mSNOHt6Dqs[Q$LLu=gT5G)v$WfW%;tx<p3UNJoZ[qn^xEh88:|b4L;RN1KD[b$S3:?:mtBz)u4l;pl
0pD
eX+yiW"Amid!sflH$rE)[8bm^)5bq#zbzs/HhV6)9sZJ}X#MPH}D6)fhX[WGRnb/Mp6anh6;!aL!an0w)QCrxK+:gT$89Y,#$K$N!N#k*/G3uL%a;o}T>!{OrV2SwpIf0HP4I8(v+S]nFFVx1s
E*Ky5rDls)BmJehN_V4v]3/*^u&vNu4}J$rmlEJDY+PC%v<==Ui<EqX0XHFvSNXe*G@Ui^Ue*+r/]DfWn|?)
aTQ)ZwRLUl*.2,bv@C!$Lq#5F4*=u1$9`-t=>r6bKDndzb
>
f-3X$p:x-nlvF<Dpck
6Sp&`c>82?e0joy`s[R[ERRA
xI]*OvdvWw(<!9mr0ANY"4R75M^Sk=bPHz9-4,6Q1PM_b"BYkjSY,@Vr%Fe,:Km/RdIU+~%eP2D]CWEU4},>?bIX#cBbqpo(N^ls0nG3fo=~"=@UXBS-sU/WQT>%-VwV=8R6O^l)1(HG<_,M=OGUN&T8<5?!/9s&/j"a*IfH$uND87p].uU2^uaD2?8_)upKVkK1p/#iJZn&XM>0--/oD>Rsa]N";AXd6|0~%x5l0jSYy!k$R$MZx"k<;/K5v_V[+fsrBj"SD-)w?vMULnwp>39i>[`B:>vTY-O/;|5gmS#;J%*y!&2[2IA+0G
yVB@@fiRxd{NhJp$rY#/Pwsq!Ey2mJx%8K}k8h{(ceI#.#:/)tqm"tmMc:X][ZkJC3}(RvE)M0g)Ffu0x,@=&*I=d6Zi#tb
E7gpe:*F@ImcPxR<W7:hAiCJB!4j2.K2XIS/J42"K(Q^X(;A59_+VsBO[U%iJQj8%!>#jOfWwe%I(F62G[;XI<z1vNMjRtbR#?Uj~%Nm,oy?P-$pG_C!1GwY@di:G`J*]BB3DG[#:4GH8mP4NT6pv
*?O)_3MSj<fpT`-@=*/rAjTk]KGYQ@8dSb#*tgf@o->^hJnWdXd;WINr/=%AXQ2iQ!G80g!#dW/PB&eFYhz3<w?"X")SlpLFmM|PXXEdTJSBV5.RS3@(F13qC[{Y-TwNCCJ*ve8#y7^d(`w<T-Gqw+j$H$/N59"4hQO.A(8v-wF=)@X%1o0qNK@CEK%#GuDYTkMd#%dG
P:J?"Hv!F;+_OQBKna2Dt:!j0(H"agR2@J?b6b@6H?<fXsDvxUk>jdHh&QW4W!PzXz9}d|_:eOds(7-$LrkNmBP0Sw6`g#c
`WBS5<8e]LpsH|&ov/SON,uCD*?yu+F}B.2uX^
a8d8p
2PyVu3njBmWe3/AnS#b#Wq
AQ]}D[788``SgAZ1CCR*Vvw+v{#(m>Dz,1pK@,dmO_u6`:*D-Im9E?nHCJNL8W#J<PB,y1D
ACOB0KRC-#j4uag)f7dB5cMO)cv)E<Y#=]p5P6OU<`ijQ=-$3kAHfw15<VL_At`dA;YEWh^_)47OV3<Dcy)[DVQ(ts
mm!c:`ljQF&SOd^J7f|:h/A0R=vfi?<!FWKZAWe9z([.Uc!<R#"jS@3rcmO-0F{(G6P"HYx+.I[`wcra{F,9u^=Pr*]p"N)py&bLaBMKUfLIu`{Y[/2^;v~O4$@I}&lm|GEjqEVAdCdoaHXhBL#FD1:syoe&_ol]k=q#A:tkU#N5u`|WyUT."ZNG_+cD)7/q}Kd*WQYf1
}gALpGE$gX0v*76JNf&"d&(sZz"<F56jI&giLyBrrZQ3{jTPkPMyrtkBpSaerUBR#htG][B..H$^CYnLpt8GATP0P;@Y{$-h**js.*):1"AC!Pdj"=:kFpgcO1N!2vw)F_TX;3Nl~(z^LRxtx6UeX25`VoesFXO!96~T9Qu5vhm=rizA(DYwe`=&Y&B!iZ^v|07y?8Y;;joq{@CXCyO3
e)#lJ9]0AT]*I~4Y:FOKc>(p9b]!18daq33e7-rk(f5JD~WL<xgR]6b=d$&.');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('$hk^CG>pG.%w`iG1}uQ4(NfaX/T]3_TC1_RYKAvNjWY"uj0SU(eqB0r9K_*MAkzy1c^
%Ih<wK|iCJ%[bc<K!rgB-^KZ]tOh1Ufi(cHZh$7"goUVmr?>Q366?F+$F.{G_bqXjg/h~?KKR`)bYG`p7QBc()QbAXqUyAnt0ve1[np
p]Ya2l=/)/<sIWU33DgWzMfjlTgb@Tw<..zTtWGy(bsZ@[rLv9=mvlo!!E<2GohM~7gnuWt[!ly#ntp-2V!p%A;u:c~;?@?8RaBe|In?T&#
Te+6_0&Q?A*d#Iq@xiMLSLiW-q-pW2%+m=TyVM0J,S
:`2uN:/z/i1]B%z)TgwIv3v2@PKuj2=1
nIsCYQT$`>#DS$lpQy@C~h:,hvUXi`cwpQ?XMpXNkTnINHI3h@-rMa+.m"`cXJ([EDJ_w(<3"J8gq;ZYe:nPclub32a?rG7@o%GF9e
#qy8d@Oxn3lL/f2*4k)*aS!=xqNo)sWjs]N+%q#73,@I7jF28^Rgf&v"8Fgt1VR^[vAhpKK<7@EyY
#w21vPEdqW4003tUobvWneu3Eqf&%JrXNaMR*)UBjf$3Fl#O4:/BGWa-m/EjpM1v1#-t+E"|a!4hj@MF<j(hp.j2RwvBp<3uLR#OX.GkVjiQ;G
&Fo6uCL:|c
RXI:8t.t=?gT?:G*qP,uWAWE_EUhmvCz&uK9sFJI`2#*FJfj&Fx$g6@Q*-uqSuAG]B:CXX-W>~E/p3*(vGvR5(ZE<l`vq`rLAAL{6.B<H.]cTR-+28qv"(g~]A+q;wrfC7<lpg;Wm34)(!5EHPfyO:#XPN/f08aXfHZd9uO5Wz]-v
L{FQ?
;@9WDe0x<Tj}9Ao+NsH0r%?bq
IAu8B)84fLf^V$*"hY)!fudN,,lK?"),Lb&K!u0TrAqKj?kR_`&UEz7R5zTd1bJSty:_FA*$3{j}>d>3s`&>!Rf;/U=c0-?!Sc-W$>#%iHR{/[V-HG>+6A^[wPpYvtG=3_>1@m3,Zd:hAe4#-*V=-7@;8:%Z=JW65T:"Y~`:e)"Q&6t9o8:/m]jO6Zb67t8xBK`lg79<s,fRKz?J+{aY4q8cPnK._W``Jc^<JCB0Ba^QbXk"UEeO$^Y;%qX)-#k#Hjw/Qz`jj.w_>4-Lg(e26!"gR+9S6L?(cEQ4pTRtDKo+u8"`-Aw-d9G
]
ys7}AVl_MWd?N]_pLWK3u
R$-gkCT|)F).qEPfoMb+Elbljw+koF8Ixk5-Dq@7qNb
N<*hf_cx3H
cHiB?uwk*(iW(dTm^6BG<tHr@]}+C]&f&aoUj9)r3[_^[&Rc3QCD}6=%5%_<vuXP&./tyYyiU6g(lcHYhy+VT[];zMw$`E(a%:X:<Mgn+F0<Men2/DbcKcWQ3DqiNTZR5V!F1;OF{;R^3g;,(,$^TG0JfBAH^d?"PDzFBfP;(Z_t,^0HHub,N`_FBkbSWAeY405RLeOqtNUj!*4Jsbn9Phr]+9d<W-pDC
<[XYX:?46=p#~xv9:3`&*y>KlAN7E/$WrR@?Er%#7]R>GIs_^j(YG`Rj2aZ]erar_DAX(?QQ]0_<Id7?BP~BBuIxy"PN
pJ_|ZS78EcunL/c2JPwnXTm1Sotr0
8jB0Q^mJr|,`?-g0h<_
M?=S6<[AvE0ae]a<!#R
rAboHr:Y:
K+j5^(4=$cTOt;T*`<U?AJMR*2uvB)Py*rJSrJ[K^x)RgCi))mk@>*E}GG%Vn6lRs|+qmn<a2?x%>;0?$Z9_V#?t_E5dw.=f/zL.Tt]M97`W]?XNFyb)hS!+FMyfWik#WTAXLeyVWwsv)X+WO6mD0,`&;b,7k"
q[UMsFpQ(-mbXnL2BU85(#rWS5<Zk*4[O01Ane%Gom[n3+QL;g,P&Sr4i/}9jFL@fUUY"R{x.<O64@,>$#960f)D>?[KB`19kBYn8+sp(8}`2(5;{wYe<L*(h/O2x,8WS:0C(xNBFC")+,X6|H6mYM]7ewg8ZoPg2nF4DHb5//aN~nqfWF;2LlK07L}^2?VVyMG+LD<2V[?EE:=?}l`UmG:/T;oa:/3IF*Df$Psm#c=Yhbp00!1mwkw[}ibnC)]LfV9]JWTyQ:tJ21:2Lr8b>t&gPrh]23fR+[
6!My4*W":$2E0OEh;AuYZm4<#/
:BRpIx#B<pJ[s1kw[F#!zdIW`Adf)jo`)s?[wJ
Q>H|5#Cw1gdzll;Sa`ioy:D{g)k-JY<#`*3A97*<<PZzT}^v^?Hz
!2GhT1s@8#_68T~VLL(tj"=a(sam,>tX8bkcNc:`p&GP1mD<&UKM7(Uwi?"JLI[Mis{$3aO:Q3gp]1B"U%^SYc<)q?]6S`
6=hC7/)/nx!j(!xxnl%-e
-q
=_0G6T0H4v.qz`v/</:/#a#H+MOJa.
b!=aBYaB7>_1(?Qjc,b+TKrP%;v2Lf)fjGohEA-E&pU;8}IyF-<gR<WIZUEE3P]k;p%fR#>Kon[Fvq&D_6K_#s8?hBt8h9Y,:9/[ST9]p(f!.g>{$U5"c8F]@OXM&H1n/bb&Gc)a^&A9XpkpjR,Mc`a^aS$y984
M`:1nzTwuzi
W3m&Hyv0v_!S,x:bO>Z<AoENgQ`<iP^J7#J)]hf{1TtU.uBYRr%ni&et:i%o:nPi.S?Q`QDW.5b4a26<.A4]/A)7Cx:@H".ih2W"A%_[NHWRnyVtT_IMmoh#7h6{?m(CW},m1DJbf_GGS&9]<BX&ocOhS-@{0`lBwLm[&XgUB?M<I<^5c~WSoyb+wh?,_fX_rXj_I@=Rw+@A7E]Nc)>_37,(@&jj=dT!&_Fi`%=oG+B~n0Fpp67{.S?squX5waq/lcM=gB-vr6wI2#p:XJTwt74cW{v3uyFC"EcoJ?`P;v2Oeok&9p@TRnO=<c&?/|X7uJ-61F:w@HXAZkn)I&OZhVKx7E,oE9p8T%!4nHT`l-/~S9jX
fl**IP_y*,va(yl9bqak86$L)gTFx:nj$n=%;R)A|+^4q%Z$ePwPyU$V4W1TS]m^;[5kk?"5[3R4aUYgZcV"!QaH&Bzk74=>:V6@V!oQ>vTr#@A
A1:K99uO5b/6nGzsafN=oWF%y%8ZeAfrWd
pGKqfX4q>.%IdSKIypHGM_gUh_milM&vT#wM>uumL:-QqE.J(%<Z9dX!HvYhXSP|)#m}S"Ef`/CBYbs_&%u{S*-P(tsoho!wi2FxRE1|.:*;DrB$9-yS,dTSBqG
^Z!^r!9P?7rI@Q%p-yE`fBT8W6[vp#_MmG(ZqY@DLv
NA~S<uG8GKeiV<U(U7+Z7aRbn=9DE9[UYG)4+l=Yo"bJ>pi14r+SIyK(Z!1FN
hBe/Id/%Yu,/1%Q2jE%PBw8RF@l#gD%6q<FKz4n)#Mjg7(5Jp&uPrQJqfi(W!$MOG>:4)9LgfEP/at?[l-D/*EOy38M!oh/I<8qUvGZ;t]y
E
SSH+O?ziSQleZ;X1Qlo,E&Nd8D/]{w!]=M&
?tdE7/<h&2^:V%|&8A53{A%r/+FfqmAcBf?5I
SqtT+;q
q^ZRc*lamSA><hBc_(PT)AkN_P2Qw)2DU09g}MI-N[G1kOoET3`,?uwpqTV:Y[FA/f=,00jBP]-ra*gap/k:}e)F4HV=`Oba|#ke
24BW)WRs*n>X)_?`RZZpt"CU%EV`vD-hFG?Hk@.<K7
uy1
E
z,,gLDK"H?9C`K^P"!VlQ:JTnX2e=#M[T<"=?.]!<W1AevYXJkD.5u2&Rly.0`WI(dp@!#UJMPzbd]ej0b1DTcoQ<)l6&U4l|YQDA&I1MFNj3F@-fMD9ycv"$In<?tr19PMC{1KC4jX6ce1%pITe>am,K.*Bw?MQpaJ=6=usW*@;@%
P4?.=Tlw8Q(O
Rt.SghF_#%Pqx/r0YZMJ7%G!;UNl$mk3p;HIyi,dYxFUsB`VYc,bM(
2[]?[G]HHNp/hjv/fbfG"T>L3F!7"TLs-F`FI^;+CPc2EjC4Z2o)Q]W8C8DQEzlnYe"ma^kjm!a4mMB|Z])P9M:Na?ApZ$tlm98|aUakUl>kx!A6m[;N>bA->%&30#C)>o4CDh4ukq2)j{#XC<-kmn9<p>w0elFum%e`2Y8a-Z9b[qgbG`=6t/DMb81:q(vo)2p$ojhtsK^qI[#I
eh,^EUK_BDXqIG4^Gs3-3olRKf*E1S{S]SdE%2,FD0TXigv9]/@Qcv-<h&yLETRPEB2J?l-*Vg?n;O#Vvvrp#7a);ouCNPx*t^g/7)ph{Ic`-rO7MxP_}vZ=h-`lK?yS!s?vQ]4BSK>j`khWFQqBmV|MT:&&8Ih>[q{WcUOer9$wK7[
h^7Z9is$1gUexFrBe:eD%F7I&3@tKikTn=N1w8cYyFFxy7rL]3KieeB,e@KTrgl0zHXXyb]y|0;Q%kE9]"ws)?q+&_B2J:6n#fF63;9K1"og7SyH)rb;^62bhKgpQ>A6X-Q]_7Hs~]j&Z.i/<`4`v/_Teaq+0aXrpr:m!]5m.;Qqc`2Z4mv(D(5i&4H=Upv*TfRE=HbDgHk8X@1gQEnc!z"^J@|@Xwb6vZm9rw^mS.gBnKuI<0xb6(WiRn-Qrh`0[WDA1^wk?5}m%5Mix;UD`8DnIYzG3P2Y5-xi,[GvOn+Ga&d#Y!.mE8f3z*?p!Brr4uC7uS.pusmSgXgk~^KT!8/`8[c`=H~b8#RkhO%X}Yy0;.|>&W&Z&Zog_sQH2!,kg]94Gh)/RNr,i
j$8%Shay>m/VoX;J7.h#0r~WDtwI9pBWKtxr]r2b-Imk*qnL-%2(Fe/J^B4Ak)uCp?g[9pzb+,=/atCKzT#bCc|VRagj*x.d6VlfvE#ok(eAOW%MroUm`^1pjUbsO)KLzgk%4l{w%H1RHpew/EyP-LbQ^@qM-gO5<6ZC3s"sc3]x>>CX*]rP_AUi<f,qXcL1]%VoPJ
*!c2x
o[nrOAyY`3DgFA?W<ly!?BjwRy<k).Bt954G>Ju^7EMeL_57m0A>V1F{lT^9jw%pyJvBjpvC.y*4xZr8D$
`cFs
2p-~ev3-xrWPUq^Q?Jq8:|$2s*@]%@ufV_?d1lC>T+"_kj7A&*w]
9i<k|BsWL$^b|NbY0>+^m7tFijl$`I#s&uQWML6TctRuj[j::QCXO82
3
V[H88!-
_ZIi.)h8qCzyR9~vfmxqOo!^0<1t/w-f]av,?1u`4V0.LgaY5B}`iBm/Df_O4DT,jh;t462DpEGSk4>&7";c"<Jg#f&EWb$W/@/r{_<@R/K,^&v3o:ln|q=`eciTy6hbzOTEOJ#qS`~0U*C(bUZp$G[cCqZ=)cO0)Js:c[hZKFTA:hlHd@2nk]X!cv[N6&&GS_dX46*seP{uE(Vj@*r2j33<RM7U@3sh%7_=O<UaUDoGPW#`+/][$^OB7]W01Z;=.0b[[mJegJKhhu/kpRA4of._O+I]]dP+"qQ]$#0
zd#PqIC,sMqFd0zq2nZFTTj`H`D5dV9gn2T`?A<JB/rT_+>)
X(lvZacO?Q[GE~gFDn>j0XLkR
i%v{mGplk0UFWJlw^/=@biAVcR^HstvIkT@f6CHWGlOdH1)ZGwntI`5bhwH.5tI`x,mT3z=:i3qBLEq}kqQYp$]^b[Db%~M1`*M:v|4=Z<A|W#</M$1jNZ>%%v;SEt/yJ4dk*`RV<QvU_IB#Rc6Iq!&th^C+1ur_<<f$QlLm>lTqB-alPe<
l>BnA,[":bKkXNhG]Sq3T"a~Mv<f
;X{`SIT4pX1CeKubp_S?b0v8/tBg:
$b8%<JO)I:&9inWX)(Sc5b1;!6tsV!Bw6L{^@F-Cf"
n/l8?%kB6SsQ-Pxi*erw?)RYAbj6J*?:h,H;GE&#b(7=G5#en$do8UC}c]7<t9SIsa3x1Dp0F@Nx%Ap;ht71[5jU*-Fc[clkOgg+caZ4Yw6u>vv
uLiqAwqmxcxcfJKl:<u%SJ4@Z:X[r>iaT~e6K$f.>vBdSUux")vT"5d[:~d
vjBYmXoUejY4ASv@/3y>GUaoALLxu8B/0IpZGT>WBm=5AAI+j/"uI<BUUYc,/~@P`oC8Jyfb?HLvLp&Dq*N3$X^t(<o`fZ=d$e,}ZES^w:g{q9I05bRlH8*(eGTb%j)hGy?)qeOt/2<ZIXC/24wbE7CZ_#QIrm`)(Nn=&@.cTmh<_fH@Y|A%l&B?5$1;EN!o4<.w/LpC_>T?L"6Jh^a;s`okT1GiQABpBcv$d}!W&sgW"^+MSui!Xd;NHirnN*2m7MRhX9sYJ`]fs}8K`PT4LMc;Ta&uZ]jcs|X{`/XCuwr-XcdCrf4R(9hZW"8g,:gY_6u%R/wbSt"joWs"5<6$.xdD!]Lq&";]WETRPxUh@baAqmRq=R("c5TT6.Xis?>?`~EqjtU<tEN{V0?:cYP1jF;bw~`y*^u_xS#Qn~_^nAKXXg?El}iHp7uj[ct?CY+XHCvMCC+bslaLv#!(n?`*6DRy)oZIlKH5n_nZb)PLExr^QLBysn_o5.A{`_Iv@7YM7+/k10?K8V:NFj7nhdy6spyWG$Pv9x%<flLw=4gzI5n
L{bJqdxUQ
JN!0apTftMc5JX]xWnrTtS
N]X#`
)>APMphR[:DYX]vOKI+
Y<gtKYwUt<=jc%y"f2QTuw6&l&IhdT-y<c_%|yrG~x{,~`wnuIg9yi_.w+nHCohQR(z>tHZ3998mqp39=yu!>7DDTDmEZS4vz7S`7f&&,0^U$)b99_wB^,}Z;u*c$_JxPxuQ*^e`k=o-Z`jv{7UN?efmw6+Pbnamw$;_~AUL-odk8i/T-vX
j9>n}`+$>@:M=H/Th;

9<XM,qq7el.at^;G|e{Vqt,H7jZmjKZG);0.I$?;#J87_mgUxXYr_rZR)fAP`18=IOWNs-4^#u!xN!J%;
yLnm0C
:mOSR-S)[,!NdE)Tx"D#",M,gP5d0vH[)aa05H$"0hk1&76qjwsf(s$Xj~#qA!*5;:X$e?@RghWXOp=L:G[ytAFxE@Eb
{Zb5I(|r%PB^,UeX
&IT}5CUPeolma%ANxpIMEL8w7~N+>hKp)B/u?O)3g-eA>_%dxrJQu,
ms</x[]&3JKeUG}Z|:Gc.h#js9:&^
8#^Avx}#~()
}S~L.A:
!<A;<)pN+IL5!apgqCl1,Z*377TG#g5V^TK@xX,Bx=Bd{r&QV+I2~;CW8SG+yH3"0M+x?)/xW%?v<a,YImr/%er3FK*gx)&%j0lmsr@T*nwq-f(mu%3T28y2*`cJePmi4;XE#2s;-"-C2yS@,?+.i*{VbO#*LFul^XNkFu=$|uttBXel&;Z+7>8WNAGB{GS*C
_cIFL_5ybPUJP*5<
5%rb]EXJdT
8xGB6=yQ{e@L4voV$EHGZ`a`4K_.u78W,QMUmA,.S8xNAeg>ptERh9I[cX"kQcV=hg)nd`SvfD?Fr].PX;9cR#|]F/TD0@ZjS3O_vmM
&_-A(OLq:0~Ol
pqug1^4n0nop*I7Py&}]^@pxzJbVxRzUSin)M:9hCY<O/FcpU9><"MNGpg(M`czTjJ1W!h+YK[H_OVS
Y>91_@!O=I8GC"prGYy/1vN7iIR?10<UmjW
9k~S>R0<&Or6I-I*h_%gz"D5
RLR2A8;nz"e4
Imm3m=>$0D&Yu*xX$Sq*GUK?[?Lg_)v/SXOiP&eGGF3.((:?JrJl,(_>lqtW},zfRpC_|bU&w9xbf$p?o7Fj]jtNFcC+P+G^8tUFp"q
OFHeV2xf?0]@?lz=!KPJPEw#w*47zHR]uEh5~>f?<&oV8jSU(r[G
L2qdG
jKMqX2^^tQkv!:X?>;FMF2I$@D[+ep)6jEVI`?o@pE[1r9`!MVsdFhbOXFwH6X%JC>,ULC)*deC
E_/daI&ZY_0yW^YdJ58!++uu>1pQgIy{DW6NL#qV&0bs:>>!2GZ/^mP[`MFu(BGD$bo}c3*z*v$T;>H2e[
;Ju"S
5`t,{y2H@kH)"Uvk<3dr
>KF<8e];*.c%<?#q)ypHBwoK@&3ajGO{
GHC.k!:T3!FqPtZe
>TO(f.`AN#`Pa^h"]7ajihTXl]OjN5a}E;h2eW44Hx+_GE"2!&A-@dV_/Bf/iq+-Sy1CoQ7D*Qj%Up@ejEZ2K
l)(l4MAzBi
j/5(syld$4}KHj}Ma+wb9*kiV>QqVqi>3go^zBOG_-yx8#!C<-
A2@nl"8NF|GgZSIu#LV>V;4cUH;?pQ86:OmVajbyov/*q,j8MyEFn
GVOsD9cy0j0Tcyrbr4N5dsp:j_!UYel&/S$b5WokqGFK"/-hl!8Ldn$MC7J."m9Le9m;Vt#pCA)??0ZXe|)2Mk](TFk2)8T;ovN]<a
_UZCy0BblQJfb=F`yI9qOd&F+kX1$jD7H99n]RDV+t*7DG:QGJ*w:w/,HIyoNOMQ:gB@YCX&4X{(S]1^-]LLe167)2CnVX&QnS5n`sHG!`I
]QUpOF$lA6Q4ls>U598c2e3S*q
A5=Ig-+rG-1PA}huA|niaY5?V#y,+Q?f)#xa1^sm6!7B:!`@mQ
xpK%*n95Yn"Ag.&Na-(+1bdB|FQl`FS_^$CJ+UT7XtM3.ZnNrSVt;%dv<%lPz-i.>:IyF0~BKOn-
9Tdy3mssrg>IB_2{)~1y:na+hpFESUnXQQU9N~Bg;yk22mLn*R@>x=%e,!74`rX5P%MEH"rLGN)OuM]AUiewq8QC/aRNx6>XbXxzr0h(DxeT/~XsM{;/g}jqU55DZz97u6xy&Dy$T24S(51)GVOw>9dRk%^n&dI4x3=xd_x>Ul@lZuppa94fRMX2>4A88]-nAH1@DMs.8qFsw"3!1ws$Q1A9aH/G1kgF2u0fWC(z;0o{Z6C/]3BDR[*zX.[s/j;^?{1Z6oiujHAxxyV2j;jv1.kYWTt,EWAZRvtS?.rp$wSC>5=
<$&Z:^#Z!`U>G8rUHw[v<&kWqK
f>oAd%Lez]QC)Ve3vfjwqT4Wd#]x?=uWl&pB*Y)T7wjfJepRn7-A^t*j=1G$>.pOmZT%3RrEu0-D9yEgbZ`a,v~25K!R]$P15urT):jd+2fNhy2&BQ3S.N4mK^0`X4SZ?!WgjPcSB1<j(=9bN[=h2+:8vroDt_J%26Oh?!J>t>/A9G1:nbLf#0vf2HcFd6@
6dUqT/wpz2*-EZdKz
<a0hAhFiO[!u`?qTDf9svk7RN:%c|J}1P><>"j,)tGT9
Fqk0;5eTTjA60(*V@TD[C4E9I)rIZ?_M;/J?r-$x%@`4pgsimW2Aqn7D2-5H?zbk@HXW/!8t%X:<sZ>2i;l:m`VS
fI~dUI).p>/9a<H[Znx1~<[AzP1x
NH.sb5($nK+?-zar6r
SDSL|"ii068e^WB>e
z;z2FV~Z0oxIEuem"x~H!J|
ivU,ZXQT]R0oW`;w"]wRlBdRTqdJLL[t0/M_ek(Z4??FMx<kasXI;s0f>M.2h.dpBv]MxFAx@Xjo`iyKXR+Iu5F/d7&nkxN[DmW%c*[r08OuNf+WQbNRR?l05!$bk>vir&M&X);G!Rn,YN69wslXrbT;qCJo32K+erl:((+e0VBEd(Rx!rQ?4O
qQ97,Lz#6BZi%<"ag`dQ(7wqgcct6b7>PtR+>>;xhcT8`P1:]p
*;o1$c.)HcZJ9!v42/@)10sZc
PY8RHjNt?rh[/S)l,ZI<PUMFd
LtE-p_6ya#E2gxg+3>saCQHc@Z4fIJcI|WPV.4s?_^&Ul3IxH2u]QQ/Hxwu/edc`0h+8)Zz"=S.5kf"V4gRYe]]>[o$/PB>y"]s1=pREJx<E)(7oc%xZRY$Z^ew9J=m#8[2dZ,_>Wi|c0TTS"4jVHQAY]qc[$(K;8
M>)DL]@gpY^A=_zv-*pvCK{612^,!HvRq!}Xhc5cyi5
4[N(~$mqtV=?fJ+Dl-86(+/U)?We"@lM3B,9_aSQkZT;:gcW:j[QzW5=,GT/AH^`yrRgRP>)qhk<%(|C</?
[-Qi.vINxn&lIOdLe%
O5n-;SbL6k/:
*cHGRoXW.%0Iz,qa%&Z`BbxFi?Z]}Srx5r[A2??4?C)OMcvk]ddeJQGg!1>AOZ]m2?*_3Dm[B%tER`"mz"m@)d#tJwZJQ7"&V;K4UydM`;@?P"BW*+l/&)[%<Xxi[8x<8Yr](oWN79v<VmiNC>"<6t0p:AH=4xG,D8ex=a#]+W3?Aj?he56_T>a61ZhijEay:$D
.!3#$b]4MtgjDAJ[Q?QB<M{;Kgb`tO6Sro.*HxY1Es:ntj`x+viWxBXR.ctgD]cZL%z2=_NLp.32))InAat4BOdl^5,osj8-<ZXE?c%y9V3YI^e$24ytiH6m9sJ@D2+mp*ZfH[#2#$HEw7l$PYplG_5"l;upA135z&TL]V?2`qQVq33p(WtvX^8FokSO%7.Ul6a?[M"%p]Bc^AVLiB0P6gNF0W#oZK2-xJps3&CMyu@loTF1"K/[c[|iRv8%cX3@.tlIf/U!+X&[Sf?JY2abHb:wd38p$)}3u$5R^mzc2DZ&67WX8#"QP%zqX+u<|$_Wc)$2ymxouIio
(OS[,@^Biw3lPdvAZtCZk,Ci$p8SXNp6;p?PGJ@nl/De0NPqUu5(@2l6>g!QI^59sS:Gvs^iEAUu^QB7(w]6AQB]9^_20oH]*,2Q%&pAjt
/Ih5$:guK:]j82JjmG6JUNz?jbuZ!B@@&yd?mR9<esW.cYzASq$3x,5Zfx8H{RNb|6)3J,j<qVN("x+Uq=z-,HDDCc=QM*s0uZb<^Fz/PDc+fk/uVKZmxZTnN0p]r%|0.^+?:<%>{,[W44*)C9;=z4<T*%">mEz3Xj--PHV)DA?)z`Iq+NS>pSqa.]l?w%%qk2WDj2g2zR_6]S5MU[H/}kT*qAGUQFguVa(x-YVRQ8-[%GItgM7;m4$Q@#~RXnE)<JLOE3zRRM.N*%^*@X!HG`;G&)jk|Vo=qt60(R_m=vQ(@aYu+Q<JnFr[bJbgWf&<(lC]}1X^hYT7`@sQ%4?M5i<NwqZb/<3E)L)VK`rUu`).5J=%X[KM5%IU{xiV+r}(?H]lOC
M(,(X>*z^AfXeL%F](Xj*=<Cm9TSpvEQTT.n={SaT`r=l[vq0ulYg(D;7Loc/llZnnJF>i*W$M1!WQw5pI7E>s.2]Z*LLv[D=jWZyb85F(uNA)ND)0w`_s^x]B
ul$XTF}3u^<>{r[*n>TKNl+p#2
)@.~kT@C,/43
qhQ4UZgT`A"l`N]jYQ|29x:>`OAt8I411=77yupbOASB{*s,BAAgX
zh1/{]!pz
R&(rI&Z(khb`&nA?J;&mTi"yDa87Sc3!/8{705@4K0B1-U9T_aDXf>a4"!<4)bL$Zg?Nit<`(c$wVUIemOgon0"%_jKl;ei_MO6YMymZ6F+Rz*$8V^fcW]I!{!h>*]q*(<(>M=%y<AG`P
KRY$=,
J:@E`Fu*9>6,5w<?]!5j8}d5A^LX774PD_eK>~V$O2(?eV[HFHXN.p*4<#:u/
%yoCT>mNW>
X_O1dH0oW:V&+^+v.Sze]a"4.ot(S;.dm0FJ*xTmhD"GKly)J^d+e2<fN]&yz-h3$f6I]KSnr8rLDjs7.bPr84igf)/j`a~&Su*dy1L29>%Q$;)x9!l.@K~`"I%fs,br^4Ce^qj]N9I?i!V?!Gm$~Iw7g]4M>nN)c+$
F!#[3_fjOWIRi]Z-D*vX*jMVff/SA%PQOBq_b7-IMh42zWV*T0bxiDlbaUzew.]Jlz&
T>wnvBRw%adh)w4[8s+OIEpl;-@k*+#gX3z!_
,)U9e+#NRnh5?W9cNH,G39lW)>EP{J>f4ptm!>S#)U?<nZzQ
mIH]aQdEqhEKO`No^onLR5*~x4<xrYlDo:0:mjAtM`T*qt?.[!<G_VKpHvjH!/>hI
c}s-^eM.h(v{2HfUU.OQ@[1e]|JSy$UgudsjTv2]5N`o(SUzB~&{GPf(=!6te7?}ZrgeeEby@6]J;GB=c/(KTc#0Y_cxu*
5INo!]%v;ko;Uf-tN3h<m_l
m?N9O&-_a]PrW;~cnqGH0]Skb!GI9Mu@9+syEG7RRZ"MJ4%WH9;LwmQq"lQ?Mkm3e!6:j
4HBw+DNPrH:dLduKxc-6+"7n~azX$cwN"Tl70z&:Ni608hca};$QBy7f,aHKrkes$vaiW[&mile%>MXo:$T
R+{L[*q@3u`SbiRCAG>ODlGJ)cKRaz(C#dJiN;}Boy9yusA*Fy2l>yenzK|A
Hp+jc,hI"^:yy6pEz#kjw0@:HjIV_o`ejSGK$gc&3yO<AOw+h-F=5-lwkOE./E)bz!`xyf>qROK|1Ba>eaEL)q"?S91Vp0i^X{:Wq&_nwXtUXCym$gYevddcERph$"n#OPL{!%s>/}f)1&@FX^tt"Tbc^9C0j}c.[gb4<d47U8B9e2K4.|E:E8/>0Sa|JREBqJcL=rRa#2`I_v"W,6t!I?,VElFYk*Uq:PH
_awnj4byS}aJboFY`(T]m#_Ip&!5loe3F5*hg)-SnW5n7,EQ3B1CbxV!IT+Yxy>6=[NzYHjEn/a,w~"rsc9Qff12%W>)KjRQOhhVO#"1/a+A;h-T*86a>|^Dpyupxs/<SckyH9ox7V_.[@lKX_G.uc.!rnp-1q%~D8p%k|6{&4Q|rz,FI<XlI.lKE;A
hmy{J`oLOY_{qGm*+RqLr0p6`8nlt%rTh,j!XpN#EQB`Bh1]e4B7)cF{bZBQ0,lmL.TL04*0I$J$F]LE0zcH@;/OM,:f[G[Ayk/OUZRse]b=a
@^u8(iF9oRZ{wp)gmtg1Lehz?XC!rNA:c3xMsWn$8tu1%XHg`Zf:MnjOQ8bUCca$o$xNPZ+hyFLPgLJB)9[%%9Q}&8X7q*Y@$Rt-(*JunBM9!HsfFyv.yt;JJuh"tD,vc$`bo&/}c*
E1Qt{xclFcnw!j8Z0/dQtEZ
`V%mREjd!rU`lpFn|w/y?>,HPF*l~O7Fil[6Qz$sH_Wt2w0j};JtF+Zt5naLGyNPZy`o$WA[(0[RmZp3N>pk1)Fu(0s_}.ApC_qAM
:D[KP"SWEa?<uLgWDMy4ij5I&KJBb`Hap)X>1nayN+k/$Z=Z?xGRK7epDmfmF$[qnm~$gHsI)WMl+Buc/1(t]C3@r!Od`wKP[YSQ8rP!C9`juKS,ww_io3P:J/}1M>Qg(G%6QD4W*z&7rJlVQ=<Gy#fXk:5c-5hs{N
t;WgK?3k+&$
mCr7a5tn.*!!6?Dhc:y%G~soyCOZ(&sEsL%,T3xFx`2vUKYSt|y+ye/-9
UM=e,R[2<l0T=~1@r;!<tUsgZAS*
^Jq6p1+kW3Rn,l[9-UIHWg?F
HSX86Xv}D|77G7UUZ{vK$9ccgvXWnuFF<bbS&p5Yd)uv4fMrMzuNtBK:V75H]R[/1)EJjr+Va}ystEy[5_
=]Wm@vVL0xR+`mJ+BB]n}kV7NQ(2(0S6B
EyNXJdfn,&I)8gx"yfYZpjI3T2FD)I]`_*0f$=(;qNSMj6e(=Vd<dX$`,0k&:@b9/*HAED&dKe#Y~aML=$sxDyn*{tTy>vocf,Y1`2:j~T]$aB3n$hMa:T(yiT;/EXUdb`oiUILSuy_e#S>K+tEdpJ"[r-0B@"27w#N2cWLDYfvZXjSydx
.Y#/SAS8RCG%=[0Fx/ZGR:Mdyd:YO4*(Gjo<?fqNh.MN#7AtH2[Fl[$O]nHCFqDu!Si0l(jf!WW=<d]/rc45;))g2]/|<ky]69Xz:I3!0c5EVKEd.+SXK|q!;dTiMO)w3BQuHfU%D6n-a_)E)CLLNGD>:p_O_DG,cTi*q&c)K/bKZk?BWTdt%;PccuBo"U#nKG++l{wEUA-i2W8AsRn:I5`g=+o[e)-L&()/N^_m4Cp(5X"qv;_|Xj85To7JbeH/"7937?YT9riGO1tb,fogEu1tJ[
^-KS2c
d^M[7)^&ip*:LnP?ntvysQ]om~8"2D9]ciVK>,GQXBGhc#[@X
4L$7M@tO2uRj=++
Ve`SbxGNy9:%U
_`,1w{C@X@B=yr>6]k$;)hH}>arrt38iWxfW.(B3v7LeTQLb4B%^4"6M4)l&l6tEsr^#lW=">uY(#@w0,pCy/|gQ0,fMlGPk7jw6m!$)bzD4igx(iDc0NX9<bX*B6?X
`
J!+w.D50:uu+yMAXSS+eBj7jJ7o$A6^KnCK7V{$RfuN$fIh0c"?Jyt$/WEI]D-CRR!`;FSA":J!.$bR6K$TK4zt_vG3$S`R?aGZ3l6T?$Ky_7~,,Nn%)18cLPQn75mEcW>NYMi
3&}g*M-JHDc1]szcDvXj=VL[dh>XE-{GZZ`m)V:/R,*[:y$7arbY.4Sc=M~5y5qUggx=b+CSMjsp;(=JlAxvZx*KbUbKYrvpE>]*n,YVy5|T9o6CLeE53th8/3G6w?Ey3fDo.V{b8Nn]L;aZ&(bCh
xLy$;Xn$K
GV?R%LjouMNoH<8@^xw"TyFm58cI*8=ZaENB-,]idi9usOE<@9%6yg*[DQFslyOK&6>nev[.q([iEBG0ZvO;u_wk)k<$f/L-l&d
.JB^hF3Wo5q/JDVVnU/Zd#b!sJxA]rQ3[k>nhJLlTrN;@
BZ/WQv>B)V%y(LgRTpJE[<:I<:/Bc7~IJ3s":*~bMGfiVR~V^Aph.2S9goZZjGt

MMa8T9Bj7(lR8T%P43pZCqX5,b2pKr`oX/fUCQP6Sz[$"{]<YoNcR^R1*mbwtkeKi`WM#o%#aE0F-,CATNMv6uQ/oIn%S=fmTh2sMt-B.9ps(6+lu#qJKWCu9ofKam37ECK_sJOoOo]u=10Q=Vfw-vJh,4Oq&vaC.^c=T2tr/L5Sh{T-
2y-^lT(f^16S|rOeL<@C70V>s<7[I2n2>e>
y"HHfjmqPSWuj8y!-;F45^;s%l>:=h6AINEHf7=53&7k<E=5:akV54^
nDr-vN{&B2,v([9J$k)Z33XBJ&2I!>V>NF0O*:x#G1?0Q8HQWA.8qs(Vx-(%>]7V~2L[7-[+7392k`iSp&LF=Nj0(w:oHSrepE=bg#{<0>="^.}"u5[;r2as3*&-vWrAswV8`w@jn+=dyK=*+>PQg!Zmn1ws-Te4ld9fT6!xe-2s]$5yn!~lE_(*ComUi,]V#hWk[ow&|GOu+i`vF"apjd$:;-D^j=XnE!,bIx)
%wJ(
v_yA@IX*Vf@jttsw)J4s3}i-fA:5$
JxnbIB?tp><F]q5;Prcj[*w4jg
oFd&7
6m8_ui~RvFInV6>%k,u!wEMqR#r5:I"^3DWoyNb7jjpUHNxa/;j
{,N56r
SFUr&K=rLGD?d_?>iuFA6-OG/&SKX=s8V*_(l9b7uhssm]+6SXc{o!rwiwrAKL`rnrXjDEu|7z,l-C2W0Fa(O%;PH])Meegv_PP6N34Q0u0|Zr^jld-ol%IZbS+z:5k"$|cHQAfsQeR}?JMBfapxrGSnG5r&Z9VIL5#uuSW|DR.?8l^8QV56Ro%1IhfW)mf~S#Yfm9Y!vH6<mRfDm8MHx#_o&*C]jde4s]4^hV_wsQyQ-HcX:ilZE(IaP=13b<$AT?
Z0B
RbhA+v.Y^&~]%m7eQQVFb)j=r&HH<y>AFsq?&=a9^KlKVLI-Lt7Z;i@sNSPxc^l2/Y)UY^+R[w5^X5@F7obLorV%KKk%ZmJF(OF65IFye+~#ac
J==oh=Tv/b5l=`_$1[0[TPh3a:yA8.6;#9KKY6Eo`1drWK,-n]KsS#j3kEJ2U0+rc3kR3&f8h+C|FI8/5;u?bB*qqMj`M$USt1(h]HX$s/(~HwKG=kFP?)PLhgDs+AONFP)*3HOgN,I;9*?Mmym^f$0L(;:Ke]Ey5
ivbkQ{v+K>(=D6P<PPGqHFV2[u;+GPrC2Fn_3u,TFXSlBD:J7|2#@h)%X(^l9j[vCem(QK]>q$"T);ny25"?)rR`
.,-y2sWfpts?mUFde0rjB3TFc_MT9I,9cx`)8is6t-m+cs.o:>z"}A5]|jQ
ec^1]x2HPGPF?#d`O*7lJmq&f?gEqGoy8d@ycr?V]$D;
4U-ww%0)sxGYwX3c3d@4V,t*
W2GU`JumwNB5
p<>URfK,_%lyF$J@.rH$be2FTm`{]
Z-ya/RA-:uNUO5f(JhX@swU3*kE{V8a`!(uL>t7_h^`1jS4YsC]kt.ag66OU_j.o+u+aSX0p;$-H;UNu$#B{nwZPIa/@F1UTJ-l,4dEaDh%Dt<S[MI@2eTmoLT4@qwjuA$V
dF-ad/hi,<<47#9{7$ULkKQ{YzAbFQpd`ZhO-GXeos-(J_y!CqJ.g/,}tGCb./l+t:%s/e.jr`s=:kyYhPg+,y&1ndINa{2Fi*7$U1js4o$Yw`AXiQ2]3J!MtWCAz$M2a1c4bzDr_~^]G^Gab<[Y74<b;LfYX.[~Y:sUp_5S8~kS6_;3p`.hiTgzo@!j(UqZY]N(ePj23[7_/cbAMrvyTi^k$u?f-:Y|vAp5r,J>h{?BC.XsMZGn++SvQ3$dZ)9BI1p;"tL"Y=/O/v/6!|PQ&Y6OJPKH3.^CFLImPkkk1{qXaVd/UDEH9RSojG]
/%-*`"Bn3;&}mvS^SV(@a[f*vp3F#8%%?M=HA^JKvx+N;w!Xl9dcj?1FO*x,DO_[,,WhdhPAc;M_Om0V!%NymfeOVy=%$[c8W.(t6]_W!$(XEbg.0Psw&FppP{M#<CP}fP7{#s=Ktik]-Fxo4:)~s
-LXM$$:L$Sa2xvQK.C;+<H/A/+=YTH4}`#eSYCC`,yh^P;gjK@_,p6!/8cHzoVYS0e[&+(RZv/NR;DQZm
i9ElhW7r&`$JKRUe@k$J.i=gbl[h
4V"(Hw~,H+Z!qb2BE4i+e$!lLpROt#T*{pB!C0A*CrJE.$`C.ErTz_j0I`E7OaMB8O|C-8_^d[;8|;la?A%R@Jrif?5WbhT8!e^IeS#rTZ0SG?W^b)YSAkrI51_bfR2_LTXclVYDRKv.V@;o,9F5g&-`4!P@DS}=J<&SDiK82;!POuw.:o7!?mo!SE.t0uk5R+iw9TiI~#k)G"[7Q,b:!+cf3XmV02}l=.dJbz#tjhv>4ah82o*uwffD9X-WGBbWJq0v(lsx+*AyU*06MLxl
Ed6o=[c~"~eg(l9Z8cYdnR-q.sM
.]Wz
wd
hbq3IsU7)DOjy}7qWQW_5@/L!aoi$b^j<o//KRe)y`I-EM1$&3&jChn!7X5pymMC;Ix8URb[As9q2Z#7%h+[
[wfg:]^ArMqR_pYwn(&wTDvePs
gS6!Pp;o*[*_2XZQ2k!*hSVVojoA%j`?&H7q3Fn{pJ%H;SZ6.J.,j-C[LC5JL>#Vacm/5;m>AS9&/civU(8#WOV=,^0>,mY%3>t_D?JysxtS.5cq[@@24~fX0E`"k1&#?+B*W"KT>iAOExtdfNw<diR-7iJcBe?B^ew@=HG8a*"tsSPhuJgGP^&9N%?&70iRdGvodgp}bSbRvH>"/`Y[5EW$9nV|kH2jbW]/oio(QAZqj=hXshoBNB2&j[b}Rr-dAL<kAJDF!SGzCi7][cv}5`sJ>Z.2M{yE_e[t?9l$[[p9.C5*b"z$1;#Xd#LGL|MG*7xeA;;Z`o7#:H;h4^qGnI5?tVw5v!65GYBM(2v1fsy/G0#|yRwuJXvi7nV=yw:}d<K*jshz
6%#0l8`<n)BQ{W9#G
/[ZcD`+9lJ%dhF$S]y^wOK4x"8/z(/$mEo$n^l/
.jvZa(vV/9w`$jt.DYnWHp@tIXuX]wX?]8:k=bIJ
.&SL:zbOm>F3sQ:B6Z>rwU7PS"5z;$,~2Hxro6lTn7b`UM=1namJE_-H
7Le2|.FK*HQ[jH!H}Bs-xvcLMXCgR3x-oSSjSWxtOt]E(a3/5K@HFCc/q]",w$p!-xxZ9-Sh2h&u8y&L&ogTS*]25DI$FMYM`Zf#(qJq~u;_@U"hF#J5;Gy;uNT6<$S*:TD7_dHGeV|/4pHEpc>hdf.jHB3PY]]wpxee*xZXR*x@u0!E*OpiFsKO=%E_!2jps9O[P76^PRqo"i1)v[MPLxRGtZv5G3DfnZP?8CTx_w]M9K3XlA<(1HW=Zu@&F^n;~Gcvr1NL|(5]W=F3:
5G0pYl7!+noy&n8-`g4?Ty}(xigLzEAx_k#(43w4+7>rIS`K-aG^YkX`,pg9vU/e;"LOg`F+Hb!+{7h#E6&-dE8xRdb^|Y>E[
ln2[G8m$2!*A#H-oI6bD2n?uRw80%rXwUEaY[U)J<7fOpU1dap8,K%p7A/)smKKC]BV2!:wHpy/H~Hw-qMY.~Cb_DUZ$q6:P%lg:*EzEY.)6i(+3#cahQ(=p.7MT;W`*RL`P:IC8mUBk
gA$lM{!8$Ee^+r2kd;bBG;y+]skAeSrI$CD48dWC9`s|yyTWpIUDq|Pql?%JGnt~7JJ#@:jRTkO*W=HMT?cB.B/_sj;{]kDmcUHX>csa;lG}CA0qB&&oib/a"IqOqX;r%I#^/)H|Ef-Y2h=up8H"ZjKS,6C8wInO.S33Lt8"+)RQYB-5sZKd(eqQ@W*+Ot.U8vSWt=<!XS++2Jok(KnH+Uut6&RD9>QA0:HFib[IZe4X@syi6s@_d+A7p$V<6h#_c0H!0?QembXaW.aJ<nm^LW,,^EyU5q3#q/i/m|6mid"O*4B?r?lsO*Rs8l
H[j6T&)=L;";VAB6[wD4b4tJ7!c+Y@M@}8xwkC*AZ04KsSzbrZWN3J~I8SU1;V=i?ejR$PGW=gG-dc7$7"z)S?YRfMm_J7Z<hlXjXZ!+
HzkLc3*hEx:SAd<RCHZvR3R)
t1/9Q_">n/#5ag^Zr=}WDIhC6objcdJ2OtseanYD.oX<)UH"<P1IL([0Qg!kb?)Ep?1;p/K0=*c_P/Q3F49];.>fP$*YahM$%DH4Pq7
j5}<<[oOP[[u0M6+nOV:4LItZj+UIRSVe:?1p^7KUS!$h>3m|(uh,/C)XgDhuY?ksX(gH"k6k*[.tunK&$"n8:;.dLzD9J$U`?$1Z!,I"hG?x7/Znh|?<U9W48IIK4+HhSL
2:XwXyxNMgPvw/h6P@NiWbEJ:l0]`DqFugN[TX3IUy|79PYLMG/Kpdc!7C[wJcSKIw+*
iQO<?gS@gV?O4dROGv_W$yn{]wCx9@313~:">}x
jIJFu$ed=#m&.~Rutq:vnu@#Ey_5YjGQi2m
<&Wd?]V[gGRlQ98O-HK
j3`?r5#LrY$z05HVLL71]b;$%f#bc#da%ULJMw^NmM">^y,oH^F$dEg7J-%$FFPbl03mCtWr+mvSLHWG>.B"]v`N]Mexc{Rb^v@97`Ki)kyNe8"1py)eeAOU2{Z<D&x[&SQ
WEARu)YYQKa
T4]OBa6-^#ha1Le#oQd~`TxXuR=pEh0LVnmT1Ki8,05nW$st%VlfcM2O]@
MD^M/@!_d(IEYs}r_:6(nef@m+B,]0xnR2>Xo+20v`E1T1Na(,:p&[C*QBIM6s(kd
PY[pc>^px^kc/N<fWPBP%CE)V?BVxb,2fkVArgmnhCDte8[Mi
qL,/iNJIe4$$"+Ti!+K;I-,seZ#^U:A*o-,F<Io`f0mPSkXWKL*`%<e9mA+8@L>9,w*B4/D@W>zg~Sv!EBUj5q5$rU0D9B/i?>KX+K8kq%"<oNS+:(Lpkh"fhv6x#RNpyKV?@KhwVDd7($cCO<!F=,CTO$cfcR!fC0fdBpGApK[eD98r4<Xnh^*ej;?J~%#j5t^fhdh`]O@8k),Rkg.+n0f>bWQ_JN1P{q/8bhruN&B$FAv"p1NQ/5*Aw;OL,>L4%&uP7Il#9]!x~
"5a9j!1.GY9K7ET-c=sIL1Pk?wRm.HjO@VW2S+>E==ep<-Ie,O/.0e(-yrn0tR)*oou>v&8CZ[ufJH=@zNM](;Z$2&,bZgB)y@!3><U0*pQ2Wf@eTN,:`JYH=;Sp("7VCEA<7SHvZK[-trV3)GWhDbk019Kdwq.YYmFCChW9I=(<bZoAtx(r^v]Vy6#x))?dB^G8kgI7[!yi.)a6=@-)fN}h1xExJ6kZsZ!Ki:kgO,%a:Cj)LpGOEZ="p58EiTQyK@R,}X!(5/jXh!L6qanSR`I`fALVE70h`W
N8:b%=@kG&Cs2!D(lQJ))??<elHK*_:B,n;Vnpm.B7FQ%KX$l]X^PJ-Lo_B@;Xa%U.2k0xpIG8:?;:ba2~!}:/9]Y/Dh/q7m
Y;kgB%J"I[}+{cmpEB3>aDAWbvJCO_@>*0AUekq2pbo1q2)0R*zVV($)ONo-$sO#
XxL(OJB$xi+fQt!~*kQ5Pp>rgA!&Svkvr$BvHF!9H6@e?z4[%GJ[>LX,PpfUi{yKLwBLSt
x
g4!wcHDQW><I!6:K?!8XU=K.D+MGO
b(=9,i&5etk],^akO&5:2eRv)[~f%]WooJMFA=8IBsO(Gv/5Zl`b!jBqR:ziWV8
U+{77>SYa
EZy;}=1<X*Zik7:E4?A+*eUg`I%6tAE5d3v@8(>`jEPMwe#y^h[<MwM8;RNP2Q4jBoj3+#8RucWlGa9,v/QS>+Yj+0[ot/tI5VHkUM"nlg<y3I",GgTfC6;>E#dkd[xIIkIb?[;K^m`MlmCQgahyT0w9.)6Ym!p2h]kIj%72%C7p3btd=V59IDo*2.N>bq5`O/)vH_q:+?O`2yR
EFw.@*h]]3b=$#ULQ=1hV.Q9^7#x4!$YH_,Oqo9()>K_8&epj7Kk[[_]LBUBGr^X<0fMo2<uf@Zl*6oRrlLX?r+V^%!>C$<o}
KSU2mu1/n_B7f=YJ;@r$a<m!(wRQgy?MUBV.zL)$RoSnuYZRkd*e^S6!GIJK^nLey[aKOK{Wi80?zIv([-L*{Rb;%v^yZa;%U*Ze!L*mA@prLo_y`=lkC@v"gyy^mY<M~
mesKLOMR$63-:yMS7.7MMr`qbPzjY^>k)>ngd<X%h1vO0r7%F6EMrD
[<
C$4^`Qz%J`MnK%{k>&oc{C?E$j9z(vjC+?P;uL=OYc{IqvRL*jnw^vO&D_^lJ)V[V[uRl"y=|f6M`>k&$%o5Nm;B|9L7-qu-WNjwV%u[0]{j7LrIwKi85k
U~n:^="K%R(loUd5rYls$xR@K1%,G3!qCQlzjj05z)oJcf)Phdn@37
:BHu$?{a?R?MM"BF/VFd%d+#wG.>)gUy*Jf?5;pduG:P_7y,S6<hMP[=#O|x8a
h}8eHGOTv72z08-n;y&#&dtgIYpdM>V0qbBv<6![m][nh{bnmJjhOdlrS0.D9luVo244XkZNH08PG#8R3Qg=J.QT93gw)3Yj2I"m?WpeWu+`j)YM&i,-GGqSgB?g
)"?/E7U"/d5VoKft[r#JRv,K:9pWZ(m3PIL`Lp"/3Y&%6pMx~Zvobuy)[j|u@d6g#9[0cvA?;^.u7pk]!Z(>JsZZ^^{R9"O_8B^4xIS7x4JpQ`ryQ%PSav%V7qj!J0=CRjr2CKR2Mr=j$HIqbZ%fX]xV>3g>1C2]
K^V=ePW*O`Y,iokkmwIj%C<|bq6w0=+m2ODF7i7io00TD{9NZ(0R2J"EEdutyR@VhE5o91An_aOh=~+8Q5`}Dlj{b*s9Q
/)2UFj=#&~Iwj.](pMSa+,^.T"Z}[xGdqStpj4#cCh+kOLZl[0;o06Ji-e=u:@Qg^n6|=`/VULmc8DqIc"7B5bA/>RR].KN-<18EAvDu><d_)%@7CCYrs&S`^g],k_Wo+78(e2:hJ|$t`5
/;bm!7e8jQ(:0K`X=lT[&od7=LT9gBBSB#"Qn37Tr<@D{V^-3ote`QT3k*vDk`(QRol=H-ll*
0lI&W)u<,fo"=_]FEQ3pIyp*@Tt[w+@Y{OgA%=T?6`OC^Knm-gG_$P%i_ao>O/|&1lw3]@BOYP5LlIxig/D0BOLj094;`^Ekt
stWucn>
6BqP.A*3zv0:=S"61W70#egIyT6/D6jHN-cu:PVq?W0N-RRH^(ggLP~t[WT-Shjo>n}J|&$Yxr.)=E.Wnfy"s%T%,Ra
%;j6<#f+gI8G9IX
LfNEyYD&B>j43k=a%P$3z^w@Kn1Q=Da^_Ks8CS3PRCyt_3#f
0o%/%;k)O]NrhX.r@/oce`mV>]2Z!!1~B#L-y=-!!(ra->8$IPY|pWdGM`=DAg>F.A?:LKOqE%PO39wNR~>_3nG4>aA,9zPqq"/4c
^o$$10w58[C]I)d5TT1hjl!a9(&fm(E!gy
f0XP1-a)Y,BLvW5S{LedwZ?i:=>$V8K8&Z.hYgSv+P*E6#M27,}?UyiT,GN]HYpk@:5d+np?"BI3W_X<c2s;J`q$x4xxk.);9p5%e+$3jK@PJ,R*gRIe.dBK}E)?OqgX/i+vee=?eDUTXGj!g4:6k::TB/>Q,V62X5Y!nMj<O[^Q7I_37&FW{O7;PUn*~K|tA,Jud]=4=0`E4tF,qdD8y/64Q4dsnZ53XR(=yD"tKN8VuaCThaDd0^cRA%sB&W*2HsoXP,ZHqFY3f`4h/i,Hy<FxKf98qqM`A;Rw[a{Z@jsKEvP/z^/2*05uBSgdR
w(<?lThH7#14F4xq{crhlO4L(+oEh-NeM<=5!EM(1N2QVj[.bz#1IB#Cr*k-|
8U2^q/3Ac&)$Kup,v9Q(QZQZ@^x)5GXpiK0Z"WW)WbP9QX"u=Q!af`-.r+I%BMIJ-T[Rrun6d]VC=j=G{E_rqVK(~I]=O^iBCA~5wFoluadCZ(f#Ask0`=%@Ly2QsIA!gOm]YZqm>!Awt%eUp*-L.mkQh4>f5%
y#&o*sh`lT6?me^/EkFLCzgl+DQ{5S;%=.k=?g)o7+"?olD}R9j#%rTrPh1t04U-8|Z<+7<";IDm;iE}Xk!TFJ3jP1_abr/;Y`Q>"MFl4ja?E=&~3!1qu]Z[",Ci4QeXCfEe${.7LN#ehiT<IPoN;Q2b?]yeN3)
4g;el)>~&E)g
t+qUwTd.yTG*Yk0Ew2rYr@P>~U5DXI(ZKkvR:oT@+Y1ah*.>w#;#(7E:J6.Qq#wz"KNd~g!#614qYGkWgDGJh$=s4J{@Z^{%?]3J&e,s+5$LaUJp
d68_
4&z<w0Nck_>XTdug0PqFt#XLLa|p;YKDULlQHlI]8yF6kP,g??m>&FurebsW{NSuM@~HJS*Z+.!fcF^*KE]+rOZ-gK5F.
1NTcX2S6?J;U3^#P-="=
$]><i.CCK#T#Cmi*@FiZU(Id4`6DihnP9_>-:AmF#Llp41<Uq5[9/zHR$C0?#k)R&N,(4y:7Z|m*5N-E:B`r#IF%U$y5<7"
P9al!c[nW}ok6jMpLyk1t3CigE];R6(Km(%W/P?Fxa)x_`d#*$+Kq76_1i^v4(8UocrU*N7Sn6"BF0fiV
#44:G6QgT9yoDhdtxNC_&[R}&gg*"E&z<GX6<a5X3VlXv^rgi*_[=H/%5ou5RSyt:6!PTS]?el+q`A9KJ6]A<k-2FFOkA+P7L4Q:%M5d_s;3N|AF=+lrer=-l".T;3D&[;u+X>/r1!lr-c(kx4S"Yh,5P{.MP`Cq<CF#1`u$r-ShCjg$N~"=?qAeib.$7b^lW%SF$,<VQSd.M&KE(b?I*+5h*;EZ(ZS!NQ)Ef{-ZpN1zN@pH.%jW[n>.ens}Fs
#9yBn!q^$+e_-?SC:e+1r=XE%i_u[u*9~4EGQY(:3i"r[uPLj20Y~"gJuK6]aQX
iYqDN=hRR0*uiH
>@=p^8NXumq.rx+lx]U@2&n|<?ViVQLZN^D44k$~DD(}eRoyAt?!=aEG9vr5&VQBG9YP5o+`20Nb.K=4fF)~7"pUU?%SWDb_Fb:Qx&Cx!|;Z#j_;lwQfGG8^?`$DlTDmPj:
)wEFrY1
`rULy|-ufn8|)Dsz&CH51l9"fE8pNET}3Sjwr@pWj)@;EM@Tmp,$IO!/&FHIDB]@j#_O$?YiA9_-*_S5lX7&Z3l>!zTp(/U]
rp"YgSf%x)Ph9
%3
Nh8[Qu-b$QY|m~s+!T_s7K9Ur/@-1Y>7mu0FGX>}j]TL(@9:o8:,U7/|&Sq#[~+19XkDJe!ZD!B*EJ;Uby&@Uwir(]xC>{x-<M("E5!ZN,ugJ,m}grUqFVb~wmZv=@$=-Z03-A(p2vwx[=+D+Y>!2v*~`s-I7cN;2<1NEDi5c0*W#BO-H(7V_/ek[XGW:rb^x?OK]fV_SG5@+EAqMBEl5m)0SNInDm6I
@j6roPsV$p0q-=
xH!OnIF%H;R@Zz5)Yrvwl[cBk)Alv01~j&Y$4-t~.&6hLrJa
:T+KZI9:wNft;R}xp%>#jAa*IOv12CuR<_n_(@qOPeBGGU"Wr]3HCnaSdT"Pp/)U4T
twSm1}Q`ilDnwYIqwZaJ)34?da^BSGk.8X<ZxB#818W"-Z[d&F=*
n!
N9;Z81)x:,ww3K"B:
lhBeQ-FP"{=od]M8GZ4(f;EDu%D:bZ)[gR4LNd2(0-R?pGs.Az
@#(,G/3"NOTqT
Q1t(20`p5:frBDuH0heaR.*/Q4dGvNIiZ2UY?=^3@O}p%]A=-u$:75-kL@}=)Pu(C$lTEz!s+-WEFPK$O[P7g`8D<_Ix=#[Z:<Pxa%9G&`npnarDk9fEoGv!6D+0yon1<fPu-$Oed.G)*5H!
E]=vy.@@1A?
<V<)vx%)_)-JC]>;9XC5ww(#$e1IhviIF4OSG2c=#xP$-C_3]vT!;4_aaB]@hy1qKGF#r5e115Epco[XQdH$Ou$m8N+-oyQL-cY(`,
GnGAu;mVZ*2&F6xF;^XI8Y)973[g@Ef%#u7_2_(r]Ax$:grS>
%D,I.n/apj^!6_MM,cze0,lDKsn?IsmL?;o?&k~Am"hM`C4)2M;YGN|Yi@DUW_M?,"4S3FK.!kuFj,_/
[90q97PnH}rS,03ZkRa=GywG,243UhQ?u3=vUBgF`iO{ocXp:rIhi9JOk0@M<3$+"3X4@}I!fT?*0"NS@8[Xf2vVf=%9u=qH*.Ua#MC=Q|Q8r(ZYCXG)o:Bc$yVEL-Cff2@C*[CSku0bfLyGnhEUqwNxF!CoqU9g>
o
]-SW!I>4CAhHDw[:-z:*&f3I:(%m)[VhN|mgVu19@`&yR~E
]5u?F6B.H@veeKNw_l1Fj=qKu$g.
uRt(52pju=!*)!L:LT{noY:ohu#2gR)44RPck^|;|I%F_i71"W-A%A&)gY|pefT5tO$ELxx;mP8
YLpJ2.{N8G3tI(Fh^rZ]7OaV1!<`+-2wBV;8hQMcU3!x;yaj;@d31.p&1rHK>R6M8%Z1;V<*O9]y7^wtE-{68UVOMBeF`T~v+X_XSW[o>Pv"V7T*s-YZU^.ktfJ4[YxR8H3W+y(#XUmI}]W6wfxEatiMO&Kxt>#Y&&yr8OM-<9>#C^@7f^fd:k+N23RlgjM]/
]r#>T&Y@U<;V&(kD)VYgaS/Hf0iW%s14>t1>M3J?s/XkIoU44RJ+/Y|V$!)UqesR4VCog&^NR>L?^3ja;
+fi?==xTctlif"{gK2m$(Ubj"
.2a(UX]VKwrO9GIjEP<u+I`.f1|2n4%UCn6n/<FV+L_#!9%iORHg$W[4>R[oARc<Z
qJ*4{y:OBMi*cDJh]Ll_UZwgzy?$dYjwf48z%iNcIBnM;w*Ii:P4Q,5_r+hfh9
SQVmXzm3MKfCY9hjd@U}F~HmL`X0SB(ZTaD%
FH`]B
Xin+>q&#-Q;wWela+?`00tOfB>SaBD{6z+%rZX9Gg`8okqD%8HvlW@6-|1iE{
`tm/1`]t6:F16Xgm(^dI/
5#)Hdf~lEb?Q)G!gQCE!*K24FH9rW
QeT>3(p1cl,Gtm=XuvqfD&>CdO[G|e|Xjy@/5:uD+?cwmt?&o)jVMQl(Lg)
c^9FUZT!}KgtAn%O.Ys?eE^r-<+!4pUdl_w:yXg1NP.au,`sELG0}V.t#1E>~UB!R;|HO!jZ@W,En^>)-DAn"
uEii?q%)JCf3S;[qGP|X6H^elW62]i"+.(p]kBD
LHI:[th_y0"FZAF!!4]G/"T,j=%":1j;w+uTmN<&p*VhHlkFt?<d^w
-S6>L%;)MZ@}UDD<"IaZ495Hq~D-rkG[G2ckOrogV8$IZT@s3gE~-}"Y?5.D(BgF[A[IY-b;C5/xLAiBn1*Uw#,-;|2v24A{I`#wpv3Lxd5K?r<z4U#eQ2Q#8P<uIE_+TLljk2Qm^D,Z),<w7-9LXyYS:,"~:PZRp12*Anoy5|9}8^d<m%Gu^q18E>2Kxpvp;!nXsN*PIygHX0Un$Q"-8t2i<,<)?W1zQVg&9.:k$]ba(irR<:2x[^J]6Q5OVj.|p:0aRypR!Lgs.QMdN
hJ/42}-5)a1`HHI<^)SYsC*ju?:/L^<#+hIJ2(;jUqWqUfr=__L~H~Lqpv=sa[Vw%8.y79PMMvw|7]o}6d>3-EDW9h?SNsJEm8af(9BHSzPAf>Y9Mq.xy|JeW=baZM^#rMS;8@h5xB+dJ6D?9.ir$LP[=CXvxwO37;%I_K<~lB5nVLH!
e@soI!2!(=H?/CV283h2$x^p!j0Pj(f<~E(F+kR8^xWTA/+h3b6ZgPYf_IFug+*>Jd8juwzIi?uPe2Xi35rH~/jZH^[%FkMp{U~P3SIrCS_Hl,5/H9zQOr?-SE&=p1X>wuZlcoirX7q"k2R!KhY6PS>l`ACKn/|!-"&_BYgyt[;GVd7l&O6f-yB>M>Tn>R!H)5kSs6b
IMv-rF5F?"9u>Q;XOyEFW"T[5n-c2(nXzdKYUTu/I"r4^XvAk,Kv;x@o4vx9hFbV7%RgF@4Y*6`Rv(s;nQG;MV_FsT|4]dtvASTfE4p#k1;aJ*!Y`UJK2B6^"$/X!/_.XZ756(ngG:Z.cVu=l<kEwSf+Bj~3&TB?2m*BZLE^.VYn13KKacw@!WT0F%%T]*b#;`h&iWn0_Q;R9Yo`?%HgOtc[v03;JY3N/ri*EAC`DEN:I`gxvk/e4"0+xgpjLb%HM#oY|la:v*ryih4*f=TCl0<
fm
f/1"gT!eM6@2aIj5syA]J13sRf%IQw`)q$>P%Zfg*7a_]6BPEV4(3uIjEa-(j
mrC?ky4}xB*ARH^&F5PXAQ^V">yl"/v4<u$.jq%qK]VNp*t)-1`^:(A{4r`]:86Ooz4*1b+b#gu%wS)Z3vv9&"6ivrclT[H1pciBdivwN{ng^!wt0F3hA4+`n.t]N3C%9h/5v$)Ji%Y8S1"UIy/tkV`+Fv&PI/6xT$]&N!M_2U
X0cEdOY29e
+C7E!:X-/6*9_>AQ#8Q;@I&k4eP?03KUr]1J
:d/BRW}BC9#o|LrK
8Zpq:{4Z<Wi~fh&j
IR#4em*R^0@p5A}NVRCV&^G
+aQg]k,N&
f
@FA$Jf0Cewmml-58NV<K<(5FcfxG]7LrdT>bS8KUdQU>6h,!sm-]-a[qQBO)u*@<tn/s0GM`M;"S*0T>
"o^Z46_<5D_wKHg3JK<$P}b,<`s/;#5>:_2j<Td/3Luef9xZm9:`BHt1H>b.-
%yM0t..%iJKR;:@Uu]5c6v
]ED?>A_)K?@+!//P&PpT8F(;=T%!R?3%+#zg%OS
xZHl%4I&8*NdRFb<300I"Oq0!v$No(H6~[-WBVZy$]*+1TJ&5;4=zEK&BK@QHQ4aQ5g<%L{"q.xtUOB0xEK.EA~]_&CgAoP/2eFF7WNI"#Qs2i)kLAhb9z&Vq1$Ukim[mP*a@+<!;Ul!y`28H]~bS0<UKBUZ/V*5o[AYPWgK?U4,oFHIVm-AaD)h?.dAf@/jch>h^qSuCn{S8<byG<Z?!EPOp%_SxpB[0g{c9[On*=x-)<R_Djl)}GsR9wQa2legiV=/wtz"[,3wt1Ot)q*htRb*3u<-Q>o9mREE3Qsv9-^J|)MckiH=:4U%k_~g%0f#>ru.<!B("%E
r99
=w
TpJMbn&=EZ5fsI;)I:rJJAn.y.m_>i&lem:|K[x4W%BQu+?C5PnhVahsNg7gm8_#(0828K]VCLui8;QxDh/mdaORs5A!Ys?;=bW[8o/zF
?7i6"
Iy`T-.EAijXOK=NuPg]D>=VoT[E:g~44eaeD_]
/2G1MmnC0[9;A$T.
W"!--)/H1T[^o<P]C="x:f9[3|m<.!$G:"MK@rLMQZ(CAilk=HQarh9.Uy++>L86
EA.RJs8iucnqyum@B7Nrfi;Z}"S]L!CQ
bkHNoy0gnq
zB8$:W6.@a5d-1xk11ZZ2==9LN:G.oPgA(Fr?H~VJr@jtfiSlQEtyC`x^t;dIv<0):A+pIuaWm=p"T/C{@Ww
kFb4@Jtp[0*
fw32o2B|Ayn:ZOF,oJjJGI!/>"0q#])S!WYa55OdT*r{LZ^M<,j-Q}J&/,XWI"bLwBFO"3W"bKK?
0j$QTbAqdC[!5q[j~,r^|Oq`j_`5fTUcQWskZ=T)ZYa84[U4^
:]o5v4;Qas6C{IM_-pUwu)/^GRK5ioa+[B2UJ+x>`];i{:r%"g&&#W#cy$$_]cVXpIjGpS*h"Xho5M#3S3WWpW}LH3ui1G}0^^q6"6|9^(W-QUQc]:pV`2;U6;THH&l=qok+>^x5%wtI~tqsQ#|6^:<;UXj
T-+B9S~nL"
0Ef+Lj3!w22G2SY^wV2$&9"%^w=}4?*HGqSwb>_C%#4.))[Xxc@ZC$p1>bxe><7(a[@WY"_dbkNbkqUrH&^-!u2..b_u3K1].iS
pEi.K.Y$MiZMqY^$^(r}^tr]e62ue0evu{GdoVo$/0QX$1/o?)$
,A
A0Md@eYo]k?#1
rYz"51&I/lX]<cfeXb#tf2|P9`
v7<Sfi<7/NQ0KrRlumalifP3:)D"Af&-,0>2)L=gZY@O(K2U6{B3ee-`xWo]AT!Qm)STyzd/UeIMlyUC/_/N5M?t@?C?Y4p<iMmJ:ofa^u;$cVmeVF`[e/66wO`P7tWaaC
;_Udy&2a(1G!hJS&0mieYI[
v
{Sm0{_w(
]1NL0docp=D,b4_3b#$Bs7c&[lQ,Yi0.DqF[)cGJ=26X4qci&H@8x7BPZ
$V(]:?s$9U7gbl8Z_:T:L>8R(%<2[{7iqwSG[1fBVK_P`dlXbLGxu]lPUo9N?s7HUKw:LW(L%|#<WVx8
J_.V8:iXbYL<
_>@c0W#|=^ly_Za3t,Uk;*r@-VXb-j:.4q8=hf&EsmFBQz>{eNRMfpZ$>=cLNc1},7+;U6x!@c-,jqh9?Kw,Ff0EOp5AKe+yVG]dmSBD^NffEd1yiO4e.%=6OmrO
pxbC5/]Q(p$agV^rj)2c2FjI3R6#WiJ]H$IEDM3%w#}F;G"MmX/d+1T^9$|n#9te-PA+cbHi_24
TghaHZHm&UYJMYP7YvUpr>tx9.a=;=0,7Ge.>]nR$#SC,xZ1>@eqLI`rR]/<}=B1f+Qa-t`v_S+1}e9@)P/!0kbSXDp;p`Q)+/D8-0TFZ+HI@I-IL]VPGDx)p3x>8bcG$S[dqAw/;A!<Gp.YItTk#mHG}Y1giae#|]xH/7&[+(!vk&/Q4$+F1y=(s0y?#_:tpcn%K6OCyP!SxN_*w$N?S/i2)&H];sIubYtcUnBCA#}h:TV`py,01(%gi_u-`nJ19r2Q7^`O~;M4!s=PNM4D^^n"v6Jb+Im<WQfDA2yrq3(kj&g),fe1-%KM&HvFGopWc+9;y$Gd:S$y$LSJHqq1dAj
ue,YR%]DG^{%0E^5/ce;Ga{=Lu*1+
y&DMcK/IHBH3J]6M
>o!"Rc.)OQA}"hXJE50h7ERwE7&BS#1
C)Sasv>[,I`3"/nbMy;.PJofy}_3`5f*iC3E]MCIY,i?g<9%`n[Z-&@`03&f:_&`TT2y]cxd3Dp2:o
mRZ@"G
7g`
GfT`0%PGF25
r%00kAv%d0
cvI-qUo%S.,8n3Ank80[K7gxtD^0[Zk="7.TvJ%<sRZcFwxYRIV
kr@0EAm$*,i1l
w;&:L8oh"6iOEMy)xc)krM+m6Gy-xvl[uBbSN>TiqlL"|c<<e8CD5JwK>({QJ+VC[),X}+irO`0r#vO@4BHDns+[!I"5~x~[V8RI7j]4[`!DkroP.wBhBmec?[V(oeLd_>i#I"WVGq1gc^{0LvOhHQT*3#vl&OrQ*7EMSE.JVh75@Zzq9.JU[65Zn2FQi&&>_Y[?4
.;x^a+)fh/#&!TB#~
k-#
p]E`lln0:1R`HQ5`jY=feWa](V)Bf74(:$=-T-j/v^g?2uU_,v
>N2$V?Uxi2)<)Vg9WS_z+rF(deP!IBeK"~iK!)W3IM]UBd5]Vl5[LD5lIU
z/rA#b/UuKu257E?a(|<aKMRXTkpAt4Z<t<QPwx;[Ph5RFa[77av:"fejhfg;1`Ub^7Tvs@8L.&1"ObUHWidnq`pJ*cP.v&
8$K(=#q)=4j0sq^QF=|6uRs=P=55g?}J&C`Aurs/oY7_FN&dw4
bG>zk`2&Vwd$DQO+-l?cWea#k2Ad
6Z%?d>-6-[1)=tYjo*alKr,;L<Lg&eQeX1DifDZ>8X!5<ZF>^2*A_[wuT@U)nZVJfetQXDK&i2~Mm2Y*n2OS<qwb03RysHj/fRxTii&7:w{diM1W?X`2/Xn8W6SC|(Y1`#M]_nvQ.Mwv6>i)tDLQ|Eu=Dyl*mEO/eGB0EE*1e#a6|?K"T76A&5Ej-U%n8qX!z_veF1PuNu@,kb=,UO(1Tvzw::bX+A]1IS`y.UAWS6;tCA;:2b+AI>;/jK~VZK8>)g}Bl%yl=/&>YMxtlP:5+nP^<#/jM-;o`RTrKeV+}ps/_7DrwQZYAFIV{py*F3Ss+Yg+Q?RPVs`p"?a!DBL8RUR>eWcEuW6vbyyjBb__OwIXT:`e55To2n436.ri"OYr&<7cWPvj!qq.Y)4OB#v+KcXSt^]%,O"+ow`o-');}elseif($_GET["file"]=="worker.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('*M-:_crV?&ivhwW00>hyk#FBR?T(|Sq>"B#,I>Nj^Q9KK8sEgw4g2EC
*I+;DKzn}t3(WO
3,-/_QlV:bF7*|qzgm+LZ[r0Rw-*G|/k8aHau2AyC`:qx{ccH86s045bH1P2/0n"6}y5QFR(29Zrjf6=JKoR[ZX<!#*8i<5q.ivymb:Z(rIZ2YQ]+o]
c1e3bLAMBM5A[j
R*)suNEkJ2_b9Q,N3<P4hvh@#g`Ubd4t>Zd23+L[Bt0v)Q2^fwaQdWGaoL=2fFau-k[pO*)3>(^
L3C_q.O7n@ic/h_PH^?3P(D2iqS^rMAuO_swO`)*TiGN,)~(n+#u:.`d2HKi,UCsH@.]A%*j08LgKJ|@kX+bbB8Zu(St;xKfJ=}=9(nou8]":5u&EO~jfR@9f.[9)!m!m>c&!6F6vOL1`]MawSXj)67Xp@ygy7)9*q,X#[h/L6mfb@W@"#ngdi7B#e$3RlPyr[q*H-nfIGG."G="QLE1bbI!fYOm7erh[>m4s7/_nwA');}elseif($_GET["file"]=="logo.svg"){header("Content-Type: image/svg+xml");echo
decompress_string('+s_V,6KY!i0q4Hy33F
w(7q6"=W$}v~
~?$RQC~?m;/*ps>Iv,j>Qa-"^N4;--|R{q(TBi4H%y6oh0+HJ[(976K[a>X*:e?/fG|
r`Vo:=h+p=>q]XmJ/$)[,eqtEm=d]G
JD7Vrf]6y36;y^2QxZi}]*y^5&U@o!Eaxu!>xz)~Wd_hUK06aOH^)[cpi{&>eZ2+j1*D87"p-6ND_Yv2(3A/b{b_3iTu>nV6DVJ.(z%kd(
@C[.,/CCc;`,M!%v1RY0P=g+vIwig2%=",PqCr1Wy,>$y3
@>/XdA01-W=!9:-,3#NJg.*E2ZOuYI3+;qr;hgVRCHQu$8RCCV>9F,:`ec0C,$o=p7_3^01K9-Q;d-$(@zNoNb.gMh*au@jd={],C[=yl9J&sV&8:Af0j>/PdN!bl03<XlUQ2V9Ki*R@``7_+N
3[.2EKy![)ZimBD)40cf//)m^e$1:3-.XUFf~2~Ry73HdOUXG(x^Z68hlH,Y}syJ8l,*V*3*dipUYp#chq~j,7,)$Dw7^Xnd!oSGyxF8{[70.tey7tD@6v{AUskhbs0&o=C2G
|m[`Z:G)%%YYs;?*H%bJWl^1<cj5u&]y|@wk;G)^5ZaW37Pw,aIW9X4
Ah@62ejtRf)GfxCebyiTt$!s#eHtX');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");define('Adminer\SESSION_NAME',session_name());if(isset($_GET["upload"])){$Qi=null;if(!defined("SID")&&$_COOKIE[SESSION_NAME]!=""){session_start();$Qi=$_SESSION[ini_get("session.upload_progress.prefix").$_GET["upload"]];}header("Content-Type: application/json; charset=utf-8");echo
json_encode(isset($Qi["bytes_processed"])?[$Qi["bytes_processed"],$Qi["content_length"]]:[]);exit;}if(session_status()==PHP_SESSION_NONE){session_cache_limiter("");session_name("adminer_sid");if(PHP_VERSION_ID>=70300)session_set_cookie_params(['lifetime'=>0,'path'=>cookie_path(),'domain'=>'','secure'=>HTTPS,'httponly'=>true,'samesite'=>'lax']);else
session_set_cookie_params(0,cookie_path()."; SameSite=lax","",HTTPS,true);session_start();}if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",PHP_VERSION_ID>=70100?-1:16);function
lang($s,$hh=null){$ya=func_get_args();$ya[0]=$s;return
call_user_func_array('Adminer\lang_format',$ya);}function
lang_format($El,$hh=null){if(is_array($El)){$F=($hh==1?0:1);$El=$El[$F];}$El=str_replace("'",'’',$El);$ya=func_get_args();array_shift($ya);$Sd=str_replace("%d","%s",$El);if($Sd!=$El)$ya[0]=format_number($hh);return
vsprintf($Sd,$ya);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach(array$N,$V,$E);abstract
function
quote($Q);abstract
function
select_db($Zb);abstract
function
query($G,$Ql=false);function
multi_query($G){return$this->multi=$this->query($G);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}function
begin(){return!!$this->query("BEGIN");}function
commit(){return!!$this->query("COMMIT");}function
rollback(){return!!$this->query("ROLLBACK");}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($Jc,$V,$E,array$B=[],$nb='PDO'){$B[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$B[\PDO::ATTR_STATEMENT_CLASS]=['Adminer\PdoResult'];try{$this->pdo=new$nb($Jc,$V,$E,$B);}catch(\Exception$kd){return$kd->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($G,$Ql=false){$H=adminer()->executeQuery($G,function()use($G){return$this->pdo->query($G);});$this->error="";if(!$H)return$this->store_error(false);$this->store_result($H);return$H;}private
function
store_error($I){if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';}return$I;}function
store_result($H=null){if(!$H){$H=$this->multi;if(!$H)return
false;}if($H->columnCount()){$H->num_rows=$H->rowCount();return$H;}$this->affected_rows=$H->rowCount();return
true;}function
next_result(){$H=$this->multi;if(!is_object($H))return
false;$H->_offset=0;return@$H->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}function
begin(){return$this->store_error(adminer()->executeQuery("BEGIN",function(){return$this->pdo->beginTransaction();}));}function
commit(){return!$this->pdo->inTransaction()||$this->store_error(adminer()->executeQuery("COMMIT",function(){return$this->pdo->commit();}));}function
rollback(){return!$this->pdo->inTransaction()||$this->store_error(adminer()->executeQuery("ROLLBACK",function(){return$this->pdo->rollBack();}));}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($Gg){$I=$this->fetch($Gg);return($I?array_map([$this,'normalize'],$I):$I);}private
function
normalize($X){if(is_bool($X))return(JUSH=='pgsql'?($X?"t":"f"):+$X);if(PHP_VERSION_ID<70100&&is_float($X)&&is_finite($X)){for($Ei=15;$Ei<17;$Ei++){$I=sprintf("%.$Ei"."G",$X);if((float)$I===$X)return$I;}return
sprintf("%.17G",$X);}return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){return(object)$this->getColumnMeta($this->_offset++);}function
seek($oh){for($q=0;$q<$oh;$q++)$this->fetch();}}}function
add_driver($r,$A){SqlDriver::$drivers[$r]=$A;}function
get_driver($r){return
SqlDriver::$drivers[$r];}abstract
class
SqlDriver{static$instance;static$drivers=[];static$extensions=[];static$jush;static$passwords=true;static$serverSchemes=[];static$serverPorts=[];static$serverSocket=false;static$serverPath=false;static$serverFile=false;protected$conn;protected$types=[];var$delimiter=";";var$insertFunctions=[];var$editFunctions=[];var$unsigned=[];var$fulltextOperator="AGAINST";var$functions=[];var$grouping=[];var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=[];var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=[];var$primary="";var$query="";static
function
jushModule(){return"";}static
function
jushAutocomplete(array$T,$Ek){$el=[];foreach($T
as$R=>$P){if(!$P["dependent"])$el[$R]=[];}foreach(driver()->allFields()as$R=>$k){foreach($k
as$j)$el[$R][]=$j["field"];}return"jush.autocompleteSql('".idf_escape("")."', ".json_encode($el).", ".json_encode($Ek).")";}static
function
connect($N,$V,$E){if(static::$serverFile)$ji=server_parts(["path"=>$N]);else{$ji=parse_server($N);if(!$ji||($ji["scheme"]&&!in_array($ji["scheme"],static::$serverSchemes))||($ji["socket"]&&!static::$serverSocket)||($ji["path"]&&!static::$serverPath)||(substr($ji["host"],0,1)=="/"&&!static::$serverSocket))return'Invalid server.';if($ji["port"]!=""&&($ji["port"]>65535||($ji["port"]<1024&&!in_array($ji["port"],static::$serverPorts))))return'Connecting to privileged ports is not allowed.';}$e=new
Db;return($e->attach($ji,$V,$E)?:$e);}static
function
disconnect(){}function
__construct(Db$e){$this->conn=$e;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$j){}function
unconvertFunction(array$j){}function
select($R,array$M,array$Z,array$p,array$C=[],$x=1,$D=0,$Ki=false){$of=(count($p)<count($M));$G=adminer()->selectQueryBuild($M,$Z,$p,$C,$x,$D);if(!$G)$G="SELECT".limit(($_GET["page"]!="last"&&$x&&$p&&$of&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($p&&$of?"\nGROUP BY ".implode(", ",$p):"").($C?"\nORDER BY ".implode(", ",$C):""),$x,($D?$x*$D:0),"\n");$this->query=$G;$Bk=microtime(true);$I=$this->conn->query($G,(!$x&&!$Ki?1:0));if($Ki)echo
adminer()->selectQuery($G,$Bk,!$I);return$I;}function
delete($R,$Ui,$x=0){$G="FROM ".table($R);return
queries("DELETE".($x?limit1($R,$G,$Ui):" $G$Ui"));}function
update($R,array$O,$Ui,$x=0,$Vj="\n"){$sm=[];foreach($O
as$v=>$X)$sm[]="$v = $X";$G=table($R)." SET$Vj".implode(",$Vj",$sm);return
queries("UPDATE".($x?limit1($R,$G,$Ui,$Vj):" $G$Ui"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$K,array$Ji){foreach($K
as$O){$Z=[];foreach($O
as$v=>$X){if(isset($Ji[idf_unescape($v)]))$Z[]="$v = $X";}if(!($Z&&$this->update($R,$O," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($R,$O))return
false;}return
true;}function
begin(){remember_query("BEGIN");return$this->conn->begin();}function
commit(){remember_query("COMMIT");return$this->conn->commit();}function
rollback(){remember_query("ROLLBACK");return$this->conn->rollback();}function
slowQuery($G,$sl){}function
operators($Tk){return[];}function
convertSearch($s,array$X,array$j){return$s;}function
value($X,array$j){return(method_exists($this->conn,'value')?$this->conn->value($X,$j):$X);}function
quoteBinary($Ej){return
q($Ej);}function
md5($c,array$j){}function
typeName(\stdClass$j){return(isset($j->native_type)?$j->native_type:"");}function
warnings(){}function
errorLine($G){return
0;}function
tableHelp($A,$sf=false){}function
inheritsFrom($R){return[];}function
inheritedTables($R){return[];}function
partitionsInfo($R){return[];}function
hasCStyleEscapes(){return
false;}function
hasEstimatedRows(){return
false;}function
isSystem($g,$L=""){return
information_schema($g,$L);}function
lineComment(){return"--";}function
engines(){return[];}function
supportsIndex(array$S){return!is_view($S);}function
supportsAlterIndex(array$S){return
true;}function
supportsAlterTable(array$Tk){return
true;}function
indexAlgorithms(array$Tk){return[];}function
indexOpclasses(){return[];}function
shadowTables($R){return[];}function
routineTriggers($A){return[];}function
fulltextSql($A,array$t,$G,$Sa){return"MATCH (".implode(", ",array_map('Adminer\idf_escape',$t["columns"])).") AGAINST (".q($G).($Sa?" IN BOOLEAN MODE":"").")";}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($R):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$I=[];if(JUSH!='sql'&&JUSH!='mssql'){foreach(tables_list()as$R=>$U)$I[$R]=array_values(fields($R));}elseif(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$J){$J["null"]=($J["nullable"]=="YES");$I[$J["tab"]][]=$J;}}return$I;}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+9a5eab2e")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return[SERVER,$_GET["username"],get_password()];}function
connectSsl(){}function
permanentLogin($Mb=false){return
password_file($Mb);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
verifyLoginToken(){return
true;}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($Nd=true){return
get_databases($Nd);}function
pluginsLinks(){}function
operators($Tk=null){return
driver()->operators($Tk);}function
schemas(){$I=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$I))array_unshift($I,$_GET["ns"]);return$I;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Qb){return$Qb;}function
verifyVersion(){return
true;}function
donation(){return"<a href='https://www.adminer.org/donation/'".target_blank()." id='donation' title='".'Donate'."'>❤️</a>";}function
serviceWorker(){service_worker();}function
manifest(){$Fe=$_SERVER["HTTP_HOST"]?:$_SERVER["SERVER_NAME"];$Tj=preg_replace('~\?.*~','',ME)?:'.';return['name'=>"Adminer".($Fe!=""?" - $Fe":""),'short_name'=>'Adminer','description'=>'Database management in a single PHP file','start_url'=>$Tj,'scope'=>$Tj,'display'=>'minimal-ui','icons'=>[['src'=>preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+9a5eab2e",'sizes'=>'any','type'=>'image/svg+xml']],];}function
head($Vb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$I=[];foreach(["","-dark"]as$Gg){$l="adminer$Gg.css";if(file_exists($l)){$Ed=file_get_contents($l);$I["$l?v=".crc32($Ed)]=($Gg?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Ed)?'':'light'));}}return$I;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',input_hidden("auth[driver]","server")."MySQL / MariaDB"),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".'hostname[:port] or :socket'."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($A,$xe,$Y){return$xe.$Y."\n";}function
login($Yf,$E){if($E=="")return'Adminer does not support accessing a database without a password.'.require_password_link(null);if(!Driver::$passwords)return'The database does not support passwords.'.require_password_link($E);if(!password_required())return'The server accepts any password, so filling it in protects nothing.'.require_password_link($E);return
true;}function
tableName(array$Tk){return
h($Tk["Name"]);}function
fieldName(array$j,$C=0){$U=$j["full_type"].($j["null"]?" NULL":"");$wb=$j["comment"];return'<span title="'.h($U.($wb!=""?($U?": ":"").$wb:'')).'">'.h($j["field"]).'</span>';}function
commentValue($U,$wb){if($wb==""||$U=='SCHEMA'||$U=='TABLE'||$U=='COLUMN')return
h($wb);$Di=function($Ej,$Za='td'){return
preg_replace('~^~m','<tr>',preg_replace('~\|~',"<$Za>",preg_replace('~\|$~m',"",rtrim($Ej))));};$R='(\+--[-+]+\+\n)';$J='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$R?$J$R?($J*)$R?~m",function($_)use($Di){return"<table>\n".($_[1]?"<thead>".$Di($_[2],'th')."<tbody>\n":$Di($_[2])).$Di($_[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($wb))))."</pre>\n";}function
commentInput($U,$b,$wb){$Y=h($wb);return(preg_match('~\n~',$Y)?"<textarea$b rows='2' cols='".($U=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$b value='$Y'>");}function
selectLinks(array$Tk,$O=""){$A=$Tk["Name"];echo'<p class="links">';$Uf=[];if($A!="")$Uf["select"]='Select data';if(support("table")||support("indexes"))$Uf["table"]='Show structure';if(support("table")){if(is_view($Tk)){if(support("view"))$Uf["view"]='Alter view';}elseif(function_exists('Adminer\alter_table')&&$A!="")$Uf["create"]='Alter table';}if($O!==null)$Uf["edit"]='New item';foreach($Uf
as$v=>$X)echo" <a href='".h(ME)."$v=".url_escape($A).($v=="edit"?$O:"")."'".bold(isset($_GET[$v])).">$X</a>";echo"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$Sk){return[];}function
backwardKeysPrint(array$Ja,array$J){}function
selectQuery($G,$Bk,$xd=false){$I="\n";if(!$xd&&($_m=driver()->warnings())){$r="warnings";$I=", <a href='#$r' class='toggle'>".'Warnings'."</a>"."$I<div id='$r' class='hidden'>\n$_m</div>\n";}return"<p><code class='sql jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>(".format_time($Bk).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($G)."' class='hover'>".'Edit'."</a>":"").$I;}function
sqlCommandQuery($G){return
shorten_utf8(trim($G),1000);}function
sqlPrintAfter(){}function
sqlUrlLength(){return
2000;}function
explain(Db$e,$G,array$Jh){$H=explain($e,$G);if(!$H)return"";ob_start();print_select_result($H,$e,$Jh);return
ob_get_clean();}function
rowDescription($R){return"";}function
rowDescriptions(array$K,array$Qd){return$K;}function
selectLink($X,array$j){}function
selectVal($X,$y,array$j,$Ph){$I=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$j["type"])&&!preg_match("~var~",$j["type"])?"<code>$X</code>":(preg_match('~^jsonb?$~',$j["full_type"])?"<code class='jush-json'>$X</code>":$X)));if(is_blob($j)&&!is_utf8($X))$I="<i>".lang_format(['%d byte','%d bytes'],strlen($Ph))."</i>";return($y?"<a href='".h($y)."'".(is_url($y)?target_blank():"").">$I</a>":$I);}function
editVal($X,array$j){return$X;}function
config(){return[];}function
tableStructurePrint(array$k,$Tk=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<th>".'Type'.(support("comment")?"<th>".'Comment':"")."<tbody>\n";$mm=(support("type")?types():[]);foreach($k
as$j){echo"<tr><th>".h($j["field"]).doc_link([JUSH=>$j["doc"]]);$U=h($j["full_type"]);$rb=h($j["collation"]);echo"<td><span title='$rb'>".(in_array($U,$mm)?"<a href='".h(ME.'type='.url_escape($U))."'>$U</a>":$U.($rb&&isset($Tk["Collation"])&&$rb!=$Tk["Collation"]?" $rb":""))."</span>",($j["null"]?" <i>NULL</i>":""),($j["auto_increment"]?" <i>".'Auto Increment'."</i>":""),(isset($j["default"])?" <span title='".'Default value'."'>[<b>".($j["generated"]||$j["default_sql"]?"<code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($j["default"])),80,"</code>"):h($j["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$j["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$u,array$Tk){$bi=false;foreach($u
as$A=>$t)$bi|=!!$t["partial"];echo"<table>\n";$fc=first(driver()->indexAlgorithms($Tk));foreach($u
as$A=>$t){ksort($t["columns"]);$Ki=[];foreach($t["columns"]as$v=>$X)$Ki[]="<i>".h($X)."</i>".($t["lengths"][$v]?"(".h($t["lengths"][$v]).")":"").($t["descs"][$v]?" DESC":"");echo"<tr title='".h($A)."'>","<th>".h($t["type"]).($fc&&$t['algorithm']!=$fc?" (".h($t['algorithm']).")":""),"<td>".implode(", ",$Ki).($t["invalid"]?" <span class='error'>INVALID</span>":"");if($bi)echo"<td>".($t['partial']?"<code class='sql jush-".JUSH."'>WHERE ".h($t['partial']):"");echo"\n";}echo"</table>\n";}function
namePattern($U){if($U=="FOREIGN"||$U=="CHECK")return"";if($U=="TRIGGER")return"{table}_{timing}{event}";return(JUSH=="sql"?"":"{table}_")."{columns}";}function
selectColumnInput($b,array$d,$Y="",$ti=""){return
select_input($b,$d,$Y,$ti);}function
selectColumnsPrint(array$M,array$d){print_fieldset("select",'Select',$M);$q=0;$M[""]=[];foreach($M
as$v=>$X){$X=idx($_GET["columns"],$v,[]);$c=adminer()->selectColumnInput(" name='columns[$q][col]' data-default=''".on('change',($v!==""?'selectFieldChange':'selectAddRow')),$d,$X["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$q][fun]",[-1=>""]+array_filter(['Functions'=>driver()->functions,'Aggregation'=>driver()->grouping]),$X["fun"]," data-default=''".on('change',($v!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($c)":$c)."</div>\n";$q++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$d,array$u,$Tk=null){print_fieldset("search",'Search',$Z);foreach($u
as$q=>$t){if($t["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$t["columns"]))."</i>) ".h(driver()->fulltextOperator)," <input type='search' name='fulltext[".h($q)."]' value='".h(idx($_GET["fulltext"],$q))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$q]",1,isset($_GET["boolean"][$q]),"BOOL"):''),"</div>\n";}$Ah=adminer()->operators($Tk);foreach(array_merge((array)$_GET["where"],[[]])as$q=>$X){$q=number($q);if(!$X||(("$X[col]$X[val]"!=""||preg_match('~NULL$~',$X["op"]))&&in_array($X["op"],$Ah)))echo"<div>".adminer()->selectColumnInput(" name='where[$q][col]' data-default=''".on('change',($X?'selectFieldChange':'selectAddRow')),$d,$X["col"],"(".'anywhere'.")"),html_select("where[$q][op]",$Ah,$X["op"]," data-default='".h(first($Ah))."'".on('change','selectFirstChange')),"<input type='search' name='where[$q][val]' value='".h($X["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$C,array$d,array$u){print_fieldset("sort",'Sort',$C);$q=0;foreach((array)$_GET["order"]as$v=>$X){if($X!=""){echo"<div>".adminer()->selectColumnInput(" name='order[$q]' data-default=''".on('change','selectFieldChange'),$d,$X),checkbox("desc[$q]",1,isset($_GET["desc"][$v]),'descending')."</div>\n";$q++;}}echo"<div>".adminer()->selectColumnInput(" name='order[$q]' data-default=''".on('change','selectAddRow'),$d),checkbox("desc[$q]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($x){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".h($x?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($pl){echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($pl)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$u){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$d=[];foreach($u
as$t){$Ub=reset($t["columns"]);if($t["type"]!="FULLTEXT"&&$Ub)$d[$Ub]=1;}$d[""]=1;foreach($d
as$v=>$X)json_row($v);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$Sc,array$d){}function
selectColumnsProcess(array$d,array$u){$M=[];$p=[];foreach((array)$_GET["columns"]as$v=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$v]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$p[]=$M[$v];}}return[$M,$p];}function
selectSearchProcess(array$k,array$u,$Tk=null){$I=[];foreach($u
as$q=>$t){if($t["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$q)!="")$I[]=driver()->fulltextSql($q,$t,$_GET["fulltext"][$q],isset($_GET["boolean"][$q]));}$Ah=adminer()->operators($Tk);foreach((array)$_GET["where"]as$v=>$X){$X+=["col"=>"","op"=>first($Ah),"val"=>""];$_GET["where"][$v]=$X;$pb=$X["col"];if(("$pb$X[val]"!=""||preg_match('~NULL$~',$X["op"]))&&in_array($X["op"],$Ah)){if($X["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$_b=[];foreach(($pb!=""?[$pb=>idx($k,$pb,[])]:$k)as$A=>$j){if($pb!=""||is_searchable($j,$X))$_b[]=adminer()->selectSearchCondition(driver()->convertSearch(idf_escape($A),$X,$j),$X["op"],$X["val"]);}$I[]=(count($_b)==1?$_b[0]:($_b?"(".implode(" OR ",$_b).")":"1 = 0"));}}return$I;}function
selectSearchCondition($c,$xh,$X){if(preg_match('~IN$~',$xh))return"$c $xh ".($X!=""?process_in($X):"(NULL)");if($xh=="BETWEEN")return"$c $xh ".process_between($X);if($xh=="SQL")return"$c $X";if(preg_match('~^(I?LIKE) %%$~',$xh,$_))return"$c $_[1] ".q("%$X%");if($xh=="FIND_IN_SET")return"$xh(".q($X).", $c)";return"$c $xh".(!preg_match('~NULL$~',$xh)?" ".q($X):"");}function
selectOrderProcess(array$k,array$u){$I=[];foreach((array)$_GET["order"]as$v=>$X){if($X!="")$I[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`\\\\]|``)+`|"(?:[^"\\\\]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$v])?" DESC".(JUSH=='pgsql'&&idx($k[$X],"null")?" NULLS LAST":""):"");}return$I;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Qd){return
false;}function
selectQueryBuild(array$M,array$Z,array$p,array$C,$x,$D){return"";}function
executeQuery($G,$Ya){return$Ya();}function
messageQuery($G,$rl,$xd=false){restart_session();$Ce=&get_session("queries");if(!idx($Ce,$_GET["db"]))$Ce[$_GET["db"]]=[];if(strlen($G)>1e6)$G=preg_replace('~[\x80-\xFF]+$~','',substr($G,0,1e6))."\n…";$Ce[$_GET["db"]][]=[$G,time(),$rl];$wk="sql-".count($Ce[$_GET["db"]]);$I="<a href='#$wk' class='toggle'>".'SQL command'."</a> ".copy_icon()."\n";if(!$xd&&($_m=driver()->warnings())){$r="warnings-".count($Ce[$_GET["db"]]);$I="<a href='#$r' class='toggle'>".'Warnings'."</a>, $I<div id='$r' class='hidden'>\n$_m</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $I<div id='$wk' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($G,1e4)."</code></pre>".($rl?" <span class='time'>($rl)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($Ce[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
error(){return
error();}function
editRowPrint($R,array$k,$J,$Zl,$G='',$rl=''){echo($G!=""?"<p><code class='sql jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>($rl)</span>\n":"");}function
editFunctions(array$j){$I=($j["null"]?"NULL/":"");$te=isset($_GET["select"])||where($_GET);foreach([driver()->insertFunctions,driver()->editFunctions]as$v=>$be){if(!$v||(!isset($_GET["call"])&&$te)){foreach($be
as$pi=>$X){if(!$pi||preg_match("~$pi~",$j["type"]))$I
.="/$X";}}if($v&&$be&&!preg_match('~set|bool~',$j["type"])&&!is_blob($j))$I
.="/SQL";}if($j["auto_increment"]&&!$te)$I='Auto Increment';return
explode("/",$I);}function
editInput($R,array$j,$b,$Y){if($j["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$b value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$b,$j,$Y,"NULL");return"";}function
editHint($R,array$j,$Y){return"";}function
processInput(array$j,$Y,$o=""){if($o=="SQL")return$Y;$A=$j["field"];$I=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$o))$I="$o()";elseif(preg_match('~^current_(date|timestamp)$~',$o))$I=$o;elseif(preg_match('~^([+-]|\|\|)$~',$o))$I=idf_escape($A)." $o $I";elseif(preg_match('~^[+-] interval$~',$o))$I=idf_escape($A)." $o ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$I);elseif(preg_match('~^(addtime|subtime|concat)$~',$o))$I="$o(".idf_escape($A).", $I)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$o))$I="$o($I)";return
unconvert_field($j,$I);}function
dumpOutput(){$I=['text'=>'open','file'=>'save'];if(function_exists('gzencode'))$I['gz']='gzip';return$I;}function
dumpFormat(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
dumpPrint(){}function
dumpDatabase($g){}function
dumpTable($R,$Jk,$sf=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Jk)dump_csv(array_keys(fields($R)));}else{if($sf==2){$k=[];foreach(fields($R)as$A=>$j)$k[]=idf_escape($A)." ".full_type_sql($j);$Mb="CREATE TABLE ".table($R)." (".implode(", ",$k).")";}else$Mb=create_sql($R,$_POST["auto_increment"],$Jk);set_utf8mb4($Mb);if($Jk&&$Mb){if(($Jk=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$sf==1)echo"DROP ".($sf==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($sf==1)$Mb=remove_definer($Mb);echo"$Mb;\n\n";}}}function
dumpData($R,$Jk,$G,array$M=[],array$Z=[],array$p=[],array$C=[]){if($Jk){$ig=(JUSH=="sqlite"?0:1048576);$k=[];$Ke=false;if($_POST["format"]=="sql"){if($Jk=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($R).";\n";$k=fields($R);if(JUSH=="mssql"){foreach($k
as$j){if($j["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Ke=true;break;}}}}$H=($G!=""?connection()->query($G,1):driver()->select($R,($M?:["*"]),$Z,$p,$C,0));if($H){$df="";$Ua="";$_f=[];$ce=[];$Th="";$Lk="";$Ad=($R!=''?'fetch_assoc':'fetch_row');$Lb=0;while($J=$H->$Ad()){if(!$_f){$sm=[];foreach($J
as$X){$j=$H->fetch_field();if(idx($k[$j->name],'generated')){$ce[$j->name]=true;continue;}$_f[]=$j->name;if(JUSH=="pgsql"&&isset($k[$j->name])&&is_identity_always($k[$j->name]))$Th=" OVERRIDING SYSTEM VALUE";$v=idf_escape($j->name);$sm[]="$v = VALUES($v)";}$Lk=($Jk=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$sm):"").";\n";}foreach($J
as$v=>$X){if(is_array($X))$J[$v]=json_encode($X,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}if($_POST["format"]!="sql"){if($Jk=="table"){dump_csv($_f);$Jk="INSERT";}dump_csv($J);}else{if(!$df)$df="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$_f)).")$Th VALUES";foreach($J
as$v=>$X){if($ce[$v]){unset($J[$v]);continue;}$j=$k[$v];$J[$v]=($X===null?"NULL":($X===false?0:unconvert_field($j,preg_match(number_type(),$j["type"])&&!preg_match('~\[~',$j["full_type"])&&is_numeric($X)?$X:(!is_blob($j)||is_utf8($X)?q($X):driver()->quoteBinary($X)))));}$Ej=($ig?"\n":" ")."(".implode(",\t",$J).")";if(!$Ua)$Ua=$df.$Ej;elseif(JUSH=='mssql'?$Lb%1000!=0:strlen($Ua)+4+strlen($Ej)+strlen($Lk)<$ig)$Ua
.=",$Ej";else{echo$Ua.$Lk;$Ua=$df.$Ej;}}$Lb++;}if($Ua)echo$Ua.$Lk;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";else
dump_csv([connection()->error]);if($Ke)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Je){return
friendly_url($Je!=""?$Je:(SERVER?:"localhost"));}function
dumpHeaders($Je,$Mg=false){$Sh=$_POST["output"];$sd=(preg_match('~sql~',$_POST["format"])?"sql":($Mg?"tar":"csv"));header("Content-Type: ".($Sh=="gz"?"application/x-gzip":($sd=="tar"?"application/x-tar":($sd=="sql"||$Sh!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($Sh=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$sd;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($Fg){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$bh=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$bh)<0?h($bh):"").version_iframe()."</a>","</span></h1>\n";if($Fg=="auth"){$Sh="";foreach((array)$_SESSION["pwds"]as$um=>$dk){foreach($dk
as$N=>$nm){$A=h(get_setting("vendor-$um-$N")?:get_driver($um));foreach($nm
as$V=>$E){if($A&&$E!==null){$dc=$_SESSION["db"][$um][$N][$V];foreach(($dc?array_keys($dc):[""])as$g)$Sh
.="<li><a href='".h(auth_url($um,$N,$V,$g))."'>($A) ".h("$V@").($N!=""?adminer()->serverName($N):"").h($g!=""?" - $g":"")."</a>\n";}}}}if($Sh)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$Sh</ul>\n";}else{$T=[];if($_POST&&!$Fg&&DB!="")connection()->select_db(DB);if($_GET["ns"]!==""&&!$Fg&&DB!="")$T=table_status('',true);adminer()->syntaxHighlighting($T);adminer()->databasesPrint($Fg);$ga=[];if(DB==""||!$Fg){if(support("sql")){$ga['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ga['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ga['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Qe=$_GET["ns"]!==""&&!$Fg&&DB!="";if($Qe&&function_exists('Adminer\alter_table'))$ga['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";$ga=adminer()->menuActions($ga,$Fg);echo($ga?"<p class='links'>\n".implode("\n",$ga)."\n":"");$Oj=[];if($Qe){if(function_exists('Adminer\alter_table'))$Oj[]=['Create table',ME."create="];if(support("view"))$Oj[]=['Create view',ME."view="];$Oj[]=['Database schema',ME."schema="];$_d=['routine'=>['routines','Routines'],'sequence'=>['sequences','Sequences'],'type'=>['user-types','User types'],'event'=>['events','Events'],];foreach($_d
as$zd=>$Nj){if(support($zd))$Oj[]=[$Nj[1],substr(ME,0,-1)."#$Nj[0]"];}}if(DB!=""&&!$Fg&&support("extension"))$Oj[]=['Extensions',preg_replace('~&ns=[^&]*~','',ME)."ns=#extensions"];$ck=['privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',];foreach($ck
as$v=>$X){if(support($v))$Oj[]=[$X,preg_replace('~&(db|ns)=[^&]*~','',ME)."$v="];}echo($Oj?script("jumpSections.unshift(...".json_encode($Oj,JSON_UNESCAPED_UNICODE).");"):"");if($Qe){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.3.1+9a5eab2e",true);$Ig=preg_replace('~<(?=/script)~i','<\\',Driver::jushModule());echo($Ig?script("addEventListener('DOMContentLoaded', () => {\n$Ig\n});"):"");if(support("sql")){echo"<script".nonce().">\n";if($T){$Uf=[];foreach($T
as$R=>$U)$Uf[]=js_escape_re($R);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$Uf).')(?!\$)\b/g',false);$yk=["sql","check","event","procedure","trigger","view","type","table","processlist"];if(support("routine")&&array_intersect_key($_GET,array_flip($yk))){foreach(routines()as$J)json_row(js_escape(ME).'function='.url_escape($J["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($J["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(array_intersect_key($_GET,array_flip(["sql","check","event","procedure","trigger","view"]))){$Ek=(isset($_GET["trigger"])?['INSERT INTO','UPDATE','DELETE FROM']:(isset($_GET["check"])?[]:(isset($_GET["view"])?['SELECT']:null)));$Fa=Driver::jushAutocomplete($T,$Ek);echo($Fa?"addEventListener('DOMContentLoaded', () => { autocompleter = $Fa; });\n":"");}}echo"</script>\n";}echo
script("syntaxHighlighting('".doc_version()."', '".connection()->flavor."');");}function
databasesPrint($Fg){if(support("single_db"))return;$f=adminer()->databases();if(DB&&$f&&!in_array(DB,$f))array_unshift($f,DB);echo"<form>\n<p id='dbs'>\n";hidden_fields_get();$ac=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".'Database'."'>".'DB'.": ".($f?html_select("db",[""=>""]+group_system($f),DB,$ac):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($f?" class='hidden'":"").">\n";foreach(["import","sql","schema","dump","privileges"]as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
menuActions(array$ga,$Fg){return$ga;}function
tablesPrint(array$T){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($T
as$R=>$P){$R="$R";$A=adminer()->tableName($P);if($A!=""&&!$P["dependent"])echo'<li><a href="'.h(ME).'select='.url_escape($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select hover")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($R).'"'.bold(in_array($R,[$_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$A</a>":"<span>$A</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($r){return
kill_process($r);}function
formatSizeValue($X){return
format_number($X);}}class
Plugins{private
static$append=['dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true];var$plugins;var$disabled=[];var$drivers=[];var$driverFiles=[];var$error='';private$hooks=[];function
__construct($xi){$Fc=SqlDriver::$drivers;$ze=" href='https://www.adminer.org/plugins/#use'".target_blank();if($xi===null){$xi=[];$Na="adminer-plugins";if(is_dir($Na)){foreach(glob("$Na/*.php")as$l){$Fd=SqlDriver::$drivers;$this->includeOnce($l);foreach(array_diff_key(SqlDriver::$drivers,$Fd)as$r=>$A)$this->driverFiles[$r]=$l;}}if(file_exists("$Na.php")){$Se=$this->includeOnce("$Na.php");if(is_array($Se)){foreach($Se
as$v=>$ui)$xi[is_object($ui)?get_class($ui):$v]=$ui;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Na.php</b>",$ze)."<br>";}foreach(get_declared_classes()as$nb){if(!$xi[$nb]&&(preg_match('~^Adminer\w~i',$nb)||is_subclass_of($nb,'Adminer\Plugin'))){$gj=new
\ReflectionClass($nb);$Db=$gj->getConstructor();if($Db&&$Db->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$ze,"<b>$nb</b>","<b>$Na.php</b>")."<br>";else$xi[$nb]=new$nb;}}}$if=array_filter($xi,function($ui){return!is_object($ui);});if($if){$this->error
.=sprintf('Every plugin must <a%s>be an object</a>.',$ze)."<br>";$xi=array_diff_key($xi,$if);}$Pk=get_setting("plugins","adminer_config");foreach($xi
as$v=>$ui){if($ui
instanceof
Plugin&&$ui::$optional){$Ck=idx($Pk,get_class($ui));if($Ck!==null?!$Ck:!$ui->enabled){$this->disabled[]=$ui;unset($xi[$v]);}}}$this->drivers=array_diff_key(SqlDriver::$drivers,$Fc);$this->plugins=$xi;$ja=new
Adminer;$xi[]=$ja;$gj=new
\ReflectionObject($ja);foreach($gj->getMethods()as$Cg){foreach($xi
as$ui){$A=$Cg->getName();if(method_exists($ui,$A))$this->hooks[$A][]=$ui;}}}function
includeOnce($l){return
include_once"./$l";}static
function
pluginName($ui){return
preg_replace('~^ Adminer\\\\? ~','',preg_replace('~[A-Z]~',' $0',get_class($ui)));}static
function
describe($ui){$oc=(method_exists($ui,'description')?$ui->description():"");if(!$oc){$gj=new
\ReflectionObject($ui);if(preg_match('~^/[\s*]+(.+?)(?:\s*\*/)?$~m',$gj->getDocComment(),$_))$oc=$_[1];}$Jj=(method_exists($ui,'screenshot')?$ui->screenshot():"");return
h($oc).($Jj?($oc?" ":"")."(<a href='".h($Jj)."'".target_blank().">".'screenshot'."</a>)":"");}static
function
checksum($l){$Ed=str_replace("\r","",file_get_contents($l));$Ed=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$Ed);return
dechex(crc32($Ed));}function
checksums(){$Gd=array_values($this->driverFiles);foreach($this->plugins
as$ui){$gj=new
\ReflectionObject($ui);$Gd[]=$gj->getFileName();}$I=[];foreach($Gd
as$l)$I[basename($l,'.php')]=self::checksum($l);return$I;}static
function
officialChecksums(){return['adminer.js'=>'a0599090','backward-keys'=>'1c017d59','before-unload'=>'9674d220','config'=>'bd5d79e5','dark-switcher'=>'8c201319','database-hide'=>'e304a899','designs'=>'c0a56b18','donation'=>'365aa745','dump-alter'=>'896b579e','dump-bz2'=>'c0d6de78','dump-date'=>'512aad63','dump-json'=>'f8aa6b99','dump-xml'=>'aedd2d3a','dump-zip'=>'96882e78','edit-foreign'=>'d04a0ac8','edit-textarea'=>'a178d7d9','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'e9005cb0','file-upload'=>'e7f2b388','foreign-system'=>'634ce119','frames'=>'b0e1d11a','highlight-codemirror'=>'ada4b569','highlight-monaco'=>'95cf93b3','highlight-prism'=>'19933b19','import-csv'=>'bcecfad2','login-ip'=>'4d174fea','login-otp'=>'66bd013d','login-passkey'=>'a73c386f','login-password-less'=>'e150daac','login-reverse-proxy'=>'24558ea2','login-servers'=>'19c42e45','login-ssl'=>'2b06a5b7','login-table'=>'811f8cef','menu-links'=>'55b9b15c','name-patterns'=>'84c10d09','remote-color'=>'c5e74631','row-numbers'=>'1ad1b6cf','select-email'=>'c33a2661','select-expression'=>'b6a4b3f1','select-foreign'=>'2c6bb6c3','select-image'=>'19dd2499','slugify'=>'7b422f0e','sql-gemini'=>'d65d01df','sql-log'=>'b9aa4ea6','sql-url-length'=>'cd70f1a0','table-indexes-structure'=>'e0b1433','table-structure'=>'f71ec61b','tables-filter'=>'2729bf7','timeout'=>'9e9bce60','version-github'=>'627cadf9','version-noverify'=>'966937e9','clickhouse'=>'79f6dd80','elastic'=>'b7e4a70d','firebird'=>'5f3d9493','igdb'=>'39c028f6','imap'=>'35808394','mongo'=>'2d9cae00','redis'=>'c2f04eae','simpledb'=>'9eb464cf',];}function
__call($A,array$Yh){$ya=[];foreach($Yh
as$v=>$X)$ya[]=&$Yh[$v];$I=null;foreach($this->hooks[$A]as$ui){$Y=call_user_func_array([$ui,$A],$ya);if($Y!==null){if(!self::$append[$A])return$Y;$I=$Y+(array)$I;}}return$I;}}abstract
class
Plugin{static$optional=false;var$enabled=true;protected$translations=[];function
disable(){$this->enabled=false;return$this;}function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($s,$hh=null){$ya=func_get_args();$ya[0]=idx($this->translations[LANG],$s)?:$s;return
call_user_func_array('Adminer\lang_format',$ya);}}class
Password{private$password_hash;private$password_matches=null;function
__construct($li){$this->password_hash=$li;}function
description(){return'Require a password verified by Adminer';}function
credentials(){$E=get_password();return[SERVER,$_GET["username"],($this->passwordMatches($E)&&!password_required()?"":$E)];}function
login($Yf,$E){if($this->passwordMatches($E))return
true;if(!Driver::$passwords||!password_required())return
false;}protected
function
passwordMatches($E){if($this->password_matches===null)$this->password_matches=(function_exists('password_verify')&&password_verify(strval($E),$this->password_hash));return$this->password_matches;}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=["server"=>"MySQL / MariaDB"]+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\mysqli{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach(array$N,$V,$E){mysqli_report(MYSQLI_REPORT_OFF);$yi=$N["port"];$Uc=("$N[host]$yi$N[socket]"=="");$Ak=adminer()->connectSsl();$jm=($Ak&&($Ak['key']||$Ak['cert']||$Ak['ca']||isset($Ak['verify'])));if($jm)$this->ssl_set($Ak['key'],$Ak['cert'],$Ak['ca'],'','');$I=@$this->real_connect((!$Uc?$N["host"]:ini_get("mysqli.default_host")),(!$Uc||$V!=""?$V:ini_get("mysqli.default_user")),(!$Uc||$V.$E!=""?$E:ini_get("mysqli.default_pw")),null,($yi!=""?intval($yi):ini_get("mysqli.default_port")),($yi!=""?null:$N["socket"]),($jm?($Ak['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($I?'':$this->error);}function
set_charset($db){if($this->send_charset($db))return
true;$this->send_charset('utf8');return$this->query("SET NAMES $db");}private
function
send_charset($db){return
adminer()->executeQuery("SET NAMES $db",function()use($db){return
parent::set_charset($db);});}function
select_db($Zb){return
adminer()->executeQuery("USE ".idf_escape($Zb),function()use($Zb){return
parent::select_db($Zb);});}function
query($G,$sj=MYSQLI_STORE_RESULT){return
adminer()->executeQuery($G,function()use($G,$sj){return
parent::query($G,$sj);});}function
multi_query($G){return
adminer()->executeQuery($G,function()use($G){return
parent::multi_query($G);});}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}function
inTransaction(){return
false;}function
begin(){return(method_exists($this,'begin_transaction')?adminer()->executeQuery("BEGIN",function(){return$this->begin_transaction();}):!!$this->query("BEGIN"));}function
commit($Ld=0,$A=null){return
adminer()->executeQuery("COMMIT",function(){return
parent::commit();});}function
rollback($Ld=0,$A=null){return
adminer()->executeQuery("ROLLBACK",function(){return
parent::rollback();});}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach(array$N,$V,$E){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable the %s or %s extension.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$yi="$N[port]$N[socket]";$A=$N["host"].($yi!=""?":$yi":"");$this->link=@mysql_connect(($A!=""?$A:ini_get("mysql.default_host")),($A.$V!=""?$V:ini_get("mysql.default_user")),($A.$V.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($db){return$this->send_charset($db)||$this->send_charset('utf8');}private
function
send_charset($db){return
adminer()->executeQuery("SET NAMES $db",function()use($db){return
mysql_set_charset($db,$this->link);});}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Zb){return
adminer()->executeQuery("USE ".idf_escape($Zb),function()use($Zb){return
mysql_select_db($Zb,$this->link);});}function
query($G,$Ql=false){$H=adminer()->executeQuery($G,function()use($G,$Ql){return@($Ql?mysql_unbuffered_query($G,$this->link):mysql_query($G,$this->link));});$this->error="";if(!$H){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($H===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($H);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=mysql_num_rows($H);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$I=mysql_fetch_field($this->result,$this->offset++);$I->orgtable=$I->table;$I->native_type=idx(["string"=>"varchar","real"=>"double"],$I->type,$I->type);return$I;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach(array$N,$V,$E){$B=[\PDO::MYSQL_ATTR_LOCAL_INFILE=>false];if(isset($_GET["select"]))$B[1012]=false;$Ak=adminer()->connectSsl();if($Ak){if($Ak['key'])$B[\PDO::MYSQL_ATTR_SSL_KEY]=$Ak['key'];if($Ak['cert'])$B[\PDO::MYSQL_ATTR_SSL_CERT]=$Ak['cert'];if($Ak['ca'])$B[\PDO::MYSQL_ATTR_SSL_CA]=$Ak['ca'];if(isset($Ak['verify']))$B[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$Ak['verify'];}$Fe=$N["host"];$yi=$N["port"];$mk=$N["socket"];return$this->dsn("mysql:charset=utf8".($Fe!=""?";host=$Fe":'').($yi!=""?";port=$yi":($mk!=""?";unix_socket=$mk":"")),$V,$E,$B);}function
set_charset($db){return$this->query("SET NAMES $db");}function
select_db($Zb){return$this->query("USE ".idf_escape($Zb));}function
query($G,$Ql=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Ql);return
parent::query($G,$Ql);}}}class
Driver
extends
SqlDriver{static$extensions=["MySQLi","MySQL","PDO_MySQL"];static$jush="sql";static$serverSocket=true;var$unsigned=["unsigned","zerofill","unsigned zerofill"];var$functions=["char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper"];var$grouping=["avg","count","count distinct","group_concat","max","min","sum"];var$partitionBy=["HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST"];function
operators($Tk){return["=","<",">","<=",">=","!=","&","LIKE","LIKE %%","REGEXP","IN","BETWEEN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL"];}static
function
connect($N,$V,$E){$e=parent::connect($N,$V,$E);if(is_string($e)){if(function_exists('iconv')&&!is_utf8($e)&&strlen($Ej=iconv("windows-1252","utf-8//IGNORE",$e))>strlen($e))$e=$Ej;return$e;}$e->set_charset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");$e->flavor=(preg_match('~MariaDB~',$e->server_info)?'maria':'mysql');add_driver(DRIVER,($e->flavor=='maria'?"MariaDB":"MySQL"));return$e;}function
__construct(Db$e){parent::__construct($e);$this->types=['Numbers'=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21],'Date and time'=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4],'Strings'=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295],'Lists'=>["enum"=>65535,"set"=>64],'Binary'=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295],'Geometry'=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0],];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if(min_version('5.7.8',10.2,$e))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$e)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$e)){$this->types['Network']["inet6"]=39;if(min_version('','10.10',$e))$this->types['Network']["inet4"]=15;}if(min_version(9,11.7,$e))$this->types['Numbers']["vector"]=16383;if(min_version(5.7,10.2,$e))$this->generated=["STORED","VIRTUAL"];}function
unconvertFunction(array$j){return(preg_match("~binary~",$j["type"])?"<code class='jush-sql'>UNHEX</code>":($j["type"]=="bit"?doc_link(['sql'=>'bit-value-literals.html'],"<code>b''</code>"):($j["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geom|point|linestring|polygon~",$j["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$K,array$Ji){$d=array_keys(reset($K));$Fi="INSERT INTO ".table($R)." (".implode(", ",$d).") VALUES\n";$sm=[];foreach($d
as$v)$sm[$v]="$v = VALUES($v)";$Lk="\nON DUPLICATE KEY UPDATE ".implode(", ",$sm);$sm=[];$w=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($sm&&(strlen($Fi)+$w+strlen($Y)+strlen($Lk)>1e6)){if(!queries($Fi.implode(",\n",$sm).$Lk))return
false;$sm=[];$w=0;}$sm[]=$Y;$w+=strlen($Y)+2;}return
queries($Fi.implode(",\n",$sm).$Lk);}function
slowQuery($G,$sl){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$sl FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$_))return"$_[1] /*+ MAX_EXECUTION_TIME(".($sl*1000).") */ $_[2]";}}function
convertColumn($s,array$j){if(preg_match("~binary~",$j["type"]))return"HEX($s)";if($j["type"]=="bit")return"BIN($s + 0)";if($j["type"]=="vector")return($this->conn->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."($s)";if(preg_match("~geom|point|linestring|polygon~",$j["type"]))return(min_version(8)?"ST_":"")."AsWKT($s)";return"";}function
convertSearch($s,array$X,array$j){return($this->convertColumn($s,$j)?:(preg_match('~'.text_type().'~',$j["type"])&&!preg_match("~^utf8~",$j["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($s USING ".charset($this->conn).")":$s));}function
typeName(\stdClass$j){$A=parent::typeName($j);if($A!=""){$Pl=["TINY"=>"tinyint","SHORT"=>"smallint","LONG"=>"int","INT24"=>"mediumint","LONGLONG"=>"bigint","NEWDECIMAL"=>"decimal","VAR_STRING"=>"varchar","STRING"=>"char",];return
idx($Pl,$A,strtolower($A));}$Pl=["decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",];$I=idx($Pl,$j->type,"");return($j->charsetnr==63?str_replace(["text","varchar","char"],["blob","varbinary","binary"],$I):$I);}function
quoteBinary($Ej){return"X".q(bin2hex($Ej));}function
md5($c,array$j){if(is_blob($j)||preg_match('~'.text_type().'~',$j["type"]))return"MD5(".(is_blob($j)||preg_match("~^utf8~",$j["collation"])?$c:"CONVERT($c USING ".charset($this->conn).")").")";}function
warnings(){$H=$this->conn->query("SHOW WARNINGS");if($H&&$H->num_rows){ob_start();print_select_result($H);return
ob_get_clean();}}function
errorLine($G){return(preg_match('~ at line (\d+)$~',$this->conn->error,$_)?(int)$_[1]:0);}function
tableHelp($A,$sf=false){$ag=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($ag?"$A-table/":str_replace("_","-",$A)."-table.html"));if(DB=="sys")return($ag?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$A)).".html"));if(DB=="mysql")return($ag?"mysql$A-table/":"system-schema.html");}function
partitionsInfo($R){$Wd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$H=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Wd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=($H?$H->fetch_row():null);if(!$J)return[];$I=[];list($I["partition_by"],$I["partition"],$I["partitions"])=$J;$hi=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Wd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$I["partition_names"]=array_keys($hi);$I["partition_values"]=array_values($hi);return$I;}function
checkConstraints($R){$I=parent::checkConstraints($R);return($this->conn->flavor=='maria'?$I:array_map('stripslashes',$I));}function
hasCStyleEscapes(){static$Va;if($Va===null){$xk=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Va=(strpos($xk,'NO_BACKSLASH_ESCAPES')===false);}return$Va;}function
hasEstimatedRows(){return
true;}function
isSystem($g,$L=""){return
information_schema($g,$L)||in_array($g,["mysql","sys"]);}function
lineComment(){return"#|-- ";}function
engines(){$I=[];foreach(get_rows("SHOW ENGINES")as$J){if(preg_match("~YES|DEFAULT~",$J["Support"]))$I[]=$J["Engine"];}return$I;}function
indexAlgorithms(array$Tk){return(preg_match('~^(MEMORY|NDB)$~',$Tk["Engine"])?["HASH","BTREE"]:[]);}}function
idf_escape($s){return"`".str_replace("`","``",$s)."`";}function
table($s){return
idf_escape($s);}function
get_databases($Nd){$I=get_session("dbs");if($I===null){$G="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Bk=microtime(true);$I=($Nd?slow_query($G):get_vals($G));if(microtime(true)-$Bk>0.1){restart_session();set_session("dbs",$I);stop_session();}}return$I;}function
limit($G,$Z,$x,$oh=0,$Vj=" "){return" $G$Z".($x?$Vj."LIMIT $x".($oh?" OFFSET $oh":""):"");}function
limit1($R,$G,$Z,$Vj="\n"){return
limit($G,$Z,1,0,$Vj);}function
db_collation($g,array$sb){static$I;if($I===null)$I=get_key_vals("SELECT SCHEMA_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA");return$I[$g];}function
logged_user(){return
get_val("SELECT CURRENT_USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$f){$I=[];foreach($f
as$g)$I[$g]=count(get_vals("SHOW TABLES IN ".idf_escape($g)));return$I;}function
table_status($A="",$yd=false){$I=[];$G="SELECT ENGINE AS Engine, TABLE_NAME AS Name, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");$L=[];foreach(($yd?[]:get_rows($G))as$J)$L[$J["Name"]]=$J;$Ii=null;foreach(get_rows($yd?$G:"SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):""))as$J){$Ph=idx($L,$J["Name"]);if($Ph){if($J["Comment"]!==$Ph["Comment"]&&$J["Comment"]!==$Ii)$J["Error"]=$J["Comment"];$Ii=$J["Comment"];$J["Comment"]=$Ph["Comment"];$J["Engine"]=$Ph["Engine"];}if($J["Engine"]=="InnoDB")$J["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$J["Comment"]);if(!isset($J["Engine"]))$J["Comment"]="";if($A!="")$J["Name"]=$A;$I[$J["Name"]]=$J;}return$I;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
parse_type($Yd){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$Yd,$_);return[$_[1],$_[2],ltrim($_[3].$_[4])];}function
fields($R){$ag=(connection()->flavor=='maria');$I=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$J){$j=$J["COLUMN_NAME"];$U=$J["COLUMN_TYPE"];$de=$J["GENERATION_EXPRESSION"];$vd=$J["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$vd,$ce);list($Ol,$w,$Xl)=parse_type($U);$h=$J["COLUMN_DEFAULT"];$hc=false;if($h!=""){$Xi=$ag;if($ag)$hc=!preg_match("~^('(?:[^'\\\\]|\\\\.|'')*'|NULL|[-+]?[\\d.]+)$~s",$h);elseif(preg_match('~text|json~',$Ol)||preg_match('~\bDEFAULT_GENERATED\b~',$vd)){$h=stripslashes($h);$Xi=preg_match("~^(_\\w+)?('(?:[^'\\\\]|\\\\.|'')*')$~s",$h,$_);if($Xi)$h=$_[2];else$hc=true;}elseif(preg_match("~^[bx]'[\\da-f]*'$~i",$h))$hc=true;if($Xi)$h=($h=="NULL"?null:(preg_match("~^'.*'$~s",$h)?stripslashes(idf_unescape($h)):$h));if(preg_match('~binary~',$Ol)&&preg_match("~^(?:0x(\\w*)|x'(\\w*)')$~",$h,$_)){$h=pack("H*",$_[1].idx($_,2));$hc=false;}}$I[$j]=["field"=>$j,"full_type"=>$U,"type"=>$Ol,"length"=>$w,"unsigned"=>$Xl,"default"=>($ce?($ag?$de:stripslashes($de)):$h),"default_sql"=>(!$ce&&$hc),"null"=>($J["IS_NULLABLE"]=="YES"),"auto_increment"=>($vd=="auto_increment"),"on_update"=>(preg_match('~\bon update current_timestamp\b~i',$vd)?"CURRENT_TIMESTAMP":""),"collation"=>$J["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$J[PRIVILEGES],where,order")),"comment"=>$J["COLUMN_COMMENT"],"primary"=>($J["COLUMN_KEY"]=="PRI"),"generated"=>($ce[1]=="PERSISTENT"?"STORED":$ce[1]),];}return$I;}function
indexes($R,$Bb=null){$I=[];foreach(get_rows("SHOW INDEX FROM ".table($R),$Bb)as$J){$A=$J["Key_name"];$I[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($J["Index_type"]=="FULLTEXT"?"FULLTEXT":($J["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$J["Index_type"])?$J["Index_type"]:"INDEX"):"UNIQUE")));$I[$A]["columns"][]=$J["Column_name"];$I[$A]["lengths"][]=($J["Index_type"]=="SPATIAL"?null:$J["Sub_part"]);$I[$A]["descs"][]=null;$I[$A]["algorithm"]=$J["Index_type"];}return$I;}function
foreign_keys($R){static$pi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$I=[];$Nb=get_val("SHOW CREATE TABLE ".table($R),1);if($Nb){preg_match_all("~CONSTRAINT ($pi) FOREIGN KEY ?\\(((?:$pi,? ?)+)\\) REFERENCES ($pi)(?:\\.($pi))? \\(((?:$pi,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Nb,$cg,PREG_SET_ORDER);foreach($cg
as$_){preg_match_all("~$pi~",$_[2],$qk);preg_match_all("~$pi~",$_[5],$il);$I[idf_unescape($_[1])]=["db"=>idf_unescape($_[4]!=""?$_[3]:$_[4]),"table"=>idf_unescape($_[4]!=""?$_[4]:$_[3]),"source"=>array_map('Adminer\idf_unescape',$qk[0]),"target"=>array_map('Adminer\idf_unescape',$il[0]),"on_delete"=>($_[6]?:"RESTRICT"),"on_update"=>($_[7]?:"RESTRICT"),];}}return$I;}function
view($A){return["select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($A),1))];}function
collations(){$I=[];foreach(get_rows("SHOW COLLATION")as$J)$I[$J["Charset"]][]=$J["Collation"];ksort($I);foreach($I
as$v=>$X)sort($I[$v]);return$I;}function
information_schema($g,$L=""){return($g=="information_schema")||(min_version(5.5)&&$g=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($g,$rb){return
queries("CREATE DATABASE ".idf_escape($g).($rb?" COLLATE ".q($rb):""));}function
drop_databases(array$f){$I=apply_queries("DROP DATABASE",$f,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$I;}function
rename_database($A,$rb){$I=false;if(create_database($A,$rb)){$T=[];$xm=[];foreach(tables_list()as$R=>$U){if($U=='VIEW')$xm[]=$R;else$T[]=$R;}$I=(!$T&&!$xm)||move_tables($T,$xm,$A);drop_databases($I?[DB]:[]);}return$I;}function
auto_increment(){$Ea=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$t){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$t["columns"],true)){$Ea="";break;}if($t["type"]=="PRIMARY")$Ea=" UNIQUE";}}return" AUTO_INCREMENT$Ea";}function
alter_table($R,$A,array$k,array$Pd,$wb,$Xc,$rb,$Da,$gi){$ta=[];foreach($k
as$j){if($j[1]){$h=$j[1][3];if(preg_match('~ GENERATED~',$h)){$j[1][3]=(connection()->flavor=='maria'?"":$j[1][2]);$j[1][2]=$h;}$ta[]=($R!=""?($j[0]!=""?"CHANGE ".idf_escape($j[0]):"ADD"):" ")." ".implode($j[1]).($R!=""?$j[2]:"");}else$ta[]="DROP ".idf_escape($j[0]);}$ta=array_merge($ta,$Pd);$P=($wb!==null?" COMMENT=".q($wb):"").($Xc?" ENGINE=".q($Xc):"").($rb?" COLLATE ".q($rb):"").($Da!=""?" AUTO_INCREMENT=$Da":"");if($gi){$hi=[];if($gi["partition_by"]=='RANGE'||$gi["partition_by"]=='LIST'){foreach($gi["partition_names"]as$v=>$X){$Y=$gi["partition_values"][$v];$hi[]="\n  PARTITION ".idf_escape($X)." VALUES ".($gi["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $gi[partition_by]($gi[partition])";if($hi)$P
.=" (".implode(",",$hi)."\n)";elseif($gi["partitions"])$P
.=" PARTITIONS ".(+$gi["partitions"]);}elseif($gi===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$ta)."\n)$P");if($R!=$A)$ta[]="RENAME TO ".table($A);if($P)$ta[]=ltrim($P);return($ta?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$ta)):true);}function
alter_indexes($R,$ta){$bb=[];foreach($ta
as$X)$bb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$bb));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$xm){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$xm)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$xm,$il){$lj=[];foreach($T
as$R)$lj[]=table($R)." TO ".idf_escape($il).".".table($R);if(!$lj||queries("RENAME TABLE ".implode(", ",$lj))){$lc=[];foreach($xm
as$R)$lc[table($R)]=view($R);connection()->select_db($il);$g=idf_escape(DB);foreach($lc
as$A=>$wm){if(!queries("CREATE VIEW $A AS ".str_replace(" $g."," ",$wm["select"]))||!queries("DROP VIEW $g.$A"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$xm,$il){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$A=($il==DB?table("copy_$R"):idf_escape($il).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($R))||!queries("INSERT INTO $A SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){$Gl=$J["Trigger"];list($hd,$jh)=trigger_event($J);if(!queries("CREATE TRIGGER ".($il==DB?idf_escape("copy_$Gl"):idf_escape($il).".".idf_escape($Gl))." $J[Timing] $hd".($jh!=""?" $jh":"")." ON $A FOR EACH ROW\n$J[Statement];"))return
false;}}foreach($xm
as$R){$A=($il==DB?table("copy_$R"):idf_escape($il).".".table($R));$wm=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $wm[select]"))return
false;}return
true;}function
trigger_event(array$J){$jd=explode(",",$J["Event"]);$I=[];foreach(["DELETE","INSERT","UPDATE"]as$hd){if(in_array($hd,$jd))$I[]=$hd;}$I=implode(" OR ",$I);if(in_array("UPDATE",$jd)&&min_version('','12.0.1')&&preg_match('~\s(?:BEFORE|AFTER)\s+(.+?)\s+ON\s~is',get_val("SHOW CREATE TRIGGER ".idf_escape($J["Trigger"]),2),$_)&&preg_match('~\bOF\s+(.+)~is',$_[1],$jh))return["$I OF",$jh[1]];return[$I,""];}function
trigger($A,$R){if($A=="")return[];$K=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($A));$I=reset($K);if($I)list($I["Event"],$I["Of"])=trigger_event($I);return($I?:[]);}function
triggers($R){$I=[];foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){list($hd)=trigger_event($J);$I[$J["Trigger"]]=[$J["Timing"],$hd];}return$I;}function
trigger_options(){return["Timing"=>["BEFORE","AFTER"],"Event"=>(min_version('','12.0.1')?["INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",]:["INSERT","UPDATE","DELETE"]),"Type"=>["FOR EACH ROW"],];}function
routine($A,$U){$K=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, COLLATION_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($A)."
ORDER BY ORDINAL_POSITION");$k=[];foreach($K
as$J){$Yd=$J["DTD_IDENTIFIER"];list($Ol,$w,$Xl)=parse_type($Yd);$k[]=["field"=>$J["PARAMETER_NAME"],"type"=>$Ol,"length"=>$w,"unsigned"=>$Xl,"null"=>true,"full_type"=>$Yd,"inout"=>($U=="FUNCTION"?"":$J["PARAMETER_MODE"]),"collation"=>$J["COLLATION_NAME"],];}$I=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	ROUTINE_DEFINITION definition,
	LOWER(EXTERNAL_LANGUAGE) language,
	IF(DEFINER = CURRENT_USER(), '', DEFINER) definer,
	IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC', 'NOT DETERMINISTIC') is_deterministic,
	SQL_DATA_ACCESS data_access,
	CONCAT('SQL SECURITY ', SECURITY_TYPE) security
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($A))->fetch_assoc();if(!$I)return[];if($I['language']!='javascript'&&preg_match('~^[^\'"]+(?=[\'"])~',$I['definition'],$_)){$Mb=get_val("SHOW CREATE $U ".idf_escape($A),2);$F=strpos($Mb,"\n$_[0]");if($F)$I['definition']=substr($Mb,$F+1);}$I['options']=["DEFINER"=>$I['definer'],"DETERMINISTIC"=>$I['is_deterministic'],"SQL_DATA_ACCESS"=>$I['data_access'],"SQL_SECURITY"=>$I['security'],"COMMENT"=>$I['comment'],];if($k&&$k[0]['field']=='')$I['returns']=array_shift($k);$I['fields']=$k;return$I;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return(min_version(9,99)?["sql"=>"sql","javascript"=>"js"]:[]);}function
routine_options($xj){return["DEFINER"=>[],"DETERMINISTIC"=>["NOT DETERMINISTIC","DETERMINISTIC"],"SQL_DATA_ACCESS"=>["CONTAINS SQL","NO SQL","READS SQL DATA","MODIFIES SQL DATA"],"SQL_SECURITY"=>["SQL SECURITY DEFINER","SQL SECURITY INVOKER"],"COMMENT"=>[],];}function
routine_id($A,array$J){return
idf_escape($A);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$e,$G){return$e->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$G);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$Da,$Jk){$I=get_val("SHOW CREATE TABLE ".table($R),1);$I=preg_replace_callback('~(?<=^CREATE TABLE )`(?:[^`]|``)+`~',function()use($R){return
table($R);},$I);if(!$Da)$I=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$I);return$I;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Zb,$Jk=""){$A=idf_escape($Zb);$I="";if(preg_match('~CREATE~',$Jk)&&($Mb=get_val("SHOW CREATE DATABASE $A",1))){set_utf8mb4($Mb);if($Jk=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $A;\n";$I
.="$Mb;\n";}return$I."USE $A";}function
trigger_sql($R){$I="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$J){list($J["Event"],$J["Of"])=trigger_event($J);$I
.="\n".create_trigger(" ON ".table($J["Table"]),$J+["Type"=>"FOR EACH ROW"]).";\n";}return$I;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$j){return
driver()->convertColumn(idf_escape($j["field"]),$j);}function
unconvert_field(array$j,$I){if(preg_match("~binary~",$j["type"]))$I="UNHEX($I)";if($j["type"]=="bit")$I="CONVERT(b$I, UNSIGNED)";if($j["type"]=="vector")$I=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($I)";if(preg_match("~geom|point|linestring|polygon~",$j["type"])){$Fi=(min_version(8)?"ST_":"");$I=$Fi."GeomFromText($I, $Fi"."SRID(".idf_escape($j["field"])."))";}return$I;}function
support($zd){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$zd);}function
kill_process($r){return
queries("KILL ".number($r));}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($ud=false){return[];}function
type_values($r){return"";}function
type_definition($r){return["kind"=>"","definition"=>""];}function
schemas(){return[];}function
get_schema(){return"";}function
set_schema($L,$Bb=null){return
true;}function
schema_comment($L){return"";}function
set_schema_comment($L,$wb){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(isset($_GET["db"])?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));if(isset($_GET["manifest"])){header("Content-Type: application/manifest+json; charset=utf-8");header("Cache-Control: no-cache");echo
json_encode(adminer()->manifest(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);exit;}function
page_header($ul,$i="",$Ta=[],$vl="",$eh=false,$Bc=""){if($eh){header("HTTP/1.1 404 Not Found");$i=($i?:'Not found.');}page_headers();if(is_ajax()&&$i){page_messages($i);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$wl=$ul.($vl!=""?": $vl":"");$xl=strip_tags($wl.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'en\' dir=\'ltr\' class=\'ltr nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$xl,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.3.1+9a5eab2e"),'">
';$Rb=adminer()->css();if(is_int(key($Rb)))$Rb=array_fill_keys($Rb,'light');$re=in_array('light',$Rb)||in_array('',$Rb);$pe=in_array('dark',$Rb)||in_array('',$Rb);$Vb=($re?($pe?null:false):($pe?:null));$rg=" media='(prefers-color-scheme: dark)'";if($Vb!==false)echo"<link rel='stylesheet'".($Vb?"":$rg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.3.1+9a5eab2e")."'>\n";echo"<meta name='color-scheme' content='".($Vb===null?"light dark":($Vb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.3.1+9a5eab2e");if(adminer()->head($Vb))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.svg&version=6.3.1+9a5eab2e")."'>\n";if(adminer()->manifest())echo"<link rel='manifest' href='".h(preg_replace('~\?.*~','',ME)."?manifest=")."' crossorigin='use-credentials'>\n";foreach($Rb
as$em=>$Gg){$b=($Gg=='dark'&&!$Vb?$rg:($Gg=='light'&&$pe?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$b href='".h($em)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape('You are offline.')."';
const numberFormat = '".js_escape('#,##0')."';
const numberDigits = '".js_escape('0123456789')."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".'Menu'."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($Ta!==null){$wf=(class_exists('Adminer\Db')&&connection());$y=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb">'.($wf?jump_input():'').'<a href="'.h($y?:".").'">'.get_driver(DRIVER).'</a> » ';$y=substr(preg_replace('~&(db|ns)=[^&]*~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ta===false)echo"$N\n";else{echo"<a href='".h($y.(DB!=""&&support("single_db")?"&db=":""))."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";$Nj="";if(is_string($Ta)){$Nj=$Ta;$Ta=[];}if($_GET["ns"]!=""||(DB!=""&&is_array($Ta))){$bc="$y&db=".url_escape(DB).(support("scheme")?"&ns=":"").(support("single_table")?"&select=":"");echo'<a href="'.h($bc.($_GET["ns"]==""?$Nj:"")).'">'.h(DB).'</a> » ';}if(is_array($Ta)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1).$Nj).'">'.h($_GET["ns"]).'</a> » ';foreach($Ta
as$v=>$X){$nc=(is_array($X)?$X[1]:h($X));if($nc!="")echo"<a href='".h(ME."$v=").url_escape(is_array($X)?$X[0]:$X)."'>$nc</a> » ";}}echo"$ul\n";}if($wf){echo"<form id='jump-form'>\n";hidden_fields_get();echo(DB!=""?input_hidden("db",DB):""),(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""),"</form>\n",script("jumpSetup();");}}echo"<h2>$wl$Bc</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($i);adminer()->serviceWorker();$f=&get_session("dbs");if(DB!=""&&$f&&!in_array(DB,$f,true))$f=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();if($eh){page_footer($eh===true?"":$eh);exit;}}function
service_worker(){$jj=has_passwords();$ob=($jj?"navigator.serviceWorker.register('".js_escape(preg_replace('~\?.*~','',ME)."?file=worker.js&version=6.3.1+9a5eab2e")."', {scope: location.pathname}).catch(() => {});":"navigator.serviceWorker.getRegistration().then(registration => registration && registration.unregister());
	caches.keys().then(keys => keys.forEach(key => key.startsWith('adminer-') && caches.delete(key)));");echo
script("if (navigator.serviceWorker) {\n\t$ob\n}");}function
has_passwords(){foreach((array)$_SESSION["pwds"]as$dk){foreach($dk
as$nm){foreach($nm
as$E){if($E!==null)return
true;}}}return
false;}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Qb){$ve=[];foreach($Qb
as$v=>$X)$ve[]="$v $X";header("Content-Security-Policy: ".implode("; ",$ve));}adminer()->headers();}function
csp(){return[["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",],];}function
design_checksums(){$km=[];foreach(array_keys(adminer()->css())as$em)$km[preg_replace('~\?.*~','',$em)]=true;$I=[];foreach(["adminer.css","adminer-dark.css"]as$l){if($km[$l]&&file_exists($l)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($l),$_);$I[$l]=[(string)$_[1],Plugins::checksum($l)];}}return$I;}function
official_design_checksums(){return['adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','cpanel/adminer.css'=>'59ce604e','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'c9d1d11c','konya/adminer.css'=>'2b409696','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'d9169f12','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'a8babfc5','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'bded6cdf','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'81be9a85','rmsoft/adminer.css'=>'6cd4a237','rmsoft_blue-dark/adminer.css'=>'32102a8','rmsoft_blue/adminer.css'=>'7d8d5b18','win98/adminer.css'=>'d20077a7',];}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$dh;if(!$dh)$dh=base64_encode(rand_string());return$dh;}function
page_messages($i){$dm=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$yg=idx($_SESSION["messages"],$dm);if($yg){echo"<div class='message'>".implode("</div>\n<div class='message'>",$yg)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$dm]);}if($i)echo"<div class='error'>$i</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($Fg=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($Fg);echo"</div>\n";if($Fg!="auth")echo'<form method="post">
<p class="logout">
<span title="Username">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'Logout\' id=\'logout\'>
',adminer()->donation()."\n",input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($Og){while($Og>=2147483648)$Og-=4294967296;while($Og<=-2147483649)$Og+=4294967296;return(int)$Og;}function
long2str(array$W,$zm){$Ej='';foreach($W
as$X)$Ej
.=pack('V',$X);if($zm)return
substr($Ej,0,end($W));return$Ej;}function
str2long($Ej,$zm){$W=array_values(unpack('V*',str_pad($Ej,4*ceil(strlen($Ej)/4),"\0")));if($zm)$W[]=strlen($Ej);return$W;}function
xxtea_mx($Im,$Hm,$Mk,$yf){return
int32((($Im>>5&0x7FFFFFF)^$Hm<<2)+(($Hm>>3&0x1FFFFFFF)^$Im<<4))^int32(($Mk^$Hm)+($yf^$Im));}function
encrypt_string($Gk,$v){if($Gk=="")return"";$v=array_values(unpack("V*",pack("H*",md5($v))));$W=str2long($Gk,true);$Og=count($W)-1;$Im=$W[$Og];$Hm=$W[0];$Ri=floor(6+52/($Og+1));$Mk=0;while($Ri-->0){$Mk=int32($Mk+0x9E3779B9);$Kc=$Mk>>2&3;for($Uh=0;$Uh<$Og;$Uh++){$Hm=$W[$Uh+1];$Ng=xxtea_mx($Im,$Hm,$Mk,$v[$Uh&3^$Kc]);$Im=int32($W[$Uh]+$Ng);$W[$Uh]=$Im;}$Hm=$W[0];$Ng=xxtea_mx($Im,$Hm,$Mk,$v[$Uh&3^$Kc]);$Im=int32($W[$Og]+$Ng);$W[$Og]=$Im;}return
long2str($W,false);}function
decrypt_string($Gk,$v){if($Gk=="")return"";if(!$v)return
false;$v=array_values(unpack("V*",pack("H*",md5($v))));$W=str2long($Gk,false);$Og=count($W)-1;$Im=$W[$Og];$Hm=$W[0];$Ri=floor(6+52/($Og+1));$Mk=int32($Ri*0x9E3779B9);while($Mk){$Kc=$Mk>>2&3;for($Uh=$Og;$Uh>0;$Uh--){$Im=$W[$Uh-1];$Ng=xxtea_mx($Im,$Hm,$Mk,$v[$Uh&3^$Kc]);$Hm=int32($W[$Uh]-$Ng);$W[$Uh]=$Hm;}$Im=$W[$Og];$Ng=xxtea_mx($Im,$Hm,$Mk,$v[$Uh&3^$Kc]);$Hm=int32($W[0]-$Ng);$W[0]=$Hm;$Mk=int32($Mk-0x9E3779B9);}return
long2str($W,true);}$si=[];if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($v)=explode(":",$X);$si[$v]=$X;}}function
add_invalid_login(){$La=get_temp_dir()."/adminer-invalid";foreach(glob("$La*")?:[$La]as$l){$n=file_open_lock($l);if($n)break;}if(!$n)$n=file_open_lock("$La-".rand_string());if(!$n)return;$kf=json_decode(stream_get_contents($n),true);$rl=time();if($kf){foreach($kf
as$lf=>$X){if($X[0]<$rl)unset($kf[$lf]);}}$if=&$kf[adminer()->bruteForceKey()];if(!$if)$if=[$rl+30*60,0];$if[1]++;file_write_unlock($n,json_encode($kf));}function
check_invalid_login(array&$si){$kf=[];foreach(glob(get_temp_dir()."/adminer-invalid*")as$l){$n=file_open_lock($l);if($n){$kf=json_decode(stream_get_contents($n),true);file_unlock($n);break;}}$v=adminer()->bruteForceKey();$if=idx($kf,$v,[]);$ch=($if[1]>29?$if[0]-time():0);if($ch>0){$i=lang_format(['Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'],ceil($ch/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$v==$_SERVER["REMOTE_ADDR"])$i
.='<br>'.sprintf('Use the %s <a%s>plugin</a> if Adminer runs behind a reverse proxy.','<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($i,$si,false);}}function
password_required(){static$I;if($I===null){$I=(bool)get_session("password_required");if(!$I){$Pb=adminer()->credentials();$I=!is_object(Driver::connect($Pb[0],$Pb[1],""));if($I)set_session("password_required",true);}}return$I;}function
require_password_link($E){$Kg="<a href='https://www.adminer.org/password/'".target_blank().">".'More options'."</a>";if(!function_exists('password_hash'))return" $Kg";$vi=($E!==null?$E:base64_encode(substr(hex2bin(rand_string()),0,12)));$ue=password_hash($vi,PASSWORD_DEFAULT);$l="adminer-plugins.php";$od=file_exists("adminer-plugins.php");if($od)$gf=($E!==null?sprintf('Add this line to %s to require the entered password:',"<b>$l</b>"):sprintf('Add this line to %s to require the password %s:',"<b>$l</b>","<b>$vi</b>"));else{$l="<button name='password_less' value='".h($ue)."' class='link'>$l</button>";$gf=($E!==null?sprintf('Save %s next to Adminer to require the entered password:',$l):sprintf('Save %s next to Adminer to require the password %s:',$l,"<b>$vi</b>"));}$Sf="\t<a>new</a> Adminer\\Password(<span class='jush-apo'>'".h($ue)."'</span>),";$I="<p>$gf
<pre><code class='jush'>".($od?$Sf:"&lt;?php\n<a>return</a> <a>array</a>(\n$Sf\n);")."</code></pre>
<p>$Kg
";return" <a href='#password-less' class='toggle'>".'Require a password.'."</a>
<div id='password-less' class='hidden'>".($od?$I:"<form method='post'>\n".$I.input_token()."</form>")."</div>";}if(preg_match('~^[-\w$./]+$~',$_POST["password_less"])&&verify_token()){header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=adminer-plugins.php");echo"<?php\nreturn array(\n\tnew Adminer\\Password('$_POST[password_less]'),\n);\n";exit;}$Ca=$_POST["auth"];if($Ca&&(!adminer()->verifyLoginToken()||verify_token())){session_regenerate_id();$um=$Ca["driver"];$N=$Ca["server"];$V=$Ca["username"];$E=(string)$Ca["password"];$g=$Ca["db"];set_password($um,$N,$V,$E);$_SESSION["db"][$um][$N][$V][$g]=true;if($Ca["permanent"]){$v=implode("-",array_map('base64_encode',[$um,$N,$V,$g]));$Li=adminer()->permanentLogin(true);$si[$v]="$v:".base64_encode($Li?encrypt_string($E,$Li):"");cookie("adminer_permanent",implode(" ",$si));}if(!array_diff(array_keys($_POST),["auth","token"])||$um!=DRIVER||$N!=SERVER||$V!==$_GET["username"]||$g!=DB)redirect(auth_url($um,$N,$V,$g));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){Driver::disconnect();foreach(["pwds","db","dbs","queries"]as$v)set_session($v,null);unset_permanent($si);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.sprintf('Thanks for using Adminer. Consider <a%s>donating</a>.'," href='https://www.adminer.org/donation/'".target_blank()));}elseif($si&&!$_SESSION["pwds"]){session_regenerate_id();$Li=adminer()->permanentLogin();foreach($si
as$v=>$X){list(,$mb)=explode(":",$X);list($um,$N,$V,$g)=array_map('base64_decode',explode("-",$v));set_password($um,$N,$V,decrypt_string(base64_decode($mb),$Li));$_SESSION["db"][$um][$N][$V][$g]=true;}}function
unset_permanent(array&$si){foreach($si
as$v=>$X){list($um,$N,$V,$g)=array_map('base64_decode',explode("-",$v));if($um==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$g==DB)unset($si[$v]);}cookie("adminer_permanent",implode(" ",$si));}function
auth_error($i,array&$si,$jf=true){$ek=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$ek]||$_GET[$ek])&&!$_SESSION["token"])$i='Session expired. Please log in again.';elseif($jf&&($E=get_password())!==null){restart_session();add_invalid_login();if($E===false)$i
.=($i?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> the %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);unset_permanent($si);}}if(!$_COOKIE[$ek]&&$_GET[$ek]&&ini_bool("session.use_only_cookies"))$i='Session support must be enabled.';$Yh=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$Yh["lifetime"]);if(!is_string($_SESSION["token"]))$_SESSION["token"]=rand_string();page_header('Login',$i,null);echo"<form method='post'>\n","<div>";if($_SERVER["HTTP_SEC_FETCH_SITE"]=="same-origin"&&hidden_fields($_POST,["auth","token"]))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo
input_token(),"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($si);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$e='';if(isset($_GET["username"])&&is_string(get_password())){check_invalid_login($si);$Pb=adminer()->credentials();$e=Driver::connect($Pb[0],$Pb[1],$Pb[2]);if(is_object($e)){Db::$instance=$e;Driver::$instance=new
Driver($e);if($e->flavor)save_settings(["vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)]);}}$Yf=null;if(!is_object($e)||($Yf=adminer()->login($_GET["username"],get_password()))!==true){$i=(is_string($e)?nl_br(h($e)):(is_string($Yf)?$Yf:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the entered password, which might be the cause.':'');auth_error($i,$si);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Submit the form again.');page_footer("db");exit;}if(!is_string($_SESSION["token"]))$_SESSION["token"]=rand_string();stop_session(true);$i='';if($_POST){if(!verify_token()){header("HTTP/1.1 403 Forbidden");$i='Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.';}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){header("HTTP/1.1 413 Content Too Large");$i=sprintf('The POST data is too large. Reduce the data or increase the %s configuration directive.',"<b>post_max_size</b>");if(isset($_GET["sql"]))$i
.=' '.'You can upload a large SQL file via FTP and import it from the server.';}function
print_select_result($H,$Bb=null,array$Jh=[],&$x=0,&$Lc=false){$Uf=[];$u=[];$d=[];$T=[];$Ji=[];$Pc=[];$Pl=[];$I=[];$Hg=$Lc;$Lc=false;for($q=0;(!$x||$q<$x)&&is_array($J=$H->fetch_row());$q++){if(!$q){echo"<div class='scrollable'>\n","<table class='nowrap odds'".($Hg?on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown'):"").">\n","<thead><tr>";for($uf=0;$uf<count($J);$uf++){$j=$H->fetch_field();$A=$j->name;$R=(isset($j->table)?$j->table:"");$Ih=(isset($j->orgtable)?$j->orgtable:"");$Hh=(isset($j->orgname)?$j->orgname:$A);$Ol=driver()->typeName($j);if($Jh&&JUSH=="sql")$Uf[$uf]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($Ih!=""){$pa=($R!=""?$R:$Ih);if($R!="")$I[$R]=$Ih;if(!isset($u[$pa])){if(!isset($Ji[$Ih])){$Ji[$Ih]=[];foreach(indexes($Ih,$Bb)as$t){if($t["type"]=="PRIMARY"){$Ji[$Ih]=array_flip($t["columns"]);break;}}}$T[$pa]=$Ih;$u[$pa]=$Ji[$Ih];$d[$pa]=$Ji[$Ih];}if(isset($d[$pa][$Hh])){unset($d[$pa][$Hh]);$u[$pa][$Hh]=$uf;$Uf[$uf]=$pa;}elseif($Hg&&isset($j->orgname)&&$j->db==DB&&!is_blob(["type"=>$Ol]))$Pc[$uf]=[$pa,$Hh,preg_match('~text|json|lob~',$Ol)];}$Pl[$uf]=$Ol;echo"<th title='".h(trim(($Ih!=""?"$Ih.$Hh":($j->name!=$Hh?$Hh:""))." ".$Ol))."'>".h($A).($Jh?doc_link(['sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"explain/#the-columns-in-explain-select",]):"");}foreach($Pc
as$uf=>$Za){if($d[$Za[0]])unset($Pc[$uf]);}echo"<tbody>\n";}$Le=[];foreach($u
as$pa=>$t){if($t&&!$d[$pa]){$s="";foreach($t
as$pb=>$uf){if($J[$uf]===null){$s=null;break;}$s
.="&where[".url_escape(bracket_escape($pb))."]=".url_escape($J[$uf]);}$Le[$pa]=$s;}}echo"<tr>";foreach($J
as$v=>$X){$y="";if(isset($Uf[$v])){if($Jh&&JUSH=="sql"){$R=$J[array_search("table=",$Uf)];$y=ME.$Uf[$v].url_escape($Jh[$R]!=""?$Jh[$R]:$R);}elseif(idx($Le,$Uf[$v])!==null)$y=ME."edit=".url_escape($T[$Uf[$v]]).$Le[$Uf[$v]];}$He=select_value($X,$y,['type'=>(preg_match('~binary~',$Pl[$v])?'blob':$Pl[$v])],null);$b="";$Za=idx($Pc,$v);if($Za&&idx($Le,$Za[0])!==null&&is_utf8($X)){$Lc=true;$b=" data-name='".h("val[".bracket_escape($T[$Za[0]])."][".bracket_escape(substr($Le[$Za[0]],1))."][".bracket_escape($Za[1])."]")."' data-text='".($Za[2]?1:0)."'".(html_entity_decode(strip_tags($He),ENT_QUOTES,"UTF-8")!==$X?" data-value='".h($X)."'":"");}echo"<td".(preg_match(number_type(),$Pl[$v])?" class='number'":"")."$b>$He";}}$x=$q;echo($q?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$I;}function
textarea($A,$Y,$K=10,$tb=80,$xf=JUSH,$dd=0){echo"<textarea name='".h($A)."' rows='$K' cols='$tb' class='sqlarea jush-".h($xf)."' spellcheck='false' wrap='off'".($dd?" data-error-line='$dd'":"").">\n";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($b,array$B,$Y="",$ti=""){if($B&&$Y!=""&&!isset($B[$Y]))$B=[$Y=>$Y]+$B;$hl=($B?"select":"input");return"<$hl$b".($B?"><option value=''>$ti".optionlist($B,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$ti'>");}function
json_row($v,$X=null,$gd=true){static$Kd=true;if($Kd)echo"{";if($v!=""){echo($Kd?"":",")."\n\t\"".addcslashes($v,"\r\n\t\"\\/").'": '.($X!==null?($gd?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$Kd=false;}else{echo"\n}\n";$Kd=true;}}function
flat_collations(){$sb=collations();return(is_array(reset($sb))?call_user_func_array('array_merge',array_values($sb)):$sb);}function
edit_type($v,array$j,array$sb,array$Rd=[],array$wd=[]){$U=(string)$j["type"];echo"<td><select name='".h($v)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($U&&!array_key_exists($U,driver()->types())&&!isset($Rd[$U])&&!in_array($U,$wd))$wd[]=$U;$Hk=driver()->structuredTypes();if($Rd)$Hk['Foreign keys']=$Rd;echo
optionlist(array_merge($wd,$Hk),$U),"</select><td>","<input name='".h($v)."[length]' value='".h($j["length"])."' size='3'".(!$j["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($sb?"<input list='collations' name='".h($v)."[collation]'".option_types($U,'('.text_type().')$')." value='".h($j["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($v)."[unsigned]'".option_types($U,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$j["unsigned"]).'</select>':''),(isset($j['on_update'])?"<select name='".h($v)."[on_update]'".option_types($U,'timestamp|datetime').'>'.optionlist([""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$j["on_update"])?"CURRENT_TIMESTAMP":$j["on_update"])).'</select>':''),($Rd?"<select name='".h($v)."[on_delete]'".option_types($U,'`')."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$j["on_delete"])."</select> ":" ");}function
option_types($U,$Pl){return" data-types='".h($Pl)."'".(preg_match("~$Pl~",$U)?"":" class='hidden'");}function
process_length($w){if(JUSH=="mssql"&&preg_match('~^\s*\(?\s*max\s*\)?\s*$~i',$w))return"(max)";if(JUSH=="oracle"&&preg_match('~^\s*\(?\s*(\d+)\s+(CHAR|BYTE)\s*\)?\s*$~i',$w,$_))return"($_[1] ".strtoupper($_[2]).")";if(JUSH=="oracle"&&preg_match('~^\s*\(?\s*\*\s*,\s*(-?\d+)\s*\)?\s*$~',$w,$_))return"(*,$_[1])";$ad=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$ad(?:\\s*,\\s*$ad)*+\\s*\\)?\\s*\$~",$w)&&preg_match_all("~$ad~",$w,$cg)?"(".implode(",",$cg[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$w)));}function
process_in($X){$X=preg_replace('~^\s*\(\s*(.*?)\s*\)\s*$~s','\1',$X);$Xi="'(?:[^']|'')*+'";$tf=(preg_match("~^\\s*$Xi(?:\\s*,\\s*$Xi)*+\\s*\$~",$X)&&preg_match_all("~$Xi~",$X,$cg)?array_map('Adminer\idf_unescape',$cg[0]):array_map('trim',explode(",",$X)));return"(".implode(", ",array_map('Adminer\q',$tf)).")";}function
process_between($X){$Xi="('(?:[^']|'')*+')";$Zi=(preg_match("~^\\s*$Xi\\s+AND\\s+$Xi\\s*\$~i",$X,$_)?[idf_unescape($_[1]),idf_unescape($_[2])]:preg_split('~\s+AND\s+~i',trim($X)));return(count($Zi)==2?q($Zi[0])." AND ".q($Zi[1]):"NULL AND NULL");}function
process_type(array$j,$qb="COLLATE"){return" ".(is_user_type($j["type"])?idf_escape($j["type"]):$j["type"]).process_length($j["length"]).(preg_match(number_type(),$j["type"])&&in_array($j["unsigned"],driver()->unsigned)?" $j[unsigned]":"").(preg_match('~'.text_type().'~',$j["type"])&&$j["collation"]?" $qb ".(JUSH=="mssql"&&preg_match('~^\w+$~',$j["collation"])?$j["collation"]:q($j["collation"])):"");}function
process_field(array$j,array$Ml){return[idf_escape(trim($j["field"])),process_type($Ml),($j["null"]?" NULL":" NOT NULL"),default_value($j),(preg_match('~timestamp|datetime~',$j["type"])&&$j["on_update"]?" ON UPDATE $j[on_update]".process_length($j["length"]):""),(support("comment")&&$j["comment"]!=""?" COMMENT ".q($j["comment"]):""),($j["auto_increment"]?auto_increment():null),];}function
default_value(array$j){if($j["default"]===null)return"";$h=str_replace("\r","",$j["default"]);$ce=$j["generated"];$Q=!preg_match('~]$~',$j["length"])&&(preg_match('~char|binary|text|clob|json|enum|set|String~',$j["type"])||driver()->enumLength($j));$Zh=(JUSH=="sqlite"||(JUSH=="sql"&&$j["default_sql"]&&!preg_match("~^(current_timestamp(\\(\\d*\\))?|[bx]'[\\da-f]*')$~i",$h)));return(in_array($ce,driver()->generated)?(JUSH=="mssql"?" AS ($h)".($ce=="VIRTUAL"?"":" $ce"):" GENERATED ALWAYS AS ($h) $ce"):(preg_match('~^GENERATED ~i',$h)?" $h":" DEFAULT ".(!$j["default_sql"]&&($Q||preg_match('~^(?![a-z])~i',$h))?(JUSH=="sql"&&preg_match('~text|json~',$j["type"])?"(".q($h).")":q($h)):($Zh?"($h)":$h))));}function
edit_fields(array$k,array$sb,$U="TABLE",array$Rd=[]){$k=array_values($k);$gc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$xb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<th id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<th id='label-length'>".'Length',"<th>".'Options';if($U=="TABLE")echo"<th id='label-null'>NULL\n","<th><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>A_I</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",]),"<th id='label-default'$gc>".'Default value',(support("comment")?"<th id='label-comment'$xb>".'Comment':"");$Hf=!support("move_col");echo"<td>".icon("plus","add[".($Hf?count($k):0)."]","+",'Add next',($Hf?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($k
as$q=>$j){$q++;$Kh=$j[($_POST?"orig":"field")];$uc=(isset($_POST["add"][$q-1])||(isset($j["field"])&&!idx($_POST["drop_col"],$q)))&&(support("drop_col")||$Kh=="");echo"<tr".($uc?"":" hidden").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$q][inout]",explode("|",driver()->inout),$j["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",'Move')." ":"");if($uc)echo"<input name='fields[$q][field]' value='".h($j["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$q-1])?" autofocus":"").">";echo
input_hidden("fields[$q][orig]",$Kh);edit_type("fields[$q]",$j,$sb,$Rd);if($U=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$q][null]",1,$j["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$q'".($j["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$gc>".html_select("fields[$q][generated]",array_merge(["","DEFAULT","DEFAULT SQL"],driver()->generated),$j["generated"],"","label-default")." ";$b=" name='fields[$q][default]' aria-labelledby='label-default'";$Y=h($j["default"]);echo(preg_match('~\n~',$j["default"])?"<textarea$b rows='2' cols='30' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$b value='$Y'>");if(support("comment")){$b=" name='fields[$q][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$xb>".adminer()->commentInput('COLUMN',$b,$j["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$q]","+",'Add next')." ":""),($Kh==""||support("drop_col")?icon("cross","drop_col[$q]","x",'Remove'):"");}}function
process_fields(array&$k){if($_POST["add"]){$k=array_values($k);array_splice($k,key($_POST["add"]),0,[[]]);}return$_POST["add"]||$_POST["drop_col"];}function
drop_create($Gc,$Mb,$Hc,$nl,$Ic,$z,$xg,$vg,$wg,$sh,$Yg){if($_POST["drop"])query_redirect($Gc,$z,$xg);elseif($sh=="")query_redirect($Mb,$z,$wg);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($z,$vg,queries($Gc)&&queries($Mb)&&driver()->commit());driver()->rollback();}elseif($sh!=$Yg){$Ob=queries($Mb);queries_redirect($z,$vg,$Ob&&queries($Gc));if($Ob&&$Hc)queries($Hc);}else
queries_redirect($z,$vg,queries($nl)&&queries($Ic)&&queries($Gc)&&queries($Mb));}function
create_trigger($vh,array$J){$tl=" $J[Timing] $J[Event]".(preg_match('~ OF~',$J["Event"])?" $J[Of]":"");return"CREATE TRIGGER ".idf_escape($J["Trigger"]).(JUSH=="mssql"?$vh.$tl:$tl.$vh).preg_replace('~[\s;]+$~',''," $J[Type]\n$J[Statement]").";";}function
q_dollar($Q){$mc='$$';while(strpos($Q.$mc,$mc)!=strlen($Q))$mc='$_'.substr($mc,1);return$mc.$Q.$mc;}function
routine_collate($rb){static$eb=[];if($rb&&!$eb){foreach(collations()as$db=>$rm){foreach((array)$rm
as$X)$eb[$X]=$db;}}return($eb[$rb]?"CHARACTER SET ".q($eb[$rb])." ":"")."COLLATE";}function
create_routine($xj,array$J){$O=[];$k=$J["fields"];ksort($k);foreach($k
as$j){if($j["field"]!=""){$bf=(preg_match("~^(".driver()->inout.")\$~",$j["inout"])?$j["inout"]:"");$O[]="\n  ".(JUSH=="mssql"?"@$j[field]".process_type($j).($bf?" $bf":""):($bf?"$bf ":"").idf_escape($j["field"]).process_type($j,routine_collate($j["collation"])));}}$jc="";$B=[];foreach(routine_options($xj)as$v=>$sm){$Y=idx($J["options"],$v,"");if($v=="DEFINER")$jc=($Y?" $v=".implode("@",array_map('Adminer\q',explode("@",$Y,2))):"");elseif(!$sm){if($Y!="")$B[]="$v ".q($Y);}elseif($Y!=reset($sm)&&in_array($Y,$sm))$B[]=$Y;}$Ff=$J["language"];$kc=preg_replace('~[\s;]+$~','',$J["definition"]);$Cc=(JUSH=="pgsql"||($Ff&&$Ff!="sql"));$Xh=($O?implode(",",$O)."\n":"");return"CREATE$jc $xj ".table(trim($J["name"])).(JUSH=="mssql"&&$xj=="PROCEDURE"?rtrim($Xh):" ($Xh)").($xj=="FUNCTION"?"\nRETURNS".process_type($J["returns"],routine_collate($J["returns"]["collation"])):"").($Ff?" LANGUAGE $Ff":"").($B?"\n".implode(" ",$B):"").($Cc?" AS ".q_dollar("\n".trim($kc)."\n"):(JUSH=="mssql"?"\nAS":"")."\n$kc;");}function
remove_definer($G){$jc=implode("@",array_map('Adminer\idf_escape',explode("@",logged_user(),2)));return
preg_replace('(^([A-Z =]+) DEFINER='.preg_quote($jc).')','\1',$G);}function
object_name($U,$R,array$d){return
str_replace(["{table}","{columns}"],[$R,implode("_",$d)],adminer()->namePattern($U));}function
format_foreign_key(array$m,$A=""){$g=$m["db"];$fh=$m["ns"];return($A!=""?" CONSTRAINT ".idf_escape($A):"")." FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$m["source"])).") REFERENCES ".($g!=""&&$g!=$_GET["db"]?idf_escape($g).".":"").($fh!=""&&$fh!=$_GET["ns"]?idf_escape($fh).".":"").idf_escape($m["table"])." (".implode(", ",array_map('Adminer\idf_escape',$m["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$m["on_delete"])?" ON DELETE $m[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$m["on_update"])?" ON UPDATE $m[on_update]":"").($m["deferrable"]?" $m[deferrable]":"");}function
tar_file($l,$yl){$I=pack("a100a8a8a8a12a12",$l,644,0,0,decoct($yl->size),decoct(time()));$jb=8*32;for($q=0;$q<strlen($I);$q++)$jb+=ord($I[$q]);$I
.=sprintf("%06o\0 ",$jb);echo$I,str_repeat("\0",512-strlen($I));$yl->send();echo
str_repeat("\0",511-($yl->size+511)%512);}function
doc_version(){$bk=connection()->server_info;if(JUSH=='oracle'){preg_match('~(?:.* |^)(\d+)\.\d+\.\d+\.\d+\.\d+~s',$bk,$_);return($_[1]>=18?$_[1]:"19");}$ij=(JUSH=='sql'||connection()->flavor=='cockroach'?'~^\d+\.\d+~':'~^\d\.?\d~');$vm=(preg_match($ij,$bk,$_)?$_[0]:"");if(JUSH=='mssql')return($vm>=15?"sql-server-ver$vm":($vm==12?"azuresqldb-current":"sql-server-2017"));return$vm;}function
doc_link(array$oi,$ol="📖"){$vm=doc_version();$fm=['sql'=>"https://dev.mysql.com/doc/refman/$vm/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$vm)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://docs.oracle.com/en/database/oracle/oracle-database/$vm/",];if(connection()->flavor=='maria'){$fm['sql']="https://mariadb.com/kb/en/";$oi['sql']=($oi['mariadb']?:str_replace(".html","/",$oi['sql']));}if(connection()->flavor=='cockroach'&&$oi['cockroach']){$fm['pgsql']="https://docs.cockroachlabs.com/docs/v$vm/";$oi['pgsql']=$oi['cockroach'];}return($oi[JUSH]?" <a href='".h($fm[JUSH].$oi[JUSH].(JUSH=='mssql'?"?view=$vm":""))."'".target_blank()." class='doc' title='".'Documentation'."'>$ol</a>":"");}function
db_size($g){if(!connection()->select_db($g))return"?";$I=0;foreach(table_status()as$S)$I+=$S["Data_length"]+$S["Index_length"];return
adminer()->formatSizeValue($I);}function
set_utf8mb4($Mb){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$Mb)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'),null,301);if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!="")page_header('Database'.": ".h(DB),adminer()->error(),true,"","db");else{if(!isset($_GET["db"])&&support("single_db")){$f=adminer()->databases();if($f)redirect(ME."db=".url_escape($f[0]));}if($_POST["db"]&&!$i)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$i,false);echo"<p class='links'>\n";foreach(['database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',]as$v=>$X){if(support($v))echo"<a href='".h(ME)."$v='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged in as: %s',"<b>".h(logged_user())."</b>")."\n";$f=adminer()->databases();$Id=$_GET["filter"];if($Id!=""){foreach($f
as$v=>$g){if(stripos($g,$Id)===false)unset($f[$v]);}}if($f){$Ij=support("scheme");echo"<form method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<th>".'Collation'."<th>".'Tables'."<th>".'Size'." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".'Compute'."</a>"."<tbody>\n";$f=($_GET["dbsize"]?count_tables($f):array_flip($f));foreach($f
as$g=>$T){$wj=h(preg_replace('~&db=[^&]*~','',ME))."db=".url_escape($g);$r=h("Db-".$g);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$g,in_array($g,(array)$_POST["db"]),"","","",$r):""),"<th><a href='$wj' id='$r'>".h($g)."</a>";$rb=h(db_collation($g,[]));echo"<td>".(support("database")?"<a href='$wj".($Ij?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$rb</a>":$rb),"<td align='right'><a href='$wj&amp;schema=' id='tables-".h($g)."' title='".'Database schema'."'>".($_GET["dbsize"]?format_number($T):"?")."</a>","<td align='right' id='size-".h($g)."'>".($_GET["dbsize"]?db_size($g):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".'Drop'."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$ja=adminer();$xi=($ja
instanceof
Plugins?$ja->plugins:[]);$Fc=($ja
instanceof
Plugins?$ja->drivers:[]);$rc=design_checksums();if($xi||$Fc||$rc){$kb=($ja
instanceof
Plugins?$ja->checksums():[]);$lh=Plugins::officialChecksums();$am=function($em){return" (<a href='$em'".target_blank()." class='update'>".VERSION."</a>)";};$wi=function($Ed)use($kb,$lh,$am){return($kb[$Ed]&&$lh[$Ed]&&$kb[$Ed]!==$lh[$Ed]?$am("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach($xi
as$ui){$gj=new
\ReflectionObject($ui);$oc=Plugins::describe($ui);echo"<li><b>".h(Plugins::pluginName($ui))."</b>".($oc!=""?": $oc":"").$wi(basename((string)$gj->getFileName(),'.php'))."\n";}foreach($Fc
as$r=>$A)echo"<li><b>".h($r)."</b>: ".h($A).$wi(basename((string)$ja->driverFiles[$r],'.php'))."\n";if($rc){$nh=official_design_checksums();foreach($rc
as$l=>$qc){list($A,$jb)=$qc;$mh=$nh["$A/$l"];echo"<li><b>".h($l)."</b>".h($A?": $A":"").($mh&&$mh!==$jb?$am("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Fb){$this->size+=strlen($Fb);fwrite($this->handler,$Fb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if($_GET["select"]!=""&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$k=fields($a);header("Content-Type: application/octet-stream");$sm=array_merge((array)$_GET["where"],(array)$_GET["val"]);header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$sm)).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$H=driver()->select($a,$M,[where($_GET,$k)],$M);$J=($H?$H->fetch_row():[]);echo
driver()->value($J[0],$k[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$k=fields($a);if(!$k)$i=adminer()->error();$S=table_status1($a);$A=adminer()->tableName($S);$i=$i?:h($S["Error"]);page_header(($k&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($A!=""?$A:h($a)),$i,[],"",!$k,($k?doc_link([JUSH=>driver()->tableHelp($a,is_view($S))]):""));$vj=[];foreach($k
as$v=>$j)$vj+=$j["privileges"];adminer()->selectLinks($S,(isset($vj["insert"])||!support("table")?"":null));$wb=$S["Comment"];if($wb!="")echo"<p class='nowrap'>".'Comment'.": ".adminer()->commentValue('TABLE',$wb)."\n";if($k)adminer()->tableStructurePrint($k,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$J){$y=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($J["ns"]),ME);echo"<li><a href='".h($y."table=".url_escape($J["table"]))."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>";}echo"</ul>\n";}$Ze=driver()->inheritsFrom($a);if($Ze){echo"<h3>".'Inherits from'."</h3>\n";tables_links($Ze);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<div>\n","<h3 id='indexes'>".'Indexes'."</h3>\n";$u=indexes($a);if($u)adminer()->tableIndexesPrint($u,$S);if(driver()->supportsAlterIndex($S))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.'Alter indexes'."</a>\n";echo"</div>\n";}if(!is_view($S)&&driver()->supportsAlterTable($S)){if(fk_support($S)){echo"<div>\n","<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$Rd=foreign_keys($a);$ua=function_exists('Adminer\alter_table');if($Rd){echo"<table>\n","<thead><tr><th>".'Source'."<th>".'Target'."<th>".'ON DELETE'."<th>".'ON UPDATE'.($ua?"<td class='hover'>":"")."<tbody>\n";foreach($Rd
as$A=>$m){echo"<tr".(!is_int($A)?" title='".h($A)."'":"").">","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$m["source"]))."</i>";$y=($m["db"]!=""?preg_replace('~&db=[^&]*~',"&db=".url_escape($m["db"]),ME):($m["ns"]!=""?preg_replace('~&ns=[^&]*~',"&ns=".url_escape($m["ns"]),ME):ME));echo"<td><a href='".h($y."table=".url_escape($m["table"]))."'>".($m["db"]!=""&&$m["db"]!=DB?"<b>".h($m["db"])."</b>.":"").($m["ns"]!=""&&$m["ns"]!=$_GET["ns"]?"<b>".h($m["ns"])."</b>.":"").h($m["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$m["target"]))."</i>)","<td>".h($m["on_delete"]),"<td>".h($m["on_update"]),($ua?'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($A)).'">'.'Alter'.'</a>':''),"\n";}echo"</table>\n";}echo($ua?'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.'Create foreign key'."</a>\n":''),"</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".'Checks'."</h3>\n";$gb=driver()->checkConstraints($a);if($gb){echo"<table>\n";foreach($gb
as$v=>$X)echo"<tr".($v!=$X?" title='".h($v)."'":"").">","<td><code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($X)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($v))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.'Create check'."</a>\n","</div>\n";}}if(support(is_view($S)?"view_trigger":"trigger")&&driver()->supportsAlterTable($S)){echo"<div>\n","<h3 id='triggers'>".'Triggers'."</h3>\n";$Jl=triggers($a);if($Jl){echo"<table>\n";foreach($Jl
as$v=>$X){echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($v)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($v))."'>".'Alter'."</a>";$xj=$X[2];if($xj){$zj=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($xj["ns"]),ME).'function='.url_escape($xj["function"]).'&name='.url_escape($xj["name"]);echo", <a href='".h($zj)."' title='".h($xj["name"])."'>".'Alter function'."</a>";}echo"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.'Create trigger'."</a>\n","</div>\n";}$hk=driver()->shadowTables($a);if($hk){echo"<h3 id='shadow-tables'>".'Shadow tables'."</h3>\n";tables_links($hk);}$Ye=driver()->inheritedTables($a);if($Ye){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$ci=driver()->partitionsInfo($a);if($ci)echo"<p><code class='sql jush-".JUSH."'>BY ".h("$ci[partition_by]($ci[partition])")."</code>\n";tables_links($Ye);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",[],h(DB.($_GET["ns"]?".$_GET[ns]":"")));function
schema_column($R,array$fj,array&$d){if(!isset($d[$R])){$d[$R]=0;foreach((array)idx($fj,$R)as$A=>$hj){if($A!=$R)$d[$R]=max($d[$R],schema_column($A,$fj,$d)+1);}}return$d[$R];}function
type_class($U){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$v=>$X){if(preg_match("~$v|$X~",$U))return" class='$v'";}}$Yk=[];$al=[];$Zk=[];$Bd=[];$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$cg,PREG_SET_ORDER);foreach($cg
as$q=>$_){$Yk[$_[1]]=[(float)$_[2],(float)$_[3]];$al[]="\n\t'".js_escape($_[1])."': [ $_[2], $_[3] ]";}$L=[];$fj=[];$Rd=[];$ra=driver()->allFields();$_e=[];$bl=[];foreach(table_status('',true)as$R=>$S){if(!is_view($S)){if(adminer()->tableName($S)!=""&&!$S["dependent"])$bl[$R]=$S;else$_e[$R]=true;}}foreach($bl
as$R=>$S){$F=0;$L[$R]["fields"]=[];foreach($ra[$R]as$j){$F+=1.25;$Bd[$R][$j["field"]]=$F;$L[$R]["fields"][$j["field"]]=$j;}foreach(adminer()->foreignKeys($R)as$X){if($X["db"]==""&&$X["ns"]==""&&!$_e[$X["table"]]){$Rd[$R][]=$X;$fj[$X["table"]][$R]=[];}}}$d=[];$ge=[];$Gm=[];$le=[];foreach(array_keys($L)as$A)schema_column($A,$fj,$d);arsort($d);foreach($d
as$A=>$c){$Dg=null;foreach((array)idx($Rd,$A)as$X){if($X["table"]!=$A&&$L[$X["table"]])$Dg=($Dg===null?$d[$X["table"]]:min($Dg,$d[$X["table"]]));}$d[$A]=max($c,(int)$Dg-1);}$dl=[];foreach($L
as$A=>$R){$c=$d[$A];$ge[$c][]=$A;$ql=.75*strlen($A);foreach($R["fields"]as$j)$ql=max($ql,.65*strlen($j["field"]));$dl[$A]=ceil($ql)+1;$Gm[$c]=max(idx($Gm,$c,0),$dl[$A]);}foreach($Rd
as$A=>$rm){foreach($rm
as$X){$ke=$d[$A]+(idx($d,$X["table"],$d[$A])>$d[$A]?1:0);$le[$ke]=idx($le,$ke,0)+1;}}ksort($ge);$ye=0;$Fm=0;$ub=0;$Hi=null;$Wk=[];foreach($ge
as$c=>$T){if($Hi!==null){$ub=round($ub+$Gm[$Hi]+1.7+idx($le,$c,0)*.1,1);$C=[];foreach($T
as$A){$Mk=0;$Lb=0;$Vg=array_keys((array)idx($fj,$A));foreach((array)idx($Rd,$A)as$X)$Vg[]=$X["table"];foreach($Vg
as$Pg){if($L[$Pg]&&$d[$Pg]<$c){$Mk+=$L[$Pg]["pos"][0];$Lb++;}}$C[$A]=($Lb?$Mk/$Lb:$ye);}asort($C);$T=array_keys($C);}$Al=0;foreach($T
as$A){$F=1.25*count($L[$A]["fields"]);$L[$A]["pos"]=($Yk[$A]?:[$Al,$ub]);$Wk[$A]=$L[$A]["pos"][1];$Al+=2.5+$F;$ye=max($ye,$L[$A]["pos"][0]+2.5+$F);$Fm=max($Fm,round($L[$A]["pos"][1]+$Gm[$c],1));if(!$Yk[$A])$Zk[]="\n\t'".js_escape($A)."': [ ".$L[$A]["pos"][0].", ".$L[$A]["pos"][1]." ]";}$Hi=$c;}$Lf=[];$Ma=[];foreach($Rd
as$A=>$rm){foreach($rm
as$X){$jl=idx($Wk,$X["table"],$Wk[$A]);$rk=$Wk[$A]+$Gm[$d[$A]];$uj=($jl-1>$rk);$Jf=($uj?$rk+1:min($Wk[$A],$jl)-1);$La=idx($Ma,(string)$Jf,0);$Ma[(string)$Jf]=$La+1;$Jf=round($uj?min($Jf+$La*.1,$jl-1):$Jf-$La*.1,1);while($Lf[(string)$Jf])$Jf-=.0001;$L[$A]["references"][$X["table"]][(string)$Jf]=[$X["source"],$X["target"]];$fj[$X["table"]][$A][(string)$Jf]=$X["target"];$Lf[(string)$Jf]=true;}}echo'<div id="schema" style="height: ',$ye,'em; width: ',$Fm,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$al)."\n",'};
const tablePosDefault = {',implode(",",$Zk)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$ye,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($L
as$A=>$R){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em; width: ".$dl[$A]."em;'>",'<a href="'.h(ME).'table='.url_escape($A).'"><b>'.h($A)."</b></a>";foreach($R["fields"]as$j){$X='<span'.type_class($j["type"]).' title="'.h($j["type"].($j["length"]?"($j[length])":"").($j["null"]?" NULL":'')).'">'.h($j["field"]).'</span>';echo"<br>".($j["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$kl=>$hj){foreach($hj
as$Jf=>$cj){$Kf=$Jf-$R["pos"][1];$Jk=($Kf>0?"left: 100%; width: calc($Kf"."em - 100%)":"left: $Kf"."em");$Fm=($Kf>0?"100%":(-$Kf)."em");$q=0;foreach($cj[0]as$qk)echo"\n<div class='references' title='".h($kl)."' id='refs$Jf-".($q++)."' style='$Jk"."; top: ".$Bd[$A][$qk]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: $Fm;'></div></div>";}}foreach((array)$fj[$A]as$kl=>$hj){foreach($hj
as$Jf=>$ll){$Kf=$Jf-$R["pos"][1];$q=0;foreach($ll
as$il)echo"\n<div class='references arrow' title='".h($kl)."' id='refd$Jf-".($q++)."' style='left: $Kf"."em; top: ".$Bd[$A][$il]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Kf)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($L
as$A=>$R){foreach((array)$R["references"]as$kl=>$hj){if($L[$kl]){foreach($hj
as$Jf=>$cj){$Eg=$ye;$kg=-10;foreach($cj[0]as$v=>$qk){$zi=$R["pos"][0]+$Bd[$A][$qk];$_i=$L[$kl]["pos"][0]+$Bd[$kl][$cj[1][$v]];$Eg=min($Eg,$zi,$_i);$kg=max($kg,$zi,$_i);}echo"<div class='references' id='refl$Jf' style='left: $Jf"."em; top: $Eg"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($kg-$Eg)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$i){$h=["auto_increment"=>''];foreach(["type","routine","event","trigger"]as$Ok){if(support($Ok))$h[$Ok."s"]='';}save_settings(array_intersect_key($_POST+$h,array_flip(["output","format","db_style","schema_style","table_style","data_style"])+$h),"adminer_export");$qa=(DB==""||$_GET["ns"]==="");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$sd=dump_headers((count($T)==1?key($T):DB),($qa||count($T)>1));$qf=preg_match('~sql~',$_POST["format"]);if($qf){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$Jk=$_POST["db_style"];$f=[DB];if(DB==""){$f=$_POST["databases"];if(is_string($f))$f=explode("\n",rtrim(str_replace("\r","",$f),"\n"));}foreach((array)$f
as$g){adminer()->dumpDatabase($g);if(connection()->select_db($g)){if($qf&&$Jk)echo
use_sql($g,$Jk).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:adminer()->schemas()))as$L){if($L!=""){if(DB==""&&information_schema(DB,$L))continue;set_schema($L);}if($qf&&$_POST["schema_style"]&&function_exists('Adminer\use_schema_sql'))echo
use_schema_sql($_GET["ns"],$_POST["schema_style"]).";\n\n";$Fk=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):[]);$rd=[];$Yb=[];foreach($Fk
as$A=>$S){if($qa||in_array($A,(array)$_POST["tables"]))$rd[$A]=$S;if($qa||in_array($A,(array)$_POST["data"]))$Yb[$A]=$S;}if($qf){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($rd);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$Kl=[];foreach($Yb
as$A=>$S){if(!is_view($S)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($rd[$A])))$Kl[]=$A;}echo
truncate_all_sql($Kl);}$Rh="";if($_POST["types"]){foreach(types()as$r=>$U){$kc=type_definition($r);$ih=($kc["kind"]=='d'?"DOMAIN":"TYPE");if($kc["definition"])$Rh
.=($Jk!='DROP+CREATE'?"DROP $ih IF EXISTS ".table($U).";;\n":"")."CREATE $ih ".table($U)." $kc[definition];\n\n";else$Rh
.="-- Could not export type ".preg_replace('~[\r\n]+~',' ',$U)."\n\n";}}if($_POST["routines"]){foreach(routines()as$J){$A=$J["ROUTINE_NAME"];$xj=$J["ROUTINE_TYPE"];$Xe=routine($J["SPECIFIC_NAME"],$xj);if($Xe["definition"]!=""){$Mb=create_routine($xj,["name"=>$A]+$Xe);set_utf8mb4($Mb);$Rh
.=($Jk!='DROP+CREATE'?"DROP $xj IF EXISTS ".table($A).";;\n":"")."$Mb;\n\n";}else$Rh
.="-- Could not export ".strtolower($xj)." ".preg_replace('~[\r\n]+~',' ',$A)."\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$J){$Mb=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($J["Name"]),3));set_utf8mb4($Mb);$Rh
.=($Jk!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($J["Name"]).";;\n":"")."$Mb;;\n\n";}}echo($Rh&&JUSH=='sql'?"DELIMITER ;;\n\n$Rh"."DELIMITER ;\n\n":$Rh);}if($_POST["table_style"]||$_POST["data_style"]){$xm=[];foreach($Fk
as$A=>$S){$R=array_key_exists($A,$rd);$Wb=array_key_exists($A,$Yb);if($R||$Wb){$yl=null;if($sd=="tar"){$yl=new
TmpFile;ob_start([$yl,'write'],1e5);}adminer()->dumpTable($A,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$xm[]=$A;elseif($Wb){$k=fields($A);$M=["*"];$Ib=convert_fields($k,$k);if($Ib)$M[]=substr($Ib,2);adminer()->dumpData($A,$_POST["data_style"],"",$M);}if($qf&&$_POST["triggers"]&&$R&&($Jl=trigger_sql($A)))echo"\nDELIMITER ;;\n$Jl\nDELIMITER ;\n";if($sd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$g/")."$A.csv",$yl);}elseif($qf)echo"\n";}}if($qf&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($rd
as$A=>$S){if(!is_view($S))echo
foreign_keys_sql($A);}}if($qf){foreach($xm
as$wm)adminer()->dumpTable($wm,$_POST["table_style"],1);}if($sd=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header('Export',$i,($_GET["export"]!=""?["table"=>$_GET["export"]]:[]),h(DB));echo'
<form method="post">
<table class="layout">
';$cc=['','USE','DROP+CREATE','CREATE'];$Gj=(JUSH=="mssql"?['','DROP+CREATE','CREATE']:$cc);$cl=['','DROP+CREATE','CREATE'];$Xb=['','TRUNCATE+INSERT','INSERT'];if(JUSH=="sql")$Xb[]='INSERT+UPDATE';$J=get_settings("adminer_export");if(!$J)$J=["output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"schema_style"=>"","table_style"=>"DROP+CREATE","data_style"=>"INSERT"];echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$J["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$J["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$cc,$J["db_style"]).(support("type")?checkbox("types",1,$J["types"],'User types'):"").(support("routine")?checkbox("routines",1,$J["routines"],'Routines'):"").(support("event")?checkbox("events",1,$J["events"],'Events'):"")),(function_exists('Adminer\use_schema_sql')?"<tr><th>".'Schema'."<td>".html_select('schema_style',$Gj,$J["schema_style"]):""),"<tr><th>".'Tables'."<td>".html_select('table_style',$cl,$J["table_style"]).checkbox("auto_increment",1,$J["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$J["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Xb,$J["data_style"]),'</table>
';adminer()->dumpPrint();echo'<p><input type=\'submit\' value=\'Export\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$Gi=[];if($_GET["ns"]===""&&support("scheme")){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".'All'."'".on('click','formCheck','^schemas\[').">".'Schema'."</label>","<tbody>\n";foreach(adminer()->schemas()as$L){if(!information_schema(DB,$L))echo"<tr><td>".checkbox("schemas[]",$L,true,$L,"","block")."\n";}}elseif(DB!=""){$hb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$hb class='jsonly' title='".'All'."'".on('click','formCheck','^tables\[').">".'Table'."</label>","<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$hb class='jsonly' title='".'All'."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$xm="";$fl=tables_list();foreach($fl
as$A=>$U){$Fi=preg_replace('~_.*~','',$A);$hb=($a==""||$a==(substr($a,-1)=="%"?"$Fi%":$A));$Ki="<tr><td>".checkbox("tables[]",$A,$hb,$A,"","block");if($U!==null&&!preg_match('~table~i',$U))$xm
.="$Ki\n";else
echo"$Ki<td align='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$hb)."</label>\n";$Gi[$Fi]++;}echo$xm;if($fl)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$f=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($f?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".'All'."'".on('click','formCheck','^databases\[').">":"").'Database'."</label>","<tbody>\n";if($f){foreach($f
as$g){if(!information_schema($g)){$Fi=preg_replace('~_.*~','',$g);echo"<tr><td>".checkbox("databases[]",$g,$a==""||$a=="$Fi%",$g,"","block")."\n";$Gi[$Fi]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$Kd=true;foreach($Gi
as$v=>$X){if($v!=""&&$X>1){echo($Kd?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$v%")."'>".h($v)."</a>";$Kd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$H=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$ee=$H;if(!$H)$H=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($ee?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<td class='hover'><tbody>\n";while($J=$H->fetch_assoc())echo'<tr><td>'.h($J["User"]),"<td>".h($J["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($J["User"]).'&host='.url_escape($J["Host"])).'">'.'Edit'."</a>\n";if(!$ee||DB!="")echo"<tr><td><input name='user' autocapitalize='off'>","<td><input name='host' value='localhost' autocapitalize='off'>","<td class='hover'><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){$Tf=driver()->lineComment();$sk="(?:\\s|\xEF\xBB\xBF|/\\*[\s\S]*?\\*/|(?:$Tf)[^\n]*\n?|--\r?\n)";$_k="~^(?:$sk|;)*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is";if(!$i&&$_POST["export"]&&JUSH=="sqlite"&&preg_match($_k,$_POST["query"],$_)!==0)$i=sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$_[1])?'ATTACH':'VACUUM INTO');if(!$i&&$_POST["export"]){save_settings(["output"=>$_POST["output"],"format"=>$_POST["format"]],"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}if(!$i&&$_POST["val"]){$ma=0;$Kk=true;$ab=[];$Dj=0;foreach($_POST["val"]as$K)$Dj+=count($K);$Oa=$Dj>1&&driver()->begin();foreach($_POST["val"]as$Uk=>$K){$R=bracket_escape($Uk,true);$k=fields($R);$Vk=indexes($R);foreach($K
as$s=>$J){parse_str(bracket_escape($s,true),$Z);$Sl=[];foreach($Z["where"]as$v=>$X)$Sl[bracket_escape($v,true)]=$X;if(!$k||$Z["null"]||array_diff_key($Sl,$k)||!unique_array($Sl,$Vk)){$Kk=false;break
2;}$O=[];$M=[];foreach($J
as$zf=>$X){$v=bracket_escape($zf,true);$j=idx($k,$v);if(!$j){$Kk=false;break
3;}$O[idf_escape($v)]=(preg_match('~char|text~',$j["type"])||$X!=""?adminer()->processInput($j,$X):"NULL");$M[$zf]=$v;}$Wi=where($Z,$k);if(!driver()->update($R,$O," WHERE $Wi",0," ")){$Kk=false;break
2;}$ma+=connection()->affected_rows;$d=[];foreach($M
as$v)$d[]=idf_escape($v);$bm=driver()->select($R,$d,[$Wi],$d);$Zg=($bm?$bm->fetch_row():[]);$uf=0;foreach($M
as$zf=>$v){$j=$k[$v];$Ik=['type'=>(preg_match('~binary~',$j["type"])?'blob':$j["type"])];$ab["val[$Uk][$s][$zf]"]=select_value(idx($Zg,$uf++),"",$Ik,null);}}}if($Oa&&$Kk)$Kk=driver()->commit();queries_redirect(null,lang_format(['%d item has been affected.','%d items have been affected.'],$ma),$Kk);if($Oa&&!$Kk)driver()->rollback();page_headers();page_messages($i);foreach($ab
as$A=>$X)echo"<div data-name='".h($A)."' hidden>$X</div>\n";exit;}restart_session();$De=&get_session("queries");$Ce=&$De[DB];if(!$i&&$_POST["clear"]){$Ce=[];redirect(remove_from_uri("history"));}stop_session();$ka=get_settings("adminer_import");if($_POST&&$ka)save_settings($ka,"adminer_import");page_header((isset($_GET["import"])?'Import':'SQL command'),$i);$dd=0;function
query_error_line($ol,$oh,$G,$Sf){return
substr_count(substr($ol,0,$oh),"\n")+($Sf?:substr_count(substr($G,0,strspn($G," \t\r\n")),"\n")+1);}if(!$i&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$mc=driver()->delimiter;$n=false;if(!isset($_GET["import"]))$G=$_POST["query"];elseif($_POST["webfile"]){$vk=adminer()->importServerPath();$n=@fopen((file_exists($vk)?$vk:"compress.zlib://$vk.gz"),"rb");$G=($n?fread($n,1e6):false);}else$G=get_file("sql_file",true,$mc);if(is_string($G)){if(($sg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($sg,strval(2*strlen($G)+memory_get_usage()+8e6)));if($G!=""&&strlen($G)<1e6){$Ri=$G.(preg_match("~$mc\\s*\$~",$G)?"":$mc);if(!$Ce||first(end($Ce))!=$Ri){restart_session();$Ce[]=[$Ri,time()];set_session("queries",$De);stop_session();}}$oh=0;$Vi=0;$Uc=true;$Kb=false;$zg=false;$Bb=connect();if($Bb&&DB!=""){$Bb->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$Bb);}$vb=0;$ed=[];$ai='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Tf.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$Bl=microtime(true);while($G!=""){if(!$oh&&preg_match("~^$sk*+DELIMITER\\s+(\\S+)~i",$G,$_)){$mc=preg_quote($_[1]);$G=substr($G,strlen($_[0]));$Vi+=strlen($_[0]);}elseif(!$oh&&JUSH=='pgsql'&&preg_match("~^($sk*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$G,$_)){$mc="\n\\\\\\.\r?\n";$Kb=true;$oh=strlen($_[0]);}elseif(!$oh&&JUSH=='pgsql'&&preg_match("~^$sk*+\\\\connect[ \t][^\n]*~",$G,$_)){$mc="\n";$zg=true;$oh=strlen($_[0]);}else{preg_match("($mc\\s*|$ai)",$G,$_,PREG_OFFSET_CAPTURE,$oh);list($Td,$F)=$_[0];if(!$Td&&$n&&!feof($n))$G
.=fread($n,1e5);else{if(!$Td&&rtrim($G)=="")break;$oh=$F+strlen($Td);if($Td&&!preg_match("(^$mc)",$Td)){$Wa=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($F>0&&strtolower($G[$F-1])=="e"));$pi=($Td=='/*'?'\*/':($Td=='['?']':(preg_match("~^(?:$Tf)~",$Td)?"\n":preg_quote($Td).($Wa?'|\\\\.':''))));while(preg_match("($pi|\$)s",$G,$_,PREG_OFFSET_CAPTURE,$oh)){$Ej=$_[0][0];if(!$Ej&&$n&&!feof($n))$G
.=fread($n,1e5);else{$oh=$_[0][1]+strlen($Ej);if(!$Ej||$Ej[0]!="\\")break;}}}else{$Ri=substr($G,0,$F+($Kb?3:0));$G=substr($G,$oh);$Si=$Vi;$Vi+=$oh;$oh=0;if($Kb||$zg){$mc=driver()->delimiter;$Kb=false;$zg=false;}$ob="<code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($Ri)."</code>";if(preg_match("~^$sk*+\$~",$Ri)&&!preg_match('~/\*M?!~',$Ri)){echo($_POST["only_errors"]?"":"<pre>$ob</pre>\n");continue;}$Uc=false;$vb++;$Ki="<pre id='sql-$vb'>$ob</pre>\n";if(JUSH=="sqlite"&&preg_match($_k,$Ri,$_)!==0){echo$Ki,"<p class='error'>".sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$_[1])?'ATTACH':'VACUUM INTO')."\n";$ed[]=" <a href='#sql-$vb'>$vb</a>";if(!$dd&&!isset($_GET["import"]))$dd=query_error_line($_POST["query"],$Si,$Ri,0);if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Ki;ob_flush();flush();}$Bk=microtime(true);if(JUSH=="pgsql"&&preg_match("~^$sk*+\\\\connect\\s+(\"(?:[^\"]|\"\")+\"|[^\\s\";]+)\\s*;*\\s*\$~",$Ri,$_)){$g=idf_unescape($_[1]);if(connection()->select_db($g)){if($Bb)$Bb->select_db($g);echo($_POST["only_errors"]?"":"<p class='message'>".'Database'.": ".h($g)." <span class='time'>(".format_time($Bk).")</span>\n");continue;}}elseif(connection()->multi_query($Ri)&&$Bb&&preg_match("~^$sk*+USE\\b~i",$Ri))$Bb->query($Ri);do{$H=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Ki:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".adminer()->error()."\n";$ed[]=" <a href='#sql-$vb'>$vb</a>";if(!$dd&&!isset($_GET["import"]))$dd=query_error_line($_POST["query"],$Si,$Ri,driver()->errorLine($Ri));if($_POST["error_stops"])break
2;}else{$y=ME."sql=".url_escape(trim($Ri));$rl=" <span class='time'>(".format_time($Bk).")</span>".(strlen($y)<adminer()->sqlUrlLength()-100?" <a href='".h($y)."'>".'Edit'."</a>":"");$ma=connection()->affected_rows;$_m=($_POST["only_errors"]?"":driver()->warnings());$Am="warnings-$vb";if($_m)$rl
.=", <a href='#$Am' class='toggle'>".'Warnings'."</a>";$pd="";$qd="explain-$vb";if(is_object($H)){$x=$_POST["limit"];$gh=$x;$Lc=!$_POST["only_errors"];if($Lc)echo"<form method='post'>\n";$Jh=print_select_result($H,$Bb,[],$gh,$Lc);if(!$_POST["only_errors"]){$gh=max($H->num_rows,$gh);echo"<p class='sql-footer'>".($gh?($x&&$gh>$x?sprintf('%d / ',$x):"").lang_format(['%d row','%d rows'],$gh):""),$rl;if($Bb&&preg_match("~^($sk|\\()*+SELECT\\b~i",$Ri)&&($pd=adminer()->explain($Bb,$Ri,$Jh))!="")echo", <a href='#$qd' class='toggle'>Explain</a>";if($Lc)echo", <input type='submit' name='save' value='".'Save'."' class='jsonly' disabled"." title='".'Ctrl+click on a value to modify it.'."'".on('click','sqlSave','Saving…').">";$r="export-$vb";echo", <a href='#$r' class='toggle'>".'Export'."</a><span id='$r' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ka["output"])." ".html_select("format",adminer()->dumpFormat(),$ka["format"]).input_hidden("query",$Ri)."<input type='submit' name='export' value='".'Export'."'".($x?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$sk*+(CREATE|DROP|ALTER)$sk++(DATABASE|SCHEMA)\\b~i",$Ri)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(['Query executed OK, %d row affected.','Query executed OK, %d rows affected.'],$ma)."$rl\n";}echo($_m?"<div id='$Am' class='hidden'>\n$_m</div>\n":""),($pd!=""?"<div id='$qd' class='hidden explain'>\n$pd</div>\n":"");}$Bk=microtime(true);}while(connection()->next_result());}}}}}if($Uc)echo"<p class='message'>".'No commands to execute.'."\n";else{$Re=connection()->inTransaction();driver()->rollback();if($Re)echo"<pre><code class='jush-".JUSH."'>ROLLBACK".(JUSH=="mssql"?" TRANSACTION":"")." -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang_format(['%d query executed OK.','%d queries executed OK.'],$vb-count($ed))," <span class='time'>(".format_time($Bl).")</span>\n";elseif($ed&&$vb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$ed)."\n";}}else
echo"<p class='error'>".upload_error($G)."\n";}echo'
<form method="post" enctype="multipart/form-data" id="form"';$cm="";if(!isset($_GET["import"]))echo
on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"),adminer()->sqlUrlLength());else
echo
on_upload_progress($cm);echo'>
';$md="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$Ri=$_GET["sql"];if($_POST)$Ri=$_POST["query"];elseif($_GET["history"]=="all")$Ri=$Ce;elseif($_GET["history"]!="")$Ri=idx($Ce[$_GET["history"]],0);echo"<p>";textarea("query",$Ri,20,80,JUSH,$dd);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$md\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$me=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",($cm?input_hidden(ini_get("session.upload_progress.name"),$cm):""),"SQL$me: ".file_input(" name='sql_file[]' multiple","\n$md"),($cm?" <progress class='jsonly hidden' max='1' value='0'></progress>":""),"</div></fieldset>\n";$Oe=adminer()->importServerPath();if($Oe)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Oe)."$me</code>")," <input type='submit' name='webfile' value='".'Run file'."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Ce){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($Ce);$X;$X=prev($Ce)){$v=key($Ce);list($Ri,$rl,$Qc)=$X;echo'<div><a href="'.h(ME."sql=&history=$v").'" class="hover">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$rl)."'>".@date("H:i:s",$rl)."</span>"." <code class='sql jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$Tf).*~m",'',$Ri))),80,"</code>").($Qc?" <span class='time'>($Qc)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$k=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$k):""):where($_GET,$k));$Zl=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($k
as$A=>$j){if((!$Zl&&!isset($j["privileges"]["insert"]))||adminer()->fieldName($j)=="")unset($k[$A]);}if($_POST&&!$i&&!isset($_GET["select"])){$z=relative_uri((string)$_POST["referer"]);if($_POST["insert"])$z=($Zl?null:relative_uri());elseif(!preg_match('~^.+&select=.+$~',$z))$z=ME."select=".url_escape($a);$u=indexes($a);$Tl=unique_array($_GET["where"],$u);$Wi="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($z,'Item has been deleted.',driver()->delete($a,$Wi,$Tl?0:1));else{$O=[];foreach($k
as$A=>$j){$X=process_input($j);if($X!==false&&$X!==null)$O[idf_escape($A)]=$X;}if($Zl){if(!$O)redirect($z);queries_redirect($z,'Item has been updated.',driver()->update($a,$O,$Wi,$Tl?0:1));if(is_ajax()){page_headers();page_messages($i);exit;}}else{$H=driver()->insert($a,$O);$If=($H?last_id($H):0);queries_redirect($z,sprintf('Item%s has been inserted.',($If?" $If":"")),$H);}}}$J=null;$G="";$rl="";if($Z){$M=[];$Rj=["*"];foreach($k
as$A=>$j){if(isset($j["privileges"]["select"])){$_a=($_POST["clone"]&&$j["auto_increment"]?"''":convert_field($j));$c=($_a?"$_a AS ":"").idf_escape($A);$M[]=$c;if($_a)$Rj[]=$c;}}$J=[];if(!support("table")){$M=["*"];$Rj=$M;}if($M){$Bk=microtime(true);$H=driver()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));$G=str_replace("SELECT ".implode(", ",$M),"SELECT ".implode(", ",$Rj),driver()->query);$rl=format_time($Bk);if(!$H)$i=adminer()->error();else{$J=$H->fetch_assoc();if(!$J)$J=false;}if(isset($_GET["select"])&&(!$J||$H->fetch_assoc()))$J=null;}}if(!$k&&driver()->primary!=""){if(!$Z){$H=driver()->select($a,["*"],[],["*"]);$J=($H?$H->fetch_assoc():false);if(!$J)$J=[driver()->primary=>""];}if($J){foreach($J
as$v=>$X){if(!$Z)$J[$v]=null;$k[$v]=["field"=>$v,"null"=>($v!=driver()->primary),"auto_increment"=>($v==driver()->primary)];}}}if($_POST["save"]){$Ai=[];foreach((array)$_POST["fields"]as$v=>$X)$Ai[bracket_escape($v,true)]=$X;$J=$Ai+($J?$J:[]);}edit_form($a,$k,$J,$Zl,$i,$G,$rl);}elseif(isset($_GET["create"])){function
referencable_primary($Tj){$I=[];foreach(table_status('',true)as$Xk=>$R){if($Xk!=$Tj&&!$R["dependent"]&&fk_support($R)){foreach(fields($Xk)as$j){if($j["primary"]){if($I[$Xk]){unset($I[$Xk]);break;}$I[$Xk]=$j;}}}}return$I;}$a=$_GET["create"];$ei=driver()->partitionBy;$ii=($ei&&$a!=""?driver()->partitionsInfo($a):[]);$ej=referencable_primary($a);$Rd=[];foreach($ej
as$Xk=>$j)$Rd[str_replace("`","``",$Xk)."`".str_replace("`","``",$j["field"])]=$Xk;$Mh=[];$S=[];$eh=false;if($a!=""){$Mh=fields($a);$S=table_status1($a);$eh=(count($S)<2);}$ua=($a==""||driver()->supportsAlterTable($S));$J=$_POST;$J["fields"]=(array)$J["fields"];if($J["auto_increment_col"])$J["fields"][$J["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$i)save_settings(["comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]]);if($_POST&&!process_fields($J["fields"])&&!$i){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables([$a]));else{$k=[];$ra=[];$gm=false;$Pd=[];$Lh=reset($Mh);$oa=" FIRST";foreach($J["fields"]as$j){$m=$Rd[$j["type"]];$Ml=($m!==null?$ej[$m]:$j);if($j["field"]!=""){if(!$j["generated"])$j["default"]=null;$j["default_sql"]=($j["generated"]=="DEFAULT SQL");$Pi=process_field($j,$Ml);$ra[]=[$j["orig"],$Pi,$oa];if(!$Lh||$Pi!==process_field($Lh,$Lh)){$k[]=[$j["orig"],$Pi,$oa];if($j["orig"]!=""||$oa||(JUSH=="sqlite"&&$j["comment"]!=""))$gm=true;}if($m!==null)$Pd[idf_escape($j["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$Rd[$j["type"]],'source'=>[$j["field"]],'target'=>[$Ml["field"]],'on_delete'=>$j["on_delete"],],object_name("FOREIGN",trim($J["name"]),[$j["field"]]));$oa=" AFTER ".idf_escape($j["field"]);}elseif($j["orig"]!=""){$gm=true;$k[]=[$j["orig"]];}if($j["orig"]!=""){$Lh=next($Mh);if(!$Lh)$oa="";}}$gi=[];if(in_array($J["partition_by"],$ei)){foreach($J
as$v=>$X){if(preg_match('~^partition~',$v))$gi[$v]=$X;}foreach($gi["partition_names"]as$v=>$A){if($A==""){unset($gi["partition_names"][$v]);unset($gi["partition_values"][$v]);}}$gi["partition_names"]=array_values($gi["partition_names"]);$gi["partition_values"]=array_values($gi["partition_values"]);if($gi==$ii)$gi=[];}elseif(preg_match("~partitioned~",$S["Create_options"]))$gi=null;$ug='Table has been altered.';if($a==""){cookie("adminer_engine",$J["Engine"]);$ug='Table has been created.';}$A=trim($J["name"]);$z=ME.(support("table")?"table=":"select=").url_escape($A);$wb=($J["Comment"]!=$S["Comment"]?$J["Comment"]:null);$Xc=($J["Engine"]&&$J["Engine"]!=$S["Engine"]?$J["Engine"]:"");$H=alter_table($a,$A,(JUSH=="sqlite"&&($gm||$Pd||$wb!==null||$Xc)?$ra:$k),$Pd,$wb,$Xc,($J["Collation"]&&$J["Collation"]!=$S["Collation"]?$J["Collation"]:""),($J["Auto_increment"]!=""?number($J["Auto_increment"]):""),$gi);if($H&&!Queries::$queries&&$a!=""&&!$k&&!$Pd)redirect($z);queries_redirect($z,$ug,$H);}}$Dk=($a!=""?"alter":"create");page_header(($a!=""?'Alter table':'Create table'),$i,["table"=>$a],h($a),$eh,doc_link(['sql'=>"$Dk-table.html",'mariadb'=>($a!=""?"$Dk-table":""),]));if(!$_POST){$Pl=driver()->types();$J=["Engine"=>$_COOKIE["adminer_engine"],"fields"=>[["field"=>"","type"=>(isset($Pl["int"])?"int":(isset($Pl["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$J=$S;$J["name"]=$a;$J["fields"]=[];if(!$_GET["auto_increment"])$J["Auto_increment"]="";foreach($Mh
as$j){if($j["generated"])$j["default"]=ltrim($j["default"]);$j["generated"]=$j["generated"]?:(isset($j["default"])?($j["default_sql"]?"DEFAULT SQL":"DEFAULT"):"");$J["fields"][]=$j;}if($ei){$J+=$ii;$J["partition_names"][]="";$J["partition_values"][]="";}}}$sb=flat_collations();$Yc=driver()->engines();foreach($Yc
as$Xc){if(!strcasecmp($Xc,$J["Engine"])){$J["Engine"]=$Xc;break;}}$fg=max_input_vars(12,20);if($fg){$_e=(count($J["fields"])>$fg?"":" hidden");echo"<p".($_e?" id='max-fields' data-columns='$fg'":"")." class='error$_e'>".max_input_vars_error()."\n";}echo'
<form method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($J["name"])."' autocapitalize='off'>\n",(!$ua?h($S["Engine"])."\n":($Yc?html_select("Engine",[""=>"(".'engine'.")"]+$Yc,$J["Engine"],on('change','helpClose').on_help_value())."\n":""));if($sb)echo"<datalist id='collations'>".optionlist($sb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($J["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")&&$ua){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($J["fields"],$sb,"TABLE",$Rd);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($J["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',on('click','columnShowClick',6),"jsonly");$yb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$yb,'Comment',on('click','editingCommentsClick',true),"jsonly").' ';$b=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($yb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$b,$J["Comment"]);}echo'<p>
<input type=\'submit\' value=\'Save\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';if($ei&&(JUSH=='sql'||$a=="")){$fi=preg_match('~RANGE|LIST~',$J["partition_by"]);print_fieldset("partition",'Partition by',$J["partition_by"]);echo"<p>".html_select("partition_by",array_merge([""],$ei),$J["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($J["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($fi||!$J["partition_by"]?" hidden":"")."' value='".h($J["partitions"])."'>\n","<table id='partition-table'".($fi?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."<tbody>\n";foreach($J["partition_names"]as$v=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off"'.($v==count($J["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($J["partition_values"],$v)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$We=["PRIMARY","UNIQUE","INDEX"];$S=table_status1($a,true);$Ue=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$We[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$We[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$S["Engine"]))$We[]="VECTOR";$u=indexes($a);$k=fields($a);$Ji=[];if(JUSH=="mongo"){$Ji=$u["_id_"];unset($We[0]);unset($u["_id_"]);}$J=$_POST;if($J)save_settings(["index_options"=>$J["options"]]);if($_POST&&!$i&&!$_POST["add"]&&!$_POST["drop_col"]){$ta=[];foreach($J["indexes"]as$t){$A=$t["name"];if(in_array($t["type"],$We)){$d=[];$Pf=[];$pc=[];$zh=[];$Ve=(support("partial_indexes")?$t["partial"]:"");$Te=(in_array($t["algorithm"],$Ue)?$t["algorithm"]:"");$O=[];ksort($t["columns"]);foreach($t["columns"]as$v=>$c){if($c!=""){$w=idx($t["lengths"],$v);$nc=idx($t["descs"],$v);$yh=idx($t["opclasses"],$v);$O[]=($k[$c]?idf_escape($c):$c).($w?"(".(+$w).")":"").($yh!=""?" ".idf_escape($yh):"").($nc?" DESC":"");$d[]=$c;$Pf[]=($w?:null);$pc[]=$nc;$zh[]="$yh";}}$nd=$u[$A];if($nd){ksort($nd["columns"]);ksort($nd["lengths"]);ksort($nd["descs"]);if($t["type"]==$nd["type"]&&array_values($nd["columns"])===$d&&(!$nd["lengths"]||array_values($nd["lengths"])===$Pf)&&array_values($nd["descs"])===$pc&&(!$nd["opclasses"]||array_values($nd["opclasses"])===$zh)&&$nd["partial"]==$Ve&&(!$Ue||$nd["algorithm"]==$Te)&&!$nd["invalid"]){unset($u[$A]);continue;}}if($d)$ta[]=[$t["type"],$A,$O,$Te,$Ve];}}foreach($u
as$A=>$nd)$ta[]=[$nd["type"],$A,"DROP"];if(!$ta)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),'Indexes have been altered.',alter_indexes($a,$ta));}page_header('Indexes',$i,["table"=>$a],h($a),false,doc_link(['sql'=>"create-index.html",]));$Dd=array_keys($k);if($_POST["add"]){foreach($J["indexes"]as$v=>$t){if($t["columns"][count($t["columns"])]!="")$J["indexes"][$v]["columns"][]="";}$t=end($J["indexes"]);if($t["type"]||array_filter($t["columns"],'strlen'))$J["indexes"][]=["columns"=>[1=>""]];}if(!$J){foreach($u
as$v=>$t){$u[$v]["name"]=$v;$u[$v]["columns"][]="";}$u[]=["columns"=>[1=>""]];$J["indexes"]=$u;}$Pf=(JUSH=="sql"||JUSH=="mssql");$zh=driver()->indexOpclasses();$ik=($_POST?$_POST["options"]:get_setting("index_options"));$Rg=[];foreach($We
as$U)$Rg[$U]=str_replace("{table}",$a,adminer()->namePattern($U));echo'
<form method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">Index Type
';$Me=" class='idxopts".($ik?"":" hidden")."'";if($Ue)echo"<th id='label-algorithm'$Me>".'Algorithm'.doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/',]);echo'<th><input type="submit" hidden>','Columns'.($Pf?"<span$Me> (".'length'.")</span>":"");if($Pf||support("descidx"))echo
checkbox("options",1,$ik,'Options',on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Me>".'Condition';echo'<td><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
<tbody>
';if($Ji){echo"<tr><td>PRIMARY<td>";foreach($Ji["columns"]as$v=>$c)echo
select_input(" disabled",array_combine($Dd,$Dd),$c),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$uf=1;foreach($J["indexes"]as$t){if(!$_POST["drop_col"]||$uf!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$uf][type]",[-1=>""]+$We,$t["type"],on('change','indexesChangeType',$Rg),"label-type");if($Ue)echo"<td$Me>".html_select("indexes[$uf][algorithm]",array_merge([""],$Ue),$t['algorithm'],"","label-algorithm");echo"<td>";ksort($t["columns"]);$q=1;foreach($t["columns"]as$v=>$c){echo"<span>".select_input(" name='indexes[$uf][columns][$q]' title='".'Column'."'".on('change','indexesChangeColumn',$Rg),($k&&($c==""||$k[$c])?array_combine($Dd,$Dd):[]),$c)," <span$Me>",($Pf?"<input type='number' name='indexes[$uf][lengths][$q]' class='size' value='".h(idx($t["lengths"],$v))."' title='".'Length'."'>":"");if($zh){$yh=idx($t["opclasses"],$v);echo
html_select("indexes[$uf][opclasses][$q]",[""=>"(".'operator class'.")"]+array_combine($zh,$zh)+($yh!=""?[$yh=>$yh]:[]),$yh),'';}echo(support("descidx")?checkbox("indexes[$uf][descs][$q]",1,idx($t["descs"],$v),'descending'):""),"<br>","</span></span>";$q++;}echo"<td><input name='indexes[$uf][name]' value='".h($t["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Me><input name='indexes[$uf][partial]' value='".h($t["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$uf]","x",'Remove',on('click','editingRemoveRow','indexes$1[type]')),(idx($u[$t["name"]],"invalid")?" <span class='error'>INVALID</span>":""),"\n";}$uf++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'Save\'>
';if(support("concurrent_indexes"))echo
checkbox("concurrently",1,$_POST["concurrently"],"concurrently").''."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["database"])){$J=$_POST;if($_POST&&!$i&&!$_POST["add"]){$A=trim($J["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases([DB]));}elseif($A!==DB){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($A),'Database has been renamed.',rename_database($A,(string)$J["collation"]));}else{$f=explode("\n",str_replace("\r","",$A));$Kk=true;$Gf="";foreach($f
as$g){if(count($f)==1||$g!=""){if(!create_database($g,(string)$J["collation"]))$Kk=false;$Gf=$g;}}restart_session();set_session("dbs",null);queries_redirect(preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($Gf),'Database has been created.',$Kk);}}else{if(!$J["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$J["collation"])?" COLLATE $J[collation]":""),substr(ME,0,-1),'Database has been altered.');}}$Dk=(DB!=""?"alter":"create");page_header(DB!=""?'Alter database':'Create database',$i,[],h(DB),false,doc_link(['sql'=>"$Dk-database.html",'mariadb'=>(DB!=""?"":"$Dk-database"),]));$sb=collations();$A=DB;if($_POST)$A=$J["name"];elseif(DB!="")$J["collation"]=db_collation(DB,$sb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$ee){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$ee,$_)&&$_[1]){$A=stripcslashes(idf_unescape("`$_[2]`"));break;}}}echo'
<form method="post">
<p>
',($_POST["add"]||strpos($A,"\n")?"<textarea autofocus name='name' rows='10' cols='40'>\n".h($A).'</textarea><br>':'<input name="name" autofocus value="'.h($A).'" data-maxlength="64" autocapitalize="off">')."\n",($sb?"<datalist id='collations'>".optionlist(flat_collations())."</datalist>"."<input list='collations' name='collation' value='".h($J["collation"])."' placeholder='(".'collation'.")'>".doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",]):"")."\n",'<input type=\'submit\' value=\'Save\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);$Bj=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$xj=routine($_GET["call"],$Bj);$rf=(idx($xj["returns"],"type")=="trigger");page_header(($rf?'Triggers':'Call').": ".h($ba),$i,"#routines","",!$xj,(isset($_GET["callf"])?"":doc_link(['sql'=>"call.html",])));$Pe=[];$Rh=[];foreach($xj["fields"]as$q=>$j){if(substr($j["inout"],-3)=="OUT"&&JUSH=='sql')$Rh[$q]="@".idf_escape($j["field"])." AS ".idf_escape($j["field"]);if(!$j["inout"]||preg_match('~^(IN|OUTPUT)~',$j["inout"]))$Pe[]=$q;}if(!$i&&$_POST){$Xa=[];foreach($xj["fields"]as$v=>$j){$X="";if(in_array($v,$Pe)){$X=process_input($j);if($X===false)$X="''";if(isset($Rh[$v]))connection()->query("SET @".idf_escape($j["field"])." = $X");}if(isset($Rh[$v]))$Xa[]="@".idf_escape($j["field"]);elseif(in_array($v,$Pe))$Xa[]=$X;}$ya=implode(", ",$Xa);$G=(isset($_GET["callf"])||JUSH!="mssql"?(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($xj["returns"],"type")=="record"?"* FROM ":"").table($ba)."($ya)":"EXEC ".table($ba).($ya!=""?" $ya":""));$Bk=microtime(true);$H=connection()->multi_query($G);$ma=connection()->affected_rows;echo
adminer()->selectQuery($G,$Bk,!$H);if(!$H)echo"<p class='error'>".adminer()->error()."\n";else{$Bb=connect();if($Bb)$Bb->select_db(DB);do{$H=connection()->store_result();if(is_object($H))print_select_result($H,$Bb);else
echo"<p class='message'>".lang_format(['Routine has been called, %d row affected.','Routine has been called, %d rows affected.'],$ma)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($Rh)print_select_result(connection()->query("SELECT ".implode(", ",$Rh)));}}if($rf){$Jl=driver()->routineTriggers($_GET["call"]);if($Jl){echo"<table>\n";foreach($Jl
as$J){$y=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($J["ns"]),ME);echo"<tr><th>".h($J["trigger"])."<td><a href='".h($y."table=".url_escape($J["table"])."#triggers")."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>"."<td class='hover'><a href='".h($y."trigger=".url_escape($J["table"])."&name=".url_escape($J["trigger"]))."'>".'Alter'."</a>\n";}echo"</table>\n";}}else{echo'<form method="post">
';if($Pe){echo"<table class='layout'>\n";foreach($Pe
as$v){$j=$xj["fields"][$v];$A=$j["field"];echo"<tr><th>".adminer()->fieldName($j);$Y=idx($_POST["fields"],$A);if($Y!=""){if($j["type"]=="set")$Y=implode(",",$Y);}input($j,$Y,idx($_POST["function"],$A,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'Call\'>
',input_token(),'</form>
';}echo'
',adminer()->commentValue($Bj,$xj['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$J=$_POST;if($_POST&&!$i&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$J["source"]=array_filter($J["source"],'strlen');ksort($J["source"]);$il=[];foreach($J["source"]as$v=>$X)$il[$v]=$J["target"][$v];$J["target"]=$il;}$Oa=false;if(JUSH=="sqlite")$H=recreate_table($a,$a,[],[],[" $A"=>($J["drop"]?"":" ".format_foreign_key($J))]);else{$Cb=$J["constraint"];$ta="ALTER TABLE ".table($a);$Gc="$ta DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A?:'');if($J["drop"])$H=queries($Gc);else{$ha=" ADD".format_foreign_key($J,$Cb);if($A=="")$H=queries($ta.$ha);elseif(JUSH=="pgsql"||(JUSH=="sql"&&$Cb!=$A))$H=queries("$Gc,$ha");else{$Oa=support("transaction_ddl")&&driver()->begin();$H=($Oa||idx(foreign_keys($a),$A)?queries($Gc):true)&&queries($ta.$ha)&&(!$Oa||driver()->commit());}}}queries_redirect(ME."table=".url_escape($a),($J["drop"]?'Foreign key has been dropped.':($A!=""?'Foreign key has been altered.':'Foreign key has been created.')),$H);if($Oa)driver()->rollback();if(!$J["drop"])$i='Source and target columns must have the same data type, there must be an index on the target columns and the referenced data must exist.';}$eh=false;if(!$_POST&&$A!=""){$Rd=foreign_keys($a);$J=idx($Rd,$A,[]);$eh=!$J;}$Qg=str_replace("{table}",$a,adminer()->namePattern("FOREIGN"));$Tg="^".str_replace('\{columns\}','.*',preg_quote($Qg))."$";page_header(($A!=""?'Alter foreign key':'Create foreign key'),$i,["table"=>$a],h($A!=""?$A:$a),$eh,doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",]));if($_POST){ksort($J["source"]);if($_POST["change"]||$_POST["change-js"])$J["target"]=[];else$J["source"][]="";}elseif($A!=""){$J["source"][]="";$J["constraint"]=$A;}else{$J["table"]=$a;$J["source"]=[""];$J["constraint"]=str_replace("{columns}","",$Qg);}echo'
<form method="post">
';$qk=array_keys(fields($a));if($J["db"]!="")connection()->select_db($J["db"]);$Nh=null;if($J["ns"]!=""){$Nh=get_schema();set_schema($J["ns"]);}$dj=array_keys(array_filter(table_status('',true),function(array$S){return!$S["dependent"]&&fk_support($S);}));$il=array_keys(fields(in_array($J["table"],$dj)?$J["table"]:reset($dj)));$b=on('change','foreignChange');echo"<p><label>".'Target table'.": ".html_select("table",$dj,$J["table"],$b)."</label>\n";if(JUSH!="sqlite"){$Sj=($J["db"]!=""?$J["db"]:DB);$dc=[];foreach(adminer()->databases()as$g){if(!driver()->isSystem($g)||$g==$Sj||$g==DB)$dc[]=$g;}echo"<label>".'DB'.": ".html_select("db",$dc,$Sj,$b)."</label>";}if($J["db"]!="")connection()->select_db(DB);echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'Change\'></noscript>
<table',on('change','foreignName',$Tg,$Qg),'>
<thead><tr><th id="label-source">Source<th id="label-target">Target<tbody>
';$uf=0;foreach($J["source"]as$v=>$X){echo"<tr>","<td>".html_select("source[".(+$v)."]",[-1=>""]+$qk,$X,($uf==count($J["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$v)."]",$il,idx($J["target"],$v),"","label-target");$uf++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",[-1=>""]+explode("|",driver()->onActions),$J["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",[-1=>""]+explode("|",driver()->onActions),$J["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$J["deferrable"]):''),'<p>
<input type=\'submit\' value=\'Save\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'Add column\'></noscript>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$A)),'>
';if(JUSH!="sqlite"){print_fieldset("options",'Options',$J["constraint"]!=$A&&!preg_match("($Tg)",$J["constraint"]));echo"<label>".'Name'.": <input name='constraint' value='".h($J["constraint"])."' autocapitalize='off'></label>\n","</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$J=$_POST;$Oh="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$Oh=strtoupper($P["Engine"]);}if($_POST&&!$i){$A=trim($J["name"]);$_a=" AS\n$J[select]";$z=ME."table=".url_escape($A);$ug='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&JUSH!="sqlite"&&$U=="VIEW"&&$Oh=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$_a,$z,$ug);else{$ml="adminer_".uniqid();drop_create("DROP $Oh ".table($a),"CREATE $U ".table($A).$_a,"DROP $U ".table($A),"CREATE $U ".table($ml).$_a,"DROP $U ".table($ml),($_POST["drop"]?substr(ME,0,-1):$z),'View has been dropped.',$ug,'View has been created.',$a,$A);}}$eh=false;if(!$_POST&&$a!=""){$J=view($a);$eh=!$J["select"];$J["name"]=$a;$J["materialized"]=($Oh!="VIEW");if(!$i)$i=adminer()->error();}page_header(($a!=""?'Alter view':'Create view'),$i,["table"=>$a],h($a),$eh,doc_link(['sql'=>"create-view.html",]));echo'
<form method="post">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$J["materialized"],'Materialized view'):""),'<p>';textarea("select",$J["select"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$hf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$Fk=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$J=$_POST;if($_POST&&!$i){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($J["INTERVAL_FIELD"],$hf)&&isset($Fk[$J["STATUS"]])){$Fj="\nON SCHEDULE ".($J["INTERVAL_VALUE"]?"EVERY ".q($J["INTERVAL_VALUE"])." $J[INTERVAL_FIELD]".($J["STARTS"]?" STARTS ".q($J["STARTS"]):"").($J["ENDS"]?" ENDS ".q($J["ENDS"]):""):"AT ".q($J["STARTS"]))." ON COMPLETION".($J["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Fj.($aa!=$J["EVENT_NAME"]?"\nRENAME TO ".idf_escape($J["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($J["EVENT_NAME"]).$Fj)."\n".$Fk[$J["STATUS"]]." COMMENT ".q($J["EVENT_COMMENT"]).rtrim(" DO\n$J[EVENT_DEFINITION]",";").";"));}}$eh=false;if(!$J&&$aa!=""){$K=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$eh=!$K;$J=reset($K);}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$i,"#events","",$eh,doc_link(['sql'=>"create-event.html"]));echo'
<form method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($J["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$J[EXECUTE_AT]$J[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($J["ENDS"]),'">
<tr><th>Every
<td><input type="number" name="INTERVAL_VALUE" value="',h($J["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$hf,$J["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$Fk,$J["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($J["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$J["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$J["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$xj=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$J=$_POST;$J["fields"]=(array)$J["fields"];if($_POST&&!process_fields($J["fields"])&&!$i){foreach($J["fields"]as$v=>$j){if($j["field"]=="")unset($J["fields"][$v]);}$th=routine($_GET["procedure"],$xj);$rh=($th?routine_id($ba,$th):"");$Xg=routine_id($J["name"],$J);$Mb=create_routine($xj,$J);$z=substr(ME,0,-1);$ug='Routine has been altered.';if(!$_POST["drop"]&&$rh==$Xg&&connection()->flavor!="mysql")queries_redirect($z,$ug,queries(substr_replace($Mb,(JUSH=="mssql"?' OR ALTER':' OR REPLACE'),6,0)));else{$ml="adminer_".uniqid();drop_create("DROP $xj $rh",$Mb,"DROP $xj $Xg",create_routine($xj,["name"=>$ml]+$J),"DROP $xj ".routine_id($ml,$J),$z,'Routine has been dropped.',$ug,'Routine has been created.',$ba,$J["name"]);}}$eh=false;if(!$_POST&&$ba!=""){$J=routine($_GET["procedure"],$xj);$eh=!$J;$J["name"]=$ba;}$_j=strtolower($xj);page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$i,"#routines","",$eh,doc_link(['sql'=>"create-procedure.html",'mariadb'=>"create-$_j/",]));if(!$_POST&&$ba=="")$J["language"]="sql";$sb=(JUSH=="sql"?flat_collations():[]);$yj=routine_languages();echo($sb?"<datalist id='collations'>".optionlist($sb)."</datalist>":""),'
<form method="post" id="form">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',($yj?"<label>".'Language'.": ".html_select("language",array_keys($yj),$J["language"],on('change','routineLanguage',$yj))."</label>\n":""),'<input type=\'submit\' value=\'Save\'>
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($J["fields"],$sb,$xj);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$J["returns"],$sb,[],(JUSH=="pgsql"?["void","trigger"]:[]));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$J["definition"],20,80,($yj[$J["language"]]?:JUSH));echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($ba!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$ba)),'>
';$Aj=routine_options($xj);if($Aj){$Eh=false;foreach($Aj
as$v=>$sm){$h=($sm?reset($sm):"");$J["options"][$v]=idx($J["options"],$v,$h);if($J["options"][$v]!=$h)$Eh=true;}print_fieldset("options",'Options',$Eh);echo"<table class='layout'>\n";foreach($Aj
as$v=>$sm){$Bf="label-option-$v";$ul=str_replace("_"," ",$v);$M=[];foreach($sm
as$Y)$M[$Y]=(strpos($Y,"$ul ")===0?substr($Y,strlen($ul)+1):$Y);echo"<tr><th id='$Bf'>$ul<td>".($M?html_select("options[$v]",$M,$J["options"][$v],"",$Bf):"<input name='options[$v]' value='".h($J["options"][$v])."' aria-labelledby='$Bf' autocapitalize='off'>")."\n";}echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$A="$_GET[name]";$J=$_POST;if($J&&!$i){$z=ME."table=".url_escape($a);$xg='Check has been dropped.';$vg='Check has been altered.';$wg='Check has been created.';if(JUSH=="sqlite"&&!(min_version(3.53)&&($A==""||idx(driver()->checkConstraints($a),$A)!=$A))){$ha=($J["drop"]?"":($J["name"]!=""?"CONSTRAINT ".idf_escape($J["name"])." ":"")."CHECK ($J[clause])");queries_redirect($z,($J["drop"]?$xg:($A!=""?$vg:$wg)),recreate_table($a,$a,[],[],[],"",[],"$A",$ha));}else{$ta="ALTER TABLE ".table($a);$fb=" CHECK ($J[clause])";$ml="adminer_".uniqid();drop_create("$ta DROP CONSTRAINT ".idf_escape($A),"$ta ADD".($J["name"]!=""?" CONSTRAINT ".idf_escape($J["name"]):"").$fb,"$ta DROP CONSTRAINT ".idf_escape($J["name"]),"$ta ADD CONSTRAINT ".idf_escape($ml).$fb,"$ta DROP CONSTRAINT ".idf_escape($ml),$z,$xg,$vg,$wg,$A,$J["name"]);}}$eh=false;if(!$J){$ib=driver()->checkConstraints($a);$eh=($A!=""&&!$ib[$A]);$J=["name"=>($A==""?object_name("CHECK",$a,[]):($ib[$A]!=$A?$A:"")),"clause"=>$ib[$A]];}page_header(($A!=""?'Alter check':'Create check'),$i,["table"=>$a],h($A!=""?$A:$a),$eh,doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",]));echo'
<form method="post">
<p><label>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off"></label>
<p>';textarea("clause",$J["clause"]);echo'<p><input type=\'submit\' value=\'Save\'>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$A)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A="$_GET[name]";$Il=trigger_options();$J=trigger($A,$a);$eh=($A!=""&&!$J);$Qg=str_replace("{table}",$a,adminer()->namePattern("TRIGGER"));$J+=["Trigger"=>strtr($Qg,["{timing}"=>"b","{event}"=>"i","{columns}"=>"","{type}"=>"row"])];if($_POST){if(!$i&&in_array($_POST["Timing"],$Il["Timing"])&&in_array($_POST["Event"],$Il["Event"])&&in_array($_POST["Type"],$Il["Type"])){$vh=" ON ".table($a);$Gc="DROP TRIGGER ".idf_escape($A).(JUSH=="pgsql"?$vh:"");$z=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Gc,$z,'Trigger has been dropped.');else{if($A!="")queries($Gc);queries_redirect($z,($A!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($vh,$_POST)));if($A!="")queries(create_trigger($vh,$J+["Type"=>reset($Il["Type"])]));}}$J=$_POST;}page_header(($A!=""?'Alter trigger':'Create trigger'),$i,["table"=>$a],h($A!=""?$A:$a),$eh,doc_link(['sql'=>"create-trigger.html",]));$Tg=strtr(preg_quote($Qg),['\{timing\}'=>'[abi]','\{event\}'=>'[iud]*','\{columns\}'=>'.*','\{type\}'=>'(row|statement)']);$Hl=on('change','triggerChange',"^$Tg$",$Qg);$kh=on('input','triggerChange',"^$Tg$",$Qg);echo'
<form method="post" id="form">
<table class="layout">
<tr><th>Time
<td>',html_select("Timing",$Il["Timing"],$J["Timing"],$Hl),'<tr><th>Event<td>',html_select("Event",$Il["Event"],$J["Event"],$Hl),(in_array("UPDATE OF",$Il["Event"])?" <input name='Of' value='".h($J["Of"])."' class='hidden'$kh>":""),'<tr><th>Type<td>',html_select("Type",$Il["Type"],$J["Type"],$Hl),'<tr><th>Name<td><input name="Trigger" value="',h($J["Trigger"]),'" data-maxlength="64" autocapitalize="off">
</table>
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$J["Statement"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($A!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$A)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){function
grant($ee,array$Ni,$d,$vh){if(!$Ni)return
true;if($Ni==["ALL PRIVILEGES","GRANT OPTION"])return($ee=="GRANT"?queries("$ee ALL PRIVILEGES$vh WITH GRANT OPTION"):queries("$ee ALL PRIVILEGES$vh")&&queries("$ee GRANT OPTION$vh"));return
queries("$ee ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$d, ",$Ni).$d).$vh);}function
queries_password($Sd,$lm,$ki){remember_query(sprintf($Sd,$lm,"<password>"));return
connection()->query(sprintf($Sd,$lm,q($ki)));}$da=$_GET["user"];$Ni=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$J){foreach(explode(",",($J["Privilege"]=="Grant option"?"":$J["Context"]))as$Gb)$Ni[$Gb=="File access on server"?"Server Admin":$Gb][$J["Privilege"]]=$J["Comment"];}unset($Ni["Server Admin"]["Usage"]);foreach($Ni["Tables"]as$v=>$X)unset($Ni["Databases"][$v]);$Wg=[];if($_POST){foreach($_POST["objects"]as$v=>$X)$Wg[$X]=(array)$Wg[$X]+idx($_POST["grants"],$v,[]);}$fe=[];$H=(isset($_GET["host"])?connection()->query("SHOW GRANTS FOR ".q($da)."@".q($_GET["host"])):null);$eh=(isset($_GET["host"])&&!$H);if($H){while($J=$H->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$J[0],$_)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$_[1],$cg,PREG_SET_ORDER)){foreach($cg
as$X){if($X[1]!="USAGE")$fe["$_[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$J[0]))$fe["$_[2]$X[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$i){$uh=(isset($_GET["host"])?q($da)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $uh",ME."privileges=",'User has been dropped.');else{$ah=q($_POST["user"])."@".q($_POST["host"]);$ki=$_POST["pass"];$Ob=false;$H=true;if($uh!=$ah){$Ob=queries_password("CREATE USER %s IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD %s":"%s"),$ah,$ki);$H=$Ob;}elseif($ki!="")$H=queries_password("SET PASSWORD FOR %s = ".(min_version(8,99)||$_POST["hashed"]?"%s":"PASSWORD(%s)"),$ah,$ki);if($H){$tj=[];foreach($Wg
as$ih=>$ee){if(isset($_GET["grant"]))$ee=array_filter($ee);$ee=array_keys($ee);if(isset($_GET["grant"]))$tj=array_diff(array_keys(array_filter($Wg[$ih],'strlen')),$ee);elseif($uh==$ah){$qh=array_keys((array)$fe[$ih]);$tj=array_diff($qh,$ee);$ee=array_diff($ee,$qh);unset($fe[$ih]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$ih,$_)&&(!grant("REVOKE",$tj,$_[2]," ON $_[1] FROM $ah")||!grant("GRANT",$ee,$_[2]," ON $_[1] TO $ah"))){$H=false;break;}}}if($H&&isset($_GET["host"])){if($uh!=$ah)queries("DROP USER $uh");elseif(!isset($_GET["grant"])){foreach($fe
as$ih=>$tj){if(preg_match('~^(.+)(\(.*\))?$~U',$ih,$_))grant("REVOKE",array_keys($tj),$_[2]," ON $_[1] FROM $ah");}}}if($H&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),$H);if($Ob)connection()->query("DROP USER $ah");}}page_header((isset($_GET["host"])?'Username'.": ".h("$da@$_GET[host]"):'Create user'),$i,["privileges"=>['','Privileges']],"",$eh,doc_link(['sql'=>"grant.html",'mariadb'=>"grant"]));$J=$_POST;if($J)$fe=$Wg;else{$J=$_GET+["host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];$fe[(DB==""||$fe?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=[];}echo'<form method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($J["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($J["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($J["pass"]),'" autocomplete="new-password">
',($J["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$J["hashed"],'Hashed',on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges';$q=0;foreach($fe
as$ih=>$ee){echo'<th>'.($ih!="*.*"?"<input name='objects[$q]' value='".h($ih)."' size='10' autocapitalize='off'>":input_hidden("objects[$q]","*.*")."*.*");$q++;}echo"<tbody>\n";foreach([""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Procedures"=>'Routine',]as$Gb=>$nc){foreach((array)$Ni[$Gb]as$Mi=>$wb){echo"<tr><td".($nc?">$nc<td":" colspan='2'").' lang="en" title="'.h($wb).'">'.h($Mi);$q=0;foreach($fe
as$ih=>$ee){$A="'grants[$q][".h(strtoupper($Mi))."]'";$Y=$ee[strtoupper($Mi)];if($Gb=="Server Admin"&&$ih!=(isset($fe["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($Y?" checked":"").($Mi=="All privileges"?" id='grants-$q-all'":($Mi=="Grant option"?"":on('click','grantsClick',"grants-$q-all"))).">","</label>";$q++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'Save\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',"$da@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$i){$Af=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$Af++;}queries_redirect(ME."processlist=",lang_format(['%d process has been killed.','%d processes have been killed.'],$Af),$Af||!$_POST["kill"]);}}page_header('Process list',$i);echo'
<form method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$q=-1;foreach(adminer()->processList()as$q=>$J){if(!$q){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($J
as$v=>$X)echo"<th>$v".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($v),]);echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$J[JUSH=="sql"?"Id":"pid"],0):"");foreach($J
as$v=>$X)echo"<td>".($X!=""&&((JUSH=="sql"&&$v=="Info"&&preg_match("~Query|Killed~",$J["Command"]))||(JUSH=="pgsql"&&$v=="query")||(JUSH=="oracle"&&$v=="sql_text"))?"<code class='sql jush-".JUSH."' data-full='".h($X)."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(($J["db"]!=""?preg_replace('~&db=[^&]*~','',ME)."db=".url_escape($J["db"])."&":ME)."sql=".url_escape($X)).'">'.'Clone'.'</a>'.' '.copy_icon():h($X));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo
format_number($q+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif($_GET["select"]!=""){$a=$_GET["select"];$S=table_status1($a);$u=indexes($a);$k=fields($a);$Rd=column_foreign_keys($a);$ph=$S["Oid"];$vj=[];$d=[];$Lj=[];$Gh=[];$pl=null;foreach($k
as$v=>$j){$A=adminer()->fieldName($j);$Sg=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($j["privileges"]["select"])&&$A!=""){$d[$v]=$Sg;if(is_shortable($j))$pl=adminer()->selectLengthProcess();}if(isset($j["privileges"]["where"])&&$A!="")$Lj[$v]=$Sg;if(isset($j["privileges"]["order"])&&$A!="")$Gh[$v]=$Sg;$vj+=$j["privileges"];}list($M,$p)=adminer()->selectColumnsProcess($d,$u);$M=array_unique($M);$p=array_unique($p);$of=count($p)<count($M);$Z=adminer()->selectSearchProcess($k,$u,$S);$C=adminer()->selectOrderProcess($k,$u);$x=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Ul=>$J){$_a=convert_field($k[key($J)]);$M=[$_a?:idf_escape(key($J))];$Z[]=where_check(bracket_escape($Ul,true),$k);$I=driver()->select($a,$M,$Z,$M);if($I)echo
first($I->fetch_row());}exit;}$Ji=$Wl=[];foreach($u
as$t){if($t["type"]=="PRIMARY"){$Ji=array_flip($t["columns"]);$Wl=($M?$Ji:[]);foreach($Wl
as$v=>$X){if(in_array(idf_escape($v),$M))unset($Wl[$v]);}break;}}if($ph&&!$Ji){$Ji=$Wl=[$ph=>0];$u[]=["type"=>"PRIMARY","columns"=>[$ph]];}if($_POST&&!$i){$Cm=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ib=[];foreach($_POST["check"]as$fb)$ib[]=where_check($fb,$k);$Cm[]="((".implode(") OR (",$ib)."))";}$Em=$Cm;$Cm=($Cm?"\nWHERE ".implode(" AND ",$Cm):"");if($_POST["export"]){save_settings(["output"=>$_POST["output"],"format"=>$_POST["format"]],"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$Qj=($M?:["*"]);$Ib=convert_fields($d,$k,$M);if($Ib)$Qj[]=substr($Ib,2);$G="";if(is_array($_POST["check"])&&!$Ji){$Wd=implode(", ",$Qj)."\nFROM ".table($a);$ie=($p&&$of?"\nGROUP BY ".implode(", ",$p):"").($C?"\nORDER BY ".implode(", ",$C):"");$Rl=[];foreach($_POST["check"]as$X)$Rl[]="(SELECT".limit($Wd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$k).$ie,1).")";$G=implode(" UNION ALL ",$Rl);}adminer()->dumpData($a,"table",$G,$Qj,$Em,($of?$p:[]),$C);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$Rd)){if($_POST["save"]||$_POST["delete"]){$H=true;$ma=0;$Oa=false;$O=[];if(!$_POST["delete"]){foreach($k
as$A=>$X){$s=bracket_escape($A);if(isset($_POST["fields"][$s])||$_FILES["fields-$s"]){$X=process_input($k[$A]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($A)]=($X!==false?$X:idf_escape($A));}}}if($_POST["delete"]||$O){$G=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($Ji&&is_array($_POST["check"]))||$of){$H=($_POST["delete"]?driver()->delete($a,$Cm):($_POST["clone"]?queries("INSERT $G$Cm".driver()->insertReturning($a)):driver()->update($a,$O,$Cm)));$ma=connection()->affected_rows;if(is_object($H))$ma+=$H->num_rows;}else{$Oa=count((array)$_POST["check"])>1&&driver()->begin();foreach((array)$_POST["check"]as$X){$Bm="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$k);$H=($_POST["delete"]?driver()->delete($a,$Bm,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$Bm)):driver()->update($a,$O,$Bm,1)));if(!$H)break;$ma+=connection()->affected_rows;}if($Oa&&$H&&!driver()->commit())$H=false;}}$ug=lang_format(['%d item has been affected.','%d items have been affected.'],$ma);if($_POST["clone"]&&$H&&$ma==1){$If=last_id($H);if($If)$ug=sprintf('Item%s has been inserted.'," $If");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$ug,$H);if($Oa)driver()->rollback();if(!$_POST["delete"]){$Ai=(array)$_POST["fields"];edit_form($a,array_intersect_key($k,$Ai),$Ai,!$_POST["clone"],$i);page_footer();exit;}}elseif(!$_POST["import"]){$H=true;$ma=0;$Oa=count((array)$_POST["val"])>1&&driver()->begin();foreach((array)$_POST["val"]as$Ul=>$J){$O=[];foreach($J
as$v=>$X){$v=bracket_escape($v,true);$O[idf_escape($v)]=(preg_match('~char|text~',$k[$v]["type"])||$X!=""?adminer()->processInput($k[$v],$X):"NULL");}$H=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($Ul,true),$k),($of||$Ji?0:1)," ");if(!$H)break;$ma+=connection()->affected_rows;}if($Oa)$H=$H&&driver()->commit();queries_redirect(remove_from_uri(),lang_format(['%d item has been affected.','%d items have been affected.'],$ma),$H);if($Oa)driver()->rollback();}else{save_settings(["format"=>$_POST["separator"]],"adminer_import");$Ed=get_file("csv_file",true);if(!is_string($Ed))$i=upload_error($Ed);elseif(!preg_match('~~u',$Ed))$i='File must be in UTF-8 encoding.';else{$tb=array_keys($k);$Vj=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$Sb=parse_csv($Ed,$Vj);$ma=count($Sb);driver()->begin();$K=[];foreach($Sb
as$v=>$sm){if(!$v&&!array_diff($sm,$tb)){$tb=$sm;$ma--;}else{$O=[];foreach($sm
as$q=>$pb)$O[idf_escape($tb[$q])]=($pb==""&&$k[$tb[$q]]["null"]?"NULL":q(csv_value($pb)));$K[]=$O;}}$H=(!$K||driver()->insertUpdate($a,$K,$Ji));if($H)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang_format(['%d row has been imported.','%d rows have been imported.'],$ma),$H);driver()->rollback();}}}}$Xk=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $Xk",$i,[],"",(!$k&&support("table")),($k?doc_link([JUSH=>driver()->tableHelp($a,is_view($S))]):""));$O=null;if(isset($vj["insert"])||!support("table")){$O="";foreach((array)$_GET["where"]as$X){$Y=$X["val"];if(is_array($Y))$Y=(count($Y)==1&&preg_match('~^val-(.*)~s',reset($Y),$_)?$_[1]:"");if($X["col"]!=""&&$Y!=""&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$Y)))))$O
.="&set[".url_escape(bracket_escape($X["col"]))."]=".url_escape($Y);}}adminer()->selectLinks($S,$O);if(!$d&&support("table"))echo"<p class='error'>".'Unable to select the table.'."\n";else{echo"<form id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$d);adminer()->selectSearchPrint($Z,$Lj,$u,$S);adminer()->selectOrderPrint($C,$Gh,$u);adminer()->selectLimitPrint($x);if($pl!==null)adminer()->selectLengthPrint($pl);adminer()->selectActionPrint($u);$uk=[];foreach((array)$_GET["where"]as$X){if($X["op"]=="SQL")$uk[]=$X["val"];}$zk=($uk&&$_SERVER["HTTP_SEC_FETCH_SITE"]!="same-origin"?hash_hmac('sha256',json_encode([$a,$uk]),$_SESSION["token"]):"");echo($zk?input_hidden("sql_sig",$zk):""),"</form>\n";if($zk&&$_GET["sql_sig"]!==$zk){echo"<p class='error'>".'Invalid CSRF token. Submit the form again.'.' '.'If you did not send this request from Adminer, close this page.'."\n";page_footer();exit;}$D=$_GET["page"];$Ud=null;if($D=="last"){$Ud=get_val(count_rows($a,$Z,$of,$p));$D=floor(max(0,intval($Ud)-1)/$x);}$Pj=$M;$he=$p;if(!$Pj){$Pj[]="*";$Ib=convert_fields($d,$k,$M);if($Ib)$Pj[]=substr($Ib,2);}foreach($M
as$v=>$X){$j=$k[idf_unescape($X)];if($j&&($_a=convert_field($j)))$Pj[$v]="$_a AS $X";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$v=>$X){if(isset($Pj[$v])&&$X["fun"])$Pj[$v].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$of&&$Wl){foreach($Wl
as$v=>$X){$Pj[]=idf_escape($v);if($he)$he[]=idf_escape($v);}}$H=driver()->select($a,$Pj,$Z,$he,$C,$x,$D,true);if(!is_object($H))echo"<p class='error'>".(adminer()->error()?:'Unknown error.')."\n";else{if(JUSH=="mssql"&&$D)$H->seek($x*$D);$Tc=[];$K=[];while($J=$H->fetch_assoc()){if($D&&JUSH=="oracle")unset($J["RNUM"]);$K[]=$J;}$se=($x&&(support("cursor")?$_GET["next"]!="":count($K)>=$x));if(is_ajax()&&$se)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$K){$lg=max_input_vars(count($K[0])+1,20);echo($lg&&count($K)>$lg?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form method='post' enctype='multipart/form-data'".on_upload_progress($cm).">\n";if($_GET["page"]!="last"&&$x&&$p&&$of&&JUSH=="sql")$Ud=get_val(" SELECT FOUND_ROWS()");if(!$K)echo"<p class='message'>".'No rows.'."\n";else{$Ka=adminer()->backwardKeys($a,$Xk);$rj=[];reset($M);foreach($K[0]as$v=>$X){if(!isset($Wl[$v])){$X=idx($_GET["columns"],key($M))?:[];$rj[$v]=["fun"=>$X["fun"],"col"=>($M?$X["col"]:$v)];next($M);}}echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$p&&$M?"":"<td class='hover check sticky'><input type='checkbox' id='all-page' class='jsonly' title='".'All rows on this page'."'".on('click','formCheck','^check').">");$Ug=[];$aj=1;foreach($rj
as$v=>$X){$j=$k[$X["col"]];$A=($j?adminer()->fieldName($j,$aj):($X["fun"]?($X["col"]!=""?h($X["col"]):"*"):h($v)));if($A!=""){$aj++;$Ug[$v]=$A;$c=idf_escape($v);$Ge=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($v);$nc="&desc[0]=1";$nk=preg_replace('~ DESC( NULLS LAST)?$~','',$C[0]);$pk=($nk==$c||$nk==$v);echo"<th id='th[".h(bracket_escape($v))."]'".($pk?" aria-sort='".($nk==$C[0]?"ascending":"descending")."'":"").">";$ae=apply_sql_function(h($X["fun"]),$A);$ok=isset($j["privileges"]["order"])||$X["fun"];echo($ok?"<a href='".h($Ge.($pk&&$nk==$C[0]?$nc:''))."'>$ae</a>":$ae),doc_link([JUSH=>idx($j,"doc")]);$tg=($ok?"<a href='".h($Ge.$nc)."' title='".'descending'."' class='text'> ↓</a>":'');if(!$X["fun"]&&isset($j["privileges"]["where"]))$tg
.="<a href='#fieldset-search' title='".'Search'."' class='text jsonly'".on('click','selectSearch',$v)."> =</a>";echo($tg?"<span class='column'>$tg</span>":"");}}$Pf=[];if($_GET["modify"]){foreach($K
as$J){foreach($J
as$v=>$X)$Pf[$v]=max($Pf[$v],min(40,utf8_length((string)$X)));}}$Be=[];$Ae=[];foreach((array)$_GET["where"]as$X){$X+=["col"=>"","op"=>"","val"=>""];$pb=$X["col"];$Kj=$X["val"];if(!is_array($Kj)&&($Kj!=""||preg_match('~NULL$~',$X["op"]))&&(!$X["op"]||in_array($X["op"],adminer()->operators($S)))){$Rf=strtr(preg_quote($Kj),["%"=>".*?","_"=>"."]);$qi=["LIKE %%"=>$Rf,"ILIKE %%"=>$Rf,"REGEXP"=>$Kj]+(JUSH=="pgsql"?["~"=>$Kj,"~*"=>$Kj]:[])+($pb!=""?[]:["="=>'^'.preg_quote($Kj).'\z',"IN"=>'^(?:'.implode("|",array_map('preg_quote',array_map('trim',explode(",",$Kj)))).')\z',"LIKE"=>"^$Rf\\z","ILIKE"=>"^$Rf\\z","FIND_IN_SET"=>'(?<=^|,)'.preg_quote($Kj).'(?=,|\z)',]);foreach(($pb!=""?[$pb=>$k[$pb]]:$k)as$A=>$j){if($pb!=""||is_searchable($j,$X)){$xh=$X["op"]?:(!preg_match('~'.text_type().'~',$j["type"])?"IN":(preg_match('~%~',$Kj)?"LIKE":"LIKE %%"));if(isset($qi[$xh])){$lb=preg_match('~^ILIKE|\*$~',$xh)||($xh!="~"&&preg_match('~^(sql|mssql|sqlite)$~',JUSH));$Be[$A][]="(?".($lb?"i":"").":$qi[$xh])";}elseif($xh=="IS NULL"&&$pb=="")$Ae[$A]=true;}}}}echo($Ka?"<th>".'Relations':"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($K,$Rd)as$Og=>$J){$Tl=unique_array($K[$Og],$u);if(!$Tl){$Tl=[];foreach($K[$Og]as$v=>$X){if(!in_array(idx(idx($rj,$v,[]),"fun"),driver()->grouping))$Tl[$v]=$X;}}$Ul="";$q=0;foreach($Tl
as$v=>$X){$qj=idx($rj,$v,[]);if($M&&$qj&&!isset($k[$qj["col"]]))continue;$ae=idx($qj,"fun","");$pb=($ae?$qj["col"]:$v);$j=(array)$k[$pb];$nf=is_blob($j);if(!$ae&&strlen($X)>64&&driver()->md5(idf_escape($pb),$j)){$ae="md5";$X=md5($nf?(string)driver()->value($X,$j):$X);}if($ae){$Ul
.="&fun[$q]=".url_escape($ae)."&col[$q]=".url_escape($pb).($X!==null?"&val[$q]=".url_escape($X===false?"f":$X):"");$q++;}else$Ul
.="&".($X!==null?"where[".url_escape(bracket_escape($pb))."]=".url_escape($X===false?"f":$X):"null[]=".url_escape($pb));}echo"<tr>".(!$p&&$M?"":"<td class='hover check sticky'>".($of||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$Ul)."' class='edit'>".'edit'."</a> ").checkbox("check[]",substr($Ul,1),in_array(substr($Ul,1),(array)$_POST["check"])));foreach($J
as$v=>$X){if(isset($Ug[$v])){$ae=$rj[$v]["fun"];$pb=$rj[$v]["col"];$j=(array)$k[$v];if($X!=""&&(!isset($Tc[$v])||$Tc[$v]!=""))$Tc[$v]=(is_mail($X)?$Ug[$v]:"");$y="";if(is_blob($j)&&$X!="")$y=ME.'download='.url_escape($a).'&field='.url_escape($v).$Ul;if(!$y&&$X!==null){foreach((array)$Rd[$v]as$m){if(count($Rd[$v])==1||end($m["source"])==$v){$y="";foreach($m["source"]as$q=>$qk)$y
.=where_link($q,$m["target"][$q],$K[$Og][$qk]);$y=($m["db"]!=""?preg_replace('~&db=[^&]*~',"&db=".url_escape($m["db"]),ME):ME).'select='.url_escape($m["table"]).$y;if($m["ns"]!="")$y=preg_replace('~&ns=[^&]*~',"&ns=".url_escape($m["ns"]),$y);if(count($m["source"])==1)break;}}}if($ae=="count"&&$pb==""){$y=ME."select=".url_escape($a);$q=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Tl))$y
.=where_link($q++,$W["col"],$W["val"],$W["op"]);}foreach($Tl
as$yf=>$W){if(idx(idx($rj,$yf,[]),"fun")){$y="";break;}$y
.=where_link($q++,$yf,$W);}}$He=select_value($X,$y,$j,$pl,($ae?[]:idx($Be,$v,[])));if($X===null&&!$ae&&isset($Ae[$v]))$He="<mark>$He</mark>";$s=bracket_escape($Ul);$r=h("val[$s][".bracket_escape($v)."]");$Ci=idx(idx($_POST["val"],$s),bracket_escape($v));$Zl=idx($j["privileges"],"update")&&!is_identity_always($j);$Oc=$K[$Og][$v];$Pc=!is_array($J[$v])&&!is_blob($j)&&is_utf8($Oc)&&!$ae&&!$j["generated"]&&$Zl;$U=($ae=="min"||$ae=="max"?$k[$pb]["type"]:$j["type"]);$ol=preg_match('~text|json|lob~',$U);$pf=preg_match(number_type(),$U)||preg_match('~^(avg|ceil|char_length|count|count distinct|floor|len|length|round|sum|time_to_sec)$~',$ae);echo"<td id='$r'".($pf&&($X===null||is_numeric(strip_tags($He))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$Pc&&$Oc!==null)||$Ci!==null){$ne=h($Ci!==null?$Ci:$Oc);echo">".($ol?"<textarea name='$r' cols='30' rows='".(substr_count($Oc,"\n")+1)."'>\n$ne</textarea>":"<input name='$r' value='$ne' size='$Pf[$v]'>");}else{$Zf=strpos($He,"<i>…</i>");$Nc=adminer()->editVal($Oc,$j);echo($Zl?" data-text='".($Zf?2:($ol?1:0))."'".($Pc?"":" data-warning='".'Use the edit link to modify this value.'."'").($Pc&&!$Zf&&html_entity_decode(strip_tags($He),ENT_QUOTES,"UTF-8")!==$Nc?" data-value='".h($Nc)."'":""):"").">$He";}}}if($Ka)echo"<td>";adminer()->backwardKeysPrint($Ka,$K[$Og]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){$la=get_settings("adminer_import");if($K||$D||$se){$ld=true;if($_GET["page"]!="last"){if(!$x||(count($K)<$x&&($K||!$D)))$Ud=($D?$D*$x:0)+count($K);elseif(JUSH!="sql"||!$of){$Ud=($of?null:found_rows($S,$Z));$ld=!driver()->hasEstimatedRows();if($Ud===null||(!$ld&&$Ud<max(1e4,2*($D+1)*$x))){$Ud=first(slow_query(count_rows($a,$Z,$of,$p)));$ld=true;}}}if(!support("cursor"))$se=(($Ud===false?count($K)+1:$Ud-$D*$x)>$x);$Vh=($x&&($se||$D));if($Vh)echo($se?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore','Loading…').'>'.'Load more data'.'</a>':''),"\n";echo"<div class='footer'><div>\n";if($Vh){$jg=($Ud===false?$D+($K?(count($K)>=$x?2:1):0):floor(($Ud-1)/$x));echo"<fieldset><legend>".'Page'."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($q=max(1,$D-4);$q<min($jg,$D+5);$q++)echo
pagination($q,$D);if($jg>0)echo($D+5<$jg?" …":""),($ld&&$Ud!==false?pagination($jg,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$jg'>".'last'."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($se?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$vc=($ld?"":"~ ").$Ud;$Bf=($Ud!==false?($ld?"":"~ ").lang_format(['%d row','%d rows'],$Ud):"");echo
checkbox("all",1,0,$Bf,on('click','countRows',$vc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".'Ctrl+click on a value to modify it.'."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>Modify</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'Save\'',($_GET["modify"]||$_POST["val"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'Edit\'>
<input type=\'submit\' name=\'clone\' value=\'Clone\'>
<input type=\'submit\' name=\'delete\' value=\'Delete\'',confirm(),'>
</div></fieldset>
';$Sd=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$c){if($c["fun"]||($c["col"]!=""&&!isset($k[$c["col"]]))){unset($Sd['sql']);break;}}if($Sd){print_fieldset("export",'Export'." <span id='selected2'></span>");$Sh=adminer()->dumpOutput();echo($Sh?html_select("output",$Sh,$la["output"])." ":""),html_select("format",$Sd,$la["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($Tc,'strlen'),$d);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".'Import'."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",($cm?input_hidden(ini_get("session.upload_progress.name"),$cm):""),file_input(" name='csv_file'"," ".html_select("separator",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$la["format"])." <input type='submit' name='import' value='".'Import'."'>".($cm?" <progress class='jsonly hidden' max='1' value='0'></progress>":"")),"</span>";echo
input_token(),"</form>\n",(!$p&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$tm=($P?adminer()->showStatus():adminer()->showVariables());if(!$tm)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($tm
as$J){echo"<tr>";$v=array_shift($J);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($v)."</code>";foreach($J
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$Nk=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];foreach(table_status()as$A=>$S){json_row("Comment-$A",h($S["Comment"]).($S["Error"]?" <span class='error'>".h($S["Error"])."</span>":""));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(["Engine","Collation"]as$v)json_row("$v-$A",h($S[$v]));foreach(array_keys($Nk+["Auto_increment"=>0,"Rows"=>0])as$v){if(array_key_exists($v,$S))json_row("$v-$A",format_status($S,$v));if($S[$v]!=""&&isset($Nk[$v]))$Nk[$v]+=($S["Engine"]!="InnoDB"||$v!="Data_free"?$S[$v]:0);}}}if(function_exists('Adminer\db_status'))$Nk=db_status();foreach($Nk
as$v=>$X)json_row("sum-$v",adminer()->formatSizeValue($X));json_row("");}else{foreach(count_tables(adminer()->databases(false))as$g=>$X){json_row("tables-$g",format_number($X));json_row("size-$g",db_size($g));}json_row("");}exit;}else{if(!isset($_GET["select"])&&support("single_table")){$T=tables_list();if($T)redirect(ME.(support("table")?"table=":"select=").url_escape(key($T)));}$qg=ME.(isset($_GET["select"])?"select=&":"");$gl=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($gl&&!$i&&!$_POST["search"]){$H=true;$ug="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$H=truncate_tables($_POST["tables"]);$ug='Tables have been truncated.';}elseif($_POST["move"]){$H=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ug='Tables have been moved.';}elseif($_POST["copy"]){$H=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$ug='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$H=drop_views($_POST["views"]);if($H&&$_POST["tables"])$H=drop_tables($_POST["tables"]);$ug='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$J)$ug
.="<b>".h($R)."</b>: ".h($J["integrity_check"])."<br>";}}elseif(JUSH=="mssql"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("DBCC CHECKTABLE (".q(table($R)).") WITH TABLERESULTS")as$J)$ug
.="<b>".h($R)."</b>: ".h($J["MessageText"])."<br>";}}elseif(JUSH!="sql"){$H=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$ug='Tables have been optimized.';}elseif(!$_POST["tables"])$ug='No tables.';elseif($H=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($J=$H->fetch_assoc())$ug
.="<b>".h($J["Table"])."</b>: ".h($J["Msg_text"])."<br>";}queries_redirect(relative_uri(),$ug,$H);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$i,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$C=$_GET["order"];$Xd=($C||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".'Tables and views'."</h3>\n";$fl=($Xd?table_status():tables_list());$Id=$_GET["filter"];if($Id!=""){foreach($fl
as$R=>$P){if(stripos("$R",$Id)===false)unset($fl[$R]);}}if(!$fl)echo"<p class='message'>".'No tables.'."\n";else{echo"<form method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>";$xh=idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%");echo
html_select("op",adminer()->operators(),$xh);$G=($_POST?$_POST["query"]:$_GET["query"]);echo" <input type='search' name='query' value='".h($G!=""?$G:$Id)."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if(!$i&&(!$_POST||$_POST["search"])&&$G!="")search_tables($G,$xh);}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr>','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.'All'.'"'.on('click','formCheck','^(tables|views)\[').'>','<th class="sticky"'.(!$C&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr($qg,0,-1)).'">'.'Table'.'</a>';$d=["Engine"=>['Engine'.doc_link(['sql'=>'storage-engines.html'])]];$sb=collations();if($sb)$d["Collation"]=['Collation'.doc_link(['sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/'])];if(function_exists('Adminer\alter_table'))$d["Data_length"]=['Data Length'.doc_link(['sql'=>'show-table-status.html',]),"create",'Alter table',];if(support("indexes"))$d["Index_length"]=['Index Length'.doc_link(['sql'=>'show-table-status.html',]),"indexes",'Alter indexes',];$d["Data_free"]=['Data Free'.doc_link(['sql'=>'show-table-status.html']),"edit",'New item'];if(function_exists('Adminer\alter_table'))$d["Auto_increment"]=['Auto Increment'.doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/']),"auto_increment=1&create",'Alter table',];$d["Rows"]=['Rows'.doc_link(['sql'=>'show-table-status.html',]),"select",'Select data',];if(support("comment"))$d["Comment"]=['Comment'.doc_link(['sql'=>'show-table-status.html',]),];$Aa=['Engine','Collation','Comment'];foreach($d
as$v=>$c)echo"<th".($C==$v?" aria-sort='".(in_array($v,$Aa)?"ascending":"descending")."'":"")."><a href='".h($qg)."order=$v'>$c[0]</a>";echo"<tbody>\n";if($C){uasort($fl,function($fa,$Ha)use($C,$Aa){$I=($fa[$C]<$Ha[$C]?-1:($fa[$C]>$Ha[$C]?1:0));return(in_array($C,$Aa)?$I:-$I);});}$T=0;$Nk=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];foreach($fl
as$A=>$P){$wm=($Xd?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$P=($Xd?$P:['Engine'=>$P]);$r=h("Table-".$A);echo'<tr><td class="hover">'.checkbox(($wm?"views[]":"tables[]"),$A,in_array("$A",$gl,true),"","","",$r),'<th class="sticky">'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($A)."' title='".'Show structure'."' id='$r'>".h($A).'</a>':h($A));if($wm&&!preg_match('~materialized~i',$P['Engine'])){$ul='View';echo'<td colspan="'.(count($d)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($A)."' title='".'Alter view'."'>$ul</a>":$ul),"<td align='right'><a href='".h(ME)."select=".url_escape($A)."' title='".'Select data'."'>?</a>";if(support("comment"))echo'<td>'.h($P['Comment']);}else{if($Xd){foreach(array_keys($Nk)as$v)$Nk[$v]+=($P["Engine"]!="InnoDB"||$v!="Data_free"?idx($P,$v):0);}foreach($d
as$v=>$c){$r=" id='$v-".h($A)."'";echo($c[1]?"<td align='right'>".($v=="Index_length"&&$P["invalid_index"]?"<span class='error'>INVALID</span> ":"")."<a href='".h(ME."$c[1]=").url_escape($A)."'$r title='$c[2]'>".format_status($P,$v)."</a>":"<td$r>".h(idx($P,$v,'?')).($v=="Comment"&&$P["Error"]?" <span class='error'>".h($P["Error"])."</span>":""));}$T++;}echo"\n";}echo"<tr><td class='hover'><th class='sticky'>".sprintf('%d in total',count($fl)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),($sb?"<td>".h(db_collation(DB,$sb)):'');if($Xd&&function_exists('Adminer\db_status'))$Nk=db_status();foreach($Nk
as$v=>$Mk)echo($d[$v]?"<td align='right' id='sum-$v'>".($Xd?adminer()->formatSizeValue($Mk):""):"");echo"\n","</table>\n",($Xd?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$pm="<input type='submit' value='".'Vacuum'."'".on_help("VACUUM")."> ";$Bh="<input type='submit' name='optimize' value='".'Optimize'."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$Ki=(JUSH=="sqlite"?$pm."<input type='submit' name='check' value='".'Check'."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$pm.$Bh:(JUSH=="mssql"?"<input type='submit' name='check' value='".'Check'."'".on_help("DBCC CHECKTABLE")."> ":(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'".on_help("ANALYZE TABLE")."> ".$Bh."<input type='submit' name='check' value='".'Check'."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".'Repair'."'".on_help("REPAIR TABLE")."> ":"")))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".'Truncate'."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".'Drop'."'".confirm().on_help("DROP TABLE").">":"");echo($Ki?"<div class='footer'><div>\n<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>$Ki\n</div></fieldset>\n":"");$f=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($f)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".'Move to another database'." <span id='selected3'></span></legend><div>";$g=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($f?html_select("target",$f,$g):'<input name="target" value="'.h($g).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$T).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".'Create table'."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".'Routines'."</h3>\n";$Cj=routines();if($Cj){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<th>'.'Type'.'<th>'.'Return type'."<td class='hover'><tbody>\n";foreach($Cj
as$J){$A=($J["SPECIFIC_NAME"]==$J["ROUTINE_NAME"]?"":"&name=".url_escape($J["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($J["SPECIFIC_NAME"]).$A).'"'.' title="'.($J["DTD_IDENTIFIER"]=="trigger"?'Triggers':'Call').'">'.h($J["ROUTINE_NAME"]).'</a>','<td>'.h($J["ROUTINE_TYPE"]),'<td>'.h($J["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($J["SPECIFIC_NAME"]).$A).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".'Events'."</h3>\n";$K=get_rows("SHOW EVENTS");if($K){echo"<table>\n","<thead><tr><th>".'Name'."<th>".'Schedule'."<th>".'Start'."<th>".'End'."<td class='hover'><tbody>\n";foreach($K
as$J)echo"<tr>","<th>".h($J["Name"]),"<td>".($J["Execute at"]?'At given time'."<td>".h($J["Execute at"]):'Every'." ".h($J["Interval value"])." ".h($J["Interval field"])."<td>".h($J["Starts"])),"<td>".h($J["Ends"]),'<td class="hover"><a href="'.h(ME).'event='.url_escape($J["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$id=get_val("SELECT @@event_scheduler");if($id&&$id!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($id)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.'Create event'."</a>\n","</div>\n";}}}}page_footer();