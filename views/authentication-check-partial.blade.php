<script type="application/javascript">
    var loginSuccessRedirectUrl = '{{ $loginSuccessRedirectUrl }}';
    var loginPageUrl = '{{ $loginPageUrl }}';
    var domainsToAuthenticateFromChecked = [];
    var domainsToAuthenticateFromCount =
            {{ count(\Railroad\Usora\Services\ConfigService::$domainsToCheckForAuthenticateOn) }};
    var failedDomains = 0;

    @foreach(\Railroad\Usora\Services\ConfigService::$domainsToCheckForAuthenticateOn as $domain)
        domainsToAuthenticateFromChecked['{{ $domain }}'] = true;

    @endforeach

    function receiveMessage(e) {
        var failed = e.data['failed'];
        var token = e.data['token'];
        var userId = e.data['user_id'];
        var domain = extractHostname(e.origin);

        if (failed || token.length < 1 || domainsToAuthenticateFromChecked[domain] === undefined) {
            failedDomains++;
            console.log('Failed: ' + domain);

            if (failedDomains => domainsToAuthenticateFromCount) {
                window.location.replace(loginPageUrl);
            }

            return;
        }

        console.log('Success: ' + domain);

        var xhr = new XMLHttpRequest();

        xhr.open('POST', '{{ url()->route('usora.authenticate.set-authentication-cookie') }}');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var result = JSON.parse(xhr.responseText);

                if (result.success) {
                    window.location.replace(loginSuccessRedirectUrl);
                } else {
                    console.log(xhr);
                    window.location.replace(loginPageUrl);
                }
            }
        };
        xhr.send(JSON.stringify({
            vt: token,
            uid: userId,
            _token: "{{ csrf_token() }}",
        }));
    }

    window.addEventListener('message', receiveMessage);

    setTimeout(function () {
        // window.location.replace(loginPageUrl);
    }, 8000);

    function extractHostname(url) {
        var hostname;

        if (url.indexOf("://") > -1) {
            hostname = url.split('/')[2];
        } else {
            hostname = url.split('/')[0];
        }

        hostname = hostname.split(':')[0];
        hostname = hostname.split('?')[0];

        return hostname;
    }
</script>

@foreach(\Railroad\Usora\Services\ConfigService::$domainsToCheckForAuthenticateOn as $domain)
    <iframe id="receiver"
            src="https://{{ $domain }}/{{ ltrim(parse_url(route('usora.authenticate.render-post-message-verification-token'))['path'] ?? '', '/') }}"
            style="width:0;height:0;border:0; border:none;">
    </iframe>
@endforeach