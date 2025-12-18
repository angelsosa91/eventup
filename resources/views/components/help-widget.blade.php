@props(['module' => null])

<!-- Widget de Ayuda Flotante -->
<div class="help-widget">
    <button class="help-widget-button" onclick="toggleHelpWidget()" title="Ayuda">
        <i class="bi bi-question-circle"></i>
    </button>

    <div class="help-widget-panel" id="helpWidgetPanel" style="display: none;">
        <div class="help-widget-header">
            <h6 class="mb-0">
                <i class="bi bi-life-preserver"></i> Ayuda
            </h6>
            <button class="btn-close btn-close-white" onclick="toggleHelpWidget()"></button>
        </div>

        <div class="help-widget-body">
            @if($module)
            <!-- Ayuda Contextual -->
            <div class="mb-3">
                <p class="small text-muted mb-2">Artículos de ayuda para este módulo:</p>
                <div id="contextualHelp">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            @endif

            <!-- Enlaces Rápidos -->
            <div class="mb-3">
                <p class="small fw-bold mb-2">Enlaces Rápidos:</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-house"></i> Centro de Ayuda
                    </a>
                    <a href="{{ route('help.index', ['category' => 'primeros-pasos']) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-rocket"></i> Primeros Pasos
                    </a>
                </div>
            </div>

            <!-- Búsqueda Rápida -->
            <div class="mb-3">
                <p class="small fw-bold mb-2">Buscar en la ayuda:</p>
                <input type="text"
                       class="form-control form-control-sm"
                       id="helpQuickSearch"
                       placeholder="Escribe tu pregunta..."
                       onkeyup="searchHelpArticles(this.value)">
                <div id="helpSearchResults" class="mt-2"></div>
            </div>

            <!-- Contacto -->
            <div class="help-widget-footer">
                <p class="small text-muted mb-2">¿No encuentras lo que buscas?</p>
                <div class="d-grid gap-1">
                    <a href="mailto:soporte@neoerp.com" class="btn btn-sm btn-outline-success">
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

<style>
.help-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.help-widget-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.help-widget-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.6);
}

.help-widget-panel {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 350px;
    max-height: 600px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.help-widget-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.help-widget-body {
    padding: 15px;
    max-height: 500px;
    overflow-y: auto;
}

.help-widget-footer {
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

.help-article-item {
    padding: 8px;
    border-left: 3px solid #667eea;
    background: #f8f9fa;
    margin-bottom: 8px;
    border-radius: 4px;
    transition: all 0.2s;
}

.help-article-item:hover {
    background: #e9ecef;
    border-left-color: #764ba2;
}

.help-article-item a {
    text-decoration: none;
    color: #333;
    font-size: 13px;
    font-weight: 500;
}

.help-article-item small {
    display: block;
    color: #6c757d;
    font-size: 11px;
    margin-top: 2px;
}
</style>

<script>
function toggleHelpWidget() {
    const panel = document.getElementById('helpWidgetPanel');
    if (panel.style.display === 'none') {
        panel.style.display = 'block';

        @if($module)
        // Cargar ayuda contextual
        loadContextualHelp('{{ $module }}');
        @endif
    } else {
        panel.style.display = 'none';
    }
}

@if($module)
function loadContextualHelp(module) {
    $.ajax({
        url: '{{ route('help.contextual') }}',
        data: { module: module },
        success: function(response) {
            const container = $('#contextualHelp');
            if (response.articles && response.articles.length > 0) {
                let html = '';
                response.articles.forEach(article => {
                    html += `
                        <div class="help-article-item">
                            <a href="/help/${article.slug}" target="_blank">
                                ${article.title}
                            </a>
                            ${article.summary ? `<small>${article.summary}</small>` : ''}
                        </div>
                    `;
                });
                container.html(html);
            } else {
                container.html('<p class="small text-muted">No hay artículos específicos para este módulo.</p>');
            }
        },
        error: function() {
            $('#contextualHelp').html('<p class="small text-danger">Error al cargar ayuda.</p>');
        }
    });
}
@endif

let searchTimeout;
function searchHelpArticles(query) {
    clearTimeout(searchTimeout);

    if (query.length < 2) {
        $('#helpSearchResults').html('');
        return;
    }

    searchTimeout = setTimeout(function() {
        $.ajax({
            url: '{{ route('help.search') }}',
            data: { q: query },
            success: function(response) {
                const container = $('#helpSearchResults');
                if (response.results && response.results.length > 0) {
                    let html = '';
                    response.results.forEach(article => {
                        html += `
                            <div class="help-article-item">
                                <a href="/help/${article.slug}" target="_blank">
                                    ${article.title}
                                </a>
                                ${article.summary ? `<small>${article.summary}</small>` : ''}
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html('<p class="small text-muted">No se encontraron resultados.</p>');
                }
            }
        });
    }, 300);
}

// Cerrar al hacer click fuera
document.addEventListener('click', function(event) {
    const widget = document.querySelector('.help-widget');
    const panel = document.getElementById('helpWidgetPanel');

    if (widget && panel && !widget.contains(event.target) && panel.style.display === 'block') {
        panel.style.display = 'none';
    }
});
</script>
