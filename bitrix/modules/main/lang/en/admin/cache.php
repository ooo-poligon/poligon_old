<?
$MESS ['MCACHE_TITLE'] = "Cache Settings";
$MESS ['MAIN_OPTION_HTML_CACHE'] = "HTML page generation";
$MESS ['MAIN_TAB_3'] = "Cleaning Cache Files";
$MESS ['MAIN_TAB_2'] = "HTML cache";
$MESS ['MAIN_TAB_4'] = "Component caching";
$MESS ['MAIN_OPTION_CLEAR_CACHE'] = "Cleaning Cache Files";
$MESS ['MAIN_OPTION_PUBL'] = "Components Cache settings";
$MESS ['MAIN_OPTION_CLEAR_CACHE_OLD'] = "Only outdated";
$MESS ['MAIN_OPTION_CLEAR_CACHE_ALL'] = "All";
$MESS ['MAIN_OPTION_CLEAR_CACHE_MENU'] = "Menu";
$MESS ['MAIN_OPTION_CLEAR_CACHE_MANAGED'] = "All managed cache";
$MESS ['MAIN_OPTION_CLEAR_CACHE_STATIC'] = "All pages in HTML cache";
$MESS ['MAIN_OPTION_CLEAR_CACHE_CLEAR'] = "Clear";
$MESS ['MAIN_OPTION_CACHE_ON'] = "Componets caching is enabled by default";
$MESS ['MAIN_OPTION_CACHE_OFF'] = "Componets caching is disabled by default";
$MESS ['MAIN_OPTION_CACHE_BUTTON_OFF'] = "Disable Caching";
$MESS ['MAIN_OPTION_CACHE_BUTTON_ON'] = "Enable Caching";
$MESS ['MAIN_OPTION_HTML_CACHE_WARNING'] = "Attention! The Statistics and/or Advertising modules detected. The data in the HTML cache will be tracked incorrectly.";
$MESS ['MAIN_OPTION_HTML_CACHE_WARNING_TRANSID'] = "Attention! session.use_trans_sid parameter is enabled. HTML caching will not work.";
$MESS ['MAIN_OPTION_HTML_CACHE_ON'] = "HTML cache is on";
$MESS ['MAIN_OPTION_HTML_CACHE_OFF'] = "HTML cache is off";
$MESS ['MAIN_OPTION_HTML_CACHE_BUTTON_OFF'] = "Disable HTML cache";
$MESS ['MAIN_OPTION_HTML_CACHE_BUTTON_ON'] = "Enable HTML cache";
$MESS ['MAIN_OPTION_HTML_CACHE_OPT'] = "HTML cache settings";
$MESS ['MAIN_OPTION_HTML_CACHE_INC_MASK'] = "Inclusion mask";
$MESS ['MAIN_OPTION_HTML_CACHE_EXC_MASK'] = "Exclusion mask";
$MESS ['MAIN_OPTION_HTML_CACHE_QUOTA'] = "Disk quote (MB)";
$MESS ['MAIN_OPTION_HTML_CACHE_SUCCESS'] = "The HTML cache mode has been changed successfully.";
$MESS ['MAIN_OPTION_HTML_CACHE_STAT'] = "Statistics";
$MESS ['MAIN_OPTION_HTML_CACHE_STAT_HITS'] = "Cache hits";
$MESS ['MAIN_OPTION_HTML_CACHE_STAT_MISSES'] = "Cache misses";
$MESS ['MAIN_OPTION_HTML_CACHE_STAT_QUOTA'] = "Cache cleanups caused by the lack of disk space";
$MESS ['MAIN_OPTION_HTML_CACHE_STAT_POSTS'] = "Cache cleanups caused by data modification";
$MESS ['MAIN_OPTION_HTML_CACHE_SAVE'] = "Save HTML cache settings";
$MESS ['MAIN_OPTION_HTML_CACHE_RESET'] = "Apply default settings";
$MESS ['cache_admin_note3'] = "<p>It is recommended to use HTML caching for rarely changing site sections that is mostly visited by anonymous users. The following processes take place when HTML cache is enabled: </p>
<ul style=\"font-size:100%\">
<li>HTML cache processes only pages listed in inclusion mask and not listed in exclusion mask;</li>
<li>For non-authorized visitors system check for the page copy stored in HML cache. If page is found in the cache it is being displayed with no system modules included. Statistics will not track visitors. Advertising, Kernel and other modules will not be included as well.;</li>
<li>Page will be sent compressed if Compression module was installed at the moment of cache generation;</li>
<li>If there is no cache found for the page it is being processed in normal way. After finishing the page load its copy will be saved in HTML cache;</li>
</ul>
<p>Cache cleanup:</p>
<ul style=\"font-size:100%\">
<li>If saving of data causes exceeding of the disk quota then cache is completely cleaned  up;</li>
<li>Complete cache cleanup is also performed if any data is changed through the Control Panel;</li>
<li>If data is posted from the public pages of the site (e.g. adding  comments or votes) then only related parts of cache are being cleaned;</li>
</ul>
<p>Please note that all session data for non-authorized users will be deleted when users visit cached site pages.</p>
<p>Important notes:</p>
<ul style=\"font-size:100%\">
<li>Statistics is not being tracked;</li>
<li>Advertising module will work only at the moment of creating HTML cache. Note that it does not affect external Ad modules (Google Ad Sense etc);</li>
<li>Result of goods comparing won’t be saved for non-authorized users (session should be started);</li>
<li>Disk quota should be specified to avoid DOS-attacks on disk space;</li>
<li>All the site section functionality should be checked after enabling HTML cache for it (e.g. blog comments will not work with old blog templates etc);</li>
</ul>";
$MESS ['MAIN_OPTION_CACHE_OK'] = "Cache Files cleaned";
$MESS ['MAIN_OPTION_CACHE_SUCCESS'] = "Type of components caching successfully switched";
$MESS ['MAIN_OPTION_CACHE_ERROR'] = "Type of components caching is already set to this value";
$MESS ['cache_admin_note1'] = "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
		<tr>
				<td valign=\"top\">The use of Autocache mode boosts your site amazingly!</td>
		</tr>
		<tr>
				<td valign=\"top\"><br />
				In Autocache mode, information rendered by components is refreshed according to settings of those components.</td>
		</tr>
		<tr>
				<td valign=\"top\"><br />
				To refresh page cached objects, you can:</td>
		</tr>
</table>
<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
		<tr>
				<td align=\"center\" valign=\"top\">&nbsp;</td>
		</tr>
		<tr>
				<td valign=\"top\">1. Open a required page and refresh its objects by clicking a special update data button on the administrative toolbar.<br />
				<img src=\"/bitrix/images/main/page_cache_en.png\" width=\"187\" height=\"83\" vspace=\"5\" /></td>
		</tr>
		<tr>
				<td align=\"center\" valign=\"top\">&nbsp;</td>
		</tr>
		<tr>
				<td valign=\"top\">2. When in Site Edit mode, you can click a clear cache button of a required component. <br />
				<img src=\"/bitrix/images/main/comp_cache_en.png\" width=\"244\" height=\"129\" vspace=\"5\" /></td>
		</tr>
		<tr>
				<td valign=\"top\">&nbsp;</td>
		</tr>
		<tr>

				<td valign=\"top\">3. Go to the component settings and switch the required components to uncached mode.<br>
				<img src=\"/bitrix/images/main/spisok_en.gif\" width=\"140\" height=\"60\" vspace=\"5\" /></td>
				</tr>
</table>
<br />
<p>After enabling caching mode by default all the components with cache settings <i>\"Auto\"</i> will be switched to work with cache.<br><br>
		Components with cache settings <i>\"Cache\"</i> and with cache time greater then 0 (zero), always work in caching mode.<br><br>
		Components with cache settings <i>\"Do not cache\"</i> or with cache time equal to 0 (zero), always work without caching.</p>";
$MESS ['cache_admin_note2'] = "After cleaning cache files all displayed content will be updated according to new data.
		New cache files will be created gradually on requesting pages with cached areas.";
?>