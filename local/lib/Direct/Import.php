<?
namespace Local\Direct;

use Local\Api\ApiException;

/**
 * Импорт кампаний и других данных из Директа
 * Class Import
 * @package Local\Direct
 */
class Import
{

	public static function campaign($clientId, $campaignId)
	{
		$client = Clients::getById($clientId);
		if (!$client)
			throw new ApiException(['wrong_client'], 400);

		$campaign = Campaigns::getById($client['NAME'], $campaignId);
		if (!$campaign)
			throw new ApiException(['wrong_campaign'], 400);

		$api = new Api5($client['TOKEN'], $client['NAME'], 'campaigns');
		$resCampaigns = $api->method('get', array(
			'SelectionCriteria' => array(
				'Ids' => array($campaign['CampaignID']),
			),
			'FieldNames' => array(
				'Id',
				'Name',
				'ClientInfo',
				'StartDate',
				'EndDate',
				'TimeTargeting',
				'TimeZone',
				'NegativeKeywords',
				'BlockedIps',
				'ExcludedSites',
				'DailyBudget',
				'Notification',
				'Type',
				'Status',
				'State',
				'StatusPayment',
				'StatusClarification',
				'SourceId',
				'Statistics',
				'Currency',
				'Funds',
				'RepresentedBy',
			),
			'TextCampaignFieldNames' => array(
				'BiddingStrategy',
				'Settings',
				'CounterIds',
				'RelevantKeywords',
			),
		));

		if ($resCampaigns['error'])
			return $resCampaigns['error'];

		$directCampaign = $resCampaigns['result']['Campaigns'][0];
		Campaigns::update($campaign, $directCampaign);

		return array(
			$clientId, $campaignId,
		);
	}
}
