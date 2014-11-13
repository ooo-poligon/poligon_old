<? // include для ПО Bitrix
/**
 *	@author "Фомичев Андрей" <afomich@rambler.ru>, "Машков Владимир" <vladimir@mashkov.com>
 *	@version 2.0.0
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Форма для заказа CD, каталога");
$APPLICATION->SetPageProperty("keywords", "ООО Полигон");
$APPLICATION->SetPageProperty("description", "ООО Полигон");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
?>
<style type="text/css">
<!-- Вставляем стиль, необходимый для формы-->
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
.goFormsCheckBox	{margin:0 10px 0 0;}
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0">	
<tr>
		<td align="left" valign="top">
			<a href="/content/feedback/quick_order/"><b>Форма "Быстрая заявка"</b></a><br />
			<a href="/content/feedback/"><b>Форма обратной связи</b></a><br />
		</td>
	</tr>
  <tr>
    <td align="center" valign="top">
	<h2>Форма заказа CD, каталога</h2>
	<div style="text-align:left; width:320px;">
	



<?php
	//include требуемых библиотек
	include("func.inc");
	include("useragent.inc");
	
	
	
	// ниже приведенный список используется для удопства.
	$goInput = 'input'; // задаем HTML поле input
	$goCheckBox = 'checkbox';
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="50"  maxlength="100" '; // здесь меняем TAG input
	$goSixeCheckBox =' type="checkbox"';
	$goSixeTextArea  = ' cols="38" rows="3" '; // здесь меняем TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >Обнаружены следующие ошибки:<br>';
	$goAttensionSuffix = 'не заполнено поле ';
	$goAttensionSuffixNotCorrect = 'не корректно заполнено поле ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">Спасибо за отправку Вашего сообщения!</span>';	
	$goMessageForCheckbox ='<b>Выберите интересующие Вас пункты:</b><br />';

//***************************************** Здесь меняем и вносим требуемые условия (начало) *****************************************//		
	$goSend[To]='test@sj@macte.ru'; // направляем письмо в указанный ящик
	$goSend[Subject] = 'Вопрос/Заявка с сайта poligon.info'; // тема письма (иногда бывает отправляют вопрос, вместо заказа)
	$goIdOfName = 1; // Уакажите название компании/организации. Это обязателное значение и будет фигурировать в заголовке письма.
	$goIdOfEmail = 6; // укажите номер индификатора для проверки Email, в случае отсутствия указать значение 0
	$goIdOfPhone = 5; // укажите номер индификатора для проверки Телефона, в случае отсутствия указать значение 0	
	

//массив $goReqParam[], может принимать только значения true или false (Поле обязательное или необязательное для заполнения); 

	$goTitle [1]='Название организации:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='Вид деятельности:';		$goTypeHTML [2]=$goInput; $goName[2]='kind'; 	$goReqParam[2]=true; 
	$goTitle [3]='Контактное лицо:';	$goTypeHTML [3]=$goInput; $goName[3]='face';	$goReqParam[3]=true; 
	$goTitle [4]='Должность:';		$goTypeHTML [4]=$goInput; $goName[4]='doljnost'; 	$goReqParam[4]=true; 
	$goTitle [5]='Тел./факс:';	$goTypeHTML [5]=$goInput; $goName[5]='phone1';	$goReqParam[5]=true; 
	$goTitle [6]='E-mail адрес:';			$goTypeHTML [6]=$goInput; $goName[6]='email';	$goReqParam[6]=true; 
	$goTitle [7]='Почтовый адрес:';			$goTypeHTML [7]=$goTextArea; $goName[7]='mail1';	$goReqParam[7]=true; 
	$goTitle [8]='Комментарий:';			$goTypeHTML [8]=$goTextArea; $goName[8]='comment';	$goReqParam[8]=false; 
	//Здесь наполняем необходимые чекбоксы
	$goTitle [9]='Каталог 1';			$goTypeHTML [9]=$goCheckBox; $goName[9]='checkbox1';	$goReqParam[9]=false;
	$goTitle [10]='Каталог номер 2';			$goTypeHTML [10]=$goCheckBox; $goName[10]='checkbox2';	$goReqParam[10]=false;


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
	//Обнуляем счётчик чекбоксов
	$countOfCheckbox = 0;
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
				case ($goCheckBox):
					++$countOfCheckbox;
					$goTypeHTML [$i] = $goInput;
    				$goSixe[$i]=$goSixeCheckBox;
					$goValueStart[$i] =	$goValueInputStart ;
					$goValueEnd[$i] = $goValueInputEnd ;	
    			break;
			}	
	}
	
?>
<form method="post" action="<?PHP_SELF?>">
  <p>
<?  
		$tempAttensionStart = '';
		$tempAttensionStart = '';
		//УДАЛЯЕМ кол-во чекбоксов, так как если они не выделены, даже пустая переменная не создаётся.
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			
			switch ($goReqParam [$i]) 
			{
				case (true): //Если поле обязателное для заполнения, то делаем проверку
				if (isset ($_POST [($tempMyNameIs)] ) ) // Если страницу закрузили только что, то проверка не производиться
				{
					if ($_POST[($tempMyNameIs)] =='') // Если данные отправлены, а поля не заполнены, то сообщаем.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // Если данные отправлены, но среди них есть поля для проверки на валидность, то производим проверку
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
				case (false): //Если поле не является обязательным, то пропускаем проверку.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] ) )
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
	
	
	
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox+1); $i++) 
	{
		$tempMyNameIs=$goName[$i];
		$tempContentForm=$_POST[($tempMyNameIs)];
		$goMailBody = $goMailBody.$goTitle [$i]."	".$tempContentForm."\n" ;
	};
	for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++) 
	{
		if ($goSixe[$i] == $goSixeCheckBox AND isset($_POST[($goName[$i])]))
			{
				$tempContentForm=$_POST[($goName[$i])];
				$goMailBody = $goMailBody.$goTitle [$i]." <b>выбран</b>\n" ;
			};
	};
	
	
	$goMailBody = $goMailBody."\n\nДата: [".getFullDate(time()).", ".getQuestionTime(time())."]\n--------------------\n\n";
	$tempSendMeFrom = 'From: '.$goGetName.'<'.$goGetEmail.'>'."nReply-To: ".$goGetEmail."\nContent-Type: text/plain; charset=windows-1251\nContent-Transfer-Encoding: 8bit" ;
	$tempSendMe = $goSend[To]."\r\n".$goSend[Subject]."\r\n".$goMailBody."\r\n".$tempSendMeFrom."\r\n \r\n";
			/**
		 *	Send email with message to admin
		 */
	@mail($goSend[To], $goSend[Subject], $goMailBody, $tempSendMeFrom);
	
	#writeDataInFile ($tempSendMe);
	
	//Сообщаем, что все отправлено
	echo $goMessageWasSend.'<br><br>';	
	$boolenMessageWasSend = true;


	
	}
}
//Печатаем сообщение об ошибке				
echo $tempAttensionStart;
	for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;

//Печатаем список полей и кнопки
$flag = 1; //флаг для вывода сообщения  "Выберите интересующие Вас пункты:" всего 1 раз в цикле
if ($boolenMessageWasSend == false)
{
	echo '	<p><span class="goAttensionTitle"><font color="#FF0000">Внимание! </font>Все поля являются обязательными для заполнения, кроме "Комментарий"</span></p>';
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox); $i++) 
		{
			//Выводим все поля кроме чек-боксов
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'
				';
			}
		};
		for ($i = $goKolichestvoElementov-$countOfCheckbox; $i <= $goKolichestvoElementov; $i++)
		{
			//выводим чекбоксы
			if ($goSixe[$i] == $goSixeCheckBox)	{
				if ($flag == 1) 
					echo  $goMessageForCheckbox; 
				$flag = 0;
				$tempMyNameIs2=$goName[$i];
				echo $tempContentForm;
				$tempContentForm=$_POST[($tempMyNameIs2)];
				echo '
				<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsCheckBox"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'<font class="goTitles">'.$goTitle[$i].'</font><br>';
			};
		}
		echo '    <br>
				<!-- Печатаем кнопки-->
				<input class="goButtonSend" type="submit" name="submit">
				<input class="goButtonClaer" type="reset" value="Очистить">
			</p>';
}
		
if ($boolenMessageWasSend == true)
{
		for ($i = 1; $i <= ($goKolichestvoElementov-$countOfCheckbox+1); $i++) 
		{
			//Выводим все поля кроме чек-боксов
			if ($goSixe[$i] != $goSixeCheckBox)	{
				$tempMyNameIs=$goName[$i];
				$tempContentForm=$_POST[($tempMyNameIs)];
				echo '<font class="goTitles">'.$goTitle[$i].' &#8212; '.$tempContentForm.'</font><br>';
			}
		};
		for ($i = ($goKolichestvoElementov-$countOfCheckbox+1); $i <= $goKolichestvoElementov; $i++)
		{
			if ($flag==1 AND isset($_POST[($goName[$i])]))	{
				echo '<b>Выбранные каталоги и диски:</b><br />';
				$flag=0;
			};
			if ($goSixe[$i] == $goSixeCheckBox AND isset($_POST[($goName[$i])]))	{
				echo '<font class="goTitles">'.$goTitle[$i].'</font><br>';
			};
		};
}
?>




</form>
</div>
	</td>
  </tr>
</table>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>