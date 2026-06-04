<?php
// Application common helpers.

if (!function_exists('httpRequest')) {
    /**
     * Send an HTTP request.
     * Supported options: url, type, data, dataType, header.
     */
    function httpRequest($options)
    {
        if (empty($options['url'])) {
            throw new \app\lib\exception\ErrorException(['msg' => 'request url is required']);
        }

        $type = strtolower((string)($options['type'] ?? 'get'));
        $client = curl_init();

        switch ($type) {
            case 'get':
                $query = '';
                if (isset($options['data']) && !empty($options['data'])) {
                    $query = http_build_query($options['data']);
                }
                $join = str_contains((string)$options['url'], '?') ? '&' : '?';
                $url = $query === '' ? $options['url'] : $options['url'] . $join . $query;
                curl_setopt($client, CURLOPT_URL, $url);
                break;
            case 'post':
                curl_setopt($client, CURLOPT_URL, $options['url']);
                curl_setopt($client, CURLOPT_POST, true);
                if (isset($options['data'])) {
                    curl_setopt($client, CURLOPT_POSTFIELDS, $options['data']);
                }
                break;
            default:
                throw new \app\lib\exception\ErrorException(['msg' => 'unsupported request type']);
        }

        if (isset($options['header'])) {
            if (!is_array($options['header'])) {
                throw new \app\lib\exception\ErrorException(['msg' => 'request header must be an array']);
            }
            curl_setopt($client, CURLOPT_HTTPHEADER, $options['header']);
        }

        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_TIMEOUT, 10);

        $scheme = parse_url((string)$options['url'], PHP_URL_SCHEME);
        if ($scheme === 'https') {
            curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
        }

        $content = curl_exec($client);
        curl_close($client);

        $dataType = $options['dataType'] ?? 'json';
        switch ($dataType) {
            case 'json':
            case 'assoc':
                return json_decode($content, true);
            case 'text':
            default:
                return $content;
        }
    }
}

if (!function_exists('uploadFile')) {
    /**
     * Upload one or more request files to the public filesystem disk.
     */
    function uploadFile($fileName, $pathName = '', $validate = '')
    {
        try {
            $files = request()->file();

            if (!isset($files[$fileName]) || empty($files[$fileName])) {
                throw new \Exception('upload file not found');
            }

            if (is_array($files[$fileName])) {
                if ($validate) {
                    validate([$fileName => $validate])->check($files);
                }

                $saveNames = [];
                foreach ($files[$fileName] as $file) {
                    $saveNames[] = \think\facade\Filesystem::disk('public')->putFile($pathName, $file);
                }

                return array_map(function ($saveName) {
                    return \think\facade\Filesystem::getDiskConfig('public')['url'] . '/' . $saveName;
                }, $saveNames);
            }

            if ($validate) {
                validate([$fileName => $validate])->check([$fileName => $files[$fileName]]);
            }

            $saveName = \think\facade\Filesystem::disk('public')->putFile($pathName, $files[$fileName]);
            return \think\facade\Filesystem::getDiskConfig('public')['url'] . '/' . $saveName;
        } catch (\Exception $e) {
            throw new \app\lib\exception\ErrorException([
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
