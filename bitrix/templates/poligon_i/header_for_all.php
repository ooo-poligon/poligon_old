<?$APPLICATION->ShowPanel();?>
		<div style="position:fixed; height:100px;"></div>
		<header class="top_bar">
			<section id="special">
				<div id="special_container">
				<?
					if(CModule::IncludeModule("iblock"))
					{
						//$i=0;
						$arFilter = Array("IBLOCK_ID"=>8, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
						$res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
						while($ar_res = $res->GetNext())
						{
							$mass[] = $ar_res;
						}
						echo '<ul id="special_list">';
						for ($i=0; $i<=count($mass); $i++){
							$db_props = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"picture"));
							$db_props1 = CIBlockElement::GetProperty(8, $mass[$i]["ID"], "sort", "asc", Array("CODE"=>"link"));
							echo '<li>';
							echo '<div>';
							if ($ar_props = $db_props->Fetch()){ 
								if ($ar_props["VALUE"]){
									echo '<img id="special_image" src="'.$ar_props["VALUE"].'" alt="" />';
									echo '<br>';
										if ($ar_props1 = $db_props1->Fetch()){ 
											echo '<a href="'.str_ireplace('&', '&amp;', $ar_props1["VALUE"]).'">';
										}
										echo $mass[$i]["NAME"].'</a>';
										echo '<br>';
										echo '<br>';
								}
								echo $mass[$i]["PREVIEW_TEXT"];
								echo '</div>';
								echo '</li>';
							}
						}
						echo '</ul>';
					}
				?>
				</div>
					<div class="special_hide_button">
						<button href="#" id="special_close">������� ������ ���������������</button>
					</div>
			</section>
			<section id="contacts">
				<article>
					<header>
						�����: 197376, �����-���������, ��. ���� ��������, �. 7, ���� 501 <span>(300� �� ��.�. �������������)</span>
					</header>
						<div class="yandex_map"> 
							<script type="text/javascript" charset="utf-8" src="//api-maps.yandex.ru/services/constructor/1.0/js/?sid=mMcHB3adzZU56nSeZMihcmzE0Jlbq5ti&amp"></script>
						</div>
						<div class="contacts_column_left">
							<p>
								����� ������ <br><span>(����������� ������ � �������� <br>�� ���� ������������, <br>����������� ����������,
									<br>
								������������������ ���������):</span> 
								<br><br>
								���������� ��������: <br>(812) 325-4220, <br>(812) 325-6420
								
								<p>������� ����: <br><a href="mailto:dolgova@poligon.info">dolgova@poligon.info</a>, <br>���.: (812) 325-64-20 (���. 115)<br></p> 	 
								<p>��������� �����: <br><a href="mailto:elcomp@poligon.info">elcomp@poligon.info</a>, <br>���.: (812) 325-64-20 (���. 114)<br></p>
								
							</p>
						</div>
						<div class="contacts_column_right">
							<p>
								����������� ���������<span> 
									<br>(TELE, RELECO, BENEDICT,
									<br>CITEL, GRAESSLIN, CBI, SONDER, EMKO),
								<br>������� �������� ������������������ ���������:</span>
								<br><br>
								���������� ��������: <br>(812) 335-3-665, <br>(812) 325-4220
								
								<p>�������� �������: <br><a href="mailto:edn@poligon.info">edn@poligon.info</a>, <br>���.: (812) 325-64-20 (���. 141)<br></p> 
								<p>������� �������: <br><a href="mailto:kruten@poligon.info">kruten@poligon.info</a>, <br>���.: (812) 325-64-20 (���. 148)<br></p>								
							</p>
						</div>
					<div class="feedback">	
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td align="center" valign="top">
									<h2>����� �������� �����</h2>
									<br>
									<div style="text-align:left; width:320px;">	
										<?php
											//include ��������� ���������
											include("/content/feedback/func.inc");
											include("/content/feedback/useragent.inc");	
											// ���� ����������� ������ ������������ ��� ��������.
											$goInput = 'input'; // ������ HTML ���� input
											$goTextArea = 'textarea';
											$goSixeInput = ' type="text"  size="50"  maxlength="100" '; // ����� ������ TAG input
											$goSixeTextArea  = ' cols="57" rows="4" '; // ����� ������ TAG textarea
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
											$goSend[To]='webmaster-poligon@yandex.ru'; // ���������� ������ � ��������� ����
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
											$goTitle [6]='������/������:';			$goTypeHTML [6]=$goTextArea; $goName[6]='question';	$goReqParam[6]=true; 
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
										<span class="goAttensionTitle"><font color="#FF0000">��������! </font>��� ���� ����������� ��� ����������:</span>
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
															case (false): //���� ���� �� �������� ������������, �� ���������� ��������.
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
														echo '<table>';
														for ($i = 1; $i <= $goKolichestvoElementov; $i++) 
														{
															$tempMyNameIs=$goName[$i];
															$tempContentForm=$_POST[($tempMyNameIs)];
															echo '<tr><td><font class="goTitles">'.$goTitle[$i].'</font></td><td>
															<'.$goTypeHTML[$i].' name="'.$goName[$i].'" class="goFormsInputAndTextarea"	id="'.$goName[$i].'"	'.$goSixe[$i].$goValueStart[$i].$tempContentForm.$goValueEnd[$i].'
															</td></tr>';
														}
														echo '    
														<!-- �������� ������--><tr><td></td><td>
														<input class="goButtonSend" type="submit" name="submit">
														<input class="goButtonClear" type="reset" value="��������">
														</td></tr>';
														echo '	</table>';
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
						</div>
						<div class="contacts_hide_button">
							<button href="#" id="trigger_2">������� ������ ���������</button>
						</div>
					</article>
			</section>
				<section class="top_background">
					<a href="/index.php" class="site_logo">
						<img class="site_logo_image" src="/bitrix/templates/poligon_i/images/logo.png" alt="������� ��� �������"/>
					</a>
					<nav class="site_menu_container">
						<ul class="site_menu_ul">
							<li class="site_menu_li">
								<a href="/index.php">�������</a>
							</li>
							<li class="site_menu_li">
								<a href="/content/news/">�������</a>
							</li>
							<li class="site_menu_li">
								<a href="/catalog/">�������</a>
							</li>
							<li class="site_menu_li">
								<a href="/content/articles/">����������</a>
							</li>
							<li class="site_menu_li_last">
								<a href="#" id="trigger_1">��������</a>
							</li>
						</ul>
					</nav>
					<p class="phone">
						(812) 325-42-20
					</p>
					<div class="search">
						<a href="/content/feedback/quick_order/"><img class="quick_logo_img" src="/bitrix/templates/poligon_i/images/quick_logo_off.png"
							onmouseover="this.src='/bitrix/templates/poligon_i/images/quick_logo_on.png';"
							onmouseout="this.src='/bitrix/templates/poligon_i/images/quick_logo_off.png';"
						/></a>
						<?$APPLICATION->IncludeComponent("bitrix:search.form", "form", Array("PAGE"	=>	"#SITE_DIR#search/index.php"));?>
						<a href="#" id="special_open"><img class="special_logo_img" src="/bitrix/templates/poligon_i/images/special_logo_off.png"
							onmouseover="this.src='/bitrix/templates/poligon_i/images/special_logo_on.png';"
							onmouseout="this.src='/bitrix/templates/poligon_i/images/special_logo_off.png';"
						/></a>
						
					</div>
					<div class="partners_pad"></div>
					<div class="partners">
						<table class="partners_table">
							<tr>
								<td>
									<a href="/index.php#tele_ancor"><img class="partners_logo" src="/images/logo/logo_200/tele_grey.gif" alt="TELE" title="���� ������� � �������� �� �������"
										
										onmouseover="this.src='/images/logo/logo_200/tele.gif';"
										onmouseout="this.src='/images/logo/logo_200/tele_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#citel_ancor">  <img class="partners_logo" src="/images/logo/logo_200/citel_grey.gif" alt="CITEL" title="CITEL - ���������� ������������"
										
										onmouseover="this.src='/images/logo/logo_200/citel.gif';"
										onmouseout="this.src='/images/logo/logo_200/citel_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#benedict_ancor"><img class="partners_logo" src="/images/logo/logo_200/benedict_grey.gif" alt="Benedict" title="����������, ���������, ������ Benedict"
										
										onmouseover="this.src='/images/logo/logo_200/benedict.gif';"
										onmouseout="this.src='/images/logo/logo_200/benedict_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#graesslin_ancor"><img class="partners_logo" src="/images/logo/logo_200/graesslin_grey.gif" alt="Graesslin" title="Graesslin - ������� � ��������. ������� � ��������."
										
										onmouseover="this.src='/images/logo/logo_200/graesslin.gif';"
										onmouseout="this.src='/images/logo/logo_200/graesslin_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#sonder_ancor"><img class="partners_logo" src="/images/logo/logo_200/sonder_grey.gif" alt="SONDER" title="SONDER - ����������"
										
										onmouseover="this.src='/images/logo/logo_200/sonder.gif';"
										onmouseout="this.src='/images/logo/logo_200/sonder_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#relequick_ancor"><img class="partners_logo" src="/images/logo/logo_200/relequick_grey.gif" alt="RELEQUICK" title="RELEQUICK - ������������ ����"
										
										onmouseover="this.src='/images/logo/logo_200/relequick.gif';"
										onmouseout="this.src='/images/logo/logo_200/relequick_grey.gif';"
										
									/></a>
								</td>
							</tr>
							<tr>
								<td>
									<a href="/index.php#comat_releco_ancor"> <img class="partners_logo" src="/images/logo/logo_200/comat_releco_grey.gif" alt="COMAT-RELECO" title="COMAT-RELECO - ������������ ������������� ����"
										
										onmouseover="this.src='/images/logo/logo_200/comat_releco.gif';"
										onmouseout="this.src='/images/logo/logo_200/comat_releco_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#emko_ancor"><img class="partners_logo" src="/images/logo/logo_200/emko_grey.gif" alt="EMKO" title="EMKO - ������������� ������� � � �����������"
										
										onmouseover="this.src='/images/logo/logo_200/emko.gif';"
										onmouseout="this.src='/images/logo/logo_200/emko_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#cbi_ancor"><img class="partners_logo" src="/images/logo/logo_200/cbi_grey.gif" alt="CBI" title="���������������� �������������� �����������"
										
										onmouseover="this.src='/images/logo/logo_200/cbi.gif';"
										onmouseout="this.src='/images/logo/logo_200/cbi_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#huber_suhner_ancor"><img class="partners_logo" src="/images/logo/logo_200/huber-suhner_grey.gif" alt="HUBER+SUHNER" title="��-�������, ���������� ����������"
										
										onmouseover="this.src='/images/logo/logo_200/huber-suhner.gif';"
										onmouseout="this.src='/images/logo/logo_200/huber-suhner_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#farnell_ancor"><img class="partners_logo" src="/images/logo/logo_200/farnell_grey.gif" alt="FARNELL" title="������� ����� � ������� �������� ����������� �����������"
										
										onmouseover="this.src='/images/logo/logo_200/farnell.gif';"
										onmouseout="this.src='/images/logo/logo_200/farnell_grey.gif';"
										
									/></a>
								</td>
								<td>
									<a href="/index.php#vemer_ancor"><img class="partners_logo" src="/images/logo/logo_200/vemer_grey.gif" alt="VEMER" title="������������� ������������ ������� ��� ��������� � �������� �������� ������������� ����������"
										
										onmouseover="this.src='/images/logo/logo_200/vemer.gif';"
										onmouseout="this.src='/images/logo/logo_200/vemer_grey.gif';"
										
									/></a>
								</td>
								<td>
								</tr>
							</table>
						</div>
					</section>
				</header>
		<div class="intro_pad"></div>