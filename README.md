# Meetup API
This a very simple, one-file, PHP client for accessing most of the [Meetup API](http://www.meetup.com/meetup_api/).  Some parameters are included behind the scenes so you don't have to using array_merge when the parameters have fixed values like signed or response_type depending on the nature of the request.

#Exceptions
Exceptions are thrown and can be caught when there's any errors interacting with the API, they are standard exceptions.

```php
try
{
   $meetup = new Meetup('<api key>');
   $meetup->getEvents();
}
catch(Exception $e)
{
    echo $e->getMessage();
}
```

#Hardcoded parameters
Underneath there's parameters (depending on the request being made) that get injected using array_merge because these values aren't variable they're fixed.  This way you don't have to know about them or worry about implementing them and if they're duplicated by you it won't matter.  You can just focus on including the core information and using the stub methods to handle the heavy lifting.

## Quick Start

* Get your [API key](http://www.meetup.com/meetup_api/key/).
* Require the library, create a Meetup object and set your key:

```php
require 'meetup.php';
$meetup = new Meetup(array(
	'key' => '<api key>'
));

$response = $meetup->getEvents(); //somewhat restricted
```

* Get your [Consumer details](https://secure.meetup.com/meetup_api/oauth_consumers/).
* Require the library, create a Meetup object and set your consumer details and gain access:

```php
if( !isset($_GET['code']) )
{
    //authorize and go back to URI w/ code
    $meetup = new Meetup();
    $meetup->authorize(
		'client_id'     => '<consumer key>',
		'redirect_uri'  => '<redirect uri>'    	
    );
}
else
{
    //assuming we came back here...
    $meetup = new Meetup(
        array(
    		"client_id"     => '<consumer key>',
    		"client_secret" => '<consumer secret>',
    		"redirect_uri"  => '<redirect uri>',
    		"code"          => $_GET['code'] //passed back to us from meetup
    	)
    );
	
    //get an access token
    $response = $meetup->access();
	        	                
    //now we can re-use this object for several requests using our access
    //token
    $meetup = new Meetup(
    	array(
    		"access_token"  => $response->access_token,
    	)
     );

     //store details for later in case we need to do requests elsewhere
     //or refresh token
     $_SESSION['access_token'] = $response->access_token;
     $_SESSION['refresh_token'] = $response->refresh_token;
     $_SESSION['expires'] = time() + intval($response->expires_in); //use if >= intval($_SESSION['expires']) to check
     
     //get all groups for this member
     $response = $meetup->getGroups('member_id' => '<member id>');
     
     //get all events for this member
     $response = $meetup->getEvents('member_id' => '<member id>');
}
```

* Retrieve some events:

```php
$response = $meetup->getEvents(array(
	'group_urlname' => 'YOUR-MEETUP-GROUP'
));

// total number of items matching the get request
$total_count = $response->meta->total_count;

foreach ($response->results as $event) {
	echo $event->name . ' at ' . date('Y-m-d H:i', $event->time / 1000) . PHP_EOL;
}
```
Many of the get requests will match more entries than the API will return in one request. A convenience method has been provided to return the next page of entries after you have performed a successful get request:

$response = $meetup->getNext($response);

$events = $response->results;
...

## Constructing the client
The class constructors takes one optional argument. This `(array)` will be stored in the object and used as default parameters for any request you make.

I would suggest passing the `key` or `consumer details` when you construct the client, but you could do just `$meetup = new Meetup;` and then pass parameters in every request you make.  These requests are somewhat restricted on the information passed back, you have to use OATH 2 for full access otherwise you may not get back some information.

Using OATH 2 there's additional steps required to get an access token and pass it on subsequent requests.  Your access token is only good for 1 hour and you'll have to refresh it if you plan on making subsequent calls to the service after that.

## Doing GET requests
You can call any [Meetup API GET-method](http://www.meetup.com/meetup_api/docs/) using `get()`.  There's several stub functions already for the more common ones and new ones will be added.

### Arguments
The method get() takes two arguments, of which the second one is optional:

1. `(string)` Meetup API method (e.g. `/2/events`)
2. `(array)` Meetup API method paramaters (e.g. `array('group_urlname' => 'your-meetup-group')`)

### Path parameters
If the Meetup API method you need requires paramaters in it's path (like `/:urlname/boards`) you can pass the API method with the parameters already in their place (e.g. `/your-meetup-group/boards`) or provide the parameters in the second argument.  (e.g. `array('urlname' => 'your-meetup-group')`). I would suggest using the latter method.

### Response
If an error occures, the client will throw an Exception giving usefull information about what exactly went wrong.

If all is OK, it will return the JSON decoded response. This will be an `(object)` for version 2 methods and an `(array)` as far as I know.

## Available short-hands
I've added just a few short-hand methods. If you check out the code you'll see they're all one-liners that simply call the general `get()` method and the version 2 ones filter out the `results` variable you need from the API response.

Feel free to fork the code and add more!

|Client method        |API method                         |
|---------------------|-----------------------------------|
| getEvents           | /2/events                         |
| getGroups           | /2/groups                         |
| getMembers          | /2/members                        |
| getPhotos           | /2/photos                         |
| getDiscussionBoards | /:urlname/boards                  |
| getDiscussions      | /:urlname/boards/:bid/discussions |


## Roadmap
* Implement `POST` and `DELETE` methods.
* Add more short-hands.
* Have some meetups...
* Modify/post using OATH 2 and write not just read

## Alternatives
Before starting this client, I checked out the following [existing clients](http://www.meetup.com/meetup_api/clients/): 

* [wizonesolutions/meetup_api](https://github.com/wizonesolutions/meetup_api): Huge library, hasn't been updated for 3 years.
* [blobaugh](https://github.com/blobaugh/Meetup-API-client-for-PHP): Huge library, documentation quick start doesn't get you started.

This is a more simplified library for access and interactions!
## License

<pre>
Copyright 2013 Fokke Zandbergen

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
</pre>
