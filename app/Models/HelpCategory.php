<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación con los artículos
     */
    public function articles()
    {
        return $this->hasMany(HelpArticle::class)->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
