<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'number_of_orders',
        'role'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];


    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')->withTimestamps();
    }

    public function latestSearches(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'latest_searches', 'user_id', 'product_id')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'user_id', 'permission_id');
    }

    public function myNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'id')->latest();
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }

    public function sendNotification(string $title, string $body): bool|string
    {

        $data = [
            'registration_ids' => [
                $this->notification_token
            ],
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ]
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization:key=' . env('SERVER_API_KEY'),
            'Content-Type:application/json'
        ];

        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($request, CURLOPT_POST, true);

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($request, CURLOPT_POSTFIELDS, $dataString);
        return curl_exec($request);

    }

}
