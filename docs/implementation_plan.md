# Plano de Implementação: Portal Trabalhe Conosco Multi-Tenant

Este documento descreve o plano detalhado de desenvolvimento para a implementação do sistema Trabalhe Conosco Multi-Tenant para a Procordis, baseado no modelo estrutural de multi-inquilinos do projeto de referência `wab-sites`.

---

## User Review Required

> [!IMPORTANT]
> **Modelo de Armazenamento Seguro dos Arquivos de Currículos (PDF):**
> Para estar em conformidade total com a LGPD e regras de privacidade, os currículos em PDF serão armazenados fora da pasta raiz pública do servidor (ex.: `/var/uploads/resumes/` privado). O download e a visualização dos currículos serão efetuados através de uma rota administrativa segura, controlada por um controller Symfony que valida a permissão do usuário logado (RH do respectivo Tenant).

> [!WARNING]
> **Configurações de DNS e RunCloud:**
> A criação de novos tenants no painel do SuperAdmin exige que o administrador crie a entrada DNS (CNAME ou A) para o domínio correspondente no registrador e mapeie esse mesmo domínio como alias na aplicação web dentro do painel RunCloud. O sistema resolverá o Tenant correto no banco dinamicamente, mas a infraestrutura de rede externa precisa estar previamente configurada.

---

## Open Questions

> [!IMPORTANT]
> 1. **Revogação de Dados (LGPD):** Quando o candidato confirmar a exclusão de seus dados, devemos realizar um **Hard Delete** (apagar permanentemente todos os registros e arquivos PDF do banco e do disco) ou um **Soft Delete** (manter dados de auditoria anonimizados apenas com hash de IP e data de exclusão para comprovação legal futura)? Recomendamos o Hard Delete dos dados pessoais combinada à manutenção de um log anonimizado na tabela `LgpdLog`.
> 2. **Serviço de Envio de E-mails:** Qual provedor de e-mail (SMTP, SendGrid, Amazon SES, Mailgun) será utilizado em produção para o envio das notificações para os candidatos e para as caixas de RH dos tenants?
> 3. **Temas Iniciais:** Quantos temas visuais (templates CSS/HTML) estarão disponíveis no início do desenvolvimento? O padrão será baseado em um design com suporte a Dark Mode semelhante aos temas `moderno` e `nepe` do `wab-sites`?

---

## Proposed Changes

Como este sistema será desenvolvido do zero, a lista abaixo descreve as novas classes, templates e estruturas que serão criados na base do projeto `/Volumes/Dados/work/curriculos`.

### Core Infrastructure & Tenant Resolution

#### [NEW] [TenantAwareInterface.php](file:///Volumes/Dados/work/curriculos/src/Contract/TenantAwareInterface.php)
Interface a ser implementada por todas as entidades que dependem de isolamento lógico de dados por Tenant (`Candidate`, `AreaOfInterest`, `ExclusionRequest`).

#### [NEW] [TenantFilter.php](file:///Volumes/Dados/work/curriculos/src/Doctrine/TenantFilter.php)
Filtro Doctrine SQL que insere automaticamente a cláusula `AND tenant_id = :tenant_id` em consultas de entidades que implementam a `TenantAwareInterface`.

#### [NEW] [TenantContext.php](file:///Volumes/Dados/work/curriculos/src/Service/TenantContext.php)
Serviço com escopo de request (`request scope`) que retém a entidade `Tenant` ativa resolvida para a requisição atual.

#### [NEW] [TenantSubscriber.php](file:///Volumes/Dados/work/curriculos/src/EventSubscriber/TenantSubscriber.php)
Subscriber escutando o evento `KernelEvents::REQUEST` (prioridade 100) para capturar o host, pesquisar no banco de dados e setar o tenant ativo no `TenantContext` e no filtro SQL do Doctrine.

#### [NEW] [TenantExtension.php](file:///Volumes/Dados/work/curriculos/src/Twig/TenantExtension.php)
Extensão Twig que expõe a função `tenant_css_vars()` para injeção dinâmica de variáveis CSS no layout da página com base no branding do Tenant ativo.

---

### Data Model & Entities

#### [NEW] [Tenant.php](file:///Volumes/Dados/work/curriculos/src/Entity/Tenant.php)
Entidade Doctrine mapeando a tabela `tenant`, contendo campos de domínio, branding (cores, logotipos, fontes), contatos, reCAPTCHA, dados SEO e os textos de consentimento LGPD e Política de Privacidade editáveis em HTML.

#### [NEW] [User.php](file:///Volumes/Dados/work/curriculos/src/Entity/User.php)
Entidade para autenticação e papéis administrativos (SuperAdmin e Admins locais).

#### [NEW] [Candidate.php](file:///Volumes/Dados/work/curriculos/src/Entity/Candidate.php)
Entidade Doctrine mapeando os candidatos inscritos, seus dados pessoais, redes profissionais (LinkedIn e Lattes), consentimentos LGPD, grade de horários semanal (Segunda a Sexta, Manhã e Tarde), link com a conta de login `User` e relações para múltiplos empregos e escolaridades.

#### [NEW] [WorkExperience.php](file:///Volumes/Dados/work/curriculos/src/Entity/WorkExperience.php)
Entidade Doctrine que armazena os registros de experiências profissionais do candidato (nome da empresa, cargo, período de atuação e atividades).

#### [NEW] [AcademicBackground.php](file:///Volumes/Dados/work/curriculos/src/Entity/AcademicBackground.php)
Entidade Doctrine que armazena a formação acadêmica do candidato (nível de escolaridade, instituição, curso, status e período).

#### [NEW] [AreaOfInterest.php](file:///Volumes/Dados/work/curriculos/src/Entity/AreaOfInterest.php)
Departamentos ou setores definidos pelo RH do inquilino para classificação de cargos (ex.: Enfermagem, Administração).

#### [NEW] [Career.php](file:///Volumes/Dados/work/curriculos/src/Entity/Career.php)
Cargos específicos associados a cada departamento/área de interesse (ex.: Auxiliar de Faturamento, Enfermeiro UTI), permitindo seleção múltipla pelo candidato.

#### [NEW] [ExclusionRequest.php](file:///Volumes/Dados/work/curriculos/src/Entity/ExclusionRequest.php)
Mapeamento de tokens de exclusão solicitados por candidatos sob a LGPD.

#### [NEW] [LgpdLog.php](file:///Volumes/Dados/work/curriculos/src/Entity/LgpdLog.php)
Tabela de log de auditoria fria para registro de aceites de termos e exclusões de dados.

---

### Backend Logic & Controllers

#### [NEW] [SuperAdminController.php](file:///Volumes/Dados/work/curriculos/src/Controller/superadmin/SuperAdminController.php)
Controller de acesso restrito global (`ROLE_SUPER_ADMIN`) contendo CRUD de Tenants, gerenciamento de usuários administrativos locais e recursos de impersonificação de inquilinos.

#### [NEW] [AdminController.php](file:///Volumes/Dados/work/curriculos/src/Controller/admin/AdminController.php)
Painel do RH local do Tenant (`ROLE_ADMIN` e `ROLE_EDITOR`). Gerenciamento de currículos, exportação de listagens, busca estruturada por nível de escolaridade/área de interesse e gerenciamento de áreas de interesse locais.

#### [NEW] [PublicController.php](file:///Volumes/Dados/work/curriculos/src/Controller/pub/PublicController.php)
Exibição pública do portal "Trabalhe Conosco" resolvido pelo domínio, formulário de cadastro, upload seguro de arquivo e solicitação/confirmação de exclusão LGPD.

#### [NEW] [EmailNotificationService.php](file:///Volumes/Dados/work/curriculos/src/Service/EmailNotificationService.php)
Serviço de geração e formatação de e-mails para o candidato e envio assíncrono para o RH do tenant (via fila do Symfony Messenger).

---

### Templates (Twig & Frontend)

#### [NEW] [layout.html.twig](file:///Volumes/Dados/work/curriculos/templates/base.html.twig)
Layout base importando fontes do Google Fonts dinâmicas, configurando o tema visual e injetando as variáveis CSS geradas dinamicamente a partir das preferências do Tenant.

#### [NEW] [trabalhe_conosco.html.twig](file:///Volumes/Dados/work/curriculos/templates/pub/trabalhe_conosco.html.twig)
Página pública que renderiza o formulário de cadastro com validações visuais nativas em CSS e lógica JavaScript para os campos condicionais.

#### [NEW] [superadmin_dashboard.html.twig](file:///Volumes/Dados/work/curriculos/templates/superadmin/dashboard.html.twig)
Painel de controle central para criação de novos inquilinos, cadastros de domínios e alteração de temas visuais de branding.

#### [NEW] [admin_dashboard.html.twig](file:///Volumes/Dados/work/curriculos/templates/admin/dashboard.html.twig)
Dashboard do recrutador local contendo a lista dos currículos cadastrados, filtros avançados de pesquisa e link para download seguro do PDF.

---

## Verification Plan

### Automated Tests
Para validar as validações de dados pessoais, upload de PDF e fluxo de exclusão de dados:
```bash
# Executar a suite de testes unitários e de integração
php bin/phpunit
```
- Criar testes em `tests/Controller/PublicControllerTest.php` cobrindo:
  - Envio de formulário com sucesso (validando que o registro `Candidate` e `LgpdLog` foram criados).
  - Envio com arquivo inválido (bloqueio de extensões que não sejam PDF, limite de tamanho).
  - Testes de exibição dinâmica de campos condicionais.

### Manual Verification
1. **Configuração de Hosts Locais**:
   Adicionar no arquivo `/etc/hosts` do ambiente de desenvolvimento:
   ```hosts
   127.0.0.1   procordis.local
   127.0.0.1   outraclinica.local
   ```
2. **Cadastro no SuperAdmin**:
   - Logar no painel `/superadmin` com o usuário `superadmin`.
   - Cadastrar o tenant "Procordis" com domínio `procordis.local` e cores corporativas azul/branco.
   - Cadastrar o tenant "Outra Clínica" com domínio `outraclinica.local` e cores verde/cinza.
3. **Validação Visual e Resolução de Tenant**:
   - Acessar `http://procordis.local:8000/trabalhe-conosco` e verificar se a interface apresenta o logo, cores e áreas configuradas para a Procordis.
   - Acessar `http://outraclinica.local:8000/trabalhe-conosco` e verificar a mudança visual completa e isolamento de campos de seleção.
4. **Fluxo Completo de Cadastro de Currículo**:
   - Preencher o formulário no site `procordis.local` simulando um candidato técnico (que exibe campos de conselho profissional COREN).
   - Anexar um arquivo PDF válido e submeter.
   - Validar o recebimento do e-mail simulado no Maildev/Mailcatcher.
   - Entrar no painel de administração da Procordis (`http://procordis.local:8000/admin`) e comprovar que o novo candidato aparece na listagem e que seu currículo PDF pode ser baixado de maneira segura.
   - Acessar o painel da Outra Clínica (`http://outraclinica.local:8000/admin`) e confirmar que o candidato cadastrado na Procordis **não é exibido** (isolamento de tenant).
5. **Fluxo LGPD de Exclusão**:
   - No portal público de um tenant, clicar em "Solicitar exclusão de dados".
   - Inserir o e-mail do candidato e confirmar.
   - Verificar recebimento do token no e-mail, clicar no link de confirmação e garantir que os dados pessoais e o arquivo PDF foram devidamente removidos da base de dados e do disco do servidor.
