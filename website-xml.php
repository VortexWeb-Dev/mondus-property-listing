<?php
require 'utils/index.php';
require_once __DIR__ . "/crest/settings.php";

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = WEB_HOOK_URL;
$entityTypeId = LISTINGS_ENTITY_TYPE_ID;
$fields = [
    'id',
    'ufCrm15ReferenceNumber',
    'ufCrm15PermitNumber',
    'ufCrm15ReraPermitNumber',
    'ufCrm15DtcmPermitNumber',
    'ufCrm15OfferingType',
    'ufCrm15PropertyType',
    'ufCrm15HidePrice',
    'ufCrm15RentalPeriod',
    'ufCrm15Price',
    'ufCrm15ServiceCharge',
    'ufCrm15NoOfCheques',
    'ufCrm15City',
    'ufCrm15Community',
    'ufCrm15SubCommunity',
    'ufCrm15Tower',
    'ufCrm15TitleEn',
    'ufCrm15TitleAr',
    'ufCrm15DescriptionEn',
    'ufCrm15DescriptionAr',
    'ufCrm15TotalPlotSize',
    'ufCrm15Size',
    'ufCrm15Bedroom',
    'ufCrm15Bathroom',
    'ufCrm15AgentId',
    'ufCrm15AgentName',
    'ufCrm15AgentEmail',
    'ufCrm15AgentPhone',
    'ufCrm15AgentPhoto',
    'ufCrm15BuildYear',
    'ufCrm15Parking',
    'ufCrm15Furnished',
    'ufCrm_15_360_VIEW_URL',
    'ufCrm15PhotoLinks',
    'ufCrm15FloorPlan',
    'ufCrm15Geopoints',
    'ufCrm15AvailableFrom',
    'ufCrm15VideoTourUrl',
    'ufCrm15Developers',
    'ufCrm15ProjectName',
    'ufCrm15ProjectStatus',
    'ufCrm15ListingOwner',
    'ufCrm15Status',
    'ufCrm15PfEnable',
    'ufCrm15BayutEnable',
    'ufCrm15DubizzleEnable',
    'ufCrm15WebsiteEnable',
    'updatedTime',
    'ufCrm15TitleDeed',
    'ufCrm15Amenities'
];

$properties = fetchAllProperties($baseUrl, $entityTypeId, $fields, 'website');

if (count($properties) > 0) {
    $xml = generateWebsiteXml($properties);
    echo $xml;
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?><list></list>';
}
