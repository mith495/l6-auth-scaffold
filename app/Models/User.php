<?php
namespace App\Models;

use App\Notifications\Password\CanResetPassword;
use Hash;
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Services\Auth\MustVerifyEmail as VerifyEmail;
use Laratrust\Traits\LaratrustUserTrait;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    JWTSubject,
    MustVerifyEmail
{
    use Authenticatable,
        CanResetPassword,
        Notifiable,
        VerifyEmail;

    use LaratrustUserTrait;

    // conflict resolution with Authorizable::can()
    use Authorizable {
        Authorizable::can insteadof LaratrustUserTrait;
        LaratrustUserTrait::can as laratrustCan;
    }

    /**
     * @var int Auto increments integer key
     */
    public $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'active',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays and API output
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Model's boot function
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function (self $user) {
            // Hash user password, if not already hashed
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($user->password);
            }
        });

        static::roleAttached(function($user, $role, $team)
        {
            // @todo:
        });

        static::roleDetached(function($user, $role, $team)
        {
            // @todo
        });
    }

    /**
     * Returns associated social details
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function socials()
    {
        return $this->hasMany(SocialUser::class, 'user_id', 'user_id');
    }

    /**
     * Return the validation rules for this model
     *
     * @return array Rules
     */
    public function getValidationRules()
    {
        return [
            'email' => 'email|max:255|unique:users',
            'first_name'  => 'required|min:3',
            'last_name'  => 'required|min:3',
            'password' => 'required|min:6'
        ];
    }

    /**
     * For Authentication
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * For Authentication
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'user_id' => $this->getKey(),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email
            ],
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Activates the user instance
     *
     * @return void
     */
    public function activate()
    {
        $this->active = true;
        $this->save();
    }

    /**
     * Activates member by assigning member role to the user
     */
    public function activateMembership()
    {
        $roleMember = Role::whereName(Role::ROLE_MEMBER)->first();

        // attachRole comes from LaratrustUserTrait
        $this->attachRole($roleMember);
        // @todo: attach permissions to the member role
    }

    /**
     * @param $service
     * @return bool
     */
    public function hasSocialLinked($service)
    {
        // @todo: check how exists() can be used here
        return (bool) $this->socials->where('provider', $service)->count();
    }

    /**
     * Returns a list of fields updatable in the profile update request
     * Password and email update would be explicit update request for that field
     *
     * @return array
     */
    public function getUpdatableFields()
    {
        return [
            'first_name', 'last_name'
        ];
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return true === $this->active;
    }

    /**
     * Checks if the user's role is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isRole(Role::ROLE_ADMIN);
    }

    /**
     * Checks if the user's role is super admin
     *
     * @return mixed
     */
    public function isSuperAdmin()
    {
        return $this->isRole(Role::ROLE_SUPER_ADMIN);
    }

    /**
     * Checks if the user's role is member
     *
     * @return mixed
     */
    public function isMember()
    {
        return $this->isRole(Role::ROLE_MEMBER);
    }

    /**
     * Checks if the user's role is moderator
     *
     * @return mixed
     */
    public function isModerator()
    {
        return $this->isRole(Role::ROLE_MODERATOR);
    }

    /**
     * Checks if the user has the given role
     *
     * @param $roleName
     * @return mixed
     */
    public function isRole($roleName)
    {
        return $this
            ->roles
            ->pluck('name')
            ->contains($roleName);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}
