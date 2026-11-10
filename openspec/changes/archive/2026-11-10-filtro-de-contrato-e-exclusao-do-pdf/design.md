# Design

## Context

Motivação em proposal.md. O estado atual, lido no código em 09/11/2026:

- `Candidate::$contractTypes` é `#[ORM\Column(nullable: true)] ?array`, mapeado como tipo `json`. O `JsonType` do DBAL 3.10 grava com `json_encode(..., JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)`, sem `JSON_UNESCAPED_UNICODE`: "Estágio" vira `"Estágio"`. No MariaDB 11.1 a coluna `JSON` é `LONGTEXT` com collation binária.
- `CandidateRepository::findFiltered()` normaliza o tipo marcado e procura `c.contractTypes LIKE '%"Estágio"%'`, que nunca casa com o texto escapado. CLT e PJ funcionam porque não têm acento. A lista (`CandidateController::index()`) e o CSV (`exportCsv()`) usam o mesmo `findFiltered()`; `countFiltered()` não é chamado em lugar nenhum e não tem esse filtro.
- `CandidateProfileController::deleteAccount()` faz logout, `remove` e `flush`, sem tocar no arquivo. O envio de PDF novo já apaga o anterior com `file_exists` + `unlink` (`profile()`). Não há exclusão de candidato pelo /admin.
- Produção, contagens de 09/11/2026 (leitura): 38 candidatos; Estágio 4, Temporário 3, Voluntário 2, todos gravados com escape e nenhum sem escape; 24 PDFs em `var/uploads/resumes` e 24 candidatos com `resume_filename`, ou seja, nenhum arquivo sobrando hoje.

## Goals / Non-Goals

**Goals:**
- Filtro de contrato correto para os cinco tipos, sem escrita no banco de produção.
- PDF apagado junto com a conta, sem deixar a exclusão depender do disco.

**Non-Goals:**
- Mudar como o JSON é gravado ou regravar os registros existentes.
- PDFs dos candidatos apagados em cascata pela exclusão de um tenant (continuam no disco; a mudança `tenant-sem-super-admin-e-login-por-tenant` só recusa a exclusão quando há usuários).
- Limpeza de arquivos órfãos antigos (não há nenhum hoje).
- Corrigir ou remover o `countFiltered()` sem uso.
- Centralizar o caminho `var/uploads/resumes`, hoje repetido em três controllers.

## Decisions

### 1. Buscar as duas formas do texto, sem regravar o banco

Para cada tipo marcado, depois da normalização que já existe, `findFiltered()` monta os padrões de `LIKE` a partir do valor canônico: o JSON sem escape (`json_encode($valor, JSON_UNESCAPED_UNICODE)`, que dá `"Estágio"`) e o JSON escapado (`json_encode($valor)`, que dá `"Estágio"`), este com a barra invertida dobrada, porque ela é o caractere de escape do `LIKE` no MariaDB. Os dois padrões entram no mesmo `OR` do filtro; para CLT e PJ as duas formas são iguais e entra um padrão só. A busca continua por parâmetro, sem SQL montado com texto do usuário.

Alternativas consideradas:
- **Gravar sem escape e converter os registros existentes**: exige um tipo Doctrine próprio para a coluna (ou sobrescrever o tipo `json` de todas as colunas, incluindo `user.roles` e `saved_search.filters`) e um `UPDATE` em produção nos registros com `á`, com backup e conferência. O ganho é só o JSON legível no banco; a busca ainda precisaria aceitar as duas formas durante a transição. Rejeitada por pôr escrita em produção sem necessidade.
- **`JSON_CONTAINS`**: compara o valor JSON, independente do escape, mas o DQL não tem a função; exigiria um pacote de funções DQL ou SQL nativo no meio do QueryBuilder.

Isolamento por tenant: `findFiltered()` só roda no /admin com tenant ativo (`AbstractTenantController`), com o `tenant_filter` ligado; a mudança só acrescenta condições ao `WHERE` e não muda o isolamento. Conferir com dois tenants (tarefa 1.2).

### 2. Apagar o PDF depois de apagar a conta

Em `deleteAccount()`, guardar o `resumeFilename` antes do `remove`; depois do `flush`, apagar `%kernel.project_dir%/var/uploads/resumes/<arquivo>` com o componente Filesystem do Symfony (já instalado), dentro de `try/catch`. Se o arquivo não existir, nada acontece; se a remoção falhar, registrar no `error_log` uma mensagem genérica, sem nome do arquivo, nome ou e-mail do candidato, e seguir para `/trabalhe-conosco/conta-excluida`. O diretório vem do mesmo `#[Autowire('%kernel.project_dir%')]` usado em `profile()` e `downloadResume()`.

Ordem: apagar o arquivo antes do `flush` deixaria um candidato sem PDF se o banco falhasse; depois do `flush`, o pior caso é um arquivo sobrando, que aparece na contagem "PDFs no disco × candidatos com PDF".

Alternativa considerada: listener Doctrine `postRemove` em `Candidate`, que cobriria também a cascata do tenant. Rejeitada agora porque a cascata é feita pelo banco (`ON DELETE CASCADE`), sem evento do Doctrine para cada candidato, então o listener não resolveria esse caso e esconderia a regra num lugar menos visível.

## Risks / Trade-offs

- [Dois `LIKE` por tipo marcado] → até 10 condições numa tabela de dezenas de registros; custo desprezível.
- [Padrão com barra invertida mal escapado não casa] → conferir no profiler local a consulta gerada e o resultado com um candidato de cada tipo.
- [Falha silenciosa ao apagar o PDF] → o `error_log` vai para o stderr do PHP-FPM, que ninguém lê; a contagem de conferência (PDFs no disco × candidatos com PDF) mostra sobras.
- [Página "Conta excluída" promete a remoção dos arquivos] → passa a ser verdade para o PDF da própria conta.

## Migration Plan

Sem migration e sem escrita no banco. Deploy só de código: anotar o commit atual, `git pull` e `cache:clear --env=prod` com o PHP do pool (`php83rc`), porque a assinatura de `deleteAccount()` muda e os argumentos dos controllers ficam no container compilado. Volta: `git checkout <commit anotado>` e `cache:clear --env=prod`. Se esta mudança for para produção junto com `tenant-sem-super-admin-e-login-por-tenant`, seguir as tarefas de produção daquela, que incluem migration.
