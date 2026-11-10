# candidatos-admin Specification

## Purpose
Tela do RH do tenant para encontrar candidatos: filtros combinados, pesquisas salvas com nome, exportação em CSV, ficha do candidato e download do PDF do currículo. Só funciona com tenant ativo no /admin (usuário do tenant ou Super Admin impersonando). Implementado em `Controller/Admin/CandidateController`, `Repository/CandidateRepository::findFiltered()`, `Form/Admin/CandidateFilterType`, `Repository/SavedSearchRepository` e `templates/admin/candidate/`.

## Requirements

### Requirement: Lista de candidatos do tenant
O sistema SHALL listar em `/admin/candidatos` os candidatos do tenant ativo, do cadastro mais recente para o mais antigo, junto com o total de candidatos do tenant. Sem tenant ativo, a tela MUST negar o acesso.

#### Scenario: Lista sem filtro
- **WHEN** o RH abre `/admin/candidatos` sem filtros
- **THEN** vê todos os candidatos do tenant, os mais recentes primeiro

### Requirement: Busca por nome ou e-mail
O sistema SHALL dividir o texto da busca em termos separados por espaço e procurar cada termo em parte do nome ou do e-mail. No modo "Qualquer termo (OR)", que é o padrão, basta um termo; no modo "Todos os termos (AND)", todos os termos MUST aparecer.

#### Scenario: Dois termos no modo AND
- **WHEN** o RH busca "maria silva" no modo AND
- **THEN** só aparecem candidatos cujo nome ou e-mail contém "maria" e também "silva"

### Requirement: Filtros combinados
O sistema SHALL combinar com E os filtros preenchidos: áreas de interesse e cargos do tenant (qualquer um dos escolhidos), UF (várias), tipo de contrato (qualquer um dos escolhidos), início imediato (sim ou não), currículo em PDF (com ou sem) e período de cadastro, de 00:00:00 do dia inicial a 23:59:59 do dia final.

#### Scenario: Área e UF
- **WHEN** o RH filtra a área Enfermagem e a UF SP
- **THEN** aparecem só candidatos de SP com algum cargo da área Enfermagem

### Requirement: Filtro por tipo de contrato
O sistema SHALL filtrar pelo tipo de contrato (CLT, PJ, Estágio, Temporário ou Voluntário, aceitando também clt, pj, estagio, temporario e voluntario), trazendo os candidatos do tenant ativo que escolheram qualquer um dos tipos marcados. O resultado MUST ser o mesmo para escolhas gravadas com os acentos escapados ou sem escape, e vale para a lista e para a exportação em CSV.

#### Scenario: CLT
- **WHEN** o RH marca o tipo CLT
- **THEN** aparecem os candidatos que escolheram CLT

#### Scenario: Estágio
- **WHEN** o RH marca o tipo Estágio e há candidatos que escolheram Estágio
- **THEN** a lista traz esses candidatos

#### Scenario: Temporário ou Voluntário
- **WHEN** o RH marca Temporário e Voluntário
- **THEN** aparecem os candidatos que escolheram pelo menos um dos dois

#### Scenario: CSV filtrado por Estágio
- **WHEN** o RH exporta o CSV com o filtro Estágio aplicado
- **THEN** o arquivo traz os mesmos candidatos da lista, com "Estágio" legível na coluna Tipo de Contrato

#### Scenario: Estágio em outro tenant
- **WHEN** o RH da Procordis marca Estágio e só um candidato da AgriVitta escolheu Estágio
- **THEN** a lista da Procordis vem vazia

### Requirement: Pesquisas salvas
Depois que o formulário de filtros é aplicado, o sistema SHALL permitir salvar a pesquisa com um nome obrigatório, gravando os filtros no tenant e no usuário. Cada usuário SHALL ver só as próprias pesquisas do tenant, mais recentes primeiro, como atalhos que reabrem a lista com os filtros na URL, e pode removê-las.

#### Scenario: Pesquisa sem nome
- **WHEN** o RH tenta salvar uma pesquisa sem nome
- **THEN** aparece "Informe um nome para a pesquisa." e nada é gravado

#### Scenario: Pesquisa salva
- **WHEN** o RH salva a pesquisa "Enfermeiros SP"
- **THEN** aparece a confirmação de pesquisa salva e o atalho "Enfermeiros SP" fica na lista dele, não na dos outros usuários

### Requirement: Exportação em CSV
O sistema SHALL exportar em `/admin/candidatos/exportar-csv` os candidatos do filtro atual num arquivo `candidatos_AAAA-MM-DD_HHMMSS.csv`, em UTF-8 com BOM e separador ponto e vírgula, com as colunas ID, Nome, E-mail, Telefone, Cidade, UF, Cargos de Interesse, Tipo de Contrato, Início Imediato, Disponibilidade, Currículo PDF e Data de Cadastro (dd/mm/aaaa hh:mm).

#### Scenario: Disponibilidade legível
- **WHEN** o candidato marcou segunda de manhã e à tarde e quarta à tarde
- **THEN** a coluna Disponibilidade traz "Segunda (Manhã/Tarde), Quarta (Tarde)"

#### Scenario: Sem disponibilidade
- **WHEN** o candidato não marcou nenhum turno
- **THEN** a coluna Disponibilidade traz "Não informado"

### Requirement: Ficha e PDF do candidato
O sistema SHALL mostrar a ficha em `/admin/candidatos/{id}` e entregar o PDF em `/admin/candidatos/{id}/download-resume` como anexo `curriculo_<nome com caracteres não alfanuméricos trocados por _>.pdf`, só para candidato do tenant ativo. Sem arquivo cadastrado ou sem o arquivo no disco, a resposta MUST ser 404.

#### Scenario: Arquivo sumiu do disco
- **WHEN** o candidato tem nome de arquivo gravado mas o PDF não está em `var/uploads/resumes/`
- **THEN** a resposta é 404 "Arquivo não encontrado no servidor."
