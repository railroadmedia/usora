<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticate</title>
</head>
<body>
<script type="application/javascript">
    var rememberToken = '{{ $rememberToken }}';
    var userId = '{{ $userId }}';

    window.onload = function () {
        @foreach(\Railroad\Usora\Services\ConfigService::$domainsToAuthenticateOn as $domain)
            window.parent.postMessage({remember_token: rememberToken, user_id: userId}, 'https://' + '{{ $domain }}');
        @endforeach
    }
</script>
</body>
</html>