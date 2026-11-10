# Proposal

## Why

Três falhas de isolamento entre tenants foram achadas na leitura do código (09/11/2026). Hoje não há nenhum caso em produção, mas cada uma pode surgir com uma ação comum:

- Excluir um tenant deixa os usuários do /admin dele sem tenant, e usuário sem tenant é tratado como Super Admin: o RH de um cliente passaria a ver e gerenciar todos os tenants.
- O login do candidato procura o e-mail em todos os tenants, porque a autenticação acontece antes de o tenant do domínio ser resolvido. Um e-mail cadastrado em dois tenants pode entrar na conta errada ou não conseguir entrar.
- A tela de dados de acesso deixa o candidato trocar o e-mail para um que outro candidato do mesmo tenant já usa, quebrando a regra de um cadastro por e-mail em cada tenant.

Jonas decidiu em 09/11/2026 corrigir as três de forma preventiva.

## What Changes

- A exclusão de um tenant que ainda tem usuários do /admin passa a ser recusada com uma mensagem; o Super Admin exclui ou move os usuários antes. O banco também deixa de aceitar a exclusão nesse caso (a chave estrangeira deixa de ser SET NULL). **BREAKING** para o Super Admin: excluir tenant com usuário não funciona mais.
- O login do candidato passa a procurar o e-mail só entre os candidatos do tenant do domínio acessado. Num domínio sem tenant, o login é recusado.
- A troca de e-mail em `/trabalhe-conosco/acesso` passa a recusar um e-mail que outro candidato do mesmo tenant já usa, sem alterar nada e sem derrubar a sessão.
- Proposta de índice único (tenant, e-mail) na tabela de candidatos, como garantia no banco; depende da confirmação do Jonas junto com a troca da chave estrangeira.

## Capabilities

### New Capabilities

Nenhuma.

### Modified Capabilities

- `tenants-e-dominios`: o requisito "Exclusão de tenant" passa a recusar a exclusão enquanto o tenant tiver usuários, em vez de deixá-los sem tenant (e Super Admin).
- `cadastro-e-login-do-candidato`: o requisito "Login do candidato" passa a autenticar só contra os candidatos do tenant do domínio.
- `area-do-candidato`: o requisito "Dados de acesso" passa a recusar e-mail já usado por outro candidato do mesmo tenant.

## Impact

- Código: `EventSubscriber/TenantSubscriber` (resolução do tenant público antes do firewall), `Repository/CandidateRepository` (carregamento do candidato pelo e-mail no tenant e conferência de e-mail em uso), `config/packages/security.yaml` (provider do candidato), `Controller/Admin/TenantController::delete()`, `Controller/Pub/CandidateProfileController::access()`, `Form/Pub/CandidateAccessType` e `Controller/Pub/CandidatePublicController::register()` (mesma conferência de e-mail).
- Banco: `Entity/User` (chave estrangeira `user.tenant_id` sem ON DELETE SET NULL) e, se confirmado, `Entity/Candidate` (índice único `tenant_id, email`). Exige migration, confirmação do Jonas antes (agents.md) e o `doctrine:migrations:status` de produção resolvido.
- Produção: deploy com migration no wab001 (escrita, com backup antes e conferência depois). Nenhum dado de candidato muda.
- Sem efeito nos usuários do /admin, que já são únicos por username e e-mail no sistema todo.
