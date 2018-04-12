<script type="application/javascript">
    var loginSuccessRedirectUrl = '{{ $loginSuccessRedirectUrl }}';
    var loginPageUrl = '{{ $loginPageUrl }}';

    window.onload = function () {
        function receiveMessage(e) {
            var failed = e.data['failed'];
            var rememberToken = e.data['remember_token'];
            var userId = e.data['user_id'];

            if (failed) {
                window.location.replace(loginPageUrl);
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ url()->route('authenticate.set-authentication-cookie') }}');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var result = JSON.parse(xhr.responseText);

                    if (result.success) {
                        window.location.replace(loginSuccessRedirectUrl);
                    } else {
                        window.location.replace(loginPageUrl);
                    }
                }
            };
            xhr.send(JSON.stringify({
                rt: rememberToken,
                uid: userId,
                _token: "{{ csrf_token() }}",
            }));
        }

        window.addEventListener('message', receiveMessage);
    }
</script>

@foreach(\Railroad\Usora\Services\ConfigService::$domainsToCheckForAuthenticateOn as $domain)
    <iframe id="receiver"
            src="https://{{ $domain }}/{{ ltrim(parse_url(route('authenticate.post-message-remember-token'))['path'] ?? '', '/') }}"
            style="width:0;height:0;border:0; border:none;">
    </iframe>
@endforeach