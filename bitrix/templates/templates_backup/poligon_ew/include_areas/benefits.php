<div class="see-also"><?
require_once "{$_SERVER['DOCUMENT_ROOT']}/bitrix/templates/poligon/include_areas/order.php";
/**
*	���� ������������ � �������� ������, ����� ������� ������� �������
*	������� �������� "�����������" � ����������� �� ID ������� (var $SECTION_ID)
*	since 18.04.2012
*	ver. 1.0
*	13/06/2012
*	� ����� ������������ � ��� ������������ "�������" ������� � ��������. ���� �������, 
*	�� �� ������� �������� �������� �� �����, � ��������� ��������� �������� �� ��� ������������� 
*	������� � ��. ��������� ���� ��-�� ����� ���� �� �������, �������� ���� ����. 
*/
switch($SECTION_ID){
	case 160: // ����� ENYA ���� �������?>
	<h3>��. �����: </h3>
	<ul class="mark">
	<li><a href="/content/articles/enya_range.php">������������ � ����� ������������ ����� ��������� ���� ENYA</a></li>
	<?
		switch($ELEMENT_ID){ // ������ �� ������ ������ � ������� ����� ����. ����, ��� ����, �� ������ �� ��������. 
		case 128: case 294: //E1ZM10 12-240VAC/DC?>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) � ������������������� ���� ������� � 2 ����������� ����������"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) � ����������� ���� � 2 ���������� ������������� ���������� �������"/></li>
			<li><entity prop="link" name="E1ZMW10 24-240VAC/DC (VE10)" text="E1ZMW10 24-240VAC/DC (VE10) (110206A) � ������������������� ���� ������� � �������������� �������� �������"/></li>
		<? break;
		case 129: case 295: //E1ZM10 24-240VAC/DC (110200)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ���� ������� � �������� 12-240V AC/DC"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - ������������������� ���� ������� � 2 ����������� ����������"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - ����������� ���� � 2 ���������� ������������� ���������� �������"/></li>
			<li><entity prop="link" name="E1ZMW10 24-240VAC/DC (VE10)" text="E1ZMW10 24-240VAC/DC (VE10) (110206A) � ������������������� ���� ������� � �������������� �������� �������"/></li>
		<? break;
		case 1217: case 1218: //E1ZMQ10 24-240VAC/DC?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ���� ������� � �������� 12-240V AC/DC � 7 ���������"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - ������������������� ���� ������� � 2 ����������� ����������"/></li>
			<li><entity prop="link" name="E1Z1E10 24-240VAC/DC (VE10)" text="E1Z1E10 24-240VAC/DC (VE10) (110204A) � ������� ���� ������� � �������� �������� ���������"/></li>
			<li><entity prop="link" name="E1Z1R10 24-240V AC/DC (VE10)" text="E1Z1R10 24-240V AC/DC (VE10) (110205A) � ������� ���� ������� � �������� �������� ����������"/></li>
		<?
		break;
		case 300: //E1ZMW10 24-240VAC/DC (VE10)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ���� ������� � �������������� �������� �������"/></li>
			<li><entity prop="link" name="E3ZM20 12-240VAC/DC" text="E3ZM20 12-240VAC/DC (111100) - ������������������� ���� ������� � 2 ����������� ����������"/></li>
		<?
		break;
		case 299: //E1ZI10 12-240VAC/DC (110101)?>
			<li><entity prop="link" name="E3ZI20 12-240VAC/DC" text="E3ZI20 12-240VAC/DC (111101) - ������������������� ������������� ���� ������� � 2 ����������� ����������"/></li>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ������������� ���� �������"/></li>
		<?
		break;
		case 298: //E1Z1E10 24-240VAC/DC (VE10) (110204A)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ���� ������� � �������� 12-240V AC/DC (� �.�. �������� ���������)"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - ����������� ���� � 2 ���������� ������������� ���������� �������"/></li>
			<li><entity prop="link" name="E1Z1R10 24-240V AC/DC (VE10)" text="E1Z1R10 24-240V AC/DC (VE10) (110205A) � ������� ���� ������� � �������� �������� ����������"/></li>			
		<?
		break;
		case 72954: //E1Z1R10 24-240V AC/DC (VE10) (110205A)?>
			<li><entity prop="link" name="E1ZM10 12-240VAC/DC" text="E1ZM10 12-240VAC/DC (110100) � ������������������� ���� ������� � �������� 12-240V AC/DC (� �.�. �������� ���������)"/></li>
			<li><entity prop="link" name="E1ZI10 12-240VAC/DC" text="E1ZI10 12-240VAC/DC (110101) - ����������� ���� � 2 ���������� ������������� ���������� �������"/></li>
			<li><entity prop="link" name="E1Z1E10 24-240VAC/DC (VE10)" text="E1Z1E10 24-240V AC/DC (VE10) (110204A) � ������� ���� ������� � �������� �������� ���������"/></li>
			<li><entity prop="link" name="D6A 3MIN 24-240VAC/DC" text="D6A 3MIN 24-240VAC/DC (234007)  � ������� �������� ���������� � ����������� �� ������� (true off delay)"/></li>
		<?
		break;
		case 303: //E3ZS20 12-240VAC/DC (111300)?>
			<li><entity prop="link" name="D6DS 24VAC/DC 110-240VAC" text="D6DS 24VAC/DC 110-240VAC (234070) � ���� ������� � �������� ������-����������� ������� 22,5��"/></li>
		<?
		break;
		case 131:  // E1ZTP 230VAC (110301)?>
			<li><entity prop="link" name="E1ZTPNC 230VAC" text="E1ZTPNC 230VAC (110300) � ���������� ������ � ����������� ������� �������"/></li>
		<?
		break;
		case 132: // E1ZTPNC 230VAC (110300)?>
			<li><entity prop="link" name="E1ZTP 230VAC" text="E1ZTP 230VAC (110301) � ������� ���������� ������ (4 �������)"/></li>
		<?
		break;/*
		case 74260: // E1ZNT 230VAC (110500)?>
			<li>���������� ������� <entity prop="link" name="E1ZTP 230VAC" text="E1ZTP 230VAC (110301) � ������� ���������� ������ (4 �������)"/>
			� <entity prop="link" name="E1ZTPNC 230VAC" text="E1ZTPNC 230VAC (110300) � ���������� ������ � ����������� ������� �������"/></li>
		<?break;*/
	}?>
	</ul>
	<?
		break;
	case 4988: {// �� ����
		switch($ELEMENT_ID){
		case 216:
		?>
			<h3>��. �����: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="������� ������ SK1"/></li>
				<li><entity prop="link" name="G2LM20 230VAC" text="���� �������� �������� � ������������ ����������"/></li>
			</ul><?; break;
		}
	}
	case 4989: // �� �����
	case 4990: // �� �����
	case 4991: // �� ����
	
	
	case 158: {// ���� �������� GAMMA
		switch($ELEMENT_ID){
			case 1254: break;
			#����� ��� G2LM
			case 1272: ?>
			<h3>��. �����: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="������� ������ SK1"/></li>
				<li>����������� ���� � �������� �� <entity prop="link" name="G2LM20 24VAC" text="24V AC"/> � <entity prop="link" name="G2LM20 115VAC" text="115V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="���� ��� �������� ������ �������� � ��������� ����������"/></li>
			</ul>
			<?; break;
			case 1273:  ?>
			<h3>��. �����: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="������� ������ SK1"/></li>
				<li>����������� ���� � �������� �� <entity prop="link" name="G2LM20 24VAC" text="24V AC"/> � <entity prop="link" name="G2LM20 230VAC" text="230V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="���� ��� �������� ������ �������� � ��������� ����������"/></li>
			</ul>
			<?; break;
			case 1274:  ?>
			<h3>��. �����: </h3>
			<ul class="mark">
				<li><entity prop="link" name="SK1" text="������� ������ SK1"/></li>
				<li>����������� ���� � �������� �� <entity prop="link" name="G2LM20 115VAC" text="115V AC"/> � <entity prop="link" name="G2LM20 230VAC" text="230V AC"/></li>
				<li><entity prop="link" name="E3LM10 230VAC" text="���� ��� �������� ������ �������� � ��������� ����������"/></li>
			</ul>
			<?;  break;
			default: {
			?><h3>��. �����: </h3>
			<ul class="mark">
				<li><entity prop="link" name="G2IW5A10" text="��������������� �� ���� �������� ����������� ���� � ���� � G2IW5A10"/></li>
			</ul>
			<?
			} break;
		}
	?>
	<p><strong>������ TELE?</strong> ���������� ����������, ����������� ��������, ������� ����� � ������� �������� ���������� (2 ����� � ���� �� ���������� ����������� ���� ������� � ���� ��������), ����� 47 ��� ���������� � ������������ ���� ��������. </p>
	<?
	}
		break;	
	case 5416: // graesslin ������������ �������?>
		<h3>��. �����: </h3>
		<ul class="mark">
			<li><a href="/rele/daily-time-switch.php">�������� ���� ������� � �������</a></li>
			<li><a href="/catalog/index.php?SECTION_ID=5417">�������� ���� �������</a> (������� � �������� ����������� ��� ����������������)</li>
			<li><a href="/catalog/index.php?SECTION_ID=159">����������� ���� �������</a></li>
		</ul>
	<?
	break;
	/*
	case 5417:
	case 5418:
	case 5420:
	case 5419:
	case 5457:
	case 5458:
	case 5454:
	case 5423: // ��� ������� �������� ������� ��������	
	?>
<p>Graesslin - �������� ������������� ��������� ����������������, ���������� ����������, ��������, �������� � �����������. �������� �������� � 1956 ���� �������������� ������������� � ���������� ����� 1500 �������. �������� ������� �������� ����������� �������������� Graesslin � ������.</p>	
	<?
		break;*/
	default: break;
}
?>
</div>