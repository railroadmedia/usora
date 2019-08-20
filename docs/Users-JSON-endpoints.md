# Users-JSON-endpoints

- [JSON Endpoints](#json-endpoints)
  * [Pull users](#pull-users)
    + [HTTP Request](#http-request)
    + [Permissions](#permissions)
    + [Request Parameters](#request-parameters)
    + [Request Example:](#request-example-)
    + [Response Example (200):](#response-example--200--)
  * [Pull user](#pull-user)
    + [HTTP Request](#http-request-1)
    + [Permissions](#permissions-1)
    + [Request Parameters](#request-parameters-1)
    + [Request Example:](#request-example--1)
    + [Response Example (200):](#response-example--200---1)
  * [Create new user](#create-new-user)
    + [HTTP Request](#http-request-2)
    + [Permissions](#permissions-2)
    + [Request Parameters](#request-parameters-2)
    + [Validation Rules](#validation-rules)
    + [Request Example:](#request-example--2)
    + [Response Example (200):](#response-example--200---2)
  * [Update an existing user.](#update-an-existing-user)
    + [HTTP Request](#http-request-3)
    + [Permissions](#permissions-3)
    + [Request Parameters](#request-parameters-3)
    + [Validation Rules](#validation-rules-1)
    + [Request Example:](#request-example--3)
    + [Response Example (200):](#response-example--200---3)
  * [Delete an user](#delete-an-user)
    + [HTTP Request](#http-request-4)
    + [Permissions](#permissions-4)
    + [Request Parameters](#request-parameters-4)
    + [Request Example:](#request-example--4)
    + [Response Example (404)](#response-example--404-)


# JSON Endpoints


<!-- START_ad35f3a7bece69a6766a5247ae066df1 -->
## Pull users


### HTTP Request
    `GET usora/json-api/user/index`


### Permissions
    - Must be logged in
    - Only users with index-users ability
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|search_term|    ||
|body|per_page|    |Default:25|
|body|page|    |Default:1|
|body|sort|    |Default:createdAt|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/json-api/user/index',
{
    "search_term": "nisi",
    "per_page": 2,
    "page": 1,
    "sort": "createdAt"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": [
        {
            "type": "user",
            "id": "154105",
            "attributes": {
                "email": "03elijah.brown@gmail.com",
                "display_name": "03elijah.brown8837",
                "created_at": "2019-04-30 18:41:13",
                "updated_at": "2019-05-23 15:56:21",
                "first_name": null,
                "last_name": null,
                "gender": "",
                "country": null,
                "region": null,
                "city": null,
                "birthday": null,
                "phone_number": "",
                "biography": null,
                "profile_picture_url": "",
                "timezone": "",
                "permission_level": null,
                "drums_playing_since_year": null,
                "drums_gear_photo": "",
                "drums_gear_cymbal_brands": null,
                "drums_gear_set_brands": null,
                "drums_gear_hardware_brands": null,
                "drums_gear_stick_brands": null,
                "guitar_playing_since_year": null,
                "guitar_gear_photo": "",
                "guitar_gear_guitar_brands": null,
                "guitar_gear_amp_brands": null,
                "guitar_gear_pedal_brands": null,
                "guitar_gear_string_brands": null,
                "piano_playing_since_year": null,
                "piano_gear_photo": "",
                "piano_gear_piano_brands": null,
                "piano_gear_keyboard_brands": null,
                "notify_on_lesson_comment_reply": true,
                "notify_weekly_update": true,
                "notify_on_forum_post_like": true,
                "notify_on_forum_followed_thread_reply": true,
                "notify_on_forum_post_reply": true,
                "notify_on_lesson_comment_like": true,
                "notifications_summary_frequency_minutes": null
            }
        },
        {
            "type": "user",
            "id": "151248",
            "attributes": {
                "email": "08borda25@gmail.com",
                "display_name": "borda91",
                "created_at": "2017-05-27 23:46:12",
                "updated_at": "2019-04-01 00:41:14",
                "first_name": null,
                "last_name": null,
                "gender": "",
                "country": null,
                "region": null,
                "city": null,
                "birthday": null,
                "phone_number": "",
                "biography": null,
                "profile_picture_url": "",
                "timezone": "",
                "permission_level": null,
                "drums_playing_since_year": null,
                "drums_gear_photo": "",
                "drums_gear_cymbal_brands": null,
                "drums_gear_set_brands": null,
                "drums_gear_hardware_brands": null,
                "drums_gear_stick_brands": null,
                "guitar_playing_since_year": null,
                "guitar_gear_photo": "",
                "guitar_gear_guitar_brands": null,
                "guitar_gear_amp_brands": null,
                "guitar_gear_pedal_brands": null,
                "guitar_gear_string_brands": null,
                "piano_playing_since_year": null,
                "piano_gear_photo": "",
                "piano_gear_piano_brands": null,
                "piano_gear_keyboard_brands": null,
                "notify_on_lesson_comment_reply": true,
                "notify_weekly_update": true,
                "notify_on_forum_post_like": true,
                "notify_on_forum_followed_thread_reply": true,
                "notify_on_forum_post_reply": true,
                "notify_on_lesson_comment_like": true,
                "notifications_summary_frequency_minutes": null
            }
        }
    ],
    "meta": {
        "pagination": {
            "total": 5825,
            "count": 2,
            "per_page": 2,
            "current_page": 1,
            "total_pages": 2913
        }
    },
    "links": {
        "self": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=1&sort=email&per_page=2",
        "first": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=1&sort=email&per_page=2",
        "next": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=2&sort=email&per_page=2",
        "last": "https:\/\/dev.musora.com\/usora\/json-api\/user\/index?limit=2&page=2913&sort=email&per_page=2"
    }
}
```




<!-- END_ad35f3a7bece69a6766a5247ae066df1 -->

<!-- START_3ed4110aa70ef306bb1f55daf621370e -->
## Pull user


### HTTP Request
    `GET usora/json-api/user/show/{id}`


### Permissions
    - Must be logged in
    - Only user with show-users ability
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|id|  yes  ||


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/json-api/user/show/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": {
        "type": "user",
        "id": "151248",
        "attributes": {
            "email": "08borda25@gmail.com",
            "display_name": "borda91",
            "created_at": "2017-05-27 23:46:12",
            "updated_at": "2019-04-01 00:41:14",
            "first_name": null,
            "last_name": null,
            "gender": "",
            "country": null,
            "region": null,
            "city": null,
            "birthday": null,
            "phone_number": "",
            "biography": null,
            "profile_picture_url": "",
            "timezone": "",
            "permission_level": null,
            "drums_playing_since_year": null,
            "drums_gear_photo": "",
            "drums_gear_cymbal_brands": null,
            "drums_gear_set_brands": null,
            "drums_gear_hardware_brands": null,
            "drums_gear_stick_brands": null,
            "guitar_playing_since_year": null,
            "guitar_gear_photo": "",
            "guitar_gear_guitar_brands": null,
            "guitar_gear_amp_brands": null,
            "guitar_gear_pedal_brands": null,
            "guitar_gear_string_brands": null,
            "piano_playing_since_year": null,
            "piano_gear_photo": "",
            "piano_gear_piano_brands": null,
            "piano_gear_keyboard_brands": null,
            "notify_on_lesson_comment_reply": true,
            "notify_weekly_update": true,
            "notify_on_forum_post_like": true,
            "notify_on_forum_followed_thread_reply": true,
            "notify_on_forum_post_reply": true,
            "notify_on_lesson_comment_like": true,
            "notifications_summary_frequency_minutes": null
        }
    }
}
```




<!-- END_3ed4110aa70ef306bb1f55daf621370e -->

<!-- START_a13a4746f770bb867d7503aa8c39da47 -->
## Create new user


### HTTP Request
    `PUT usora/json-api/user/store`


### Permissions
    - Must be logged in
    - Only user with create-users ability
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|data.type|  yes  |Must be 'user'.|
|body|data.attributes.email|    |string|
|body|data.attributes.display_name|    |string |
|body|data.attributes.password|    |string |
|body|data.attributes.first_name|    ||
|body|data.attributes.last_name|    ||
|body|data.attributes.gender|    ||
|body|data.attributes.country|    ||
|body|data.attributes.region|    ||
|body|data.attributes.city|    ||
|body|data.attributes.birthday|    ||
|body|data.attributes.phone_number|    ||
|body|data.attributes.biography|    ||
|body|data.attributes.profile_picture_url|    ||
|body|data.attributes.timezone|    ||
|body|data.attributes.permission_level|    ||
|body|data.attributes.notify_on_lesson_comment_reply|    ||
|body|data.attributes.notify_weekly_update|    ||
|body|data.attributes.notify_on_forum_post_like|    ||
|body|data.attributes.notify_on_forum_followed_thread_reply|    ||
|body|data.attributes.notify_on_forum_post_reply|    ||
|body|data.attributes.notify_on_lesson_comment_like|    ||
|body|data.attributes.notifications_summary_frequency_minutes|    ||

### Validation Rules
```php
        return [
            'data.attributes.email' => 'required|email|max:255|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',email',
            'data.attributes.display_name' => 'required|string|max:64|min:2|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',display_name',
            'data.attributes.password' => 'required|string|min:8|max:128',

            'data.attributes.first_name' => 'string|max:64',
            'data.attributes.last_name' => 'string|max:64',
            'data.attributes.gender' => 'string|in:male,female,other',
            'data.attributes.country' => 'string|max:84',
            'data.attributes.region' => 'string|max:84',
            'data.attributes.city' => 'string|max:84',
            'data.attributes.birthday' => 'string|date',
            'data.attributes.phone_number' => 'string|max:15',
            'data.attributes.biography' => 'string|max:15000',
            'data.attributes.profile_picture_url' => 'string|url|max:1000',
            'data.attributes.timezone' => 'string|in:' . implode(',', timezone_identifiers_list()),
            'data.attributes.permission_level' => 'string|max:255',

            'data.attributes.notify_on_lesson_comment_reply' => 'nullable|boolean',
            'data.attributes.notify_weekly_update' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_like' => 'nullable|boolean',
            'data.attributes.notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_reply' => 'nullable|boolean',
            'data.attributes.notify_on_lesson_comment_like' => 'nullable|boolean',
            'data.attributes.notifications_summary_frequency_minutes' => 'nullable|integer|max:43200',

            'data.attributes.drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.drums_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.drums_gear_cymbal_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_set_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_hardware_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_stick_brands' => 'nullable|string|max:255',

            'data.attributes.guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.guitar_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.guitar_gear_guitar_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_amp_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_pedal_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_string_brands' => 'nullable|string|max:255',

            'data.attributes.piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.piano_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.piano_gear_piano_brands' => 'nullable|string|max:255',
            'data.attributes.piano_gear_keyboard_brands' => 'nullable|string|max:255',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/json-api/user/store',
{
    "data": {
        "type": "user",
        "attributes": {
            "email": "test@test.te",
            "display_name": "John Snow",
            "password": "John",
            "first_name": "John",
            "last_name": "Snow",
            "gender": "female",
            "country": "nemo",
            "region": "deleniti",
            "city": "ut",
            "birthday": "2019-05-21 21:20:10",
            "phone_number": "0045124512",
            "biography": "debitis",
            "profile_picture_url": "''",
            "timezone": "eveniet",
            "permission_level": 12,
            "notify_on_lesson_comment_reply": [],
            "notify_weekly_update": [],
            "notify_on_forum_post_like": [],
            "notify_on_forum_followed_thread_reply": [],
            "notify_on_forum_post_reply": [],
            "notify_on_lesson_comment_like": [],
            "notifications_summary_frequency_minutes": []
        }
    }
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": {
        "type": "user",
        "id": "151248",
        "attributes": {
            "email": "08borda25@gmail.com",
            "display_name": "borda91",
            "created_at": "2017-05-27 23:46:12",
            "updated_at": "2019-04-01 00:41:14",
            "first_name": null,
            "last_name": null,
            "gender": "",
            "country": null,
            "region": null,
            "city": null,
            "birthday": null,
            "phone_number": "",
            "biography": null,
            "profile_picture_url": "",
            "timezone": "",
            "permission_level": null,
            "drums_playing_since_year": null,
            "drums_gear_photo": "",
            "drums_gear_cymbal_brands": null,
            "drums_gear_set_brands": null,
            "drums_gear_hardware_brands": null,
            "drums_gear_stick_brands": null,
            "guitar_playing_since_year": null,
            "guitar_gear_photo": "",
            "guitar_gear_guitar_brands": null,
            "guitar_gear_amp_brands": null,
            "guitar_gear_pedal_brands": null,
            "guitar_gear_string_brands": null,
            "piano_playing_since_year": null,
            "piano_gear_photo": "",
            "piano_gear_piano_brands": null,
            "piano_gear_keyboard_brands": null,
            "notify_on_lesson_comment_reply": true,
            "notify_weekly_update": true,
            "notify_on_forum_post_like": true,
            "notify_on_forum_followed_thread_reply": true,
            "notify_on_forum_post_reply": true,
            "notify_on_lesson_comment_like": true,
            "notifications_summary_frequency_minutes": null
        }
    }
}
```




<!-- END_a13a4746f770bb867d7503aa8c39da47 -->

<!-- START_e29a163fee0033a2545cfa2a0ab4772d -->
## Update an existing user.


### HTTP Request
    `PATCH usora/json-api/user/update/{id}`


### Permissions
    - Must be logged in
    - Must have the update-users permission to update
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|user_id|  yes  ||
|body|data.type|  yes  |Must be 'user'.|
|body|data.attributes.email|    ||
|body|data.attributes.display_name|    ||
|body|data.attributes.first_name|    ||
|body|data.attributes.last_name|    ||
|body|data.attributes.gender|    ||
|body|data.attributes.country|    ||
|body|data.attributes.region|    ||
|body|data.attributes.city|    ||
|body|data.attributes.birthday|    ||
|body|data.attributes.phone_number|    ||
|body|data.attributes.biography|    ||
|body|data.attributes.profile_picture_url|    ||
|body|data.attributes.timezone|    ||
|body|data.attributes.permission_level|    ||
|body|data.attributes.notify_on_lesson_comment_reply|    ||
|body|data.attributes.notify_weekly_update|    ||
|body|data.attributes.notify_on_forum_post_like|    ||
|body|data.attributes.notify_on_forum_followed_thread_reply|    ||
|body|data.attributes.notify_on_forum_post_reply|    ||
|body|data.attributes.notify_on_lesson_comment_like|    ||
|body|data.attributes.notifications_summary_frequency_minutes|    ||

### Validation Rules
```php
        return [
            'data.attributes.email' => 'email|max:255|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',email,' .
                $this->route('id'),
            'data.attributes.display_name' => 'string|max:64|min:2|unique:' .
                config('usora.database_connection_name') .
                '.' .
                config('usora.tables.users') .
                ',display_name,' .
                $this->route('id'),
            'data.attributes.password' => 'string|min:8|max:128|confirmed',

            'data.attributes.first_name' => 'nullable|string|max:64',
            'data.attributes.last_name' => 'nullable|string|max:64',
            'data.attributes.gender' => 'nullable|string|in:male,female,other',
            'data.attributes.country' => 'nullable|string|max:84',
            'data.attributes.region' => 'nullable|string|max:84',
            'data.attributes.city' => 'nullable|string|max:84',
            'data.attributes.birthday' => 'nullable|string|date',
            'data.attributes.phone_number' => 'nullable|string|max:15',
            'data.attributes.biography' => 'nullable|string|max:15000',
            'data.attributes.profile_picture_url' => 'nullable|string|url|max:1000',
            'data.attributes.timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
            'data.attributes.permission_level' => 'nullable|string|max:255',

            'data.attributes.notify_on_lesson_comment_reply' => 'nullable|boolean',
            'data.attributes.notify_weekly_update' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_like' => 'nullable|boolean',
            'data.attributes.notify_on_forum_followed_thread_reply' => 'nullable|boolean',
            'data.attributes.notify_on_forum_post_reply' => 'nullable|boolean',
            'data.attributes.notify_on_lesson_comment_like' => 'nullable|boolean',
            'data.attributes.notifications_summary_frequency_minutes' => 'nullable|integer|max:43200',

            'data.attributes.drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.drums_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.drums_gear_cymbal_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_set_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_hardware_brands' => 'nullable|string|max:255',
            'data.attributes.drums_gear_stick_brands' => 'nullable|string|max:255',

            'data.attributes.guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.guitar_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.guitar_gear_guitar_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_amp_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_pedal_brands' => 'nullable|string|max:255',
            'data.attributes.guitar_gear_string_brands' => 'nullable|string|max:255',

            'data.attributes.piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
            'data.attributes.piano_gear_photo' => 'nullable|url|max:1000',
            'data.attributes.piano_gear_piano_brands' => 'nullable|string|max:255',
            'data.attributes.piano_gear_keyboard_brands' => 'nullable|string|max:255',
        ];
```

### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/json-api/user/update/1',
{
    "data": {
        "type": "user",
        "attributes": {
            "email": "test@test.te",
            "display_name": "John Snow",
            "first_name": "John",
            "last_name": "Snow",
            "gender": "female",
            "country": "doloribus",
            "region": "libero",
            "city": "ut",
            "birthday": "2019-05-21 21:20:10",
            "phone_number": "0045124512",
            "biography": "eius",
            "profile_picture_url": "''",
            "timezone": "assumenda",
            "permission_level": 3,
            "notify_on_lesson_comment_reply": [],
            "notify_weekly_update": [],
            "notify_on_forum_post_like": [],
            "notify_on_forum_followed_thread_reply": [],
            "notify_on_forum_post_reply": [],
            "notify_on_lesson_comment_like": [],
            "notifications_summary_frequency_minutes": []
        }
    }
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "data": {
        "type": "user",
        "id": "151248",
        "attributes": {
            "email": "08borda25@gmail.com",
            "display_name": "borda91",
            "created_at": "2017-05-27 23:46:12",
            "updated_at": "2019-04-01 00:41:14",
            "first_name": null,
            "last_name": null,
            "gender": "",
            "country": null,
            "region": null,
            "city": null,
            "birthday": null,
            "phone_number": "",
            "biography": null,
            "profile_picture_url": "",
            "timezone": "",
            "permission_level": null,
            "drums_playing_since_year": null,
            "drums_gear_photo": "",
            "drums_gear_cymbal_brands": null,
            "drums_gear_set_brands": null,
            "drums_gear_hardware_brands": null,
            "drums_gear_stick_brands": null,
            "guitar_playing_since_year": null,
            "guitar_gear_photo": "",
            "guitar_gear_guitar_brands": null,
            "guitar_gear_amp_brands": null,
            "guitar_gear_pedal_brands": null,
            "guitar_gear_string_brands": null,
            "piano_playing_since_year": null,
            "piano_gear_photo": "",
            "piano_gear_piano_brands": null,
            "piano_gear_keyboard_brands": null,
            "notify_on_lesson_comment_reply": true,
            "notify_weekly_update": true,
            "notify_on_forum_post_like": true,
            "notify_on_forum_followed_thread_reply": true,
            "notify_on_forum_post_reply": true,
            "notify_on_lesson_comment_like": true,
            "notifications_summary_frequency_minutes": null
        }
    }
}
```




<!-- END_e29a163fee0033a2545cfa2a0ab4772d -->

<!-- START_57f891df3a8fcf913c0c60e1436acd08 -->
## Delete an user


### HTTP Request
    `DELETE usora/json-api/user/delete/{id}`


### Permissions
    - Must be logged in
    - Must have the delete-users permission to delete
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|query|user_id|  yes  ||


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/json-api/user/delete/1',
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (404)

```json
```




<!-- END_57f891df3a8fcf913c0c60e1436acd08 -->

