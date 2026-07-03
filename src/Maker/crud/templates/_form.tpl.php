{{ form_start(form) }}
    <div class="admin-card">
        {{ form_widget(form) }}
    </div>
    <div class="w-full text-right">
        <button type="submit" class="btn-primary" id="submit-btn">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Salvar registro
        </button>
    </div>
{{ form_end(form) }}