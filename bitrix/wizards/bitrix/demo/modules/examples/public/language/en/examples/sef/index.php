<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("URLs processing");
?>
<p>You will have to configure SEF support manually only for existing projects (the Kernel module must be updated to version 5.1.8 or higher which enables UrlRewrite). New installations will have SEF support automatically configured.</p>

<p><b>URL Rewrite</b></p>

<p>UrlRewrite is used to enable a script to be accessible not only at its physical address, but also at any other address. For example, you can define such a UrlRewrite rule that a script <i>/fld/c.php</i> that can be accessed at <i>/fld/c.php?id=15 </i>will be called when a page <i>/catalog/15.php</i> is requested.</p>

<p>An address at which the script will be available may not physically exist on the server. In the address exists, the corresponding script will be called. URLRewrite will not be activated.</p>

<p>URL processing rules can be configured in the Control Panel (<i>Settings </i>-&gt; <i>System settings </i>-&gt; <i>URL processing, </i><em>/bitrix/admin/urlrewrite_list.php</em>).</p>

<p>URLRewrite mechanism was created for use with Components 2.0 supporting SEF. However, it can be used to override any other URL's, not only with Components 2.0.</p>

<p>If a SEF-enabled component is added to a page and the page is saved using the editor API, a URL processing rule is created automatically. If the page is created otherwise (e.g. uploaded using FTP), you will have to create the rule manually.</p>

<p><b>Enabling URL processing</b></p>

<p><b>1</b>. If you have the 404 error handler configured - for example, a <i>ErrorDocument </i>directive is specified for Apache in <i>.htaccess</i>: 
  <br />
<code>&nbsp;&nbsp;&nbsp;&nbsp; ErrorDocument 404 /404.php 
    <br />
  </code>you will have to edit <em>/404.php</em> by adding the following call at the beginning of the file: 
  <br />
<code>&nbsp;include_once( $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/urlrewrite.php' ); 
    <br />
  </code></p>

<p><b>2</b>. If you use <b>mod_rewrite</b> with Apache, you can specify the following settings (e.g. in <i>.htaccess</i>): 
  <br />
<code>&lt;IfModule mod_rewrite.c&gt; 
    <br />
  RewriteEngine On 
    <br />
  RewriteCond %{REQUEST_FILENAME} !-f 
    <br />
  RewriteCond %{REQUEST_FILENAME} !-l 
    <br />
  RewriteCond %{REQUEST_FILENAME} !-d 
    <br />
  RewriteCond %{REQUEST_FILENAME} !/bitrix/urlrewrite.php$ 
    <br />
  RewriteRule ^(.*)$ /bitrix/urlrewrite.php [L] 
    <br />
  &lt;/IfModule&gt;</code></p>

<p>The settings will enable the regular SEF support mechanism for new components.</p>

<p><b>How to test your settings</b></p>

<p><b>1</b>. Open <em>Settings -</em>&gt;<em> System settings -</em>&gt;<em> URL processing</em> in the Control Panel.</p>

<p><b>2</b>. Click <b>New record </b>and add: 
  <br />
<em>&nbsp;&nbsp; Condition</em>: #^/sef_test/# 
  <br />
<em>&nbsp;&nbsp; </em><em>Compoment</em>: empty 
  <br />
<em>&nbsp;&nbsp; File</em>: /index.php (physically existing file) 
  <br />
<em>&nbsp;&nbsp; </em><em>Rule</em>: empty. 
  <br />
Save changes.</p>

<p><b>3</b>. Open any page in /sef_test/(e.g. //localhost/sef_test/test.html).</p>

<p>If SEF functions properly, you should see the page specified in the <b>File </b>field of the rule.</p>
<b>Example of using SEF for news details page</b> 
<p>The following rule will map pages whose names are the ID's of news (e.g. <i>234.html</i>), to URL's with the ID parameter (e.g. <i>detail.php?ID=234</i>):</p>
<code>Condition: #^/news/([0-9]+)\.html(\?.*)?$ 
  <br />
Rule: /about/news/detail.php?ID=$1</code> 
<p><b>Opening a .php page when a .html </b><b>file is requested</b></p>

<p>Sometimes, you may be forced to use <b>.html </b>extension for your pages. In this case, the rule may be the following:</p>
<code>Conditon: #^(.+)\.html(\?.*)?$# 
  <br />
Rule: $1.php</code> 
<p><b>Processing old links</b></p>

<p>When you move information from an old site to a new one, you may want to retain links to critical pages that can be indexed by search engines or added to visitors' Favourites. These links require that you establish conditions that will map old links to new ones.</p>

<p>For example, the following rule can be defined for <i>about.html</i>:</p>
<code>Condition: #^/about.html(\?.*)?$# 
  <br />
Rule: /about/index.php 
  <br />
</code><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>