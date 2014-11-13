<?

define("PG_MAIN","0");     
define("PG_ABOUT","1");     
define("PG_PRODUCT","2");     
define("PG_SERTIFICATE","3");     
define("PG_CONTACTS","4");     

define("LIST_RECORD","0");     
define("NEW_RECORD","1");     
define("EDIT_RECORD","2");     
define("INSERT_RECORD","3");     
define("SAVE_RECORD","4");     
define("SEARCH_RECORD","5");     
define("COPY_RECORD","6");     

/************************************
*
* Функция PrintHeader
* Параметры:
*   title - заголовок страницы (выводится в информационной строке браузера)
*
*************************************/
function PrintHeader($title="Администрирование поиска")
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><? echo $title; ?></title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
<meta http-equiv="last-modified" content="<? print gmdate("D, d M Y H:i:s"); ?> GMT +0300">
<meta content="(c) 2010" name=copyright>
<meta content="no-cache, max-age=0, proxy-revalidate" http-equiv="Pragma">
<meta content="" name="description">
<meta content="" name="keyword">
</head>
<body><center>
<?
}



/************************************
*
* Функция PrintMenu($page_id=1)
* Описание: выводит верхнее меню на каждой странице сайта,  
*           с учетом текущео раздела
*************************************/

function PrintMenu($page_id=PG_MAIN)
{
}


/************************************
*
* Функция PrintBottom
* Параметры: нет
*
*************************************/
function PrintBottom()
{
?>
</body></html>
<?
}


function MySQLConnect()
{
 $localhost   = "localhost";
 $db          = "poliinfo_bitrix";
 $RootUserName      = "poliinfo_bitrix";
 $RootUserPassword  = "Y2Gd75q";


 $result = @mysql_connect( $localhost, $RootUserName, $RootUserPassword );
 if ($result>0) @mysql_select_db($db,$result);
 return $result;
}


function MySQLClose($MySQLDB)
{
  return @mysql_close($MySQLDB);
}

function ExecQuery($sQuery,$MySQLDB=0)
{
  $result = false;
  $disconect = 1;
  if (!is_resource($MySQLDB)) $$MySQLDB = MySQLConnect();
  else $disconect = 0;
  if (is_resource($$MySQLDB))
  {
    $result = mysql_query($sQuery, $$MySQLDB);
    @mysql_free_result($result);
    $result = (mysql_errno()==0);
    if ($disconect==1) @MySQLClose($$MySQLDB);
  }
  else echo "<br>Ошибка БД.</a>.";
  return $result;
}

function GetOneRecord($sQuery,$MySQLDB=0)
{
  $record = array();
  $disconect = 1;
  if (!is_resource($MySQLDB)) $$MySQLDB = MySQLConnect();
  else $disconect = 0;
  if (is_resource($$MySQLDB)) 
  {
    $result = mysql_query($sQuery, $$MySQLDB);
    if (mysql_errno()==0 and $result>0)
     $record = mysql_fetch_array($result);
    @mysql_free_result($result);
    if ($disconect==1) @MySQLClose($$MySQLDB);
  }
  else echo "<br>Ошибка БД.</a>.";
  return $record;
}

function GetRecords($sQuery,$MySQLDB=0)
{
  $record = array();
  $disconect = 1;
  if (!is_resource($MySQLDB)) $$MySQLDB = MySQLConnect();
  else $disconect = 0;
  if (is_resource($$MySQLDB)) 
  {
    $result = mysql_query($sQuery, $$MySQLDB);
    if (mysql_errno()==0 and $result>0)
     while ($row = mysql_fetch_array($result)) 
       array_push($record,$row);
    @mysql_free_result($result);
    if ($disconect==1) @MySQLClose($$MySQLDB);
  }
  else echo "<br>Ошибка БД.</a>.";
  return $record;
}

function CheckDigital($digit,$min=0,$max=0)
{
  $digit = trim($digit);
  $result = (integer) $digit;
  if (!($min==0 and $max==0))
  {
    if ($result<$min) $result=$min;
    if ($result>$max) $result=$max;
  }
  
  return $result;
}

function CheckString($s,$maxlen=0)
{
  $s = trim($s);
  // $s = str_replace("<","&lt;",$s);
  $s = (string) $s;
  if (($maxlen>0) and ($maxlen<strlen($s))) $s = substr($s,0,$maxlen);
  return $s;
}

function CheckEnglishString($s)
{
  $Eng = ":;()@_-.qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789";
  for($i=0;$i<strlen($s);$i++)
   if (!strstr($Eng,$s[$i])) return false;
  return true;
}


function CheckCheckBox($s)
{
  $result = false;
  $s = CheckString($s,2);
  if ($s=="on") $result=1; else $result=0;
  return $result;
}

function GetChecked($par)
{
 if ($par==1) $s="checked"; else $s="";
 return $s;
}


function GetRChecked($par,$val)
{
 if ($par==$val) $s="checked"; else $s="";
 return $s;
}

function Redirect($UrlToName)
{
  echo ("<script language=\"JavaScript\">\n  document.location=\"$UrlToName\"\n</script>");
  exit;
}



  function GetFile($filename_source,$filename_target)
  {
    $result = copy($filename_source,$filename_target);
    return $result;
  }

  function GetFileINET($filename_source,$filename_target)
  {
    $result = ($fd = @fopen($filename_source,"r"));
    $result = ($result and ($fw = @fopen($filename_target,"w")));
    if ($result)
    {
      $result = false;
      while (!feof($fd))
      {
       $line = fread($fd, 1024);
       fwrite($fw,$line, 1024);
      }
      fclose($fw);
      fclose($fd);
      $result = true;
    }
    return $result;
  }


function utf8_to_cp1251($utf8) {

    $windows1251 = "";
    $chars = preg_split("//",$utf8);

    for ($i=1; $i<count($chars)-1; $i++) {
        $prefix = ord($chars[$i]);
        $suffix = ord($chars[$i+1]);

        if ($prefix==215) {
            $windows1251 .= chr($suffix+80);
            $i++;
        } elseif ($prefix==214) {
            $windows1251 .= chr($suffix+16);
            $i++;
        } else {
            $windows1251 .= $chars[$i];
        }
    }

    return $windows1251;
}

function Utf8Win($str,$type="w")  {
    static $conv='';

    if (!is_array($conv))  {
        $conv = array();

        for($x=128;$x<=143;$x++)  {
            $conv['u'][]=chr(209).chr($x);
            $conv['w'][]=chr($x+112);

        }

        for($x=144;$x<=191;$x++)  {
            $conv['u'][]=chr(208).chr($x);
            $conv['w'][]=chr($x+48);
        }

        $conv['u'][]=chr(208).chr(129);
        $conv['w'][]=chr(168);
        $conv['u'][]=chr(209).chr(145);
        $conv['w'][]=chr(184);
        $conv['u'][]=chr(208).chr(135);
        $conv['w'][]=chr(175);
        $conv['u'][]=chr(209).chr(151);
        $conv['w'][]=chr(191);
        $conv['u'][]=chr(208).chr(134);
        $conv['w'][]=chr(178);
        $conv['u'][]=chr(209).chr(150);
        $conv['w'][]=chr(179);
        $conv['u'][]=chr(210).chr(144);
        $conv['w'][]=chr(165);
        $conv['u'][]=chr(210).chr(145);
        $conv['w'][]=chr(180);
        $conv['u'][]=chr(208).chr(132);
        $conv['w'][]=chr(170);
        $conv['u'][]=chr(209).chr(148);
        $conv['w'][]=chr(186);
        $conv['u'][]=chr(226).chr(132).chr(150);
        $conv['w'][]=chr(185);
    }

    if ($type == 'w') {
        return str_replace($conv['u'],$conv['w'],$str);
    } elseif ($type == 'u') {
        return str_replace($conv['w'], $conv['u'],$str);
    } else {
        return $str;
    }
}

?>