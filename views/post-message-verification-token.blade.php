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
        try {

            if (failed) {
                window.parent.postMessage({failed: true}, '*');
                return;
            }

            @foreach($domains as $domain)
            window.parent.postMessage(
                {token: token, user_id: userId, failed: false},
                'https://' + '{{ $domain }}'
            );

            @endforeach
        } catch(error) {

        }
    }
</script>
</body>
</html>