<?php
function getAvailabilityInfo($mms) {
  $ch = curl_init();
  $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/{' . $mms . '}';
  $templateParamNames = array('{' . $mms . '}');
  $templateParamValues = array(urlencode($mms));
  $url = str_replace($templateParamNames, $templateParamValues, $url);
  $queryParams = '?' . urlencode('expand') . '=' . urlencode('p_avail') . '&' . urlencode('apikey') . '=' . urlencode('ALMAAPIKEY');
  curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  $response = curl_exec($ch);
  curl_close($ch);
  return($response);
}
function listAvailability($availInfo) {
	if ($availInfo) {
	$results = $availInfo->xpath("//datafield[@tag='AVA']"); 
		$availability = '';
		foreach ($results as $result) {
			$library = $result->xpath(".//subfield[@code='b']");
			$location = $result->xpath(".//subfield[@code='c']");
			$call_no = $result->xpath(".//subfield[@code='d']");
			$avail= $result->xpath(".//subfield[@code='e']");
			$holdings = $location[0] . ': ' . $call_no[0];		
			$availability .= '<input type="radio" name="holdings" id="' . $location[0] .  '" value="' . $holdings . '">';
			$availability .= '<label for="' . $location[0] . '"> ' . $location[0] . '</label>';
			$availability .= '<br />';
		}
	}
	return $availability;
}
?>