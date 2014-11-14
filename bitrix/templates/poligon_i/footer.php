
<!--В Explorer есть баг, когда в контенте область заканчивается на <p></p>, то див наследует маргин от p. В общем не удалять fix ниже комментария -->
<!--[if lte IE 6]><b class="iefix">.</b><![endif]-->
		    </div>
		</td>		
		</tr>
	</table>	
		
</div>
</article>
<!--#################################################################################################################-->
<footer>
	<div id="footer_container">
	<?$APPLICATION->IncludeComponent("bitrix:menu", "footer_menu", Array(
		"ROOT_MENU_TYPE"	=>	"footer",
		"MAX_LEVEL"	=>	"1",
		"CHILD_MENU_TYPE"	=>	"left",
		"USE_EXT"	=>	"N"
		)
	);?>
		<img src="/images/round.gif"  style="position:relative; top:-66px; left:767px;" usemap="#map">
		<map name="map">
			<area shape="circle" coords="70,70,70" href="/PDF/cert/POLIGON_ISO9001.pdf" alt="Посмотреть сертификат">
		</map>	
	<div>
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/copyright.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
	</div>
	</div>
</footer>
<!--#################################################################################################################-->
</body>
</html>
