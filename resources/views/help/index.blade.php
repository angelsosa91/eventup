@extends('layouts.app')

@section('title', 'Centro de Ayuda')
@section('page-title', 'Centro de Ayuda')

@section('content')
<div class="help-center">
    <!-- Buscador Principal -->
    <div class="card mb-4">
        <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h2 class="text-white mb-3">¿En qué podemos ayudarte?</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form action="{{ route('help.index') }}" method="GET">
                        <div class="input-group input-group-lg">
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Buscar en la ayuda..."
                                   value="{{ $search ?? '' }}"
                                   autocomplete="off">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar de Categorías -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-folder"></i> Categorías</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('help.index') }}"
                       class="list-group-item list-group-item-action {{ !$categorySlug ? 'active' : '' }}">
                        <i class="bi bi-files"></i> Todos los artículos
                        <span class="badge bg-secondary float-end">{{ $categories->sum('articles_count') }}</span>
                    </a>
                    @foreach($categories as $category)
                    <a href="{{ route('help.index', ['category' => $category->slug]) }}"
                       class="list-group-item list-group-item-action {{ $categorySlug == $category->slug ? 'active' : '' }}">
                        <i class="bi {{ $category->icon ?? 'bi-folder' }}"></i> {{ $category->name }}
                        <span class="badge bg-secondary float-end">{{ $category->articles_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Artículos Populares -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-fire"></i> Más Visitados</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($popularArticles as $popular)
                    <a href="{{ route('help.show', $popular->slug) }}"
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <small>{{ $popular->title }}</small>
                            <small class="text-muted">{{ $popular->views }} <i class="bi bi-eye"></i></small>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-muted">
                        <small>No hay artículos populares aún</small>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-md-9">
            @if(!$search && !$categorySlug)
            <!-- Artículos Destacados -->
            <div class="mb-4">
                <h4 class="mb-3"><i class="bi bi-star-fill text-warning"></i> Artículos Destacados</h4>
                <div class="row">
                    @forelse($featuredArticles as $featured)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('help.show', $featured->slug) }}" class="text-decoration-none">
                                        {{ $featured->title }}
                                    </a>
                                </h6>
                                <p class="card-text text-muted small">{{ $featured->excerpt }}</p>
                                <a href="{{ route('help.show', $featured->slug) }}" class="btn btn-sm btn-outline-primary">
                                    Leer más <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <small class="text-muted">
                                    <i class="bi bi-eye"></i> {{ $featured->views }} vistas
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No hay artículos destacados disponibles.
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- Lista de Artículos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $title }}</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($articles as $article)
                    <a href="{{ route('help.show', $article->slug) }}"
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $article->title }}</h6>
                                <p class="mb-1 text-muted small">{{ $article->excerpt }}</p>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-primary">{{ $article->category->name }}</span>
                                    @if($article->video_url)
                                    <span class="badge bg-danger"><i class="bi bi-play-circle"></i> Video</span>
                                    @endif
                                    @if($article->tags)
                                        @foreach($article->tags as $tag)
                                        <span class="badge bg-secondary">{{ $tag }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted">
                                    <i class="bi bi-eye"></i> {{ $article->views }}
                                </small>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-center py-5">
                        <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No se encontraron artículos</p>
                        @if($search)
                        <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todos los artículos
                        </a>
                        @endif
                    </div>
                    @endforelse
                </div>
                @if($articles->hasPages())
                <div class="card-footer">
                    {{ $articles->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.help-center .list-group-item.active {
    background-color: #667eea;
    border-color: #667eea;
}
</style>
@endsection
