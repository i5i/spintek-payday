# Spintek Payday Microservice

## Summary 

Microservice will return a json array containing payday and notification dates
in response to a GET request routed to a integer value of year between 2000 and 2999
for example:
```
GET localhost:port/2024 
```
Default payday is adjusted to avoid holidays and weekends as are the suggested notification dates.
Example of output:
```

    {
        0...11:
        {
            notificationDate : "2024-12-07",
            paydayDate : "2024-12-10",
        }
    }

```

## Setup

You can configure the defualt date in the config file:

>/config/services.yaml

The value is:
>parameters: app.payday.day_of_month: 10

### Testing
Requires php 8.3, composer (tested with version 2.7) and symfony CLI 

install vendors with composer 

```composer install```

then start symfony CLI test server (found at https://symfony.com/download)

```symfony server:start```

run integrated test with phpunit

```php bin/phpunit```

To see what things look like in prod disable the debugger in .env 

>APP_ENV=prod

(and of course don't use the example .env on prod)
