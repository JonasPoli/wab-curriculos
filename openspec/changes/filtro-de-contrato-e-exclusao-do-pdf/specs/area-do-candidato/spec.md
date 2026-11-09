## MODIFIED Requirements

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
