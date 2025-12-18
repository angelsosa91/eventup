@extends('layouts.app')

@section('title', $article->title)
@section('page-title', 'Centro de Ayuda')

@section('content')
<div class="help-article">
    <div class="row">
        <div class="col-md-9">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('help.index') }}">
                            <i class="bi bi-house"></i> Ayuda
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('help.index', ['category' => $category->slug]) }}">
                            {{ $category->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ $article->title }}</li>
                </ol>
            </nav>

            <!-- Artículo -->
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Header -->
                    <div class="mb-4">
                        <h1 class="mb-3">{{ $article->title }}</h1>
                        <div class="d-flex gap-2 align-items-center mb-3">
                            <span class="badge bg-primary">{{ $category->name }}</span>
                            @if($article->tags)
                                @foreach($article->tags as $tag)
                                <span class="badge bg-secondary">{{ $tag }}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-eye"></i> {{ $article->views }} vistas
                            <span class="mx-2">•</span>
                            <i class="bi bi-calendar"></i> Actualizado {{ $article->updated_at->diffForHumans() }}
                        </div>
                    </div>

                    <!-- Video (si existe) -->
                    @if($article->video_url)
                    <div class="mb-4">
                        <div class="ratio ratio-16x9">
                            @if(str_contains($article->video_url, 'youtube.com') || str_contains($article->video_url, 'youtu.be'))
                                @php
                                    $videoId = null;
                                    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $article->video_url, $matches)) {
                                        $videoId = $matches[1];
                                    } elseif (preg_match('/youtu\.be\/([^?]+)/', $article->video_url, $matches)) {
                                        $videoId = $matches[1];
                                    }
                                @endphp
                                @if($videoId)
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}"
                                        allowfullscreen></iframe>
                                @endif
                            @else
                            <video controls>
                                <source src="{{ $article->video_url }}" type="video/mp4">
                            </video>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Contenido -->
                    <div class="article-content">
                        {!! nl2br(e($article->content)) !!}
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 text-muted small">¿Te resultó útil este artículo?</p>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="rateArticle('yes')">
                                <i class="bi bi-hand-thumbs-up"></i> Sí
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="rateArticle('no')">
                                <i class="bi bi-hand-thumbs-down"></i> No
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Artículos Relacionados -->
            @if($relatedArticles->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Artículos Relacionados</h5>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($relatedArticles as $related)
                    <a href="{{ route('help.show', $related->slug) }}"
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $related->title }}</h6>
                            <small class="text-muted">
                                <i class="bi bi-eye"></i> {{ $related->views }}
                            </small>
                        </div>
                        <p class="mb-1 text-muted small">{{ $related->excerpt }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-3">
            <!-- Navegación Rápida -->
            <div class="card mb-3">
                <div class="card-body">
                    <a href="{{ route('help.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-arrow-left"></i> Volver al Centro de Ayuda
                    </a>
                    <a href="{{ route('help.index', ['category' => $category->slug]) }}"
                       class="btn btn-outline-secondary w-100">
                        <i class="bi bi-folder"></i> Ver más de {{ $category->name }}
                    </a>
                </div>
            </div>

            <!-- Ayuda Adicional -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-question-circle"></i> ¿Necesitas más ayuda?</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-3">Si no encontraste lo que buscabas, contáctanos:</p>
                    <div class="d-grid gap-2">
                        <a href="mailto:soporte@neoerp.com" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-envelope"></i> Email
                        </a>
                        <a href="https://wa.me/595981234567" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.article-content {
    font-size: 1.05rem;
    line-height: 1.8;
}

.article-content h1,
.article-content h2,
.article-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1rem;
}

.article-content code {
    background-color: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}
</style>

<script>
function rateArticle(rating) {
    $.messager.show({
        title: 'Gracias',
        msg: 'Tu opinión nos ayuda a mejorar',
        timeout: 3000,
        showType: 'slide'
    });

    // Aquí podrías enviar la calificación al servidor
    console.log('Article rated:', rating);
}
</script>
@endsection
