## MODIFIED Requirements

### Requirement: Exclusão de tenant
O sistema SHALL excluir o tenant só com token CSRF válido e só quando ele não tem nenhum usuário do /admin; nesse caso, apaga em cascata os candidatos (com experiências, formações e cargos escolhidos), as áreas de interesse com os cargos, as pesquisas salvas e os pedidos de exclusão do tenant. Com usuários, a exclusão MUST ser recusada sem apagar nada, e nenhum usuário MUST ficar sem tenant por causa da exclusão de um tenant.

#### Scenario: Tenant excluído com usuário
- **WHEN** o Super Admin tenta excluir um tenant que tem um usuário administrador
- **THEN** o tenant, os candidatos, as áreas, os cargos e o usuário continuam como estavam
- **AND** aparece "Este tenant tem 1 usuário(s). Exclua os usuários ou mova-os para outro tenant antes de excluir o tenant."

#### Scenario: Tenant sem usuários
- **WHEN** o Super Admin exclui um tenant que não tem usuários
- **THEN** o tenant, os candidatos, as áreas, os cargos, as pesquisas salvas e os pedidos de exclusão dele são apagados
- **AND** aparece "Tenant excluído."

#### Scenario: Usuário de tenant não vira Super Admin
- **WHEN** um tenant que tinha usuários é excluído depois de o Super Admin excluir os usuários dele
- **THEN** não existe usuário sem tenant além dos Super Admins que já existiam antes
