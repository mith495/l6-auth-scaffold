<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Database\Eloquent\Model;

class RestfulModel extends Model
{
    /**
     * Every model should have a primary UUID key, which will be returned to API consumers.
     *
     * @var string UUID key
     */
    public $primaryKey = '';

    /**
     * @var bool Set to false for UUID keys
     */
    public $incrementing = false;

    /**
     * @var string Set to string for UUID keys
     */
    protected $keyType = 'string';

    /**
     * These attributes (in addition to primary & uuid keys) are not allowed to be updated explicitly through
     *  API routes of update and put. They can still be updated internally by Laravel, and your own code.
     *
     * @var array Attributes to disallow updating through an API update or put
     */
    public $immutableAttributes = ['created_at', 'deleted_at'];

    /**
     * Acts like $with (eager loads relations), however only for immediate controller requests for that object
     * This is useful if you want to use "with" for immediate resource routes, however don't want these relations
     *  always loaded in various service functions, for performance reasons
     *
     * @var array Relations to load implicitly by Restful controllers
     */
    public static $localWith = [];

    /**
     * Return the validation rules for this model
     *
     * @return array Validation rules to be used for the model when creating it
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Return the validation rules for this model's update operations
     * In most cases, they will be the same as for the create operations
     *
     * @return array Validation roles to use for updating model
     */
    public function getValidationRulesUpdating()
    {
        return $this->getValidationRules();
    }

    /**
     * Return any custom validation rule messages to be used
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return [];
    }

    /**
     * Boot the model
     *
     * Add various functionality in the model lifecycle hooks
     */
    public static function boot()
    {
        parent::boot();

        // Add functionality for creating a model
        static::creating(function (self $model) {
            // If the PK(s) are missing, generate them
            $uuidKeyName = $model->getKeyName();

            if (! is_array($uuidKeyName) && ! array_key_exists($uuidKeyName, $model->getAttributes())) {
                $model->$uuidKeyName = Uuid::uuid4()->toString();
            }
        });

        // Add functionality for updating a model
        static::updating(function (self $model) {
            // Disallow updating UUID keys
            if ($model->getAttribute($model->getKeyName()) != $model->getOriginal($model->getKeyName())) {
                throw new BadRequestHttpException('Updating the UUID of a resource is not allowed.');
            }

            // Disallow updating immutable attributes
            if (! empty($model->immutableAttributes)) {
                // For each immutable attribute, check if they have changed
                foreach ($model->immutableAttributes as $attributeName) {
                    if ($model->getOriginal($attributeName) != $model->getAttribute($attributeName)) {
                        throw new BadRequestHttpException('Updating the "'.camel_case($attributeName).'" attribute is not allowed.');
                    }
                }
            }
        });
    }

    /**
     * When Laravel creates a new model, it will add any new attributes (such as UUID) at the end. When a create
     * operation such as a POST returns the new resource, the UUID will thus be at the end, which doesn't look nice.
     * For purely aesthetic reasons, we have this function to conduct a simple reorder operation to move the UUID
     * attribute to the head of the attributes array
     *
     * This will be used at the end of create-related controller functions
     *
     * @return void
     */
    public function orderAttributesUuidFirst()
    {
        if ($this->getKeyName()) {
            $UuidValue = $this->getKey();
            unset($this->attributes[$this->getKeyName()]);
            $this->attributes = [$this->getKeyName() => $UuidValue] + $this->attributes;
        }
    }

    /************************************************************
     * Extending Laravel Functions Below
     ***********************************************************/

    /**
     * We're extending the existing Laravel Builder
     *
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
