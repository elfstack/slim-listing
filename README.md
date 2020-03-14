# Slim Listing

[![Latest Stable Version](https://poser.pugx.org/elfstack/slim-listing/v/stable)](https://packagist.org/packages/elfstack/slim-listing)
[![Total Downloads](https://poser.pugx.org/elfstack/slim-listing/downloads)](https://packagist.org/packages/elfstack/slim-listing)
[![Latest Unstable Version](https://poser.pugx.org/elfstack/slim-listing/v/unstable)](https://packagist.org/packages/elfstack/slim-listing)
[![License](https://poser.pugx.org/elfstack/slim-listing/license)](https://packagist.org/packages/elfstack/slim-listing)

This package provides functionality to build a general listing API in a quick and convenient fashion.
Which is heavily inspired by [BRACKETS-by-TRIAD/admin-listing](https://github.com/BRACKETS-by-TRIAD/admin-listing).
It handles request like pagination, sorting, filtering and searching.

## Installation
### Composer
```shell script
composer require elfstack/slim-listing
```
### Slim (Recommended)
```php
$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../database.sqlite'
        ]
    ],
];

$app = new \Slim\App($config);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->bootEloquent();
$capsule->setAsGlobal();

$container['db'] = function ($container) use ($capsule){
    return $capsule;
};

```

## Usage

### Full Example
```php
function controllerMethod(Request $request, Response $response)
{
    return Listing::create(new Model())
                   ->attachSearching(['field1', 'field2'])
                   ->attachSorting(['field1', 'field2'])
                   ->attachFiltering(['field1', 'field2'])
                   ->modifyQuery(function ($query) {
                        $query->with('model2');
                   })
                   ->get($request, $response);
}
```

### Create an Instance

```php
Listing::create(Model::class);
Listing::create(new Model());
```

### Retrieving Result
#### Get collection only
```php
get($request);
```
#### Get response
```php
get($request, $response);
```

## Query String Pattern
Patterns below can be used together

* Ordering: `orderBy=<column>&direction=<desc|asc>`
* Filtering: `filter=<column1:val1,val2;column2:val1,val2;column3:val1,val2>`
* Pagination: `perPage=<perPage>&page=<page>`
* Search: `keyword=<keyword>`

## Response
```
{
    "current_page":1,
    "data":[],
    "first_page_url":"/?page=1",
    "from":1,
    "last_page":1,
    "last_page_url":"/?page=1",
    "next_page_url":null,
    "path":"/",
    "per_page":10,
    "prev_page_url":null,
    "to":3,
    "total":3
}
```
**NOTE**: The url is not implemented and will remove in the future.
## License

MIT

