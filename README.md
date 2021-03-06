Facebook Query Builder
======================

[![Build Status](http://img.shields.io/travis/SammyK/FacebookQueryBuilder.svg)](https://travis-ci.org/SammyK/FacebookQueryBuilder)
[![Latest Stable Version](http://img.shields.io/packagist/v/sammyk/facebook-query-builder.svg)](https://packagist.org/packages/sammyk/facebook-query-builder)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE)


An elegant and efficient way to interface with Facebook's [Graph API](https://developers.facebook.com/docs/graph-api) using the latest [Facebook PHP SDK v4](https://github.com/facebook/facebook-php-sdk-v4). It's as easy as:

```php
$user = $fqb->object('me')->get();
```

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Method Reference](#method-reference)
- [Request Objects](#request-objects)
- [Response Objects](#response-objects)
- [Testing](#testing)
- [TODO](#todo)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


Installation
============

Facebook Query Builder is installed using [Composer](https://getcomposer.org/). Add the Facebook Query Builder package to your `composer.json` file.

```json
{
    "require": {
        "sammyk/facebook-query-builder": "1.0.*"
    }
}
```

Or via the command line in the root of your project installation.

```bash
$ composer require "sammyk/facebook-query-builder:1.0.*"
```

> **Note:** The new "facebook/php-sdk-v4" was just released on **April 30, 2014** and there is a long laundry list of refactoring to be done. This package pulls in "dev-master" of the SDK to make sure all the latest refactoring and bug fixes are pulled in. Once a solid, stable version of the SDK is tagged, we'll use that one. :)


Usage
=====

After [creating an app in Facebook](https://developers.facebook.com/apps), you'll need to provide the app ID and secret. You'll also need to [obtain an access token](https://developers.facebook.com/docs/facebook-login/access-tokens/) and provide that as well.

```php
use SammyK\FacebookQueryBuilder\FQB;

FQB::setAppCredentials('your_app_id', 'your_app_secret');

FQB::setAccessToken('access_token');

$fqb = new FQB();
```


Examples
========

## Getting a single object from Graph

Get the logged in user's profile.

```php
$user = $fqb->object('me')->fields('id','email')->get();
```

Get info from a Facebook page.

```php
$page = $fqb->object('facebook_page_id')->fields('id','name','about')->get();
```


## Nested requests

Facebook Query Builder supports [nested requests](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion) so you can get a lot more data with just one call to Graph.

> **Note:** Facebook calls endpoints on the Graph API "edges". This package adopts the same nomenclature.

The following example will get the logged in user's name & first 5 photos they are tagged in with just one call to Graph.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);
$user = $fqb->object('me')->fields('name', $photos)->get();
```

And edges can have other edges embedded in them to allow for infinite deepness. This allows you to do fairly complex calls to Graph while maintaining very readable code.

The following example will get a user's name, and first 10 photos they are tagged in. For each photo get the first 2 comments and all the likes.

```php
$likes = $fqb->edge('likes');
$comments = $fqb->edge('comments')->fields('message')->limit(2);
$photos = $fqb->edge('photos')->fields('id', 'source', $comments, $likes)->limit(10);

$user = $fqb->object('user_id')->fields('name', $photos)->get();
```


Method Reference
================


## object(<string> "graph_edge")

Returns a new instance of the `FQB` factory. Any valid Graph edge can be passed to `object()`.


## get()

Performs a `GET` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Get the logged in user's profile
$user = $fqb->object('me')->get();

// Get a list of test users for this app
$test_users = $fqb->object('my_app_id/accounts/test-users')->get();
```


## post()

Sends a `POST` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Update a page's profile
$new_about_data = ['about' => 'This is the new about section!'];

$response = $fqb->object('page_id')->with($new_data)->post();


// Like a photo
$response = $fqb->object('photo_id/likes')->post();


// Post a status update for the logged in user
$data = ['message' => 'My witty status update.'];

$response = $fqb->object('me/feed')->with($data)->post();
$status_update_id = $response['id'];


// Post a comment to a status update
$comment = ['message' => 'My witty comment on your status update.'];

$response = $fqb->object('status_update_id/comments')->with($comment)->post();
```


## delete()

Sends a `DELETE` request to Graph and returns the response in the form of a collection. Will throw an `FacebookQueryBuilderException` if something went wrong while trying to communicate with Graph.

```php
// Delete a comment
$response = $fqb->object('comment_id')->delete();

// Unlike a photo
$response = $fqb->object('photo_id/likes')->delete();
```


## edge(<string> "edge_name")

Returns an `Edge` value object to be passed to the `fields()` method.


## fields(<array|string> "list of fields or edges")

Set the fields and edges for this `Edge`. The fields and edges can be passed as an array or list of arguments.

```php
$edge_one = $fqb->edge('my_edge')->fields('my_field', 'my_other_field');
$edge_two = $fqb->edge('my_edge')->fields(['field_one', 'field_two']);

$obj = $fqb->object('some_object')->fields('some_field', $edge_one, $edge_two)->get();
```


## limit(<int> "number of results to return")

Limit the number of results returned from Graph.

```php
$edge = $fqb->edge('some_list_edge')->limit(7);
```


## with(<array> "data to post or update")

Used only in conjunction with the `post()` method. The array should be an associative array. The key should be the name of the field as defined by Facebook.

```php
// Post a new comment to a photo
$comment_data = ['message' => 'My new comment!'];

$response = $fqb->object('photo_id')->with($comment_data)->post('comments');

// Update an existing comment
$comment_data = ['message' => 'My updated comment.'];

$response = $fqb->object('comment_id')->with($comment_data)->post();
```


Request Objects
===============

Requests sent to Graph are represented by 2 value objects, `RootEdge` & `Edge`. Each object represents a segment of the URL that will eventually be compiled, formatted as a string, and sent to Graph with either the `get()` or `post()` method.


## RootEdge

For debugging, you can access `RootEdge` as a string to get the URL that will be sent to Graph.

```php
$root_edge = $fqb->object('me')->fields('id', 'email');

echo $root_edge;
```

The above example will output:

    /me?fields=id,email


## Edge

An `Edge` has the same properties as a `RootEdge` but it will be formatted using [nested-request syntax](https://developers.facebook.com/docs/graph-api/using-graph-api/v2.0#fieldexpansion) when it is converted to a string.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);

echo $photos;
```

The above example will output:

    photos.limit(5).fields(id,source)

`Edge`'s can be embedded into other `Edge`'s.

```php
$photos = $fqb->edge('photos')->fields('id', 'source')->limit(5);
$root_edge = $fqb->object('me')->fields('email', $photos);

echo $root_edge;
```

The above example will output:

    /me?fields=email,photos.limit(5).fields(id,source)


Response Objects
================

All responses from Graph are returned as a collection object that has many useful methods for playing with the response data.

```php
$user = $fqb->object('me')->fields('email', 'photos')->get();

// Access properties like an array
$email = $user['email'];

// Get data as array
$user_array = $user->toArray();

// Get data as JSON string
$user_json = $user->toJson();

// Iterate through the values
foreach ($user['photos'] as $photo) {
    // . . .
}

// Morph the data with a closure
$user['photos'].each(function ($value) {
    $value->new_height = $value->height + 22;
});
```

Check out the [Collection class](https://github.com/SammyK/FacebookQueryBuilder/blob/master/src/Collection.php) for the full list of methods.


## GraphObject

The `GraphObject` collection represents any set of data Graph would consider an "object". This could be a user, page, photo, etc. When you request an object by ID from Graph, the response will be returned as a `GraphObject` collection.

```php
$my_graph_object = $fqb->object('object_id')->get();
```

`GraphObject`'s can also contain `GraphCollection`'s if you request an edge in the fields list.

```php
$my_graph_object = $fqb->object('object_id')->fields('id','likes')->get();

$my_graph_collection = $my_graph_object['likes'];
```


## GraphCollection

The `GraphCollection` collection is a collection of `GraphObject`'s.

```php
$my_graph_collection = $fqb->object('me/statuses')->get();
```


## GraphError

If Graph returns an error, the response will be cast as a `GraphError` collection and a `FacebookQueryBuilderException` will be thrown.

```php
try
{
    $statuses = $fqb->object('me/statuses')->limit(10)->get();
}
catch (FacebookQueryBuilderException $e)
{
    $graph_error = $e->getResponse();

    echo 'Oops! Graph said: ' . $graph_error['message'];
}
```


Testing
=======

Just run `phpunit` from the root directory of this project.

``` bash
$ phpunit
```


TODO
====

Future developments:

1. Batch requests

```php
$res = $fqb->batch(function($fqb) {
    $fqb->object('user_id')->get();
    $fqb->object('page_id')->fields('name', 'about')->get();
});
```

2. Pagination on `GraphCollection` objects


Contributing
============

Please see [CONTRIBUTING](https://github.com/SammyK/FacebookQueryBuilder/blob/master/CONTRIBUTING.md) for details.


Credits
=======

- [Sammy Kaye Powers](https://github.com/SammyK)
- [All Contributors](https://github.com/SammyK/FacebookQueryBuilder/contributors)


License
=======

The MIT License (MIT). Please see [License File](https://github.com/SammyK/FacebookQueryBuilder/blob/master/LICENSE) for more information.
