<?php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DecryptFieldsScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $decryptableFields = [
            'firstname',
            'lastname',
            'username',
            'profile_url',
            'email',
            // Add more fields as needed
        ];

        foreach ($decryptableFields as $field) {
            $builder->where($field, $model::decrypt($builder->getModel()->getAttribute($field)));
        }
    }

    public function registerModelEvent($model)
    {
        $model->retrieved(function ($model) {
            $this->apply($model->newQuery(), $model);
        });
    }

    public static function register()
    {
        static::registerModelEvent(new DecryptFieldsScope());
    }
}

DecryptFieldsScope::register();
