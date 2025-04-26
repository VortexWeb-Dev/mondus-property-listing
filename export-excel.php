<?php

require __DIR__ . "/crest/crest.php";
require __DIR__ . "/crest/crestcurrent.php";
require __DIR__ . "/crest/settings.php";
require __DIR__ . "/utils/index.php";
require __DIR__ . "/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$id = $_GET['id'];

$response = CRest::call('crm.item.list', [
    "entityTypeId" => LISTINGS_ENTITY_TYPE_ID,
    "filter" => ["id" => $id],
    "select" => [
        "ufCrm15ReferenceNumber",
        "ufCrm15OfferingType",
        "ufCrm15PropertyType",
        "ufCrm15SaleType",
        "ufCrm15UnitNo",
        "ufCrm15Size",
        "ufCrm15Bedroom",
        "ufCrm15Bathroom",
        "ufCrm15Parking",
        "ufCrm15LotSize",
        "ufCrm15TotalPlotSize",
        "ufCrm15BuildupArea",
        "ufCrm15LayoutType",
        "ufCrm15TitleEn",
        "ufCrm15DescriptionEn",
        "ufCrm15TitleAr",
        "ufCrm15DescriptionAr",
        "ufCrm15Geopoints",
        "ufCrm15ListingOwner",
        "ufCrm15LandlordName",
        "ufCrm15LandlordEmail",
        "ufCrm15LandlordContact",
        "ufCrm15ReraPermitNumber",
        "ufCrm15ReraPermitIssueDate",
        "ufCrm15ReraPermitExpirationDate",
        "ufCrm15DtcmPermitNumber",
        "ufCrm15Location",
        "ufCrm15BayutLocation",
        "ufCrm15ProjectName",
        "ufCrm15ProjectStatus",
        "ufCrm15Ownership",
        "ufCrm15Developers",
        "ufCrm15BuildYear",
        "ufCrm15Availability",
        "ufCrm15AvailableFrom",
        "ufCrm15RentalPeriod",
        "ufCrm15Furnished",
        "ufCrm15DownPaymentPrice",
        "ufCrm15NoOfCheques",
        "ufCrm15ServiceCharge",
        "ufCrm15PaymentMethod",
        "ufCrm15FinancialStatus",
        "ufCrm15AgentName",
        "ufCrm15ContractExpiryDate",
        "ufCrm15FloorPlan",
        "ufCrm15QrCodePropertyBooster",
        "ufCrm15VideoTourUrl",
        "ufCrm_15_360_VIEW_URL",
        "ufCrm15BrochureDescription",
        "ufCrm_15_BROCHURE_DESCRIPTION_2",
        "ufCrm15PhotoLinks",
        "ufCrm15Notes",
        "ufCrm15Amenities",
        "ufCrm15Price",
        "ufCrm15Status",
        "ufCrm15HidePrice",
        "ufCrm15PfEnable",
        "ufCrm15BayutEnable",
        "ufCrm15DubizzleEnable",
        "ufCrm15WebsiteEnable",
        "ufCrm15TitleDeed",
        "ufCrm_12_LANDLORD_NAME_2",
        "ufCrm_12_LANDLORD_EMAIL_2",
        "ufCrm_12_LANDLORD_CONTACT_2",
        "ufCrm_12_LANDLORD_NAME_3",
        "ufCrm_12_LANDLORD_EMAIL_3",
        "ufCrm_12_LANDLORD_CONTACT_3"
        // "ufCrm15City",
        // "ufCrm15Community",
        // "ufCrm15SubCommunity",
        // "ufCrm15Tower",
        // "ufCrm15BayutCity",
        // "ufCrm15BayutCommunity",
        // "ufCrm15BayutSubCommunity",
        // "ufCrm15BayutTower",
        // "ufCrm15AgentId",
        // "ufCrm15AgentEmail",
        // "ufCrm15AgentPhone",
        // "ufCrm15AgentLicense",
        // "ufCrm15AgentPhoto",
        // "ufCrm15Watermark",
    ]
]);

$property = $response['result']['items'][0];

if (!$property) {
    die("Property not found.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

function getExcelColumn($index)
{
    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
}

$columnIndex = 1;
foreach ($property as $key => $value) {
    if (empty($value)) {
        continue;
    }

    $colLetter = getExcelColumn($columnIndex);
    $sheet->setCellValue($colLetter . '1', $key);
    $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
    $sheet->setCellValue($colLetter . '2', is_array($value) ? implode(', ', $value) : $value); // Values
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    $columnIndex++;
}

function sanitizeFileName($filename)
{
    $filename = trim($filename);
    $filename = str_replace(' ', '_', $filename);
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);
    $filename = preg_replace('/_+/', '_', $filename);

    return $filename;
}

$filename = "property_" . sanitizeFileName($property['ufCrm15ReferenceNumber']) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
