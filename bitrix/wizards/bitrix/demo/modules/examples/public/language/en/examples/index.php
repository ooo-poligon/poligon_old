<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Examples");
?><p>This section demonstrates the following typical features.</p>
<ul>
	<li>The <b>Menu </b>page was created using the <b>Menu</b> visual component (<i>System </i>-&gt;
		<i>Navigation</i>) that can render a menu of a specified type. The component has many
		presets and numerous settings which allows to create menus of any type: left plain menu,
		horizontal multilevel pop-up menu etc. On the <b>Menu </b>page you will find examples of a
		simple tabbed menu in blue; Vista-style tabbed menu; and a tree-like menu. The template <b><i>/site2/</i></b>
		uses vertical pop-up menu.
	<li><b>Controlled downloads.</b> The system is capable of providing public or subscription files
		for download and register download events in the <b>Statistics </b>module.
	<li><b>RSS import</b>. RSS is an essential part of any modern web site. The <b>RSS Import</b>
		component (<i>Content </i>-&gt; <i>RSS</i>) can obtain data from external sites and publish
		it on your site. You can specify the URL of an external site, port, RSS file path etc.</li>
</ul><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>