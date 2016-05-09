<?php

namespace caminstech\rest;

use Yii;

use yii\helpers\ArrayHelper;
use yii\data\BaseDataProvider;

class RESTDataProvider extends BaseDataProvider
{
    /**
     * @var string the column name that is used as the key of the data models.
     * If this is not set, the index of the [[models]] array will be used.
     * @see getKeys()
     */
    public $key;

    /**
     * @var string the name of the [[yii\base\Model|Model]] class that will be represented.
    */
    public $modelClass;

    private $allModels;
    private $filter = [];

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        $modelClass = $this->modelClass;
        $this->allModels = $modelClass::findAll();
        $this->allModels = $this->filterModels($this->allModels, $this->filter);

        if (($models = $this->allModels) === null) {
            return [];
        }
        if (($sort = $this->getSort()) !== false) {
            $models = $this->sortModels($models, $sort);
        }
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->getPageSize() > 0) {
                $models = array_slice($models, $pagination->getOffset(), $pagination->getLimit(), true);
            }
        }
        return $models;
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        return count($this->allModels);
    }

    /**
     * Sorts the data models according to the given sort definition
     * @param array $models the models to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted data models
     */
    protected function sortModels($models, $sort)
    {
        $orders = $sort->getOrders();
        if (!empty($orders)) {
            ArrayHelper::multisort($models, array_keys($orders), array_values($orders));
        }
        return $models;
    }

    /**
     * Add a new string filter to the data models
     * @param string $attribute the name of the attribute to filter.
     * @param string $value the value of the attribute to filter.
     * @param boolean $strict whether the comparation has to be strict or partial and case insensitive.
     * @return array the sorted data models
     */
    public function addFilter($attribute, $value, $strict = false)
    {
        if (empty($value))
            return;
        $this->filter[] = ['attribute' => $attribute, 'value' => $value, 'strict' => $strict];
    }

    private function filterModels($models, $filters)
    {
        $result = [];
        foreach ($models as $model) {
            $filtered = false;
            foreach ($filters as $filter) {
                $attribute = $filter['attribute'];
                $value = $filter['value'];
                $strict = $filter['strict'];
                $filtered = $filtered || ($strict && $model->$attribute != $value) || (!$strict && stripos($model->$attribute, $value) === false);
            }
            if (!$filtered)
                $result[] = $model;
        }
        return $result;
    }
}
