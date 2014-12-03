<!--¬ Explorer есть баг, когда в контенте область заканчиваетс€ на <p></p>, то див наследует маргин от p. ¬ общем не удал€ть fix ниже комментари€ -->
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

	<!-- Yandex.Metrika counter -->
	<div style="display:none;"><script type="text/javascript">
	(function(w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter9739159 = new Ya.Metrika({id:9739159, enableAll: true});
			}
			catch(e) { }
		});
	})(window, "yandex_metrika_callbacks");
	</script></div>
	<script src="//mc.yandex.ru/metrika/watch_visor.js" type="text/javascript" defer="defer"></script>
	<noscript><div><img src="//mc.yandex.ru/watch/9739159" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->	
	
	<div id="copyright">
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/copyright.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
	</div>
</div>
</body>
</html>
