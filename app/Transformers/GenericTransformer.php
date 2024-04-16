<?php

namespace App\Transformers;

use App\Generic;
use App\Traits\Helpers\CrudPermissionTrait;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Database\Eloquent\Model;

class GenericTransformer extends Transformer
{use CrudPermissionTrait;
    public $details;
    public function __construct(bool $details = false)
    {
        $this->details = $details;
    }
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Generic $generic
     * @return array
     */
    public function transform(Model $generic)
    {
        $meta = $generic->meta;
        $permission = $generic->permission;
        unset($generic->meta, $generic->permission);

       $data = $generic->toArray();

        if ($this->details) {
            $data = [
                ...$generic->toArray(), ...$meta
            ];
        }
        if ($permission) {
            $data['permission']= $this->transformArray(json_decode($permission));
        }

        return $data;
    }
}
