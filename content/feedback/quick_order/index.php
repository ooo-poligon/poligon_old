<? // include для ПО Bitrix
/**
 *	@author "Фомичев Андрей" <afomich@rambler.ru>, "Машков Владимир" <vladimir@mashkov.com>
 *	@version 2.0.0
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Быстрая заявка");
$APPLICATION->SetPageProperty("keywords", "ООО Полигон");
$APPLICATION->SetPageProperty("description", "ООО Полигон");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
?>


<style type="text/css">
<!-- Вставляем стиль, необходимый для формы
.goMessage {
	color: #FF0000;
	font-family:Tahoma;
	font-size:14px;
}
.goFormsInputAndTextarea {
	border: 1px solid #000000;
}
.goTitles {
	margin-left:0px;
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionTitle {
	font-family:Tahoma;
	font-size:12px;
}
.goAttensionError {
	color:#FF0000;
	font-family:Tahoma;
	font-size:12px;
}
.goButtonSend {border:solid 1px;}
.goButtonClaer {border:solid 1px;}
-->
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" valign="top">
			<a href="/content/feedback/"><b>Форма обратной связи</b></a><br />
			<a href="/content/feedback/catalog_order/"><b>Форма для заказа CD, каталога</b></a><br />
		</td>
	</tr>
  <tr>
    <td align="center" valign="top">
	<h2>Форма быстрой заявки</h2>
<p><span class="goAttensionTitle"><font color="#FF0000">Внимание! </font> Поля помеченные * обязательны для заполнения:</span></p>
	<div style="text-align:left; width:320px;">
	



<?php
	//include требуемых библиотек
	include("func.inc");
	include("useragent.inc");
	
	
	
	// ниже приведенный список используется для удопства.
	$goInput = 'input'; // задаем HTML поле input
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="35"  maxlength="100" '; // здесь меняем TAG input
	$goSixeTextArea  = ' cols="40" rows="5" '; // здесь меняем TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >Обнаружены следующие ошибки:<br>';
	$goAttensionSuffix = 'не заполнено поле ';
	$goAttensionSuffixNotCorrect = 'не корректно заполнено поле ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">Спасибо за отправку Вашего сообщения!</span>';	

//***************************************** Здесь меняем и вносим требуемые условия (начало) *****************************************//		
	$goSend[To]='web-site-mailbox@poligon.info'; // направляем письмо в указанный ящик
	$goSend[Subject] = 'Вопрос/Заявка с сайта poligon.info'; // тема письма (иногда бывает отправляют вопрос, вместо заказа)
	$goIdOfName = 1; // Уакажите название компании. Это обязателное значение и будет фигурировать в формировании письма.
	$goIdOfEmail = 2; // укажите номер индификатора (см. в массиве $goName ниже)  для проверки Email, в случае отсутствия указать значение 0
	$goIdOfPhone = 3; // укажите номер индификатора для проверки Телефона, в случае отсутствия указать значение 0	
	

//массив $goReqParam[], может принимать только значения true или false (Поле обязательное или необязательное для заполнения); 

	$goTitle [1]='Ф.И.О.*:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='E-Mail:*';		$goTypeHTML [2]=$goInput; $goName[2]='email'; 	$goReqParam[2]=true; 
	$goTitle [3]='Тел./факс*:';	$goTypeHTML [3]=$goInput; $goName[3]='phone1';	$goReqParam[3]=true; 
	$goTitle [4]='Компания*:';		$goTypeHTML [4]=$goInput; $goName[4]='company'; 	$goReqParam[4]=true; 
	$goTitle [5]='Должность*:';	$goTypeHTML [5]=$goInput; $goName[5]='doljnost';	$goReqParam[5]=false; 
	$goTitle [6]='Наименование1:';	$goTypeHTML [6]=$goInput; $goName[6]='article1';	$goReqParam[6]=false; 
	$goTitle [7]='Количество:';	$goTypeHTML [7]=$goInput; $goName[7]='col1';	$goReqParam[7]=false; 
	
	if ($_POST["kol"])
	{
		$pp = 2;
		for ($p=8;$p<=$_POST["kol"];$p++){
			$goTitle[$p] = 'Наименование'.$pp.':';
			$goTypeHTML [$p]=$goInput; $goName[$p]='col'.$p;	$goReqParam[$p]=false; 
			$p++;
			$goTitle[$p] = 'Количество:';
			$goTypeHTML [$p]=$goInput; $goName[$p]='col'.$p;	$goReqParam[$p]=false; 
			$pp++;
		}
		$goTitle [$_POST['kol']+1]='Комментарий:';			$goTypeHTML [$_POST['kol']+1]=$goTextArea; $goName[$_POST['kol']+1]='comment';	$goReqParam[$_POST['kol']+1]=false; 
	}
	else{
	/*$goTitle [8]='Наименование 2:';	$goTypeHTML [8]=$goInput; $goName[8]='article2';	$goReqParam[8]=false; 
	$goTitle [9]='Количество:';	$goTypeHTML [9]=$goInput; $goName[9]='col2';	$goReqParam[9]=false; 
	
	$goTitle [10]='Наименование 3:';	$goTypeHTML [10]=$goInput; $goName[10]='article3';	$goReqParam[10]=false; 
	$goTitle [11]='Количество:';	$goTypeHTML [11]=$goInput; $goName[11]='col3';	$goReqParam[11]=false; 
	
	$goTitle [12]='Наименование 4:';	$goTypeHTML [12]=$goInput; $goName[12]='article4';	$goReqParam[12]=false; 
	$goTitle [13]='Количество:';	$goTypeHTML [13]=$goInput; $goName[13]='col4';	$goReqParam[13]=false; 
	
	$goTitle [14]='Наименование 5:';	$goTypeHTML [14]=$goInput; $goName[14]='article5';	$goReqParam[14]=false; 
	$goTitle [15]='Количество:';	$goTypeHTML [15]=$goInput; $goName[15]='col5';	$goReqParam[15]=false; 
	*/
	$goTitle [8]='Комментарий:';			$goTypeHTML [8]=$goTextArea; $goName[8]='comment';	$goReqParam[8]=false; 
	}


//***************************************** Здесь меняем и вносим требуемые условия (конец) *****************************************//

//***************************************** Все, что ниже менять не рекомендуется *****************************************//

	$goKolichestvoElementov = count($goTitle); // не трогать, и неперемещать. Эта строчка должна быть внизу.
	$goDefaultSendFrom = $goSendTo; // Ящик от которого будет приходить сообщение, в случае отсутствия E-Mail отправителя в формах.
	$goCheck[email]=$goName[$goIdOfEmail];
	$goCheck[phone]=$goName[$goIdOfPhone];
	$tempWeHaveGotError = false;
	$goAttensionSuffixNotCorrectEmail = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfEmail].'<br>';
	$goAttensionSuffixNotCorrectPhone = $goAttensionSuffixNotCorrect.''.$goTitle [$goIdOfPhone].'<br>';
	
	$boolenMessageWasSend = false;
	
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
    		switch ($goTypeHTML [$i]) 
			{
				case ($goTextArea):
    				$goSixe[$i]=$goSixeTextArea;
					$goValueStart[$i] =	$goValueTextAreaStart ;
					$goValueEnd[$i] = $goValueTextAreaEnd ;						
    			break;
				case ($goInput):
    				$goSixe[$i]=$goSixeInput;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
    			break;
			}	
	}
	
?>
<form name="feedback" method="post" action="<?PHP_SELF?>">
  <p>
<?  
		$tempAttensionStart = '';
		$tempAttensionStart = '';
		for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
		{
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			
			switch ($goReqParam [$i]) 
			{
				case (true): //Если поле обязателное для заполнения, то делаем проверку
				if (isset ($_POST [($tempMyNameIs)] ) ) // Если страницу закрузили, только что, то проверка не производиться
				{
					if ($_POST[($tempMyNameIs)] =='') // Если данные отправлены, а поля не заполнены, то сообщаем.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // Если данные отправленны, но среди них есть поля для проверки на валидность, то производим проверку
						if (($_POST [($goCheck[email])]) == '') // Проверяем E-MAIL на корректность заполнения
						{ 
						$tempContentFormErro[$goIdOfEmail] = true;
						}else{
							if (validEmail($_POST [($goCheck[email])]) == true)
							{
								$tempCheckedEmail='';
							}else{
								$tempCheckedEmail= $goAttensionSuffixNotCorrectEmail;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
						
						
						if (($_POST [($goCheck[phone])]) == '') // Проверяем Телефон на корректность заполнения
						{ 
						$tempContentFormErro[$goIdOfPhone] = true;
						}else{
							if (isPhoneNumber($_POST [($goCheck[phone])]) == true)
							{
								$tempCheckedPhone='';
							}else{
								$tempCheckedPhone= $goAttensionSuffixNotCorrectPhone;
								$tempAttensionStart = $goAttensionStart;
								$tempAttensionEnd = $goAttensionEnd;
								$tempWeHaveGotError = true;
							}
						}
								

						
					
					
					}
				}
				break;
				case (false): //Если поле не является обязательным, то пропускае проверку.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] )&&$_POST['mail']=='go')
{
	if ($tempAttensionStart != $goAttensionStart) // ошибок нет, отправляем письмо.
	{
	if (!isset($HTTP_X_FORWARDED_FOR))
	{
	$HTTP_X_FORWARDED_FOR = "";
	}
	if	($HTTP_X_FORWARDED_FOR)
	{
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		$proxy = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
	}else {
		$ip = getenv("REMOTE_ADDR");
		$host = gethostbyaddr($REMOTE_ADDR);
		$proxy = "";
	}
		
	$userAgent = $HTTP_USER_AGENT;
	$browser = getBrowser($arrBrowser,$userAgent);
	$system = getSystem($arrSystem,$userAgent);
	$server = $HTTP_HOST;
		
	$goGetName=($_POST [($goName[$goIdOfName])]);
	if ($goIdOfEmail != 0) 
	{
		$goGetEmail = ($_POST [($goCheck[email])]);
	}else{
		$goGetEmail = $goDefaultSendFrom;
	}
	
	
	
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
		$tempMyNameIs=$goName[$i];
		$tempContentForm=$_POST[($tempMyNameIs)];
		$goMailBody = $goMailBody.$goTitle [$i]."	".$tempContentForm."\n" ;
	}
	
	$goMailBody = $goMailBody."\n\nДата: [".getFullDate(time()).", ".getQuestionTime(time())."]\n--------------------\n\n";
	$tempSendMeFrom = 'From: '.$goGetName.'<'.$goGetEmail.'>'."\nReply-To: ".$goGetEmail."\nContent-Type: text/plain; charset=windows-1251\nContent-Transfer-Encoding: 8bit" ;
	$tempSendMe = $goSend[To]."\r\n".$goSend[Subject]."\r\n".$goMailBody."\r\n".$tempSendMeFrom."\r\n \r\n";
			/**
		 *	Send email with message to admin
		 */
	@mail($goSend[To], $goSend[Subject], $goMailBody, $tempSendMeFrom);
	
	//echo $tempSendMe;
	#writeDataInFile ($tempSendMe);
	
	//Сообщаем, что все отправлено
	echo $goMessageWasSend.'<br><br>';	
	$boolenMessageWasSend = true;


	
	}
}
if ($_POST["mail"]=='go'){
//Печатаем сообщение о ошибке				
echo $tempAttensionStart;
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;}?>
<script>
	function run()
	{
		document.getElementById("hid").innerHTML="<input name='kol' type=hidden value=<?=$goKolichestvoElementov+1?>>";
		//document.feedback.submit();
	}
	function run2()
	{
		document.getElementById("hid").innerHTML="<input name='mail' type=hidden value='go'><input name='kol' type=hidden value=<?=$goKolichestvoElementov-1?>>";
		//document.feedback.submit();
	}
</script>
<?
//Печатаем список полей и кнопки
if ($boolenMessageWasSend == false)
{
	//var_dump($_POST);
	echo '<table>';		
		for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
		{
			
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			if($i==6&&intval($_POST["kol"]))
			{
				for ($i==6;$i<=intval($_POST["kol"]-1);$i++)
				{
					$tempMyNameIs=$goName[$i];
					$tempContentForm=$_POST[($tempMyNameIs)];						
					echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
				<td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100"	'.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td>';
					$i++;
					$tempMyNameIs=$goName[$i];
					$tempContentForm=$_POST[($tempMyNameIs)];
					echo '<td><font class="goTitles">'.$goTitle[$i].'</font></td><td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100"	'.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'';
if ($i==intval($_POST["kol"])) echo  '</td><td><input onclick="run()" type="submit"  value="Еще..."><div id="hid"></div>';
echo '</td></tr>';
				}
				$i--;
			}
			elseif ($i==6&&!intval($_POST["kol"]))
			{
					echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
				<td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'" type="text"  size="10"  maxlength="100" '.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td>';
					$i++;
					echo '<td><font class="goTitles">'.$goTitle[$i].'</font></td><td><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	type="text" size="10"  maxlength="100" '.$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td><td><input onclick="run()" type="submit"  value="Еще..."><div id="hid"></div></td></tr>';
			}
			elseif ($i!=6){
			echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td>
			<td colspan=4><'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'</td></tr>
			';
			}
//echo '<tr><Td colspan=4>'.$i.'<td></tr>';
			
			if ($_POST["kol"]) $koll = $_POST["kol"]; else $koll=7;
			//if($i == $koll) echo '<input onclick="run()" type="submit"  value="Еще..."><div id="hid"></div>';
			if($i == 5 OR $i == 7 OR $i == 9 OR $i == 11 OR $i == 13 OR $i == 15) {echo '';};
		}
		echo '<tr><td colspan=4><p>
				<!-- Печатаем кнопки-->
				<input class="goButtonSend" type="submit" value="Отправить заявку" name="submit" onclick=run2()>
				<input class="goButtonClaer" type="reset" value="Очистить">
			</p></td></tr></table>';
}
		
if ($boolenMessageWasSend == true)
{
			for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
			{
		
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			echo '<font class="goTitles">'.$goTitle[$i].' &#8212; '.$tempContentForm.'</font><br>';
			if($i == 5 OR $i == 7 OR $i == 9 OR $i == 11 OR $i == 13 OR $i == 15) {echo '<br />';};
			}
}
?>




</form>
</div>
	</td>
  </tr>
</table>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
