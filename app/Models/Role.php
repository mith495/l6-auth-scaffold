<?php

namespace App\Models;

use Illuminate\Support\Facades\Config;
use Laratrust\Contracts\LaratrustRoleInterface;
use Laratrust\Traits\LaratrustRoleTrait;

class Role extends BaseModel implements LaratrustRoleInterface
{
    use LaratrustRoleTrait;

    const ROLE_MEMBER = 'member';
    const ROLE_ADMIN = 'admin';

    /**
     * All available roles
     */
    const ALL_ROLES = [
        [
            'name' => self::ROLE_MEMBER,
            'display_name' => self::ROLE_MEMBER,
            'description' => 'Role assigned to member users.'
        ],
        [
            'name' => self::ROLE_ADMIN,
            'display_name' => self::ROLE_ADMIN,
            'description' => 'Role assigned to admin users.'
        ]
    ];

    /**
     * @var int Auto increments integer key
     */
    public $primaryKey = 'role_id';

    /**
     * @var string UUID key
     */
    public $uuidKey = 'role_uuid';

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
        $this->table = Config::get('laratrust.tables.roles');
    }

    public static function boot()
    {
        parent::boot();

        static::roleAttached(function($role, $permission)
        {
            // @todo
        });

        static::roleDetached(function($role, $permission)
        {
            // @todo
        });
    }
}
