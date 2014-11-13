<?
// конец буферизации для parseForDynamicContent();
//ob_end_flush();
?>
<!--В Explorer есть баг, когда в контенте область заканчивается на <p></p>, то див наследует маргин от p. В общем не удалять fix ниже комментария -->
<!--[if lte IE 6]><b class="iefix">.</b><![endif]-->
	</div>
		</div>
	</div>
	<div class="clear"><!-- --></div>
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
	<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter9739159 = new Ya.Metrika({id:9739159,
						webvisor:true,
						clickmap:true,
						trackLinks:true,
						accurateTrackBounce:true});
			} catch(e) { }
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f, false);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
	</script>
	<noscript><div><img src="//mc.yandex.ru/watch/9739159" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->
	
	<!-- RedHelper -->
	<script id="rhlpscrtg" type="text/javascript" charset="utf-8" async="async" src="https://web.redhelper.ru/service/main.js?c=poligon"></script>
	<!--/Redhelper -->	
	
	<div id="copyright">
	<script type="text/javascript" src="/js/orphus/orphus.js"></script>
	<a href="http://orphus.ru" id="orphus" target="_blank"><img alt="Система Orphus" src="/js/orphus/orphus.gif" border="0" width="121" height="21" /></a>	
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/copyright.php"),
			Array(),
			Array("MODE"=>"html")
		);
		?>
	</div>




<!-- begin of Top100 code -->

<script id="top100Counter" type="text/javascript"
src="http://counter.rambler.ru/top100.jcn?3056713"></script>
<noscript>
<a href="http://top100.rambler.ru/navi/3056713/">
<img src="http://counter.rambler.ru/top100.cnt?3056713" alt="Rambler's Top100"
border="0" />
</a></noscript>

<!-- end of Top100 code -->






</div>






</td>
<td>
<div id="side_logo">
<img src="http://poligon.info/bitrix/templates/poligon/images/background_logo.jpg" position="relative" top="220"/>
</div>
</td>
</tr>
</tbody>
</table>




</body>
</html>