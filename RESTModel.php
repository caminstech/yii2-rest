<?php

namespace caminstech\rest;

use Yii;

abstract class RESTModel extends \yii\base\Model
{
    private $isNewRecord = true;

    abstract protected static function getClient();

    abstract protected static function getUrl();

    public static function primaryKey()
    {
        return 'id';
    }

    protected static function getViewUrl($id)
    {
        return static::getUrl().'/'.$id;
    }

    protected static function getListUrl()
    {
        return static::getUrl();
    }

    public function getIsNewRecord()
    {
        return $this->isNewRecord;
    }

    public static function findById($id)
    {
        $response = static::getClient()->get(self::getViewUrl($id), $validCodes = [ RESTClient::HTTP_OK, RESTClient::HTTP_NOT_FOUND ]);
        if ($response['code'] == RESTClient::HTTP_NOT_FOUND) {
            return null;
        }
        $response['data'] = json_decode($response['data'], true);

        $classname = self::className();
        $model = new $classname();
        foreach($response['data'] as $attribute => $value) {
            $model->$attribute = $value;
        }
        $model->isNewRecord = false;
        return $model;
    }

    public static function findAll()
    {
        $response = static::getClient()->get(self::getListUrl(), $validCodes = [ RESTClient::HTTP_OK ]);
        $response['data'] = json_decode($response['data'], true);

        $models = [];
        $classname = self::className();
        foreach($response['data'] as $elem) {
            $model = new $classname();
            foreach($elem as $attribute => $value) {
                $model->$attribute = $value;
            }
            $model->isNewRecord = false;
            $models[] = $model;
        }
        return $models;
    }
}
