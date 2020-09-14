# APP endpoints

- [APP endpoints](#app-endpoints)
  * [User login](#user-login)
    + [HTTP Request](#http-request)
    + [Permissions](#permissions)
    + [Request Parameters](#request-parameters)
    + [Request Example:](#request-example-)
    + [Response Example (200):](#response-example--200--)
  * [Logout the authenticated user and invalidate the jwt token](#logout-the-authenticated-user-and-invalidate-the-jwt-token)
    + [HTTP Request](#http-request-1)
    + [Permissions](#permissions-1)
    + [Request Parameters](#request-parameters-1)
    + [Request Example:](#request-example--1)
    + [Response Example (200):](#response-example--200---1)
  * [Get authenticated user](#get-authenticated-user)
    + [HTTP Request](#http-request-2)
    + [Permissions](#permissions-2)
    + [Request Parameters](#request-parameters-2)
    + [Request Example:](#request-example--2)
    + [Response Example (200):](#response-example--200---2)
  * [Send the password reset link to the user](#send-the-password-reset-link-to-the-user)
    + [HTTP Request](#http-request-3)
    + [Permissions](#permissions-3)
    + [Request Parameters](#request-parameters-3)
    + [Request Example:](#request-example--3)
    + [Response Example (200):](#response-example--200---3)
  * [Update user profile](#usora-api-profile-update)
    + [HTTP Request](#http-request-4)
    + [Permissions](#permissions-4)
    + [Request Parameters](#request-parameters-4)
    + [Validation Rules](#validation-rules)
    + [Request Example:](#request-example--4)
    + [Response Example (200):](#response-example--200---4)

<small><i><a href='http://ecotrust-canada.github.io/markdown-toc/'>Table of contents generated with markdown-toc</a></i></small>

<!-- START_692bc7d0ef531cf3a7a131915b47da97 -->
## User login


### HTTP Request
    `PUT usora/api/login`


### Permissions
    - Without restrictions
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|email|  yes  ||
|body|password|  yes  ||


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/api/login',
{
    "email": "email@email.ro",
    "password": "password"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZGV2LmRydW1lby5jb21cL2xhcmF2ZWxcL3B1YmxpY1wvYXBpXC9sb2dpbiIsImlhdCI6MTU2NTcwNDczNiwiZXhwIjoxNTY1NzA4MzM2LCJuYmYiOjE1NjU3MDQ3MzYsImp0aSI6Im8yMWJFaVU3WUcyS3VCa0wiLCJzdWIiOjE0OTYyOCwicHJ2IjoiOWY4YTIzODlhMjBjYTA3NTJhYTllOTUwOTM1MTU1MTdlOTBlMTk0YyJ9.ayJrvjNMrfDg78Aedglp6sEEoz6jzMLbHl7Gcy6Cygg",
    "isEdge": true,
    "isEdgeExpired": false,
    "edgeExpirationDate": null,
    "isPackOwner": true,
    "tokenType": "bearer",
    "expiresIn": 3600,
    "userId": 149628
}
```




<!-- END_692bc7d0ef531cf3a7a131915b47da97 -->

<!-- START_7b166a897678442a993399a0a61243e6 -->
## Logout the authenticated user and invalidate the jwt token


### HTTP Request
    `PUT usora/api/logout`


### Permissions
    - Only authenticated user
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/api/logout',
     headers: {
        'Authorization': `Bearer ${token}`,
    },
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "success": true,
    "message": "Successfully logged out"
}
```




<!-- END_7b166a897678442a993399a0a61243e6 -->

<!-- START_9040b99e6fa1413cd5ee8012d11d2f6c -->
## Get authenticated user


### HTTP Request
    `PUT usora/api/profile`


### Permissions
    - Only authenticated user
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/api/profile',
     headers: {
        'Authorization': `Bearer ${token}`,
    },
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "id": 149628,
    "wordpressId": 152167,
    "ipbId": 150228,
    "email": "roxana.riza@artsoft-consult.ro",
    "permission_level": "administrator",
    "login_username": "roxana.riza@artsoft-consult.ro",
    "display_name": "Roxana",
    "first_name": "Roxana",
    "last_name": "",
    "gender": "",
    "country": "Romania",
    "region": "",
    "city": "",
    "birthday": "2017-07-04 00:00:00",
    "phone_number": "",
    "bio": "",
    "created_at": "2017-07-31 22:54:41",
    "updated_at": "2019-08-01 07:05:50",
    "avatarUrl": "https:\/\/drumeo-profile-images.s3.us-west-2.amazonaws.com\/149628_avatar_url_1563362703.jpeg",
    "totalXp": 54280,
    "xpRank": "Master II"
}
```




<!-- END_9040b99e6fa1413cd5ee8012d11d2f6c -->

<!-- START_5abeccccb16446e91dcd2680d349c211 -->
## Send the password reset link to the user


### HTTP Request
    `PUT usora/api/forgot`


### Permissions
    - Without restrictions
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|
|body|email|  yes  ||


### Request Example:

```js
$.ajax({
    url: 'https://www.domain.com' +
             '/usora/api/forgot',
{
    "email": "email@email.ro"
}
   ,
    success: function(response) {},
    error: function(response) {}
});
```

### Response Example (200):

```json
{
    "success": true,
    "title": "Please check your email",
    "message": "Follow the instructions sent to your email address to reset your password."
}
```




<!-- END_5abeccccb16446e91dcd2680d349c211 -->

<!-- START_4a15a910480bee31ce83f8846c413e55 -->
## usora/api/profile/update

### HTTP Request
    `POST usora/api/profile/update`


### Permissions
    - Only authenticated user
    
### Request Parameters


|Type|Key|Required|Notes|
|----|---|--------|-----|

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
             '/usora/api/profile/update',
     headers: {
        'Authorization': `Bearer ${token}`,
    },
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
            "profile_picture_url": "https:\/\/drumeo-profile-images.s3.us-west-2.amazonaws.com\/149628_avatar_url_1563362703.jpeg",
            "timezone": "assumenda",
            "permission_level": 3,
            "notify_on_lesson_comment_reply": true,
            "notify_weekly_update": true,
            "notify_on_forum_post_like":true,
            "notify_on_forum_followed_thread_reply": true,
            "notify_on_forum_post_reply": true,
            "notify_on_lesson_comment_like": true,
            "notifications_summary_frequency_minutes": 500
        }
    },
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
           "email": "test@test.te",
            "display_name": "John Snow",
            "first_name": "John",
            "last_name": "Snow",
            "created_at": "2017-05-27 23:46:12",
            "updated_at": "2019-04-01 00:41:14",
            "gender": "",
           "country": "doloribus",
            "region": "libero",
            "city": "ut",
            "birthday": "2019-05-21 21:20:10",
            "phone_number": "0045124512",
            "biography": "eius",
            "profile_picture_url": "https:\/\/drumeo-profile-images.s3.us-west-2.amazonaws.com\/149628_avatar_url_1563362703.jpeg",
            "timezone": "assumenda",
            "permission_level": 3,
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
            "notifications_summary_frequency_minutes": 500
        }
    }
}
```




<!-- END_4a15a910480bee31ce83f8846c413e55 -->

