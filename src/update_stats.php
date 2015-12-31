<?php

require dirname(__FILE__) . '/../vendor/autoload.php';
include dirname(__FILE__) . '/../config.php';

$statsd = new League\StatsD\Client();

$statsd->configure(array(
	'host'      => isset($statsd_host) ? $statsd_host : '127.0.0.1',
	'port'      => isset($statsd_port) ? $statsd_port : 8125,
	'namespace' => isset($statsd_namespace) ? $statsd_namespace : null
));

$baseUrl = "https://insights-api.newrelic.com/v1/accounts/" . $nr_account_id . "/query?nrql=";

foreach ($queries as $query) {
	$fullUrl = $baseUrl . $query['encodedQuery'];
	$ch = curl_init($fullUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array ('X-Query-Key: ' . $query['query_api_key']));
	$data = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$fetchedData = null;

	if ($status == 200) {
		$dataAr = json_decode($data, true);
		$indexInResult = isset($query['indexInResult']) ? $query['indexInResult'] : 0;
		if (isset($dataAr['results'][$indexInResult][$query['keyToFetch']])) {
			$fetchedData = $dataAr['results'][$indexInResult][$query['keyToFetch']];
		}
	}

	if ($fetchedData !== null && $fetchedData >= 0) {
		switch ($query['statsdMetricType']) {
			case 'gauge':
				$statsd->gauge($query['statsdKey'], $fetchedData);
				echo "Updating metric type 'gauge', key: " . $query['statsdKey'] . ", value: " . $fetchedData . PHP_EOL;
				break;
			case 'set':
				$statsd->set($query['statsdKey'], $fetchedData);
				echo "Updating metric type 'set', key: " . $query['statsdKey'] . ", value: " . $fetchedData . PHP_EOL;
				break;
			case 'increment':
				$statsd->increment($query['statsdKey'], $fetchedData);
				echo "Updating metric type 'increment', key: " . $query['statsdKey'] . ", value: " . $fetchedData . PHP_EOL;
				break;
			case 'decrement':
				$statsd->decrement($query['statsdKey'], $fetchedData);
				echo "Updating metric type 'decrement', key: " . $query['statsdKey'] . ", value: " . $fetchedData . PHP_EOL;
				break;
			default:
				echo "Unknown or missing metric type '" . $query['statsdMetricType'] . "'";
				exit(1);
		}
	}
}
