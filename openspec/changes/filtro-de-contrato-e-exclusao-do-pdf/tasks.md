# Tasks

## 1. Filtro de tipo de contrato

- [x] 1.1 Em `CandidateRepository::findFiltered()`, para cada tipo marcado, depois da normalização atual, acrescentar ao mesmo `OR` os padrões do JSON sem escape (`JSON_UNESCAPED_UNICODE`) e do JSON escapado com a barra invertida dobrada para o `LIKE`, sem repetir o padrão quando as duas formas forem iguais (CLT, PJ). Verificar no profiler local: a consulta do filtro Estágio leva os dois parâmetros (`%"Estágio"%` e `%"Est\\u00e1gio"%`). (10/11/2026: sem profiler, porque o DebugClassLoader falha no disco exFAT do Mac; conferido no general log do MySQL local: o filtro Estágio gera `LIKE '%"Estágio"%' OR LIKE '%"Est\\u00e1gio"%'`, e CLT/PJ geram um padrão só.)
- [x] 1.2 Testar localmente com dois tenants: no tenant A, candidatos de teste com Estágio, Temporário, Voluntário e CLT (escolhidos pela tela de perfil); no tenant B, um candidato com Estágio. Verificar: no /admin do tenant A, cada tipo traz só os candidatos de A que o escolheram, Temporário + Voluntário traz a união, e o CSV com o filtro Estágio traz os mesmos candidatos da lista; no tenant B, Estágio traz só o candidato de B. (10/11/2026: local, com `contract_types` em LONGTEXT como no MariaDB e um candidato gravado sem escape: no tenant A, Estágio, Temporário, Voluntário e CLT trazem só os candidatos de A que os escolheram, Temporário + Voluntário traz a união e o CSV com Estágio traz os mesmos da lista com "Estágio" legível; no tenant B, Estágio traz só o candidato de B.)

## 2. PDF apagado com a conta

- [x] 2.1 Em `CandidateProfileController::deleteAccount()`, guardar o `resumeFilename` antes do `remove` e, depois do `flush`, apagar o arquivo de `var/uploads/resumes` com o Filesystem do Symfony dentro de `try/catch`, registrando falha no `error_log` só com mensagem genérica (sem nome do arquivo, nome ou e-mail). Verificar localmente: candidato de teste com PDF exclui a conta e o arquivo some de `var/uploads/resumes`. (10/11/2026: local, candidato de teste com PDF exclui a conta e o arquivo some de `var/uploads/resumes`.)
- [x] 2.2 Testar os casos de borda localmente: candidato sem PDF e candidato com nome de arquivo gravado mas arquivo apagado à mão antes. Verificar: nos dois casos a conta é excluída e aparece `/trabalhe-conosco/conta-excluida`, sem erro. (10/11/2026: local, os dois casos excluem a conta e mostram `/trabalhe-conosco/conta-excluida` sem erro. Para isso, a ação `accountDeleted` passou do `CandidateProfileController` (com `#[IsGranted('ROLE_CANDIDATE')]` na classe) para o `CandidatePublicController`, com a mesma rota: antes, o candidato recém-deslogado era mandado para o login e nunca via a página, apesar do `PUBLIC_ACCESS` no `security.yaml`; num host sem tenant ela responde 404.)

## 3. Decisões fora do escopo

- [x] 3.1 Registrar no `context:` do `openspec/config.yaml` as decisões do Jonas de 09/11/2026 que não entram nesta mudança (perfis Editor/Recrutador e Revisor fora do /admin; alerta ao RH, reCAPTCHA e exclusão por token não serão feitos; horas do Procordis Carreiras sem contrato; PetVida sem cliente no sistema WAB). Verificar: o YAML carrega (`python3 -c "import yaml; yaml.safe_load(open('openspec/config.yaml'))"`) e `openspec validate filtro-de-contrato-e-exclusao-do-pdf --strict` passa.

## 4. Produção (wab001, app-curriculo)

- [ ] 4.1 Leitura: confirmar que esta mudança não traz migration (`git diff --stat` sem arquivos em `migrations/`) e que `/RunCloud/Packages/php83rc/bin/php bin/console doctrine:migrations:status --env=prod` responde sem erro e sem migration nova, depois da correção do `serverVersion` feita pela conversa principal em 09/11/2026. Verificar: saída sem "metadata storage is not up to date". Se for junto com `tenant-sem-super-admin-e-login-por-tenant`, seguir as tarefas 6.x daquela mudança no lugar desta seção.
- [ ] 4.2 Leitura, antes: anotar `git rev-parse HEAD` (backup para a volta, já que não há mudança no banco) e contar, só o número, os PDFs em `var/uploads/resumes` e os candidatos com `resume_filename` preenchido (24 e 24 em 09/11/2026). Verificar: hash e contagens anotados.
- [ ] 4.3 Escrita, com aprovação do Jonas: em `~/webapps/app-curriculo`, como runcloud, `git pull` e `/RunCloud/Packages/php83rc/bin/php bin/console cache:clear --env=prod` (sem `build.sh`: não há dependência nem asset novo). Verificar: `git log -1` mostra o commit da mudança e o cache:clear termina sem erro.
- [ ] 4.4 Leitura, conferência: HTTP 200 em https://carreiras.procordis.org.br/, https://curriculo.wab.com.br/, https://curriculo.petvida.com.br/ e https://agrivitta-curriculos.wab.com.br/; no /admin, a soma dos resultados do filtro nos tenants dá Estágio 4, Temporário 3 e Voluntário 2 (ou o número atualizado por contagem), conferindo só a quantidade, sem print nem registro de nomes. Verificar: os quatro domínios respondem 200 e as quantidades batem.
- [ ] 4.5 Conferência do PDF sem escrever em produção (o Jonas não autorizou contas de teste em produção, 10/11/2026): o roteiro de criar conta, enviar PDF e excluir fica no teste local (tarefa 2.1); em produção, só leitura: PDFs no disco = candidatos com PDF, conferido de novo na primeira exclusão real de conta. Verificar: as duas contagens batem.

## Workflow follow-up

- Se o trabalho for registrado no sistema WAB, o Procordis (cliente 212, projeto Carreiras) fica sem `contract_id` por enquanto, conforme decisão do Jonas de 09/11/2026.
- Arquivar a mudança depois do deploy e da conferência.
