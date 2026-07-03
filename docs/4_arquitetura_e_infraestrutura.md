# Documento de Arquitetura de Software e Infraestrutura
## Portal Trabalhe Conosco Multi-Tenant — Procordis

---

### 1. Visão Geral da Arquitetura

O sistema adota uma arquitetura **Single-Database (Shared Schema)** com isolamento lógico rígido feito no nível do ORM (Doctrine). Isso significa que todas as clínicas/tenants compartilham a mesma base de dados MySQL e o mesmo código PHP (Symfony 7). Esta abordagem simplifica drasticamente a manutenção e as atualizações do sistema, mantendo os custos de infraestrutura baixos.

```
       [ Domínio A: carreiras.procordis.com.br ]    [ Domínio B: trabalheconosco.outra.com ]
                                 \                      /
                                  \                    /
                               [ Apontam DNS CNAME/A ]
                                         v
                              [ DigitalOcean Droplet ]
                                         v
                         [ RunCloud Nginx (Multi-Domain) ]
                                         v
                           [ Symfony 7 Kernel (PHP 8.3) ]
                                         v
                       [ TenantSubscriber (Resolve Host) ]
                                         v
                    [ TenantContext / Doctrine SQLFilter ]
                                         v
                     [ Banco de Dados MySQL (Shared Schema) ]
```

---

### 2. Fluxo de Resolução de Tenant e Isolamento

#### 2.1 Resolução por Domínio (`TenantSubscriber`)
1. Toda requisição HTTP é capturada pelo `TenantSubscriber` escutando o evento `KernelEvents::REQUEST` com alta prioridade (ex: 100).
2. O subscriber captura o host atual da requisição via `$request->getHost()`.
3. É realizada uma busca no `TenantRepository::findOneBy(['domain' => $host])`.
4. Se o domínio não for localizado, o sistema retorna um erro amigável "Domínio não configurado" (HTTP 404).
5. Se localizado, o objeto `Tenant` correspondente é injetado no `TenantContext` (serviço global com escopo de request) para acesso nos Controllers, Extensões Twig e Services.

#### 2.2 Isolamento Automático de Consultas (`TenantFilter`)
Para evitar vazamento acidental de dados entre inquilinos, utiliza-se a técnica de SQL Filters do Doctrine:
1. Toda entidade associada a um Tenant implementa a interface `TenantAwareInterface`.
2. O filtro Doctrine `TenantFilter` intercepta dinamicamente a geração de Queries SQL.
3. Se a entidade sob consulta implementar a interface, o filtro anexa automaticamente a cláusula `AND tenant_id = :current_tenant_id` à query.
4. Isso garante que buscas comuns (ex: `$repository->findAll()`) retornem exclusivamente registros pertencentes ao Tenant do domínio ativo na requisição.

---

### 3. Infraestrutura (DigitalOcean & RunCloud)

#### 3.1 DigitalOcean (Droplet)
- Instância virtual configurada com recursos dimensionados para o tráfego do portal.
- Armazenamento SSD rápido para banco de dados local (MySQL/MariaDB) e armazenamento de arquivos (currículos PDFs).

#### 3.2 Painel de Controle RunCloud
O RunCloud gerencia o provisionamento e configurações do servidor no Droplet:
1. **Configuração da Web App**: Uma única Web App é criada apontando para o diretório `/public` da aplicação Symfony.
2. **Associação de Domínios (Domain Aliases)**: O SuperAdmin aponta novos domínios cadastrados (ex.: `carreiras.procordis.com.br`) na interface do RunCloud. O painel Nginx do RunCloud redireciona as conexões de todos esses domínios para a mesma raiz física do Symfony.
3. **Instalação Automática de SSL**: O RunCloud gerencia e renova de forma automática os certificados Let's Encrypt SSL para todos os domínios mapeados, garantindo HTTPS obrigatório.
4. **Deploy Contínuo**: Integração via Webhook do Git para puxar atualizações do repositório principal no momento do push.

---

### 4. Customização de Branding e Temas Dinâmicos

#### 4.1 Injeção Dinâmica de CSS Variables
Para que cada domínio exiba a identidade visual exata do seu Tenant correspondente, o sistema utiliza variáveis CSS injetadas dinamicamente no cabeçalho das páginas públicas:
- A classe global `TenantExtension` (extensão de Twig) disponibiliza as cores cadastradas no banco de dados (`primaryColor`, `secondaryColor`, `primaryColorDark`, `secondaryColorDark` e cores de background) convertidas em variáveis CSS.
- Exemplo do código injetado dinamicamente:
```html
<style>
    :root {
        --color-primary: {{ currentTenant.primaryColor }};
        --color-secondary: {{ currentTenant.secondaryColor }};
        --color-bg-light-1: {{ currentTenant.bgColorLight1 }};
        --color-bg-light-2: {{ currentTenant.bgColorLight2 }};
    }
    @media (prefers-color-scheme: dark) {
        :root {
            --color-primary: {{ currentTenant.primaryColorDark }};
            --color-secondary: {{ currentTenant.secondaryColorDark }};
            --color-bg-dark-1: {{ currentTenant.bgColorDark1 }};
            --color-bg-dark-2: {{ currentTenant.bgColorDark2 }};
        }
    }
</style>
```

#### 4.2 Seleção de Temas
A pasta `templates/themes/` contém os layouts Twig suportados. O controller principal direcionará a renderização pública para a pasta correspondente ao tema salvo na entidade `Tenant` ativa (ex: `return $this->render('themes/' . $tenant->getActiveTheme() . '/trabalhe_conosco.html.twig', $data);`).

---

### 5. Recursos Críticos de Segurança e Envio Assíncrono

#### 5.1 Downloads Seguros de Currículos PDFs
Para evitar acessos públicos a currículos sigilosos:
- Os uploads de PDFs são armazenados na pasta privativa fora da pasta root acessível pela web: `/var/uploads/resumes/tenant_{id}/`.
- O nome físico do arquivo é gerado usando hashes únicos.
- O link do currículo no painel do RH aponta para uma rota administrativa protegida: `/admin/candidate/{id}/download-resume`.
- O controller correspondente verifica as credenciais do usuário do RH (se pertence ao mesmo Tenant) e, se autorizado, realiza a leitura e faz o streaming do PDF via `BinaryFileResponse` ou `StreamedResponse`.

#### 5.2 Disparo Assíncrono com Symfony Messenger
O envio de e-mails de alerta com arquivos PDF anexados é processado via fila:
- Ao submeter o cadastro com sucesso, um evento/mensagem `CandidateRegisteredMessage` é despachado.
- O Symfony Messenger salva a mensagem no banco de dados (tabela `messenger_messages`).
- Um processo supervisor rodando em background (gerenciado pelo RunCloud Supervisor ou Systemd) consome a fila executando `php bin/console messenger:consume async`.
- O handler `CandidateRegisteredHandler` envia os e-mails (candidato e RH) sem onerar o tempo de resposta do candidato no navegador.

#### 5.3 Painel SuperAdmin e Impersonificação (*Switch User*)
- **SuperAdmin Dashboard**: Acessado pela rota `/superadmin` por usuários globais (`ROLE_SUPER_ADMIN`). Permite gerenciar Tenants (domínios, cores, logotipos), usuários locais e auditar logs LGPD gerais.
- **Switch User**: Utiliza o recurso nativo de impersonificação do Symfony Security. O SuperAdmin pode visualizar o painel administrativo de um Tenant específico simulando o login de um administrador local com um clique: `/admin?_switch_user=admin_local_username`. O sistema desativa temporariamente filtros globais permitindo o acesso simulado.
