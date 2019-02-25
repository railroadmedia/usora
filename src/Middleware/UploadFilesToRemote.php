<?php

namespace Railroad\Usora\Middleware;

use Aws\S3\S3Client;
use Closure;
use Illuminate\Http\Request;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class UploadFilesToRemote
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new error binder instance.
     *
     * @param  \Illuminate\Contracts\View\Factory $view
     * @return void
     */
    public function __construct()
    {
        $client = new S3Client(
            [
                'credentials' => [
                    'key' => config('usora.file_upload_aws_s3_access_key'),
                    'secret' => config('usora.file_upload_aws_s3_access_secret'),
                ],
                'region' => config('usora.file_upload_aws_s3_region'),
                'version' => 'latest',
            ]
        );

        $adapter = new AwsS3Adapter(
            $client, config('usora.file_upload_aws_s3_bucket')
        );

        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $requestWithFileUrls = null;

        foreach (config('usora.allowed_file_upload_request_keys') as $key => $options) {
            if (!empty($request->file($key))) {
                $path = '/' . trim($options['path'], '/') . '/';
                $fileName = $this->getFileName($request, $key);

                $this->processFile($request, $key);

                $uploadedSuccessfully = $this->filesystem->put(
                    $path . $fileName,
                    file_get_contents($request->file($key)),
                    ['visibility' => 'public']
                );

                $url = config('usora.file_upload_aws_s3_bucket_cloud_front_url') . $path . $fileName;

                // we must replace the entire request class instance since laravel doesnt let you override file inputs
                if ($uploadedSuccessfully) {

                    $request->files->remove($key);

                    $requestWithFileUrls = Request::createFrom($request);

                    $requestWithFileUrls->attributes->set($key, $url);
                    $requestWithFileUrls->request->set($key, $url);

                    app()->instance('request', $requestWithFileUrls);

                } else {
                    error_log('Usora failed to upload file: ' . $path . $fileName);
                }
            }
        }

        return $next($requestWithFileUrls ?? $request);
    }

    /**
     * @param $request
     * @param $key
     */
    protected function processFile($request, $key)
    {

    }

    protected function getFileName($request, $key)
    {
        if (auth()->check()) {
            return time() .
                '_' .
                auth()->id() .
                '_' .
                $key .
                '.' .
                $request->file($key)
                    ->extension();
        }

        return time() .
            '_' .
            $key .
            '.' .
            $request->file($key)
                ->extension();
    }
}
