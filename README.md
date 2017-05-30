# Health Check Bundle

A bundle that provides a simple `/healthcheck` route

[![Latest Stable Version](https://poser.pugx.org/ekreative/health-check-bundle/v/stable.png)](https://packagist.org/packages/ekreative/health-check-bundle)
[![License](https://poser.pugx.org/ekreative/health-check-bundle/license.png)](https://packagist.org/packages/ekreative/health-check-bundle)
[![Build Status](https://travis-ci.org/ekreative/health-check-bundle.svg?branch=master)](https://travis-ci.org/ekreative/health-check-bundle)

## Install

### Composer

```bash
composer require ekreative/health-check-bundle
```

### AppKernel

Include the bundle in your AppKernel

```php
public function registerBundles()
{
    $bundles = [
        ...
        new Ekreative\HealthCheckBundle\EkreativeHealthCheckBundle(),
```

### Routing

```yaml
ekreatve_health_check:
    resource: "@EkreativeHealthCheckBundle/Controller/"
    type:     annotation
    prefix:   /
```

## Configuration

By default healthcheck will check that your default doctrine connection is working.

### Doctrine

To check more than one doctrine connection you should add the configuration, listing
the names of the connections

```yaml
ekreative_health_check:
    doctrine:
        - 'default'
        - 'alternative'
```

Its possible to disable the doctrine check

```yaml
ekreative_health_check:
    doctrine_enabled: false
```

### Redis

The bundle can also check that redis connections are working. You should add a list of
service names to check

```yaml
ekreative_health_check:
    redis:
        - 'redis'
```
