## MODIFIED Requirements

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
