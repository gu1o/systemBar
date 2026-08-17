<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensagens de validação
    |--------------------------------------------------------------------------
    |
    | As mensagens das regras usadas por este sistema (required, numeric,
    | min/max, email, unique, confirmed...) foram escritas em linguagem direta,
    | porque o público do sistema não é técnico. As demais seguem a tradução
    | literal do Laravel — o arquivo precisa estar completo, senão a chave crua
    | (ex.: "validation.regex") aparece na tela do usuário.
    |
    */

    'accepted' => 'É necessário aceitar :attribute.',
    'accepted_if' => 'É necessário aceitar :attribute quando :other for :value.',
    'active_url' => 'O campo :attribute não contém um endereço válido.',
    'after' => 'O campo :attribute deve ser uma data depois de :date.',
    'after_or_equal' => 'O campo :attribute deve ser uma data igual ou depois de :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, números, hífens e sublinhados.',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e números.',
    'any_of' => 'O campo :attribute não é válido.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'ascii' => 'O campo :attribute deve conter apenas caracteres e símbolos de um byte.',
    'before' => 'O campo :attribute deve ser uma data antes de :date.',
    'before_or_equal' => 'O campo :attribute deve ser uma data igual ou antes de :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O arquivo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve ser um valor entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'can' => 'O campo :attribute contém um valor não permitido.',
    'confirmed' => 'A confirmação do campo :attribute não confere.',
    'contains' => 'Falta um valor obrigatório no campo :attribute.',
    'current_password' => 'A senha informada está incorreta.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O campo :attribute deve estar no formato :format.',
    'decimal' => 'O campo :attribute deve ter :decimal casas decimais.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other for :value.',
    'different' => 'Os campos :attribute e :other devem ser diferentes.',
    'digits' => 'O campo :attribute deve ter :digits dígitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'A imagem :attribute está com dimensões inválidas.',
    'distinct' => 'O campo :attribute tem um valor repetido.',
    'doesnt_contain' => 'O campo :attribute não pode conter nenhum destes valores: :values.',
    'doesnt_end_with' => 'O campo :attribute não pode terminar com: :values.',
    'doesnt_start_with' => 'O campo :attribute não pode começar com: :values.',
    'email' => 'Informe um :attribute válido.',
    'encoding' => 'O campo :attribute deve estar codificado em :encoding.',
    'ends_with' => 'O campo :attribute deve terminar com: :values.',
    'enum' => 'A opção escolhida para :attribute não é válida.',
    'exists' => 'A opção escolhida para :attribute não é válida.',
    'extensions' => 'O arquivo :attribute deve ter uma destas extensões: :values.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'filled' => 'Preencha o campo :attribute.',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens.',
        'file' => 'O arquivo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ter mais de :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value itens ou mais.',
        'file' => 'O arquivo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value caracteres ou mais.',
    ],
    'hex_color' => 'O campo :attribute deve ser uma cor hexadecimal válida.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'A opção escolhida para :attribute não é válida.',
    'in_array' => 'O campo :attribute deve existir em :other.',
    'in_array_keys' => 'O campo :attribute deve conter pelo menos uma destas chaves: :values.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'ip' => 'O campo :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O campo :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O campo :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O campo :attribute deve ser um JSON válido.',
    'list' => 'O campo :attribute deve ser uma lista.',
    'lowercase' => 'O campo :attribute deve conter apenas letras minúsculas.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O arquivo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute não pode ter mais de :value itens.',
        'file' => 'O arquivo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value caracteres ou menos.',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereço MAC válido.',
    'max' => [
        'array' => 'O campo :attribute não pode ter mais de :max itens.',
        'file' => 'O arquivo :attribute não pode ser maior que :max kilobytes.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'max_digits' => 'O campo :attribute não pode ter mais de :max dígitos.',
    'mimes' => 'O arquivo :attribute deve ser do tipo: :values.',
    'mimetypes' => 'O arquivo :attribute deve ser do tipo: :values.',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
        'file' => 'O arquivo :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min dígitos.',
    'missing' => 'O campo :attribute não deve ser enviado.',
    'missing_if' => 'O campo :attribute não deve ser enviado quando :other for :value.',
    'missing_unless' => 'O campo :attribute não deve ser enviado a não ser que :other seja :value.',
    'missing_with' => 'O campo :attribute não deve ser enviado quando :values estiver presente.',
    'missing_with_all' => 'O campo :attribute não deve ser enviado quando :values estiverem presentes.',
    'multiple_of' => 'O campo :attribute deve ser um múltiplo de :value.',
    'not_in' => 'A opção escolhida para :attribute não é válida.',
    'not_regex' => 'O formato do campo :attribute não é válido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'password' => [
        'letters' => 'A senha deve conter pelo menos uma letra.',
        'mixed' => 'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
        'numbers' => 'A senha deve conter pelo menos um número.',
        'symbols' => 'A senha deve conter pelo menos um símbolo (por exemplo: ! @ # $).',
        'uncompromised' => 'Esta senha já apareceu em vazamentos de dados na internet. Escolha outra senha.',
    ],
    'present' => 'O campo :attribute deve ser enviado.',
    'present_if' => 'O campo :attribute deve ser enviado quando :other for :value.',
    'present_unless' => 'O campo :attribute deve ser enviado a não ser que :other seja :value.',
    'present_with' => 'O campo :attribute deve ser enviado quando :values estiver presente.',
    'present_with_all' => 'O campo :attribute deve ser enviado quando :values estiverem presentes.',
    'prohibited' => 'O campo :attribute não é permitido.',
    'prohibited_if' => 'O campo :attribute não é permitido quando :other for :value.',
    'prohibited_if_accepted' => 'O campo :attribute não é permitido quando :other for aceito.',
    'prohibited_if_declined' => 'O campo :attribute não é permitido quando :other for recusado.',
    'prohibited_unless' => 'O campo :attribute não é permitido a não ser que :other esteja em :values.',
    'prohibits' => 'O campo :attribute impede que :other seja enviado.',
    'regex' => 'O formato do campo :attribute não é válido.',
    'required' => 'Preencha o campo :attribute.',
    'required_array_keys' => 'O campo :attribute deve conter entradas para: :values.',
    'required_if' => 'Preencha o campo :attribute quando :other for :value.',
    'required_if_accepted' => 'Preencha o campo :attribute quando :other for aceito.',
    'required_if_declined' => 'Preencha o campo :attribute quando :other for recusado.',
    'required_unless' => 'Preencha o campo :attribute a não ser que :other esteja em :values.',
    'required_with' => 'Preencha o campo :attribute quando :values estiver presente.',
    'required_with_all' => 'Preencha o campo :attribute quando :values estiverem presentes.',
    'required_without' => 'Preencha o campo :attribute quando :values não estiver presente.',
    'required_without_all' => 'Preencha o campo :attribute quando nenhum de :values estiver presente.',
    'same' => 'Os campos :attribute e :other devem ser iguais.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O arquivo :attribute deve ter :size kilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'starts_with' => 'O campo :attribute deve começar com: :values.',
    'string' => 'O campo :attribute deve ser um texto.',
    'timezone' => 'O campo :attribute deve ser um fuso horário válido.',
    'unique' => 'Este :attribute já está sendo usado.',
    'uploaded' => 'Não foi possível enviar o arquivo :attribute.',
    'uppercase' => 'O campo :attribute deve conter apenas letras maiúsculas.',
    'url' => 'O campo :attribute deve ser um endereço válido.',
    'ulid' => 'O campo :attribute deve ser um ULID válido.',
    'uuid' => 'O campo :attribute deve ser um UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Mensagens específicas por campo
    |--------------------------------------------------------------------------
    |
    | Convenção "campo.regra". Usado onde a mensagem genérica ficaria confusa
    | para quem não é técnico.
    |
    */

    'custom' => [
        'password' => [
            'confirmed' => 'A senha e a confirmação da senha não são iguais.',
        ],
        'customer_id' => [
            'required' => 'Escolha um cliente para esta venda.',
            'exists' => 'O cliente escolhido não foi encontrado.',
        ],
        'items' => [
            'required' => 'Adicione pelo menos um produto à venda.',
            'min' => 'Adicione pelo menos um produto à venda.',
        ],
        'items.*.product_id' => [
            'required' => 'Escolha um produto em todas as linhas da venda.',
            'exists' => 'Um dos produtos escolhidos não foi encontrado.',
        ],
        'items.*.quantity' => [
            'required' => 'Informe a quantidade de cada produto.',
            'min' => 'A quantidade deve ser no mínimo 1.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nomes dos campos
    |--------------------------------------------------------------------------
    |
    | Faz a mensagem citar o campo com o mesmo nome que aparece no formulário,
    | em vez do nome da coluna do banco ("sale_price").
    |
    */

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'current_password' => 'senha atual',
        'phone' => 'telefone',
        'notes' => 'observações',
        'description' => 'descrição',
        'cost_price' => 'preço de custo',
        'sale_price' => 'preço de venda',
        'stock_quantity' => 'quantidade em estoque',
        'stock_alert' => 'alerta de estoque',
        'customer_id' => 'cliente',
        'product_id' => 'produto',
        'quantity' => 'quantidade',
        'total_amount' => 'valor total',
        'status' => 'situação',
        'items' => 'produtos da venda',
        'items.*.product_id' => 'produto',
        'items.*.quantity' => 'quantidade',
    ],

];
