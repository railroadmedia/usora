<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticate</title>
</head>
<body>
<script type="application/javascript">
    var token = '{{ $token }}';
    var userId = '{{ $userId }}';
    var failed = '{{ $failed }}';

    window.onload = function () {
        if (failed) {
            window.parent.postMessage({failed: true}, '*');
            return;
        }

        @foreach(\Railroad\Usora\Services\ConfigService::$domainsToAuthenticateOn as $domain)
            window.parent.postMessage(
                {token: token, user_id: userId, failed: false},
                'https://' + '{{ $domain }}'
            );
        @endforeach
    }
</script>
</body>
</html>