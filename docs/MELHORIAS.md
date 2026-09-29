# SYSTEM BAR — Plano de Melhorias

> Documento de referência para evolução do sistema **sem perder a premissa central**: um app para donos de pequeno comércio de 50/60+ anos, com pouca familiaridade com tecnologia.
>
> Cada item tem um **ID estável** (`B1`, `A1`, `V1`, `F1`, `T1`) para ser citado em commits, issues e conversas. Cada item traz **onde está**, **por que importa para este público** e **como validar** que foi resolvido.

**Stack atual:** Laravel 12 · Blade (sem Livewire/Inertia) · Tailwind CSS v4 (CSS-first, sem `tailwind.config.js`) · daisyUI 5 · Alpine 3 · MySQL. Multi-tenant por `user_id` (cada usuário cadastrado é um comércio isolado).

---

## Princípio orientador

Antes de qualquer item, o critério de decisão que deve valer em todas as escolhas daqui pra frente:

1. **Nada invisível.** Se a informação só aparece no `hover`, ela não existe — este público usa celular e não passa o mouse sobre as coisas.
2. **Nada pequeno.** 16px é o piso, não o padrão. Alvo de toque mínimo de 44px.
3. **Nada silencioso.** Toda ação dá resposta visível. Erro invisível é pior que erro feio.
4. **Nada irreversível sem aviso.** E o aviso diz **o quê** vai acontecer com **qual** registro, pelo nome.
5. **Nada em inglês.** Nem mensagem de validação, nem "Previous/Next" da paginação.

---

## 0. Sumário executivo — as 5 ações de maior retorno

| # | Ação | Esforço | Impacto |
|---|---|---|---|
| 1 | ~~**Remover o override de `.text-2xl` em `resources/css/app.css:36-41`**~~ **✅ FEITO** — ele reduzia para 16px justamente os textos grandes feitos para este público (§A1) | 1 linha | Altíssimo |
| 2 | ~~**Configurar locale pt-BR + criar `lang/pt_BR/`**~~ **✅ FEITO** — validação saía em inglês, e no e-mail duplicado aparecia a string crua `validation.unique` na tela (§B7) | ~1h | Altíssimo |
| 3 | ~~**Corrigir o subtotal dos itens de venda**~~ **✅ FEITO** — todo comprovante impresso mostrava `R$ 0,00` em cada linha (§B1) | 1 linha | Alto |
| 4 | ~~**Mostrar os erros de validação nos 4 formulários que não mostram**~~ **✅ FEITO** — o usuário salvava, falhava, e a tela voltava igual sem explicar nada (§B10) | ~1h | Alto |
| 5 | ~~**Trocar os ícones-only das tabelas por botões com texto**~~ **✅ FEITO** — "Editar" / "Excluir" com 44px (§A2, §A3) | ~2h | Alto |

**As 5 ações do sumário estão aplicadas.** O que sobrou de mais urgente, em ordem: `B2` (estoque negativo), `B5` (transação na venda), `B6` (máscara de moeda quebrada na edição de produto), `B3`/`B4` (exclusões que corrompem histórico).

---

# 1. Correções — o que hoje trava o uso real

Prioridade máxima. São defeitos, não preferências.

### B1 — Todo item de venda mostra `R$ 0,00` no comprovante ✅ FEITO

**Onde:** `app/Http/Controllers/SaleController.php:60` · `resources/views/sales/show.blade.php:48` · `database/migrations/2026_01_19_185750_create_sale_items_table.php`

O controller grava `'subtotal' => $subtotal` ao criar o item da venda. Mas `subtotal` **não é coluna** de `sale_items` **e não está em** `SaleItem::$fillable`. O Eloquent descarta silenciosamente. A view então renderiza `number_format($item->subtotal, ...)` sobre `null` → `R$ 0,00` em toda linha, mais um deprecation de PHP 8.1+. O "Total Geral" está certo porque lê `$sale->total_amount`, o que torna o comprovante **internamente contraditório**: linhas zeradas somando um total real.

**Por que importa:** é o documento que o dono do comércio entrega ao cliente. Um comprovante que se contradiz destrói a confiança no sistema todo — e a reação natural deste público não é reportar bug, é parar de usar.

**Como resolver:** duas saídas válidas, escolher uma.
- **Simples:** remover `'subtotal'` do `create()` e renderizar `$item->unit_price * $item->quantity` na view. Nenhuma migration.
- **Explícita:** adicionar a coluna `subtotal` na migration + `$fillable`. Vale se houver intenção futura de descontos por item (aí o subtotal deixa de ser derivável).

Recomendação: a simples. O valor é derivável e coluna derivada é coluna que dessincroniza.

**Aceite:** abrir uma venda com 2 itens de quantidades diferentes → cada linha mostra valor próprio, e a soma das linhas bate com o Total Geral.

**O que foi feito** (variação da saída simples, sem migration):

- `app/Models/SaleItem.php` — accessor `subtotal()` (`Attribute::get`) devolvendo `quantity * unit_price`. Escolhido em vez de mudar a view porque `$item->subtotal` passa a **existir de verdade** no modelo: a view do comprovante continua igual, e qualquer relatório ou recibo futuro que leia `subtotal` já lê o valor certo em vez de reinventar a multiplicação.
- `app/Http/Controllers/SaleController.php` — removida a chave `'subtotal' => $subtotal` do `items()->create()`. Era código morto que aparentava gravar e não gravava; a variável local `$subtotal` segue em uso para somar o `total_amount`.
- `tests/Feature/SaleReceiptTest.php` — registra uma venda com 2 itens de quantidades diferentes e confere que o comprovante mostra `R$ 31,50`, `R$ 14,50` e total `R$ 46,00`. É o aceite acima, automatizado. Sem factories (ainda não existem, §T1): os registros são criados direto pelas relações do usuário.

Nenhuma coluna nova, nenhuma migration. Efeito colateral resolvido: o `number_format(null, ...)` que gerava deprecation no PHP 8.1+ a cada linha do comprovante.

---

### B2 — Estoque fica negativo ✅ FEITO

**Onde:** `app/Http/Controllers/SaleController.php:42,63`

A validação é só `'items.*.quantity' => 'required|integer|min:1'`. Não existe checagem contra `stock_quantity`. A linha 63 faz `decrement()` direto. Vender 100 unidades de um produto com 3 em estoque funciona e deixa `-97`. O `create()` filtra produtos com `stock_quantity > 0`, mas isso só esconde o produto zerado — não limita a quantidade. O `<input>` em `sales/create.blade.php:39` tem `min="1"` e nenhum `max`.

**Por que importa:** o estoque é o motivo de existir da tela. Um número negativo não é interpretável — o usuário não sabe se é um bug, uma dívida ou um erro dele.

**Como resolver:** validar item a item contra o estoque disponível, com mensagem que **nomeia o produto e diz quanto tem**: *"Você tem apenas 3 unidades de Coca Cola 2L em estoque."* Uma mensagem genérica de "quantidade inválida" não ajuda ninguém aqui. Somar quantidades quando o mesmo produto aparece em duas linhas (ver B2b).

**B2b — linhas duplicadas do mesmo produto** ✅ **FEITO** — antes geravam dois `sale_items` e dois `decrement()`; o mesmo produto aparecia duas vezes no comprovante entregue ao cliente.

- `app/Http/Controllers/SaleController.php` — as linhas do formulário passam por `groupBy('product_id')` + soma das quantidades antes de gravar. Um item por produto, uma única baixa de estoque. O loop agora itera sobre as quantidades consolidadas, não sobre `$request->items` cru.
- `tests/Feature/SaleReceiptTest.php` — venda com o mesmo produto em 2 linhas (3 + 2) → 1 item de quantidade 5, total `R$ 52,50`, estoque caindo 5 (não 3 nem 2 duas vezes).

Isso também é a base do que falta do B2: a validação de estoque, quando entrar, precisa olhar essa quantidade consolidada — validar linha a linha continuaria deixando passar 3 + 3 num produto com 5 em estoque.

**Aceite:** tentar vender mais que o estoque → erro claro nomeando o produto, nenhuma venda criada, estoque intacto.

**O que foi feito** ✅

- `SaleController::store()` — a quantidade **consolidada** por produto é conferida contra `stock_quantity` antes de gravar qualquer coisa. Validar linha a linha deixaria passar 3 + 3 num produto com 5.
- A mensagem nomeia o produto e diz quanto tem: *"Você tem apenas 3 unidades de Coca Cola 2L em estoque."* — com singular/plural correto. Sobe como erro de validação, então aparece no `<x-form-errors />` que `sales/create` já tem.
- `tests/Feature/SaleStockTest.php` — venda acima do estoque (nenhuma venda criada, estoque intacto), duas linhas do mesmo produto somando acima do estoque, e o caso de borda: vender **exatamente** o que tem continua funcionando.
---

### B3 — Excluir um cliente derruba a lista de vendas (erro 500) ✅ FEITO

**Onde:** `app/Http/Controllers/CustomersController.php` (`destroy`) · `resources/views/sales/index.blade.php:51` · `resources/views/sales/show.blade.php:19`

A FK `sales.customer_id` é `onDelete('set null')` e o `destroy` do cliente é exclusão física. Depois de excluir um cliente que tem vendas, `$sale->customer->name` estoura *"Attempt to read property name on null"*. **A tela de Vendas para de abrir** — não uma venda, a lista inteira.

**Por que importa:** é o pior tipo de falha para este público. A ação ("excluir cliente") parece dar certo, e a quebra aparece depois, em outro lugar, sem relação aparente. Não há como o usuário ligar causa e efeito, nem se recuperar sozinho.

**Como resolver:** três camadas, todas valem a pena.
1. **Guarda na view** (`$sale->customer?->name ?? 'Cliente removido'`) — conserta a tela imediatamente.
2. **`SoftDeletes` em `Customer`** — o histórico de vendas mantém o nome do cliente. Este é o conserto real.
3. **Aviso na exclusão**: se o cliente tem vendas, dizer isso antes (*"Este cliente tem 12 compras registradas. Excluir vai remover o nome dele do histórico."*).

**Aceite:** excluir um cliente com vendas → lista de Vendas continua abrindo e as vendas antigas continuam identificáveis.

**O que foi feito** ✅

- `SoftDeletes` em `Customer` — a exclusão física deixou de acontecer, então a FK `set null` nunca dispara e nenhuma venda perde o cliente.
- `Sale::customer()` ganhou `->withTrashed()`: o cliente arquivado continua nomeado no histórico e no comprovante. Sem isso, arquivar teria o mesmo efeito visível de apagar.
- Guarda nas views mesmo assim (`$sale->customer?->name ?? 'Cliente removido'` em `sales/index` e `sales/show`) — `customer_id` segue `nullable`, e venda antiga do banco atual pode já estar sem cliente.
- O `confirm()` parou de prometer o que não é mais verdade: *"Ele sai da sua lista. As compras dele continuam no histórico."* em vez de "não pode ser desfeita".
- `tests/Feature/ArquivamentoTest.php` — exclui cliente com venda e confere que a lista de Vendas **abre** e continua mostrando "João da Silva", que ele some da lista de Clientes, e que uma venda sem cliente nenhum mostra "Cliente removido" em vez de quebrar.

**Item 3 também feito** ✅ — a lista de clientes carrega `withCount('sales')` e a modal de arquivar diz *"Este cliente tem 2 compras registradas. Ele sai da sua lista. As compras dele continuam no histórico."* (singular/plural certo; sem compras, a frase da contagem não aparece). Teste em `FuncionalidadesTest`.
---

### B4 — Excluir um produto apaga o histórico de vendas ✅ FEITO

**Onde:** `database/migrations/2026_01_19_185750_create_sale_items_table.php` · `app/Http/Controllers/ProductController.php` (`destroy`)

`sale_items.product_id` é `onDelete('cascade')`. Excluir um produto **apaga todos os itens de venda dele**, em todas as vendas passadas — enquanto `sales.total_amount` continua com o valor antigo. Resultado: vendas onde a soma dos itens não bate com o total, e faturamento histórico perdido de forma irrecuperável.

**Por que importa:** "parei de vender esse produto, vou tirar da lista" é a coisa mais natural do mundo para o dono do comércio fazer. E é destrutiva sem qualquer aviso.

**Como resolver:** `SoftDeletes` em `Product`, e a listagem passa a ocultar os excluídos. Vendas antigas mantêm o produto. Opcionalmente trocar o conceito de "Excluir" por **"Arquivar"** na interface — que é o que o usuário realmente quer dizer.

**Aceite:** excluir um produto que já foi vendido → vendas antigas continuam completas e com os itens visíveis.

**O que foi feito** ✅

- `SoftDeletes` em `Product` — o `onDelete('cascade')` de `sale_items.product_id` nunca mais chega a disparar, então os itens de vendas passadas continuam lá e a soma segue batendo com `total_amount`.
- `SaleItem::product()` ganhou `->withTrashed()` — o comprovante antigo continua nomeando o produto arquivado.
- Listagem de produtos e o `<select>` de `sales/create` já ocultam os arquivados sozinhos (é o comportamento padrão do `SoftDeletes`); o `confirm()` agora diz *"Ele sai da sua lista. As vendas já registradas continuam completas."*
- `tests/Feature/ArquivamentoTest.php` — vende 2 unidades, exclui o produto, confere que a venda mantém 1 item e R$ 20,00, que o produto sumiu da lista **e** que continua visível no comprovante.

**Rótulo trocado** ✅ — como tudo passa pelo `<x-row-actions>`, foi uma mudança só: botão, título da modal e botão de confirmação dizem **"Arquivar"**, e as mensagens de sucesso de produto e cliente dizem o que aconteceu (*"Produto arquivado. As vendas já registradas continuam completas."*). "Excluir" ficou só na exclusão de conta, que é exclusão de verdade.
---

### B5 — Venda sem transação de banco ✅ FEITO

**Onde:** `app/Http/Controllers/SaleController.php:45-67`

A venda é criada com `total_amount => 0`, depois o loop cria itens e baixa o estoque, e só no fim o total é atualizado. Não há `DB::transaction`. Qualquer falha no meio (o `findOrFail` da linha 53, timeout, deadlock) deixa **uma venda persistida com R$ 0,00**, itens parciais e estoque já baixado.

**Por que importa:** venda fantasma de R$ 0,00 no meio da lista é impossível de o usuário diagnosticar ou limpar — não existe tela para excluir venda (ver B9).

**Como resolver:** envolver todo o `store()` em `DB::transaction()`. Combinado com B2, calcular o total antes e criar a venda já com o valor certo, eliminando o `update()` posterior.

**Aceite:** forçar exceção no meio do loop → nenhuma venda no banco, estoque intacto.

**O que foi feito** ✅

- Todo o `store()` passou a rodar dentro de `DB::transaction()`.
- O total é calculado **antes** de criar a venda, então ela nasce com o valor certo — o `update(['total_amount' => ...])` posterior, que era a origem do R$ 0,00 persistido, deixou de existir.
- Os produtos são carregados uma vez com `findMany()` em vez de um `findOrFail()` por item dentro do loop.
- `tests/Feature/SaleStockTest.php` — força exceção no `creating` de `SaleItem` e confere que não sobra venda nem baixa de estoque.
---

### B6 — Máscara de moeda quebrada na edição de produto ✅ FEITO

**Onde:** `resources/views/products/edit.blade.php:42,57` chamam `brlCurrencyMask(event)`, mas a função só existe em `resources/views/products/create.blade.php:9-30`

Na tela de **editar** produto, cada tecla digitada em Preço de Custo ou Preço de Venda lança `ReferenceError` no console. A máscara simplesmente não funciona — o campo aceita qualquer coisa sem formatar.

Agrava: `products/edit.blade.php:37,52` têm `step="0.01"` em `type="text"`, atributo que não faz nada ali.

**Por que importa:** o usuário digita o preço em `create` e vê formatar bonito; volta em `edit` e o mesmo campo se comporta diferente. Inconsistência é o que mais desorienta quem está aprendendo a usar.

**Como resolver:** extrair a máscara para `resources/js/` (ou um componente Blade compartilhado) e usar nas duas telas. Ver §V4 — hoje há 2 cópias da máscara de telefone e 1 cópia + 1 referência quebrada da de moeda.

**Aceite:** digitar `1234` em Preço de Venda na tela de edição → aparece `12,34`, sem erro no console.

**O que foi feito** ✅

- `resources/views/components/currency-mask.blade.php` (novo) — a função saiu de dentro de `products/create` e virou componente usado pelas **duas** telas. Uma cópia só; a de edição parou de dar `ReferenceError`.
- Os dois `step="0.01"` em campo `type="text"` de `products/edit` foram removidos — atributo sem efeito ali.
- `tests/Feature/SmokeTest.php` — confere que as duas telas carregam a definição da máscara **e** o `oninput` que a chama. Referência quebrada não volta sem o teste apontar.
---

### B7 — Mensagens de validação em inglês, e uma delas sai como código na tela ✅ FEITO

**Onde:** `config/app.php:81-85` · **não existe diretório `lang/`** no projeto · `app/Http/Controllers/Auth/RegisteredUserController.php:41` · `app/Http/Requests/ProfileUpdateRequest.php:28`

`'locale' => env('APP_LOCALE', 'en')`, `fallback_locale` também `en`, e nenhum arquivo de tradução publicado. Consequências:

- Todas as mensagens do framework saem em inglês: *"The name field is required."*, `auth.failed`, `passwords.*`.
- **Toda chamada `__('...')` nas views é decorativa.** A interface aparece em português porque as strings pt-BR *são as chaves* — o `__()` não encontra tradução e devolve a chave. Funciona por acidente.
- **Bug visível:** `RegisteredUserController.php:41` e `ProfileUpdateRequest.php:28` fazem `__('validation.unique', ...)`. Sem `lang/`, isso renderiza a **string literal `validation.unique`** como mensagem de erro. Quem tenta se cadastrar com e-mail já usado vê `validation.unique` na tela.
- Sobras em inglês nas próprias views: `__('Delete Account')` e `__('Password')` em `profile/partials/delete-user-form.blade.php:4,31`, `__('Your email address is unverified.')`, e todo o `auth/verify-email.blade.php`.

**Por que importa:** este é o item de maior desproporção entre esforço e impacto no documento. Um público 50+ que não fala inglês recebe, no momento em que mais precisa de ajuda (o erro), um texto que não entende — ou um código de programador.

**Como resolver:** `APP_LOCALE=pt_BR` e `APP_FALLBACK_LOCALE=pt_BR` no `.env` e no `.env.example`; `faker_locale` para `pt_BR`; criar `lang/pt_BR/validation.php`, `auth.php`, `passwords.php`, `pagination.php` (o pacote `laravel-lang/lang` resolve isso de uma vez). Traduzir também os `attributes` do validation para os campos aparecerem como "Nome", "Preço de Venda" e não "name", "sale_price".

**Aceite:** enviar o formulário de produto vazio → erros em português nomeando os campos em português. Cadastrar com e-mail repetido → mensagem em português, não `validation.unique`.

**O que foi feito:**
- `.env` e `.env.example`: `APP_LOCALE`, `APP_FALLBACK_LOCALE` e `APP_FAKER_LOCALE` para `pt_BR`.
- `config/app.php:81-85`: os *defaults* do `env()` também passaram para `pt_BR`, para um deploy sem essas variáveis definidas não voltar para inglês.
- Criado `lang/pt_BR/` com `validation.php`, `auth.php`, `passwords.php` e `pagination.php`. **`validation.php` está completo** — cobre todas as chaves da versão do framework, porque chave ausente volta a aparecer crua na tela (era exatamente o sintoma deste bug).
- As regras que este sistema usa (`required`, `numeric`, `integer`, `min`/`max`, `email`, `unique`, `confirmed`, `current_password`, `exists`, `in`) foram escritas em linguagem direta — `required` é *"Preencha o campo :attribute."*, não *"O campo :attribute é obrigatório."*. As regras que o sistema não usa seguem tradução literal.
- `validation.attributes` mapeia as colunas para o nome que aparece no formulário: `sale_price` → "preço de venda", `stock_quantity` → "quantidade em estoque", `customer_id` → "cliente".
- `validation.custom` para os casos onde a mensagem genérica confundiria: venda sem cliente → *"Escolha um cliente para esta venda."*; venda sem itens → *"Adicione pelo menos um produto à venda."*; senha e confirmação diferentes → *"A senha e a confirmação da senha não são iguais."*
- Sobras em inglês nas views traduzidas: `auth/verify-email.blade.php` (4), `auth/confirm-password.blade.php` (3), `profile/partials/delete-user-form.blade.php` (2), `update-profile-information-form.blade.php` (1). E os rótulos `__('Email')` → `__('E-mail')` em 4 telas, para casar com o nome do campo nas mensagens de erro.
- A paginação passou a sair em português sem publicar view — a view padrão do Laravel já usa `__('pagination.previous')`.
- **Teste:** `tests/Feature/LocalizationTest.php` — 4 casos, incluindo o do `validation.unique` cru, que era o bug visível. Suíte completa: 29 passando.

**Não foi feito (fora do escopo do B7):** o `verify-email.blade.php` foi traduzido, mas continua **inalcançável** — a verificação de e-mail está desativada (ver §T6). Traduzir não ativou o fluxo.

---

### B8 — Horário das vendas 3 horas adiantado ✅ FEITO

**Onde:** `config/app.php:68` → `'timezone' => 'UTC'`, enquanto as views formatam `->format('d/m/Y H:i')` (`sales/index.blade.php:49`, `sales/show.blade.php:24`)

Uma venda registrada às 20h aparece como 23h. Perto da meia-noite, aparece **no dia seguinte**.

**Por que importa:** o dono do comércio confere as vendas do dia pelo horário. Data errada no comprovante é problema real, não estético. E vira bloqueio para §F1 (totais do dia).

**Como resolver:** `'timezone' => 'America/Sao_Paulo'`. Manter UTC no banco e converter na exibição é a alternativa mais correta a longo prazo, mas para um sistema de um fuso só, mudar o timezone da aplicação resolve com uma linha.

**Aceite:** registrar uma venda → o horário exibido é o do relógio de parede.

**O que foi feito** ✅ — `config/app.php:68` passou de `UTC` para `America/Sao_Paulo`. Uma linha. Desbloqueia o "vendido hoje" de §F1.
---

### B9 — 5 rotas registradas sem método no controller ✅ FEITO *(a lacuna de cancelar venda segue em F4)*

**Onde:** `routes/web.php:23,26,30`

`Route::resource` registra as 7 rotas do CRUD, mas:
- `ProductController` e `CustomersController` **não têm `show()`** → `products.show` e `customers.show` retornam 500 (`BadMethodCallException`).
- `SaleController` **não tem `edit()`, `update()` nem `destroy()`** → `sales.edit`, `sales.update`, `sales.destroy` idem.

Nada linka para elas hoje, então é latente. Mas o corolário é uma lacuna funcional séria: **uma venda registrada por engano não tem saída nenhuma.** Não há editar, não há excluir, não há cancelar.

**Como resolver:** `->only([...])` nos três resources para a superfície ficar honesta. E tratar a lacuna de verdade em §F4.

**Aceite:** acessar `/products/1` → 404, não 500.

**O que foi feito** ✅ — `->except('show')` em `products` e `customers`, `->only(['index','create','store','show'])` em `sales`. **Resultado real: 405, não 404** — a URI `/products/{id}` continua registrada para `PUT`/`DELETE`, então o `GET` bate em método não permitido. O que importa é que deixou de ser 500 (`BadMethodCallException`). Coberto em `tests/Feature/SmokeTest.php`.
---

### B10 — Erros de validação invisíveis em 4 dos 5 formulários ✅ FEITO

**Onde:** o bloco `@if ($errors->any())` existe **só** em `resources/views/products/create.blade.php:38-47`. Não existe em `products/edit`, `customers/create`, `customers/edit`, `sales/create`.

O usuário preenche, clica em Salvar, e a página **volta exatamente igual, sem nenhuma mensagem**. Pior em `customers/create.blade.php:17,22,46`: os campos não usam `old()`, então **tudo que foi digitado é apagado**. Nome, telefone e observações somem sem explicação.

Também não há erro por campo (`aria-invalid`, `aria-describedby`) em nenhuma tela de CRUD, e `session('error')` não é renderizado em lugar nenhum do app.

**Por que importa:** este é o cenário que faz o usuário concluir "o sistema não funciona" e desistir. Ele não erra de propósito — ele não sabe que errou. E ao tentar de novo, tem que digitar tudo outra vez.

**Como resolver:** criar `<x-form-errors />` (§V4) e usar nos 5 formulários; adicionar `old()` em todos os campos de `customers/*`; marcar o campo com problema visualmente, não só listar no topo.

**Aceite:** enviar cada um dos 5 formulários com erro → mensagem visível em português **e** os dados digitados preservados.

**O que foi feito** ✅

- `resources/views/components/form-errors.blade.php` (novo) — resumo dos erros no topo do formulário, borda vermelha de 2px, `role="alert"` + `aria-live="assertive"` para leitor de tela anunciar sem o usuário ter que procurar. Título no singular ou plural conforme a quantidade de erros.
- Aplicado nos **5** formulários: `products/create` (substituindo o bloco copiado que existia só ali), `products/edit`, `customers/create`, `customers/edit`, `sales/create`.
- `customers/create` — `old()` em nome, telefone e observações. Era o pior caso: o usuário perdia tudo que havia digitado.
- **Marcação por campo:** `@error(...)` põe borda vermelha de 2px e `aria-invalid="true"` no campo com problema, nos dois formulários de produto e nos dois de cliente, mais o `<select>` de cliente em `sales/create`. Não é só lista no topo.
- Os `<label>` de `customers/*` ganharam `for` (e os campos, `id`) — sem isso a marcação por campo não tem a quem se associar, e o rótulo não era clicável.
- `tests/Feature/FormErrorsTest.php` — salva cliente sem nome e confere que a mensagem aparece **e** que telefone e observações digitados continuam na tela; salva produto sem campos e confere o título no plural.

Ficou de fora, é `A7`: `session('error')` continua sem ser renderizado em lugar nenhum.

---

# 2. Acessibilidade e UX para 50+

O que já está certo e deve ser preservado: tiles grandes de destino único no dashboard, inputs `py-3 text-lg`, labels `text-xl font-bold` nos CRUDs, `aria-current="page"` correto na navegação, `sr-only` nos botões de ícone do menu, `x-modal` acessível com foco preso e ESC.

### A1 — Tipografia: um override anula todo o resto ✅ FEITO

**O problema central de todo este documento.**

`resources/css/app.css:36-41`:
```css
@layer utilities {
    .text-2xl {
        font-size: 1rem;      /* Tailwind: 1.5rem */
        line-height: 1.25rem; /* e line-height apertado demais */
    }
}
```

**Toda classe `text-2xl` do app renderiza a 16px**, não 24px. O que isso encolhe:

| Elemento | Arquivo | Deveria ser |
|---|---|---|
| Rótulos dos 4 tiles do dashboard | `dashboard.blade.php:21,37,53,71` | 24px |
| Botão **"Finalizar Venda"** — a ação mais importante do sistema | `sales/create.blade.php:47` | 24px |
| **Total Geral** do comprovante | `sales/show.blade.php:55` | 24px |
| Nome do cliente e data no comprovante | `sales/show.blade.php:19,24` | 24px |
| CTAs da página inicial | `welcome.blade.php:28,33,39` | 24px |

Há uma tentativa de compensação em `dashboard.blade.php:80-90` — um `<style>` inline com o comentário *"Ajustes para acessibilidade 50+"* setando `body { font-size: 1.1rem }`. Mas ele (a) vale **só na página do dashboard**, (b) está **fora do `<head>`**, e (c) **não alcança** o override de `.text-2xl`, porque utility ganha de elemento.

Efeito colateral de contraste: o tile "Configurações" usa texto branco sobre `#4682B4`, que dá **4,11:1**. Isso passa em AA apenas como *texto grande* (≥18,66px em negrito). Com o override, o rótulo é 16px em negrito → **não é texto grande → reprova em AA**. Corrigir a fonte corrige o contraste de graça.

**Como resolver:**
1. **Apagar o bloco `@layer utilities`** de `app.css`.
2. Definir a base tipográfica no lugar certo — `@layer base { html { font-size: 112.5%; } }` (18px) — e remover o `<style>` de `dashboard.blade.php`.
3. Eliminar `text-xs` (12px) do app. Onde está hoje: cabeçalhos de **todas** as tabelas (`products/index.blade.php:29,32,35,38,41`; `sales/index.blade.php:29-42`; `customers/index.blade.php:27-29`; `sales/show.blade.php:36-39`), dica de senha (`auth/register.blade.php:28`), e — ironicamente — a **lista de erros de validação** (`products/create.blade.php:41`), renderizada menor que os campos que ela descreve.
4. Os cabeçalhos de tabela combinam os três piores fatores de uma vez: `text-xs` + `uppercase tracking-wider` + `text-gray-500`. Menor tamanho, pior legibilidade de caixa e menor contraste, simultaneamente. Trocar por `text-base` em caixa normal e `text-gray-700`.
5. Subir os componentes Breeze — `components/primary-button.blade.php:1`, `secondary-button`, `danger-button` estão em `text-xs uppercase tracking-widest`, e `components/input-label.blade.php:3` em `text-sm`. **São 4 arquivos que consertam toda a área de autenticação e o perfil de uma vez.**

**Aceite:** medir com o inspetor — nenhum texto do app abaixo de 16px; rótulos dos tiles em 24px.

**O que foi feito** ✅

- `resources/css/app.css` — bloco `@layer utilities` **apagado**. Base tipográfica movida para o lugar certo: `@layer base { html { font-size: 112.5% } }` (18px). Como todo o Tailwind é em `rem`, texto e espaçamento escalam juntos.
- O `letter-spacing` que vivia no `<style>` da página do dashboard virou regra de `body` no `app.css` — a intenção era boa, o escopo estava errado. O `<style>` foi removido de `dashboard.blade.php`.
- **`text-xs` não existe mais no app.** Eram os cabeçalhos das 4 tabelas (`products/index`, `sales/index`, `customers/index`, `sales/show`) e as 3 dicas de senha (`register`, `reset-password`, `update-password-form`).
- Cabeçalhos de tabela: `text-xs uppercase tracking-wider text-gray-500` → `text-base font-semibold text-gray-700`. Os três piores fatores saíram de uma vez.
- Pills de status (`products/index`, `sales/show`) subiram de `text-sm px-3 py-1` para `text-base px-4 py-2`, e de `text-*-800` para `text-*-900`.
- Componentes Breeze, os 4 arquivos que consertam auth e perfil de uma vez: `primary`/`secondary`/`danger-button` saíram de `px-4 py-2 text-xs uppercase tracking-widest` para `min-h-11 px-6 py-3 text-base` sem caixa alta; `input-label` de `text-sm` para `text-lg`; `input-error` de `text-sm text-red-600` para `text-base font-medium text-red-700`.

Verificado no CSS que o Vite serve de fato — `.text-2xl` voltou a resolver pelo token do Tailwind (`var(--text-2xl)`, 1,5rem = **27px** na base de 18px) em vez do `1rem` forçado, e `html { font-size: 112.5% }` está presente.

**`text-sm` resolvido** ✅ — os 8 restantes (textos de apoio do perfil, `login`, `forgot-password`, `confirm-password`, `auth-session-status` e a dica de `products/create`) viraram `text-base` (18px), e os `text-gray-500/600` deles viraram `700`. Escolhida a varredura em vez de subir a base para 116%: mexer na base reescalaria todo o espaçamento do app por causa de 8 textos. Não sobrou `text-sm` em view nenhuma.

---

### A2 — Alvos de toque pequenos demais e perigosamente próximos ✅ FEITO

**Onde:** `products/index.blade.php:67-92` · `customers/index.blade.php:43-66`

As ações de cada linha são **SVG de 24×24 sem padding**, separados por `gap-4` (16px). Muito abaixo do mínimo de 44px. E o pior: **"Editar" e "Excluir" ficam a 16px um do outro**, sem rótulo, distinguíveis apenas por cor (azul/vermelho) e forma do desenho.

Para mãos com menos precisão, é um erro esperando acontecer — e o erro possível é a exclusão.

Também pequenos:
- `<select>` de status da venda: `px-3 py-1 text-sm w-36` → ~28px de altura (`sales/index.blade.php:59-62`).
- Botões Breeze: `px-4 py-2 text-xs` → ~30px (`components/primary-button.blade.php`).
- Links de paginação: view padrão do Laravel, alvos pequenos e sem customização.

Aceitáveis: FAB de novo produto (`p-3` + 24px ≈ 48px, `products/index.blade.php:103`), botão do menu (40px + área estendida por `absolute -inset-0.5`, `navigation.blade.php:21`).

**Como resolver:** substituir os ícones-only por **botões com texto** — "Editar" e "Excluir" — com no mínimo `py-3 px-4`, separados por espaço generoso, e **Excluir visualmente distinto** (contorno vermelho, não só ícone vermelho). Texto é mais rápido de ler que ícone para quem não cresceu com essas convenções.

**O que foi feito** ✅

- `products/index` e `customers/index` — os 4 SVG de 24px viraram botões com rótulo **"Editar"** e **"Excluir"**: `min-h-11 px-5 py-3 text-base font-bold`, contorno de 2px, `gap-3`, e `focus-visible:outline` (antes não havia foco visível em ação nenhuma de linha). Excluir tem contorno vermelho próprio, não só o ícone tingido de vermelho.
- `sales/index` — "Ver Detalhes" era um link solto de texto; virou botão com a mesma altura mínima de 44px. E o `<select>` de status saiu de `px-3 py-1 text-sm w-36` (~28px de altura) para `min-h-11 px-4 py-3 text-base w-44`.
- **Bônus de `A6`, uma string cada:** o `confirm()` agora **nomeia o registro** — *"Excluir o produto Coca Cola 2L? Esta ação não pode ser desfeita."* em vez de "Tem certeza que deseja excluir este produto?". Usa a diretiva `@js()` do Blade, que codifica em JSON com `JSON_HEX_QUOT|JSON_HEX_APOS`, então nome com aspas ou apóstrofo não quebra o JavaScript. **Continua sendo o `confirm()` nativo** — trocar pelo `x-modal` acessível segue pendente em `A6`.
- `tests/Feature/SmokeTest.php` — abre as 10 telas principais (`assertOk`) e confere que as listas mostram "Editar" e "Excluir" como texto. Dez views foram editadas nesta rodada; erro de Blade só aparece ao renderizar.

**Continua pendente em `A2`:** a paginação segue sendo a view padrão do Laravel, com alvos pequenos. Precisa de `php artisan vendor:publish --tag=laravel-pagination` para customizar — é uma tarefa própria, não um ajuste de classe.

**O que foi feito na paginação** ✅ — `resources/views/vendor/pagination/tailwind.blade.php` publicada e reescrita: dois botões de 44px ("« Anterior" / "Próxima »", já em pt-BR pelo `lang/pt_BR/pagination.php`) e a posição por extenso, **"Página 2 de 6"**, que é a pergunta que o usuário de fato tem. A lista de números de página saiu — para quem tem 60 produtos ela é ruído, e a resposta real é a busca de §F3. As outras 8 views publicadas pelo `vendor:publish` foram apagadas: só a `tailwind` é usada.
---

### A3 — Botões de ícone sem nome acessível ✅ FEITO

**Onde:** `products/index.blade.php:67` (editar), `:80` (excluir), `:103` (FAB) · `customers/index.blade.php:43,57`

Nenhum tem `aria-label`, `title` ou `sr-only`. Um leitor de tela anuncia um link vazio. E como não há `title`, nem o usuário com mouse consegue descobrir o que o ícone faz.

O padrão correto **já existe no projeto** — `navigation.blade.php:22,81` fazem certo com `<span class="sr-only">`. É só aplicar. Resolvido de graça se §A2 for implementado com texto visível.

Adjacente: os SVGs decorativos dos tiles do dashboard não têm `aria-hidden="true"` (`dashboard.blade.php:15,31,47,63`), enquanto os da navegação têm.

**O que foi feito** ✅ — os 4 ícones das tabelas viraram texto (§A2), então o nome acessível passou a ser o próprio rótulo: não sobrou ícone-only nas listas. O FAB de novo produto ganhou `title` + `<span class="sr-only">` e `aria-hidden="true"` no SVG. Os 4 SVG decorativos dos tiles do dashboard também ganharam `aria-hidden="true"` — o rótulo ao lado já diz o que o tile é, e o leitor de tela não precisa anunciar o desenho.

---

### A4 — Formulários: rótulos desconectados e teclado errado no celular ✅ FEITO

**Rótulo sem `for`/`id`** — clicar no rótulo não foca o campo, e leitor de tela não associa:
- `customers/create.blade.php:16,45` e `customers/edit.blade.php:17,48` — o campo `phone` tem `id` mas o rótulo não tem `for`.
- `sales/create.blade.php:29,38` e as cópias geradas por JS em `:67,76`.

**Tipo de campo errado:**
- Telefone é `type="text"` (`customers/create.blade.php:22`) com máscara em JS. **No celular abre o teclado alfabético** para um campo que só aceita dígitos. Falta `type="tel"` / `inputmode="numeric"`.
- Preços são `type="text"` (`products/create.blade.php:63,88`) sem `inputmode="decimal"` — mesmo problema.

**Outros:**
- `min="0"` existe em `products/create.blade.php:100` e **foi perdido** em `products/edit.blade.php:66-74`.
- `placeholder` como única dica em todos os campos de moeda e telefone — placeholder desaparece ao focar, exatamente quando a pessoa mais precisa dele. Virar texto de apoio permanente abaixo do campo.
- Nenhum `autocomplete` fora das telas de autenticação.
- `profile/partials/delete-user-form.blade.php:31`: o rótulo do campo de senha é `sr-only` — o campo mais destrutivo do app tem placeholder como única indicação.

**O que foi feito** ✅

- Telefone virou `type="tel" inputmode="numeric" autocomplete="tel"` — no celular abre o teclado numérico, não o alfabético.
- Preços ganharam `inputmode="decimal"`.
- Dica **permanente** abaixo dos campos, com `aria-describedby` no telefone: *"Com DDD, só números. Exemplo: (11) 98765-4321"* e *"Digite só os números: 1234 vira 12,34."* O `placeholder` continua, mas deixou de ser a única dica — ele some justo quando a pessoa foca o campo.
- `sales/create`: os rótulos de Produto e Quantidade ganharam `for`/`id`, **inclusive nas linhas geradas por JavaScript**, e subiram de `text-sm text-gray-600` para `text-base text-gray-700`.
- `resources/views/components/phone-mask.blade.php` (novo): a máscara de telefone era o mesmo bloco copiado em `customers/create` e `customers/edit`, cada um preso a `getElementById('phone')`. Agora vale para qualquer campo com `data-mask-telefone`.
- **Correção de rastro:** o `min="0"` de `products/edit` e os `for` dos rótulos de `customers/*` já existiam — entraram na rodada do `B10`.
---

### A5 — Informação e affordance que só existem no hover ✅ FEITO

- **Números 1–4 dos tiles** (`dashboard.blade.php:22-24,38-40,54-56,72-74`): `opacity-0 group-hover:opacity-100` — **invisíveis em qualquer celular ou tablet**. E quando aparecem, são `text-white/30`. Se são atalhos de teclado, não estão implementados; se são numeração, deviam estar sempre visíveis. Numerar os tiles ("1. Registrar Compra") é, aliás, uma boa ideia para este público — dá ordem a um menu.
- **Links de auth sem sublinhado em repouso** (`auth/login.blade.php:32`, `register.blade.php:47`): o sublinhado só aparece no hover, via `hover:after:scale-x-100`. Em repouso, "Esqueceu sua senha?" é texto cinza indistinguível do resto. Link tem que parecer link **antes** do mouse chegar.
- **Nenhum `focus-visible`** nas ações de linha das tabelas — navegação por teclado sem indicação de onde está.
- **`lg:hover:scale-105`** nos tiles (`dashboard.blade.php:13,29,45,61`) e nenhum `prefers-reduced-motion` no projeto inteiro.
- Feedback de linha por `hover:bg-gray-50` sem equivalente de foco.
- "Ver Detalhes" em `sales/index.blade.php:74` distingue-se do texto vizinho **só pela cor** (`text-indigo-600`), sem sublinhado.

**O que foi feito** ✅

- Os números 1–4 dos tiles do dashboard eram `opacity-0 group-hover:opacity-100` — invisíveis em qualquer celular. Agora são permanentes (`text-white/70`): numeram o menu, que é o que dá ordem a este público.
- "Esqueceu sua senha?" e o link de cadastro tinham sublinhado só no `hover`, via `after:scale-x-100`. Agora é `underline` em repouso, `text-base` e com `focus-visible`. Link tem que parecer link antes de o mouse chegar.
- `prefers-reduced-motion: reduce` no CSS (o projeto inteiro não tinha nenhuma), zerando transições e o `lg:hover:scale-105` dos tiles.
- `tr:focus-within` nas listas: navegar por teclado agora mostra onde se está — era o mesmo problema do hover, do outro lado.
---

### A6 — Confirmação de ações destrutivas ✅ FEITO

**Estado atual** — parcialmente resolvido junto com §A2: os dois `confirm()` agora **nomeiam o registro** (*"Excluir o produto Coca Cola 2L? Esta ação não pode ser desfeita."*), então o usuário consegue verificar que clicou na linha certa.

**O que continua pendente aqui:** ainda é o `confirm()` nativo — diálogo minúsculo, sem estilo, sem foco gerenciado, com botões "OK/Cancelar" em vez de "Excluir/Manter". O `x-modal` acessível já existe no repositório e é usado só na exclusão de conta.

**Pior caso, e não tem confirmação nenhuma:** `sales/index.blade.php:59` — `<select name="status" onchange="this.form.submit()">`. Um giro de roda do mouse sobre o select focado, ou uma seta do teclado, **marca a venda como Paga e grava**. A ação é irreversível pela interface: o select fica `disabled` quando `paid` (`:61`) e não há como voltar.

**Como resolver:**
1. Trocar os dois `confirm()` por `<x-modal>` — **o componente já existe e é acessível** (`components/modal.blade.php`: foco preso, ESC, scroll travado). Hoje é usado em um único lugar, a exclusão de conta. A modal **nomeia o registro**: *"Excluir o cliente **João da Silva**? Esta ação não pode ser desfeita."*
2. O status da venda deixa de ser `<select onchange>` e passa a ser **um botão explícito** "Marcar como Pago" com confirmação. Botão é intencional; select não é.
3. Onde for irreversível, dizer que é irreversível — com essas palavras.

**O que foi feito** ✅

- **O pior caso saiu:** o `<select name="status" onchange="this.form.submit()">` virou um **botão** "Marcar como Pago" que nomeia o cliente e o valor na confirmação. Um giro de roda do mouse não aperta botão.
- **Guarda no servidor** (`SaleController::updateStatus`): a validação aceita só `paid`, e a venda já paga responde com mensagem de erro em vez de voltar para `pending`. A tela escondia o caminho de volta; o servidor não.
- O status pago virou selo verde "Pago", sem controle de formulário nenhum.

**Continua pendente:** a troca dos dois `confirm()` de exclusão pelo `<x-modal>`. Eles já nomeiam o registro e já dizem o que acontece (§B3/§B4), e o `x-modal` seria uma instância por registro na listagem — cabe junto com `<x-row-actions>` de §V4, não solto.
---

### A7 — Nenhum estado de carregamento, e feedback inconsistente ✅ FEITO

**Nenhum estado de carregamento em todo o app.** Sem spinner, sem `disabled` no submit, sem `aria-busy`. "Finalizar Venda" (`sales/create.blade.php:47`) clicado duas vezes — comportamento comum quando nada acontece na tela após o primeiro clique — **registra duas vendas e baixa o estoque duas vezes** (agravado por B5). Este público clica de novo justamente porque não houve resposta.

**Feedback inconsistente:**
- Banner de sucesso copiado 3 vezes com 2 marcações diferentes: `sales/index.blade.php:18-21` e `products/index.blade.php:18-21` (negrito "Sucesso!" + `role="alert"`) vs `customers/index.blade.php:19-21` (sem `role`, sem título).
- `session('error')` **nunca é renderizado** — não existe canal de mensagem de erro no app.
- Confirmações do perfil **desaparecem em 2 segundos** (`update-password-form.blade.php:43`, `update-profile-information-form.blade.php:58`) e são `text-sm text-gray-600` — o texto menos visível da página, sumindo antes de ser lido.

**Como resolver:** componente único `<x-alert>` (§V4); `disabled` + texto "Salvando..." no submit; aumentar ou eliminar o auto-hide das confirmações de perfil.

**O que foi feito** ✅

- `resources/views/components/alert.blade.php` (novo) — as 3 cópias do banner, com 2 marcações diferentes, viraram um componente só, renderizado **uma vez no layout**: toda tela do app ganha o aviso, sem precisar lembrar de copiar o bloco.
- **`session('error')` agora aparece.** Era o canal que não existia: nenhuma mensagem de erro do servidor chegava ao usuário.
- **Trava de duplo envio** no layout: ao enviar, o formulário marca `aria-busy`, o botão fica `disabled` e o texto vira "Salvando...". Um `pageshow` destrava tudo quando a pessoa volta pelo botão do navegador — senão a tela voltaria do cache com os botões mortos.
- `tests/Feature/UxTest.php` cobre o `session('error')` chegando na tela.

**Confirmações do perfil** ✅ — já não somem: *"Alterações salvas."* fica fixo, em `text-lg font-bold text-green-800`. (Estava resolvido no código e desatualizado aqui.)
---

### A8 — Nenhum estado vazio ✅ FEITO

Todos os índices usam `@foreach`, não `@forelse` (`products/index.blade.php:46`, `customers/index.blade.php:34`, `sales/index.blade.php:46`). **A primeira tela que um usuário novo vê é um cabeçalho de tabela vazio**, sem uma palavra de orientação.

Igual em `sales/create.blade.php`: sem produtos ou clientes cadastrados, os `<select>` aparecem vazios sem nenhuma explicação — e como cliente é obrigatório, a venda é impossível e o motivo é invisível.

**Por que importa:** é o momento de maior risco de abandono, e o único momento em que o app tem certeza de que a pessoa é nova.

**Como resolver:** `@forelse` com estado vazio que **instrui e oferece o próximo passo**: *"Você ainda não cadastrou produtos. Comece cadastrando o primeiro."* + botão grande. Em `sales/create`, quando faltar cliente ou produto, mostrar o que falta e o link para cadastrar.

**O que foi feito** ✅

- `resources/views/components/empty-state.blade.php` (novo) + `@forelse` nos 3 índices: *"Você ainda não cadastrou nenhum produto. Comece pelo que mais vende."* com botão grande para o próximo passo.
- `sales/create` agora **diz o que falta** em vez de mostrar `<select>` vazios: *"Para registrar uma venda falta um cliente cadastrado e um produto com estoque."* — e o formulário não é renderizado, porque a venda é impossível. Cobre também o caso invisível de *ter* produtos, todos zerados.
- `tests/Feature/UxTest.php` — usuário novo vê a orientação nas 3 listas e o motivo na tela de venda.
---

### A9 — Tabelas exigem rolagem horizontal no celular ✅ FEITO

`products/index.blade.php:24` e `sales/index.blade.php:24` usam `overflow-x-auto`. **`customers/index.blade.php:24` não tem nem isso** — a tabela transborda o cartão.

Rolagem horizontal é um obstáculo conhecido para este público: a rolagem vertical é aprendida, a horizontal não é descoberta — a coluna "Ações" simplesmente não existe para quem não sabe arrastar.

**Como resolver:** abaixo de `sm`, trocar tabela por **cartões empilhados** — um cartão por produto/cliente/venda, com rótulo e valor em linhas próprias e os botões de ação em largura total. Nada de rolagem lateral.

**O que foi feito** ✅

- Abaixo de 768px as três tabelas viram **cartões empilhados**: um cartão por registro, rótulo e valor em linhas próprias, ações em largura total. Nada de rolagem lateral.
- Feito em CSS (`.tabela-cartoes` em `resources/css/app.css`) e não duplicando a marcação em Blade: cada `<td data-rotulo="Nome">` serve os dois formatos, e o rótulo da coluna vira o rótulo da linha do cartão via `::before`. Duas marcações paralelas divergiriam na primeira coluna nova.
- `customers/index`, que não tinha nem `overflow-x-auto` e estourava o cartão, entrou junto.
- Conferido no navegador em 390px, 720px e 1280px.
---

### A10 — Breakpoints redefinidos criam uma faixa quebrada ✅ FEITO

`resources/css/app.css:16-21` redefine a escala:

```css
--breakpoint-md: 64rem;  /* 1024px — o padrão do Tailwind é 768px */
--breakpoint-lg: 80rem;  /* 1280px — padrão 1024px */
--breakpoint-xl: 90rem;  /* 1440px — padrão 1280px */
```

`md:` dispara a **1024px**, não 768. Então todo `md:grid-cols-2` / `md:grid-cols-3` (`dashboard.blade.php:10`, `sales/create.blade.php:27`, `products/create.blade.php:56`, `sales/show.blade.php:16`) fica em uma coluna até 1024px — tablets e celulares na horizontal incluídos. E `products/index.blade.php:8` mantém o botão "+ Novo Produto" escondido (`hidden md:flex`) com o FAB no lugar dele até 1024px.

Enquanto isso a **navegação** troca para desktop em `sm:` (640px). **Entre 640 e 1024px o app tem menu de desktop com corpo em modo celular.** É a faixa de um iPad na vertical.

**Como resolver:** decidir **um** ponto de virada e usá-lo nos dois lugares. Recomendação: voltar `md` ao padrão de 768px e trocar a navegação de `sm:` para `md:` — em telas menores que 768px o app fica coerentemente em modo celular.

**O que foi feito** ✅ — `--breakpoint-md` voltou ao padrão do Tailwind (`48rem`/768px) e a navegação passou de `sm:` para `md:`. Agora existe **um** ponto de virada: abaixo de 768px é tudo celular — menu, grades e as tabelas-cartão de §A9. A faixa do iPad em pé, que tinha menu de desktop com corpo de celular, deixou de existir. `lg` e `xl` ficaram como estavam: não era ali o problema.
---

### A11 — Consistência e detalhes ✅ FEITO *(menos o welcome.blade.php)*

- **`customers/edit.blade.php:4` diz "Cadastrar Novo Cliente"** na tela de edição. O usuário não tem confirmação de que está editando e não criando — e pode achar que duplicou o cliente.
- **`<title>` idêntico em todas as páginas** (`layouts/app.blade.php:8` → só `config('app.name')`). Abas e histórico do navegador ficam indistinguíveis. Um público que abre várias abas e usa o botão Voltar sente isso.
- **`min-h-screen` duplicado**: o shell já é `min-h-screen` (`layouts/app.blade.php:21`) e cada página aplica de novo num div interno (`dashboard.blade.php:8`, `products/index.blade.php:14`, e todas as outras). Toda página tem no mínimo o dobro da altura da tela, com rolagem morta no fim. Confuso: rolar e não encontrar nada.
- **Comprovante sem CSS de impressão**: `sales/show.blade.php:62` chama `window.print()` e não existe `@media print`. A impressão sai com o fundo azul-marinho, a navegação, o cabeçalho e o próprio botão "Imprimir Comprovante". Desperdiça tinta e o resultado não parece um comprovante. Um `@media print` que esconda o chrome é ganho grande por diff pequeno.
- **Sem `<a href="#main">` de pular para o conteúdo**, e `main` sem `id`.
- **`welcome.blade.php` duplica o documento HTML inteiro** em vez de usar layout (head, fontes e vite repetidos).
- Typos que anulam classes: `transtition-colors` (`products/create.blade.php:105`, `products/edit.blade.php:79`) e `flex-col flex-col` (`welcome.blade.php:24`).
- Emoji como ícone semântico sem rótulo em `welcome.blade.php:49,57,64` — inclusive `👴` representando "fácil de usar". Vale reconsiderar: falar *para* o público sem estereotipá-lo.
- **"Sair do Sistema" só existe dentro do dropdown de perfil** (`navigation.blade.php:104`) e **não é repetido no menu mobile**. Sair é uma das poucas ações que este público procura ativamente; deve estar visível.

**O que foi feito** ✅

- **`<title>` por página** (`<x-slot name="titulo">`), nas 10 telas: "Estoque de Produtos · System Bar". Abas e histórico deixaram de ser indistinguíveis.
- **CSS de impressão** (`@media print`): o comprovante sai em preto sobre branco, sem navegação, sem cabeçalho e sem o próprio botão "Imprimir" (`.nao-imprimir`).
- **`min-h-screen` duplicado removido** das 10 páginas — o shell do layout já é `min-h-screen`, então toda tela tinha no mínimo o dobro da altura, com rolagem morta no fim.
- **"Sair do Sistema" no menu do celular**, que só existia dentro do dropdown de perfil.
- **Link "Pular para o conteúdo"** e `id="conteudo"` no `<main>`.
- Typos que anulavam a classe inteira: `transtition-colors`, `flex-col flex-col` e — o mais visível — `rounded-fullbg-red-100`, que deixava os selos "Baixo Estoque"/"Normal" **sem fundo nenhum**.
- `customers/edit` já dizia "Editar Cliente" (corrigido antes desta rodada).

**Continua pendente:** `welcome.blade.php` duplica o documento HTML inteiro em vez de usar layout, e usa emoji como ícone semântico (inclusive 👴 para "fácil de usar"). É reescrita de página, não ajuste — vai junto com §V2.
---

# 3. Redesenho visual

O pedido foi uma proposta de direção, não só correção. Segue **uma** direção recomendada, com justificativa pela audiência.

### V1 — Diagnóstico

| Problema | Evidência |
|---|---|
| Paleta em hex solto em ~40 lugares | `#002366`, `#0047AB`/`#0056D2`, `#008080`/`#00A0A0`, `#4682B4`/`#5A9BD5` espalhados por todas as views |
| Zero tokens de cor | `app.css:13-22` define só `--font-sans` e breakpoints |
| daisyUI 5 instalado e praticamente não usado | 3 `btn btn-soft` nas telas de auth, e **nenhum `data-theme` no `<html>`** — pode seguir o esquema do sistema operacional independente do resto da página |
| Duas stacks interativas concorrentes | Alpine (modal, perfil) **e** `@tailwindplus/elements` (navegação) |
| Dependência de CDN não fixada no caminho crítico | `layouts/app.blade.php:18` carrega `@tailwindplus/elements@1` do jsDelivr, **fora do `package.json`**. `layouts/navigation.blade.php` depende dele para `<el-dropdown>`, `<el-disclosure>` e `command`/`commandfor`. **Se o CDN falhar: nenhum menu no celular e nenhum jeito de sair do sistema.** E `layouts/guest.blade.php` não carrega o script — as telas de auth já vivem sem ele |
| Classes mortas no Tailwind v4 | `opacity-75` e `ring-opacity-5` em `components/modal.blade.php:63` e `dropdown.blade.php:31` não fazem nada na v4 — o fundo da modal pode renderizar opaco |
| Componentes copiados e colados | ver §V4 |

### V2 — Direção recomendada: inverter o peso ✅ FEITO

**Hoje:** o app é um bloco azul-marinho escuro (`#002366`) com cartões brancos flutuando dentro. A cor de marca é o **fundo da página**.

**Proposta:** fundo claro neutro, com a marca aplicada em **cabeçalho, ações primárias e status**. A identidade (marinho + teal) é preservada — deixa de ser plano de fundo e passa a ser sinalização.

**Por que, para este público especificamente:**
1. **Contraste mais confiável.** Grande área escura com cartões brancos gera brilho relativo alto — em tela de celular no sol, ou monitor com brilho alto, o cartão branco "estoura" e o texto ao redor desaparece. Superfície clara com texto escuro é o par mais tolerante à variação de brilho e à catarata/presbiopia.
2. **Menos ruído de saturação.** `#002366` em tela cheia compete com o conteúdo. Reduzir a área saturada faz a cor voltar a **significar** algo: se só o botão principal é azul forte, ele vira o ponto óbvio da tela.
3. **Hierarquia por posição, não por moldura.** Hoje todo conteúdo mora dentro de um cartão branco idêntico (`bg-white shadow-xl sm:rounded-lg p-6|p-8`, 9 cópias) — cartão não diferencia nada quando tudo é cartão.
4. **Impressão.** Fundo escuro é hostil ao comprovante impresso (§A11).

Preservado sem discussão: tiles grandes, um destino por tile, texto grande, nada de densidade.

**Alternativas** (não desenvolvidas, ficam registradas):
- **Manter o fundo marinho e só corrigir a execução** — tokens, contraste, tamanhos. Menor risco de estranhamento, teto mais baixo.
- **Adotar de fato um tema daisyUI** e migrar botões/cartões/alertas para os componentes dele. Reduz muito classe solta; exige aceitar as escolhas visuais do daisyUI.

### V3 — Sistema de tokens ✅ FEITO *(cores; escala tipográfica não)*

Substituir os ~40 hex soltos por tokens no `@theme` do Tailwind v4. Contrastes verificados contra WCAG AA:

```css
@theme {
    /* Marca */
    --color-brand-900: #002366;  /* 14,7:1 sobre branco — títulos, barra do topo */
    --color-brand-700: #0047AB;  /*  8,4:1 com texto branco — ação primária */
    --color-brand-600: #0056D2;  /* hover da primária */
    --color-accent-700: #008080; /*  4,8:1 com texto branco — ação de cadastro */

    /* Superfícies */
    --color-surface: #ffffff;
    --color-surface-muted: #f5f7fa;  /* fundo da página */
    --color-border: #d4dae3;

    /* Texto */
    --color-ink: #1a2233;         /* corpo */
    --color-ink-muted: #4a5568;   /* apoio — 7,4:1, substitui text-gray-500 */

    /* Estado */
    --color-success-bg: #e6f4ea;  --color-success-ink: #1e5631;
    --color-warning-bg: #fff4e0;  --color-warning-ink: #7a4b00;
    --color-danger-bg:  #fdecea;  --color-danger-ink:  #8b1a10;

    /* Tipografia — base 18px */
    --text-base: 1.125rem;
    --text-lg: 1.25rem;
    --text-xl: 1.5rem;
    --text-2xl: 1.875rem;

    --radius-card: 0.75rem;
}
```

Notas importantes:
- **`#4682B4` sai da paleta** ou vira só decorativo. Com texto branco dá **4,11:1** — reprova em AA para texto normal (§A1). Hoje ele é o fundo do tile "Configurações".
- **`text-gray-500` sai** dos cabeçalhos de tabela e textos de apoio, trocado por `--color-ink-muted`.
- A escala tipográfica já nasce maior — não precisa de override em `@layer utilities`, que é exatamente a origem do problema §A1. **Token é o lugar certo de mudar tamanho de fonte; `@layer utilities` sobrescrevendo uma utility do Tailwind é o lugar errado.**
- Se `data-theme` do daisyUI for adotado, derivá-lo destes tokens para `btn`/`alert`/`badge`/`card` ficarem coerentes com o resto.

### V4 — Componentes a criar ✅ FEITO

| Componente | Cópias hoje |
|---|---|
| `<x-alert type="success\|error">` | 3 cópias, 2 marcações diferentes (§A7) |
| `<x-form-errors />` | existe em **1 de 5** formulários (§B10) |
| `<x-card>` | `bg-white overflow-hidden shadow-xl sm:rounded-lg p-6\|p-8` — 9 cópias |
| `<x-page-header>` com slot de ação | 5 cópias |
| `<x-row-actions>` (Editar/Excluir com texto e modal) | 2 cópias (§A2, §A6) |
| `<x-status-badge>` | 3 cópias |
| `<x-empty-state>` | não existe (§A8) |
| Máscaras (moeda, telefone) em `resources/js/` | telefone: 2 cópias; moeda: 1 cópia + 1 **referência quebrada** (§B6) |
| Views de paginação publicadas | hoje é o default com "Previous/Next" em inglês e alvos pequenos |

**Consolidar nos botões:** `components/button-submit.blade.php` (`bg-[#008080] py-3 px-8 text-xl`) é **o único botão do repositório com tamanho adequado a este público** e é usado em apenas 2 lugares (`products/create`, `products/edit`). Deve ser o padrão do app, e os botões Breeze (`text-xs uppercase`) devem se alinhar a ele.

### V5 — Limpeza ✅ FEITO

- **Código morto:** `components/nav-link.blade.php`, `responsive-nav-link.blade.php`, `dropdown.blade.php`, `dropdown-link.blade.php` (a navegação migrou para `<el-*>`) e `application-logo.blade.php` (a nav usa `assets/imgs/logo.png`).
- **Classes inertes na v4:** `opacity-75` / `ring-opacity-5` em `modal` e `dropdown`.
- `stock_alert` e `description` são validados nos controllers sem existir no formulário (§F2, §F5).

### V6 — Sair do CDN `@tailwindplus/elements` ✅ FEITO

O CDN não fixado é dependência de rede em runtime numa parte crítica: **sem ele, não há menu no celular nem botão de sair**. Opções:
1. Instalar via npm com versão fixa e entrar no bundle do Vite.
2. **Reescrever navegação e dropdown em Alpine** — já está no bundle, já é usado pela `x-modal`, e elimina uma das duas stacks concorrentes.

**Recomendação: (2).** Uma stack a menos para manter, nada carregado de fora, e a `x-modal` prova que o padrão Alpine já funciona aqui. Como efeito colateral, resolve o "Sair do Sistema" ausente no mobile (§A11).

**O que foi feito** ✅

- O `min-h-screen bg-brand-900` do shell virou `bg-surface-muted`: a marca deixou de ser o fundo da página. Agora ela aparece na **barra do topo**, na **faixa do cabeçalho**, nas **ações primárias** e nos **status** — passou de plano de fundo a sinalização.
- A barra de navegação era translúcida (`bg-brand-700/50`) porque flutuava sobre o marinho da página; sobre fundo claro precisou virar sólida (`bg-brand-900`), e o item ativo passou a `bg-white/15`.
- O cartão saiu de `shadow-xl` para `border border-edge shadow-sm`: sobre fundo claro, sombra pesada vira sujeira — quem separa o cartão do fundo agora é a borda.
- **Não foi invertido:** as telas de entrada (`welcome`, `login`, `cadastro`). Ali o marinho cheio é a apresentação da marca, não uma tela de trabalho — o argumento de §V2 (superfície clara tolera melhor variação de brilho em tela de trabalho) não se aplica a uma capa.
- `welcome.blade.php` continua duplicando o documento HTML: é uma página de forma diferente das outras, e enfiá-la no `guest` layout (cartão centrado) seria pior. O que foi corrigido ali: os emoji ganharam `aria-hidden` e o 👴 que ilustrava "fácil de usar" virou 👍 — falar *para* o público, não *sobre* ele.

**Vale testar com um usuário real do perfil-alvo antes de considerar fechado.** Conferido só no navegador, em 390px, 720px e 1280px.
**O que foi feito** ✅

- Os ~40 hex soltos viraram tokens no `@theme`: `brand-900/700/600`, `accent-700/600`, `surface`, `surface-muted`, `edge`, `ink`, `ink-muted`, `--radius-card`. Nenhum `#rrggbb` sobrou nas classes das views (só num atributo `stroke` de SVG).
- **`#4682B4` e `#5A9BD5` saíram da paleta**, como o documento pedia: 4,11:1 com texto branco reprova em AA, e era o fundo do tile "Configurações". O tile agora usa `brand-700`.
- **A escala tipográfica em token NÃO entrou**, de propósito: a base já é 18px pelo `font-size: 112.5%` no `html` (§A1). Aplicar também `--text-base: 1.125rem` multiplicaria os dois (18px × 1,125 = 20,25px) e quebraria todo o espaçamento. Um mecanismo só para o tamanho de fonte.
**O que foi feito** ✅

| Componente | Estado |
|---|---|
| `<x-alert>` | criado, renderizado uma vez no layout (§A7) |
| `<x-form-errors />` | já existia, nos 5 formulários (§B10) |
| `<x-card>` | criado — substituiu as 9 cópias literais |
| `<x-page-header>` | criado — substituiu as 5 cópias |
| `<x-row-actions>` | criado — Editar/Excluir **com modal**, substituiu as 2 cópias |
| `<x-status-badge>` | criado — substituiu as 3 cópias |
| `<x-empty-state>` | criado (§A8) |
| `<x-search-form>` | criado (§F3) |
| Máscaras (`<x-currency-mask>`, `<x-phone-mask>`) | criadas (§B6, §A4) |
| Views de paginação | publicadas e reescritas (§A2) |

Com o `<x-row-actions>` entrou também o que faltava de §A6: os dois `confirm()` viraram `<x-modal>`, com o registro nomeado no título e os botões dizendo o que fazem — **"Excluir" / "Manter"**, não "OK/Cancelar".

**Botões do Breeze alinhados** ✅ — `primary-button` saiu do cinza-escuro genérico para `bg-brand-700` (a ação primária do app), e os três (`primary`, `secondary`, `danger`) ganharam `text-lg font-bold rounded-lg`, no mesmo peso do `button-submit`. Não viraram um componente só: são três papéis visuais diferentes. De quebra, `forgot-password` forçava `h-10` (40px) no botão, abaixo dos 44px — saiu.
**O que foi feito** ✅

- **Removidos:** `nav-link`, `responsive-nav-link`, `dropdown`, `dropdown-link`, `application-logo` — nenhum era referenciado em lugar nenhum.
- **Classe inerte na v4 com efeito visível:** `opacity-75` no fundo da modal virou `bg-gray-500/75`. E, ao conferir no navegador, apareceu a outra metade do problema: **o painel da modal renderizava sob o véu cinza**, porque `transform` sozinho não cria mais contexto de empilhamento na v4. Resolvido com `relative` no painel — isso valia para a modal de excluir conta desde a migração para a v4.
- `stock_alert` e `description` deixaram de ser regra de validação sem campo (§F2, §F5).
- Sobras do `overflow-x-auto` que a tabela-cartão substituiu.
**O que foi feito** ✅ — adotada a **opção 2**: navegação e dropdown reescritos em Alpine, e o `<script src="cdn.jsdelivr.net/@tailwindplus/elements@1">` saiu do layout. Uma stack a menos para manter, nada carregado de fora no caminho crítico, e o menu do celular não depende mais de rede de terceiro para existir. `[x-cloak]` entrou no CSS para o dropdown não piscar aberto antes de o Alpine assumir.
---

# 4. Funcionalidades novas

Poucas, cada uma justificada por "o que o dono do comércio tem que fazer hoje na falta dela".

### F1 — Dashboard com números ✅ FEITO

**Hoje:** `routes/web.php:13` é uma closure devolvendo uma grade estática. Zero informação. `cost_price` é coletado e **não é usado para nada**.

**Sem isso, o usuário faz:** abre a lista de Vendas e soma de cabeça, ou na calculadora, para saber quanto vendeu hoje.

**Proposta:** criar `DashboardController` e mostrar, nos próprios tiles, números grandes acima do rótulo:
- **Vendido hoje** e **vendido no mês** (depende de §B8 — com timezone errado, "hoje" está errado)
- **Vendas pendentes** (quantidade + valor a receber) — a mais útil das quatro: é dinheiro na rua
- **Produtos com estoque baixo** (depende de §F2)

Os 4 tiles atuais continuam. É a mesma tela, agora informando.

### F2 — Alerta de estoque baixo de verdade ✅ FEITO

A coluna `stock_alert` existe com `default(5)`, está em `$fillable`, e é **validada nos dois métodos** do `ProductController` (`:65` e `:105`). Mas:
- **Não existe campo dela em nenhum formulário** → vale 5 para sempre, para todo produto.
- `products/index.blade.php:53,57` **ignora a coluna** e usa `5` fixo no código.

É uma funcionalidade construída até a metade. Faltam duas coisas: o campo no formulário e ler `$product->stock_alert` no lugar do `5`.

**Por que importa:** o limiar certo depende do produto. Cerveja e detergente não têm o mesmo ponto de reposição, e quem sabe disso é o dono.

Adicionar também um contador de produtos em alerta no dashboard (§F1) — o alerta hoje só existe se a pessoa for até a tela de Estoque e olhar linha por linha.

### F3 — Busca e filtro ✅ FEITO

**Não existe busca ou filtro em nenhuma tela**, com `paginate(10)`. Quem tem 60 produtos precisa navegar 6 páginas para achar um.

- Produtos e Clientes: busca por nome, campo grande e visível no topo.
- Vendas: filtro por status (Pendente/Pago) e por período.

**Por que importa:** paginação sem busca transfere o trabalho de lembrar para o usuário. E o filtro "Pendentes" é o relatório que este negócio mais usa: quem me deve.

### F4 — Cancelar venda com devolução ao estoque ✅ FEITO

**Hoje uma venda registrada por engano não tem saída nenhuma:**
- Não há `edit` nem `destroy` em `SaleController` (§B9).
- `sales.status` é só `enum('paid','pending')` — não existe `cancelled`.
- `updateStatus` valida `'status' => 'required|in:pending,paid'` **sem guarda de máquina de estado**: o `<select>` fica `disabled` na tela quando pago, mas um PATCH direto volta de `paid` para `pending`.

**Sem isso, o usuário faz:** conviver com a venda errada para sempre, e corrigir o estoque na mão editando o produto — o que desalinha o histórico do faturamento.

**Proposta:** status `cancelled`; ação "Cancelar Venda" com confirmação nomeando o valor e o cliente; devolução do estoque dentro de transação; venda cancelada permanece na lista, marcada, fora dos totais. **Cancelar, não excluir** — o histórico é o registro do negócio.

### F5 — Campo de descrição do produto ✅ DECIDIDO: removido

Mesma situação do `stock_alert`: `description` está em `$fillable`, é validado (`ProductController:64`), **não tem campo em formulário nenhum e não é exibido em lugar nenhum**. Decidir: usar (campo + exibição na lista e no comprovante) ou remover das regras de validação. Regra validando campo inexistente é ruído que confunde na próxima leitura do código.

### Fora de escopo agora (backlog)

Registrado para não se perder, sem prioridade atribuída: ~~**relatório de faturamento por período**~~ **✅ feito, ver abaixo**; ~~**margem de lucro**~~ **✅ feito, ver abaixo**; formas de pagamento; fechamento de caixa; data retroativa de venda (hoje usa `created_at`, então não se lança a venda de ontem); exportação para planilha; recibo por WhatsApp.

**Margem de lucro** ✅
- **O custo fica gravado na venda** (`sale_items.unit_cost`, migration `2026_09_28_100000`), como o preço já ficava. Sem isso, atualizar o custo de um produto reescreveria o lucro de todos os meses passados. Vendas anteriores receberam o custo atual do produto, que era a melhor informação disponível.
- **Painel:** "Lucro: R$ X" dentro do cartão "Vendido no mês". Não é um quinto cartão: `text-4xl` em cinco colunas não cabe a 1280px. Compras canceladas ficam fora, como em todo total.
- **Lista de produtos:** coluna "Lucro por unidade", com o valor em reais e a porcentagem sobre o preço de venda. Fica vermelha se o produto dá prejuízo.
- **Produto sem preço de custo não conta como custo zero**, o que mostraria a venda inteira como lucro. Na lista aparece "Sem preço de custo". No painel o item fica fora da conta, e a tela diz quantos ficaram de fora.
- `tests/Feature/MargemTest.php` — lucro com custo antigo gravado, item sem custo, venda cancelada e coluna da lista.

**Relatório de faturamento** ✅ — `/faturamento` (`RelatorioController`)
- Abre no **mês corrente até hoje**, que é a pergunta de sempre. Campos De/Até com o calendário nativo, período repetido por extenso ("De 01/09/2026 até 28/09/2026"), e datas invertidas são destrocadas em vez de devolver um período vazio.
- Quatro números: **Vendido** (e quantas compras), **Lucro** (mesma regra do painel: custo gravado na venda, item sem custo fora da conta e sinalizado), **Ainda a receber** das compras do período e **Média por compra**. Tabela **dia a dia** (compras, vendido, lucro), que vira cartões no celular (§A9), e botão de imprimir.
- Canceladas ficam fora de tudo. Acesso: item "Faturamento" no menu (desktop e celular) e link "Ver faturamento" no cartão "Vendido no mês" do painel.
- A agregação é feita em PHP, não em SQL (comentário `ponytail:` no controller). Cabe no volume de um pequeno comércio; trocar por `GROUP BY` se o relatório de um ano ficar lento.
- O `data()` que valida as datas da URL subiu para o `Controller` base: vendas e relatório usam o mesmo.
- `tests/Feature/RelatorioTest.php` — somas com pagas, pendentes e canceladas, limites do período, mês padrão, datas invertidas e isolamento entre comércios.

**Teste de ponta a ponta (28/09/2026)**: checklist abaixo percorrido no navegador com um usuário novo, mais pagamento, cancelamento, painel e relatório, em 1280px e 390px. Achados e corrigidos:
- **A edição de produto mostrava o preço como `1234.56`** (formato americano), enquanto o cadastro mostra `1.234,56`. Agora formata em pt-BR; o `ProductRequest` já entendia esse formato ao salvar. Teste em `MargemTest`.
- **Erro de JavaScript em `sales/create`** quando falta cliente ou produto: o formulário não é renderizado (§A8), mas o script procurava o botão "Adicionar outro produto". Resolvido com `?.`.
- **Tela de erro 500 depois de rodar os testes**: `php artisan test` rodado como root no container compilava views em `storage/` que o PHP-FPM (`www-data`) não conseguia sobrescrever. Posse corrigida, e o comando abaixo passou a usar `-u www-data`.

**O que foi feito** ✅

- `app/Http/Controllers/DashboardController.php` (novo, invocável) no lugar da closure que devolvia view estática.
- Quatro números acima dos tiles: **Vendido hoje**, **Vendido no mês**, **A receber** (valor + quantas compras) e **Estoque baixo** (produtos no limiar). Os dois últimos são links para a lista correspondente.
- **Venda cancelada fica fora de todo total** — ela existe no histórico justamente para dizer que não valeu (§F4).
- Depende de `B8` (timezone) para "hoje" significar hoje, e de `F2` para o limiar de estoque ser o do dono. Os dois já estavam prontos.
- **Bônus encontrado ao conferir no navegador:** o tile "Clientes" usava o mesmo ícone de caixa do tile "Estoque" — dois desenhos idênticos lado a lado, para um público que navega por ícone.
**O que foi feito** ✅ — campo **"Avisar quando o estoque chegar em"** nos dois formulários de produto, com texto de apoio explicando que cerveja e detergente não se repõem no mesmo ponto; e a lista passou a comparar com `$product->stock_alert` no lugar do `5` fixo, tanto no selo quanto na cor do número. O contador de produtos em alerta entrou no dashboard (§F1).
**O que foi feito** ✅

- `<x-search-form>` em Produtos e Clientes: busca por nome, campo grande no topo, botão **Buscar** e, quando há busca ativa, **Limpar**. A paginação mantém a busca (`withQueryString`).
- Vendas ganharam filtro por situação — **Todas · Pendentes · Pagas · Canceladas** — em botões de 44px, com o ativo marcado por cor e `aria-current`. "Pendentes" é o relatório que este negócio mais usa: quem me deve.
- **Busca sem resultado não mente:** o estado vazio diz *"Nenhum produto com «coca» no nome"* e oferece "Ver todos", em vez da orientação de primeiro cadastro, que seria falsa para quem só filtrou.
- **Filtro por período** ✅ — campos **De** e **Até** (`<input type="date">` nativo, que no celular abre o calendário do sistema), combináveis com o filtro de situação; um preserva o outro. Padrão decidido: **nenhum** — vazio é "todas as datas", como sempre foi. Os dois limites incluem o dia inteiro, e data impossível (`2026-02-31`) é ignorada em vez de virar erro. Teste em `FuncionalidadesTest`.
**O que foi feito** ✅

- Status `cancelled` no enum (migration) — **cancelar, não excluir**.
- `SaleController::cancel()` devolve o estoque item a item e muda o status **dentro de uma transação**: estoque devolvido com a venda ainda ativa seria pior que o problema original. Cancelar de novo não devolve duas vezes.
- Botão **"Cancelar esta Compra"** no comprovante, com modal que nomeia o cliente e o valor e diz o que vai acontecer com o estoque.
- A **máquina de estado** fechou: pendente → paga, pendente/paga → cancelada, e cancelada não volta a nada. Uma compra cancelada não aceita mais `Marcar como Pago`, nem pela tela nem por PATCH direto.
- Fica na lista, marcada como **Cancelada**, e fora dos totais do painel.
**Decisão tomada: removido** ✅ — a regra `'description' => 'nullable|string'` saiu dos dois métodos do `ProductController` e o campo saiu do `$fillable`. Formulário a mais é custo real para este público, e ninguém pediu descrição de produto; regra validando campo que não existe em tela nenhuma é ruído que confunde na próxima leitura. **A coluna continua no banco** — se um dia for usada, é só devolver a regra; migration de drop não paga o próprio risco.
---

# 5. Base técnica que falta

Curto e direto, porque sustenta tudo acima.

### T1 — Zero testes do domínio ✅ FEITO

`tests/` é apenas o scaffolding do Breeze: 6 testes de autenticação, `ProfileTest`, 2 `ExampleTest`. **Nenhum teste de produto, cliente ou venda.** Não há teste da baixa de estoque, do cálculo do total, do parser de moeda pt-BR, do blind index — nem do **isolamento multi-tenant**, que foi o objetivo inteiro do commit `ad4f1a8`.

**O bloqueio prático:** só existe `UserFactory`. Sem `ProductFactory`, `CustomerFactory`, `SaleFactory`, escrever esses testes é penoso. Criar as factories é o primeiro passo, e Pest 4 já está instalado.

Prioridade de cobertura: (1) isolamento entre usuários, (2) baixa de estoque e total da venda, (3) parser de moeda `normalizeMoneyForValidation`.

### T2 — `ScopedToUser` falha aberto ✅ FEITO *(documentado e cercado por teste)*

`app/Models/Concerns/ScopedToUser.php:16` aplica o escopo só `if (Auth::check())`. Em contexto de console, queue ou scheduler **não há autenticação, logo não há escopo** — `Product::all()` retorna as linhas de todos os usuários. Não há comandos nem jobs hoje, então é latente; é uma armadilha de vazamento entre comércios no primeiro que for escrito. Documentar no próprio trait, no mínimo.

### T3 — `user_id` é `nullable` nas três tabelas de tenant ✅ FEITO

`products`, `customers` e `sales` declaram `->string('user_id', 36)->nullable()`. Uma linha com `user_id = NULL` fica **invisível para todos os usuários** (por causa do escopo global) e órfã para sempre. Deveria ser obrigatório.

### T4 — Regras de validação duplicadas ✅ FEITO

As regras de produto estão repetidas literalmente entre `store()` (`ProductController:59-66`) e `update()` (`:99-106`); as de cliente entre `CustomersController:24-28` e `:49-53`. Duas cópias divergem no primeiro ajuste. Extrair para FormRequest (só `ProfileUpdateRequest` e `Auth/LoginRequest` existem hoje).

Menor, no mesmo arquivo: `unset($validated['user_id'])` em `ProductController:68` e `CustomersController:30` é código morto — `user_id` não está nas regras, então nunca chega em `$validated`.

### T5 — Sem casts decimais ✅ FEITO

`Product` e `SaleItem` não declaram `casts()`. `sale_price`, `cost_price` e `unit_price` voltam do MySQL como **string**, e `SaleController:54` faz `$product->sale_price * $item['quantity']` contando com a conversão implícita do PHP. Funciona, mas `'sale_price' => 'decimal:2'` é o correto e evita surpresa de arredondamento.

### T6 — Verificação de e-mail é código morto ✅ DECIDIDO: removida

`app/Models/User.php:5` tem `MustVerifyEmail` **comentado** e a classe não o implementa. Logo o middleware `verified` em `routes/web.php:15` **não faz nada**. Mas `ProfileController:32` continua zerando `email_verified_at` ao trocar o e-mail, e `update-profile-information-form.blade.php:34-43` continua renderizando o aviso "e-mail não verificado" — **um aviso que o usuário nunca consegue resolver**, porque o fluxo está desativado.

Decidir: ativar a verificação, ou remover o aviso, o `verified` e as 4 rotas/controllers de verificação. Para este público, um aviso insolúvel na tela de conta é motivo de ligação para o suporte.

### T7 — `password_reset_tokens.email` em texto puro ✅ FEITO

`users.email` é criptografado com blind index HMAC — um design cuidadoso. Mas `password_reset_tokens` tem `email` como **string em texto puro** e chave primária (`0001_01_01_000000_create_users_table.php`). Cada pedido de "esqueci a senha" grava o e-mail em claro no banco enquanto o token existir. Vale registrar dado o esforço investido na criptografia.

### T8 — Índice composto ausente ✅ FEITO

Todos os índices usam `->latest()` (ordena por `created_at`) filtrando por `user_id`, e não há índice `(user_id, created_at)`. O banco filtra pelo índice de `user_id` e ordena em memória. Irrelevante no volume atual; barato de resolver junto com outra migration.

### T9 — Dívidas de infraestrutura ✅ FEITO *(um passo manual pendente, ver abaixo)*

- `docker-compose.yml` tem **senhas de MySQL em texto no repositório** (`MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`). Em ambiente local é tolerável; vira problema no dia em que o mesmo arquivo servir de base para outro ambiente. Mover para `.env` com default é barato.

  > **Correção desta seção:** quando esta doc foi escrita, o `docker-compose.yml` era uma cópia obsoleta da infra compartilhada (montava `./workspace`, `./mysql/*`, contexto `./php2`) e a doc concluía que o projeto rodava em `/home/guilherme`. Isso **não vale mais**: o projeto hoje tem **stack própria e self-contida** (`systembar_nginx`, `systembar_php`, `systembar_mysql`, `systembar_node`), com portas deslocadas de propósito para conviver com a infra compartilhada e com a do auditoria-ideal. Ver a seção "Como rodar e verificar".
- `database/seeders/DatabaseSeeder.php:18-22` cria `admin@admin.com` / `102030` **sem guarda de ambiente** (`app()->environment()`). Precisa de guarda antes de qualquer deploy.
- `bootstrap/app.php`: `withMiddleware` e `withExceptions` vazios. Sem página de erro customizada — um 500 cru é assustador para este público. Uma página de erro em português dizendo o que fazer é barato e vale muito.

**O que foi feito** ✅ (parte do bloqueio)

- `ProductFactory`, `CustomerFactory` e `SaleFactory` criadas. `SaleFactory` documenta o uso de `recycle($user)` — sem ele, cada relação cria o próprio usuário e o teste nasce com dados de dois comércios diferentes.
- Cobertura atual: comprovante e consolidação de itens (`SaleReceiptTest`), estoque e transação (`SaleStockTest`), erros de formulário (`FormErrorsTest`), locale (`LocalizationTest`), telas principais (`SmokeTest`). **41 testes passando.**
- **Continua faltando** o item (1) da lista de prioridade acima: teste de **isolamento entre usuários**, que era o objetivo do commit `ad4f1a8`. E o parser `normalizeMoneyForValidation`.
**O que foi feito** ✅ — `database/migrations/2026_09_14_100001_make_user_id_required_on_tenant_tables.php` põe `NOT NULL` em `products.user_id`, `customers.user_id` e `sales.user_id`. A migration **falha antes de alterar nada** se houver linha órfã, dizendo quantas e em qual tabela: linha sem dono é dado de alguém, não se apaga sozinho. Aplicada no banco de dev.
**O que foi feito** ✅

- O trait ganhou um bloco de documentação dizendo **que ele falha aberto e por quê** — console, fila e scheduler não têm autenticação, logo não têm escopo —, com a regra para quem escrever o primeiro comando ou job: partir sempre do dono (`$user->products()`), nunca consultar o modelo direto.
- **A proteção real de hoje é teste**, e ela passou a existir: `tests/Feature/IsolamentoTest.php` cobre os três caminhos — a lista não mostra o registro do vizinho, abrir/editar/excluir/cancelar registro alheio dá 404 (o escopo global esconde a linha, então o route model binding nem resolve), e a venda recusa `customer_id`/`product_id` de outro comércio.
- Não troquei por escopo explícito (`Product::forUser($user)`): hoje não há comando nem job nenhum, e a troca custaria mexer em toda consulta do app para resolver um problema que ainda não existe. A regra está escrita onde vai ser lida.

Isso fecha o item (1) da lista de prioridade de §T1 — **isolamento entre usuários**, que era o objetivo do commit `ad4f1a8` e estava sem teste.
**O que foi feito** ✅

- `app/Http/Requests/ProductRequest.php` e `CustomerRequest.php` (novos): uma cópia das regras, usada por `store()` e `update()`.
- O `normalizeMoneyForValidation` do controller virou `prepareForValidation()` no `ProductRequest` — o valor em pt-BR (1.234,56) é normalizado antes de validar, e o controller voltou a ter só o que é dele.
- Os dois `unset($validated['user_id'])` sumiram junto: eram código morto, `user_id` nunca esteve nas regras.
- Os quatro métodos ficaram com 2 a 3 linhas cada.
**O que foi feito** ✅ — `casts()` em `Product` (`sale_price`, `cost_price` decimal:2, quantidades inteiras), `SaleItem` (`unit_price`, `quantity`) e `Sale` (`total_amount`). O cálculo do total deixou de depender da conversão implícita de string para número que o PHP fazia por baixo.
**Decisão tomada: removida** ✅

Ativar significaria pôr uma barreira de e-mail entre o dono do comércio e o sistema — com SMTP para configurar e mais uma tela para quem tem pouca familiaridade com tecnologia. Código que não faz nada é pior que código que não existe.

Saíram: as 3 rotas de verificação e seus 3 controllers, a view `auth/verify-email`, o `EmailVerificationTest`, o middleware `verified` da rota do painel (que não fazia nada), o `email_verified_at = null` do `ProfileController`, o `use` comentado de `MustVerifyEmail` e o estado `unverified()` da `UserFactory`. A coluna `email_verified_at` fica no banco.

**Correção do diagnóstico:** o aviso de "e-mail não verificado" **não estava aparecendo** na tela de conta. O `@if` que o envolvia testava `$user instanceof MustVerifyEmail`, e a classe não implementa o contrato — a condição era sempre falsa. O bloco existia, morto, mas nenhum usuário chegou a ver um aviso insolúvel. Ele saiu junto.
**O que foi feito** ✅

- `User::getEmailForPasswordReset()` passou a devolver o **blind index** em vez do endereço. É o método que o broker usa para gravar **e** para consultar o token, então os dois lados batem sozinhos e a tabela nunca mais recebe e-mail em claro. O link continua indo para o endereço real (`routeNotificationForMail` usa o atributo `email`).
- Migration apaga as linhas antigas: elas ficariam com a chave no formato velho — inúteis para qualquer token novo e ainda expondo endereços. Token de reset é descartável e expira em 60 minutos.
- `tests/Feature/Auth/PasswordResetTokenTest.php` confere que o que foi gravado **não** é o e-mail e **é** o hash. O `PasswordResetTest` do Breeze, que faz o fluxo inteiro até trocar a senha, continua passando — a simetria está coberta ponta a ponta.
**O que foi feito** ✅ — índice `(user_id, created_at)` em `products`, `customers` e `sales`. Era o que faltava para o `->latest()` de toda listagem não ordenar em memória. Irrelevante no volume de hoje; barato agora, caro quando doer.
**O que foi feito** ✅

- **Senhas fora do repositório:** `MYSQL_ROOT_PASSWORD` e `MYSQL_PASSWORD` saíram do `docker-compose.yml` e viraram `${SYSTEMBAR_DB_ROOT_PASSWORD:?...}` / `${SYSTEMBAR_DB_PASSWORD:?...}`. Sem elas o compose **recusa subir**, com a mensagem dizendo o que falta — melhor que subir com credencial diferente da gravada no volume e só falhar no healthcheck, que é o que um valor padrão causaria.

  > **Passo manual** ✅ — as duas variáveis já estão no `.env` (que não vai para o repositório); o `docker compose up -d` sobe normalmente.

- **Seeder com guarda de ambiente:** `DatabaseSeeder` só roda em `local`/`testing`. Fora disso avisa e não cria nada — ele cria `admin@admin.com` com senha conhecida.
- **Página de erro em português:** `resources/views/errors/` com 404, 403, 419, 429, 500 e 503 sobre um layout comum. Cada uma diz **o que aconteceu e o que fazer**, em uma frase, sem jargão e sem código de status gritando, com um botão grande de voltar ao início. O 500 começa por "Não foi culpa sua".
- `withMiddleware`/`withExceptions` de `bootstrap/app.php` continuam vazios: as views de erro bastam, e configuração vazia não é dívida.
---

# 6. Ordem de execução sugerida

> **Validado contra o código em 14/09/2026**, e atualizado no mesmo dia depois da rodada do P0. Cada item abaixo foi conferido no repositório, não só na leitura desta doc. Correções de rastro: `customers/edit.blade.php:4` já diz "Editar Cliente" e `products/edit` já tem `min="0"` no estoque — os dois sub-itens (`A11`, `A4`) estavam marcados como pendentes sem estar.

## Situação

| Bloco | Itens | Concluídos | Pendentes |
|---|---|---|---|
| **B** — Bugs | 10 | **10 — bloco fechado** | **0** |
| **A** — Acessibilidade/UX | 11 | **11 — bloco fechado** | **0** |
| **V** — Visual | 5 acionáveis (`V1` é diagnóstico) | **5 — bloco fechado** | **0** |
| **F** — Funcionalidades | 5 | **5 — bloco fechado** | **0** |
| **T** — Base técnica | 9 | **9 — bloco fechado** | **0** |
| **Total** | **40** | **40** | **0** |

**Os 40 itens deste documento estão fechados.** 65 testes passando. O que sobrou está listado em "O que ficou de fora", logo abaixo — decisões tomadas de propósito, não esquecimentos.

## Ordem de execução

Ordenada por *dano ao usuário ÷ esforço*, não por bloco. Faça de cima para baixo.

### P0 — Integridade de dados
Nesta ordem; `T1` primeiro porque é a rede de proteção dos outros oito.

| # | Item | Por quê agora |
|---|---|---|
| 1 | ~~`T1` factories (`Product`, `Customer`, `Sale`)~~ **✅** | Rede de proteção do resto do P0 |
| 2 | ~~`B8` timezone~~ **✅** | 1 linha; data e hora de venda estavam 3h à frente |
| 3 | ~~`B6` máscara de moeda~~ **✅** | `ReferenceError` a cada tecla ao editar preço |
| 4 | ~~`B5` `DB::transaction` no `store()`~~ **✅** | Falha no meio deixava venda fantasma de R$ 0,00 |
| 5 | ~~`B2` validação de estoque~~ **✅** | Sobre a quantidade consolidada, com o produto nomeado na mensagem |
| 6 | ~~`B9` `->only()` nos 3 resources~~ **✅** | Deixou de ser 500; virou 405 (a URI segue viva para `PUT`/`DELETE`) |
| 7 | ~~`B4` `SoftDeletes` em `Product`~~ **✅** | O cascade apagava itens de vendas passadas |
| 8 | ~~`B3` `SoftDeletes` em `Customer` + `?->` na view~~ **✅** | Derrubava a lista de Vendas inteira |
| 9 | ~~`T3` `user_id` obrigatório~~ **✅** | Fecha a porta da linha órfã invisível |

**P0 concluído.** 44 testes passando. Exclusão agora é arquivamento: o registro sai da lista e o histórico fica inteiro. **Próximo é o P1, começando por `A7`** (clique duplo em "Finalizar Venda" ainda registra duas vendas — a transação do `B5` garante que cada uma seja íntegra, não que só exista uma).

### P1 — UX que faz o usuário errar

| # | Item | Por quê |
|---|---|---|
| 10 | ~~`A7` trava de duplo envio · `<x-alert>` · `session('error')`~~ **✅** | Clique duplo registrava duas vendas |
| 11 | ~~`A6` status da venda vira botão~~ **✅** | `<select onchange>` marcava como Paga sem intenção e sem volta |
| 12 | ~~`A8` `@forelse` + estados vazios~~ **✅** | A primeira tela do usuário novo era uma tabela vazia |
| 13 | ~~`A4` `type="tel"`/`inputmode` + rótulos~~ **✅** | Teclado alfabético num campo só de dígitos |
| 14 | ~~`A9` cartões empilhados abaixo de 768px~~ **✅** | A coluna "Ações" não existia para quem não arrasta |
| 15 | ~~`A5` fim do que só aparece no hover~~ **✅** | Numeração dos tiles, sublinhado dos links, `prefers-reduced-motion` |
| 16 | ~~`A2` paginação com alvo de 44px~~ **✅** | "Página 2 de 6" no lugar da lista de números |
| 17 | ~~`A10` ponto de virada único (768px)~~ **✅** | Acabou a faixa com menu de desktop e corpo de celular |
| 18 | ~~`A11` impressão, `<title>`, `min-h-screen`, sair no celular~~ **✅** | Mais o typo que deixava os selos de estoque sem fundo |

**P1 concluído.** 50 testes passando, e conferido no navegador em 390px, 720px e 1280px com dados reais. Dois itens ficaram de fora **de propósito**, porque pertencem ao P2: o `confirm()` de exclusão virar `<x-modal>` (vai com `<x-row-actions>`, §V4) e o `welcome.blade.php` (vai com §V2).

### P2 — Visual e funcionalidades
~~`V4` componentes~~ **✅** → ~~`V5` limpeza~~ **✅** → ~~`V6` sair do CDN~~ **✅** → ~~`V3` tokens~~ **✅** → ~~`V2` inversão de peso~~ **✅** → ~~`F2`~~ **✅** → ~~`F4`~~ **✅** → ~~`F1`~~ **✅** → ~~`F3`~~ **✅** → ~~`F5`~~ **✅**

**P2 concluído.** 56 testes passando. `F4` foi feito antes de `F1` (a ordem original era o inverso) porque os totais do painel precisam saber o que é uma venda cancelada — fazer na ordem da lista significaria escrever a consulta duas vezes.

`V2` é a única etapa que muda a aparência de forma perceptível, e a única conferida só por mim, no navegador. **Vale testar com um usuário real do perfil-alvo antes de considerar fechada.**

### P3 — Base técnica
~~`T4` FormRequests~~ **✅** · ~~`T5` casts decimais~~ **✅** · ~~`T8` índice composto~~ **✅** · ~~`T2` `ScopedToUser` falha aberto~~ **✅** · ~~`T6` verificação de e-mail~~ **✅ removida** · ~~`T7` `password_reset_tokens.email`~~ **✅** · ~~`T9` infra~~ **✅**

**P3 concluído.** Nada aqui é visível para o usuário — é o que sustenta o resto. Duas decisões em vez de implementações: `T6` (a verificação de e-mail foi **removida**, não ativada) e `F5` (`description` **removido**, não implementado).

## O que ficou de fora, de propósito

| O quê | Por quê |
|---|---|
| `welcome.blade.php` com documento HTML próprio | É uma página de forma diferente das outras; enfiá-la no layout `guest` (cartão centrado) seria pior |
| Inversão visual (`V2`) nas telas de entrada | Ali o marinho cheio é apresentação de marca, não tela de trabalho |
| Teste de `V2` com um usuário real do perfil-alvo | Não é tarefa de código; só uma pessoa de 50/60+ usando responde |
| Escopo explícito no lugar do `ScopedToUser` | Não existe comando nem job hoje; a regra está documentada no trait e coberta por teste |
| Backlog de §4 (formas de pagamento, fechamento de caixa, data retroativa, exportação, recibo por WhatsApp) | Nunca entrou no escopo deste documento |


---

## Como rodar e verificar

O projeto tem **stack própria**, subida de dentro do diretório do projeto. Não usa a infra compartilhada de `/home/guilherme`.

```bash
# Subir (de dentro de workspace/systemBar)
docker compose up -d

# Testes
# como www-data: rodando como root, as views compiladas em storage/ ficam
# sem permissão para o PHP-FPM e o app responde 500
docker exec -it -u www-data systembar_php sh -c "cd /var/www && php artisan test --compact"

# Build do front (produção)
docker exec -it systembar_node sh -c "cd /var/www && npm run build"
```

| Serviço | Container | Porta no host |
|---|---|---|
| HTTP | `systembar_nginx` | **8090** |
| PHP-FPM 8.3 | `systembar_php` | — (working dir `/var/www`) |
| MySQL 8.4 | `systembar_mysql` | 33062 |
| Vite | `systembar_node` | 5174 |

App em **`http://systembar.localhost:8090`**. O Vite **já sobe junto** com o `docker compose up -d` — não é preciso rodar `npm run dev` à mão.

> ### ⚠️ Não suba um segundo `npm run dev`
>
> O `systembar_node` já mantém o Vite no ar. Subir outro Vite à mão — especialmente dentro do `guilherme-php2-1` da infra compartilhada, que também monta este diretório — **sobrescreve o `public/hot`**, o arquivo por onde o Laravel decide de onde servir CSS e JS. O intruso grava um endereço que o navegador não alcança (`http://[::1]:5173`, porta não publicada) e **o front inteiro quebra**: página sem estilo, Alpine morto, menu do celular inexistente. Já aconteceu.
>
> Sintoma e diagnóstico — olhar para onde apontam as tags de asset:
>
> ```bash
> cat public/hot     # tem que ser http://localhost:5174
> curl -s -H "Host: systembar.localhost" http://127.0.0.1:8090/login | grep -o 'http://[^"]*app.css'
> ```
>
> Conserto: matar o Vite intruso e `docker restart systembar_node`, que reescreve o `hot`.

> `vite.config.js` fixa `server.hmr.host = 'localhost'`. Sem isso, o `--host 0.0.0.0` do compose faz o `laravel-vite-plugin` gravar `http://0.0.0.0:5174` no `public/hot` — e `0.0.0.0` **não conecta do Windows** (`localhost` conecta). Não mexer nessa linha sem testar o carregamento dos assets no navegador.

> Depois de mexer em `config/` ou `.env`, rodar `php artisan config:clear` — config em cache ignora a mudança de locale.
>
> A infra compartilhada de `/home/guilherme` também serve este app na porta 80 (`guilherme-nginx-1` + `guilherme-php2-1`), apontando para **outro banco**. Rodar comando ali é o que causou a confusão acima; prefira sempre os containers `systembar_*`.

**Checklist manual do fluxo completo** — vale rodar ao fim de cada fase, de celular:

1. Cadastrar produto com preço em formato brasileiro (`1.234,56`) → salva certo
2. **Editar** o mesmo produto → máscara funciona, valores certos (`B6`)
3. Cadastrar cliente com telefone → teclado numérico no celular (`A4`)
4. Registrar venda com 2 itens → estoque baixa exatamente
5. Tentar vender mais que o estoque → erro claro nomeando o produto, nada é gravado (`B2`)
6. Abrir o comprovante → cada linha com seu valor, soma batendo com o total (`B1`)
7. Imprimir o comprovante → sai como comprovante, sem menu nem fundo azul (`A11`)
8. Enviar formulário vazio → erro em português, dados preservados (`B7`, `B10`)
9. Excluir um cliente que tem vendas → lista de Vendas continua abrindo (`B3`)
10. Percorrer tudo com o teclado (Tab) → sempre visível onde está o foco (`A5`)

**Alinhamento no celular (29/09/2026)** ✅ — as 12 telas usavam `max-w-* mx-auto sm:px-6 lg:px-8` **sem `px-4`**: abaixo de 640px os cartões encostavam na borda da tela, enquanto o título do cabeçalho tinha respiro. O painel compensava com `px-10` fixo (40px, desalinhado do resto). Agora toda tela usa o mesmo contêiner do cabeçalho do layout (`px-4 sm:px-6 lg:px-8`), e medido no navegador a 390px título e cartões começam em 18px em todas. `profile/edit`, que tinha ficado com os cartões do Breeze (sem canto no celular, `shadow` em vez de borda), cabeçalho próprio e sem `<title>`, passou a usar `<x-card>` e `<x-page-header>` — e o título virou "Minha Conta", o mesmo nome do item do menu.
