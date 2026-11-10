# cadastro-e-login-do-candidato Specification

## Purpose
Cria a conta do candidato no tenant do domínio acessado, faz o login e recupera a senha. A conta do candidato é o próprio registro `Candidate` (login pelo e-mail), separado dos usuários do /admin. Implementado em `Controller/Pub/CandidatePublicController`, `Form/Pub/CandidateRegisterType`, no firewall `candidate` de `config/packages/security.yaml` e nos e-mails `templates/email/candidate_welcome.html.twig` e `candidate_reset_password.html.twig`.

## Requirements

### Requirement: Cadastro inicial
O sistema SHALL oferecer em `/trabalhe-conosco/cadastro` um formulário com nome completo, e-mail, telefone, cidade, UF (texto de até 2 letras), senha repetida de no mínimo 8 caracteres e a caixa "Li e aceito os termos de uso e a política de privacidade.", que MUST estar marcada. Nome, e-mail válido, telefone, cidade e UF são obrigatórios.

#### Scenario: Termos não aceitos
- **WHEN** o candidato envia o cadastro sem marcar a caixa dos termos
- **THEN** o cadastro é recusado com "Você deve aceitar os termos para prosseguir."
- **AND** nada é gravado

#### Scenario: Senha curta
- **WHEN** a senha tem menos de 8 caracteres
- **THEN** o cadastro é recusado com "A senha deve ter no mínimo 8 caracteres."

### Requirement: Um cadastro por e-mail em cada tenant
O sistema SHALL recusar o cadastro quando já existe candidato com o mesmo e-mail no mesmo tenant, mostrando "Este e-mail já está cadastrado. Faça login ou recupere sua senha.". O mesmo e-mail pode ter cadastro em outro tenant.

#### Scenario: E-mail repetido no mesmo tenant
- **WHEN** um e-mail já cadastrado na Procordis tenta novo cadastro em carreiras.procordis.org.br
- **THEN** o formulário volta com a mensagem de e-mail já cadastrado e nada é gravado

#### Scenario: Mesmo e-mail em outro tenant
- **WHEN** o mesmo e-mail se cadastra em curriculo.petvida.com.br
- **THEN** o cadastro é aceito como um candidato novo da PetVida

### Requirement: Gravação do cadastro e consentimento
Ao aceitar o cadastro, o sistema SHALL gravar o candidato no tenant ativo com a senha em hash, `activeRegistration` verdadeiro e o consentimento LGPD com data e hora, IP e User-Agent da requisição.

#### Scenario: Cadastro aceito
- **WHEN** o candidato envia o cadastro válido
- **THEN** o candidato é gravado no tenant do domínio com o consentimento LGPD preenchido

### Requirement: Boas-vindas e entrada automática
Depois de gravar, o sistema SHALL enviar ao candidato o e-mail "Bem-vindo ao <nome do tenant>" com o link da área do candidato, fazer o login automático e redirecionar para `/trabalhe-conosco/minha-conta`. Falha no envio do e-mail MUST NOT impedir o cadastro nem o login. O RH do tenant não recebe e-mail.

#### Scenario: Servidor de e-mail fora do ar
- **WHEN** o cadastro é gravado e o envio do e-mail de boas-vindas falha
- **THEN** o erro vai para o error_log do PHP
- **AND** o candidato entra na área do candidato normalmente

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

### Requirement: Páginas protegidas do candidato
O sistema SHALL exigir ROLE_CANDIDATE em todo `/trabalhe-conosco/*`, exceto entrar, cadastro, esqueci-a-senha, redefinir-senha, conta-excluida, termos e privacidade, que são públicas. A sessão do candidato vale só dentro de `/trabalhe-conosco`.

#### Scenario: Acesso sem login
- **WHEN** um visitante sem login abre `/trabalhe-conosco/perfil`
- **THEN** é levado para `/trabalhe-conosco/entrar`

### Requirement: Pedido de nova senha
Em `/trabalhe-conosco/esqueci-a-senha`, o sistema SHALL procurar o e-mail informado só no tenant ativo e, se achar, gravar um token aleatório de 64 caracteres válido por 1 hora e enviar o e-mail "Redefinição de senha" com o link. A resposta MUST ser sempre "Se este e-mail estiver cadastrado, você receberá um link de recuperação em breve.", exista ou não o e-mail.

#### Scenario: E-mail de outro tenant
- **WHEN** o e-mail informado só existe em outro tenant
- **THEN** nenhum e-mail é enviado e a mensagem mostrada é a mesma

### Requirement: Nova senha pelo link
Em `/trabalhe-conosco/redefinir-senha/{token}`, o sistema SHALL aceitar só token do tenant ativo e dentro do prazo, exigir senha de no mínimo 8 caracteres igual à confirmação, gravar a senha em hash, apagar o token e redirecionar para o login com "Senha redefinida com sucesso! Faça login.".

#### Scenario: Link vencido
- **WHEN** o candidato abre o link mais de 1 hora depois do pedido
- **THEN** é levado ao pedido de nova senha com "Link inválido ou expirado. Solicite um novo."

#### Scenario: Senhas diferentes
- **WHEN** a nova senha e a confirmação não coincidem
- **THEN** a senha não muda e aparece "As senhas não coincidem."
