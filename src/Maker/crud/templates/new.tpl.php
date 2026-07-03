{% extends 'admin/base.html.twig' %}

{% block body %}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fa-solid fa-rectangle-list" style="margin-right:8px;color:var(--bm-accent)" aria-hidden="true"></i>
                <?php echo $displayName; ?>
            </h1>
            <p class="page-subtitle"></p>
        </div>
        <a href="{{ path('<?= $route_name ?>_index') }}" class="btn-secondary">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Voltar à listagem
        </a>
    </div>
        
    {{ include('<?= $templates_path ?>/_form.html.twig') }}

{% endblock %}
