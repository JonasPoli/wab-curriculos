# area-do-candidato Specification

## Purpose
Área logada em que o candidato completa e mantém o currículo: dados pessoais e PDF, experiências, formação, disponibilidade e cargos de interesse, dados de acesso e exclusão da conta. Mostra o progresso do cadastro em etapas. Implementado em `Controller/Pub/CandidateProfileController`, `Controller/Pub/CandidatePublicController::dashboard()`, `Twig/CandidateProgressExtension` e nos formulários de `Form/Pub/`.

## Requirements

### Requirement: Progresso do cadastro
O sistema SHALL mostrar o progresso em 6 etapas e o percentual de etapas concluídas, arredondado: dados pessoais (nome, e-mail, telefone, cidade e UF preenchidos), experiências (ao menos uma), formação acadêmica (ao menos uma), disponibilidade (ao menos um cargo de interesse), currículo PDF (arquivo enviado) e dados de acesso, que conta sempre como concluída.

#### Scenario: Recém-cadastrado
- **WHEN** o candidato acabou de criar a conta e ainda não enviou PDF, experiência, formação nem cargo
- **THEN** o progresso mostra 2 de 6 etapas (33%)

#### Scenario: Grade preenchida sem cargo
- **WHEN** o candidato marcou turnos na grade de disponibilidade mas nenhum cargo de interesse
- **THEN** a etapa de disponibilidade continua pendente

### Requirement: Dados pessoais e profissionais
Em `/trabalhe-conosco/perfil`, o sistema SHALL permitir alterar nome, telefone, data de nascimento, cidade, UF, LinkedIn e Lattes (URLs, com https por padrão), conselho e número de registro, resumo profissional (até 1.500 caracteres), mensagem ao selecionador (até 1.000), tipos de contrato (CLT, PJ, Estágio, Temporário, Voluntário) e início imediato. O e-mail aparece bloqueado nessa página.

#### Scenario: Resumo longo demais
- **WHEN** o candidato grava um resumo profissional com mais de 1.500 caracteres
- **THEN** a gravação é recusada e nada muda

### Requirement: Currículo em PDF
O sistema SHALL aceitar no perfil um arquivo PDF de até 5 MB e gravá-lo em `var/uploads/resumes/` com o nome `<id do candidato>_<uniqid>.pdf`, fora da pasta pública. Ao enviar um PDF novo, o arquivo anterior SHALL ser apagado do disco. O candidato baixa o próprio PDF em `/trabalhe-conosco/curriculo/download` como `curriculo_<id>.pdf`.

#### Scenario: Arquivo que não é PDF
- **WHEN** o candidato envia uma imagem ou um PDF maior que 5 MB
- **THEN** o envio é recusado com "Envie um arquivo PDF válido (máx. 5 MB)."

#### Scenario: Troca do currículo
- **WHEN** o candidato que já tem PDF envia outro
- **THEN** o PDF antigo é apagado do disco e o novo passa a ser o currículo

### Requirement: Experiências profissionais
Em `/trabalhe-conosco/experiencias`, o sistema SHALL adicionar experiências com empresa, cargo, data de início e descrição obrigatórias (empresa e cargo até 150 caracteres, descrição até 1.000), data de término opcional e a marca de emprego atual. A remoção SHALL exigir token CSRF e que a experiência seja do candidato logado.

#### Scenario: Remover experiência de outra pessoa
- **WHEN** um candidato envia a remoção de uma experiência que pertence a outro candidato
- **THEN** recebe acesso negado e nada é removido

### Requirement: Formação acadêmica
Em `/trabalhe-conosco/formacao`, o sistema SHALL adicionar formações com nível (Ensino Fundamental, Ensino Médio, Curso Livre, Curso Técnico, Ensino Superior Incompleto, Ensino Superior Completo, Pós-graduação, Mestrado, Doutorado), instituição, curso e status (Concluído, Incompleto, Em andamento) obrigatórios e datas de início e conclusão opcionais. A remoção SHALL exigir token CSRF e que a formação seja do candidato logado.

#### Scenario: Formação sem curso
- **WHEN** o candidato adiciona uma formação sem informar o curso
- **THEN** a formação é recusada com "O nome do curso é obrigatório."

### Requirement: Disponibilidade e cargos de interesse
Em `/trabalhe-conosco/disponibilidade`, o sistema SHALL gravar a grade de turnos (manhã e tarde, de segunda a sexta) e os cargos de interesse escolhidos entre os cargos das áreas do tenant, ordenados por área e cargo e exibidos como "Área > Cargo". A lista não separa cargos ativos de inativos.

#### Scenario: Cargo desativado pelo RH
- **WHEN** o RH marca um cargo como inativo
- **THEN** o cargo continua aparecendo para o candidato escolher

### Requirement: Dados de acesso
Em `/trabalhe-conosco/acesso`, o sistema SHALL permitir trocar o e-mail de login e, opcionalmente, a senha (repetida, mínimo de 8 caracteres). Senha em branco mantém a atual. O sistema MUST recusar um e-mail que outro candidato do mesmo tenant já usa, sem diferenciar maiúsculas, com "Este e-mail já está em uso por outra conta.", sem alterar nada e mantendo o candidato logado. O mesmo e-mail em outro tenant não impede a troca.

#### Scenario: Só troca de e-mail
- **WHEN** o candidato altera o e-mail e deixa a senha em branco
- **THEN** o novo e-mail passa a ser o login e a senha continua a mesma

#### Scenario: E-mail de outro candidato do tenant
- **WHEN** o candidato da Procordis tenta trocar o e-mail para o de outro candidato da Procordis, escrito com outras maiúsculas
- **THEN** a troca é recusada com "Este e-mail já está em uso por outra conta."
- **AND** o e-mail e a senha continuam os de antes e o candidato continua logado ao abrir a próxima página

#### Scenario: E-mail usado só em outro tenant
- **WHEN** o candidato da Procordis troca o e-mail para um que só existe na AgriVitta
- **THEN** a troca é aceita

### Requirement: Exclusão da conta pelo candidato
Em `/trabalhe-conosco/excluir-conta`, o sistema SHALL excluir a conta só quando o candidato digita a palavra "remover" (sem diferenciar maiúsculas), fazendo logout, apagando o candidato com experiências, formações e cargos escolhidos, apagando do disco o PDF do currículo, se houver, e redirecionando para `/trabalhe-conosco/conta-excluida`. A falta do arquivo ou uma falha ao apagá-lo MUST NOT impedir a exclusão da conta.

#### Scenario: Palavra errada
- **WHEN** o candidato digita outra palavra
- **THEN** a conta é mantida e aparece o aviso para digitar a palavra "remover"

#### Scenario: Conta excluída
- **WHEN** o candidato que enviou um PDF digita "Remover"
- **THEN** o registro do candidato some do banco e do /admin
- **AND** o PDF dele não está mais em `var/uploads/resumes/`

#### Scenario: Conta sem PDF
- **WHEN** o candidato que nunca enviou PDF exclui a conta
- **THEN** a conta é excluída e ele vê `/trabalhe-conosco/conta-excluida`

#### Scenario: PDF já ausente do disco
- **WHEN** o candidato tem nome de arquivo gravado, o PDF não está no disco e ele exclui a conta
- **THEN** a conta é excluída sem erro e ele vê `/trabalhe-conosco/conta-excluida`
