<?
function Number2Word_Rus($source, $IS_MONEY = "Y")
{
	$result = "";

	// k - �������
	if ($IS_MONEY == "Y")
	{
		$source = DoubleVal($source);

		$dotpos = strpos($source, ".");
		if ($dotpos === false)
		{
			$ipart = $source;
			$fpart = "";
		}
		else
		{
			$ipart = substr($source, 0, $dotpos);
			$fpart = substr($source, $dotpos + 1);
		}

		$fpart = substr($fpart, 0, 2);
		while (strlen($fpart)<2) $fpart .= "0";
	}
	else
	{
		$source = IntVal($source);
		$ipart = $source;
		$fpart = "";
	}

	while ($ipart[0]=="0") $ipart = substr($ipart, 1);

	$ipart1 = StrRev($ipart);
	$ipart = "";
	$i = 0;
	while ($i<strlen($ipart1))
	{
		$ipart_tmp = $ipart1[$i];
		// t - ������; m - �������; b - ���������;
		// e - �������; d - �������; c - �����;
		if ($i % 3 == 0)
		{
			if ($i==0) $ipart_tmp .= "e";
			elseif ($i==3) $ipart_tmp .= "et";
			elseif ($i==6) $ipart_tmp .= "em";
			elseif ($i==9) $ipart_tmp .= "eb";
			else $ipart_tmp .= "x";
		}
		elseif ($i % 3 == 1) $ipart_tmp .= "d";
		elseif ($i % 3 == 2) $ipart_tmp .= "c";
		$ipart = $ipart_tmp.$ipart;
		$i++;
	}

	if ($IS_MONEY == "Y")
	{
		$result = $ipart.".".$fpart."k";
	}
	else
	{
		$result = $ipart;
	}

	if ($result[0] == ".")
		$result = "���� ".$result;

	$result = str_replace("0c0d0et", "", $result);
	$result = str_replace("0c0d0em", "", $result);
	$result = str_replace("0c0d0eb", "", $result);

	$result = str_replace("0c", "", $result);
	$result = str_replace("1c", "��� ", $result);
	$result = str_replace("2c", "������ ", $result);
	$result = str_replace("3c", "������ ", $result);
	$result = str_replace("4c", "��������� ", $result);
	$result = str_replace("5c", "������� ", $result);
	$result = str_replace("6c", "�������� ", $result);
	$result = str_replace("7c", "������� ", $result);
	$result = str_replace("8c", "��������� ", $result);
	$result = str_replace("9c", "��������� ", $result);

	$result = str_replace("1d0e", "������ ", $result);
	$result = str_replace("1d1e", "����������� ", $result);
	$result = str_replace("1d2e", "���������� ", $result);
	$result = str_replace("1d3e", "���������� ", $result);
	$result = str_replace("1d4e", "������������ ", $result);
	$result = str_replace("1d5e", "���������� ", $result);
	$result = str_replace("1d6e", "����������� ", $result);
	$result = str_replace("1d7e", "���������� ", $result);
	$result = str_replace("1d8e", "������������ ", $result);
	$result = str_replace("1d9e", "������������ ", $result);

	$result = str_replace("0d", "", $result);
	$result = str_replace("2d", "�������� ", $result);
	$result = str_replace("3d", "�������� ", $result);
	$result = str_replace("4d", "����� ", $result);
	$result = str_replace("5d", "��������� ", $result);
	$result = str_replace("6d", "���������� ", $result);
	$result = str_replace("7d", "��������� ", $result);
	$result = str_replace("8d", "����������� ", $result);
	$result = str_replace("9d", "��������� ", $result);

	$result = str_replace("0e", "", $result);
	$result = str_replace("5e", "���� ", $result);
	$result = str_replace("6e", "����� ", $result);
	$result = str_replace("7e", "���� ", $result);
	$result = str_replace("8e", "������ ", $result);
	$result = str_replace("9e", "������ ", $result);

	if ($IS_MONEY == "Y")
	{
		$result = str_replace("1e.", "���� ����� ", $result);
		$result = str_replace("2e.", "��� ����� ", $result);
		$result = str_replace("3e.", "��� ����� ", $result);
		$result = str_replace("4e.", "������ ����� ", $result);
	}
	else
	{
		$result = str_replace("1e", "���� ", $result);
		$result = str_replace("2e", "��� ", $result);
		$result = str_replace("3e", "��� ", $result);
		$result = str_replace("4e", "������ ", $result);
	}

	$result = str_replace("1et", "���� ������ ", $result);
	$result = str_replace("2et", "��� ������ ", $result);
	$result = str_replace("3et", "��� ������ ", $result);
	$result = str_replace("4et", "������ ������ ", $result);
	$result = str_replace("1em", "���� ������� ", $result);
	$result = str_replace("2em", "��� �������� ", $result);
	$result = str_replace("3em", "��� �������� ", $result);
	$result = str_replace("4em", "������ �������� ", $result);
	$result = str_replace("1eb", "���� �������� ", $result);
	$result = str_replace("2eb", "��� ��������� ", $result);
	$result = str_replace("3eb", "��� ��������� ", $result);
	$result = str_replace("4eb", "������ ��������� ", $result);

	if ($IS_MONEY == "Y")
	{
		$result = str_replace("11k", "11 ������", $result);
		$result = str_replace("12k", "12 ������", $result);
		$result = str_replace("13k", "13 ������", $result);
		$result = str_replace("14k", "14 ������", $result);
		$result = str_replace("1k", "1 �������", $result);
		$result = str_replace("2k", "2 �������", $result);
		$result = str_replace("3k", "3 �������", $result);
		$result = str_replace("4k", "4 �������", $result);
	}

	if ($IS_MONEY == "Y")
		$result = str_replace(".", "������ ", $result);

	$result = str_replace("t", "����� ", $result);
	$result = str_replace("m", "��������� ", $result);
	$result = str_replace("b", "���������� ", $result);

	if ($IS_MONEY == "Y")
		$result = str_replace("k", " ������", $result);

	return (ToUpper(substr($result, 0, 1)) . substr($result, 1));
}
?>
