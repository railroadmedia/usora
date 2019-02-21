# Usora

Usora is a user management system including auth, user settings, user information, and single sign on.

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

### Form API

`PUT /usora/user/store`
`PATCH /usora/user/update/{user_id}`
`DETLETE /usora/user/delete/{user_id}`

Available attributes to put/patch with validation rules:

**\* NOTE: Email can only be updated by admins with special privileges. Normal users must use the email change endpoint.**

```php
[
    'email' => 'required|email|unique:usora_users,email',
    'display_name' => 'required|string|max:255|min:2|unique:usora_users,display_name',
    'password' => 'required|string|min:8|max:128',
    
    'first_name' => 'string|max:255',
    'last_name' => 'string|max:255',
    'gender' => 'string|in:male,female,other',
    'country' => 'string',
    'region' => 'string',
    'city' => 'string',
    'birthday' => 'string|date',
    'phone_number' => 'string|integer',
    'biography' => 'string',
    'profile_picture_url' => 'string|url',
    'timezone' => 'string|in:' . implode(',', timezone_identifiers_list()),
    'permission_level' => 'string',
    
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