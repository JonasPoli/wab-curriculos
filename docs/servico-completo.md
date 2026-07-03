analise profundamente como é este sistema base, como as imagens são armazenadas.
Entenda a evolução para um sistema multi-tenant de curriculos
[docs](file;file:///Volumes/Dados/work/curriculos/docs) e planeje completamente como deve ser este sistema.

A estrutura multi-tenant pode ser conferida em  /Volumes/Dados/work/wab-sites

Monte o projeto completo em várias fases, pensando que podemos ter algum problema e precisar parar, ajustar e continuar a partir da fase seguinte.

Quando criar as entities, ao criar os CRUDs use sempre o [CustomMakeCrud.php](file;file:///Volumes/Dados/work/curriculos/src/Maker/CustomMakeCrud.php) 
Depois, volte e ajuste os cruds para que os campos fiquem com seus rótulos, listagem e form, com o label em portugues.

Use todos os recursos, depois do crud criado, existentes e apresentados em [exemplo](file;file:///Volumes/Dados/work/curriculos/templates/admin/exemplo) 

Volte, despois dos cruds criados, em cada uma das listagens para exibir apenas os campos que cabem na tela, melhorando a experiência do usuário, a apresentação dos campos, etc.
Use sempre badges nas listagens e enum
As imagens, quando existirem, devem sempre ser listadas com thumb e no form, quando a imagem já existir, mostrar o thumb e o botão de remover a imagem.

As entidades que possuem a necessidade de serem ordenadas, com o campo position, por exemplo, devem possuir o sistema de arrastar e soltar e salvar na listagem, como em /Volumes/Dados/work/wab-sites

Ao terminar, cada fase, documente o que precisa ser explicado no readme
Ao terminar as entities e os migrations, crie um command para injetar registros, inclusive com as imagens. Gere algumas imagens adequadas, crie uma pasta de imagens a serem usadas no sistema de injetar dados e use-as. Este sistema de gerar dados pode ser que seja ajustado, e as imagens já vão existir para ajudar a testar. 

Na listagem dos Tenants, né necessário ter um botão "impersonar" Isso deve permitir que o usuário atual seja automaticamente e instantaneamente "trocado" para aquele tenant, sem precisar de senha, já acessando numa nova guia o dashboard da área administrativa do Tennant. Impersonado, deverá ter um menu "Sair da impersonação" ou um nome mais curto.
Quando desimpersonar deve voltar ao painel administrativo do superusuário na listagem de tenants