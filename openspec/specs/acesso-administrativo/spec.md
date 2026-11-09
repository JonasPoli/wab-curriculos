# acesso-administrativo Specification

## Purpose
Login, perfis e senhas dos usuários do /admin (RH dos tenants e Super Admin), separados das contas dos candidatos. O perfil vem do tenant do usuário e do grupo de trabalho; o Super Admin cadastra usuários e tenants. Implementado em `Entity/User::getRoles()`, `Controller/SecurityController`, `Controller/Security/ResetPasswordController`, `Controller/Admin/UserController`, no firewall `main` de `config/packages/security.yaml` e nos comandos `app:create-super-admin`, `app:admin-user` e `app:reset-admin-password`.

## Requirements
### Requirement: Login do /admin
O sistema SHALL autenticar os usuários do /admin em `/login` pelo username (`_username`) e senha (`_password`), com token CSRF, e redirecionar para o painel `/admin/`. O login vale em qualquer domínio atendido pelo app.

#### Scenario: Login pelo domínio do tenant
- **WHEN** o usuário da AgriVitta entra em `agrivitta-curriculos.wab.com.br/login` com username e senha corretos
- **THEN** chega ao painel `/admin/` com os dados da AgriVitta

### Requirement: Perfis derivados do usuário
O sistema SHALL dar a todo usuário ROLE_USER, mais ROLE_SUPER_ADMIN quando ele não tem tenant, mais o papel do grupo de trabalho: Administrador (ROLE_ADMIN), Editor / Recrutador (ROLE_EDITOR) ou Revisor (ROLE_REVISOR). A hierarquia é ROLE_SUPER_ADMIN > ROLE_ADMIN > ROLE_EDITOR > ROLE_REVISOR.

#### Scenario: Usuário sem tenant
- **WHEN** um usuário é gravado sem tenant, com qualquer grupo de trabalho
- **THEN** ele tem ROLE_SUPER_ADMIN e acessa a gestão de tenants e usuários

### Requirement: Acesso ao /admin só para administradores
O sistema SHALL exigir ROLE_ADMIN em todo `/admin`. Usuários do grupo Editor / Recrutador ou Revisor de um tenant MUST receber acesso negado no /admin.

#### Scenario: Recrutador tenta entrar
- **WHEN** um usuário do grupo Editor / Recrutador faz login
- **THEN** o redirecionamento para `/admin/` é negado por falta de ROLE_ADMIN

### Requirement: Gestão de usuários pelo Super Admin
O sistema SHALL permitir só ao ROLE_SUPER_ADMIN listar, criar, editar e excluir usuários em `/admin/users`, com nome, username, e-mail, grupo de trabalho, tenant (vazio cria Super Admin) e senha repetida, obrigatória na criação e opcional na edição. A exclusão SHALL exigir token CSRF. Username e e-mail são únicos no banco.

#### Scenario: Edição sem trocar a senha
- **WHEN** o Super Admin edita um usuário e deixa a senha em branco
- **THEN** os dados mudam e a senha continua a mesma

### Requirement: Pedido de nova senha do /admin
Em `/esqueci-a-senha`, o sistema SHALL procurar o usuário do /admin pelo e-mail em todos os tenants e, se achar, gravar um token aleatório válido por 1 hora e enviar o e-mail "Redefinição de senha" com o link, usando o nome do tenant ativo como remetente. A tela MUST mostrar sucesso exista ou não o e-mail.

#### Scenario: E-mail desconhecido
- **WHEN** alguém pede nova senha para um e-mail que não é de nenhum usuário
- **THEN** nenhum e-mail é enviado e a tela mostra a mesma mensagem de sucesso

### Requirement: Nova senha do /admin pelo link
Em `/redefinir-senha/{token}`, o sistema SHALL aceitar só token válido e dentro do prazo, exigir senha de no mínimo 8 caracteres igual à confirmação, gravar a senha em hash, apagar o token e levar ao `/login` com "Senha redefinida com sucesso! Faça login com a nova senha.".

#### Scenario: Token inválido
- **WHEN** o link usado não corresponde a nenhum usuário ou já venceu
- **THEN** a pessoa volta para `/esqueci-a-senha` com "O link de redefinição é inválido ou expirou. Solicite um novo."
