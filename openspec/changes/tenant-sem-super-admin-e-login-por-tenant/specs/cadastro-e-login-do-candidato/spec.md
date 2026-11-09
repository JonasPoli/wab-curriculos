## MODIFIED Requirements

### Requirement: Login do candidato
O sistema SHALL autenticar o candidato em `/trabalhe-conosco/entrar` pelo e-mail (`_email`) e senha (`_password`), com token CSRF, procurando o e-mail só entre os candidatos do tenant do domínio acessado, e redirecionar para `/trabalhe-conosco/minha-conta`. Num domínio sem tenant, o login MUST ser recusado. Candidato já logado que abre a página de login SHALL ir direto para a área do candidato. A saída por `/trabalhe-conosco/logout` volta para a página de login.

#### Scenario: Login correto
- **WHEN** o candidato informa e-mail e senha corretos
- **THEN** entra em `/trabalhe-conosco/minha-conta`

#### Scenario: Mesmo e-mail em dois tenants
- **WHEN** o mesmo e-mail tem cadastro na Procordis e na AgriVitta, com senhas diferentes, e a pessoa entra em `agrivitta-curriculos.wab.com.br` com a senha da AgriVitta
- **THEN** entra na conta da AgriVitta e vê os dados dela, nunca os da Procordis

#### Scenario: E-mail só existe em outro tenant
- **WHEN** alguém tenta entrar em `carreiras.procordis.org.br` com e-mail e senha de um candidato que só existe na AgriVitta
- **THEN** o login é recusado com "E-mail ou senha incorretos."

#### Scenario: Domínio sem tenant
- **WHEN** alguém envia o formulário de login para `/trabalhe-conosco/entrar` num host que não é domínio de nenhum tenant
- **THEN** o login é recusado e ninguém é autenticado
