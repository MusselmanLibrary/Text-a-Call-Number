<?php
declare(strict_types=1);

/**
 * Alma client (minimal, curl-based) for looking up bib / availability.
 * You can swap to Guzzle if preferred, but curl keeps dependencies light.
 */

function alma_get_bib_xml(string $mmsId, string $apiKey, string $apiBase): string {
    $url = rtrim($apiBase, '/') . '/almaws/v1/bibs/' . rawurlencode($mmsId) . '?apikey=' . rawurlencode($apiKey);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Accept: application/xml']
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Alma request failed: ' . $err);
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code < 200 || $code >= 300) {
        throw new RuntimeException('Alma request HTTP ' . $code . ' for ' . $url);
    }
    return $resp;
}

/**
 * Very basic extraction of a primary call number from the bib XML.
 * Adjust the XPath as needed for your institution’s cataloging practice.
 */
function alma_extract_call_number_from_bib_xml(string $xml): ?string {
    libxml_use_internal_errors(true);
    $doc = simplexml_load_string($xml);
    if ($doc === false) {
        return null;
    }
    $doc->registerXPathNamespace('bib', 'http://www.exlibrisgroup.com/xsd/jaguar/bib');
    // Try holdings > items > call_number if present
    $paths = [
        '//bib:record/bib:metadata/bib:collection/bib:record/bib:datafield[@tag="090"]/bib:subfield[@code="a"]',
        '//bib:record/bib:metadata/bib:collection/bib:record/bib:datafield[@tag="050"]/bib:subfield[@code="a"]',
        '//bib:record/bib:metadata/bib:collection/bib:record/bib:datafield[@tag="099"]/bib:subfield[@code="a"]',
    ];
    foreach ($paths as $xp) {
        $nodes = $doc->xpath($xp);
        if ($nodes && isset($nodes[0])) {
            $val = trim((string)$nodes[0]);
            if ($val !== '') return $val;
        }
    }
    return null;
}
