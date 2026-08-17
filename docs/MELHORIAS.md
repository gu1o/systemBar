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
| 1 | **Remover o override de `.text-2xl` em `resources/css/app.css:36-41`** — ele reduz para 16px justamente os textos grandes feitos para este público (§A1) | 1 linha | Altíssimo |
| 2 | **Configurar locale pt-BR + criar `lang/pt_BR/`** — hoje toda validação sai em inglês, e no e-mail duplicado aparece a string crua `validation.unique` na tela (§B7) | ~1h | Altíssimo |
| 3 | **Corrigir o subtotal dos itens de venda** — todo comprovante impresso mostra `R$ 0,00` em cada linha (§B1) | 1 linha | Alto |
| 4 | **Mostrar os erros de validação nos 4 formulários que não mostram** — hoje o usuário salva, falha, e a tela volta igual sem explicar nada (§B10) | ~1h | Alto |
| 5 | **Trocar os ícones-only das tabelas por botões com texto** "Editar" / "Excluir" com 44px (§A2, §A3) | ~2h | Alto |

Os itens 1, 2 e 4 sozinhos mudam a percepção do app inteiro com diff pequeno e risco baixo.

---

# 1. Correções — o que hoje trava o uso real

Prioridade máxima. São defeitos, não preferências.

### B1 — Todo item de venda mostra `R$ 0,00` no comprovante

**Onde:** `app/Http/Controllers/SaleController.php:60` · `resources/views/sales/show.blade.php:48` · `database/migrations/2026_01_19_185750_create_sale_items_table.php`

O controller grava `'subtotal' => $subtotal` ao criar o item da venda. Mas `subtotal` **não é coluna** de `sale_items` **e não está em** `SaleItem::$fillable`. O Eloquent descarta silenciosamente. A view então renderiza `number_format($item->subtotal, ...)` sobre `null` → `R$ 0,00` em toda linha, mais um deprecation de PHP 8.1+. O "Total Geral" está certo porque lê `$sale->total_amount`, o que torna o comprovante **internamente contraditório**: linhas zeradas somando um total real.

**Por que importa:** é o documento que o dono do comércio entrega ao cliente. Um comprovante que se contradiz destrói a confiança no sistema todo — e a reação natural deste público não é reportar bug, é parar de usar.

**Como resolver:** duas saídas válidas, escolher uma.
- **Simples:** remover `'subtotal'` do `create()` e renderizar `$item->unit_price * $item->quantity` na view. Nenhuma migration.
- **Explícita:** adicionar a coluna `subtotal` na migration + `$fillable`. Vale se houver intenção futura de descontos por item (aí o subtotal deixa de ser derivável).

Recomendação: a simples. O valor é derivável e coluna derivada é coluna que dessincroniza.

**Aceite:** abrir uma venda com 2 itens de quantidades diferentes → cada linha mostra valor próprio, e a soma das linhas bate com o Total Geral.

---

### B2 — Estoque fica negativo

**Onde:** `app/Http/Controllers/SaleController.php:42,63`

A validação é só `'items.*.quantity' => 'required|integer|min:1'`. Não existe checagem contra `stock_quantity`. A linha 63 faz `decrement()` direto. Vender 100 unidades de um produto com 3 em estoque funciona e deixa `-97`. O `create()` filtra produtos com `stock_quantity > 0`, mas isso só esconde o produto zerado — não limita a quantidade. O `<input>` em `sales/create.blade.php:39` tem `min="1"` e nenhum `max`.

**Por que importa:** o estoque é o motivo de existir da tela. Um número negativo não é interpretável — o usuário não sabe se é um bug, uma dívida ou um erro dele.

**Como resolver:** validar item a item contra o estoque disponível, com mensagem que **nomeia o produto e diz quanto tem**: *"Você tem apenas 3 unidades de Coca Cola 2L em estoque."* Uma mensagem genérica de "quantidade inválida" não ajuda ninguém aqui. Somar quantidades quando o mesmo produto aparece em duas linhas (ver B2b).

**B2b — linhas duplicadas do mesmo produto** não são consolidadas: geram dois `sale_items` e dois `decrement()`. A validação de estoque tem que considerar o total por produto, não por linha.

**Aceite:** tentar vender mais que o estoque → erro claro nomeando o produto, nenhuma venda criada, estoque intacto.

---

### B3 — Excluir um cliente derruba a lista de vendas (erro 500)

**Onde:** `app/Http/Controllers/CustomersController.php` (`destroy`) · `resources/views/sales/index.blade.php:51` · `resources/views/sales/show.blade.php:19`

A FK `sales.customer_id` é `onDelete('set null')` e o `destroy` do cliente é exclusão física. Depois de excluir um cliente que tem vendas, `$sale->customer->name` estoura *"Attempt to read property name on null"*. **A tela de Vendas para de abrir** — não uma venda, a lista inteira.

**Por que importa:** é o pior tipo de falha para este público. A ação ("excluir cliente") parece dar certo, e a quebra aparece depois, em outro lugar, sem relação aparente. Não há como o usuário ligar causa e efeito, nem se recuperar sozinho.

**Como resolver:** três camadas, todas valem a pena.
1. **Guarda na view** (`$sale->customer?->name ?? 'Cliente removido'`) — conserta a tela imediatamente.
2. **`SoftDeletes` em `Customer`** — o histórico de vendas mantém o nome do cliente. Este é o conserto real.
3. **Aviso na exclusão**: se o cliente tem vendas, dizer isso antes (*"Este cliente tem 12 compras registradas. Excluir vai remover o nome dele do histórico."*).

**Aceite:** excluir um cliente com vendas → lista de Vendas continua abrindo e as vendas antigas continuam identificáveis.

---

### B4 — Excluir um produto apaga o histórico de vendas

**Onde:** `database/migrations/2026_01_19_185750_create_sale_items_table.php` · `app/Http/Controllers/ProductController.php` (`destroy`)

`sale_items.product_id` é `onDelete('cascade')`. Excluir um produto **apaga todos os itens de venda dele**, em todas as vendas passadas — enquanto `sales.total_amount` continua com o valor antigo. Resultado: vendas onde a soma dos itens não bate com o total, e faturamento histórico perdido de forma irrecuperável.

**Por que importa:** "parei de vender esse produto, vou tirar da lista" é a coisa mais natural do mundo para o dono do comércio fazer. E é destrutiva sem qualquer aviso.

**Como resolver:** `SoftDeletes` em `Product`, e a listagem passa a ocultar os excluídos. Vendas antigas mantêm o produto. Opcionalmente trocar o conceito de "Excluir" por **"Arquivar"** na interface — que é o que o usuário realmente quer dizer.

**Aceite:** excluir um produto que já foi vendido → vendas antigas continuam completas e com os itens visíveis.

---

### B5 — Venda sem transação de banco

**Onde:** `app/Http/Controllers/SaleController.php:45-67`

A venda é criada com `total_amount => 0`, depois o loop cria itens e baixa o estoque, e só no fim o total é atualizado. Não há `DB::transaction`. Qualquer falha no meio (o `findOrFail` da linha 53, timeout, deadlock) deixa **uma venda persistida com R$ 0,00**, itens parciais e estoque já baixado.

**Por que importa:** venda fantasma de R$ 0,00 no meio da lista é impossível de o usuário diagnosticar ou limpar — não existe tela para excluir venda (ver B9).

**Como resolver:** envolver todo o `store()` em `DB::transaction()`. Combinado com B2, calcular o total antes e criar a venda já com o valor certo, eliminando o `update()` posterior.

**Aceite:** forçar exceção no meio do loop → nenhuma venda no banco, estoque intacto.

---

### B6 — Máscara de moeda quebrada na edição de produto

**Onde:** `resources/views/products/edit.blade.php:42,57` chamam `brlCurrencyMask(event)`, mas a função só existe em `resources/views/products/create.blade.php:9-30`

Na tela de **editar** produto, cada tecla digitada em Preço de Custo ou Preço de Venda lança `ReferenceError` no console. A máscara simplesmente não funciona — o campo aceita qualquer coisa sem formatar.

Agrava: `products/edit.blade.php:37,52` têm `step="0.01"` em `type="text"`, atributo que não faz nada ali.

**Por que importa:** o usuário digita o preço em `create` e vê formatar bonito; volta em `edit` e o mesmo campo se comporta diferente. Inconsistência é o que mais desorienta quem está aprendendo a usar.

**Como resolver:** extrair a máscara para `resources/js/` (ou um componente Blade compartilhado) e usar nas duas telas. Ver §V4 — hoje há 2 cópias da máscara de telefone e 1 cópia + 1 referência quebrada da de moeda.

**Aceite:** digitar `1234` em Preço de Venda na tela de edição → aparece `12,34`, sem erro no console.

---

### B7 — Mensagens de validação em inglês, e uma delas sai como código na tela

**Onde:** `config/app.php:81-85` · **não existe diretório `lang/`** no projeto · `app/Http/Controllers/Auth/RegisteredUserController.php:41` · `app/Http/Requests/ProfileUpdateRequest.php:28`

`'locale' => env('APP_LOCALE', 'en')`, `fallback_locale` também `en`, e nenhum arquivo de tradução publicado. Consequências:

- Todas as mensagens do framework saem em inglês: *"The name field is required."*, `auth.failed`, `passwords.*`.
- **Toda chamada `__('...')` nas views é decorativa.** A interface aparece em português porque as strings pt-BR *são as chaves* — o `__()` não encontra tradução e devolve a chave. Funciona por acidente.
- **Bug visível:** `RegisteredUserController.php:41` e `ProfileUpdateRequest.php:28` fazem `__('validation.unique', ...)`. Sem `lang/`, isso renderiza a **string literal `validation.unique`** como mensagem de erro. Quem tenta se cadastrar com e-mail já usado vê `validation.unique` na tela.
- Sobras em inglês nas próprias views: `__('Delete Account')` e `__('Password')` em `profile/partials/delete-user-form.blade.php:4,31`, `__('Your email address is unverified.')`, e todo o `auth/verify-email.blade.php`.

**Por que importa:** este é o item de maior desproporção entre esforço e impacto no documento. Um público 50+ que não fala inglês recebe, no momento em que mais precisa de ajuda (o erro), um texto que não entende — ou um código de programador.

**Como resolver:** `APP_LOCALE=pt_BR` e `APP_FALLBACK_LOCALE=pt_BR` no `.env` e no `.env.example`; `faker_locale` para `pt_BR`; criar `lang/pt_BR/validation.php`, `auth.php`, `passwords.php`, `pagination.php` (o pacote `laravel-lang/lang` resolve isso de uma vez). Traduzir também os `attributes` do validation para os campos aparecerem como "Nome", "Preço de Venda" e não "name", "sale_price".

**Aceite:** enviar o formulário de produto vazio → erros em português nomeando os campos em português. Cadastrar com e-mail repetido → mensagem em português, não `validation.unique`.

---

### B8 — Horário das vendas 3 horas adiantado

**Onde:** `config/app.php:68` → `'timezone' => 'UTC'`, enquanto as views formatam `->format('d/m/Y H:i')` (`sales/index.blade.php:49`, `sales/show.blade.php:24`)

Uma venda registrada às 20h aparece como 23h. Perto da meia-noite, aparece **no dia seguinte**.

**Por que importa:** o dono do comércio confere as vendas do dia pelo horário. Data errada no comprovante é problema real, não estético. E vira bloqueio para §F1 (totais do dia).

**Como resolver:** `'timezone' => 'America/Sao_Paulo'`. Manter UTC no banco e converter na exibição é a alternativa mais correta a longo prazo, mas para um sistema de um fuso só, mudar o timezone da aplicação resolve com uma linha.

**Aceite:** registrar uma venda → o horário exibido é o do relógio de parede.

---

### B9 — 5 rotas registradas sem método no controller (e venda não pode ser cancelada)

**Onde:** `routes/web.php:23,26,30`

`Route::resource` registra as 7 rotas do CRUD, mas:
- `ProductController` e `CustomersController` **não têm `show()`** → `products.show` e `customers.show` retornam 500 (`BadMethodCallException`).
- `SaleController` **não tem `edit()`, `update()` nem `destroy()`** → `sales.edit`, `sales.update`, `sales.destroy` idem.

Nada linka para elas hoje, então é latente. Mas o corolário é uma lacuna funcional séria: **uma venda registrada por engano não tem saída nenhuma.** Não há editar, não há excluir, não há cancelar.

**Como resolver:** `->only([...])` nos três resources para a superfície ficar honesta. E tratar a lacuna de verdade em §F4.

**Aceite:** acessar `/products/1` → 404, não 500.

---

### B10 — Erros de validação invisíveis em 4 dos 5 formulários

**Onde:** o bloco `@if ($errors->any())` existe **só** em `resources/views/products/create.blade.php:38-47`. Não existe em `products/edit`, `customers/create`, `customers/edit`, `sales/create`.

O usuário preenche, clica em Salvar, e a página **volta exatamente igual, sem nenhuma mensagem**. Pior em `customers/create.blade.php:17,22,46`: os campos não usam `old()`, então **tudo que foi digitado é apagado**. Nome, telefone e observações somem sem explicação.

Também não há erro por campo (`aria-invalid`, `aria-describedby`) em nenhuma tela de CRUD, e `session('error')` não é renderizado em lugar nenhum do app.

**Por que importa:** este é o cenário que faz o usuário concluir "o sistema não funciona" e desistir. Ele não erra de propósito — ele não sabe que errou. E ao tentar de novo, tem que digitar tudo outra vez.

**Como resolver:** criar `<x-form-errors />` (§V4) e usar nos 5 formulários; adicionar `old()` em todos os campos de `customers/*`; marcar o campo com problema visualmente, não só listar no topo.

**Aceite:** enviar cada um dos 5 formulários com erro → mensagem visível em português **e** os dados digitados preservados.

---

# 2. Acessibilidade e UX para 50+

O que já está certo e deve ser preservado: tiles grandes de destino único no dashboard, inputs `py-3 text-lg`, labels `text-xl font-bold` nos CRUDs, `aria-current="page"` correto na navegação, `sr-only` nos botões de ícone do menu, `x-modal` acessível com foco preso e ESC.

### A1 — Tipografia: um override anula todo o resto

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

---

### A2 — Alvos de toque pequenos demais e perigosamente próximos

**Onde:** `products/index.blade.php:67-92` · `customers/index.blade.php:43-66`

As ações de cada linha são **SVG de 24×24 sem padding**, separados por `gap-4` (16px). Muito abaixo do mínimo de 44px. E o pior: **"Editar" e "Excluir" ficam a 16px um do outro**, sem rótulo, distinguíveis apenas por cor (azul/vermelho) e forma do desenho.

Para mãos com menos precisão, é um erro esperando acontecer — e o erro possível é a exclusão.

Também pequenos:
- `<select>` de status da venda: `px-3 py-1 text-sm w-36` → ~28px de altura (`sales/index.blade.php:59-62`).
- Botões Breeze: `px-4 py-2 text-xs` → ~30px (`components/primary-button.blade.php`).
- Links de paginação: view padrão do Laravel, alvos pequenos e sem customização.

Aceitáveis: FAB de novo produto (`p-3` + 24px ≈ 48px, `products/index.blade.php:103`), botão do menu (40px + área estendida por `absolute -inset-0.5`, `navigation.blade.php:21`).

**Como resolver:** substituir os ícones-only por **botões com texto** — "Editar" e "Excluir" — com no mínimo `py-3 px-4`, separados por espaço generoso, e **Excluir visualmente distinto** (contorno vermelho, não só ícone vermelho). Texto é mais rápido de ler que ícone para quem não cresceu com essas convenções.

---

### A3 — Botões de ícone sem nome acessível

**Onde:** `products/index.blade.php:67` (editar), `:80` (excluir), `:103` (FAB) · `customers/index.blade.php:43,57`

Nenhum tem `aria-label`, `title` ou `sr-only`. Um leitor de tela anuncia um link vazio. E como não há `title`, nem o usuário com mouse consegue descobrir o que o ícone faz.

O padrão correto **já existe no projeto** — `navigation.blade.php:22,81` fazem certo com `<span class="sr-only">`. É só aplicar. Resolvido de graça se §A2 for implementado com texto visível.

Adjacente: os SVGs decorativos dos tiles do dashboard não têm `aria-hidden="true"` (`dashboard.blade.php:15,31,47,63`), enquanto os da navegação têm.

---

### A4 — Formulários: rótulos desconectados e teclado errado no celular

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

---

### A5 — Informação e affordance que só existem no hover

- **Números 1–4 dos tiles** (`dashboard.blade.php:22-24,38-40,54-56,72-74`): `opacity-0 group-hover:opacity-100` — **invisíveis em qualquer celular ou tablet**. E quando aparecem, são `text-white/30`. Se são atalhos de teclado, não estão implementados; se são numeração, deviam estar sempre visíveis. Numerar os tiles ("1. Registrar Compra") é, aliás, uma boa ideia para este público — dá ordem a um menu.
- **Links de auth sem sublinhado em repouso** (`auth/login.blade.php:32`, `register.blade.php:47`): o sublinhado só aparece no hover, via `hover:after:scale-x-100`. Em repouso, "Esqueceu sua senha?" é texto cinza indistinguível do resto. Link tem que parecer link **antes** do mouse chegar.
- **Nenhum `focus-visible`** nas ações de linha das tabelas — navegação por teclado sem indicação de onde está.
- **`lg:hover:scale-105`** nos tiles (`dashboard.blade.php:13,29,45,61`) e nenhum `prefers-reduced-motion` no projeto inteiro.
- Feedback de linha por `hover:bg-gray-50` sem equivalente de foco.
- "Ver Detalhes" em `sales/index.blade.php:74` distingue-se do texto vizinho **só pela cor** (`text-indigo-600`), sem sublinhado.

---

### A6 — Confirmação de ações destrutivas

**Estado atual:**
- Produto: `confirm('Tem certeza que deseja excluir este produto?')` (`products/index.blade.php:82`)
- Cliente: `confirm('Excluir este cliente?')` (`customers/index.blade.php:58`)

Três palavras, sem dizer **qual** cliente, num diálogo nativo minúsculo e sem estilo. Numa lista de 10 linhas, o usuário não tem como confirmar que clicou na linha certa.

**Pior caso, e não tem confirmação nenhuma:** `sales/index.blade.php:59` — `<select name="status" onchange="this.form.submit()">`. Um giro de roda do mouse sobre o select focado, ou uma seta do teclado, **marca a venda como Paga e grava**. A ação é irreversível pela interface: o select fica `disabled` quando `paid` (`:61`) e não há como voltar.

**Como resolver:**
1. Trocar os dois `confirm()` por `<x-modal>` — **o componente já existe e é acessível** (`components/modal.blade.php`: foco preso, ESC, scroll travado). Hoje é usado em um único lugar, a exclusão de conta. A modal **nomeia o registro**: *"Excluir o cliente **João da Silva**? Esta ação não pode ser desfeita."*
2. O status da venda deixa de ser `<select onchange>` e passa a ser **um botão explícito** "Marcar como Pago" com confirmação. Botão é intencional; select não é.
3. Onde for irreversível, dizer que é irreversível — com essas palavras.

---

### A7 — Nenhum estado de carregamento, e feedback inconsistente

**Nenhum estado de carregamento em todo o app.** Sem spinner, sem `disabled` no submit, sem `aria-busy`. "Finalizar Venda" (`sales/create.blade.php:47`) clicado duas vezes — comportamento comum quando nada acontece na tela após o primeiro clique — **registra duas vendas e baixa o estoque duas vezes** (agravado por B5). Este público clica de novo justamente porque não houve resposta.

**Feedback inconsistente:**
- Banner de sucesso copiado 3 vezes com 2 marcações diferentes: `sales/index.blade.php:18-21` e `products/index.blade.php:18-21` (negrito "Sucesso!" + `role="alert"`) vs `customers/index.blade.php:19-21` (sem `role`, sem título).
- `session('error')` **nunca é renderizado** — não existe canal de mensagem de erro no app.
- Confirmações do perfil **desaparecem em 2 segundos** (`update-password-form.blade.php:43`, `update-profile-information-form.blade.php:58`) e são `text-sm text-gray-600` — o texto menos visível da página, sumindo antes de ser lido.

**Como resolver:** componente único `<x-alert>` (§V4); `disabled` + texto "Salvando..." no submit; aumentar ou eliminar o auto-hide das confirmações de perfil.

---

### A8 — Nenhum estado vazio

Todos os índices usam `@foreach`, não `@forelse` (`products/index.blade.php:46`, `customers/index.blade.php:34`, `sales/index.blade.php:46`). **A primeira tela que um usuário novo vê é um cabeçalho de tabela vazio**, sem uma palavra de orientação.

Igual em `sales/create.blade.php`: sem produtos ou clientes cadastrados, os `<select>` aparecem vazios sem nenhuma explicação — e como cliente é obrigatório, a venda é impossível e o motivo é invisível.

**Por que importa:** é o momento de maior risco de abandono, e o único momento em que o app tem certeza de que a pessoa é nova.

**Como resolver:** `@forelse` com estado vazio que **instrui e oferece o próximo passo**: *"Você ainda não cadastrou produtos. Comece cadastrando o primeiro."* + botão grande. Em `sales/create`, quando faltar cliente ou produto, mostrar o que falta e o link para cadastrar.

---

### A9 — Tabelas exigem rolagem horizontal no celular

`products/index.blade.php:24` e `sales/index.blade.php:24` usam `overflow-x-auto`. **`customers/index.blade.php:24` não tem nem isso** — a tabela transborda o cartão.

Rolagem horizontal é um obstáculo conhecido para este público: a rolagem vertical é aprendida, a horizontal não é descoberta — a coluna "Ações" simplesmente não existe para quem não sabe arrastar.

**Como resolver:** abaixo de `sm`, trocar tabela por **cartões empilhados** — um cartão por produto/cliente/venda, com rótulo e valor em linhas próprias e os botões de ação em largura total. Nada de rolagem lateral.

---

### A10 — Breakpoints redefinidos criam uma faixa quebrada

`resources/css/app.css:16-21` redefine a escala:

```css
--breakpoint-md: 64rem;  /* 1024px — o padrão do Tailwind é 768px */
--breakpoint-lg: 80rem;  /* 1280px — padrão 1024px */
--breakpoint-xl: 90rem;  /* 1440px — padrão 1280px */
```

`md:` dispara a **1024px**, não 768. Então todo `md:grid-cols-2` / `md:grid-cols-3` (`dashboard.blade.php:10`, `sales/create.blade.php:27`, `products/create.blade.php:56`, `sales/show.blade.php:16`) fica em uma coluna até 1024px — tablets e celulares na horizontal incluídos. E `products/index.blade.php:8` mantém o botão "+ Novo Produto" escondido (`hidden md:flex`) com o FAB no lugar dele até 1024px.

Enquanto isso a **navegação** troca para desktop em `sm:` (640px). **Entre 640 e 1024px o app tem menu de desktop com corpo em modo celular.** É a faixa de um iPad na vertical.

**Como resolver:** decidir **um** ponto de virada e usá-lo nos dois lugares. Recomendação: voltar `md` ao padrão de 768px e trocar a navegação de `sm:` para `md:` — em telas menores que 768px o app fica coerentemente em modo celular.

---

### A11 — Consistência e detalhes

- **`customers/edit.blade.php:4` diz "Cadastrar Novo Cliente"** na tela de edição. O usuário não tem confirmação de que está editando e não criando — e pode achar que duplicou o cliente.
- **`<title>` idêntico em todas as páginas** (`layouts/app.blade.php:8` → só `config('app.name')`). Abas e histórico do navegador ficam indistinguíveis. Um público que abre várias abas e usa o botão Voltar sente isso.
- **`min-h-screen` duplicado**: o shell já é `min-h-screen` (`layouts/app.blade.php:21`) e cada página aplica de novo num div interno (`dashboard.blade.php:8`, `products/index.blade.php:14`, e todas as outras). Toda página tem no mínimo o dobro da altura da tela, com rolagem morta no fim. Confuso: rolar e não encontrar nada.
- **Comprovante sem CSS de impressão**: `sales/show.blade.php:62` chama `window.print()` e não existe `@media print`. A impressão sai com o fundo azul-marinho, a navegação, o cabeçalho e o próprio botão "Imprimir Comprovante". Desperdiça tinta e o resultado não parece um comprovante. Um `@media print` que esconda o chrome é ganho grande por diff pequeno.
- **Sem `<a href="#main">` de pular para o conteúdo**, e `main` sem `id`.
- **`welcome.blade.php` duplica o documento HTML inteiro** em vez de usar layout (head, fontes e vite repetidos).
- Typos que anulam classes: `transtition-colors` (`products/create.blade.php:105`, `products/edit.blade.php:79`) e `flex-col flex-col` (`welcome.blade.php:24`).
- Emoji como ícone semântico sem rótulo em `welcome.blade.php:49,57,64` — inclusive `👴` representando "fácil de usar". Vale reconsiderar: falar *para* o público sem estereotipá-lo.
- **"Sair do Sistema" só existe dentro do dropdown de perfil** (`navigation.blade.php:104`) e **não é repetido no menu mobile**. Sair é uma das poucas ações que este público procura ativamente; deve estar visível.

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

### V2 — Direção recomendada: inverter o peso

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

### V3 — Sistema de tokens

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

### V4 — Componentes a criar (hoje copiados e colados)

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

### V5 — Limpeza

- **Código morto:** `components/nav-link.blade.php`, `responsive-nav-link.blade.php`, `dropdown.blade.php`, `dropdown-link.blade.php` (a navegação migrou para `<el-*>`) e `application-logo.blade.php` (a nav usa `assets/imgs/logo.png`).
- **Classes inertes na v4:** `opacity-75` / `ring-opacity-5` em `modal` e `dropdown`.
- `stock_alert` e `description` são validados nos controllers sem existir no formulário (§F2, §F5).

### V6 — Decisão a tomar: sair do CDN `@tailwindplus/elements`

O CDN não fixado é dependência de rede em runtime numa parte crítica: **sem ele, não há menu no celular nem botão de sair**. Opções:
1. Instalar via npm com versão fixa e entrar no bundle do Vite.
2. **Reescrever navegação e dropdown em Alpine** — já está no bundle, já é usado pela `x-modal`, e elimina uma das duas stacks concorrentes.

**Recomendação: (2).** Uma stack a menos para manter, nada carregado de fora, e a `x-modal` prova que o padrão Alpine já funciona aqui. Como efeito colateral, resolve o "Sair do Sistema" ausente no mobile (§A11).

---

# 4. Funcionalidades novas

Poucas, cada uma justificada por "o que o dono do comércio tem que fazer hoje na falta dela".

### F1 — Dashboard com números

**Hoje:** `routes/web.php:13` é uma closure devolvendo uma grade estática. Zero informação. `cost_price` é coletado e **não é usado para nada**.

**Sem isso, o usuário faz:** abre a lista de Vendas e soma de cabeça, ou na calculadora, para saber quanto vendeu hoje.

**Proposta:** criar `DashboardController` e mostrar, nos próprios tiles, números grandes acima do rótulo:
- **Vendido hoje** e **vendido no mês** (depende de §B8 — com timezone errado, "hoje" está errado)
- **Vendas pendentes** (quantidade + valor a receber) — a mais útil das quatro: é dinheiro na rua
- **Produtos com estoque baixo** (depende de §F2)

Os 4 tiles atuais continuam. É a mesma tela, agora informando.

### F2 — Alerta de estoque baixo de verdade

A coluna `stock_alert` existe com `default(5)`, está em `$fillable`, e é **validada nos dois métodos** do `ProductController` (`:65` e `:105`). Mas:
- **Não existe campo dela em nenhum formulário** → vale 5 para sempre, para todo produto.
- `products/index.blade.php:53,57` **ignora a coluna** e usa `5` fixo no código.

É uma funcionalidade construída até a metade. Faltam duas coisas: o campo no formulário e ler `$product->stock_alert` no lugar do `5`.

**Por que importa:** o limiar certo depende do produto. Cerveja e detergente não têm o mesmo ponto de reposição, e quem sabe disso é o dono.

Adicionar também um contador de produtos em alerta no dashboard (§F1) — o alerta hoje só existe se a pessoa for até a tela de Estoque e olhar linha por linha.

### F3 — Busca e filtro

**Não existe busca ou filtro em nenhuma tela**, com `paginate(10)`. Quem tem 60 produtos precisa navegar 6 páginas para achar um.

- Produtos e Clientes: busca por nome, campo grande e visível no topo.
- Vendas: filtro por status (Pendente/Pago) e por período.

**Por que importa:** paginação sem busca transfere o trabalho de lembrar para o usuário. E o filtro "Pendentes" é o relatório que este negócio mais usa: quem me deve.

### F4 — Cancelar venda com devolução ao estoque

**Hoje uma venda registrada por engano não tem saída nenhuma:**
- Não há `edit` nem `destroy` em `SaleController` (§B9).
- `sales.status` é só `enum('paid','pending')` — não existe `cancelled`.
- `updateStatus` valida `'status' => 'required|in:pending,paid'` **sem guarda de máquina de estado**: o `<select>` fica `disabled` na tela quando pago, mas um PATCH direto volta de `paid` para `pending`.

**Sem isso, o usuário faz:** conviver com a venda errada para sempre, e corrigir o estoque na mão editando o produto — o que desalinha o histórico do faturamento.

**Proposta:** status `cancelled`; ação "Cancelar Venda" com confirmação nomeando o valor e o cliente; devolução do estoque dentro de transação; venda cancelada permanece na lista, marcada, fora dos totais. **Cancelar, não excluir** — o histórico é o registro do negócio.

### F5 — Campo de descrição do produto

Mesma situação do `stock_alert`: `description` está em `$fillable`, é validado (`ProductController:64`), **não tem campo em formulário nenhum e não é exibido em lugar nenhum**. Decidir: usar (campo + exibição na lista e no comprovante) ou remover das regras de validação. Regra validando campo inexistente é ruído que confunde na próxima leitura do código.

### Fora de escopo agora (backlog)

Registrado para não se perder, sem prioridade atribuída: relatório de faturamento por período; **margem de lucro** (o `cost_price` já é coletado e nunca usado — dado pronto sem uso); formas de pagamento; fechamento de caixa; data retroativa de venda (hoje usa `created_at`, então não se lança a venda de ontem); exportação para planilha; recibo por WhatsApp.

---

# 5. Base técnica que falta

Curto e direto, porque sustenta tudo acima.

### T1 — Zero testes do domínio

`tests/` é apenas o scaffolding do Breeze: 6 testes de autenticação, `ProfileTest`, 2 `ExampleTest`. **Nenhum teste de produto, cliente ou venda.** Não há teste da baixa de estoque, do cálculo do total, do parser de moeda pt-BR, do blind index — nem do **isolamento multi-tenant**, que foi o objetivo inteiro do commit `ad4f1a8`.

**O bloqueio prático:** só existe `UserFactory`. Sem `ProductFactory`, `CustomerFactory`, `SaleFactory`, escrever esses testes é penoso. Criar as factories é o primeiro passo, e Pest 4 já está instalado.

Prioridade de cobertura: (1) isolamento entre usuários, (2) baixa de estoque e total da venda, (3) parser de moeda `normalizeMoneyForValidation`.

### T2 — `ScopedToUser` falha aberto

`app/Models/Concerns/ScopedToUser.php:16` aplica o escopo só `if (Auth::check())`. Em contexto de console, queue ou scheduler **não há autenticação, logo não há escopo** — `Product::all()` retorna as linhas de todos os usuários. Não há comandos nem jobs hoje, então é latente; é uma armadilha de vazamento entre comércios no primeiro que for escrito. Documentar no próprio trait, no mínimo.

### T3 — `user_id` é `nullable` nas três tabelas de tenant

`products`, `customers` e `sales` declaram `->string('user_id', 36)->nullable()`. Uma linha com `user_id = NULL` fica **invisível para todos os usuários** (por causa do escopo global) e órfã para sempre. Deveria ser obrigatório.

### T4 — Regras de validação duplicadas

As regras de produto estão repetidas literalmente entre `store()` (`ProductController:59-66`) e `update()` (`:99-106`); as de cliente entre `CustomersController:24-28` e `:49-53`. Duas cópias divergem no primeiro ajuste. Extrair para FormRequest (só `ProfileUpdateRequest` e `Auth/LoginRequest` existem hoje).

Menor, no mesmo arquivo: `unset($validated['user_id'])` em `ProductController:68` e `CustomersController:30` é código morto — `user_id` não está nas regras, então nunca chega em `$validated`.

### T5 — Sem casts decimais

`Product` e `SaleItem` não declaram `casts()`. `sale_price`, `cost_price` e `unit_price` voltam do MySQL como **string**, e `SaleController:54` faz `$product->sale_price * $item['quantity']` contando com a conversão implícita do PHP. Funciona, mas `'sale_price' => 'decimal:2'` é o correto e evita surpresa de arredondamento.

### T6 — Verificação de e-mail é código morto

`app/Models/User.php:5` tem `MustVerifyEmail` **comentado** e a classe não o implementa. Logo o middleware `verified` em `routes/web.php:15` **não faz nada**. Mas `ProfileController:32` continua zerando `email_verified_at` ao trocar o e-mail, e `update-profile-information-form.blade.php:34-43` continua renderizando o aviso "e-mail não verificado" — **um aviso que o usuário nunca consegue resolver**, porque o fluxo está desativado.

Decidir: ativar a verificação, ou remover o aviso, o `verified` e as 4 rotas/controllers de verificação. Para este público, um aviso insolúvel na tela de conta é motivo de ligação para o suporte.

### T7 — `password_reset_tokens.email` em texto puro

`users.email` é criptografado com blind index HMAC — um design cuidadoso. Mas `password_reset_tokens` tem `email` como **string em texto puro** e chave primária (`0001_01_01_000000_create_users_table.php`). Cada pedido de "esqueci a senha" grava o e-mail em claro no banco enquanto o token existir. Vale registrar dado o esforço investido na criptografia.

### T8 — Índice composto ausente

Todos os índices usam `->latest()` (ordena por `created_at`) filtrando por `user_id`, e não há índice `(user_id, created_at)`. O banco filtra pelo índice de `user_id` e ordena em memória. Irrelevante no volume atual; barato de resolver junto com outra migration.

### T9 — Dívidas de infraestrutura

- `docker-compose.yml` no repositório tem **senhas de MySQL em texto** e monta caminhos (`./workspace`, `./mysql/*`, contexto `./php2`) que **não existem no repositório** — parece obsoleto. Este projeto roda no ambiente compartilhado de `/home/guilherme`, no container `guilherme_php2_1`. Ou o compose é corrigido, ou removido.
- `database/seeders/DatabaseSeeder.php:18-22` cria `admin@admin.com` / `102030` **sem guarda de ambiente** (`app()->environment()`). Precisa de guarda antes de qualquer deploy.
- `bootstrap/app.php`: `withMiddleware` e `withExceptions` vazios. Sem página de erro customizada — um 500 cru é assustador para este público. Uma página de erro em português dizendo o que fazer é barato e vale muito.

---

# 6. Ordem de execução sugerida

Quatro entregas pequenas, não uma grande. Cada uma é utilizável por si.

### Fase 1 — Base invisível (menor risco, maior percepção)
`B7` locale + `lang/pt_BR` · `B8` timezone · `A1` tipografia (**incluindo apagar o override de `.text-2xl`**) · `B6` máscara · `B10` erros visíveis + `old()`

Nada de estrutura nova. Muda a sensação do app inteiro com diff pequeno.
**Pronto quando:** nenhum texto abaixo de 16px, nenhuma mensagem em inglês, todo formulário mostra o que deu errado sem apagar o que foi digitado.

### Fase 2 — Integridade de dados
`T1` factories + primeiros testes · `B1` subtotal · `B2` estoque negativo · `B5` transação · `B3` cliente excluído · `B4` produto excluído · `B9` `->only()` · `T3` `user_id` obrigatório

Factories primeiro — sem elas o resto vai sem rede de proteção.
**Pronto quando:** existe teste cobrindo baixa de estoque, total da venda e isolamento entre usuários, e nenhum caminho de exclusão corrompe histórico.

### Fase 3 — UX 50+
`A2` a `A9`, criando os componentes de §V4 no caminho · `A11` (impressão do comprovante, `<title>`, `min-h-screen`) · `A10` decidir o breakpoint

**Pronto quando:** nenhum alvo abaixo de 44px, nenhuma ação destrutiva sem confirmação nomeada, nenhuma tela vazia sem orientação, nenhuma tabela com rolagem horizontal no celular.

### Fase 4 — Visual e funcionalidades
`V3` tokens no `@theme` · `V2` inversão de peso · `V6` sair do CDN · `V5` limpeza · `F1` dashboard com números · `F2` estoque baixo real · `F3` busca

`V2` é a única fase que muda a aparência de forma perceptível. Vale testar com um usuário real do perfil-alvo antes de fechar.

Fora das fases, quando fizer sentido: `T4` FormRequests · `T5` casts · `T6` decisão sobre verificação de e-mail · `F4` cancelar venda · `F5` `description` · `T9` infra.

---

## Como rodar e verificar

```bash
# Testes (linha de base antes das fases 2-4)
docker exec -it guilherme_php2_1 bash -lc "cd /var/www/html/systemBar && php artisan test --compact"

# Build do front
docker exec -it guilherme_php2_1 bash -lc "cd /var/www/html/systemBar && npm run build"

# Dev
docker exec -it guilherme_php2_1 bash -lc "cd /var/www/html/systemBar && npm run dev"
```

App em `systembar.localhost` (container `guilherme_php2_1`, PHP 8.3).

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
