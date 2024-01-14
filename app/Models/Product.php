<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stichoza\GoogleTranslate\GoogleTranslate;
use StillCode\ArPhpLaravel\ArPhpLaravel;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')->withTimestamps();
    }

    public function latestSearches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'latest_searches', 'product_id', 'user_id')->withTimestamps();
    }

    public function is_fav($id): bool
    {
        $favs = User::find($id)->favorites;
        foreach ($favs as $fav) {
            if ($fav['id'] == $this['id']) return true;
        }
        return false;
    }

    /**
     * Filter the product
     *
     * @param Builder $query
     * @param array $filters
     * @return void
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['classification'] ?? false, function (Builder $query, $classification) {
            $query->where('classification_id', $classification);
        });

        $query->when($filters['company'] ?? false, function (Builder $query, $company) {
            $query->where('company_id', $company);
        });

        $query->when($filters['search_word'] ?? false, function (Builder $query, $word) {
            $query->where(function (Builder $query) use ($word) {
                if (auth()->user()->lang == 'en') {
                    $query
                        ->where('scientific_name', 'like', $word . "%")
                        ->orWhere('commercial_name', 'like', $word . '%');
                } else {
                    $query
                        ->where('scientific_name_ar', 'like', $word . "%")
                        ->orWhere('commercial_name_ar', 'like', $word . '%');
                }
            });
        });
    }

    private $columns = ['id', 'company_id', 'scientific_name', 'commercial_name', 'scientific_name_ar', 'commercial_name_ar', 'description', 'quantity', 'price', 'expiration_date', 'photo', 'number_of_sales', 'classification_id'];

    public function scopeExclude(Builder $query, array $values = [])
    {
        return $query->select(array_diff($this->columns, $values));
    }

    public function commercialNameAr()
    {
        return $this->commercial_name_ar;
    }

    public function commercialName(): Attribute
    {
        return Attribute::make(
            get: function ($name) {
                if (auth()->user()->lang == 'ar') {
                    return $this->commercialNameAr();
                }
                return $name;
            }
        );
    }

    public function scientificNameAr()
    {
        return $this->commercial_name_ar;
    }

    public function scientificName(): Attribute
    {
        return Attribute::make(
            get: function ($name) {
                if (auth()->user()->lang == 'ar') {
                    return $this->scientificNameAr();
                }
                return $name;
            }
        );
    }

    public function descriptionAr($description): string
    {
        $lang = new GoogleTranslate();
        return $lang->setSource('en')->setTarget('ar')->translate($description);
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: function (string $description) {

                if (auth()->user()->lang == 'ar') {
                    return $this->descriptionAr($description);
                }
                return $description;
            }
        );
    }
}
