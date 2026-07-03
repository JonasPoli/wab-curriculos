# Documento de Especificação de Requisitos (Requirements Specification)
## Portal Trabalhe Conosco Multi-Tenant — Procordis

---

### 1. Requisitos Funcionais (RF)

#### RF01: Customização do Banner Inicial por Tenant
O sistema deve permitir que o administrador do Tenant configure o título e o texto de apoio da seção de entrada (Banner) da página Trabalhe Conosco, bem como a imagem ou cor de fundo do banner.

#### RF02: Customização do Texto Institucional por Tenant
O sistema deve permitir que o administrador do Tenant configure um título e corpo de texto apresentando as vantagens e a cultura da empresa ("Por que trabalhar na [Empresa]?").

#### RF03: Formulário de Inscrição Inteligente e Condicional
O formulário de candidatura deve ser dividido em seções claras e possuir regras dinâmicas de exibição (front-end):
1. **Dados Pessoais**:
   - *Nome completo* (Obrigatório, texto, max 150 caracteres).
   - *Data de nascimento* (Opcional, data).
   - *E-mail* (Obrigatório, e-mail válido).
   - *Telefone/WhatsApp* (Obrigatório, padrão com máscara: `(XX) XXXXX-XXXX` ou `(XX) XXXX-XXXX`).
   - *Cidade* (Obrigatório, texto).
   - *Estado (UF)* (Obrigatório, select com lista de todos os 26 estados brasileiros + DF).
 2. **Informações Profissionais e Redes**:
   - *Cargos de Interesse* (Obrigatório, seleção de um ou mais cargos/funções mapeados dentro das áreas de interesse (`AreasOfInterest`) configuradas para o Tenant).
   - *Link do LinkedIn* (Opcional, URL).
   - *Link do Currículo Lattes* (Opcional, URL).
 3. **Formação Acadêmica (Múltiplas ocorrências - N)**:
   O formulário deve permitir a adição dinâmica de múltiplas formações acadêmicas, contendo:
   - *Escolaridade/Nível* (Obrigatório, select com opções: Ensino Fundamental, Ensino Médio, Curso Técnico, Ensino Superior Incompleto, Ensino Superior Completo, Pós-graduação, Mestrado, Doutorado).
   - *Instituição de formação* (Obrigatório, texto).
   - *Curso de formação* (Obrigatório se escolaridade >= Curso Técnico, texto).
   - *Status do curso* (Obrigatório, select: Concluído, Incompleto, Em andamento).
   - *Ano/Data de conclusão ou previsão* (Opcional, numérico AAAA ou formato data).
 4. **Experiências Profissionais (Múltiplas ocorrências - N)**:
   O formulário deve permitir a adição dinâmica de múltiplos históricos profissionais, contendo:
   - *Nome da empresa* (Obrigatório, texto).
   - *Cargo ocupado* (Obrigatório, texto).
   - *Data de início* (Obrigatório, data).
   - *Data de término* (Obrigatório se não for emprego atual, data).
   - *Emprego atual?* (Radio/Checkbox, se marcado, oculta e anula a data de término).
   - *Resumo das atividades* (Obrigatório, textarea, max 1.000 caracteres).
 5. **Registro Profissional Condicional**:
   - *Possui registro profissional ativo?* (Obrigatório, radio "Sim" / "Não").
   - Se "Sim", exibir e tornar obrigatório:
     - *Conselho profissional* (ex: CRM, COREN, CRF, CRP, CREFITO - texto).
     - *Número do registro* (texto, max 30 caracteres).
 6. **Disponibilidade**:
   - *Tipo de vaga desejada* (Obrigatório, checkbox de seleção múltipla: CLT, PJ, Estágio, Jovem Aprendiz, Banco de Talentos).
   - *Disponibilidade para início imediato?* (Opcional, radio "Sim" / "Não").
   - *Grade de Disponibilidade Semanal* (Obrigatório, matriz contendo checkboxes de seleção de turnos: Segunda a Sexta-feira, períodos da Manhã e Tarde, para cruzamento de grade de horário do RH).
 7. **Currículo**:
   - *Upload do arquivo* (Obrigatório, formato .pdf, tamanho máximo de 10 MB).
 8. **Mensagem Adicional**:
   - *Mensagem* (Opcional, textarea, max 1.000 caracteres).
   - *Experiência profissional resumida (Geral)* (Opcional, textarea, max 1.500 caracteres, para observações gerais sobre a trajetória).

#### RF04: Termos de Aceite LGPD
A página deve exibir três caixas de seleção obrigatórias imediatamente antes do envio, cujos textos explicativos e políticas associadas devem ser configuráveis pelo administrador em formato HTML no painel administrativo do Tenant:
1. `[ ]` *Declaro que as informações fornecidas são verdadeiras.*
2. `[ ]` *Texto de consentimento para tratamento de dados pessoais (configurável em HTML pelo admin - ex.: Autorizo a empresa a utilizar meus dados exclusivamente para fins de recrutamento, seleção e formação de banco de talentos, conforme a LGPD).*
3. `[ ]` *Declaro que li e concordo com a Política de Privacidade (com link abrindo modal ou página dedicada contendo o texto completo da política, também configurável em HTML pelo admin).*

#### RF05: Envio de Notificações por E-mail (Alertas de RH)
Ao finalizar o cadastro com sucesso, o sistema deve:
- Apresentar uma tela/mensagem de confirmação amigável.
- Enviar um e-mail de notificação para o endereço configurado nas preferências do Tenant contendo os dados estruturados do candidato e o arquivo PDF do currículo anexado.
- Enviar um e-mail de confirmação curto para o próprio candidato informando o recebimento.

#### RF06: Módulo de Exclusão de Dados pelo Candidato (LGPD)
O sistema deve disponibilizar um fluxo para que o titular dos dados solicite a exclusão de suas informações da base. Isso pode ser feito através de um formulário simples de "Solicitar Exclusão" que envia um token de confirmação para o e-mail cadastrado do candidato; ao clicar no token recebido por e-mail, todos os dados pessoais do candidato e seu currículo físico são apagados.

#### RF07: Área Autenticada do Candidato (Painel/Dashboard)
O portal de currículos deve disponibilizar uma área restrita e segura para o candidato, com login baseado no e-mail e senha cadastrados no primeiro acesso:
- **Cadastro de Conta**: O fluxo de envio do currículo cria automaticamente um registro em `User` associado.
- **Gerenciamento de Currículo**: O candidato pode logar e atualizar dados de contato, formação, experiências e carregar novo PDF.
- **Controle LGPD**: Opção integrada de solicitar exclusão definitiva imediata de dentro do painel autenticado.

---

### 2. Requisitos Não Funcionais (RNF)

#### RNF01: Isolamento de Dados por Tenant
Nenhum Tenant ou usuário administrativo de um Tenant poderá ter acesso a dados de candidatos pertencentes a outro Tenant. As consultas de banco de dados devem ser automaticamente isoladas por meio de um mecanismo do Doctrine ORM (Filtro SQL dinâmico que injeta `tenant_id` nas cláusulas WHERE), replicando o modelo do WAB Sites.

#### RNF02: Hospedagem e Configuração DNS (Multi-Domain)
O sistema deve ser compatível com a hospedagem em **DigitalOcean (Droplet)** gerenciada pelo painel **RunCloud**. Cada domínio do cliente será direcionado via registro CNAME ou A para o servidor RunCloud, o qual mapeará todos os hosts virtuais para o mesmo diretório da aplicação Symfony. O sistema resolverá o Tenant correspondente no evento `KernelEvents::REQUEST` com base no cabeçalho HTTP `Host`.

#### RNF03: Segurança no Armazenamento de PDFs
Os currículos em PDF de candidatos não devem ser armazenados em uma pasta pública exposta à internet diretamente. Eles devem residir em uma pasta privada do sistema (ex.: `/var/uploads/resumes/`). O download ou visualização do currículo pelo painel administrativo local deve ser processado por um controller seguro que verifica as permissões do usuário logado (RH do Tenant) e retorna o arquivo em formato StreamedResponse.

#### RNF04: Performance e Resiliência de Envio de E-mails
Os disparos de e-mail não devem travar a requisição HTTP do usuário candidato. O sistema deve utilizar uma fila assíncrona (Symfony Messenger) com um banco de dados de fila (ou Redis) para processar os envios em segundo plano, evitando lentidão na tela do usuário.

#### RNF05: Proteção Contra Spam e Abuso
O formulário de inscrição deve conter proteção obrigatória contra bots, utilizando **Google reCAPTCHA v3** (ou hCaptcha/Cloudflare Turnstile) configurado através de chaves públicas/privadas cadastradas no painel do SuperAdmin ou de cada Tenant de forma individual.

#### RNF06: Validação de Dados Duplicada
A validação de todos os dados do formulário deve ser efetuada em duas etapas:
1. **Front-end**: Interface responsiva e com feedback visual instantâneo usando CSS vanilla (ex.: `:invalid`, `:user-valid`).
2. **Back-end**: Validadores nativos da entidade do Symfony (Assert) para impedir o registro de dados corrompidos ou maliciosos.
