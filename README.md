# New Relic Insights data to StatsD

You can use this small script to read any data from [New Relic Insights](https://newrelic.com/insights) API and pass it to StatsD.

## Installation

Clone this repository for example to dir ```/var/www/newrelic-insights-to-statsd```

Get composer ```curl -sS https://getcomposer.org/installer | php``` and run ```php composer.phar install``` to get StatsD library installed.

## Requirements

This script requires PHP >5.3.3 and cURL extension.

Your New Relic account ID can be found from URL when you login to New Relic.

Query API key can be created on Insights > Administrator > Manage data > API Keys

## Example configuration

Good starting point is to copy ```config.php.example``` to ```config.php```

```php

$statsd_host = '127.0.0.1';
$statsd_port = 8125;
$statsd_namespace = '';
$nr_account_id = ''; // set your New Relic account ID here

$queries = array(
	array (
		'query_api_key'     => "YOUR_QUERY_API_KEY", // New Relic query API key
		'encodedQuery'      => "SELECT%20uniqueCount%28session%29%20FROM%20PageView%20since%204%20minute%20ago", // encoded query string
		'keyToFetch'        => 'uniqueCount', // which key from the results we want to fetch
		'indexInResult'     => 0, // API can return multiple results - which one to fetch. defaults to 0 
		'statsdKey'         => 'web.concurrent_users', // which statsd key to store result into
		'statsdMetricType'  => 'gauge', // statsd metric type - gauge, increment, decrement or set
	),
	// and next query comes here...
);
```

This example configuration reads unique sessions during last 4 minutes and stores is to ```web.concurrent_users``` StatsD key as gauge metric.

## Usage

Run script ```php src/update_stats.php``` and it will update stats. Script returns exit code 1 on error.

It's probably good idea to place script for example to cron if you want to gather data regularly.
