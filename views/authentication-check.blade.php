<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticate</title>
</head>
<body>

<script type="application/javascript">
    window.onload = function() {
        function receiveMessage(e) {
            console.log(e.data['laravel_session']);
        }

        window.addEventListener('message', receiveMessage);
    }
</script>

@foreach(\Railroad\Usora\Services\ConfigService::$domainsToCheckForAuthenticateOn as $domain)
    <iframe id="receiver"
            src="https://{{ $domain }}/{{ ltrim(parse_url(route('authenticate.post-message-authentication-cookie'))['path'] ?? '', '/') }}"
            width="500" height="500">
    </iframe>
@endforeach

</body>
</html>