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
    <div>
        <a href="{{ path('<?= $route_name ?>_new') }}" class="btn-primary">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Adicionar
        </a>
    </div>
</div>

<div class="admin-card">
    
    <table class="admin-table">
        <thead>
            <tr>
                <?php foreach ($entity_fields as $field): ?>
                    <th><?= ucfirst($field['fieldName']) ?></th>
                <?php endforeach; ?>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
                <tr>
                    <?php foreach ($entity_fields as $field): ?>
                        <?php if (!empty($field['enumType'])): ?>
                        <td>{{ <?= $entity_twig_var_singular ?>.<?= $field['fieldName'] ?>.label() }}</td>
                        <?php else: ?>
                        <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <td><a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn-secondary" aria-label="Editar {{ <?= $entity_twig_var_singular ?>.name }}" title="Editar {{ <?= $entity_twig_var_singular ?>.name }}"><i class="fa-solid fa-pen" aria-hidden="true"></i>Editar</a></td>
                </tr>
            {% else %}
                <tr>
                    <td colspan="<?= (count($entity_fields) + 1) ?>">Nenhum registro</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
</div>

{% endblock %}

{% block stylesheets %}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.dataTables.min.css">
{% endblock %}

{% block javascripts %}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <script>
        {% if <?= $entity_twig_var_plural ?> is not empty %}
            $(document).ready(function () {
            $('.admin-table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/pt-BR.json',
                },
                pageLength: 10,
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, 'Todos']],
                order: [[0, 'asc']],
            });
        {% endif %}
    });
    </script>
{% endblock %}