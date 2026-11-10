# Tasks

## 1. Confirmação antes de mudar entidade (agents.md)

- [x] 1.1 Confirmado pelo Jonas em 10/11/2026: (a) recusar a exclusão de tenant com usuários (FK sem `ON DELETE SET NULL`, nada de CASCADE) e (b) índice único `candidate (tenant_id, email)`. Texto original da tarefa: mostrar ao Jonas, em resumo, as consequências das mudanças de entidade e pedir confirmação antes de tocar em `Entity/User` e `Entity/Candidate`: (a) FK `user.tenant_id` sem `ON DELETE SET NULL`, o banco passa a recusar excluir tenant com usuários; (b) índice único `candidate (tenant_id, email)`, que falha se houver duplicidade, troca duplicado silencioso por erro em cadastro simultâneo e pode substituir o índice simples de `tenant_id`; (c) alternativa CASCADE para os usuários. Verificar: resposta do Jonas registrada (aceita a, b, ambas ou CASCADE) antes da tarefa 2.1.

## 2. Exclusão de tenant sem criar Super Admin

- [ ] 2.1 Em `Entity/User`, trocar o `JoinColumn` de `tenant` para `nullable: true` sem `onDelete` (ou `CASCADE`, se for a escolha da 1.1). Verificar: `bin/console doctrine:schema:validate` só aponta a diferença esperada da FK.
- [ ] 2.2 Em `TenantController::delete()`, com CSRF válido, contar os usuários do tenant com `UserRepository::count(['tenant' => $tenant])`; se houver, não remover e mostrar o flash de erro "Este tenant tem N usuário(s). Exclua os usuários ou mova-os para outro tenant antes de excluir o tenant."; sem usuários, manter "Tenant excluído.". Verificar localmente: tenant com 1 usuário continua existindo com a mensagem; tenant sem usuários é excluído com cascata.
- [ ] 2.3 Conferir localmente que nenhum usuário fica sem tenant ao excluir tenant (contar usuários sem tenant na lista de `/admin/users` antes e depois). Verificar: o número de Super Admins não muda.

## 3. Login do candidato só no tenant do domínio

- [ ] 3.1 Em `TenantSubscriber`, separar os ouvintes: rotas públicas com prioridade 16 (antes do firewall) e /admin em -10, mantendo as exceções `/_` e `/logout`. Verificar: home pública, `/login` do /admin e `/admin/candidatos` continuam com o tenant certo nos dois tenants locais.
- [ ] 3.2 Em `CandidateRepository`, implementar `UserLoaderInterface::loadUserByIdentifier()` com e-mail e tenant do `TenantContext` explícitos na consulta, devolvendo `null` sem tenant; em `config/packages/security.yaml`, tirar `property: email` do `candidate_provider`. Verificar: `bin/console debug:config security` sem erro e login correto no tenant local.
- [ ] 3.3 Testar com dois tenants locais (dois hosts apontando para o servidor local): mesmo e-mail nos dois com senhas diferentes entra em cada um na própria conta; e-mail só do tenant B é recusado no host do tenant A com "E-mail ou senha incorretos."; POST de login num host sem tenant não autentica. Verificar: os três casos como descrito no spec de `cadastro-e-login-do-candidato`.

## 4. Troca de e-mail com conferência no tenant

- [ ] 4.1 Em `CandidateRepository`, criar `isEmailInUse(Tenant $tenant, string $email, ?Candidate $except = null): bool` e usar no cadastro (`CandidatePublicController::register()`) no lugar do `findOneBy`. Verificar: cadastro com e-mail repetido no mesmo tenant continua recusado com a mensagem atual.
- [ ] 4.2 Em `CandidateAccessType`, deixar `email` com `mapped: false`, valor inicial do e-mail atual e restrições `NotBlank` e `Email`; em `CandidateProfileController::access()`, recusar e-mail em uso com o erro "Este e-mail já está em uso por outra conta." no campo e só gravar o e-mail na entidade depois de tudo válido. Verificar localmente: troca para e-mail de outro candidato do mesmo tenant (com outras maiúsculas) é recusada e o candidato continua logado na página seguinte; troca para e-mail só de outro tenant é aceita; troca normal continua funcionando.
- [ ] 4.3 Conferir que trocar o e-mail com a confirmação de senha errada não desloga mais o candidato. Verificar: depois do erro, abrir `/trabalhe-conosco/minha-conta` sem ir para o login.

## 5. Migration

- [ ] 5.1 Se a 1.1 aceitou o índice, acrescentar em `Entity/Candidate` `#[ORM\UniqueConstraint(name: 'UNIQ_CANDIDATE_TENANT_EMAIL', columns: ['tenant_id', 'email'])]`. Verificar: `doctrine:schema:validate` só aponta as diferenças esperadas.
- [ ] 5.2 Gerar a migration com `bin/console make:migration`, deixar só o índice único (primeiro) e a troca da FK `FK_8D93D6499033212A`, com `down()` inverso, e rodar localmente `doctrine:migrations:migrate` e `migrate prev`. Verificar: sobe e desce sem erro e `doctrine:schema:validate` fica limpo depois de subir.
- [ ] 5.3 Atualizar no `openspec/config.yaml` a linha "Excluir um tenant deixa os usuários dele sem tenant (FK SET NULL)" para o comportamento novo. Verificar: `openspec validate tenant-sem-super-admin-e-login-por-tenant --strict` sem erros e o YAML carrega.

## 6. Produção (wab001, app-curriculo)

- [ ] 6.1 Leitura: confirmar que o `serverVersion` do `.env.local` foi corrigido (sem exibir o valor) e que `/RunCloud/Packages/php83rc/bin/php bin/console doctrine:migrations:status --env=prod` responde sem erro, com 8 executadas e nenhuma nova antes do pull. Verificar: saída do comando sem "metadata storage is not up to date". Se ainda falhar, parar aqui.
- [ ] 6.2 Leitura: contar, só o número, e-mails repetidos no mesmo tenant em `candidate` e usuários com `tenant_id` nulo em `user`. Verificar: 0 repetidos (senão, parar e falar com o Jonas antes do índice) e anotar o número de Super Admins.
- [ ] 6.3 Escrita, com aprovação do Jonas: backup do banco (dump fora de `public/`, sem exibir credenciais) e anotar `git rev-parse HEAD`. Verificar: arquivo de dump com tamanho maior que zero e hash anotado.
- [ ] 6.4 Escrita: deploy em `~/webapps/app-curriculo` como runcloud: `git pull`, `/RunCloud/Packages/php83rc/bin/php bin/console doctrine:migrations:migrate --no-interaction --env=prod` à mão, com a saída visível, e `/RunCloud/Packages/php83rc/bin/php bin/console cache:clear --env=prod` (sem `build.sh`, porque não há mudança de dependências nem de assets e ele esconde a falha da migration). Verificar: migration executada sem erro.
- [ ] 6.5 Leitura, conferência: `doctrine:migrations:status --env=prod` com 9 executadas; `SHOW CREATE TABLE user` sem `ON DELETE SET NULL` na FK; índice `UNIQ_CANDIDATE_TENANT_EMAIL` presente (se aprovado); usuários sem tenant iguais à contagem da 6.2; HTTP 200 em https://carreiras.procordis.org.br/, https://curriculo.wab.com.br/, https://curriculo.petvida.com.br/ e https://agrivitta-curriculos.wab.com.br/ e em `/trabalhe-conosco/entrar` de cada um. Verificar: todos os itens conferidos.
- [ ] 6.6 Conferência funcional sem escrever em produção (o Jonas não autorizou contas de teste em produção, 10/11/2026): repetir no ambiente local, com cópia da estrutura e dois tenants, o roteiro de criar conta, sair, entrar, tentar trocar o e-mail para um já usado e excluir; em produção, só leitura: `/trabalhe-conosco/entrar` responde 200 nos 4 domínios e um POST de login com e-mail inexistente responde com a mensagem genérica. Verificar: os casos locais passam e nenhuma conta foi criada em produção.
