<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'exists' => 'Ընտրված էլ. Հասցեով օգտատեր  գոյություն ունի։',
    'password' => 'Խնդրում ենք մուտքագրել ճիշտ գաղտնաբառ:',
    'invalid-password' => 'Սխալ գաղտնաբառ',
    'valid-password' => 'Վավեր գաղտնաբառ',
    'email' => 'Խնդրում ենք մուտքագրել ճիշտ էլ. հասցե:',
    'required' => 'Դաշտը պարտադիր է լցնելու համար:',
    'invalid-provider' => 'Դիմում սխալ provider-ով',
    'confirmed' => 'Գաղտնաբառի հաստատումը չի համընկնում:',
    'max' => [
        'array' => 'Չպետք է ունենա :max տարրից ավելի:',
        'file' => 'Չպետք է ավելի մեծ լինի, քան :max կիլոբայթը:',
        'numeric' => 'Չպետք է ավելի մեծ լինի, քան :max :',
        'string' => 'Չպետք է լինի :max նիշից մեծ:',
    ],
    'max_digits' => 'Չպետք է ունենա ավելի քան :max թվանշան:',
    'integer' => 'Պետք է լինի ամբողջ թիվ։',
    'first_name' => 'Մինիմում 5 տառ',
    'last_name' => 'Մինիմում 5 տառ',
    'company_name' => 'Մինիմում 5 տառ',
    'tax_identity_number' => 'Մինիմում 5 տառ',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'price' => [
            'regex' => 'Գինը պետք է լինի տասնորդական:',
        ],
        "role_id" => [
            "exists" => "Դերը չի գտնվել"
        ],
        "trainer_id" => [
            "exists" => "Դասընթացավարը չի գտնվել"
        ],
        "category_id" => [
            "exists" => "Կատեգորիան չի գտնվել"
        ],
        "language" => [
            "exists" => "Լեզուն չի գտնվել"
        ],
        "language_id" => [
            "exists" => "Լեզուն չի գտնվել"
        ],
        "link" => [
            "regex" => "Սխալ հասցե"
        ],

        "course_id" => [
            "exists" => "Կուրսը չի գտնվել"
        ],

       "email"=> [
           "unique"=>"Դուք արդեն բաժանորդագրված եք"
       ],
        'file' => [
            'mimes' => 'Ֆայլի սխալ ֆորմատ',
            'max' => 'Ֆայլի չափը չպետք է գերազանցի :max կիլոբայթը',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
