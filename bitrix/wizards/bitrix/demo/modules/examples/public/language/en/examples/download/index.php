<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Configurable Download");
?>

Download <a href="/examples/download/manual.zip">manual.zip</a> 
<br />
 
<br />
 Download <a href="/examples/download/download_private/private_file.zip">private_file.zip</a> 
<br /><br />
The system is capable of providing public or subscription files for download.
<p>The catalog <b>/examples/download/</b> contains an example of downloading a public file and
registering the download event in the <b>Statistics </b>module. All download files reside in <i>/examples/download/files/</i>.
However, <i>/files/</i> is not added to a download URL. When a visitor clicks the link, the system
calls the 404 error handler specified in <i>.htaccess</i> of a current folder. In this example, the
file is <i>download.php</i>.</p>
<p>This folder also contains the script <i>download_balance.php</i>. It balances downloads between
multiple servers. The folders <i>/download/</i> must be identical and have the same structure and
files, on all servers. To activate the autobalancing script, do the following.</p>
<ol>
	<li>Edit <i>.htaccess</i> and specify <i>download_balance.php</i> as the 404 error handler.
	<li>Uncomment <i>$arrHOSTS</i> in <i>download_balance.php</i> and set the URL's of download
		servers and the server selection probability.</li>
</ol>
<p>If you want the system to allow unauthorised downloads, create a file <i> .access.php</i> in the
site root, and type the following directive in it:<br>
<code>&lt;? $PERM[&quot;/&quot;][&quot;*&quot;]=&quot;R&quot;; ?&gt;</code></p>
<p>The catalog <b>/examples/download/download_private/</b>  contains files illustrating the
implementation of downloading private files and registering the download event in the <b>Statistics </b>module.</p>
<p>All files are stored in <i>/examples/download/download_private/files/;</i> the download URL's
have the format <i>&lt;a&nbsp;href=&quot;/examples/download/download_private/private_file.zip&quot;&gt;
private_file.zip&lt;/a&gt;</i> (without <i>/files/</i>).<br>
Parameters of the script are set so that registered users only are allowed to download files in this
catalog. Only registsred users can access file in <i>/files/</i>. <i>download_private.php</i>
 is called as the 404 error handler.
</p>




<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>