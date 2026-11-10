# Proposal

## Why

Duas falhas que o RH e o candidato percebem, achadas na leitura do código e conferidas por contagem em produção em 09/11/2026:

- O filtro de tipo de contrato em `/admin/candidatos` não encontra quem escolheu Estágio, Temporário ou Voluntário, porque a escolha é gravada com os acentos escapados (`Estágio`) e a busca procura o texto acentuado. Em produção, 4 candidatos escolheram Estágio, 3 Temporário e 2 Voluntário, e nenhum aparece no filtro nem no CSV filtrado.
- Ao excluir a própria conta, o candidato lê que o currículo em PDF será removido ("Todos os seus dados pessoais e arquivos foram removidos..."), mas o arquivo continua em `var/uploads/resumes`. É dado pessoal guardado sem base depois do pedido de exclusão (LGPD). Hoje não há arquivo sobrando (24 PDFs no disco para 24 candidatos com PDF).

Jonas decidiu em 09/11/2026 corrigir as duas.

## What Changes

- O filtro de tipo de contrato passa a encontrar os cinco tipos, tanto nas escolhas já gravadas (com acento escapado) quanto em escolhas gravadas sem escape. Nada é regravado no banco.
- A exportação em CSV, que usa o mesmo filtro, passa a trazer esses candidatos.
- A exclusão da conta pelo candidato passa a apagar do disco o PDF do currículo, depois de apagar o registro. Falha ao apagar o arquivo não impede a exclusão da conta.

## Capabilities

### New Capabilities

Nenhuma.

### Modified Capabilities

- `candidatos-admin`: o requisito "Filtro por tipo de contrato" passa a encontrar os tipos acentuados.
- `area-do-candidato`: o requisito "Exclusão da conta pelo candidato" passa a apagar o PDF do disco.

## Impact

- Código: `Repository/CandidateRepository::findFiltered()` e `Controller/Pub/CandidateProfileController::deleteAccount()`.
- Banco: nenhuma mudança de estrutura nem de dados; sem migration.
- Produção: deploy só de código no wab001 (escrita no servidor, com o commit anotado antes e conferência depois). Os PDFs passam a ser apagados quando um candidato exclui a conta.
- Fora do escopo (registrado no `openspec/config.yaml` em 09/11/2026): perfis Editor/Recrutador e Revisor no /admin, alerta ao RH, reCAPTCHA no cadastro e exclusão por token.
