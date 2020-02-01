<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;
use Laratrust\Contracts\LaratrustPermissionInterface;
use Laratrust\Traits\LaratrustPermissionTrait;

class Permission extends BaseModel implements LaratrustPermissionInterface
{
    use LaratrustPermissionTrait;

    /**
     * @var int Auto increments integer key
     */
    public $primaryKey = 'permission_id';

    /**
     * @var string UUID key
     */
    public $uuidKey = 'permission_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'description'
    ];

    /**
     * Creates a new instance of the model.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('laratrust.tables.permissions');
    }
}
