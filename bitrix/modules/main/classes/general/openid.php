<?
IncludeModuleLangFile(__FILE__);

class COpenIDClient
{
	var $_trust_providers = array();
	
	function SetTrustProviders($t)
	{
		if (is_array($t))
		{
			$this->_trust_providers = array_filter($t);
		}
	}
	
	function CheckTrustProviders($url)
	{
		if (count($this->_trust_providers) <= 0)
		{
			return true;
		}
		
		$arUrl = CHTTP::ParseURL($url);
		foreach ($this->_trust_providers as $p)
		{
			if (strpos($arUrl['host'], $p) !== false)
			{
				return true;
			}
		}
			
		return false;
	}
	
	function GetOpenIDServerTags($url)
	{
		if ($str = CHTTP::sGet($url, true))
		{
			$server = '';
			$delegate = '';
			if (preg_match('/<link[^>]+rel=["|\']openid.server["|\'][^>]*>/iU'.BX_UTF_PCRE_MODIFIER, $str, $arLinks))
			{
				if (preg_match('/href=["|\']([^"|\']+)["|\']/iU', $arLinks[0], $arHref))
				{
					$server = $arHref[1];
				}
			}
			if (preg_match('/<link[^>]+rel=["|\']openid.delegate["|\'][^>]*>/iU'.BX_UTF_PCRE_MODIFIER, $str, $arLinks))
			{
				if (preg_match('/href=["|\']([^"|\']+)["|\']/iU', $arLinks[0], $arHref))
				{
					$delegate = $arHref[1];
				}
			}		
			
			if (strlen($server) <= 0)	
			{
				$GLOBALS['APPLICATION']->ThrowException(GetMessage('OPENID_CLIENT_NO_OPENID_SERVER_TAG'));
				return false;
			}
			return array('server' => $server, 'delegate' => $delegate);
		}
		return false;
	}
	
	function GetRedirectUrl($identity, $return_to = false)
	{
		if (strlen($identity) <= 0)
		{
			$GLOBALS['APPLICATION']->ThrowException(GetMessage('OPENID_CLIENT_EMPTY_IDENTITY'));
			return false;
		}
		if (strlen($identity) > 1024)
		{
			$identity = substr($identity, 0, 1024); // may be 256 ????
		}
		
		if (!$return_to)
		{
			$return_to = 'http://' . $_SERVER['SERVER_NAME'] . $GLOBALS['APPLICATION']->GetCurPageParam('', array(), false);
		}
		
		$_SESSION['BX_OPENID_IDENTITY'] = $identity;
		
		if (strpos(strtolower($identity), 'http://') === false && strpos(strtolower($identity), 'https://') === false)
		{
			$identity = 'http://' . $identity;
		}
		
		if ($arOpenidServerTags = $this->GetOpenIDServerTags($identity))
		{
			if (!$this->CheckTrustProviders($arOpenidServerTags['server']))
			{
				$GLOBALS['APPLICATION']->ThrowException(GetMessage('OPENID_CLIENT_CHECK_TRUST_PRIVIDERS_FAULT'));
				return false;
			}
			
			if (strlen($arOpenidServerTags['delegate']) > 0)
			{
				$identity = $arOpenidServerTags['delegate'];
			}

			$trust_root = COption::GetOptionString('main', 'OPENID_TRUST_ROOT', '', SITE_ID);
			if (strlen($trust_root) <= 0)
			{
				$trust_root = 'http://' . $_SERVER['SERVER_NAME'] . '/';
			}
			
			$url = $arOpenidServerTags['server'] . (strpos($arOpenidServerTags['server'], '?')!==false ? '&' : '?') . 
				'openid.mode=checkid_setup' . 
				'&openid.return_to=' . urlencode($return_to) . 
				'&openid.identity=' . urlencode($identity) . 
				'&openid.trust_root=' . urlencode($trust_root);
			
			$sreg_required = COption::GetOptionString('main', 'OPENID_SREG_REQUIRED', 'email,fullname,gender');
			if (strlen($sreg_required) > 0)
			{
				$url .= '&openid.sreg.required=' . urlencode($sreg_required);
			}
			
			return $url;
		}
		
		return false;
	}
	
	function Validate()
	{
		if ($arOpenidServerTags = $this->GetOpenIDServerTags($_GET['openid_identity']))
		{
			$arParams = array(
				'openid.assoc_handle' => $_GET['openid_assoc_handle'],
				'openid.signed' => $_GET['openid_signed'],
				'openid.sig' => $_GET['openid_sig'],
			);
			$arSigned = explode(',', $_GET['openid_signed']);
			foreach ($arSigned as $s)
			{
				$arParams['openid.' . $s] = $_GET['openid_' . str_replace('.', '_', $s)];
			}
			
			$arParams['openid.mode'] = 'check_authentication';
			
			$str = CHTTP::sPost($arOpenidServerTags['server'], $arParams, true);
			
			if (preg_match('/is_valid\s*\:\s*true/' . BX_UTF_PCRE_MODIFIER, $str))
			{
				return array(
						'server' => $arOpenidServerTags['server'], 
						'identity' => $_GET['openid_identity']
					);
			}
			else 
			{
				$GLOBALS['APPLICATION']->ThrowException(GetMessage('OPENID_CLIENT_ERROR_AUTH'));
			}
		}
		return false;
	}
	
	function Authorize()
	{
		global $APPLICATION, $USER;
		
		if ($arOpenID = $this->Validate())
		{
			$arFields = array(
				'EXTERNAL_AUTH_ID' => 'OPENID#' . $arOpenID['server'],
				'XML_ID' => $arOpenID['identity'],
				'EMAIL' => '',
				'PASSWORD' => randString(30),
			);
			if (array_key_exists('openid_sreg_email', $_GET))
			{
				$arFields['EMAIL'] = $_GET['openid_sreg_email'];
			}
			if (array_key_exists('openid_sreg_gender', $_GET) && ($_GET['openid_sreg_gender'] == 'M' || $_GET['openid_sreg_gender'] == 'F'))
			{
				$arFields['PERSONAL_GENDER'] = $_GET['openid_sreg_gender'];
			}
			if (array_key_exists('openid_sreg_fullname', $_GET))
			{
				$fullname = trim($_GET['openid_sreg_fullname']);
				if (($pos = strpos($fullname, ' ')) !== false)
				{
					$arFields['NAME'] = substr($fullname, 0, $pos);
					$arFields['LAST_NAME'] = substr($fullname, $pos + 1);
				}
				else 
				{
					$arFields['NAME'] = $fullname;
				}
			}
			
			if (array_key_exists('BX_OPENID_IDENTITY', $_SESSION))
			{
				$arFields['LOGIN'] = $_SESSION['BX_OPENID_IDENTITY'];
			}
			else 
			{
				$arFields['LOGIN'] = preg_replace(
					array(',^https?\://,i' . BX_UTF_PCRE_MODIFIER, ',/$,' . BX_UTF_PCRE_MODIFIER),
					'',
					$arOpenID['identity']
				);
			}
			
			$USER_ID = 0;
			
			$rsUsers = $USER->GetList($B, $O, array('XML_ID' => $arFields['XML_ID'], 'EXTERNAL_AUTH_ID' => $arFields['EXTERNAL_AUTH_ID']));
			if ($arUser = $rsUsers->Fetch())
			{
				$USER_ID = $arUser['ID'];
			}
			else 
			{
				$def_group = COption::GetOptionString('main', 'new_user_registration_def_group', '');
				if($def_group != '')
				{
					$arFields['GROUP_ID'] = explode(',', $def_group);
				}
				$rsEvents = GetModuleEvents('main', 'OnBeforeOpenIDUserAdd');
				while ($arEvent = $rsEvents->Fetch())
				{
					$arFields = ExecuteModuleEvent($arEvent, $arFields);
				}
				if ( !($USER_ID = $USER->Add($arFields)) )
				{
					return false;
				}
			}
			if (intval($USER_ID) > 0)
			{
				$USER->Authorize($USER_ID);
				
				$arKillParams = array();
				foreach (array_keys($_GET) as $k)
				{
					if (strpos($k, 'openid_') === 0)
					{
						$arKillParams[] = $k;
					}
				}
				$redirect_url = $APPLICATION->GetCurPageParam('', $arKillParams, false);
				
				$rsEvents = GetModuleEvents('main', 'OnBeforeOpenIDAuthFinalRedirect');
				while ($arEvent = $rsEvents->Fetch())
				{
					$redirect_url = ExecuteModuleEvent($arEvent, $redirect_url, $USER_ID, $arFields);
				}
				if ($redirect_url)
				{
					LocalRedirect($redirect_url);
				}
				return $USER_ID;
			}			
		}
		
		return false;
	}
	
	/*public static*/
	function GetOpenIDAuthStep($request_var = 'OPENID_IDENTITY')
	{
		if (
			COption::GetOptionString('main', 'new_user_registration', 'N') == 'Y' &&
			COption::GetOptionString('main', 'auth_openid', 'N') == 'Y'
		)
		{
			if (array_key_exists('openid_mode', $_GET) && $_GET['openid_mode'] == 'id_res')
			{
				return 2;
			}
			elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists($request_var, $_REQUEST) && strlen($_REQUEST[$request_var]))
			{
				return 1;
			}
		}
		return 0;
	}
	
	/*public static*/
	function OnExternalAuthList()
	{
		global $DB;
		$arResult = Array();
		if (
			COption::GetOptionString('main', 'new_user_registration', 'Y') == 'Y' &&
			COption::GetOptionString('main', 'auth_openid', 'N') == 'Y'
		)
		{
			$query = "SELECT DISTINCT(EXTERNAL_AUTH_ID) AS EXTERNAL_AUTH_ID FROM b_user " . 
				"WHERE (EXTERNAL_AUTH_ID IS NOT NULL AND EXTERNAL_AUTH_ID LIKE 'OPENID%')";
				
			$rs = $DB->Query($query);
			while ($arr = $rs->Fetch())
			{
				$arResult[] = Array(
					'ID' => $arr['EXTERNAL_AUTH_ID'],
					'NAME' => $arr['EXTERNAL_AUTH_ID'],
					);			
			}
		}
		return $arResult;
	}	
}
