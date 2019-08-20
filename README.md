# Usora

Usora is a user management system including auth, user settings, user information, and single sign on.

- [Usora](#usora)
  * [How To Install](#how-to-install)
  * [Single Sign On](#single-sign-on)
    + [How it Works](#how-it-works)
  * [API Reference](#api-reference)
    + [User Form API](#user-form-api)
    + [Email Change Form API](#email-change-form-api)
  * [JSON endpoints](docs/Users-JSON-endpoints.md#json-endpoints)
      + [Pull users](docs/Users-JSON-endpoints.md#pull-users)
      + [Show user](docs/Users-JSON-endpoints.md#pull-user)
      + [Create new user](docs/Users-JSON-endpoints.md#create-new-user)
      + [Update an existing user.](docs/Users-JSON-endpoints.md#update-an-existing-user)
      + [Delete an user](docs/Users-JSON-endpoints.md#delete-an-user)
  * [APP endpoints](docs/APP-endpoints.md#app-endpoints)
     + [User login](docs/APP-endpoints.md#user-login)
     + [Logout the authenticated user and invalidate the jwt token](docs/APP-endpoints.md##logout-the-authenticated-user-and-invalidate-the-jwt-token)
     + [Get authenticated user](docs/APP-endpoints.md#get-authenticated-user)
     + [Send the password reset link to the user](docs/APP-endpoints.md#send-the-password-reset-link-to-the-user)
     + [Update user profile](docs/APP-endpoints.md#usora-api-profile-update)

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


Single Sign On
-----------------------------------------

### How it Works

Users can be signed in on any domain running this package with a single login attempt from any of the domains as long as they are all connected to the same usora database. This is possible by setting authentication cookies on all participating domains after the login succeeds using html img tags.


API Reference
-----------------------------------------

### User Form API

**`PUT /usora/user/store`**  
**`PATCH /usora/user/update/{user_id}`**    
**`DELETE /usora/user/delete/{user_id}`**  

Parameters and validation for PUT/PATCH:

**\* NOTE: Email can only be updated by admins with special privileges. Normal users must use the email change endpoint.**

*NOTE: Required parameters are only required for PUT/create requests.

```php
[
    'email' => 'required|email|unique:usora_users,email',
    'display_name' => 'required|string|max:255|min:2|unique:usora_users,display_name',
    'password' => 'required|string|min:8|max:128',
    
    'first_name' => 'nullable|string|max:255',
    'last_name' => 'nullable|string|max:255',
    'gender' => 'nullable|string|in:male,female,other',
    'country' => 'nullable|string',
    'region' => 'nullable|string',
    'city' => 'nullable|string',
    'birthday' => 'nullable|string|date',
    'phone_number' => 'nullable|string|integer',
    'biography' => 'nullable|string',
    'profile_picture_url' => 'nullable|string|url',
    'timezone' => 'nullable|string|in:' . implode(',', timezone_identifiers_list()),
    'permission_level' => 'nullable|string',
    
    'notify_on_lesson_comment_reply' => 'nullable|boolean',
    'notify_weekly_update' => 'nullable|boolean',
    'notify_on_forum_post_like' => 'nullable|boolean',
    'notify_on_forum_followed_thread_reply' => 'nullable|boolean',
    'notify_on_forum_post_reply' => 'nullable|boolean',
    'notify_on_lesson_comment_like' => 'nullable|boolean',
    'notifications_summary_frequency_minutes' => 'nullable|integer',
    
    'drums_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
    'drums_gear_photo' => 'nullable|url',
    'drums_gear_cymbal_brands' => 'nullable|string',
    'drums_gear_set_brands' => 'nullable|string',
    'drums_gear_hardware_brands' => 'nullable|string',
    'drums_gear_stick_brands' => 'nullable|string',
    
    'guitar_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
    'guitar_gear_photo' => 'nullable|url',
    'guitar_gear_guitar_brands' => 'nullable|string',
    'guitar_gear_amp_brands' => 'nullable|string',
    'guitar_gear_pedal_brands' => 'nullable|string',
    'guitar_gear_string_brands' => 'nullable|string',
    
    'piano_playing_since_year' => 'nullable|integer|between:1900,' . date('Y'),
    'piano_gear_photo' => 'nullable|url',
    'piano_gear_piano_brands' => 'nullable|string',
    'piano_gear_keyboard_brands' => 'nullable|string',
];
```

Will return a redirect to URL passed in with 'redirect' parameter, or will return to previous URL. Always redirects with
```php
['success' => true]
```
flashed to the session.


### Email Change Form API

**`POST /usora/email-change/request`**

Parameters and validation for POST:

```php
[
    'email' => 'required|email|unique:usora_users,email',
];
```

Will return a redirect to URL passed in with 'redirect' parameter, or will return to previous URL. Always redirects with
```php
[
    'successes' => new MessageBag(
        ['password' => 'An email confirmation link has been sent to your new email address.']
    ),
]
```
flashed to the session.


**`GET /usora/email-change/confirm`**

```php
[
    'token' => 'bail|required|string|exists:usora_email_changes,token',
]
```

Will return a redirect to URL passed in with 'redirect' parameter, or will return to previous URL. Always redirects with
```php
[
    'successes' => new MessageBag(
        ['password' => 'Your email has been updated successfully.']
    ),
];
```
flashed to the session.