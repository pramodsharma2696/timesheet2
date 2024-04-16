<?php

namespace App\Http\Controllers\Helper;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait ModelUpdateHelper
{

    public function saveModel(Model $setting, ...$additionalModels)
    {

        foreach ($additionalModels as $model) {
            throw_unless($model instanceof Model, "Additional Parameters should be models");
        }

        $up = [];
        $meta = $setting->meta ?? [];

        $registered_columns = Schema::getColumnListing($setting->getTable());
        $fill_c = array_fill_keys($registered_columns, null);

        $up = [...array_intersect_key($setting->toArray(), $fill_c)];

        $t = array_diff_key($setting->toArray(), $fill_c);

        $meta = array_merge($meta, $t);
        //    dd($up);
        if (in_array("meta", $registered_columns)) {
            $up['meta'] = $meta;
        }
        $setting = $setting::create($up);
        //  $setting->save($up);

        foreach ($additionalModels as $model) {
            $model->save($up);
        }

        return $setting;
    }



    public function updateModel(array $input, Model $setting, ...$additionalModels):Bool
    {


        foreach ($additionalModels as $model) {
            throw_unless($model instanceof Model, "Additional Parameters should be models");
        }

        $up = [];
        $meta = $setting->meta ?? [];

        $registered_columns = Schema::getColumnListing($setting->getTable());
        $fill_c = array_fill_keys($registered_columns, null);

        $up = [...array_intersect_key($input, $fill_c)];

        $t = array_diff_key($input, $fill_c);

        $meta = array_merge($meta, $t);

        if (in_array("meta", $registered_columns)) {
            $up['meta'] = $meta;
        }

        if ($setting->update($up)) {
            foreach ($additionalModels as $model) {
                $model->update($up);
            }
            return true;
        }

        return false;
    }


    public function saveModelForce(array $input, Model $setting, ...$additionalModels)
    {

        foreach ($additionalModels as $model) {
            throw_unless($model instanceof Model, "Additional Parameters should be models");
        }

        $up = [];
        $meta = $setting->meta ?? [];

        $registered_columns = Schema::getColumnListing($setting->getTable());
        $fill_c = array_fill_keys($registered_columns, null);

        $up = [...array_intersect_key($input, $fill_c)];

        $t = array_diff_key($input, $fill_c);

        $meta = array_merge($meta, $t);

        if (in_array("meta", $registered_columns)) {
            $up['meta'] = $meta;
        }

        $setting->updateOrCreate($setting->toArray(),$up);

        foreach ($additionalModels as $model) {
            $model->save($up);
        }

        return true;
    }
}
