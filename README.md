# Usora

## How To Install

1. composer require railroad/usora

2. In your auth.php file, set your web guard to use usora (or whichever guard you wish to use):

```php
'guards' => [
    'web' => [
        'driver' => 'usora',
        'provider' => 'usora',
    ],
],
```

3. In your auth.php file, set add the usora provider:

```php
'providers' => [
    'usora' => [
        'driver' => 'usora',
    ],
],
```

4. In your auth.php file, set the password reset configuration:

```php
'passwords' => [
    'users' => [
        'provider' => 'usora',
        'table' => 'my_database.usora_password_resets',
        'expire' => 60,
    ],
],
``` 

4. In your auth.php file, make sure the default guard is set to the web guard (or whichever guard you configured to use usora) and your default password configuration key is set:

```php
'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],
```

Usora is a user management system including auth, user settings, user information, and single sign on.

- [Usora](#usora)
  * [Single Sign On](#single-sign-on)
    + [How it Works](#how-it-works)
  * [API Reference](#api-reference)
    + [Get Index of Users](#get-index-of-users)
      - [Request Example(s)](#request-example-s-)
      - [Request Parameters](#request-parameters)
      - [Response Example(s)](#response-example-s-)
        * [`200 OK`](#-200-ok-)
    + [Get Single User by ID](#get-single-user-by-id)
      - [Request Example(s)](#request-example-s--1)
      - [Request Parameters](#request-parameters-1)
      - [Response Example(s)](#response-example-s--1)
        * [`200 OK`](#-200-ok--1)
    + [Update User's Display Name](#update-user-s-display-name)
      - [Request Example(s)](#request-example-s--2)
      - [Request Parameters](#request-parameters-2)
      - [Response Example(s)](#response-example-s--2)
        * [`200 OK`](#-200-ok--2)
    + [(todo: the rest of the endpoints)](#-todo--the-rest-of-the-endpoints-)
  * [Events](#events)
    + [`EmailChangeRequest`](#-emailchangerequest-)
    + [`UserEvent`](#-userevent-)

<!-- ecotrust-canada.github.io/markdown-toc -->


Single Sign On
-----------------------------------------

### How it Works

Users can be signed in on any domain running this package with a single login attempt from any of the domains as long as they are all connected to the same usora database. This is possible by setting authentication cookies on all participating domains after the login succeeds using html img tags.

API Reference
-----------------------------------------

### Get Index of Users

`GET user/index`


#### Request Example(s)

```js
$.ajax({
    url: 'https://www.musora.com' +
        '/usora/user/index?' +
        'limit=10' + '&' +
        'page=1' + '&' +
        'order_by_column=email' + '&' +
        'order_by_direction=asc',
    type: 'get',
    dataType: 'json',
    success: function(response) {
        // handle success
    },
    error: function(response) {
        // handle error
    }
});
```

#### Request Parameters

| path\|query\|body |  key                  |  required |  default        |  description\|notes | 
|-------------------|-----------------------|-----------|-----------------|---------------------| 
| query             |  `limit`              |           | `25`            |                     | 
| query             |  `page`               |           |  `1`            |                     | 
| query             |  `order_by_column`    |           |  `'created_at'` |                     | 
| query             |  `order_by_direction` |           |  `'desc'`       |                     | 

<!--
path\|query\|body, key, required, default, description\|notes
query, `limit`, ,`25`,
query, `page`,  , `1`, 
query, `order_by_column`,  , `'created_at'`,
query, `order_by_direction`,  , `'desc'`,
-->


#### Response Example(s)

##### `200 OK`

```json
{
   "status":"ok",
   "code":201,
   "results":{
      "id":217988,
      "content_id":202313,
      "key":"difficulty",
      "value":"1",
      "type":"integer",
      "position":1
   }
}
```


------------------------------------------------------------------------------------------------------------------------


### Get Single User by ID

`GET user/show/{id}`


#### Request Example(s)

```js
var userId = 1;

$.ajax({
    url: 'https://www.musora.com' +
        '/usora/user/show/' . userId,
    type: 'get',
    dataType: 'json',
    success: function(response) {
        // handle success
    },
    error: function(response) {
        // handle error
    }
});
```

#### Request Parameters

| path\|query\|body |  key |  required |  default |  description\|notes            | 
|-------------------|------|-----------|----------|--------------------------------| 
| path              |      |  yes      |          |  Id of the user to be returned | 
  
<!--
path\|query\|body, key, required, default, description\|notes
path, , yes ,  , Id of the user to be returned
-->


#### Response Example(s)

##### `200 OK`

```json
{
   "id":"1",
   "email":"pascale84@schimmel.com",
   "password":"$2y$10$hh5cU.fo.Jq48A267zkjiun\\/W.TwbRs4Pg02Nm.X7k.s5yKQxVMj2",
   "remember_token":"D5mpp6aZhvi5vOD7Fs4EDMw8782Be3hXcrRa7cUEaqt6eXlmQPmKbaU1RKdy",
   "session_salt":"0bPpeEbf13tpNi5zkN6bHSQ5Oq72s7YVrCkh2rkRA65Jttd16d0RGQNJbc1R",
   "display_name":"sed accusamus dolorem ut",
   "created_at":"1526460917",
   "updated_at":"1526460917",
   "fields":[]
}
```

### Update User's Display Name

`PUT user/update/{id}`


#### Request Example(s)

```js
var userId = 1;
var displayNameToSet = 'sed accusamus dolorem ut';

$.ajax({
    url: 'https://www.musora.com' +
        '/usora/user/update/' . userId,
    type: 'patch',
    data: {display_name: displayNameToSet},
    dataType: 'json',
    success: function(response) {
        // handle success
    },
    error: function(response) {
        // handle error
    }
});
```

#### Request Parameters

| path\|query\|body |  key             |  required |  default |  description\|notes      | 
|-------------------|------------------|-----------|----------|--------------------------| 
| path              |                  |  yes      |          |  user id                 | 
| body              |  `display_name`  |  yes      |          |  new display name to set | 
 
<!--
path\|query\|body, key, required, default, description\|notes
path ,  , yes ,  , user id
body , `display_name` , yes ,  , new display name to set 
-->


#### Response Example(s)

##### `200 OK`

```json
{
   "id":"1",
   "email":"pascale84@schimmel.com",
   "password":"$2y$10$hh5cU.fo.Jq48A267zkjiun\\/W.TwbRs4Pg02Nm.X7k.s5yKQxVMj2",
   "remember_token":"D5mpp6aZhvi5vOD7Fs4EDMw8782Be3hXcrRa7cUEaqt6eXlmQPmKbaU1RKdy",
   "session_salt":"0bPpeEbf13tpNi5zkN6bHSQ5Oq72s7YVrCkh2rkRA65Jttd16d0RGQNJbc1R",
   "display_name":"sed accusamus dolorem ut",
   "created_at":"1526460917",
   "updated_at":"1526460917",
   "fields":[

   ]
}
```

------------------------------------------------------------------------------------------------------------------------

### (todo: the rest of the endpoints)


Get details from "USORA USER MANAGEMENT SYSTEM - JSON API" section of https://musora.readme.io/v1.0.0/reference

* put, user/store 
* patch, user/update/:id 
* delete, user/delete/:id 
* get, user-field/index/:id 
* get, user-field/show/:id 
* put, user-field/store 
* patch, user-field/update/:id 
* patch, user-field/update-or-create-by-key 
* delete, user-field/delete/:id 
* patch, user-field/update-or-create-multiple-by-key 


Events
----------------------------------------

| Name               |  Parameters    |  Listener exists |  Resultant action(s) | 
|--------------------|----------------|------------------|----------------------| 
| UserEvent          |  id, eventType |  no              |  n/a                 | 
| EmailChangeRequest |  token, email  |  no              |  n/a                 | 

<!-- select "Semicolon Separated" from menu at donatstudios.com/CsvToMarkdownTable - so that we can use commas here 
Name; Parameters; Listener exists; Resultant action(s)
EmailChangeRequest; token, email; no; n/a  
UserEvent; id, eventType; no; n/a 
-->

### `EmailChangeRequest`

Captures that an EmailChangeRequest was made.

Trigger exists in `request` method of `EmailChangeController`

No Listener exists.
    
    
### `UserEvent`

Capture any user-account change.
 
Trigger exists in:...

1. Both `UserQuery`\* methods
    1. `insertGetId` (eventType param value: "created")
    1. `update` (eventType param value: "updated")
1. All three `UserFieldQuery`\* methods (eventType param value: "field-updated" in each)
    1. `insertGetId`
    1. `update`
    1. `delete`
    
\*namespace for each is `Railroad\Usora\Repositories\Queries`

No listener exists.

------------------------------------------------------------------------------------------------------------------------

<div style="text-align:center">The End.</div>
