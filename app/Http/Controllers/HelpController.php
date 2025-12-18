<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Mostrar el centro de ayuda
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categorySlug = $request->get('category');

        // Obtener categorías activas
        $categories = HelpCategory::active()->withCount('articles')->get();

        // Artículos destacados
        $featuredArticles = HelpArticle::featured()->limit(6)->get();

        // Buscar artículos
        if ($search) {
            $articles = HelpArticle::search($search)->paginate(10);
            $title = "Resultados de búsqueda: {$search}";
        } elseif ($categorySlug) {
            $category = HelpCategory::where('slug', $categorySlug)->firstOrFail();
            $articles = $category->articles()->paginate(10);
            $title = $category->name;
        } else {
            $articles = HelpArticle::active()->paginate(10);
            $title = 'Todos los artículos';
        }

        // Artículos más vistos
        $popularArticles = HelpArticle::active()
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();

        return view('help.index', compact(
            'categories',
            'featuredArticles',
            'articles',
            'popularArticles',
            'title',
            'search',
            'categorySlug'
        ));
    }

    /**
     * Mostrar un artículo específico
     */
    public function show($slug)
    {
        $article = HelpArticle::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Incrementar vistas
        $article->incrementViews(auth()->id());

        // Artículos relacionados
        $relatedArticles = $article->getRelatedArticles(4);

        // Categoría del artículo
        $category = $article->category;

        return view('help.show', compact('article', 'relatedArticles', 'category'));
    }

    /**
     * Obtener ayuda contextual por módulo (AJAX)
     */
    public function contextual(Request $request)
    {
        $module = $request->get('module');

        if (!$module) {
            return response()->json(['articles' => []]);
        }

        $articles = HelpArticle::byModule($module)
            ->limit(5)
            ->get(['id', 'title', 'slug', 'summary']);

        return response()->json(['articles' => $articles]);
    }

    /**
     * API para búsqueda rápida (typeahead)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');

        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $articles = HelpArticle::search($search)
            ->limit(10)
            ->get(['id', 'title', 'slug', 'summary']);

        return response()->json(['results' => $articles]);
    }
}
