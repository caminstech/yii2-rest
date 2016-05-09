<?php

namespace caminstech\rest;

use Yii;
use yii\base\Exception;

class RESTClient {
    const METHOD_POST   = 'post';
    const METHOD_GET    = 'get';
    const METHOD_PUT    = 'put';
    const METHOD_DELETE = 'delete';

    const HTTP_OK          = 200;
    const HTTP_CREATED     = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_NOT_FOUND   = 404;

    public $username = '';
    public $password = '';
    public $timeout = 10;
    public $sslVerify = true;

    public function get($url)
    {
        return $this->method(self::METHOD_GET, $url);
    }

    public function post($url, $parameters)
    {
        return $this->method(self::METHOD_POST, $url, $parameters);
    }

    public function put($url, $parameters)
    {
        return $this->method(self::METHOD_PUT, $url, $parameters);
    }

    public function delete($url, $parameters)
    {
        throw new Exception('Method not implemented');
    }

    private function method($method, $url, $parameters = [])
    {
        Yii::trace("RESTClient::method($method, $url)");

        $curl = curl_init($url);
        if (!empty($this->username) || !empty($this->password))
            curl_setopt($curl, CURLOPT_USERPWD, $this->username.':'.$this->password);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, ($this->sslVerify ? 2 : 0));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($this->sslVerify ? 1 : 0));
        if ($method == self::METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }
        if ($method == self::METHOD_PUT) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        $result = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        $curl_error = curl_error($curl);
        $curl_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($curl_errno > 0) {
            throw new Exception($curl_error, $curl_errno);
        }
        Yii::trace("RESTClient::method($method, $url) => httpCode=$curl_httpCode");
        return ['code' => $curl_httpCode, 'data' => $result];
    }
}
