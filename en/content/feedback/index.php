<? // include ��� �� Bitrix
/**
 *	@author "������� ������" <afomich@rambler.ru>, "������ ��������" <vladimir@mashkov.com>
 *	@version 2.0.0
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "����� �������� �����");
$APPLICATION->SetPageProperty("keywords", "��� �������");
$APPLICATION->SetPageProperty("description", "��� �������");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
?>


<style type="text/css">
<!-- ��������� �����, ����������� ��� �����
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
			<a href="/content/feedback/quick_order/"><b>����� "������� ������"</b></a><br />
			<a href="/content/feedback/catalog_order/"><b>����� ��� ������ CD, ��������</b></a><br />
		</td>
	</tr>
  <tr>
    <td align="center" valign="top">
	<h2>����� �������� �����</h2>
	<div style="text-align:left; width:320px;">
	



<?php
	//include ��������� ���������
	include("func.inc");
	include("useragent.inc");
	
	
	
	// ���� ����������� ������ ������������ ��� ��������.
	$goInput = 'input'; // ������ HTML ���� input
	$goTextArea = 'textarea';
	$goSixeInput = ' type="text"  size="50"  maxlength="100" '; // ����� ������ TAG input
	$goSixeTextArea  = ' cols="38" rows="3" '; // ����� ������ TAG textarea
	$goValueInputStart  = 'value="';
	$goValueInputEnd  = '">';
	$goValueTextAreaStart  = '>';
	$goValueTextAreaEnd  = '</textarea>';
	$goAttensionStart = '<span class="goAttensionError" >���������� ��������� ������:<br>';
	$goAttensionSuffix = '�� ��������� ���� ';
	$goAttensionSuffixNotCorrect = '�� ��������� ��������� ���� ';
	$goAttensionEnd = '</span></p>';
	$goMessageWasSend = '<span class="goMessage">������� �� �������� ������ ���������!</span>';	

//***************************************** ����� ������ � ������ ��������� ������� (������) *****************************************//		
	$goSend[To]='fromwebsite@poligon.info'; // ���������� ������ � ��������� ����
	$goSend[Subject] = '������/������ � ����� poligon.info'; // ���� ������ (������ ������ ���������� ������, ������ ������)
	$goIdOfName = 1; // �������� �������� ��������. ��� ����������� �������� � ����� ������������ � ������������ ������.
	$goIdOfEmail = 2; // ������� ����� ������������ ��� �������� Email, � ������ ���������� ������� �������� 0
	$goIdOfPhone = 3; // ������� ����� ������������ ��� �������� ��������, � ������ ���������� ������� �������� 0	
	

//������ $goReqParam[], ����� ��������� ������ �������� true ��� false (���� ������������ ��� �������������� ��� ����������); 

	$goTitle [1]='�.�.�.:';		$goTypeHTML [1]=$goInput; $goName[1]='name'; 	$goReqParam[1]=true; 
	$goTitle [2]='E-Mail:';		$goTypeHTML [2]=$goInput; $goName[2]='email'; 	$goReqParam[2]=true; 
	$goTitle [3]='�������:';	$goTypeHTML [3]=$goInput; $goName[3]='phone1';	$goReqParam[3]=true; 
	$goTitle [4]='��������:';		$goTypeHTML [4]=$goInput; $goName[4]='company'; 	$goReqParam[4]=true; 
	$goTitle [5]='���������:';	$goTypeHTML [5]=$goTextArea; $goName[5]='doljnost';	$goReqParam[5]=true; 
	$goTitle [6]='������/������:';			$goTypeHTML [6]=$goTextArea; $goName[6]='question';	$goReqParam[6]=false; 


//***************************************** ����� ������ � ������ ��������� ������� (�����) *****************************************//

//***************************************** ���, ��� ���� ������ �� ������������� *****************************************//

	$goKolichestvoElementov = count($goTitle); // �� �������, � ������������. ��� ������� ������ ���� �����.
	$goDefaultSendFrom = $goSendTo; // ���� �� �������� ����� ��������� ���������, � ������ ���������� E-Mail ����������� � ������.
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
<p><span class="goAttensionTitle"><font color="#FF0000">��������! </font>��� ���� ����������� ��� ����������:</span>
<form method="post" action="<?PHP_SELF?>">
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
				case (true): //���� ���� ����������� ��� ����������, �� ������ ��������
				if (isset ($_POST [($tempMyNameIs)] ) ) // ���� �������� ���������, ������ ���, �� �������� �� �������������
				{
					if ($_POST[($tempMyNameIs)] =='') // ���� ������ ����������, � ���� �� ���������, �� ��������.
					{
						$tempContentFormErro[$i] = true;
						$tempAttensionStart = $goAttensionStart;
						$tempAttensionSuffix = $goAttensionSuffix;
						$tempAttensionEnd = $goAttensionEnd;
						$tempWeHaveGotError = true;
					 } else { // ���� ������ �����������, �� ����� ��� ���� ���� ��� �������� �� ����������, �� ���������� ��������
						if (($_POST [($goCheck[email])]) == '') // ��������� E-MAIL �� ������������ ����������
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
						
						
						if (($_POST [($goCheck[phone])]) == '') // ��������� ������� �� ������������ ����������
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
				case (false): //���� ���� �� �������� ������������, �� ��������� ��������.
						$tempContentFormErro[$i] = false;
									
				break;
	
			}
			

		}	
if (isset ($_POST [($tempMyNameIs)] ) )
{
	if ($tempAttensionStart != $goAttensionStart) // ������ ���, ���������� ������.
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
	
	$goMailBody = $goMailBody."\n\n����: [".getFullDate(time()).", ".getQuestionTime(time())."]\n--------------------\n\n";
	$tempSendMeFrom = 'From: '.$goGetName.'<'.$goGetEmail.'>'."nReply-To: ".$goGetEmail."\nContent-Type: text/plain; charset=windows-1251\nContent-Transfer-Encoding: 8bit" ;
	$tempSendMe = $goSend[To]."\r\n".$goSend[Subject]."\r\n".$goMailBody."\r\n".$tempSendMeFrom."\r\n \r\n";
			/**
		 *	Send email with message to admin
		 */
	@mail($goSend[To], $goSend[Subject], $goMailBody, $tempSendMeFrom);
	
	#writeDataInFile ($tempSendMe);
	
	//��������, ��� ��� ����������
	echo $goMessageWasSend.'<br><br>';	
	$boolenMessageWasSend = true;


	
	}
}
//�������� ��������� � ������				
echo $tempAttensionStart;
	for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
	{
		if ($tempContentFormErro[$i] == true) 
		{
			echo $tempAttensionSuffix.$goTitle[$i].'<br>';
		}
	}


echo $tempCheckedEmail.$tempCheckedPhone.$tempAttensionEnd;

//�������� ������ ����� � ������
if ($boolenMessageWasSend == false)
{
		for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
		{
		
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			echo '<font class="goTitles">'.$goTitle[$i].'</font><br>
			<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'
			<br>';
		}
		echo '    <br><br>
				<!-- �������� ������-->
				<input class="goButtonSend" type="submit" name="submit">
				<input class="goButtonClaer" type="reset" value="��������">
			</p>';
}
		
if ($boolenMessageWasSend == true)
{
			for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
			{
		
			$tempMyNameIs=$goName[$i];
			$tempContentForm=$_POST[($tempMyNameIs)];
			echo '<font class="goTitles">'.$goTitle[$i].' &#8212; '.$tempContentForm.'</font><br>';
			}
}
?>




</form>
</div>
	</td>
  </tr>
</table>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>