# A better Redis queue driver for Laravel

[![Build Status](https://travis-ci.org/halaei/hredis.svg)](https://travis-ci.org/halaei/hredis)
[![Latest Stable Version](https://poser.pugx.org/halaei/hredis/v/stable)](https://packagist.org/packages/halaei/hredis)
[![Total Downloads](https://poser.pugx.org/halaei/hredis/downloads)](https://packagist.org/packages/halaei/hredis)
[![Latest Unstable Version](https://poser.pugx.org/halaei/hredis/v/unstable)](https://packagist.org/packages/halaei/hredis)
[![License](https://poser.pugx.org/halaei/hredis/license)](https://packagist.org/packages/halaei/hredis)

## Changes (vs the current Laravel Redis queue driver)
- Implement `migrateExpiredJobs()` using `eval` instead of watch-multi-exec (check and set) transactions to prevent issue #12653 of laravel/framework.
- Increment `attempts` counter when reserving job, instead of when releasing it, so that fatal errors be considered as tries.
- Migrate expired delayed jobs but not expired release ones when expire is null. Issue #12595 laravel/framework.
- A few transaction guards for the times we moving jobs between main/reserved and delayed queues, so that the jobs are not lost in Redis server in the case of network/client failures.
