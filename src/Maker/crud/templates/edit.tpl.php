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
    
    <form method="post" action="{{ path('<?= $route_name ?>_delete', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" onsubmit="return confirm('Tem certeza de que quer apagar este registro?');" class="text-right mt-6">
            <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>) }}">
            <button type="submit" class="btn-danger" id="submit-btn">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                Apagar registro
            </button>
        </form>
{% endblock %}
