# tenants-e-dominios Specification

## Purpose
Define como o sistema descobre o tenant (a empresa) de cada requisição e isola os dados entre os tenants, que compartilham o mesmo código e o mesmo banco. A área pública usa o domínio acessado; o /admin usa o tenant impersonado pelo Super Admin ou o tenant do usuário. Implementado em `EventSubscriber/TenantSubscriber`, `Repository/TenantRepository::findByDomain()`, `Doctrine/TenantFilter`, `Controller/Admin/AbstractTenantController`, `Controller/Admin/TenantImpersonationController`, `Controller/Admin/TenantController` e `Twig/TenantExtension`.

## Requirements
### Requirement: Tenant pelo domínio na área pública
O sistema SHALL resolver o tenant das rotas fora de `/admin` pelo host da requisição. Host e domínio cadastrado são comparados em minúsculas, sem `http://` ou `https://`, sem `www.` no início e sem barra final. Rotas que começam com `/_` ou `/logout` não resolvem tenant.

#### Scenario: Domínio gravado com protocolo e barra
- **WHEN** o tenant tem o domínio gravado como `https://carreiras.procordis.org.br/` e a requisição chega em `carreiras.procordis.org.br` ou em `www.carreiras.procordis.org.br`
- **THEN** o tenant Procordis fica ativo na requisição

### Requirement: Domínio sem tenant
O sistema SHALL seguir sem tenant quando o host não corresponde a nenhum cadastro: a home pública abre com os textos padrão ("Trabalhe Conosco") e as páginas de login e de cadastro do candidato MUST responder 404.

#### Scenario: Cadastro num domínio não configurado
- **WHEN** alguém abre `/trabalhe-conosco/cadastro` num host que não é domínio de nenhum tenant
- **THEN** a resposta é 404 "Tenant não encontrado."

### Requirement: Tenant do /admin
Nas rotas `/admin`, o sistema SHALL usar o tenant gravado na sessão pela impersonação (`admin_impersonate_tenant_id`) e, sem ela, o tenant do usuário logado. O domínio acessado MUST NOT influir no tenant do /admin.

#### Scenario: Administrador entra por outro domínio
- **WHEN** o administrador da Procordis faz login e abre `/admin/candidatos` pelo domínio de outro tenant
- **THEN** vê os candidatos da Procordis

#### Scenario: Super Admin sem impersonação
- **WHEN** o Super Admin, sem impersonar, abre `/admin/candidatos`, `/admin/areas-de-interesse` ou `/admin/cargos`
- **THEN** recebe acesso negado com "Esta área é exclusiva para gestão de tenant. Acesse via impersonação ou com uma conta de tenant."
- **AND** o painel `/admin/` mostra só o total de tenants e o total de candidatos de todos os tenants

### Requirement: Isolamento automático por tenant
Com o tenant definido, o sistema SHALL ativar o filtro Doctrine `tenant_filter`, que acrescenta `tenant_id = <id do tenant>` às consultas das entidades que implementam `TenantAwareInterface` e têm o campo `tenant` (Candidate, AreaOfInterest, SavedSearch e ExclusionRequest). Sem tenant, o filtro fica desligado e as consultas veem todos os tenants.

#### Scenario: Candidato de outro tenant
- **WHEN** o RH da Procordis pesquisa candidatos ou abre `/admin/candidatos/{id}` de um candidato da AgriVitta
- **THEN** o candidato não aparece na lista e a página de detalhe não mostra os dados dele

### Requirement: Conferência do dono no /admin
O sistema SHALL negar com "Você não tem acesso a este recurso." a exibição, edição ou exclusão no /admin de candidato, área de interesse ou cargo cujo tenant não é o tenant ativo, além do filtro automático.

#### Scenario: Cargo de outro tenant
- **WHEN** um administrador tenta editar `/admin/cargos/{id}/edit` de um cargo cuja área pertence a outro tenant
- **THEN** a edição é negada e nada é alterado

### Requirement: Impersonação de tenant
O sistema SHALL permitir que o ROLE_SUPER_ADMIN gerencie um tenant por `/admin/tenants/{id}/impersonar`, que grava o tenant na sessão e abre o painel `/admin/` com o aviso "Você está gerenciando o tenant …". `/admin/tenants/sair-impersonacao` SHALL remover o tenant da sessão e voltar à lista de tenants. Quem não tem ROLE_SUPER_ADMIN MUST receber acesso negado.

#### Scenario: Entrar e sair da impersonação
- **WHEN** o Super Admin clica em impersonar a AgriVitta e depois em sair
- **THEN** enquanto impersona, o /admin mostra os dados da AgriVitta
- **AND** ao sair, volta para `/admin/tenants` sem tenant ativo

### Requirement: Cores do tenant
O sistema SHALL gerar, para o tenant ativo, as variáveis CSS `--color-primary`, `--color-secondary`, `--color-primary-dark`, `--color-secondary-dark`, `--bm-accent` e `--bm-accent-dark` com as cores cadastradas, usando #0044cc, #ffaa00, #3b82f6 e #fbbf24 quando a cor está vazia. Sem tenant, nenhuma variável é gerada.

#### Scenario: Tenant sem cor escura
- **WHEN** o tenant tem cor primária cadastrada e a cor primária escura vazia
- **THEN** `--color-primary` recebe a cor cadastrada e `--color-primary-dark` recebe #3b82f6

### Requirement: Scripts do tenant nas páginas públicas
O sistema SHALL inserir sem escape o `headerScript` do tenant antes de `</head>` e o `bodyScript` logo depois de `<body>` nas páginas públicas que recebem o tenant.

#### Scenario: Tag de rastreamento
- **WHEN** o Super Admin grava um script do Google Tag Manager no campo de cabeçalho do tenant
- **THEN** o script aparece no `<head>` das páginas públicas daquele domínio

### Requirement: Exclusão de tenant
O sistema SHALL excluir o tenant só com token CSRF válido e, pelo banco, apagar em cascata os candidatos (com experiências, formações e cargos escolhidos), as áreas de interesse com os cargos, as pesquisas salvas e os pedidos de exclusão do tenant. Os usuários do tenant SHALL ficar sem tenant (SET NULL).

#### Scenario: Tenant excluído com usuário
- **WHEN** o Super Admin exclui um tenant que tem um usuário administrador
- **THEN** os candidatos, áreas e cargos do tenant são apagados
- **AND** o usuário continua existindo, sem tenant, e passa a ser tratado como Super Admin
