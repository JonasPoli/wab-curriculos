- Variable names with a class instance should be the came case as the class name, e.g. `$templateService` for `TemplateService`.

- Evite inserir comentários em excesso, apenas em situações muito complexas. Se o código estiver autoexplicativo, não insira comentários. Nunca insira comentários em arquivos css.

- Se o usuário pedir para criar um crud de funcionalidade padrão/comum lembre-o da existência do comando `bin/console make:custom-crud` e peça para que ele confirme a geração pelo agente antes de continuar, não faça o crud sem confirmação.

- Performance and maintainability should be top priority when writing code.

- Use the standard Symfony way of doing things if possible.

- Never try to read .env.local

- The database used in the project is MySQL

- Don't run any phpunit command, as there are no tests in the project.

- Never try to read database data from .sql files nor with sql commands.

- if using icons in current task you may check docs/icons.md for the complete list of icons only if your knowledge and the code available in the files you read for the task are not enough.

- before creating utility or common functionality check if it does not exist in the project yet.

- before changing anything in the entities that would result in changes in the database's structure, **ask the user for confirmation** and **show the consequences of the change** in a very brief summary.


Siga o padrão abaixo para rotas da área administrativa quando possível:
```
admin_{entidade}_{ação}
```
Exemplos:
- `admin_user_index`
- `admin_user_new`
- `admin_user_edit`
- `admin_user_delete`

Siga o padrão abaixo para rotas da área pública:
```
app_{pagina}
```
Exemplos:
- `app_home`


custom styles should be in `assets/styles/app.css`

### Para a área administrativa, siga o padrão estético abaixo para os botões indicados

```html
<!-- salvar -->
<button class="btn-primary">
    <i class="fa-solid fa-check" aria-hidden="true"></i>
    Salvar
</button>

<!-- voltar -->
<button class="btn-secondary">
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    Voltar
</button>

<!-- apagar -->
<button class="btn-danger">
    <i class="fa-solid fa-trash" aria-hidden="true"></i>
    Excluir
</button>

<!-- Largura total (destaque) -->
<button class="btn-primary w-full justify-center">
    Enviar
</button>
```