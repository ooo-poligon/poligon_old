</div></div>
	<div class="clear"><!-- --></div>

		
<!--� Explorer ���� ���, ����� � �������� ������� ������������� �� <p></p>, �� ��� ��������� ������ �� p. � ����� �� ������� fix ���� ����������� -->
<!--[if lte IE 6]><b class="iefix">.</b><![endif]-->
			<div class="clear"><!-- --></div>
			<div class="spacer2"><!-- --></div>
		</div>
	</div>
</div>
<div id="footer">
	<div id="adress">
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/adress.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
	</div>
	<?$APPLICATION->IncludeComponent("bitrix:menu", "footer_menu", Array(
		"ROOT_MENU_TYPE"	=>	"footer",
		"MAX_LEVEL"	=>	"1",
		"CHILD_MENU_TYPE"	=>	"left",
		"USE_EXT"	=>	"N"
		)
	);?>
	<div id="copyright">
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/copyright.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
		<img src="images/counter.jpg" alt="" />
	</div>
</div>
</body>
</html>
