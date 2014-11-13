<?
$MESS ['SUBSCRIBE_CONFIRM_NAME'] = "Confirmation of subscription";
$MESS ['SUBSCRIBE_CONFIRM_DESC'] = "#ID# - subscription ID
#EMAIL# - subscription email
#CONFIRM_CODE# - confirmation code
#SUBSCR_SECTION# - section with subscription edit page (specifies in the settings)
#USER_NAME# - subscriber's name (can be absent)
#DATE_SUBSCR# - date of adding/changing the address
";
$MESS ['SUBSCRIBE_CONFIRM_SUBJECT'] = "#SITE_NAME#: Subscription confirmation";
$MESS ['SUBSCRIBE_CONFIRM_MESSAGE'] = "Informational message from #SITE_NAME#
---------------------------------------

Hello,

You have received this message because your address was subscribed for
news from #SERVER_NAME#.

Here is detailed info about your subscription:

Subscription email .............. #EMAIL#
Date of email adding/editing .... #DATE_SUBSCR#

Your confirmation code: #CONFIRM_CODE#

Please visit the link provided in this letter to confirm your subscription.
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#

Or go to this page and enter your confirmaton code manually:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#

You will not receive any message till you send us your confirmation.

---------------------------------------------------------------------
Please save this message because it contains information for authorization.
Using the confirmation code you can change subscription parameters or
unsubscribe.

Edit parameters:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#

Unsubscribe:
http://#SERVER_NAME##SUBSCR_SECTION#subscr_edit.php?ID=#ID#&CONFIRM_CODE=#CONFIRM_CODE#&action=unsubscribe
---------------------------------------------------------------------

This is automatically generated message.
";
?>