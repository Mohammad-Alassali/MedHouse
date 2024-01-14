<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Stichoza\GoogleTranslate\GoogleTranslate;
use StillCode\ArPhpLaravel\ArPhpLaravel;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = [
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function title(): Attribute
    {
        return Attribute::make(
            get: function ($title) {

                if (Auth::user()['lang'] == 'ar') {
                    return "الطلب " . $title;
                }
                return "Order " . $title;
            }
        );
    }

    public function body(): Attribute
    {
        return Attribute::make(
            get: fn($body) => trans('messages.' . $body)
        );
    }

    public function time(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this['created_at']->format('H:i')
        );
    }

    public function date(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this['created_at']->format('d/M/Y')
        );
    }
}
