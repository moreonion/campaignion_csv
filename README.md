[![Build Status](https://travis-ci.org/moreonion/campaignion_csv.svg?branch=7.x-1.x)](https://travis-ci.org/moreonion/campaignion_csv) [![codecov](https://codecov.io/gh/moreonion/campaignion_csv/branch/7.x-1.x/graph/badge.svg)](https://codecov.io/gh/moreonion/campaignion_csv)

# Campaignion CSV exports

This module manages pre-generated CSV exports in a specially designated folder.


## Installation

The module can be installed as usual for drupal modules. Dependencies are:

* [campaignion](https://www.drupal.org/project/campaignion)
* [little_helpers](https://www.drupal.org/project/little_helpers)
* [redhen](https://www.drupal.org/project/redhen)
* [variable](https://www.drupal.org/project/variable)


## Configuration

The module uses the following configuration variables:

| variable | purpose |
|---|---|
| `campaignion_csv_path` | CSV export directory: Path to the managed export files, relative to the `DRUPAL_ROOT` |
| `campaignion_csv_time_limit` | Soft time limit for cron-job: The cron job doesn’t start new file exports after this time limit passes. |
| `campaignion_csv_memory_limit` | Soft limit for leaked memory: Some of the exports might leak memory. No new exports are started once this amount of memory has been leaked. |


## Usage

By default the cron-job is disabled for normal cron runs. It can be invoked directly by using:

```bash
drush cron-run campaignion_csv_cron
```

Each cron-run generates files until the time/memory limits are hit or all files are in the desired state.
