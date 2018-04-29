<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Coolrunner_Helper
{

    public static function jsonValidate()
    {
        if (json_last_error() === 0) {
            return true;
        }

        return false;
    }

    public static function getLastURLSegment($url) {
		$path = parse_url($url, PHP_URL_PATH); // to get the path from a whole URL
		$pathTrimmed = trim($path, '/'); // normalise with no leading or trailing slash
		$pathTokens = explode('/', $pathTrimmed); // get segments delimited by a slash

		return end($pathTokens); // get the last segment
	}


	public static function result($error = true, $message = '', $response = array(), $details = array())
	{
		return array(
            'error'     => $error,
            'message'   => $message,
            'result'  	=> $response,
			'details' 	=> $details,
        );
	}

	public static function formatResponse($response, $successfull_response_message = '')
	{
		$code = wp_remote_retrieve_response_code($response);
		$response_message = wp_remote_retrieve_response_message($response);
		$body = wp_remote_retrieve_body($response);
		$header = wp_remote_retrieve_headers($response);
		$json_decoded_body = json_decode($body, true);

		$json_decoded_result = [];

		if (isset($json_decoded_body['result'])) {
			$json_decoded_result = $json_decoded_body['result'];
		}

		if (self::isBadErrorCode($code)) {
			return self::result(true, $response_message, $json_decoded_result, $json_decoded_body);
		}

		$response_message = ($successfull_response_message != '') ? $successfull_response_message : $response_message;

		//We have a successfull request
		return self::result(false, $response_message, $json_decoded_result, $json_decoded_body);
	}

	public static function isBadErrorCode($code = 0)
	{
		return ($code < 200 || $code > 299) ? true : false;
	}

	public static function gramsToKilograms($weight = 0)
	{
		return $weight/1000;
	}

	public static function formatWeight($weight = 0, $suffix = ' Kg', $prefix = '')
	{
		return $prefix.number_format($weight, 2, ',', '.').$suffix;
	}

	public static function formatPriceDKK($price = 0, $suffix = ' DKK', $prefix = '')
	{
		return $prefix.number_format($price, 2, ',', '.').$suffix;
	}

	public static function parcelWeights(){
		return [
			[
				'name'			=> '0 - 0.1kg',
				'weight_from'	=> 0,
				'weight_to'		=> 100,
			],
			[
				'name'			=> '0.1 - 0.25kg',
				'weight_from'	=> 100,
				'weight_to'		=> 250,
			],
			[
				'name'			=> '0.25 - 0.5kg',
				'weight_from'	=> 250,
				'weight_to'		=> 500,
			],
			[
				'name'			=> '0.5 - 1kg',
				'weight_from'	=> 500,
				'weight_to'		=> 1000,
			],

			[
				'name'			=> '1 - 2kg',
				'weight_from'	=> 1000,
				'weight_to'		=> 2000,
			],
			[
				'name'			=> '1 - 5kg',
				'weight_from'	=> 1000,
				'weight_to'		=> 5000,
			],
			[
				'name'			=> '5 - 10kg',
				'weight_from'	=> 5000,
				'weight_to'		=> 10000,
			],
			[
				'name'			=> '10 - 15kg',
				'weight_from'	=> 10000,
				'weight_to'		=> 15000,
			],
			[
				'name'			=> '15 - 20kg',
				'weight_from'	=> 15000,
				'weight_to'		=> 20000,
			],
			[
				'name'			=> '20 - 25kg',
				'weight_from'	=> 20000,
				'weight_to'		=> 25000,
			],
			[
				'name'			=> '25 - 30kg',
				'weight_from'	=> 25000,
				'weight_to'		=> 30000,
			],
			[
				'name'			=> '0 - 1kg',
				'weight_from'	=> 0,
				'weight_to'		=> 1000,
			],
		];
	}

	public static function carrierFreights($carrier = '')
	{
		$carriers = array(
			'dao'	=> [
				0 => [
					'id'		=> 0,
					'carrier'	=> 'dao',
					'name'		=> 'DAO Pakkeshop - 0-20kg',
					'product' 	=> 'private',
					'service'	=> 'droppoint',
					'max_weight'=> 20000,
					'max_size'	=> [
						'L'	=> 50,
						'W'	=> 30,
						'H'	=> 30,
					],
				],
				1 => [
					'id'		=> 1,
					'carrier'	=> 'dao',
					'name'		=> 'DAO Hjemmelevering - Minipakke 0-1kg',
					'product' 	=> 'private',
					'service'	=> 'delivery_letter',
					'max_weight'=> 1000,
					'max_size'	=> [
						'L'	=> 33,
						'W'	=> 23,
						'H'	=> 3,
					],
				],
				2 => [
					'id'		=> 2,
					'carrier'	=> 'dao',
					'name'		=> 'DAO Hjemmelevering - Pakke 0-2kg',
					'product' 	=> 'private',
					'service'	=> 'delivery_package',
					'max_weight'=> 2000,
					'max_size'	=> [
						'L'	=> 33,
						'W'	=> 23,
						'H'	=> 6,
					],
				],

				//'droppoint'		=> 'DAO Pakkeshop',
				//'delivery'		=> 'DAO Hjemmelevering',
			],
			'pdk'	=> [
				0 => [
					'id'		=> 0,
					'carrier'	=> 'pdk',
					'name'		=> 'PostNord Afhentningssted - 0-20kg',
					'product' 	=> 'private',
					'service'	=> 'droppoint',
					'max_weight'=> 20000,
					'max_size'	=> [
						'L'	=> 61,
						'W'	=> 37,
						'H'	=> 35,
					],
				],
				1 => [
					'id'		=> 1,
					'carrier'	=> 'pdk',
					'name'		=> 'PostNord Omdeling - 0-20kg',
					'product' 	=> 'private',
					'service'	=> 'delivery',
					'max_weight'=> 20000,
					'max_size'	=> [
						'L'		=> 220,
						'LC'	=> 360, //L+2*W+2*H
					],
				],
				2 => [
					'id'		=> 2,
					'carrier'	=> 'pdk',
					'name'		=> 'PostNord Erhverv - 0-30kg',
					'product' 	=> 'business',
					'service'	=> '',
					'max_weight'=> 30000,
					'max_size'	=> [
						'L'		=> 220,
						'LC'	=> 360, //L+2*W+2*H
					],
				],

				//'droppoint'		=> 'PostNord Afhentningssted',
				//'delivery'		=> 'PostNord Med Omdeling',
				//'business'		=> 'PostNord Erhverv',
			],
			'gls'	=> [
				0 => [
					'id'		=> 0,
					'carrier'	=> 'gls',
					'name'		=> 'GLS ShopDelivery (Pakkeshop) - 0-20kg',
					'product' 	=> 'private',
					'service'	=> 'droppoint',
					'max_weight'=> 20000,
					'max_size'	=> [
						'L'		=> 200,
						'LC'	=> 300, //L+2*W+2*H
					],
				],
				1 => [
					'id'		=> 1,
					'carrier'	=> 'gls',
					'name'		=> 'GLS PrivateDelivery (Omdeling) - 0-20kg',
					'product' 	=> 'private',
					'service'	=> 'delivery',
					'max_weight'=> 30000,
					'max_size'	=> [
						'L'		=> 200,
						'LC'	=> 300, //L+2*W+2*H
					],
				],
				2 => [
					'id'		=> 2,
					'carrier'	=> 'gls',
					'name'		=> 'GLS BusinessParcel (Erhverv) - 0-20kg',
					'product' 	=> 'business',
					'service'	=> '',
					'max_weight'=> 30000,
					'max_size'	=> [
						'L'		=> 200,
						'LC'	=> 300, //L+2*W+2*H
					],
				],

				//'droppoint'		=> 'GLS ShopDelivery (Pakkeshop)',
				//'delivery'		=> 'GLS PrivateDelivery (Omdeling)',
				//'business'		=> 'GLS BusinessParcel (Erhverv)',
			],
		);

		if ($carrier != '') {
			return (self::isValidCarrier($carrier)) ? $carriers[$carrier] : null;
		}

		return $carriers;
	}

	public static function availableCarriers($carrier = '')
	{
		$carriers = array(
			'dao'	=> 'Dansk Avis Omdeling',
			'pdk'	=> 'PostNord',
			'gls'	=> 'GLS',
		);

		if ($carrier != '') {
			return (self::isValidCarrier($carrier)) ? $carriers[$carrier] : null;
		}

		return $carriers;
	}

	public static function isValidCarrier($carriers)
	{
		if (is_string($carriers)) {
			return array_key_exists(strtolower($carriers), Coolrunner_Helper::availableCarriers());
		}

		foreach ($carriers as $carrier) {
            if (!array_key_exists(strtolower($carrier), Coolrunner_Helper::availableCarriers())){
                return false;
            }
        }

		return true;
	}

	public static function isInvalidCarrier($carriers)
	{
		return !self::isValidCarrier($carriers);
	}

	public static function isValidCountryCode($code = '')
	{
		return in_array(strtoupper($code), array_keys(Coolrunner_Helper::countryCodes()));
	}

	public static function isInvalidCountryCode($code = '')
	{
		return !self::isValidCountryCode($code);
	}

	/* Notices */
	public static function displaySuccessNotice($str = '') {
		?>
		<div class="updated notice notice-success">
			<p><?php echo $str; ?></p>
		</div>
		<?php
	}

	public static function displayWarningNotice($str = '') {
		?>
		<div class="warning notice notice-warning">
			<p><?php echo $str; ?></p>
		</div>
		<?php
	}
	/* / Notices*/

	public static function countryCodes()
	{
		return array(
			'DK' => 'Denmark',
			'AT' => 'Austria',
			'BE' => 'Belgium',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'EE' => 'Estonia',
			'ES' => 'Spain',
			'FI' => 'Finland',
			'FR' => 'France',
			'GB' => 'United Kingdom',
			'HU' => 'Hungary',
			'IE' => 'Ireland',
			'IT' => 'Italy',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'NL' => 'Netherlands',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'SK' => 'Slovakia',
			'SE' => 'Sweden',
		);
	}

	public static function isValidPostcode($postcode = '')
	{
		return array_key_exists($postcode, self::postcodes());
	}

	public static function isInvalidPostcode($postcode = '')
	{
		return !self::isValidPostcode($postcode);
	}

	public static function postcodes()
	{
		/*$d = 'https://dawa.aws.dk/postnumre';

		$get = wp_remote_get($d);
		$body = json_decode(wp_remote_retrieve_body($get), true);
		echo 'array(';
		foreach ($body as $k) {
			echo "'".$k['nr']."' => '".$k['navn']."',"."\n";
			//pred($k);
		}
		echo ');';*/

		return array('1050' => 'København K', '1051' => 'København K', '1052' => 'København K', '1053' => 'København K', '1054' => 'København K', '1055' => 'København K', '1056' => 'København K', '1057' => 'København K', '1058' => 'København K', '1059' => 'København K', '1060' => 'København K', '1061' => 'København K', '1062' => 'København K', '1063' => 'København K', '1064' => 'København K', '1065' => 'København K', '1066' => 'København K', '1067' => 'København K', '1068' => 'København K', '1069' => 'København K', '1070' => 'København K', '1071' => 'København K', '1072' => 'København K', '1073' => 'København K', '1074' => 'København K', '1100' => 'København K', '1101' => 'København K', '1102' => 'København K', '1103' => 'København K', '1104' => 'København K', '1105' => 'København K', '1106' => 'København K', '1107' => 'København K', '1110' => 'København K', '1111' => 'København K', '1112' => 'København K', '1113' => 'København K', '1114' => 'København K', '1115' => 'København K', '1116' => 'København K', '1117' => 'København K', '1118' => 'København K', '1119' => 'København K', '1120' => 'København K', '1121' => 'København K', '1122' => 'København K', '1123' => 'København K', '1124' => 'København K', '1125' => 'København K', '1126' => 'København K', '1127' => 'København K', '1128' => 'København K', '1129' => 'København K', '1130' => 'København K', '1131' => 'København K', '1150' => 'København K', '1151' => 'København K', '1152' => 'København K', '1153' => 'København K', '1154' => 'København K', '1155' => 'København K', '1156' => 'København K', '1157' => 'København K', '1158' => 'København K', '1159' => 'København K', '1160' => 'København K', '1161' => 'København K', '1162' => 'København K', '1164' => 'København K', '1165' => 'København K', '1166' => 'København K', '1167' => 'København K', '1168' => 'København K', '1169' => 'København K', '1170' => 'København K', '1171' => 'København K', '1172' => 'København K', '1173' => 'København K', '1174' => 'København K', '1175' => 'København K', '1200' => 'København K', '1201' => 'København K', '1202' => 'København K', '1203' => 'København K', '1204' => 'København K', '1205' => 'København K', '1206' => 'København K', '1207' => 'København K', '1208' => 'København K', '1209' => 'København K', '1210' => 'København K', '1211' => 'København K', '1212' => 'København K', '1213' => 'København K', '1214' => 'København K', '1215' => 'København K', '1216' => 'København K', '1218' => 'København K', '1219' => 'København K', '1220' => 'København K', '1221' => 'København K', '1250' => 'København K', '1251' => 'København K', '1252' => 'København K', '1253' => 'København K', '1254' => 'København K', '1255' => 'København K', '1256' => 'København K', '1257' => 'København K', '1259' => 'København K', '1260' => 'København K', '1261' => 'København K', '1263' => 'København K', '1264' => 'København K', '1265' => 'København K', '1266' => 'København K', '1267' => 'København K', '1268' => 'København K', '1270' => 'København K', '1271' => 'København K', '1300' => 'København K', '1301' => 'København K', '1302' => 'København K', '1303' => 'København K', '1304' => 'København K', '1306' => 'København K', '1307' => 'København K', '1308' => 'København K', '1309' => 'København K', '1310' => 'København K', '1311' => 'København K', '1312' => 'København K', '1313' => 'København K', '1314' => 'København K', '1315' => 'København K', '1316' => 'København K', '1317' => 'København K', '1318' => 'København K', '1319' => 'København K', '1320' => 'København K', '1321' => 'København K', '1322' => 'København K', '1323' => 'København K', '1324' => 'København K', '1325' => 'København K', '1326' => 'København K', '1327' => 'København K', '1328' => 'København K', '1329' => 'København K', '1350' => 'København K', '1352' => 'København K', '1353' => 'København K', '1354' => 'København K', '1355' => 'København K', '1356' => 'København K', '1357' => 'København K', '1358' => 'København K', '1359' => 'København K', '1360' => 'København K', '1361' => 'København K', '1362' => 'København K', '1363' => 'København K', '1364' => 'København K', '1365' => 'København K', '1366' => 'København K', '1367' => 'København K', '1368' => 'København K', '1369' => 'København K', '1370' => 'København K', '1371' => 'København K', '1400' => 'København K', '1401' => 'København K', '1402' => 'København K', '1403' => 'København K', '1406' => 'København K', '1407' => 'København K', '1408' => 'København K', '1409' => 'København K', '1410' => 'København K', '1411' => 'København K', '1412' => 'København K', '1413' => 'København K', '1414' => 'København K', '1415' => 'København K', '1416' => 'København K', '1417' => 'København K', '1418' => 'København K', '1419' => 'København K', '1420' => 'København K', '1421' => 'København K', '1422' => 'København K', '1423' => 'København K', '1424' => 'København K', '1425' => 'København K', '1426' => 'København K', '1427' => 'København K', '1428' => 'København K', '1429' => 'København K', '1430' => 'København K', '1431' => 'København K', '1432' => 'København K', '1433' => 'København K', '1434' => 'København K', '1435' => 'København K', '1436' => 'København K', '1437' => 'København K', '1438' => 'København K', '1439' => 'København K', '1440' => 'København K', '1441' => 'København K', '1450' => 'København K', '1451' => 'København K', '1452' => 'København K', '1453' => 'København K', '1454' => 'København K', '1455' => 'København K', '1456' => 'København K', '1457' => 'København K', '1458' => 'København K', '1459' => 'København K', '1460' => 'København K', '1461' => 'København K', '1462' => 'København K', '1463' => 'København K', '1464' => 'København K', '1465' => 'København K', '1466' => 'København K', '1467' => 'København K', '1468' => 'København K', '1470' => 'København K', '1471' => 'København K', '1472' => 'København K', '1473' => 'København K', '1550' => 'København V', '1551' => 'København V', '1552' => 'København V', '1553' => 'København V', '1554' => 'København V', '1555' => 'København V', '1556' => 'København V', '1557' => 'København V', '1558' => 'København V', '1559' => 'København V', '1560' => 'København V', '1561' => 'København V', '1562' => 'København V', '1563' => 'København V', '1564' => 'København V', '1567' => 'København V', '1568' => 'København V', '1569' => 'København V', '1570' => 'København V', '1571' => 'København V', '1572' => 'København V', '1573' => 'København V', '1574' => 'København V', '1575' => 'København V', '1576' => 'København V', '1577' => 'København V', '1600' => 'København V', '1601' => 'København V', '1602' => 'København V', '1603' => 'København V', '1604' => 'København V', '1605' => 'København V', '1606' => 'København V', '1607' => 'København V', '1608' => 'København V', '1609' => 'København V', '1610' => 'København V', '1611' => 'København V', '1612' => 'København V', '1613' => 'København V', '1614' => 'København V', '1615' => 'København V', '1616' => 'København V', '1617' => 'København V', '1618' => 'København V', '1619' => 'København V', '1620' => 'København V', '1621' => 'København V', '1622' => 'København V', '1623' => 'Købe@�    @�                    O�            �L�    ��            `�     @      `�            nhavn V', '1634' => 'København V', '1635' => 'København V', '1650' => 'København V', '1651' => 'København V', '1652' => 'København V', '1653' => 'København V', '1654' => 'København V', '1655' => 'København V', '1656' => 'København V', '1657' => 'København V', '1658' => 'København V', '1659' => 'København V', '1660' => 'Københa��&    ��&                    ��&            ��&    ��&            ��&     @      ��&            `            �benhavn V', '1672' => 'København V', '1673' => 'København V', '1674' => 'København V', '1675' => 'København V', '1676' => 'København V', '1677' => 'København V', '1699' => 'København V', '1700' => 'København V', '1701' => 'København V', '1702' => 'København V', '1703' => 'København V', '1704' => 'København V', '1705' => 'København V', '1706' => 'København V', '1707' => 'København V', '1708' => 'København V', '1709' => 'København V', '1710' => 'København V', '1711' => 'København V', '1712' => 'København V', '1714' => 'København V', '1715' => 'København V', '1716' => 'København V', '1717' => 'København V', '1718' => 'København V', '1719' => 'København V', '1720' => 'København V', '1721' => 'København V', '1722' => 'København V', '1723' => 'København V', '1724' => 'København V', '1725' => 'København V', '1726' => 'København V', '1727' => 'København V', '1728' => 'København V', '1729' => 'København V', '1730' => 'København V', '1731' => 'København V', '1732' => 'København V', '1733' => 'København V', '1734' => 'København V', '1735' => 'København V', '1736' => 'København V', '1737' => 'København V', '1738' => 'København V', '1739' => 'København V', '1749' => 'København V', '1750' => 'København V', '1751' => 'København V', '1752' => 'København V', '1753' => 'København V', '1754' => 'København V', '1755' => 'København V', '1756' => 'København V', '1757' => 'København V', '1758' => 'København V', '1759' => 'København V', '1760' => 'København V', '1761' => 'København V', '1762' => 'København V', '1763' => 'København V', '1764' => 'København V', '1765' => 'København V', '1766' => 'København V', '1770' => 'København V', '1771' => 'København V', '1772' => 'København V', '1773' => 'København V', '1774' => 'København V', '1775' => 'København V', '1777' => 'København V', '1799' => 'København V', '1800' => 'Frederiksberg C', '1801' => 'Frederiksberg C', '1802' => 'Frederiksberg C', '1803' => 'Frederiksberg C', '1804' => 'Frederiksberg C', '1805' => 'Frederiksberg C', '1806' => 'Frederiksberg C', '1807' => 'Frederiksberg C', '1808' => 'Frederiksberg C', '1809' => 'Frederiksberg C', '1810' => 'Frederiksberg C', '1811' => 'Frederiksberg C', '1812' => 'Frederiksberg C', '1813' => 'Frederiksberg C', '1814' => 'Frederiksberg C', '1815' => 'Frederiksberg C', '1816' => 'Frederiksberg C', '1817' => 'Frederiksberg C', '1818' => 'Frederiksberg C', '1819' => 'Frederiksberg C', '1820' => 'Frederiksberg C', '1822' => 'Frederiksberg C', '1823' => 'Frederiksberg C', '1824' => 'Frederiksberg C', '1825' => 'Frederiksberg C', '1826' => 'Frederiksberg C', '1827' => 'Frederiksberg C', '1828' => 'Frederiksberg C', '1829' => 'Frederiksberg C', '1850' => 'Frederiksberg C', '1851' => 'Frederiksberg C', '1852' => 'Frederiksberg C', '1853' => 'Frederiksberg C', '1854' => 'Frederiksberg C', '1855' => 'Frederiksberg C', '1856' => 'Frederiksberg C', '1857' => 'Frederiksberg C', '1860' => 'Frederiksberg C', '1861' => 'Frederiksberg C', '1862' => 'Frederiksberg C', '1863' => 'Frederiksberg C', '1864' => 'Frederiksberg C', '1865' => 'Frederiksberg C', '1866' => 'Frederiksberg C', '1867' => 'Frederiksberg C', '1868' => 'Frederiksberg C', '1870' => 'Frederiksberg C', '1871' => 'Frederiksberg C', '1872' => 'Frederiksberg C', '1873' => 'Frederiksberg C', '1874' => 'Frederiksberg C', '1875' => 'Frederiksberg C', '1876' => 'Frederiksberg C', '1877' => 'Frederiksberg C', '1878' => 'Frederiksberg C', '1879' => 'Frederiksberg C', '1900' => 'Frederiksberg C', '1901' => 'Frederiksberg C', '1902' => 'Frederiksberg C', '1903' => 'Frederiksberg C', '1904' => 'Frederiksberg C', '1905' => 'Frederiksberg C', '1906' => 'Frederiksberg C', '1908' => 'Frederiksberg C', '1909' => 'Frederiksberg C', '1910' => 'Frederiksberg C', '1911' => 'Frederiksberg C', '1912' => 'Frederiksberg C', '1913' => 'Frederiksberg C', '1914' => 'Frederiksberg C', '1915' => 'Frederiksberg C', '1916' => 'Frederiksberg C', '1917' => 'Frederiksberg C', '1920' => 'Frederiksberg C', '1921' => 'Frederiksberg C', '1922' => 'Frederiksberg C', '1923' => 'Frederiksberg C', '1924' => 'Frederiksberg C', '1925' => 'Frederiksberg C', '1926' => 'Frederiksberg C', '1927' => 'Frederiksberg C', '1928' => 'Frederiksberg C', '1950' => 'Frederiksberg C', '1951' => 'Frederiksberg C', '1952' => 'Frederiksberg C', '1953' => 'Frederiksberg C', '1954' => 'Frederiksberg C', '1955' => 'Frederiksberg C', '1956' => 'Frederiksberg C', '1957' => 'Frederiksberg C', '1958' => 'Frederiksberg C', '1959' => 'Frederiksberg C', '1960' => 'Frederiksberg C', '1961' => 'Frederiksberg C', '1962' => 'Frederiksberg C', '1963' => 'Frederiksberg C', '1964' => 'Frederiksberg C', '1965' => 'Frederiksberg C', '1966' => 'Frederiksberg C', '1967' => 'Frederiksberg C', '1970' => 'Frederiksberg C', '1971' => 'Frederiksberg C', '1972' => 'Frederiksberg C', '1973' => 'Frederiksberg C', '1974' => 'Frederiksberg C', '2000' => 'Frederiksberg', '2100' => 'København Ø', '2150' => 'Nordhavn', '2200' => 'København N', '2300' => 'København S', '2400' => 'København NV', '2450' => 'København SV', '2500' => 'Valby', '2600' => 'Glostrup', '2605' => 'Brøndby', '2610' => 'Rødovre', '2620' => 'Albertslund', '2625' => 'Vallensbæk', '2630' => 'Taastrup', '2635' => 'Ishøj', '2640' => 'Hedehusene', '2650' => 'Hvidovre', '2660' => 'Brøndby Strand', '2665' => 'Vallensbæk Strand', '2670' => 'Greve', '2680' => 'Solrød Strand', '2690' => 'Karlslunde', '2700' => 'Brønshøj', '2720' => 'Vanløse', '2730' => 'Herlev', '2740' => 'Skovlunde', '2750' => 'Ballerup', '2760' => 'Måløv', '2765' => 'Smørum', '2770' => 'Kastrup', '2791' => 'Dragør', '2800' => 'Kgs. Lyngby', '2820' => 'Gentofte', '2830' => 'Virum', '2840' => 'Holte', '2850' => 'Nærum', '2860' => 'Søborg', '2870' => 'Dyssegård', '2880' => 'Bagsværd', '2900' => 'Hellerup', '2920' => 'Charlottenlund', '2930' => 'Klampenborg', '2942' => 'Skodsborg', '2950' => 'Vedbæk', '2960' => 'Rungsted Kyst', '2970' => 'Hørsholm', '2980' => 'Kokkedal', '2990' => 'Nivå', '3000' => 'Helsingør', '3050' => 'Humlebæk', '3060' => 'Espergærde', '3070' => 'Snekkersten', '3080' => 'Tikøb', '3100' => 'Hornbæk', '3120' => 'Dronningmølle', '3140' => 'Ålsgårde', '3150' => 'Hellebæk', '3200' => 'Helsinge', '3210' => 'Vejby', '3220' => 'Tisvildeleje', '3230' => 'Græsted', '3250' => 'Gilleleje', '3300' => 'Frederiksværk', '3310' => 'Ølsted', '3320' => 'Skævinge', '3330' => 'Gørløse', '3360' => 'Liseleje', '3370' => 'Melby', '3390' => 'Hundested', '3400' => 'Hillerød', '3450' => 'Allerød', '3460' => 'Birkerød', '3480' => 'Fredensborg', '3490' => 'Kvistgård', '3500' => 'Værløse', '3520' => 'Farum', '3540' => 'Lynge', '3550' => 'Slangerup', '3600' => 'Frederikssund', '3630' => 'Jægerspris', '3650' => 'Ølstykke', '3660' => 'Stenløse', '3670' => 'Veksø Sjælland', '3700' => 'Rønne', '3720' => 'Aakirkeby', '3730' => 'Nexø', '3740' => 'Svaneke', '3751' => 'Østermarie', '3760' => 'Gudhjem', '3770' => 'Allinge', '3782' => 'Klemensker', '3790' => 'Hasle', '4000' => 'Roskilde', '4030' => 'Tune', '4040' => 'Jyllinge', '4050' => 'Skibby', '4060' => 'Kirke Såby', '4070' => 'Kirke Hyllinge', '4100' => 'Ringsted', '4130' => 'Viby Sjælland', '4140' => 'Borup', '4160' => 'Herlufmagle', '4171' => 'Glumsø', '4173' => 'Fjenneslev', '4174' => 'Jystrup Midtsj', '4180' => 'Sorø', '4190' => 'Munke Bjergby', '4200' => 'Slagelse', '4220' => 'Korsør', '4230' => 'Skælskør', '4241' => 'Vemmelev', '4242' => 'Boeslunde', '4243' => 'Rude', '4244' => 'Agersø', '4245' => 'Omø', '4250' => 'Fuglebjerg', '4261' => 'Dalmose', '4262' => 'Sandved', '4270' => 'Høng', '4281' => 'Gørlev', '4291' => 'Ruds Vedby', '4293' => 'Dianalund', '4295' => 'Stenlille', '4296' => 'Nyrup', '4300' => 'Holbæk', '4305' => 'Orø', '4320' => 'Lejre', '4330' => 'Hvalsø', '4340' => 'Tølløse', '4350' => 'Ugerløse', '4360' => 'Kirke Eskilstrup', '4370' => 'Store Merløse', '4390' => 'Vipperød', '4400' => 'Kalundborg', '4420' => 'Regstrup', '4440' => 'Mørkøv', '4450' => 'Jyderup', '4460' => 'Snertinge', '4470' => 'Svebølle', '4480' => 'Store Fuglede', '4490' => 'Jerslev Sjælland', '4500' => 'Nykøbing Sj', '4520' => 'Svinninge', '4532' => 'Gislinge', '4534' => 'Hørve', '4540' => 'Fårevejle', '4550' => 'Asnæs', '4560' => 'Vig', '4571' => 'Grevinge', '4572' => 'Nørre Asmindrup', '4573' => 'Højby', '4581' => 'Rørvig', '4583' => 'Sjællands Odde', '4591' => 'Føllenslev', '4592' => 'Sejerø', '4593' => 'Eskebjerg', '4600' => 'Køge', '4621' => 'Gadstrup', '4622' => 'Havdrup', '4623' => 'Lille Skensved', '4632' => 'Bjæverskov', '4640' => 'Faxe', '4652' => 'Hårlev', '4653' => 'Karise', '4654' => 'Faxe Ladeplads', '4660' => 'Store Heddinge', '4671' => 'Strøby', '4672' => 'Klippinge', '4673' => 'Rødvig Stevns', '4681' => 'Herfølge', '4682' => 'Tureby', '4683' => 'Rønnede', '4684' => 'Holmegaard', '4690' => 'Haslev', '4700' => 'Næstved', '4720' => 'Præstø', '4733' => 'Tappernøje', '4735' => 'Mern', '4736' => 'Karrebæksminde', '4750' => 'Lundby', '4760' => 'Vordingborg', '4771' => 'Kalvehave', '4772' => 'Langebæk', '4773' => 'Stensved', '4780' => 'Stege', '4791' => 'Borre', '4792' => 'Askeby', '4793' => 'Bogø By', '4800' => 'Nykøbing F', '4840' => 'Nørre Alslev', '4850' => 'Stubbekøbing', '4862' => 'Guldborg', '4863' => 'Eskilstrup', '4871' => 'Horbelev', '4872' => 'Idestrup', '4873' => 'Væggerløse', '4874' => 'Gedser', '4880' => 'Nysted', '4891' => 'Toreby L', '4892' => 'Kettinge', '4894' => 'Øster Ulslev', '4895' => 'Errindlev', '4900' => 'Nakskov', '4912' => 'Harpelunde', '4913' => 'Horslunde', '4920' => 'Søllested', '4930' => 'Maribo', '4941' => 'Bandholm', '4942' => 'Askø-Lilleø', '4943' => 'Torrig L', '4944' => 'Fejø', '4945' => 'Femø', '4951' => 'Nørreballe', '4952' => 'Stokkemarke', '4953' => 'Vesterborg', '4960' => 'Holeby', '4970' => 'Rødby', '4983' => 'Dannemare', '4990' => 'Sakskøbing', '5000' => 'Odense C', '5200' => 'Odense V', '5210' => 'Odense NV', '5220' => 'Odense SØ', '5230' => 'Odense M', '5240' => 'Odense NØ', '5250' => 'Odense SV', '5260' => 'Odense S', '5270' => 'Odense N', '5290' => 'Marslev', '5300' => 'Kerteminde', '5320' => 'Agedrup', '5330' => 'Munkebo', '5350' => 'Rynkeby', '5370' => 'Mesinge', '5380' => 'Dalby', '5390' => 'Martofte', '5400' => 'Bogense', '5450' => 'Otterup', '5462' => 'Morud', '5463' => 'Harndrup', '5464' => 'Brenderup Fyn', '5466' => 'Asperup', '5471' => 'Søndersø', '5474' => 'Veflinge', '5485' => 'Skamby', '5491' => 'Blommenslyst', '5492' => 'Vissenbjerg', '5500' => 'Middelfart', '5540' => 'Ullerslev', '5550' => 'Langeskov', '5560' => 'Aarup', '5580' => 'Nørre Aaby', '5591' => 'Gelsted', '5592' => 'Ejby', '5600' => 'Faaborg', '5601' => 'Lyø', '5602' => 'Avernakø', '5603' => 'Bjørnø', '5610' => 'Assens', '5620' => 'Glamsbjerg', '5631' => 'Ebberup', '5642' => 'Millinge', '5672' => 'Broby', '5683' => 'Haarby', '5690' => 'Tommerup', '5700' => 'Svendborg', '5750' => 'Ringe', '5762' => 'Vester Skerninge', '5771' => 'Stenstrup', '5772' => 'Kværndrup', '5792' => 'Årslev', '5800' => 'Nyborg', '5853' => 'Ørbæk', '5854' => 'Gislev', '5856' => 'Ryslinge', '5863' => 'Ferritslev Fyn', '5871' => 'Frørup', '5874' => 'Hesselager', '5881' => 'Skårup Fyn', '5882' => 'Vejstrup', '5883' => 'Oure', '5884' => 'Gudme', '5892' => 'Gudbjerg Sydfyn', '5900' => 'Rudkøbing', '5932' => 'Humble', '5935' => 'Bagenkop', '5943' => 'Strynø', '5953' => 'Tranekær', '5960' => 'Marstal', '5965' => 'Birkholm', '5970' => 'Ærøskøbing', '5985' => 'Søby Ærø', '6000' => 'Kolding', '6040' => 'Egtved', '6051' => 'Almind', '6052' => 'Viuf', '6064' => 'Jordrup', '6070' => 'Christiansfeld', '6091' => 'Bjert', '6092' => 'Sønder Stenderup', '6093' => 'Sjølund', '6094' => 'Hejls', '6100' => 'Haderslev', '6200' => 'Aabenraa', '6210' => 'Barsø', '6230' => 'Rødekro', '6240' => 'Løgumkloster', '6261' => 'Bredebro', '6270' => 'Tønder', '6280' => 'Højer', '6300' => 'Gråsten', '6310' => 'Broager', '6320' => 'Egernsund', '6330' => 'Padborg', '6340' => 'Kruså', '6360' => 'Tinglev', '6372' => 'Bylderup-Bov', '6392' => 'Bolderslev', '6400' => 'Sønderborg', '6430' => 'Nordborg', '6440' => 'Augustenborg', '6470' => 'Sydals', '6500' => 'Vojens', '6510' => 'Gram', '6520' => 'Toftlund', '6534' => 'Agerskov', '6535' => 'Branderup J', '6541' => 'Bevtoft', '6560' => 'Sommersted', '6580' => 'Vamdrup', '6600' => 'Vejen', '6621' => 'Gesten', '6622' => 'Bække', '6623' => 'Vorbasse', '6630' => 'Rødding', '6640' => 'Lunderskov', '6650' => 'Brørup', '6660' => 'Lintrup', '6670' => 'Holsted', '6682' => 'Hovborg', '6683' => 'Føvling', '6690' => 'Gørding', '6700' => 'Esbjerg', '6705' => 'Esbjerg Ø', '6710' => 'Esbjerg V', '6715' => 'Esbjerg N', '6720' => 'Fanø', '6731' => 'Tjæreborg', '6740' => 'Bramming', '6752' => 'Glejbjerg', '6753' => 'Agerbæk', '6760' => 'Ribe', '6771' => 'Gredstedbro', '6780' => 'Skærbæk', '6792' => 'Rømø', '6800' => 'Varde', '6818' => 'Årre', '6823' => 'Ansager', '6830' => 'Nørre Nebel', '6840' => 'Oksbøl', '6851' => 'Janderup Vestj', '6852' => 'Billum', '6853' => 'Vejers Strand', '6854' => 'Henne', '6855' => 'Outrup', '6857' => 'Blåvand', '6862' => 'Tistrup', '6870' => 'Ølgod', '6880' => 'Tarm', '6893' => 'Hemmet', '6900' => 'Skjern', '6920' => 'Videbæk', '6933' => 'Kibæk', '6940' => 'Lem St', '6950' => 'Ringkøbing', '6960' => 'Hvide Sande', '6971' => 'Spjald', '6973' => 'Ørnhøj', '6980' => 'Tim', '6990' => 'Ulfborg', '7000' => 'Fredericia', '7080' => 'Børkop', '7100' => 'Vejle', '7120' => 'Vejle Øst', '7130' => 'Juelsminde', '7140' => 'Stouby', '7150' => 'Barrit', '7160' => 'Tørring', '7171' => 'Uldum', '7173' => 'Vonge', '7182' => 'Bredsten', '7183' => 'Randbøl', '7184' => 'Vandel', '7190' => 'Billund', '7200' => 'Grindsted', '7250' => 'Hejnsvig', '7260' => 'Sønder Omme', '7270' => 'Stakroge', '7280' => 'Sønder Felding', '7300' => 'Jelling', '7321' => 'Gadbjerg', '7323' => 'Give', '7330' => 'Brande', '7361' => 'Ejstrupholm', '7362' => 'Hampen', '7400' => 'Herning', '7430' => 'Ikast', '7441' => 'Bording', '7442' => 'Engesvang', '7451' => 'Sunds', '7470' => 'Karup J', '7480' => 'Vildbjerg', '7490' => 'Aulum', '7500' => 'Holstebro', '7540' => 'Haderup', '7550' => 'Sørvad', '7560' => 'Hjerm', '7570' => 'Vemb', '7600' => 'Struer', '7620' => 'Lemvig', '7650' => 'Bøvlingbjerg', '7660' => 'Bækmarksbro', '7673' => 'Harboøre', '7680' => 'Thyborøn', '7700' => 'Thisted', '7730' => 'Hanstholm', '7741' => 'Frøstrup', '7742' => 'Vesløs', '7752' => 'Snedsted', '7755' => 'Bedsted Thy', '7760' => 'Hurup Thy', '7770' => 'Vestervig', '7790' => 'Thyholm', '7800' => 'Skive', '7830' => 'Vinderup', '7840' => 'Højslev', '7850' => 'Stoholm Jyll', '7860' => 'Spøttrup', '7870' => 'Roslev', '7884' => 'Fur', '7900' => 'Nykøbing M', '7950' => 'Erslev', '7960' => 'Karby', '7970' => 'Redsted M', '7980' => 'Vils', '7990' => 'Øster Assels', '8000' => 'Aarhus C', '8200' => 'Aarhus N', '8210' => 'Aarhus V', '8220' => 'Brabrand', '8230' => 'Åbyhøj', '8240' => 'Risskov', '8250' => 'Egå', '8260' => 'Viby J', '8270' => 'Højbjerg', '8300' => 'Odder', '8305' => 'Samsø', '8310' => 'Tranbjerg J', '8320' => 'Mårslet', '8330' => 'Beder', '8340' => 'Malling', '8350' => 'Hundslund', '8355' => 'Solbjerg', '8361' => 'Hasselager', '8362' => 'Hørning', '8370' => 'Hadsten', '8380' => 'Trige', '8381' => 'Tilst', '8382' => 'Hinnerup', '8400' => 'Ebeltoft', '8410' => 'Rønde', '8420' => 'Knebel', '8444' => 'Balle', '8450' => 'Hammel', '8462' => 'Harlev J', '8464' => 'Galten', '8471' => 'Sabro', '8472' => 'Sporup', '8500' => 'Grenaa', '8520' => 'Lystrup', '8530' => 'Hjortshøj', '8541' => 'Skødstrup', '8543' => 'Hornslet', '8544' => 'Mørke', '8550' => 'Ryomgård', '8560' => 'Kolind', '8570' => 'Trustrup', '8581' => 'Nimtofte', '8585' => 'Glesborg', '8586' => 'Ørum Djurs', '8592' => 'Anholt', '8600' => 'Silkeborg', '8620' => 'Kjellerup', '8632' => 'Lemming', '8641' => 'Sorring', '8643' => 'Ans By', '8653' => 'Them', '8654' => 'Bryrup', '8660' => 'Skanderborg', '8670' => 'Låsby', '8680' => 'Ry', '8700' => 'Horsens', '8721' => 'Daugård', '8722' => 'Hedensted', '8723' => 'Løsning', '8732' => 'Hovedgård', '8740' => 'Brædstrup', '8751' => 'Gedved', '8752' => 'Østbirk', '8762' => 'Flemming', '8763' => 'Rask Mølle', '8765' => 'Klovborg', '8766' => 'Nørre Snede', '8781' => 'Stenderup', '8783' => 'Hornsyld', '8789' => 'Endelave', '8799' => 'Tunø', '8800' => 'Viborg', '8830' => 'Tjele', '8831' => 'Løgstrup', '8832' => 'Skals', '8840' => 'Rødkærsbro', '8850' => 'Bjerringbro', '8860' => 'Ulstrup', '8870' => 'Langå', '8881' => 'Thorsø', '8882' => 'Fårvang', '8883' => 'Gjern', '8900' => 'Randers C', '8920' => 'Randers NV', '8930' => 'Randers NØ', '8940' => 'Randers SV', '8950' => 'Ørsted', '8960' => 'Randers SØ', '8961' => 'Allingåbro', '8963' => 'Auning', '8970' => 'Havndal', '8981' => 'Spentrup', '8983' => 'Gjerlev J', '8990' => 'Fårup', '9000' => 'Aalborg', '9200' => 'Aalborg SV', '9210' => 'Aalborg SØ', '9220' => 'Aalborg Øst', '9230' => 'Svenstrup J', '9240' => 'Nibe', '9260' => 'Gistrup', '9270' => 'Klarup', '9280' => 'Storvorde', '9293' => 'Kongerslev', '9300' => 'Sæby', '9310' => 'Vodskov', '9320' => 'Hjallerup', '9330' => 'Dronninglund', '9340' => 'Asaa', '9352' => 'Dybvad', '9362' => 'Gandrup', '9370' => 'Hals', '9380' => 'Vestbjerg', '9381' => 'Sulsted', '9382' => 'Tylstrup', '9400' => 'Nørresundby', '9430' => 'Vadum', '9440' => 'Aabybro', '9460' => 'Brovst', '9480' => 'Løkken', '9490' => 'Pandrup', '9492' => 'Blokhus', '9493' => 'Saltum', '9500' => 'Hobro', '9510' => 'Arden', '9520' => 'Skørping', '9530' => 'Støvring', '9541' => 'Suldrup', '9550' => 'Mariager', '9560' => 'Hadsund', '9574' => 'Bælum', '9575' => 'Terndrup', '9600' => 'Aars', '9610' => 'Nørager', '9620' => 'Aalestrup', '9631' => 'Gedsted', '9632' => 'Møldrup', '9640' => 'Farsø', '9670' => 'Løgstør', '9681' => 'Ranum', '9690' => 'Fjerritslev', '9700' => 'Brønderslev', '9740' => 'Jerslev J', '9750' => 'Østervrå', '9760' => 'Vrå', '9800' => 'Hjørring', '9830' => 'Tårs', '9850' => 'Hirtshals', '9870' => 'Sindal', '9881' => 'Bindslev', '9900' => 'Frederikshavn', '9940' => 'Læsø', '9970' => 'Strandby', '9981' => 'Jerup', '9982' => 'Ålbæk', '9990' => 'Skagen', );
	}
}
