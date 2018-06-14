# Usora
Usora is a user management system including auth, user settings, user information, and single sign on.

## Single Sign On
### How it Works
Users can be signed in on any domain running this package with a single login attempt from any of the domains as long as they are all connected to the same usora database. This is possible by setting authentication cookies on all participating domains after the login succeeds using html img tags.

API Reference
=============

### Get Users

`GET user/index`

#### Request Example

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

#### Response Data Example

```json
[
   {
      "id":"6",
      "email":"brooks.kshlerin@reinger.net",
      "password":"$2y$10$nqNvTklmxrt6.I2qTQvv9OfInEz6SXUaFgUmQvbubpnqBOT.wqkzi",
      "remember_token":"3ti1JYMTmXdXjQ0MpCIljdUfxnANgeZry4xDYjwxcdHshPWCoR1TY7O0663Z",
      "session_salt":"plhIzS9NLmoIHxZNNae1HjmjY7OiNuuFufddlIjdF5Xp2oRm72wn4L5DH7f0",
      "display_name":"necessitatibus consectetur voluptas consequatur",
      "created_at":"1526460569",
      "updated_at":"1526460569",
      "fields":[]
   },
   {
      "id":"1",
      "email":"concepcion.lindgren@hotmail.com",
      "password":"$2y$10$c36zFJtbV1bvG.2EYLel1.aLRHpJtG91p4AHJXicetsRGLhRdZnka",
      "remember_token":"CTfeT1Rm3iZkJM7JfwU0PxKvztFYcmbRtXQZ0xkIhZ3EqpxN0ArMAyCFEylQ",
      "session_salt":"isapVqlC17PatmckIlunIVKRLsfEhKfV6AoU2ftIfBmuyg2DXz6scmoao9Ol",
      "display_name":"temporibus id voluptates cupiditate",
      "created_at":"1526460569",
      "updated_at":"1526460569",
      "fields":[]
   }
]
```


#### Parameters

************ <!-- replace *this line* with markdown table generated using donatstudios.com/CsvToMarkdownTable -->
************ 
************ <!--
************ #, name, required, default, type, description
************  ,  ,  ,  ,  , 
************  ,  ,  ,  ,  , 
************ -->

#### Responses

************ <!-- replace *this line* with markdown table generated using donatstudios.com/CsvToMarkdownTable -->
************ 
************ <!--
************ outcome, return data type, return data value (example), notes about return data
************ ,  ,  , 
************ -->


