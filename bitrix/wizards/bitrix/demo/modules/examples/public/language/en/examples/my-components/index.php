<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("How to create your own component?");
?>
<p>The standard components shipped with the product enable developers to build any complex solution. To modify the behaviour of standard components, it is sufficient to alter the component template or reprocess parameters of a component in <i>result_modifier.php</i>.</p>

<p>result_modifier.php is included immediately before the component template. This script accepts an array of component results and an array of component parameters on input. This allows to alter the array of component results for a required template.</p>

<p>For example: a standard component fully satisfies requirements except that it does not return some required field. In this case, create a file <i>result_modifier.php</i> in the component template and add the field to the array of component results. Or, you can use this file for any other purpose.</p>

<p>However, developers may want to create custom, task-specific components.</p>

<p>Custom components can be created from scratch using the supplied documentation, or they can be based on standard components by changing their functions.</p>

<p>All components reside in <em>/bitrix/components/</em>. Standard components can be found in <em>/bitrix/components/bitrix/</em>. The contents of this folder is modified by the update system and must not be changed by developers.</p>

<p>Attention! Changing any file or structure of <em>/bitrix/components/bitrix/</em> may cause unpredictable results.</p>

<p>Custom components can be saved in any subfolder of <em>/bitrix/components/</em>. For example, this demo site uses a folder <em><strong>/bitrix/components/demo/</strong></em> to store examples of custom components.</p>

<p>In the demo site, you will find the following custom components:</p>

<ul>
  <li>The list of news component </li>

  <li>News details component </li>

  <li>The composite component: <strong>news</strong> </li>
</ul>

<p>In this section, you will find examples of using these components.</p>

<p>You can add components in the visual editor:</p>

<p><img src="/upload/screens/html_editor.png" height="377" alt="My components" width="350"/></p>

<p>The following code is used to connect a component:</p>
<code>&lt;?$APPLICATION-&gt;IncludeComponent(&quot;demo:news.detail&quot;, &quot;.default&quot;, Array(
  <br />
&nbsp;&nbsp;&nbsp;&quot;IBLOCK_TYPE&quot; =&gt; &quot;news&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;IBLOCK_ID&quot; =&gt; &quot;#IBLOCK.ID(XML_ID=content-news)#&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;ELEMENT_ID&quot; =&gt; $_REQUEST[&quot;ID&quot;],
  <br />
&nbsp;&nbsp;&nbsp;&quot;IBLOCK_URL&quot; =&gt; &quot;news_list.php&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;CACHE_TYPE&quot; =&gt; &quot;A&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;CACHE_TIME&quot; =&gt; &quot;3600&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;DISPLAY_PANEL&quot; =&gt; &quot;N&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;SET_TITLE&quot; =&gt; &quot;Y&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;ADD_SECTIONS_CHAIN&quot; =&gt; &quot;N&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;DISPLAY_DATE&quot; =&gt; &quot;Y&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;DISPLAY_NAME&quot; =&gt; &quot;N&quot;,
  <br />
&nbsp;&nbsp;&nbsp;&quot;DISPLAY_PICTURE&quot; =&gt; &quot;Y&quot;
  <br />
&nbsp;)
  <br />
);?&gt;
  <br />
</code>
<p>Please note that the name of a subfolder of <i>/bitrix/components/</i> is used to address components. For example, system components are connected using the following call:</p>
<code>$APPLICATION-&gt;IncludeComponent(&quot;bitrix:news.line&quot;, ...)</code>
<p>Custom components from <i>/bitrix/components/<b>demo</b></i> are connected as follows:</p>
<code>$APPLICATION-&gt;IncludeComponent(&quot;demo:news.line&quot;, ...)</code>
<p>Note that modifying a standard component to create a custom one has a serious drawback: the component will not be updated, errors fixes and new features will not be available.</p>

<!-- <p>You will find the detailed information on creating custom components in <a href="http://www.bitrixsoft.com/help/source/main/help/ru/developer/general/component20/01.components.php.html">the developer's reference</a>.</p> -->

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>