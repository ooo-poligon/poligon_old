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
</td>
</tr>
</table>





</div>
<div id="footer">



<!--
	<div id="adress">
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/adress.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
	</div>
-->


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
	
	<div id="copyright" >

<br />

		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/copyright.php"),
			Array(),
			Array("MODE"=>"html")
		);?>

	</div>
<div align="center">



<a href="http://yandex.ru/cy?base=0&amp;host=poligon.info"><img src="http://www.yandex.ru/cycounter?poligon.info" width="88" height="31" alt="Индекс цитирования" border="0" /></a>





<!-- Yandex.Metrika informer -->
<a href="https://metrika.yandex.ru/stat/?id=9739159&amp;from=informer"
target="_blank" rel="nofollow"><img src="//bs.yandex.ru/informer/9739159/3_0_FFFFFFFF_E6E6FAFF_0_pageviews"
style="width:88px; height:31px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (просмотры, визиты и уникальные посетители)" onclick="try{Ya.Metrika.informer({i:this,id:9739159,lang:'ru'});return false}catch(e){}"/></a>
<!-- /Yandex.Metrika informer -->

<!-- begin of Top100 code -->
<script id="top100Counter" type="text/javascript"
src="http://counter.rambler.ru/top100.jcn?3056713"></script>
<noscript>
<a href="http://top100.rambler.ru/navi/3056713/">
<img src="http://counter.rambler.ru/top100.cnt?3056713" min-height="31" min-width="88" alt="Rambler's Top100"
border="0" />
</a></noscript>

<!-- end of Top100 code -->


<!-- Rating@Mail.ru logo -->
<a href="http://top.mail.ru/jump?from=299343">
<img src="//top-fwz1.mail.ru/counter?id=299343;t=441;l=1" 
style="border:0;" height="31" width="88" alt="Рейтинг@Mail.ru" /></a>
<!-- //Rating@Mail.ru logo -->





</div>






</div>






</td>
<td>
<div id="side_logo">



<img src="/bitrix/templates/poligon/images/background_logo.jpg" position="relative" top="220"/>
</div>
</td>
</tr>
</tbody>
</table>




</body>
</html>