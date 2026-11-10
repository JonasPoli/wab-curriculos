# Design

## Context

Motivação em proposal.md. O estado atual, lido no código em 09/11/2026:

- `Entity/User`: `tenant` com `JoinColumn(nullable: true, onDelete: 'SET NULL')` (FK `FK_8D93D6499033212A`, criada em `Version20260703155218`). `User::getRoles()` e `isSuperAdmin()` tratam `tenant === null` como Super Admin. `TenantController::delete()` só confere o CSRF e remove.
- `EventSubscriber/TenantSubscriber` escuta `kernel.request` com prioridade -10, depois do firewall do Symfony (prioridade 8). No POST de `/trabalhe-conosco/entrar`, o `form_login` do firewall `candidate` chama o provider `entity` com `property: email`, que faz `findOneBy(['email' => ...])` sem o `tenant_filter` ligado: o primeiro candidato com aquele e-mail, de qualquer tenant, é o que tem a senha conferida. Num host sem tenant, o GET da página dá 404, mas o POST é tratado pelo firewall antes do controller e também procura em todos os tenants.
- `CandidateAccessType` liga o campo `email` direto na entidade do candidato logado; `CandidateProfileController::access()` grava sem conferir duplicidade. O cadastro confere com `findOneBy(['email', 'tenant'])`. A coluna `candidate.email` usa `utf8mb4_unicode_ci`, então a comparação por `=` não diferencia maiúsculas.
- Produção (contagens de 09/11/2026, leitura): 38 candidatos, nenhum e-mail repetido no mesmo tenant ou entre tenants e nenhum usuário órfão de tenant excluído (dados da conversa principal). O `doctrine:migrations:status` falha por causa do `serverVersion=8.0` contra MariaDB 11.1; a correção do `serverVersion` está a cargo da conversa principal (09/11/2026).

## Goals / Non-Goals

**Goals:**
- Nenhum caminho (tela, console ou SQL) transforma usuário de tenant em Super Admin.
- A autenticação e a renovação da sessão do candidato só enxergam candidatos do tenant do domínio.
- A regra "um e-mail por tenant" vale no cadastro e na troca de e-mail, e a recusa não derruba a sessão.

**Non-Goals:**
- Mudar a definição de Super Admin (usuário sem tenant) ou os perfis Editor/Recrutador e Revisor, que continuam fora do /admin por decisão do Jonas (09/11/2026).
- Login do /admin, recuperação de senha do /admin e do candidato (esta já procura só no tenant ativo).
- Normalizar e-mails já gravados (minúsculas, espaços).

## Decisions

### 1. Exclusão de tenant: recusar enquanto houver usuários, na tela e no banco

`TenantController::delete()` conta os usuários do tenant (`UserRepository::count(['tenant' => $tenant])`) e, se houver, não remove e mostra "Este tenant tem N usuário(s). Exclua os usuários ou mova-os para outro tenant antes de excluir o tenant.". No banco, a FK `user.tenant_id` passa a não ter `ON DELETE` (comportamento RESTRICT do MariaDB): `JoinColumn(nullable: true)` sem `onDelete`. Assim, uma exclusão feita por outro caminho falha em vez de criar Super Admin.

Alternativas consideradas:
- **CASCADE** (apagar os usuários junto com o tenant): combina com o aviso "Excluir este tenant e todos os seus dados?", mas apaga contas do /admin sem aviso e não tem volta. Descartado: em 10/11/2026 o Jonas escolheu recusar a exclusão enquanto houver usuários.
- **Super Admin explícito** (papel gravado em `user.roles` em vez de "sem tenant"): corrige a causa, mas mexe em `getRoles()`, `isSuperAdmin()`, `UserType` ("vazio cria Super Admin"), nos três comandos e exige gravar o papel nos Super Admins atuais. Mais risco para um caso que hoje tem 0 ocorrências.
- **Só a checagem na tela**: resolve o uso normal sem migration, mas deixa o banco aceitando a exclusão em cascata com SET NULL.

Isolamento: `User` não é `TenantAwareInterface`; a contagem roda no /admin do Super Admin sem tenant ativo, então não passa pelo `tenant_filter`.

### 2. Login do candidato restrito ao tenant do domínio

Duas partes que se reforçam:

- `TenantSubscriber` passa a ter dois ouvintes de `kernel.request`: o das rotas públicas com prioridade 16 (antes do firewall, que é 8; só precisa do host e do caminho) e o do /admin mantido em -10 (precisa do usuário autenticado). Com isso o `tenant_filter` já está ligado quando o firewall autentica e quando renova a sessão (`refreshUser` busca o candidato pelo id, com o filtro).
- `CandidateRepository` implementa `UserLoaderInterface::loadUserByIdentifier()`: procura o e-mail com `tenant = TenantContext::getTenant()` explícito na consulta e devolve `null` quando não há tenant. Em `security.yaml`, o `candidate_provider` perde o `property: email` para usar o repositório. O resultado nulo vira "E-mail ou senha incorretos." pela tradução de `security.pt_BR.yaml`.

Alternativas consideradas:
- **Só reordenar o subscriber**: o filtro passa a valer no `findOneBy`, mas num host sem tenant o filtro fica desligado e a busca volta a ser em todos os tenants.
- **Só o loader resolvendo o tenant pelo host** (`RequestStack` + `findByDomain`): repete a regra de resolução do tenant e deixa a renovação da sessão sem filtro.
- **Authenticator próprio**: mais código para o mesmo efeito; o `form_login` padrão continua servindo.

Isolamento: o candidato só é carregado (no login e a cada requisição) se for do tenant do domínio. Uma sessão de candidato apresentada em outro domínio não é renovada e o candidato sai. O firewall `main` não é afetado (`User` não é tenant-aware). Conferir com dois tenants (tarefas 3.x).

### 3. Troca de e-mail com conferência no tenant, sem mexer na entidade antes da hora

`CandidateAccessType` passa a ter o campo `email` com `mapped: false`, valor inicial do e-mail atual e as restrições `NotBlank` e `Email`. Em `access()`, com o formulário válido, o controller pergunta a `CandidateRepository::isEmailInUse($tenant, $email, $candidate)` (mesmo tenant, outro candidato, comparação pela collation da coluna) e, se estiver em uso, acrescenta o erro "Este e-mail já está em uso por outra conta." no campo e não grava. Só depois de passar tudo o e-mail é posto na entidade e gravado. O cadastro passa a usar o mesmo método, para a regra ficar num lugar só (a mensagem do cadastro não muda).

Por que não ligar o campo na entidade: o candidato do formulário é o próprio usuário da sessão. Se o e-mail muda na memória e a gravação é recusada, o token é guardado na sessão com o e-mail novo; na requisição seguinte o Symfony compara com o registro do banco, vê o identificador diferente e desloga. Isso já acontece hoje quando a confirmação da senha falha junto com a troca de e-mail.

Alternativas consideradas:
- **`UniqueEntity(fields: ['tenant', 'email'])` na entidade**: valida, mas depois de a entidade já ter sido alterada (mesmo problema da sessão) e passaria a rodar também nos outros formulários do candidato.
- **`$em->refresh($candidate)` quando inválido**: funciona, mas é fácil de esquecer num caminho novo; o campo não mapeado evita o problema por construção.

### 4. Índice único (tenant_id, email) em `candidate` (depende de confirmação)

Proposto como garantia no banco para a regra da decisão 3: `#[ORM\UniqueConstraint(name: 'UNIQ_CANDIDATE_TENANT_EMAIL', columns: ['tenant_id', 'email'])]`. Consequências: a migration falha se houver duplicidade no momento do deploy (0 em 09/11; recontar antes); dois cadastros simultâneos com o mesmo e-mail passam a dar erro 500 no segundo em vez de gravar duplicado; o Doctrine pode propor apagar o índice simples `IDX_C8B28E449033212A (tenant_id)` por redundância, o que é aceitável porque o índice composto começa por `tenant_id`. Se o Jonas recusar, a regra fica só na aplicação e os requisitos continuam atendidos.

### 5. Migration e migrations:status

Uma migration gerada localmente (`bin/console make:migration`) e revista à mão para conter só: criar o índice único (primeiro, porque é o passo que pode falhar por dado), trocar a FK `FK_8D93D6499033212A` (drop e add sem `ON DELETE`) e o `down()` inverso. DDL no MariaDB não é transacional: com o índice primeiro, uma falha por duplicidade não deixa a FK pela metade. Em produção, a migration roda à mão com saída visível, não pelo `build.sh` (que engole erros), e só depois de o `doctrine:migrations:status --env=prod` responder sem erro, com as 8 migrations executadas e 1 nova.

## Risks / Trade-offs

- [Super Admin não consegue mais excluir tenant com usuários num clique] → mensagem diz o que fazer; a gestão de usuários já permite excluir ou trocar o tenant.
- [Ouvinte público antes do firewall liga o filtro mais cedo] → nas rotas públicas nada entre as prioridades 16 e -10 consulta entidades tenant-aware além do provider do candidato; conferir login do /admin e home pública nos quatro domínios.
- [Mesmo e-mail em dois tenants passa a entrar só no tenant do domínio] → é o comportamento desejado; hoje não há casos.
- [Migration falha no meio em produção] → índice antes da FK, contagem de duplicidade antes, backup do banco, rodar à mão com saída visível.
- [Campo de e-mail não mapeado perde a validação da entidade] → `NotBlank` e `Email` repetidos no campo do formulário.

## Migration Plan

1. Confirmação do Jonas para as mudanças de entidade (FK e índice), com as consequências da decisão 1 e da decisão 4.
2. Implementação e testes locais com dois tenants.
3. Produção, leitura: `doctrine:migrations:status --env=prod` sem erro (depende do `serverVersion` corrigido); contagens de e-mail repetido por tenant e de usuários sem tenant.
4. Produção, escrita: backup do banco e do commit atual; `git pull`; migration à mão; `cache:clear --env=prod`.
5. Produção, leitura: estrutura da FK e do índice, migrations:status, HTTP 200 nos quatro domínios e login de teste.
6. Volta: `git checkout <commit anotado>`, `doctrine:migrations:migrate prev --env=prod` (ou restaurar o backup) e `cache:clear --env=prod`.
