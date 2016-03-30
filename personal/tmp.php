<?
use Local\Direct\Clients;

define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("tmp");

//Clients::tmp();

debugmessage($_REQUEST['code']);

$path = $_SERVER["DOCUMENT_ROOT"] . '/local/vendor/googleads/googleads-php-lib/src/';
require_once $path . 'Google/Api/Ads/AdWords/Lib/AdWordsUser.php';
$user = new AdWordsUser();
$user->LogDefaults();

/*$redirectUri = 'http://context.com/personal/tmp.php';
$offline = true;
// Get the authorization URL for the OAuth2 token.
// No redirect URL is being used since this is an installed application. A web
// application would pass in a redirect URL back to the application,
// ensuring it's one that has been configured in the API console.
// Passing true for the second parameter ($offline) will provide us a refresh
// token which can used be refresh the access token when it expires.
$OAuth2Handler = $user->GetOAuth2Handler();
$authorizationUrl = $OAuth2Handler->GetAuthorizationUrl(
	$user->GetOAuth2Info(), $redirectUri, $offline);
debugmessage($authorizationUrl);*/

debugmessage($user->GetOAuth2Info());

$campaignService = $user->GetService('CampaignService');

// Create selector.
$selector = new Selector();
$selector->fields = array('Id', 'Name');
$selector->ordering[] = new OrderBy('Name', 'ASCENDING');

// Create paging controls.
$selector->paging = new Paging(0, AdWordsConstants::RECOMMENDED_PAGE_SIZE);

do {
	// Make the get request.
	$page = $campaignService->get($selector);

	// Display results.
	if (isset($page->entries)) {
		foreach ($page->entries as $campaign) {
			debugmessage($campaign);
		}
	} else {
		debugmessage('no');
	}

	// Advance the paging index.
	$selector->paging->startIndex += AdWordsConstants::RECOMMENDED_PAGE_SIZE;
} while ($page->totalNumEntries > $selector->paging->startIndex);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");