## MODIFIED Requirements

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
