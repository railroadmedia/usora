<script type="application/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var loginSuccessRedirectUrl = '{{ $loginSuccessRedirectUrl }}';
        var loginPageUrl = '{{ $loginPageUrl }}';
        var domainsToAuthenticateFromChecked = [];
        var domainsToAuthenticateFromCount =
                {{ count(config('usora.domains_to_authenticate_on_with_request_urls')) }};
        var failedDomains = 0;
        var attemptedDomains = 0;
        var messageReceived = false;

        @foreach($domains as $domain)
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

                if (failedDomains >= domainsToAuthenticateFromCount) {
                    window.location.replace(loginPageUrl);
                }

                return;
            }

            messageReceived = true;

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
            window.location.replace(loginPageUrl);
        }, 9000);

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

        function iframeLoaded(e) {
            attemptedDomains++;

            if (attemptedDomains >= domainsToAuthenticateFromCount) {
                console.log('NOW!');

                setTimeout(function () {
                    console.log(messageReceived);

                    if (!messageReceived) {
                        messageReceived = true;

                        console.log('SSO failed, could not contact other domains.');

                        window.location.replace(loginPageUrl);
                    }
                }, 500);
            }
        }

        var iframes = document.querySelectorAll('.cookie-iframe');

        for (var i = 0; i < iframes.length; i++) {
            iframes[i].onload = function (e) {
                iframeLoaded(e);
            }
        }
    });
</script>

@foreach($postMessageUrls as $postMessageUrl)
    <iframe id="receiver"
            class="cookie-iframe"
            src="{{ $postMessageUrl }}"
            style="width:0;height:0;border:0; border:none;">
    </iframe>
@endforeach