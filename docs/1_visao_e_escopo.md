# Documento de Visão e Escopo (Vision & Scope)
## Portal Trabalhe Conosco Multi-Tenant — Procordis

---

### 1. Introdução e Contexto
O projeto **Trabalhe Conosco** consiste em uma plataforma web multi-tenant voltada para o cadastro de currículos e formação de banco de talentos. A Procordis (e outras unidades ou entidades sob modelo de tenants separados por domínios) necessita de um canal oficial estruturado para atrair talentos, coletar informações profissionais ricas de forma segura e aderente à LGPD, e disponibilizar esses dados para seus departamentos de Recursos Humanos de maneira eficiente.

O sistema será integrado a uma infraestrutura multi-domain rodando em **DigitalOcean + RunCloud**, onde cada domínio (ex.: `carreiras.procordis.com.br`, `trabalheconosco.outraclinica.com.br`) representará um inquilino (Tenant) isolado, compartilhando a mesma base de código e base de dados.

---

### 2. Objetivos de Negócio
- **Centralização e Organização**: Substituir o recebimento de currículos por e-mails dispersos por um banco de talentos centralizado e estruturado.
- **Isolamento de Dados (Multi-Tenancy)**: Permitir que múltiplas marcas/clínicas utilizem o sistema sob seus próprios domínios de forma isolada, transparente e personalizada.
- **Adequação Legal (LGPD)**: Garantir conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018), coletando consentimentos explícitos e permitindo a revogação de dados.
- **Otimização do Recrutamento**: Permitir buscas rápidas de candidatos por área de interesse, escolaridade, cargo pretendido e experiência no painel de administração.
- **Automação de Alertas**: Encaminhar notificações automáticas por e-mail com o arquivo PDF do currículo anexado para as caixas de correio de recrutamento correspondentes de cada tenant.

---

### 3. Stakeholders & Perfis de Usuário

#### 3.1 Usuários Externos
- **Candidato**: Profissional interessado em ingressar na instituição. Acessa a página pública (`/trabalhe-conosco`), preenche o formulário condicional e realiza o upload do currículo em formato PDF.

#### 3.2 Usuários Internos
- **Super Administrador (Global)**: Responsável por gerenciar os tenants e as credenciais globais. Atua em um domínio administrativo centralizado (ou rota `/superadmin`), cadastrando domínios, vinculando temas, definindo limites e impersonando administradores locais.
- **Administrador do Tenant (Local)**: Responsável por gerenciar as configurações específicas da sua unidade (e-mails de recebimento, textos do banner, logotipo, cores institucionais, áreas de interesse permitidas no formulário).
- **Editor/Recrutador (Tenant)**: Profissional de RH local que visualiza os currículos cadastrados, realiza filtros de busca, baixa PDFs e gerencia o status das candidaturas.
- **Revisor (Tenant)**: Usuário com permissão para atuar na aprovação e controle de dados (como solicitações de exclusão de candidatos sob LGPD).

---

### 4. Escopo do Projeto

#### 4.1 Escopo Incluído (In-Scope)
1. **Landing Page Pública**: Exibição de banner institucional, descrição de cultura/valores e formulário de inscrição estruturado por seções.
2. **Formulário Dinâmico de Inscrição**: 
   - Validações em tempo real (front-end e back-end).
   - Campos condicionais para nível superior (instituição, curso, ano de conclusão).
   - Campos condicionais para conselho profissional (CRM, COREN, etc.).
   - Upload restrito a arquivos PDF (máximo de 10 MB) com nomenclatura interna criptografada/ofuscada.
3. **Módulo LGPD**: Termos de consentimento explícitos na página com registro de IP, data/hora e metadados de aceitação. Interface para o candidato solicitar exclusão dos dados.
4. **Painel do SuperAdmin**: Interface global para criar tenants, vincular domínios DNS, definir temas, gerenciar credenciais administrativas locais e realizar impersonificação (*Switch User*).
5. **Painel do Admin Local**: Interface de gestão de candidatos recebidos por tenant, personalização visual (logotipos, cores primárias/secundárias, favicon, fontes), customização do formulário (configuração de e-mail de destino dos alertas, áreas de interesse e estados de atuação).
6. **Disparo de E-mails**: Sistema de fila de e-mails para envio assíncrono das notificações com anexo em PDF.
7. **Segurança de Armazenamento**: Upload de currículos em pastas protegidas fora do diretório público direto ou com URLs ofuscadas/assinadas temporariamente para evitar download não autorizado.

#### 4.2 Escopo Excluído (Out-of-Scope)
1. **Triagem de Candidatos baseada em IA**: Não haverá análise inteligente automatizada do conteúdo do currículo.
2. **Sistema de Vídeo-Entrevista**: Gravação de vídeos de candidatos na própria plataforma.
3. **Módulo de Testes Técnicos**: Provas de conhecimento ou testes comportamentais integrados.

---

### 5. Premissas e Restrições
- **Tecnologia Base**: Symfony 7 / PHP 8.3+ e MySQL/MariaDB (seguindo a arquitetura do projeto `wab-sites`).
- **Isolamento de Banco de Dados**: Banco único compartilhado com isolamento lógico rígido baseado em filtros automáticos do Doctrine (`tenant_id` em todas as tabelas isoladas).
- **Servidor de Produção**: DigitalOcean (Droplet) com painel RunCloud para gerenciamento de SSL LetsEncrypt, múltiplos domínios web apontando para o mesmo diretório público e deploy contínuo via repositório Git.
- **Armazenamento de Arquivos**: O armazenamento local dos arquivos PDF de currículos deve ser isolado por tenant e seguro (ex.: diretório `/var/uploads` privado, com download servido via StreamedResponse autenticado no controller).
