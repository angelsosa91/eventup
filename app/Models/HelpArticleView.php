<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpArticleView extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'help_article_id',
        'user_id',
        'ip_address',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Relación con el artículo
     */
    public function article()
    {
        return $this->belongsTo(HelpArticle::class, 'help_article_id');
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
