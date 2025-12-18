<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HelpArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_category_id',
        'title',
        'slug',
        'summary',
        'content',
        'module',
        'video_url',
        'order',
        'views',
        'is_featured',
        'is_active',
        'tags',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }

    /**
     * Relación con las vistas
     */
    public function articleViews()
    {
        return $this->hasMany(HelpArticleView::class);
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews($userId = null)
    {
        $this->increment('views');

        HelpArticleView::create([
            'help_article_id' => $this->id,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Generar slug automáticamente
     */
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Scope para artículos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    /**
     * Scope para artículos destacados
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /**
     * Scope para artículos por módulo
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module)->where('is_active', true);
    }

    /**
     * Buscar artículos
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('content', 'LIKE', "%{$search}%")
              ->orWhere('summary', 'LIKE', "%{$search}%")
              ->orWhereJsonContains('tags', $search);
        })->where('is_active', true);
    }

    /**
     * Obtener artículos relacionados
     */
    public function getRelatedArticles($limit = 5)
    {
        return static::where('help_category_id', $this->help_category_id)
            ->where('id', '!=', $this->id)
            ->active()
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener extracto del contenido
     */
    public function getExcerptAttribute()
    {
        return $this->summary ?: Str::limit(strip_tags($this->content), 150);
    }
}
