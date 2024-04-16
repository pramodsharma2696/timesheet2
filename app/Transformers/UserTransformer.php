<?php

namespace App\Transformers;

use App\Models\User;
use Flugg\Responder\Transformers\Transformer;

class UserTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [ 'organisation', 'token'];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => (int) $user['id'],
            'finm' => $user['finm'],
            'lamn' => $user['lamn'],
            'phnb' => $user['phnb'],
            'unmn' => $user['unmn'],
            'orgn' => $user['orgn'],
            'user_id' => $user['billsby_id'],
            'email' => $user['email'],

        ];
    }

    public function includeToken(User $user)
    {
        return [cache($user->unmn)];
    }

    public function includeOrganisation(User $user)
    {
        return $user->currentTeam();
    }
}
