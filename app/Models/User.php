<?php

namespace App\Models;

use App\Models\Traits\Attribute\UserAttribute;
use App\Models\Traits\Scope\SearchScope;
use App\Notifications\ResetPasswordUser;
use App\Utilities\CodeGenerator;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use UserAttribute;
    use CanResetPassword;
    use SearchScope;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'credit_card_id',
        'zip_code',
        'city_code',
        'city',
        'prefecture_code',
        'prefecture',
        'address',
        'building_name',
        'status_user_id',
        'is_checked_tutorial',
        'latitude',
        'longitude',
        'using_app',
        'deleted_at'
    ];

    protected $with = [
        'status',
        'places',
    ];

    protected $appends = [
        'gmo_member_id'
    ];

    /**
     * Get the user's booking cancellation.
     */
    public function cancellation()
    {
        return $this->morphOne(BookingCancellation::class, 'cancellationable');
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->code = CodeGenerator::generate('TCU', 'users', $model->city_code, 7);
        });
    }

    /**
     * @return BelongsToMany
     */
    public function places(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'place_users', 'user_id', 'place_id')->withPivot('user_id');
    }

    /**
     * @param bool $trashed
     * @return BelongsToMany
     */
    public function chargers(bool $trashed = false): BelongsToMany
    {
        return $this->belongsToMany(Charger::class, 'charger_users', 'user_id', 'charger_id')
            ->when($trashed, function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('charger_users.deleted_at')
                        ->orWhereNull('charger_users.deleted_at');
                });
            }, function ($q) {
                $q->whereNull('charger_users.deleted_at');
            })
            ->withPivot(['id', 'charging_fee', 'order_id'])
            ->withTimestamps();
    }

    /**
     * @return HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(UserStatus::class, 'id', 'status_user_id');
    }

    /**
     * @return HasOne
     */
    public function credit(): HasOne
    {
        return $this->hasOne(CreditCard::class, 'id', 'credit_card_id');
    }

    /**
     * @return HasOne
     */
    public function deviceToken(): HasOne
    {
        return $this->hasOne(DeviceToken::class);
    }

    /**
     * @return hasMany
     */
    public function chargingHistories(): HasMany
    {
        return $this->hasMany(ChargingHistory::class);
    }

    /**
     * @return hasMany
     */
    public function notices(): HasMany
    {
        return $this->hasMany(PersonalNotice::class);
    }

    /**
     * @param $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordUser($token, $this->email));
    }
}
