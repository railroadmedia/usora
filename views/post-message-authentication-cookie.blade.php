<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticate</title>
</head>
<body>
<script type="application/javascript">
    var sessionCookieName = '{{ $sessionCookieName }}';
    var sessionCookieValue = '{{ $sessionCookieValue }}';
    var rememberCookieName = '{{ $rememberCookieName }}';
    var rememberCookieValue = '{{ $rememberCookieValue }}';

    window.onload = function () {
        var cookies = [];

        cookies[sessionCookieName] = sessionCookieValue;
        cookies[rememberCookieName] = rememberCookieValue;

        @foreach(\Railroad\Usora\Services\ConfigService::$domainsToAuthenticateOn as $domain)
            window.parent.postMessage(cookies, 'https://' + '{{ $domain }}');
        @endforeach
    }
</script>
</body>
</html>