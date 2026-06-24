<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * Коды стран:
 *    - ISO 3166-1 2-буквенные сокращения;
 *    - ISO 3166-1 3-буквенные сокращения;
 *    - ISO 3166-1 трёхцифровая система;
 *    - ISO 3166-2 алфавитно-цифровые геокоды;
 *    - ГОСТ 7.67 «Коды названий стран».
 * 
 * Если в стране не указан язык, значит страна не входит в перечень локалей {@link locales.php}.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

return [
    'ZW' => // (0) ISO 3166-1 alpha-2
    [
        'name'         => 'Zimbabwe',
        'rusName'      => 'Зимбабве',
        'languages'    => ['en', 'nd', 'sn'],
        'iso3166_1_a2' => 'ZW',
        'iso3166_1_a3' => 'ZWE',
        'iso3166_1_n'  => '716',
        'iso3166_2'    => 'ISO 3166-2:ZW',
        'gost7_67'     => ['cyrillic' => 'ЗИМ', 'numeric' => '716'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Flag_of_Zimbabwe.svg/22px-Flag_of_Zimbabwe.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Flag_of_Zimbabwe.svg/320px-Flag_of_Zimbabwe.svg.png'
        ] 
    ],
    'ZM' => // (1) ISO 3166-1 alpha-2
    [
        'name'         => 'Zambia',
        'rusName'      => 'Замбия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'ZM',
        'iso3166_1_a3' => 'ZMB',
        'iso3166_1_n'  => '894',
        'iso3166_2'    => 'ISO 3166-2:ZM',
        'gost7_67'     => ['cyrillic' => 'ЗАМ', 'numeric' => '894'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/06/Flag_of_Zambia.svg/22px-Flag_of_Zambia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/06/Flag_of_Zambia.svg/320px-Flag_of_Zambia.svg.png'
        ] 
    ],
    'ZA' => // (2) ISO 3166-1 alpha-2
    [
        'name'         => 'South Africa',
        'rusName'      => 'Южно-Африканская Республика',
        'languages'    => ['af', 'en', 'zu'],
        'iso3166_1_a2' => 'ZA',
        'iso3166_1_a3' => 'ZAF',
        'iso3166_1_n'  => '710',
        'iso3166_2'    => 'ISO 3166-2:ZA',
        'gost7_67'     => ['cyrillic' => 'ЮЖН', 'numeric' => '710'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/af/Flag_of_South_Africa.svg/22px-Flag_of_South_Africa.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/af/Flag_of_South_Africa.svg/320px-Flag_of_South_Africa.svg.png'
        ] 
    ],
    'YT' => // (3) ISO 3166-1 alpha-2
    [
        'name'         => 'Mayotte',
        'rusName'      => 'Майотта',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'YT',
        'iso3166_1_a3' => 'MYT',
        'iso3166_1_n'  => '175',
        'iso3166_2'    => 'ISO 3166-2:YT',
        'gost7_67'     => ['cyrillic' => 'МАО', 'numeric' => '175'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Flag_of_Mayotte_%28local%29.svg/22px-Flag_of_Mayotte_%28local%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Flag_of_Mayotte_%28local%29.svg/320px-Flag_of_Mayotte_%28local%29.svg.png'
        ] 
    ],
    'YE' => // (4) ISO 3166-1 alpha-2
    [
        'name'         => 'Yemen',
        'rusName'      => 'Йемен',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'YE',
        'iso3166_1_a3' => 'YEM',
        'iso3166_1_n'  => '887',
        'iso3166_2'    => 'ISO 3166-2:YE',
        'gost7_67'     => ['cyrillic' => 'ЙЕМ', 'numeric' => '887'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/89/Flag_of_Yemen.svg/22px-Flag_of_Yemen.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/89/Flag_of_Yemen.svg/320px-Flag_of_Yemen.svg.png'
        ] 
    ],
    'WS' => // (5) ISO 3166-1 alpha-2
    [
        'name'         => 'Samoa',
        'rusName'      => 'Самоа',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'WS',
        'iso3166_1_a3' => 'WSM',
        'iso3166_1_n'  => '882',
        'iso3166_2'    => 'ISO 3166-2:WS',
        'gost7_67'     => ['cyrillic' => 'САМ', 'numeric' => '882'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Samoa.svg/22px-Flag_of_Samoa.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Samoa.svg/320px-Flag_of_Samoa.svg.png'
        ] 
    ],
    'WF' => // (6) ISO 3166-1 alpha-2
    [
        'name'         => 'Wallis & Futuna',
        'rusName'      => 'Уоллис и Футуна',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'WF',
        'iso3166_1_a3' => 'WLF',
        'iso3166_1_n'  => '876',
        'iso3166_2'    => 'ISO 3166-2:WF',
        'gost7_67'     => ['cyrillic' => 'УОЛ', 'numeric' => '876'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/22px-Flag_of_France.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/320px-Flag_of_France.svg.png'
        ] 
    ],
    'VU' => // (7) ISO 3166-1 alpha-2
    [
        'name'         => 'Vanuatu',
        'rusName'      => 'Вануату',
        'languages'    => ['en', 'fr'],
        'iso3166_1_a2' => 'VU',
        'iso3166_1_a3' => 'VUT',
        'iso3166_1_n'  => '548',
        'iso3166_2'    => 'ISO 3166-2:VU',
        'gost7_67'     => ['cyrillic' => 'ВАН', 'numeric' => '548'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Vanuatu.svg/22px-Flag_of_Vanuatu.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Vanuatu.svg/320px-Flag_of_Vanuatu.svg.png'
        ] 
    ],
    'VN' => // (8) ISO 3166-1 alpha-2
    [
        'name'         => 'Vietnam',
        'rusName'      => 'Вьетнам',
        'languages'    => ['vi'],
        'iso3166_1_a2' => 'VN',
        'iso3166_1_a3' => 'VNM',
        'iso3166_1_n'  => '704',
        'iso3166_2'    => 'ISO 3166-2:VN',
        'gost7_67'     => ['cyrillic' => 'ВЬЕ', 'numeric' => '704'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/22px-Flag_of_Vietnam.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/320px-Flag_of_Vietnam.svg.png'
        ] 
    ],
    'VI' => // (9) ISO 3166-1 alpha-2
    [
        'name'         => 'U.S. Virgin Islands',
        'rusName'      => 'Виргинские о-ва (США)',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'VI',
        'iso3166_1_a3' => 'VIR',
        'iso3166_1_n'  => '850',
        'iso3166_2'    => 'ISO 3166-2:VI',
        'gost7_67'     => ['cyrillic' => 'ВИР', 'numeric' => '850'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Flag_of_the_United_States_Virgin_Islands.svg/22px-Flag_of_the_United_States_Virgin_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Flag_of_the_United_States_Virgin_Islands.svg/320px-Flag_of_the_United_States_Virgin_Islands.svg.png'
        ] 
    ],
    'VG' => // (10) ISO 3166-1 alpha-2
    [
        'name'         => 'British Virgin Islands',
        'rusName'      => 'Виргинские о-ва (Великобритания)',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'VG',
        'iso3166_1_a3' => 'VGB',
        'iso3166_1_n'  => '092',
        'iso3166_2'    => 'ISO 3166-2:VG',
        'gost7_67'     => ['cyrillic' => 'ВИБ', 'numeric' => '092'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/42/Flag_of_the_British_Virgin_Islands.svg/22px-Flag_of_the_British_Virgin_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/42/Flag_of_the_British_Virgin_Islands.svg/320px-Flag_of_the_British_Virgin_Islands.svg.png'
        ] 
    ],
    'VE' => // (11) ISO 3166-1 alpha-2
    [
        'name'         => 'Venezuela',
        'rusName'      => 'Венесуэла',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'VE',
        'iso3166_1_a3' => 'VEN',
        'iso3166_1_n'  => '862',
        'iso3166_2'    => 'ISO 3166-2:VE',
        'gost7_67'     => ['cyrillic' => 'ВЕС', 'numeric' => '862'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/7b/Flag_of_Venezuela_%28state%29.svg/22px-Flag_of_Venezuela_%28state%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/7b/Flag_of_Venezuela_%28state%29.svg/320px-Flag_of_Venezuela_%28state%29.svg.png'
        ] 
    ],
    'VC' => // (12) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Vincent & Grenadines',
        'rusName'      => 'Сент-Винсент и Гренадины',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'VC',
        'iso3166_1_a3' => 'VCT',
        'iso3166_1_n'  => '670',
        'iso3166_2'    => 'ISO 3166-2:VC',
        'gost7_67'     => ['cyrillic' => 'СЕР', 'numeric' => '670'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Flag_of_Saint_Vincent_and_the_Grenadines.svg/22px-Flag_of_Saint_Vincent_and_the_Grenadines.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Flag_of_Saint_Vincent_and_the_Grenadines.svg/320px-Flag_of_Saint_Vincent_and_the_Grenadines.svg.png'
        ] 
    ],
    'VA' => // (13) ISO 3166-1 alpha-2
    [
        'name'         => 'Vatican City',
        'rusName'      => 'Ватикан',
        'languages'    => [],
        'iso3166_1_a2' => 'VA',
        'iso3166_1_a3' => 'VAT',
        'iso3166_1_n'  => '336',
        'iso3166_2'    => 'ISO 3166-2:VA',
        'gost7_67'     => ['cyrillic' => 'ВАТ', 'numeric' => '336'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_the_Vatican_City.svg/20px-Flag_of_the_Vatican_City.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_the_Vatican_City.svg/20px-Flag_of_the_Vatican_City.svg.png'
        ] 
    ],
    'UZ' => // (14) ISO 3166-1 alpha-2
    [
        'name'         => 'Uzbekistan',
        'rusName'      => 'Узбекистан',
        'languages'    => ['uz'],
        'iso3166_1_a2' => 'UZ',
        'iso3166_1_a3' => 'UZB',
        'iso3166_1_n'  => '860',
        'iso3166_2'    => 'ISO 3166-2:UZ',
        'gost7_67'     => ['cyrillic' => 'УЗБ', 'numeric' => '860'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/84/Flag_of_Uzbekistan.svg/22px-Flag_of_Uzbekistan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/84/Flag_of_Uzbekistan.svg/320px-Flag_of_Uzbekistan.svg.png'
        ] 
    ],
    'UY' => // (15) ISO 3166-1 alpha-2
    [
        'name'         => 'Uruguay',
        'rusName'      => 'Уругвай',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'UY',
        'iso3166_1_a3' => 'URY',
        'iso3166_1_n'  => '858',
        'iso3166_2'    => 'ISO 3166-2:UY',
        'gost7_67'     => ['cyrillic' => 'УРУ', 'numeric' => '858'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Uruguay.svg/22px-Flag_of_Uruguay.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Uruguay.svg/320px-Flag_of_Uruguay.svg.png'
        ] 
    ],
    'US' => // (16) ISO 3166-1 alpha-2
    [
        'name'         => 'United States',
        'rusName'      => 'Соединенные Штаты',
        'languages'    => ['en', 'es'],
        'iso3166_1_a2' => 'US',
        'iso3166_1_a3' => 'USA',
        'iso3166_1_n'  => '840',
        'iso3166_2'    => 'ISO 3166-2:US',
        'gost7_67'     => ['cyrillic' => 'СОЕ', 'numeric' => '840'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/22px-Flag_of_the_United_States.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/320px-Flag_of_the_United_States.svg.png'
        ] 
    ],
    'UM' => // (17) ISO 3166-1 alpha-2
    [
        'name'         => 'U.S. Outlying Islands',
        'rusName'      => 'Внешние малые о-ва (США)',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'UM',
        'iso3166_1_a3' => 'UMI',
        'iso3166_1_n'  => '581',
        'iso3166_2'    => 'ISO 3166-2:UM',
        'gost7_67'     => ['cyrillic' => 'МЕЛ', 'numeric' => '581'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/22px-Flag_of_the_United_States.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/320px-Flag_of_the_United_States.svg.png'
        ] 
    ],
    'UG' => // (18) ISO 3166-1 alpha-2
    [
        'name'         => 'Uganda',
        'rusName'      => 'Уганда',
        'languages'    => ['en', 'lg', 'sw'],
        'iso3166_1_a2' => 'UG',
        'iso3166_1_a3' => 'UGA',
        'iso3166_1_n'  => '800',
        'iso3166_2'    => 'ISO 3166-2:UG',
        'gost7_67'     => ['cyrillic' => 'УГА', 'numeric' => '800'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Flag_of_Uganda.svg/22px-Flag_of_Uganda.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Flag_of_Uganda.svg/320px-Flag_of_Uganda.svg.png'
        ] 
    ],
    'UA' => // (19) ISO 3166-1 alpha-2
    [
        'name'         => 'Ukraine',
        'rusName'      => 'Украина',
        'languages'    => ['uk', 'ru'],
        'iso3166_1_a2' => 'UA',
        'iso3166_1_a3' => 'UKR',
        'iso3166_1_n'  => '804',
        'iso3166_2'    => 'ISO 3166-2:UA',
        'gost7_67'     => ['cyrillic' => 'УКР', 'numeric' => '804'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Ukraine.svg/22px-Flag_of_Ukraine.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Ukraine.svg/320px-Flag_of_Ukraine.svg.png'
        ] 
    ],
    'TZ' => // (20) ISO 3166-1 alpha-2
    [
        'name'         => 'Tanzania',
        'rusName'      => 'Танзания',
        'languages'    => ['en', 'sw'],
        'iso3166_1_a2' => 'TZ',
        'iso3166_1_a3' => 'TZA',
        'iso3166_1_n'  => '834',
        'iso3166_2'    => 'ISO 3166-2:TZ',
        'gost7_67'     => ['cyrillic' => 'ТАН', 'numeric' => '834'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Tanzania.svg/22px-Flag_of_Tanzania.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Tanzania.svg/320px-Flag_of_Tanzania.svg.png'
        ] 
    ],
    'TW' => // (21) ISO 3166-1 alpha-2
    [
        'name'         => 'Taiwan',
        'rusName'      => 'Тайвань',
        'languages'    => ['zh'],
        'iso3166_1_a2' => 'TW',
        'iso3166_1_a3' => 'TWN',
        'iso3166_1_n'  => '158',
        'iso3166_2'    => 'ISO 3166-2:TW',
        'gost7_67'     => ['cyrillic' => 'ТАЙ', 'numeric' => '158'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/72/Flag_of_the_Republic_of_China.svg/22px-Flag_of_the_Republic_of_China.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/72/Flag_of_the_Republic_of_China.svg/320px-Flag_of_the_Republic_of_China.svg.png'
        ] 
    ],
    'TV' => // (22) ISO 3166-1 alpha-2
    [
        'name'         => 'Tuvalu',
        'rusName'      => 'Тувалу',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'TV',
        'iso3166_1_a3' => 'TUV',
        'iso3166_1_n'  => '798',
        'iso3166_2'    => 'ISO 3166-2:TV',
        'gost7_67'     => ['cyrillic' => 'ТУВ', 'numeric' => '798'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Tuvalu.svg/22px-Flag_of_Tuvalu.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Tuvalu.svg/320px-Flag_of_Tuvalu.svg.png'
        ] 
    ],
    'TT' => // (23) ISO 3166-1 alpha-2
    [
        'name'         => 'Trinidad & Tobago',
        'rusName'      => 'Тринидад и Тобаго',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'TT',
        'iso3166_1_a3' => 'TTO',
        'iso3166_1_n'  => '780',
        'iso3166_2'    => 'ISO 3166-2:TT',
        'gost7_67'     => ['cyrillic' => 'ТРИ', 'numeric' => '780'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Flag_of_Trinidad_and_Tobago.svg/22px-Flag_of_Trinidad_and_Tobago.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Flag_of_Trinidad_and_Tobago.svg/320px-Flag_of_Trinidad_and_Tobago.svg.png'
        ] 
    ],
    'TR' => // (24) ISO 3166-1 alpha-2
    [
        'name'         => 'Turkey',
        'rusName'      => 'Турция',
        'languages'    => ['tr'],
        'iso3166_1_a2' => 'TR',
        'iso3166_1_a3' => 'TUR',
        'iso3166_1_n'  => '792',
        'iso3166_2'    => 'ISO 3166-2:TR',
        'gost7_67'     => ['cyrillic' => 'ТУЦ', 'numeric' => '792'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Turkey.svg/22px-Flag_of_Turkey.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Turkey.svg/320px-Flag_of_Turkey.svg.png'
        ] 
    ],
    'TO' => // (25) ISO 3166-1 alpha-2
    [
        'name'         => 'Tonga',
        'rusName'      => 'Тонга',
        'languages'    => ['en', 'to'],
        'iso3166_1_a2' => 'TO',
        'iso3166_1_a3' => 'TON',
        'iso3166_1_n'  => '776',
        'iso3166_2'    => 'ISO 3166-2:TO',
        'gost7_67'     => ['cyrillic' => 'ТОН', 'numeric' => '776'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Tonga.svg/22px-Flag_of_Tonga.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Tonga.svg/320px-Flag_of_Tonga.svg.png'
        ] 
    ],
    'TN' => // (26) ISO 3166-1 alpha-2
    [
        'name'         => 'Tunisia',
        'rusName'      => 'Тунис',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'TN',
        'iso3166_1_a3' => 'TUN',
        'iso3166_1_n'  => '788',
        'iso3166_2'    => 'ISO 3166-2:TN',
        'gost7_67'     => ['cyrillic' => 'ТУН', 'numeric' => '788'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Flag_of_Tunisia.svg/22px-Flag_of_Tunisia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Flag_of_Tunisia.svg/320px-Flag_of_Tunisia.svg.png'
        ] 
    ],
    'TM' => // (27) ISO 3166-1 alpha-2
    [
        'name'         => 'Turkmenistan',
        'rusName'      => 'Туркменистан',
        'languages'    => [],
        'iso3166_1_a2' => 'TM',
        'iso3166_1_a3' => 'TKM',
        'iso3166_1_n'  => '795',
        'iso3166_2'    => 'ISO 3166-2:TM',
        'gost7_67'     => ['cyrillic' => 'ТУР', 'numeric' => '795'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Flag_of_Turkmenistan.svg/22px-Flag_of_Turkmenistan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Flag_of_Turkmenistan.svg/320px-Flag_of_Turkmenistan.svg.png'
        ] 
    ],
    'TL' => // (28) ISO 3166-1 alpha-2
    [
        'name'         => 'Timor-Leste',
        'rusName'      => 'Восточный Тимор',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'TL',
        'iso3166_1_a3' => 'TLS',
        'iso3166_1_n'  => '626',
        'iso3166_2'    => 'ISO 3166-2:TL',
        'gost7_67'     => ['cyrillic' => 'ВОТ', 'numeric' => '626'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/26/Flag_of_East_Timor.svg/22px-Flag_of_East_Timor.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/26/Flag_of_East_Timor.svg/320px-Flag_of_East_Timor.svg.png'
        ] 
    ],
    'TK' => // (29) ISO 3166-1 alpha-2
    [
        'name'         => 'Tokelau',
        'rusName'      => 'Токелау',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'TK',
        'iso3166_1_a3' => 'TKL',
        'iso3166_1_n'  => '772',
        'iso3166_2'    => 'ISO 3166-2:TK',
        'gost7_67'     => ['cyrillic' => 'ТОК', 'numeric' => '772'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Flag_of_Tokelau.svg/22px-Flag_of_Tokelau.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Flag_of_Tokelau.svg/320px-Flag_of_Tokelau.svg.png'
        ] 
    ],
    'TJ' => // (30) ISO 3166-1 alpha-2
    [
        'name'         => 'Tajikistan',
        'rusName'      => 'Таджикистан',
        'languages'    => [],
        'iso3166_1_a2' => 'TJ',
        'iso3166_1_a3' => 'TJK',
        'iso3166_1_n'  => '762',
        'iso3166_2'    => 'ISO 3166-2:TJ',
        'gost7_67'     => ['cyrillic' => 'ТАД', 'numeric' => '762'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Tajikistan.svg/22px-Flag_of_Tajikistan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Tajikistan.svg/320px-Flag_of_Tajikistan.svg.png'
        ] 
    ],
    'TH' => // (31) ISO 3166-1 alpha-2
    [
        'name'         => 'Thailand',
        'rusName'      => 'Таиланд',
        'languages'    => ['th'],
        'iso3166_1_a2' => 'TH',
        'iso3166_1_a3' => 'THA',
        'iso3166_1_n'  => '764',
        'iso3166_2'    => 'ISO 3166-2:TH',
        'gost7_67'     => ['cyrillic' => 'ТАИ', 'numeric' => '764'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Flag_of_Thailand.svg/22px-Flag_of_Thailand.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Flag_of_Thailand.svg/320px-Flag_of_Thailand.svg.png'
        ] 
    ],
    'TG' => // (32) ISO 3166-1 alpha-2
    [
        'name'         => 'Togo',
        'rusName'      => 'Того',
        'languages'    => ['ee', 'fr'],
        'iso3166_1_a2' => 'TG',
        'iso3166_1_a3' => 'TGO',
        'iso3166_1_n'  => '768',
        'iso3166_2'    => 'ISO 3166-2:TG',
        'gost7_67'     => ['cyrillic' => 'ТОГ', 'numeric' => '768'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/68/Flag_of_Togo.svg/22px-Flag_of_Togo.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/68/Flag_of_Togo.svg/320px-Flag_of_Togo.svg.png'
        ] 
    ],
    'TF' => // (33) ISO 3166-1 alpha-2
    [
        'name'         => 'French Southern Territories',
        'rusName'      => 'Французские Южные территории',
        'languages'    => [],
        'iso3166_1_a2' => 'TF',
        'iso3166_1_a3' => 'ATF',
        'iso3166_1_n'  => '260',
        'iso3166_2'    => 'ISO 3166-2:TF',
        'gost7_67'     => ['cyrillic' => 'ФРЮ', 'numeric' => '260'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Flag_of_the_French_Southern_and_Antarctic_Lands.svg/22px-Flag_of_the_French_Southern_and_Antarctic_Lands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Flag_of_the_French_Southern_and_Antarctic_Lands.svg/320px-Flag_of_the_French_Southern_and_Antarctic_Lands.svg.png'
        ] 
    ],
    'TD' => // (34) ISO 3166-1 alpha-2
    [
        'name'         => 'Chad',
        'rusName'      => 'Чад',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'TD',
        'iso3166_1_a3' => 'TCD',
        'iso3166_1_n'  => '148',
        'iso3166_2'    => 'ISO 3166-2:TD',
        'gost7_67'     => ['cyrillic' => 'ЧАД', 'numeric' => '148'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Flag_of_Chad.svg/22px-Flag_of_Chad.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Flag_of_Chad.svg/320px-Flag_of_Chad.svg.png'
        ] 
    ],
    'TC' => // (35) ISO 3166-1 alpha-2
    [
        'name'         => 'Turks & Caicos Islands',
        'rusName'      => 'о-ва Тёркс и Кайкос',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'TC',
        'iso3166_1_a3' => 'TCA',
        'iso3166_1_n'  => '796',
        'iso3166_2'    => 'ISO 3166-2:TC',
        'gost7_67'     => ['cyrillic' => 'ТЁР', 'numeric' => '796'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flag_of_the_Turks_and_Caicos_Islands.svg/22px-Flag_of_the_Turks_and_Caicos_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flag_of_the_Turks_and_Caicos_Islands.svg/320px-Flag_of_the_Turks_and_Caicos_Islands.svg.png'
        ] 
    ],
    'SZ' => // (36) ISO 3166-1 alpha-2
    [
        'name'         => 'Eswatini',
        'rusName'      => 'Эсватини',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'SZ',
        'iso3166_1_a3' => 'SWZ',
        'iso3166_1_n'  => '748',
        'iso3166_2'    => 'ISO 3166-2:SZ',
        'gost7_67'     => ['cyrillic' => 'СВА', 'numeric' => '748'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fb/Flag_of_Eswatini.svg/22px-Flag_of_Eswatini.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fb/Flag_of_Eswatini.svg/320px-Flag_of_Eswatini.svg.png'
        ] 
    ],
    'SY' => // (37) ISO 3166-1 alpha-2
    [
        'name'         => 'Syria',
        'rusName'      => 'Сирия',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'SY',
        'iso3166_1_a3' => 'SYR',
        'iso3166_1_n'  => '760',
        'iso3166_2'    => 'ISO 3166-2:SY',
        'gost7_67'     => ['cyrillic' => 'СИР', 'numeric' => '760'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/53/Flag_of_Syria.svg/22px-Flag_of_Syria.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/53/Flag_of_Syria.svg/320px-Flag_of_Syria.svg.png'
        ] 
    ],
    'SX' => // (38) ISO 3166-1 alpha-2
    [
        'name'         => 'Sint Maarten',
        'rusName'      => 'Синт-Мартен',
        'languages'    => ['en', 'nl'],
        'iso3166_1_a2' => 'SX',
        'iso3166_1_a3' => 'SXM',
        'iso3166_1_n'  => '534',
        'iso3166_2'    => 'ISO 3166-2:SX',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '534'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Sint_Maarten.svg/22px-Flag_of_Sint_Maarten.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Sint_Maarten.svg/320px-Flag_of_Sint_Maarten.svg.png'
        ] 
    ],
    'SV' => // (39) ISO 3166-1 alpha-2
    [
        'name'         => 'El Salvador',
        'rusName'      => 'Сальвадор',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'SV',
        'iso3166_1_a3' => 'SLV',
        'iso3166_1_n'  => '222',
        'iso3166_2'    => 'ISO 3166-2:SV',
        'gost7_67'     => ['cyrillic' => 'САЛ', 'numeric' => '222'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/34/Flag_of_El_Salvador.svg/22px-Flag_of_El_Salvador.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/34/Flag_of_El_Salvador.svg/320px-Flag_of_El_Salvador.svg.png'
        ] 
    ],
    'ST' => // (40) ISO 3166-1 alpha-2
    [
        'name'         => 'São Tomé & Príncipe',
        'rusName'      => 'Сан-Томе и Принсипи',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'ST',
        'iso3166_1_a3' => 'STP',
        'iso3166_1_n'  => '678',
        'iso3166_2'    => 'ISO 3166-2:ST',
        'gost7_67'     => ['cyrillic' => 'САТ', 'numeric' => '678'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Flag_of_Sao_Tome_and_Principe.svg/22px-Flag_of_Sao_Tome_and_Principe.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Flag_of_Sao_Tome_and_Principe.svg/320px-Flag_of_Sao_Tome_and_Principe.svg.png'
        ] 
    ],
    'SS' => // (41) ISO 3166-1 alpha-2
    [
        'name'         => 'South Sudan',
        'rusName'      => 'Южный Судан',
        'languages'    => ['ar', 'en'],
        'iso3166_1_a2' => 'SS',
        'iso3166_1_a3' => 'SSD',
        'iso3166_1_n'  => '728',
        'iso3166_2'    => 'ISO 3166-2:SS',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '728'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Flag_of_South_Sudan.svg/22px-Flag_of_South_Sudan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Flag_of_South_Sudan.svg/320px-Flag_of_South_Sudan.svg.png'
        ] 
    ],
    'SR' => // (42) ISO 3166-1 alpha-2
    [
        'name'         => 'Suriname',
        'rusName'      => 'Суринам',
        'languages'    => ['nl'],
        'iso3166_1_a2' => 'SR',
        'iso3166_1_a3' => 'SUR',
        'iso3166_1_n'  => '740',
        'iso3166_2'    => 'ISO 3166-2:SR',
        'gost7_67'     => ['cyrillic' => 'СУР', 'numeric' => '740'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/60/Flag_of_Suriname.svg/22px-Flag_of_Suriname.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/60/Flag_of_Suriname.svg/320px-Flag_of_Suriname.svg.png'
        ] 
    ],
    'SO' => // (43) ISO 3166-1 alpha-2
    [
        'name'         => 'Somalia',
        'rusName'      => 'Сомали',
        'languages'    => ['ar', 'so'],
        'iso3166_1_a2' => 'SO',
        'iso3166_1_a3' => 'SOM',
        'iso3166_1_n'  => '706',
        'iso3166_2'    => 'ISO 3166-2:SO',
        'gost7_67'     => ['cyrillic' => 'СОМ', 'numeric' => '706'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flag_of_Somalia.svg/22px-Flag_of_Somalia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Flag_of_Somalia.svg/320px-Flag_of_Somalia.svg.png'
        ] 
    ],
    'SN' => // (44) ISO 3166-1 alpha-2
    [
        'name'         => 'Senegal',
        'rusName'      => 'Сенегал',
        'languages'    => ['fr', 'ff'],
        'iso3166_1_a2' => 'SN',
        'iso3166_1_a3' => 'SEN',
        'iso3166_1_n'  => '686',
        'iso3166_2'    => 'ISO 3166-2:SN',
        'gost7_67'     => ['cyrillic' => 'СЕН', 'numeric' => '686'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fd/Flag_of_Senegal.svg/22px-Flag_of_Senegal.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fd/Flag_of_Senegal.svg/320px-Flag_of_Senegal.svg.png'
        ] 
    ],
    'SM' => // (45) ISO 3166-1 alpha-2
    [
        'name'         => 'San Marino',
        'rusName'      => 'Сан-Марино',
        'languages'    => ['it'],
        'iso3166_1_a2' => 'SM',
        'iso3166_1_a3' => 'SMR',
        'iso3166_1_n'  => '674',
        'iso3166_2'    => 'ISO 3166-2:SM',
        'gost7_67'     => ['cyrillic' => 'САН', 'numeric' => '674'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Flag_of_San_Marino.svg/22px-Flag_of_San_Marino.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Flag_of_San_Marino.svg/320px-Flag_of_San_Marino.svg.png'
        ] 
    ],
    'SL' => // (46) ISO 3166-1 alpha-2
    [
        'name'         => 'Sierra Leone',
        'rusName'      => 'Сьерра-Леоне',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'SL',
        'iso3166_1_a3' => 'SLE',
        'iso3166_1_n'  => '694',
        'iso3166_2'    => 'ISO 3166-2:SL',
        'gost7_67'     => ['cyrillic' => 'СЬЕ', 'numeric' => '694'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/17/Flag_of_Sierra_Leone.svg/22px-Flag_of_Sierra_Leone.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/17/Flag_of_Sierra_Leone.svg/320px-Flag_of_Sierra_Leone.svg.png'
        ] 
    ],
    'SK' => // (47) ISO 3166-1 alpha-2
    [
        'name'         => 'Slovakia',
        'rusName'      => 'Словакия',
        'languages'    => ['sk'],
        'iso3166_1_a2' => 'SK',
        'iso3166_1_a3' => 'SVK',
        'iso3166_1_n'  => '703',
        'iso3166_2'    => 'ISO 3166-2:SK',
        'gost7_67'     => ['cyrillic' => 'СЛА', 'numeric' => '703'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Flag_of_Slovakia.svg/22px-Flag_of_Slovakia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Flag_of_Slovakia.svg/320px-Flag_of_Slovakia.svg.png'
        ] 
    ],
    'SJ' => // (48) ISO 3166-1 alpha-2
    [
        'name'         => 'Svalbard & Jan Mayen',
        'rusName'      => 'Шпицберген и Ян-Майен',
        'languages'    => ['nb'],
        'iso3166_1_a2' => 'SJ',
        'iso3166_1_a3' => 'SJM',
        'iso3166_1_n'  => '744',
        'iso3166_2'    => 'ISO 3166-2:SJ',
        'gost7_67'     => ['cyrillic' => 'СВБ', 'numeric' => '744'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/22px-Flag_of_Norway.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/320px-Flag_of_Norway.svg.png'
        ] 
    ],
    'SI' => // (49) ISO 3166-1 alpha-2
    [
        'name'         => 'Slovenia',
        'rusName'      => 'Словения',
        'languages'    => ['sl'],
        'iso3166_1_a2' => 'SI',
        'iso3166_1_a3' => 'SVN',
        'iso3166_1_n'  => '705',
        'iso3166_2'    => 'ISO 3166-2:SI',
        'gost7_67'     => ['cyrillic' => 'СЛО', 'numeric' => '705'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Flag_of_Slovenia.svg/22px-Flag_of_Slovenia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Flag_of_Slovenia.svg/320px-Flag_of_Slovenia.svg.png'
        ] 
    ],
    'SH' => // (50) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Helena',
        'rusName'      => 'о-в Св. Елены',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'SH',
        'iso3166_1_a3' => 'SHN',
        'iso3166_1_n'  => '654',
        'iso3166_2'    => 'ISO 3166-2:SH',
        'gost7_67'     => ['cyrillic' => 'СВЯ', 'numeric' => '654'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Saint_Helena.svg/22px-Flag_of_Saint_Helena.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Saint_Helena.svg/320px-Flag_of_Saint_Helena.svg.png'
        ] 
    ],
    'SG' => // (51) ISO 3166-1 alpha-2
    [
        'name'         => 'Singapore',
        'rusName'      => 'Сингапур',
        'languages'    => ['en', 'ms', 'zh', 'ta'],
        'iso3166_1_a2' => 'SG',
        'iso3166_1_a3' => 'SGP',
        'iso3166_1_n'  => '702',
        'iso3166_2'    => 'ISO 3166-2:SG',
        'gost7_67'     => ['cyrillic' => 'СИН', 'numeric' => '702'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Singapore.svg/22px-Flag_of_Singapore.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Singapore.svg/320px-Flag_of_Singapore.svg.png'
        ] 
    ],
    'SE' => // (52) ISO 3166-1 alpha-2
    [
        'name'         => 'Sweden',
        'rusName'      => 'Швеция',
        'languages'    => ['se', 'sv'],
        'iso3166_1_a2' => 'SE',
        'iso3166_1_a3' => 'SWE',
        'iso3166_1_n'  => '752',
        'iso3166_2'    => 'ISO 3166-2:SE',
        'gost7_67'     => ['cyrillic' => 'ШВЕ', 'numeric' => '752'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Flag_of_Sweden.svg/22px-Flag_of_Sweden.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Flag_of_Sweden.svg/320px-Flag_of_Sweden.svg.png'
        ] 
    ],
    'SD' => // (53) ISO 3166-1 alpha-2
    [
        'name'         => 'Sudan',
        'rusName'      => 'Судан',
        'languages'    => ['ar', 'en'],
        'iso3166_1_a2' => 'SD',
        'iso3166_1_a3' => 'SDN',
        'iso3166_1_n'  => '729',
        'iso3166_2'    => 'ISO 3166-2:SD',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '729'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Sudan.svg/22px-Flag_of_Sudan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Sudan.svg/320px-Flag_of_Sudan.svg.png'
        ] 
    ],
    'SC' => // (54) ISO 3166-1 alpha-2
    [
        'name'         => 'Seychelles',
        'rusName'      => 'Сейшельские Острова',
        'languages'    => ['en', 'fr'],
        'iso3166_1_a2' => 'SC',
        'iso3166_1_a3' => 'SYC',
        'iso3166_1_n'  => '690',
        'iso3166_2'    => 'ISO 3166-2:SC',
        'gost7_67'     => ['cyrillic' => 'СЕЙ', 'numeric' => '690'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Flag_of_Seychelles.svg/22px-Flag_of_Seychelles.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Flag_of_Seychelles.svg/320px-Flag_of_Seychelles.svg.png'
        ] 
    ],
    'SB' => // (55) ISO 3166-1 alpha-2
    [
        'name'         => 'Solomon Islands',
        'rusName'      => 'Соломоновы Острова',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'SB',
        'iso3166_1_a3' => 'SLB',
        'iso3166_1_n'  => '090',
        'iso3166_2'    => 'ISO 3166-2:SB',
        'gost7_67'     => ['cyrillic' => 'СОЛ', 'numeric' => '090'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_the_Solomon_Islands.svg/22px-Flag_of_the_Solomon_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_the_Solomon_Islands.svg/320px-Flag_of_the_Solomon_Islands.svg.png'
        ] 
    ],
    'SA' => // (56) ISO 3166-1 alpha-2
    [
        'name'         => 'Saudi Arabia',
        'rusName'      => 'Саудовская Аравия',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'SA',
        'iso3166_1_a3' => 'SAU',
        'iso3166_1_n'  => '682',
        'iso3166_2'    => 'ISO 3166-2:SA',
        'gost7_67'     => ['cyrillic' => 'САУ', 'numeric' => '682'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Flag_of_Saudi_Arabia.svg/22px-Flag_of_Saudi_Arabia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Flag_of_Saudi_Arabia.svg/320px-Flag_of_Saudi_Arabia.svg.png'
        ] 
    ],
    'RW' => // (57) ISO 3166-1 alpha-2
    [
        'name'         => 'Rwanda',
        'rusName'      => 'Руанда',
        'languages'    => ['en', 'fr', 'rw'],
        'iso3166_1_a2' => 'RW',
        'iso3166_1_a3' => 'RWA',
        'iso3166_1_n'  => '646',
        'iso3166_2'    => 'ISO 3166-2:RW',
        'gost7_67'     => ['cyrillic' => 'РУА', 'numeric' => '646'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/17/Flag_of_Rwanda.svg/22px-Flag_of_Rwanda.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/17/Flag_of_Rwanda.svg/320px-Flag_of_Rwanda.svg.png'
        ] 
    ],
    'RU' => // (58) ISO 3166-1 alpha-2
    [
        'name'         => 'Russia',
        'rusName'      => 'Россия',
        'languages'    => ['os', 'ru'],
        'iso3166_1_a2' => 'RU',
        'iso3166_1_a3' => 'RUS',
        'iso3166_1_n'  => '643',
        'iso3166_2'    => 'ISO 3166-2:RU',
        'gost7_67'     => ['cyrillic' => 'РОФ', 'numeric' => '643'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f3/Flag_of_Russia.svg/22px-Flag_of_Russia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f3/Flag_of_Russia.svg/320px-Flag_of_Russia.svg.png'
        ] 
    ],
    'RS' => // (59) ISO 3166-1 alpha-2
    [
        'name'         => 'Serbia',
        'rusName'      => 'Сербия',
        'languages'    => ['sr'],
        'iso3166_1_a2' => 'RS',
        'iso3166_1_a3' => 'SRB',
        'iso3166_1_n'  => '688',
        'iso3166_2'    => 'ISO 3166-2:RS',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '688'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Flag_of_Serbia.svg/22px-Flag_of_Serbia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Flag_of_Serbia.svg/320px-Flag_of_Serbia.svg.png'
        ] 
    ],
    'RO' => // (60) ISO 3166-1 alpha-2
    [
        'name'         => 'Romania',
        'rusName'      => 'Румыния',
        'languages'    => ['ro'],
        'iso3166_1_a2' => 'RO',
        'iso3166_1_a3' => 'ROU',
        'iso3166_1_n'  => '642',
        'iso3166_2'    => 'ISO 3166-2:RO',
        'gost7_67'     => ['cyrillic' => 'РУМ', 'numeric' => '642'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/73/Flag_of_Romania.svg/22px-Flag_of_Romania.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/73/Flag_of_Romania.svg/320px-Flag_of_Romania.svg.png'
        ] 
    ],
    'RE' => // (61) ISO 3166-1 alpha-2
    [
        'name'         => 'Réunion',
        'rusName'      => 'Реюньон',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'RE',
        'iso3166_1_a3' => 'REU',
        'iso3166_1_n'  => '638',
        'iso3166_2'    => 'ISO 3166-2:RE',
        'gost7_67'     => ['cyrillic' => 'РЕЮ', 'numeric' => '638'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Flag_of_R%C3%A9union.svg/22px-Flag_of_R%C3%A9union.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Flag_of_R%C3%A9union.svg/320px-Flag_of_R%C3%A9union.svg.png'
        ] 
    ],
    'QA' => // (62) ISO 3166-1 alpha-2
    [
        'name'         => 'Qatar',
        'rusName'      => 'Катар',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'QA',
        'iso3166_1_a3' => 'QAT',
        'iso3166_1_n'  => '634',
        'iso3166_2'    => 'ISO 3166-2:QA',
        'gost7_67'     => ['cyrillic' => 'КАТ', 'numeric' => '634'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/65/Flag_of_Qatar.svg/22px-Flag_of_Qatar.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/65/Flag_of_Qatar.svg/320px-Flag_of_Qatar.svg.png'
        ] 
    ],
    'PY' => // (63) ISO 3166-1 alpha-2
    [
        'name'         => 'Paraguay',
        'rusName'      => 'Парагвай',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'PY',
        'iso3166_1_a3' => 'PRY',
        'iso3166_1_n'  => '600',
        'iso3166_2'    => 'ISO 3166-2:PY',
        'gost7_67'     => ['cyrillic' => 'ПАР', 'numeric' => '600'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/27/Flag_of_Paraguay.svg/22px-Flag_of_Paraguay.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/27/Flag_of_Paraguay.svg/320px-Flag_of_Paraguay.svg.png'
        ] 
    ],
    'PW' => // (64) ISO 3166-1 alpha-2
    [
        'name'         => 'Palau',
        'rusName'      => 'Палау',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'PW',
        'iso3166_1_a3' => 'PLW',
        'iso3166_1_n'  => '585',
        'iso3166_2'    => 'ISO 3166-2:PW',
        'gost7_67'     => ['cyrillic' => 'ПАЛ', 'numeric' => '585'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Palau.svg/22px-Flag_of_Palau.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Palau.svg/320px-Flag_of_Palau.svg.png'
        ] 
    ],
    'PT' => // (65) ISO 3166-1 alpha-2
    [
        'name'         => 'Portugal',
        'rusName'      => 'Португалия',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'PT',
        'iso3166_1_a3' => 'PRT',
        'iso3166_1_n'  => '620',
        'iso3166_2'    => 'ISO 3166-2:PT',
        'gost7_67'     => ['cyrillic' => 'ПОР', 'numeric' => '620'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Portugal.svg/22px-Flag_of_Portugal.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Portugal.svg/320px-Flag_of_Portugal.svg.png'
        ] 
    ],
    'PS' => // (66) ISO 3166-1 alpha-2
    [
        'name'         => 'Palestinian Territories',
        'rusName'      => 'Палестинские территории',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'PS',
        'iso3166_1_a3' => 'PSE',
        'iso3166_1_n'  => '275',
        'iso3166_2'    => 'ISO 3166-2:PS',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '275'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Palestine.svg/22px-Flag_of_Palestine.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Palestine.svg/320px-Flag_of_Palestine.svg.png'
        ] 
    ],
    'PR' => // (67) ISO 3166-1 alpha-2
    [
        'name'         => 'Puerto Rico',
        'rusName'      => 'Пуэрто-Рико',
        'languages'    => ['en', 'es'],
        'iso3166_1_a2' => 'PR',
        'iso3166_1_a3' => 'PRI',
        'iso3166_1_n'  => '630',
        'iso3166_2'    => 'ISO 3166-2:PR',
        'gost7_67'     => ['cyrillic' => 'ПУЭ', 'numeric' => '630'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/28/Flag_of_Puerto_Rico.svg/22px-Flag_of_Puerto_Rico.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/28/Flag_of_Puerto_Rico.svg/320px-Flag_of_Puerto_Rico.svg.png'
        ] 
    ],
    'PN' => // (68) ISO 3166-1 alpha-2
    [
        'name'         => 'Pitcairn Islands',
        'rusName'      => 'о-ва Питкэрн',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'PN',
        'iso3166_1_a3' => 'PCN',
        'iso3166_1_n'  => '612',
        'iso3166_2'    => 'ISO 3166-2:PN',
        'gost7_67'     => ['cyrillic' => 'ПИТ', 'numeric' => '612'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/88/Flag_of_the_Pitcairn_Islands.svg/22px-Flag_of_the_Pitcairn_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/88/Flag_of_the_Pitcairn_Islands.svg/320px-Flag_of_the_Pitcairn_Islands.svg.png'
        ] 
    ],
    'PM' => // (69) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Pierre & Miquelon',
        'rusName'      => 'Сен-Пьер и Микелон',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'PM',
        'iso3166_1_a3' => 'SPM',
        'iso3166_1_n'  => '666',
        'iso3166_2'    => 'ISO 3166-2:PM',
        'gost7_67'     => ['cyrillic' => 'СЕП', 'numeric' => '666'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_Saint-Pierre_and_Miquelon.svg/22px-Flag_of_Saint-Pierre_and_Miquelon.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_Saint-Pierre_and_Miquelon.svg/320px-Flag_of_Saint-Pierre_and_Miquelon.svg.png'
        ] 
    ],
    'PL' => // (70) ISO 3166-1 alpha-2
    [
        'name'         => 'Poland',
        'rusName'      => 'Польша',
        'languages'    => ['pl'],
        'iso3166_1_a2' => 'PL',
        'iso3166_1_a3' => 'POL',
        'iso3166_1_n'  => '616',
        'iso3166_2'    => 'ISO 3166-2:PL',
        'gost7_67'     => ['cyrillic' => 'ПОЛ', 'numeric' => '616'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/12/Flag_of_Poland.svg/22px-Flag_of_Poland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/12/Flag_of_Poland.svg/320px-Flag_of_Poland.svg.png'
        ] 
    ],
    'PK' => // (71) ISO 3166-1 alpha-2
    [
        'name'         => 'Pakistan',
        'rusName'      => 'Пакистан',
        'languages'    => ['en', 'ur', 'pa'],
        'iso3166_1_a2' => 'PK',
        'iso3166_1_a3' => 'PAK',
        'iso3166_1_n'  => '586',
        'iso3166_2'    => 'ISO 3166-2:PK',
        'gost7_67'     => ['cyrillic' => 'ПАК', 'numeric' => '586'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/32/Flag_of_Pakistan.svg/22px-Flag_of_Pakistan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/32/Flag_of_Pakistan.svg/320px-Flag_of_Pakistan.svg.png'
        ] 
    ],
    'PH' => // (72) ISO 3166-1 alpha-2
    [
        'name'         => 'Philippines',
        'rusName'      => 'Филиппины',
        'languages'    => ['en', 'es', 'tl'],
        'iso3166_1_a2' => 'PH',
        'iso3166_1_a3' => 'PHL',
        'iso3166_1_n'  => '608',
        'iso3166_2'    => 'ISO 3166-2:PH',
        'gost7_67'     => ['cyrillic' => 'ФИЛ', 'numeric' => '608'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/99/Flag_of_the_Philippines.svg/22px-Flag_of_the_Philippines.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/99/Flag_of_the_Philippines.svg/320px-Flag_of_the_Philippines.svg.png'
        ] 
    ],
    'PG' => // (73) ISO 3166-1 alpha-2
    [
        'name'         => 'Papua New Guinea',
        'rusName'      => 'Папуа — Новая Гвинея',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'PG',
        'iso3166_1_a3' => 'PNG',
        'iso3166_1_n'  => '598',
        'iso3166_2'    => 'ISO 3166-2:PG',
        'gost7_67'     => ['cyrillic' => 'ПАП', 'numeric' => '598'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Flag_of_Papua_New_Guinea.svg/22px-Flag_of_Papua_New_Guinea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Flag_of_Papua_New_Guinea.svg/320px-Flag_of_Papua_New_Guinea.svg.png'
        ] 
    ],
    'PF' => // (74) ISO 3166-1 alpha-2
    [
        'name'         => 'French Polynesia',
        'rusName'      => 'Французская Полинезия',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'PF',
        'iso3166_1_a3' => 'PYF',
        'iso3166_1_n'  => '258',
        'iso3166_2'    => 'ISO 3166-2:PF',
        'gost7_67'     => ['cyrillic' => 'ФРП', 'numeric' => '258'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/db/Flag_of_French_Polynesia.svg/22px-Flag_of_French_Polynesia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/db/Flag_of_French_Polynesia.svg/320px-Flag_of_French_Polynesia.svg.png'
        ] 
    ],
    'PE' => // (75) ISO 3166-1 alpha-2
    [
        'name'         => 'Peru',
        'rusName'      => 'Перу',
        'languages'    => ['qu', 'es'],
        'iso3166_1_a2' => 'PE',
        'iso3166_1_a3' => 'PER',
        'iso3166_1_n'  => '604',
        'iso3166_2'    => 'ISO 3166-2:PE',
        'gost7_67'     => ['cyrillic' => 'ПЕР', 'numeric' => '604'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/df/Flag_of_Peru_%28state%29.svg/22px-Flag_of_Peru_%28state%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/df/Flag_of_Peru_%28state%29.svg/320px-Flag_of_Peru_%28state%29.svg.png'
        ] 
    ],
    'PA' => // (76) ISO 3166-1 alpha-2
    [
        'name'         => 'Panama',
        'rusName'      => 'Панама',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'PA',
        'iso3166_1_a3' => 'PAN',
        'iso3166_1_n'  => '591',
        'iso3166_2'    => 'ISO 3166-2:PA',
        'gost7_67'     => ['cyrillic' => 'ПАН', 'numeric' => '591'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Flag_of_Panama.svg/22px-Flag_of_Panama.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Flag_of_Panama.svg/320px-Flag_of_Panama.svg.png'
        ] 
    ],
    'OM' => // (77) ISO 3166-1 alpha-2
    [
        'name'         => 'Oman',
        'rusName'      => 'Оман',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'OM',
        'iso3166_1_a3' => 'OMN',
        'iso3166_1_n'  => '512',
        'iso3166_2'    => 'ISO 3166-2:OM',
        'gost7_67'     => ['cyrillic' => 'ОМА', 'numeric' => '512'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Oman.svg/22px-Flag_of_Oman.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Oman.svg/320px-Flag_of_Oman.svg.png'
        ] 
    ],
    'NZ' => // (78) ISO 3166-1 alpha-2
    [
        'name'         => 'New Zealand',
        'rusName'      => 'Новая Зеландия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'NZ',
        'iso3166_1_a3' => 'NZL',
        'iso3166_1_n'  => '554',
        'iso3166_2'    => 'ISO 3166-2:NZ',
        'gost7_67'     => ['cyrillic' => 'НОЗ', 'numeric' => '554'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Flag_of_New_Zealand.svg/22px-Flag_of_New_Zealand.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Flag_of_New_Zealand.svg/320px-Flag_of_New_Zealand.svg.png'
        ] 
    ],
    'NU' => // (79) ISO 3166-1 alpha-2
    [
        'name'         => 'Niue',
        'rusName'      => 'Ниуэ',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'NU',
        'iso3166_1_a3' => 'NIU',
        'iso3166_1_n'  => '570',
        'iso3166_2'    => 'ISO 3166-2:NU',
        'gost7_67'     => ['cyrillic' => 'НИУ', 'numeric' => '570'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Niue.svg/22px-Flag_of_Niue.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Niue.svg/320px-Flag_of_Niue.svg.png'
        ] 
    ],
    'NR' => // (80) ISO 3166-1 alpha-2
    [
        'name'         => 'Nauru',
        'rusName'      => 'Науру',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'NR',
        'iso3166_1_a3' => 'NRU',
        'iso3166_1_n'  => '520',
        'iso3166_2'    => 'ISO 3166-2:NR',
        'gost7_67'     => ['cyrillic' => 'НАУ', 'numeric' => '520'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/30/Flag_of_Nauru.svg/22px-Flag_of_Nauru.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/30/Flag_of_Nauru.svg/320px-Flag_of_Nauru.svg.png'
        ] 
    ],
    'NP' => // (81) ISO 3166-1 alpha-2
    [
        'name'         => 'Nepal',
        'rusName'      => 'Непал',
        'languages'    => ['ne'],
        'iso3166_1_a2' => 'NP',
        'iso3166_1_a3' => 'NPL',
        'iso3166_1_n'  => '524',
        'iso3166_2'    => 'ISO 3166-2:NP',
        'gost7_67'     => ['cyrillic' => 'НЕП', 'numeric' => '524'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9b/Flag_of_Nepal.svg/16px-Flag_of_Nepal.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9b/Flag_of_Nepal.svg/16px-Flag_of_Nepal.svg.png'
        ] 
    ],
    'NO' => // (82) ISO 3166-1 alpha-2
    [
        'name'         => 'Norway',
        'rusName'      => 'Норвегия',
        'languages'    => ['se', 'nb', 'nn', 'no'],
        'iso3166_1_a2' => 'NO',
        'iso3166_1_a3' => 'NOR',
        'iso3166_1_n'  => '578',
        'iso3166_2'    => 'ISO 3166-2:NO',
        'gost7_67'     => ['cyrillic' => 'НОР', 'numeric' => '578'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/22px-Flag_of_Norway.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/320px-Flag_of_Norway.svg.png'
        ] 
    ],
    'NL' => // (83) ISO 3166-1 alpha-2
    [
        'name'         => 'Netherlands',
        'rusName'      => 'Нидерланды',
        'languages'    => ['nl', 'fy'],
        'iso3166_1_a2' => 'NL',
        'iso3166_1_a3' => 'NLD',
        'iso3166_1_n'  => '528',
        'iso3166_2'    => 'ISO 3166-2:NL',
        'gost7_67'     => ['cyrillic' => 'НИД', 'numeric' => '528'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/20/Flag_of_the_Netherlands.svg/22px-Flag_of_the_Netherlands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/20/Flag_of_the_Netherlands.svg/320px-Flag_of_the_Netherlands.svg.png'
        ] 
    ],
    'NI' => // (84) ISO 3166-1 alpha-2
    [
        'name'         => 'Nicaragua',
        'rusName'      => 'Никарагуа',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'NI',
        'iso3166_1_a3' => 'NIC',
        'iso3166_1_n'  => '558',
        'iso3166_2'    => 'ISO 3166-2:NI',
        'gost7_67'     => ['cyrillic' => 'НИК', 'numeric' => '558'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Nicaragua.svg/22px-Flag_of_Nicaragua.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Nicaragua.svg/320px-Flag_of_Nicaragua.svg.png'
        ] 
    ],
    'NG' => // (85) ISO 3166-1 alpha-2
    [
        'name'         => 'Nigeria',
        'rusName'      => 'Нигерия',
        'languages'    => ['en', 'ha', 'ig', 'yo'],
        'iso3166_1_a2' => 'NG',
        'iso3166_1_a3' => 'NGA',
        'iso3166_1_n'  => '566',
        'iso3166_2'    => 'ISO 3166-2:NG',
        'gost7_67'     => ['cyrillic' => 'НИГ', 'numeric' => '566'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_Nigeria.svg/22px-Flag_of_Nigeria.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_Nigeria.svg/320px-Flag_of_Nigeria.svg.png'
        ] 
    ],
    'NF' => // (86) ISO 3166-1 alpha-2
    [
        'name'         => 'Norfolk Island',
        'rusName'      => 'о-в Норфолк',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'NF',
        'iso3166_1_a3' => 'NFK',
        'iso3166_1_n'  => '574',
        'iso3166_2'    => 'ISO 3166-2:NF',
        'gost7_67'     => ['cyrillic' => 'НОФ', 'numeric' => '574'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Norfolk_Island.svg/22px-Flag_of_Norfolk_Island.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/48/Flag_of_Norfolk_Island.svg/320px-Flag_of_Norfolk_Island.svg.png'
        ] 
    ],
    'NE' => // (87) ISO 3166-1 alpha-2
    [
        'name'         => 'Niger',
        'rusName'      => 'Нигер',
        'languages'    => ['fr', 'ha'],
        'iso3166_1_a2' => 'NE',
        'iso3166_1_a3' => 'NER',
        'iso3166_1_n'  => '562',
        'iso3166_2'    => 'ISO 3166-2:NE',
        'gost7_67'     => ['cyrillic' => 'НИА', 'numeric' => '562'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f4/Flag_of_Niger.svg/22px-Flag_of_Niger.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f4/Flag_of_Niger.svg/320px-Flag_of_Niger.svg.png'
        ] 
    ],
    'NC' => // (88) ISO 3166-1 alpha-2
    [
        'name'         => 'New Caledonia',
        'rusName'      => 'Новая Каледония',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'NC',
        'iso3166_1_a3' => 'NCL',
        'iso3166_1_n'  => '540',
        'iso3166_2'    => 'ISO 3166-2:NC',
        'gost7_67'     => ['cyrillic' => 'НОК', 'numeric' => '540'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/66/Flag_of_FLNKS.svg/22px-Flag_of_FLNKS.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/66/Flag_of_FLNKS.svg/320px-Flag_of_FLNKS.svg.png'
        ] 
    ],
    'NA' => // (89) ISO 3166-1 alpha-2
    [
        'name'         => 'Namibia',
        'rusName'      => 'Намибия',
        'languages'    => ['af', 'en'],
        'iso3166_1_a2' => 'NA',
        'iso3166_1_a3' => 'NAM',
        'iso3166_1_n'  => '516',
        'iso3166_2'    => 'ISO 3166-2:NA',
        'gost7_67'     => ['cyrillic' => 'НАМ', 'numeric' => '516'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Namibia.svg/22px-Flag_of_Namibia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/00/Flag_of_Namibia.svg/320px-Flag_of_Namibia.svg.png'
        ] 
    ],
    'MZ' => // (90) ISO 3166-1 alpha-2
    [
        'name'         => 'Mozambique',
        'rusName'      => 'Мозамбик',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'MZ',
        'iso3166_1_a3' => 'MOZ',
        'iso3166_1_n'  => '508',
        'iso3166_2'    => 'ISO 3166-2:MZ',
        'gost7_67'     => ['cyrillic' => 'МОЗ', 'numeric' => '508'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Mozambique.svg/22px-Flag_of_Mozambique.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Mozambique.svg/320px-Flag_of_Mozambique.svg.png'
        ] 
    ],
    'MY' => // (91) ISO 3166-1 alpha-2
    [
        'name'         => 'Malaysia',
        'rusName'      => 'Малайзия',
        'languages'    => ['en', 'ms', 'ta'],
        'iso3166_1_a2' => 'MY',
        'iso3166_1_a3' => 'MYS',
        'iso3166_1_n'  => '458',
        'iso3166_2'    => 'ISO 3166-2:MY',
        'gost7_67'     => ['cyrillic' => 'МАЗ', 'numeric' => '458'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/66/Flag_of_Malaysia.svg/22px-Flag_of_Malaysia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/66/Flag_of_Malaysia.svg/320px-Flag_of_Malaysia.svg.png'
        ] 
    ],
    'MX' => // (92) ISO 3166-1 alpha-2
    [
        'name'         => 'Mexico',
        'rusName'      => 'Мексика',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'MX',
        'iso3166_1_a3' => 'MEX',
        'iso3166_1_n'  => '484',
        'iso3166_2'    => 'ISO 3166-2:MX',
        'gost7_67'     => ['cyrillic' => 'МЕК', 'numeric' => '484'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Flag_of_Mexico.svg/22px-Flag_of_Mexico.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fc/Flag_of_Mexico.svg/320px-Flag_of_Mexico.svg.png'
        ] 
    ],
    'MW' => // (93) ISO 3166-1 alpha-2
    [
        'name'         => 'Malawi',
        'rusName'      => 'Малави',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'MW',
        'iso3166_1_a3' => 'MWI',
        'iso3166_1_n'  => '454',
        'iso3166_2'    => 'ISO 3166-2:MW',
        'gost7_67'     => ['cyrillic' => 'МАЕ', 'numeric' => '454'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Flag_of_Malawi.svg/22px-Flag_of_Malawi.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Flag_of_Malawi.svg/320px-Flag_of_Malawi.svg.png'
        ] 
    ],
    'MV' => // (94) ISO 3166-1 alpha-2
    [
        'name'         => 'Maldives',
        'rusName'      => 'Мальдивы',
        'languages'    => [],
        'iso3166_1_a2' => 'MV',
        'iso3166_1_a3' => 'MDV',
        'iso3166_1_n'  => '462',
        'iso3166_2'    => 'ISO 3166-2:MV',
        'gost7_67'     => ['cyrillic' => 'МАЛ', 'numeric' => '462'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_Maldives.svg/22px-Flag_of_Maldives.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_Maldives.svg/320px-Flag_of_Maldives.svg.png'
        ] 
    ],
    'MU' => // (95) ISO 3166-1 alpha-2
    [
        'name'         => 'Mauritius',
        'rusName'      => 'Маврикий',
        'languages'    => ['en', 'fr'],
        'iso3166_1_a2' => 'MU',
        'iso3166_1_a3' => 'MUS',
        'iso3166_1_n'  => '480',
        'iso3166_2'    => 'ISO 3166-2:MU',
        'gost7_67'     => ['cyrillic' => 'МАБ', 'numeric' => '480'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_Mauritius.svg/22px-Flag_of_Mauritius.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_Mauritius.svg/320px-Flag_of_Mauritius.svg.png'
        ] 
    ],
    'MT' => // (96) ISO 3166-1 alpha-2
    [
        'name'         => 'Malta',
        'rusName'      => 'Мальта',
        'languages'    => ['en', 'mt'],
        'iso3166_1_a2' => 'MT',
        'iso3166_1_a3' => 'MLT',
        'iso3166_1_n'  => '470',
        'iso3166_2'    => 'ISO 3166-2:MT',
        'gost7_67'     => ['cyrillic' => 'МАМ', 'numeric' => '470'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/73/Flag_of_Malta.svg/22px-Flag_of_Malta.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/73/Flag_of_Malta.svg/320px-Flag_of_Malta.svg.png'
        ] 
    ],
    'MS' => // (97) ISO 3166-1 alpha-2
    [
        'name'         => 'Montserrat',
        'rusName'      => 'Монтсеррат',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'MS',
        'iso3166_1_a3' => 'MSR',
        'iso3166_1_n'  => '500',
        'iso3166_2'    => 'ISO 3166-2:MS',
        'gost7_67'     => ['cyrillic' => 'МОТ', 'numeric' => '500'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Montserrat.svg/22px-Flag_of_Montserrat.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Flag_of_Montserrat.svg/320px-Flag_of_Montserrat.svg.png'
        ] 
    ],
    'MR' => // (98) ISO 3166-1 alpha-2
    [
        'name'         => 'Mauritania',
        'rusName'      => 'Мавритания',
        'languages'    => ['ar', 'fr', 'ff'],
        'iso3166_1_a2' => 'MR',
        'iso3166_1_a3' => 'MRT',
        'iso3166_1_n'  => '478',
        'iso3166_2'    => 'ISO 3166-2:MR',
        'gost7_67'     => ['cyrillic' => 'МАВ', 'numeric' => '478'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/43/Flag_of_Mauritania.svg/22px-Flag_of_Mauritania.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/43/Flag_of_Mauritania.svg/320px-Flag_of_Mauritania.svg.png'
        ] 
    ],
    'MQ' => // (99) ISO 3166-1 alpha-2
    [
        'name'         => 'Martinique',
        'rusName'      => 'Мартиника',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'MQ',
        'iso3166_1_a3' => 'MTQ',
        'iso3166_1_n'  => '474',
        'iso3166_2'    => 'ISO 3166-2:MQ',
        'gost7_67'     => ['cyrillic' => 'МАТ', 'numeric' => '474'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Snake_Flag_of_Martinique.svg/22px-Snake_Flag_of_Martinique.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Snake_Flag_of_Martinique.svg/320px-Snake_Flag_of_Martinique.svg.png'
        ] 
    ],
    'MP' => // (100) ISO 3166-1 alpha-2
    [
        'name'         => 'Northern Mariana Islands',
        'rusName'      => 'Северные Марианские о-ва',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'MP',
        'iso3166_1_a3' => 'MNP',
        'iso3166_1_n'  => '580',
        'iso3166_2'    => 'ISO 3166-2:MP',
        'gost7_67'     => ['cyrillic' => 'СЕВ', 'numeric' => '580'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Flag_of_the_Northern_Mariana_Islands.svg/22px-Flag_of_the_Northern_Mariana_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Flag_of_the_Northern_Mariana_Islands.svg/320px-Flag_of_the_Northern_Mariana_Islands.svg.png'
        ] 
    ],
    'MO' => // (101) ISO 3166-1 alpha-2
    [
        'name'         => 'Macao SAR China',
        'rusName'      => 'Макао (САР)',
        'languages'    => ['en', 'pt', 'zh'],
        'iso3166_1_a2' => 'MO',
        'iso3166_1_a3' => 'MAC',
        'iso3166_1_n'  => '446',
        'iso3166_2'    => 'ISO 3166-2:MO',
        'gost7_67'     => ['cyrillic' => 'АОМ', 'numeric' => '446'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/63/Flag_of_Macau.svg/22px-Flag_of_Macau.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/63/Flag_of_Macau.svg/320px-Flag_of_Macau.svg.png'
        ] 
    ],
    'MN' => // (102) ISO 3166-1 alpha-2
    [
        'name'         => 'Mongolia',
        'rusName'      => 'Монголия',
        'languages'    => ['mn'],
        'iso3166_1_a2' => 'MN',
        'iso3166_1_a3' => 'MNG',
        'iso3166_1_n'  => '496',
        'iso3166_2'    => 'ISO 3166-2:MN',
        'gost7_67'     => ['cyrillic' => 'МОО', 'numeric' => '496'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Flag_of_Mongolia.svg/22px-Flag_of_Mongolia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4c/Flag_of_Mongolia.svg/320px-Flag_of_Mongolia.svg.png'
        ] 
    ],
    'MM' => // (103) ISO 3166-1 alpha-2
    [
        'name'         => 'Myanmar (Burma)',
        'rusName'      => 'Мьянма (Бирма)',
        'languages'    => ['my'],
        'iso3166_1_a2' => 'MM',
        'iso3166_1_a3' => 'MMR',
        'iso3166_1_n'  => '104',
        'iso3166_2'    => 'ISO 3166-2:MM',
        'gost7_67'     => ['cyrillic' => 'МЬЯ', 'numeric' => '104'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Flag_of_Myanmar.svg/22px-Flag_of_Myanmar.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Flag_of_Myanmar.svg/320px-Flag_of_Myanmar.svg.png'
        ] 
    ],
    'ML' => // (104) ISO 3166-1 alpha-2
    [
        'name'         => 'Mali',
        'rusName'      => 'Мали',
        'languages'    => ['bm', 'fr'],
        'iso3166_1_a2' => 'ML',
        'iso3166_1_a3' => 'MLI',
        'iso3166_1_n'  => '466',
        'iso3166_2'    => 'ISO 3166-2:ML',
        'gost7_67'     => ['cyrillic' => 'МАИ', 'numeric' => '466'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_Mali.svg/22px-Flag_of_Mali.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_Mali.svg/320px-Flag_of_Mali.svg.png'
        ] 
    ],
    'MK' => // (105) ISO 3166-1 alpha-2
    [
        'name'         => 'North Macedonia',
        'rusName'      => 'Северная Македония',
        'languages'    => ['sq', 'mk'],
        'iso3166_1_a2' => 'MK',
        'iso3166_1_a3' => 'MKD',
        'iso3166_1_n'  => '807',
        'iso3166_2'    => 'ISO 3166-2:MK',
        'gost7_67'     => ['cyrillic' => 'МАД', 'numeric' => '807'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_North_Macedonia.svg/22px-Flag_of_North_Macedonia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/79/Flag_of_North_Macedonia.svg/320px-Flag_of_North_Macedonia.svg.png'
        ] 
    ],
    'MH' => // (106) ISO 3166-1 alpha-2
    [
        'name'         => 'Marshall Islands',
        'rusName'      => 'Маршалловы Острова',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'MH',
        'iso3166_1_a3' => 'MHL',
        'iso3166_1_n'  => '584',
        'iso3166_2'    => 'ISO 3166-2:MH',
        'gost7_67'     => ['cyrillic' => 'МАШ', 'numeric' => '584'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Flag_of_the_Marshall_Islands.svg/22px-Flag_of_the_Marshall_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Flag_of_the_Marshall_Islands.svg/320px-Flag_of_the_Marshall_Islands.svg.png'
        ] 
    ],
    'MG' => // (107) ISO 3166-1 alpha-2
    [
        'name'         => 'Madagascar',
        'rusName'      => 'Мадагаскар',
        'languages'    => ['en', 'fr', 'mg'],
        'iso3166_1_a2' => 'MG',
        'iso3166_1_a3' => 'MDG',
        'iso3166_1_n'  => '450',
        'iso3166_2'    => 'ISO 3166-2:MG',
        'gost7_67'     => ['cyrillic' => 'МАГ', 'numeric' => '450'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Madagascar.svg/22px-Flag_of_Madagascar.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Madagascar.svg/320px-Flag_of_Madagascar.svg.png'
        ] 
    ],
    'MF' => // (108) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Martin',
        'rusName'      => 'Сен-Мартен',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'MF',
        'iso3166_1_a3' => 'MAF',
        'iso3166_1_n'  => '663',
        'iso3166_2'    => 'ISO 3166-2:MF',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '663'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Saint-Martin_%28fictional%29.svg/22px-Flag_of_Saint-Martin_%28fictional%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Saint-Martin_%28fictional%29.svg/320px-Flag_of_Saint-Martin_%28fictional%29.svg.png'
        ] 
    ],
    'ME' => // (109) ISO 3166-1 alpha-2
    [
        'name'         => 'Montenegro',
        'rusName'      => 'Черногория',
        'languages'    => ['sr'],
        'iso3166_1_a2' => 'ME',
        'iso3166_1_a3' => 'MNE',
        'iso3166_1_n'  => '499',
        'iso3166_2'    => 'ISO 3166-2:ME',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '499'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Flag_of_Montenegro.svg/22px-Flag_of_Montenegro.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/64/Flag_of_Montenegro.svg/320px-Flag_of_Montenegro.svg.png'
        ] 
    ],
    'MD' => // (110) ISO 3166-1 alpha-2
    [
        'name'         => 'Moldova',
        'rusName'      => 'Молдова',
        'languages'    => ['ro', 'ru'],
        'iso3166_1_a2' => 'MD',
        'iso3166_1_a3' => 'MDA',
        'iso3166_1_n'  => '498',
        'iso3166_2'    => 'ISO 3166-2:MD',
        'gost7_67'     => ['cyrillic' => 'МОЛ', 'numeric' => '498'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/27/Flag_of_Moldova.svg/22px-Flag_of_Moldova.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/27/Flag_of_Moldova.svg/320px-Flag_of_Moldova.svg.png'
        ] 
    ],
    'MC' => // (111) ISO 3166-1 alpha-2
    [
        'name'         => 'Monaco',
        'rusName'      => 'Монако',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'MC',
        'iso3166_1_a3' => 'MCO',
        'iso3166_1_n'  => '492',
        'iso3166_2'    => 'ISO 3166-2:MC',
        'gost7_67'     => ['cyrillic' => 'МОН', 'numeric' => '492'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ea/Flag_of_Monaco.svg/22px-Flag_of_Monaco.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ea/Flag_of_Monaco.svg/320px-Flag_of_Monaco.svg.png'
        ] 
    ],
    'MA' => // (112) ISO 3166-1 alpha-2
    [
        'name'         => 'Morocco',
        'rusName'      => 'Марокко',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'MA',
        'iso3166_1_a3' => 'MAR',
        'iso3166_1_n'  => '504',
        'iso3166_2'    => 'ISO 3166-2:MA',
        'gost7_67'     => ['cyrillic' => 'МАР', 'numeric' => '504'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Morocco.svg/22px-Flag_of_Morocco.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Morocco.svg/320px-Flag_of_Morocco.svg.png'
        ] 
    ],
    'LY' => // (113) ISO 3166-1 alpha-2
    [
        'name'         => 'Libya',
        'rusName'      => 'Ливия',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'LY',
        'iso3166_1_a3' => 'LBY',
        'iso3166_1_n'  => '434',
        'iso3166_2'    => 'ISO 3166-2:LY',
        'gost7_67'     => ['cyrillic' => 'ЛИИ', 'numeric' => '434'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Libya.svg/22px-Flag_of_Libya.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Libya.svg/320px-Flag_of_Libya.svg.png'
        ] 
    ],
    'LV' => // (114) ISO 3166-1 alpha-2
    [
        'name'         => 'Latvia',
        'rusName'      => 'Латвия',
        'languages'    => ['lv'],
        'iso3166_1_a2' => 'LV',
        'iso3166_1_a3' => 'LVA',
        'iso3166_1_n'  => '428',
        'iso3166_2'    => 'ISO 3166-2:LV',
        'gost7_67'     => ['cyrillic' => 'ЛАТ', 'numeric' => '428'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/84/Flag_of_Latvia.svg/22px-Flag_of_Latvia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/84/Flag_of_Latvia.svg/320px-Flag_of_Latvia.svg.png'
        ] 
    ],
    'LU' => // (115) ISO 3166-1 alpha-2
    [
        'name'         => 'Luxembourg',
        'rusName'      => 'Люксембург',
        'languages'    => ['de', 'fr', 'lb'],
        'iso3166_1_a2' => 'LU',
        'iso3166_1_a3' => 'LUX',
        'iso3166_1_n'  => '442',
        'iso3166_2'    => 'ISO 3166-2:LU',
        'gost7_67'     => ['cyrillic' => 'ЛЮК', 'numeric' => '442'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/da/Flag_of_Luxembourg.svg/22px-Flag_of_Luxembourg.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/da/Flag_of_Luxembourg.svg/320px-Flag_of_Luxembourg.svg.png'
        ] 
    ],
    'LT' => // (116) ISO 3166-1 alpha-2
    [
        'name'         => 'Lithuania',
        'rusName'      => 'Литва',
        'languages'    => ['lt'],
        'iso3166_1_a2' => 'LT',
        'iso3166_1_a3' => 'LTU',
        'iso3166_1_n'  => '440',
        'iso3166_2'    => 'ISO 3166-2:LT',
        'gost7_67'     => ['cyrillic' => 'ЛИТ', 'numeric' => '440'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/11/Flag_of_Lithuania.svg/22px-Flag_of_Lithuania.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/11/Flag_of_Lithuania.svg/320px-Flag_of_Lithuania.svg.png'
        ] 
    ],
    'LS' => // (117) ISO 3166-1 alpha-2
    [
        'name'         => 'Lesotho',
        'rusName'      => 'Лесото',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'LS',
        'iso3166_1_a3' => 'LSO',
        'iso3166_1_n'  => '426',
        'iso3166_2'    => 'ISO 3166-2:LS',
        'gost7_67'     => ['cyrillic' => 'ЛЕС', 'numeric' => '426'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Flag_of_Lesotho.svg/22px-Flag_of_Lesotho.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Flag_of_Lesotho.svg/320px-Flag_of_Lesotho.svg.png'
        ] 
    ],
    'LR' => // (118) ISO 3166-1 alpha-2
    [
        'name'         => 'Liberia',
        'rusName'      => 'Либерия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'LR',
        'iso3166_1_a3' => 'LBR',
        'iso3166_1_n'  => '430',
        'iso3166_2'    => 'ISO 3166-2:LR',
        'gost7_67'     => ['cyrillic' => 'ЛИБ', 'numeric' => '430'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Flag_of_Liberia.svg/22px-Flag_of_Liberia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b8/Flag_of_Liberia.svg/320px-Flag_of_Liberia.svg.png'
        ] 
    ],
    'LK' => // (119) ISO 3166-1 alpha-2
    [
        'name'         => 'Sri Lanka',
        'rusName'      => 'Шри-Ланка',
        'languages'    => ['si', 'ta'],
        'iso3166_1_a2' => 'LK',
        'iso3166_1_a3' => 'LKA',
        'iso3166_1_n'  => '144',
        'iso3166_2'    => 'ISO 3166-2:LK',
        'gost7_67'     => ['cyrillic' => 'ШРИ', 'numeric' => '144'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/11/Flag_of_Sri_Lanka.svg/22px-Flag_of_Sri_Lanka.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/11/Flag_of_Sri_Lanka.svg/320px-Flag_of_Sri_Lanka.svg.png'
        ] 
    ],
    'LI' => // (120) ISO 3166-1 alpha-2
    [
        'name'         => 'Liechtenstein',
        'rusName'      => 'Лихтенштейн',
        'languages'    => ['de'],
        'iso3166_1_a2' => 'LI',
        'iso3166_1_a3' => 'LIE',
        'iso3166_1_n'  => '438',
        'iso3166_2'    => 'ISO 3166-2:LI',
        'gost7_67'     => ['cyrillic' => 'ЛИХ', 'numeric' => '438'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/47/Flag_of_Liechtenstein.svg/22px-Flag_of_Liechtenstein.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/47/Flag_of_Liechtenstein.svg/320px-Flag_of_Liechtenstein.svg.png'
        ] 
    ],
    'LC' => // (121) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Lucia',
        'rusName'      => 'Сент-Люсия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'LC',
        'iso3166_1_a3' => 'LCA',
        'iso3166_1_n'  => '662',
        'iso3166_2'    => 'ISO 3166-2:LC',
        'gost7_67'     => ['cyrillic' => 'СЕТ', 'numeric' => '662'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_Saint_Lucia.svg/22px-Flag_of_Saint_Lucia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_Saint_Lucia.svg/320px-Flag_of_Saint_Lucia.svg.png'
        ] 
    ],
    'LB' => // (122) ISO 3166-1 alpha-2
    [
        'name'         => 'Lebanon',
        'rusName'      => 'Ливан',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'LB',
        'iso3166_1_a3' => 'LBN',
        'iso3166_1_n'  => '422',
        'iso3166_2'    => 'ISO 3166-2:LB',
        'gost7_67'     => ['cyrillic' => 'ЛИВ', 'numeric' => '422'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/59/Flag_of_Lebanon.svg/22px-Flag_of_Lebanon.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/59/Flag_of_Lebanon.svg/320px-Flag_of_Lebanon.svg.png'
        ] 
    ],
    'LA' => // (123) ISO 3166-1 alpha-2
    [
        'name'         => 'Laos',
        'rusName'      => 'Лаос',
        'languages'    => ['lo'],
        'iso3166_1_a2' => 'LA',
        'iso3166_1_a3' => 'LAO',
        'iso3166_1_n'  => '418',
        'iso3166_2'    => 'ISO 3166-2:LA',
        'gost7_67'     => ['cyrillic' => 'ЛАО', 'numeric' => '418'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/56/Flag_of_Laos.svg/22px-Flag_of_Laos.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/56/Flag_of_Laos.svg/320px-Flag_of_Laos.svg.png'
        ] 
    ],
    'KZ' => // (124) ISO 3166-1 alpha-2
    [
        'name'         => 'Kazakhstan',
        'rusName'      => 'Казахстан',
        'languages'    => ['kk', 'ru'],
        'iso3166_1_a2' => 'KZ',
        'iso3166_1_a3' => 'KAZ',
        'iso3166_1_n'  => '398',
        'iso3166_2'    => 'ISO 3166-2:KZ',
        'gost7_67'     => ['cyrillic' => 'КАЗ', 'numeric' => '398'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Kazakhstan.svg/22px-Flag_of_Kazakhstan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Kazakhstan.svg/320px-Flag_of_Kazakhstan.svg.png'
        ] 
    ],
    'KY' => // (125) ISO 3166-1 alpha-2
    [
        'name'         => 'Cayman Islands',
        'rusName'      => 'Острова Кайман',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'KY',
        'iso3166_1_a3' => 'CYM',
        'iso3166_1_n'  => '136',
        'iso3166_2'    => 'ISO 3166-2:KY',
        'gost7_67'     => ['cyrillic' => 'КАЙ', 'numeric' => '136'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_the_Cayman_Islands.svg/22px-Flag_of_the_Cayman_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_the_Cayman_Islands.svg/320px-Flag_of_the_Cayman_Islands.svg.png'
        ] 
    ],
    'KW' => // (126) ISO 3166-1 alpha-2
    [
        'name'         => 'Kuwait',
        'rusName'      => 'Кувейт',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'KW',
        'iso3166_1_a3' => 'KWT',
        'iso3166_1_n'  => '414',
        'iso3166_2'    => 'ISO 3166-2:KW',
        'gost7_67'     => ['cyrillic' => 'КУВ', 'numeric' => '414'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/aa/Flag_of_Kuwait.svg/22px-Flag_of_Kuwait.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/aa/Flag_of_Kuwait.svg/320px-Flag_of_Kuwait.svg.png'
        ] 
    ],
    'KR' => // (127) ISO 3166-1 alpha-2
    [
        'name'         => 'South Korea',
        'rusName'      => 'Республика Корея',
        'languages'    => ['ko'],
        'iso3166_1_a2' => 'KR',
        'iso3166_1_a3' => 'KOR',
        'iso3166_1_n'  => '410',
        'iso3166_2'    => 'ISO 3166-2:KR',
        'gost7_67'     => ['cyrillic' => 'КОР', 'numeric' => '410'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/09/Flag_of_South_Korea.svg/22px-Flag_of_South_Korea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/09/Flag_of_South_Korea.svg/320px-Flag_of_South_Korea.svg.png'
        ] 
    ],
    'KP' => // (128) ISO 3166-1 alpha-2
    [
        'name'         => 'North Korea',
        'rusName'      => 'КНДР',
        'languages'    => ['ko'],
        'iso3166_1_a2' => 'KP',
        'iso3166_1_a3' => 'PRK',
        'iso3166_1_n'  => '408',
        'iso3166_2'    => 'ISO 3166-2:KP',
        'gost7_67'     => ['cyrillic' => 'КОП', 'numeric' => '408'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/51/Flag_of_North_Korea.svg/22px-Flag_of_North_Korea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/51/Flag_of_North_Korea.svg/320px-Flag_of_North_Korea.svg.png'
        ] 
    ],
    'KN' => // (129) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Kitts & Nevis',
        'rusName'      => 'Сент-Китс и Невис',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'KN',
        'iso3166_1_a3' => 'KNA',
        'iso3166_1_n'  => '659',
        'iso3166_2'    => 'ISO 3166-2:KN',
        'gost7_67'     => ['cyrillic' => 'СЕС', 'numeric' => '659'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Saint_Kitts_and_Nevis.svg/22px-Flag_of_Saint_Kitts_and_Nevis.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Saint_Kitts_and_Nevis.svg/320px-Flag_of_Saint_Kitts_and_Nevis.svg.png'
        ] 
    ],
    'KM' => // (130) ISO 3166-1 alpha-2
    [
        'name'         => 'Comoros',
        'rusName'      => 'Коморы',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'KM',
        'iso3166_1_a3' => 'COM',
        'iso3166_1_n'  => '174',
        'iso3166_2'    => 'ISO 3166-2:KM',
        'gost7_67'     => ['cyrillic' => 'КОМ', 'numeric' => '174'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/94/Flag_of_the_Comoros.svg/22px-Flag_of_the_Comoros.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/94/Flag_of_the_Comoros.svg/320px-Flag_of_the_Comoros.svg.png'
        ] 
    ],
    'KI' => // (131) ISO 3166-1 alpha-2
    [
        'name'         => 'Kiribati',
        'rusName'      => 'Кирибати',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'KI',
        'iso3166_1_a3' => 'KIR',
        'iso3166_1_n'  => '296',
        'iso3166_2'    => 'ISO 3166-2:KI',
        'gost7_67'     => ['cyrillic' => 'КИР', 'numeric' => '296'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Kiribati.svg/22px-Flag_of_Kiribati.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Flag_of_Kiribati.svg/320px-Flag_of_Kiribati.svg.png'
        ] 
    ],
    'KH' => // (132) ISO 3166-1 alpha-2
    [
        'name'         => 'Cambodia',
        'rusName'      => 'Камбоджа',
        'languages'    => ['km'],
        'iso3166_1_a2' => 'KH',
        'iso3166_1_a3' => 'KHM',
        'iso3166_1_n'  => '116',
        'iso3166_2'    => 'ISO 3166-2:KH',
        'gost7_67'     => ['cyrillic' => 'КАК', 'numeric' => '116'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/83/Flag_of_Cambodia.svg/22px-Flag_of_Cambodia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/83/Flag_of_Cambodia.svg/320px-Flag_of_Cambodia.svg.png'
        ] 
    ],
    'KG' => // (133) ISO 3166-1 alpha-2
    [
        'name'         => 'Kyrgyzstan',
        'rusName'      => 'Киргизия',
        'languages'    => ['ky', 'ru'],
        'iso3166_1_a2' => 'KG',
        'iso3166_1_a3' => 'KGZ',
        'iso3166_1_n'  => '417',
        'iso3166_2'    => 'ISO 3166-2:KG',
        'gost7_67'     => ['cyrillic' => 'КЫР', 'numeric' => '417'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Flag_of_Kyrgyzstan.svg/22px-Flag_of_Kyrgyzstan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Flag_of_Kyrgyzstan.svg/320px-Flag_of_Kyrgyzstan.svg.png'
        ] 
    ],
    'KE' => // (134) ISO 3166-1 alpha-2
    [
        'name'         => 'Kenya',
        'rusName'      => 'Кения',
        'languages'    => ['en', 'ki', 'om', 'so', 'sw'],
        'iso3166_1_a2' => 'KE',
        'iso3166_1_a3' => 'KEN',
        'iso3166_1_n'  => '404',
        'iso3166_2'    => 'ISO 3166-2:KE',
        'gost7_67'     => ['cyrillic' => 'КЕН', 'numeric' => '404'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Kenya.svg/22px-Flag_of_Kenya.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/49/Flag_of_Kenya.svg/320px-Flag_of_Kenya.svg.png'
        ] 
    ],
    'JP' => // (135) ISO 3166-1 alpha-2
    [
        'name'         => 'Japan',
        'rusName'      => 'Япония',
        'languages'    => ['ja'],
        'iso3166_1_a2' => 'JP',
        'iso3166_1_a3' => 'JPN',
        'iso3166_1_n'  => '392',
        'iso3166_2'    => 'ISO 3166-2:JP',
        'gost7_67'     => ['cyrillic' => 'ЯПО', 'numeric' => '392'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Flag_of_Japan.svg/22px-Flag_of_Japan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Flag_of_Japan.svg/320px-Flag_of_Japan.svg.png'
        ] 
    ],
    'JO' => // (136) ISO 3166-1 alpha-2
    [
        'name'         => 'Jordan',
        'rusName'      => 'Иордания',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'JO',
        'iso3166_1_a3' => 'JOR',
        'iso3166_1_n'  => '400',
        'iso3166_2'    => 'ISO 3166-2:JO',
        'gost7_67'     => ['cyrillic' => 'ИОР', 'numeric' => '400'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Flag_of_Jordan.svg/22px-Flag_of_Jordan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Flag_of_Jordan.svg/320px-Flag_of_Jordan.svg.png'
        ] 
    ],
    'JM' => // (137) ISO 3166-1 alpha-2
    [
        'name'         => 'Jamaica',
        'rusName'      => 'Ямайка',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'JM',
        'iso3166_1_a3' => 'JAM',
        'iso3166_1_n'  => '388',
        'iso3166_2'    => 'ISO 3166-2:JM',
        'gost7_67'     => ['cyrillic' => 'ЯМА', 'numeric' => '388'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Flag_of_Jamaica.svg/22px-Flag_of_Jamaica.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Flag_of_Jamaica.svg/320px-Flag_of_Jamaica.svg.png'
        ] 
    ],
    'JE' => // (138) ISO 3166-1 alpha-2
    [
        'name'         => 'Jersey',
        'rusName'      => 'Джерси',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'JE',
        'iso3166_1_a3' => 'JEY',
        'iso3166_1_n'  => '832',
        'iso3166_2'    => 'ISO 3166-2:JE',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '832'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Flag_of_Jersey.svg/22px-Flag_of_Jersey.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Flag_of_Jersey.svg/320px-Flag_of_Jersey.svg.png'
        ] 
    ],
    'IT' => // (139) ISO 3166-1 alpha-2
    [
        'name'         => 'Italy',
        'rusName'      => 'Италия',
        'languages'    => ['it', 'ca'],
        'iso3166_1_a2' => 'IT',
        'iso3166_1_a3' => 'ITA',
        'iso3166_1_n'  => '380',
        'iso3166_2'    => 'ISO 3166-2:IT',
        'gost7_67'     => ['cyrillic' => 'ИТА', 'numeric' => '380'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/03/Flag_of_Italy.svg/22px-Flag_of_Italy.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/03/Flag_of_Italy.svg/320px-Flag_of_Italy.svg.png'
        ] 
    ],
    'IS' => // (140) ISO 3166-1 alpha-2
    [
        'name'         => 'Iceland',
        'rusName'      => 'Исландия',
        'languages'    => ['is'],
        'iso3166_1_a2' => 'IS',
        'iso3166_1_a3' => 'ISL',
        'iso3166_1_n'  => '352',
        'iso3166_2'    => 'ISO 3166-2:IS',
        'gost7_67'     => ['cyrillic' => 'ИСЛ', 'numeric' => '352'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Flag_of_Iceland.svg/22px-Flag_of_Iceland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Flag_of_Iceland.svg/320px-Flag_of_Iceland.svg.png'
        ] 
    ],
    'IR' => // (141) ISO 3166-1 alpha-2
    [
        'name'         => 'Iran',
        'rusName'      => 'Иран',
        'languages'    => ['fa'],
        'iso3166_1_a2' => 'IR',
        'iso3166_1_a3' => 'IRN',
        'iso3166_1_n'  => '364',
        'iso3166_2'    => 'ISO 3166-2:IR',
        'gost7_67'     => ['cyrillic' => 'ИРН', 'numeric' => '364'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Flag_of_Iran.svg/22px-Flag_of_Iran.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Flag_of_Iran.svg/320px-Flag_of_Iran.svg.png'
        ] 
    ],
    'IQ' => // (142) ISO 3166-1 alpha-2
    [
        'name'         => 'Iraq',
        'rusName'      => 'Ирак',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'IQ',
        'iso3166_1_a3' => 'IRQ',
        'iso3166_1_n'  => '368',
        'iso3166_2'    => 'ISO 3166-2:IQ',
        'gost7_67'     => ['cyrillic' => 'ИРК', 'numeric' => '368'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Flag_of_Iraq.svg/22px-Flag_of_Iraq.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Flag_of_Iraq.svg/320px-Flag_of_Iraq.svg.png'
        ] 
    ],
    'IO' => // (143) ISO 3166-1 alpha-2
    [
        'name'         => 'British Indian Ocean Territory',
        'rusName'      => 'Британская территория в Индийском океане',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'IO',
        'iso3166_1_a3' => 'IOT',
        'iso3166_1_n'  => '086',
        'iso3166_2'    => 'ISO 3166-2:IO',
        'gost7_67'     => ['cyrillic' => 'БРИ', 'numeric' => '086'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/65/Flag_of_the_Commissioner_of_the_British_Indian_Ocean_Territory.svg/22px-Flag_of_the_Commissioner_of_the_British_Indian_Ocean_Territory.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/65/Flag_of_the_Commissioner_of_the_British_Indian_Ocean_Territory.svg/320px-Flag_of_the_Commissioner_of_the_British_Indian_Ocean_Territory.svg.png'
        ] 
    ],
    'IN' => // (144) ISO 3166-1 alpha-2
    [
        'name'         => 'India',
        'rusName'      => 'Индия',
        'languages'    => ['as', 'bn', 'en', 'gu', 'hi', 'kn', 'ks', 'ml', 'mr', 'ne', 'ur', 'or', 'pa', 'ta', 'te', 'bo'],
        'iso3166_1_a2' => 'IN',
        'iso3166_1_a3' => 'IND',
        'iso3166_1_n'  => '356',
        'iso3166_2'    => 'ISO 3166-2:IN',
        'gost7_67'     => ['cyrillic' => 'ИНД', 'numeric' => '356'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_India.svg/22px-Flag_of_India.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_India.svg/320px-Flag_of_India.svg.png'
        ] 
    ],
    'IM' => // (145) ISO 3166-1 alpha-2
    [
        'name'         => 'Isle of Man',
        'rusName'      => 'о-в Мэн',
        'languages'    => ['en', 'gv'],
        'iso3166_1_a2' => 'IM',
        'iso3166_1_a3' => 'IMN',
        'iso3166_1_n'  => '833',
        'iso3166_2'    => 'ISO 3166-2:IM',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '833'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Flag_of_the_Isle_of_Mann.svg/22px-Flag_of_the_Isle_of_Mann.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5d/Flag_of_the_Isle_of_Mann.svg/320px-Flag_of_the_Isle_of_Mann.svg.png'
        ] 
    ],
    'IL' => // (146) ISO 3166-1 alpha-2
    [
        'name'         => 'Israel',
        'rusName'      => 'Израиль',
        'languages'    => ['ar', 'he'],
        'iso3166_1_a2' => 'IL',
        'iso3166_1_a3' => 'ISR',
        'iso3166_1_n'  => '376',
        'iso3166_2'    => 'ISO 3166-2:IL',
        'gost7_67'     => ['cyrillic' => 'ИЗР', 'numeric' => '376'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Flag_of_Israel.svg/22px-Flag_of_Israel.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Flag_of_Israel.svg/320px-Flag_of_Israel.svg.png'
        ] 
    ],
    'IE' => // (147) ISO 3166-1 alpha-2
    [
        'name'         => 'Ireland',
        'rusName'      => 'Ирландия',
        'languages'    => ['en', 'ga'],
        'iso3166_1_a2' => 'IE',
        'iso3166_1_a3' => 'IRL',
        'iso3166_1_n'  => '372',
        'iso3166_2'    => 'ISO 3166-2:IE',
        'gost7_67'     => ['cyrillic' => 'ИРЯ', 'numeric' => '372'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/45/Flag_of_Ireland.svg/22px-Flag_of_Ireland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/45/Flag_of_Ireland.svg/320px-Flag_of_Ireland.svg.png'
        ] 
    ],
    'ID' => // (148) ISO 3166-1 alpha-2
    [
        'name'         => 'Indonesia',
        'rusName'      => 'Индонезия',
        'languages'    => ['id'],
        'iso3166_1_a2' => 'ID',
        'iso3166_1_a3' => 'IDN',
        'iso3166_1_n'  => '360',
        'iso3166_2'    => 'ISO 3166-2:ID',
        'gost7_67'     => ['cyrillic' => 'ИНЗ', 'numeric' => '360'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_Indonesia.svg/22px-Flag_of_Indonesia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_Indonesia.svg/320px-Flag_of_Indonesia.svg.png'
        ] 
    ],
    'HU' => // (149) ISO 3166-1 alpha-2
    [
        'name'         => 'Hungary',
        'rusName'      => 'Венгрия',
        'languages'    => ['hu'],
        'iso3166_1_a2' => 'HU',
        'iso3166_1_a3' => 'HUN',
        'iso3166_1_n'  => '348',
        'iso3166_2'    => 'ISO 3166-2:HU',
        'gost7_67'     => ['cyrillic' => 'ВЕН', 'numeric' => '348'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Flag_of_Hungary.svg/22px-Flag_of_Hungary.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Flag_of_Hungary.svg/320px-Flag_of_Hungary.svg.png'
        ] 
    ],
    'HT' => // (150) ISO 3166-1 alpha-2
    [
        'name'         => 'Haiti',
        'rusName'      => 'Гаити',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'HT',
        'iso3166_1_a3' => 'HTI',
        'iso3166_1_n'  => '332',
        'iso3166_2'    => 'ISO 3166-2:HT',
        'gost7_67'     => ['cyrillic' => 'ГАИ', 'numeric' => '332'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/56/Flag_of_Haiti.svg/22px-Flag_of_Haiti.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/56/Flag_of_Haiti.svg/320px-Flag_of_Haiti.svg.png'
        ] 
    ],
    'HR' => // (151) ISO 3166-1 alpha-2
    [
        'name'         => 'Croatia',
        'rusName'      => 'Хорватия',
        'languages'    => ['hr'],
        'iso3166_1_a2' => 'HR',
        'iso3166_1_a3' => 'HRV',
        'iso3166_1_n'  => '191',
        'iso3166_2'    => 'ISO 3166-2:HR',
        'gost7_67'     => ['cyrillic' => 'ХОР', 'numeric' => '191'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Flag_of_Croatia.svg/22px-Flag_of_Croatia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Flag_of_Croatia.svg/320px-Flag_of_Croatia.svg.png'
        ] 
    ],
    'HN' => // (152) ISO 3166-1 alpha-2
    [
        'name'         => 'Honduras',
        'rusName'      => 'Гондурас',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'HN',
        'iso3166_1_a3' => 'HND',
        'iso3166_1_n'  => '340',
        'iso3166_2'    => 'ISO 3166-2:HN',
        'gost7_67'     => ['cyrillic' => 'ГОН', 'numeric' => '340'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Flag_of_Honduras_%28darker_variant%29.svg/22px-Flag_of_Honduras_%28darker_variant%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Flag_of_Honduras_%28darker_variant%29.svg/320px-Flag_of_Honduras_%28darker_variant%29.svg.png'
        ] 
    ],
    'HM' => // (153) ISO 3166-1 alpha-2
    [
        'name'         => 'Heard & McDonald Islands',
        'rusName'      => 'о-ва Херд и Макдональд',
        'languages'    => [],
        'iso3166_1_a2' => 'HM',
        'iso3166_1_a3' => 'HMD',
        'iso3166_1_n'  => '334',
        'iso3166_2'    => 'ISO 3166-2:HM',
        'gost7_67'     => ['cyrillic' => 'ХЕМ', 'numeric' => '334'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Flag_of_Australia.svg/22px-Flag_of_Australia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Flag_of_Australia.svg/320px-Flag_of_Australia.svg.png'
        ] 
    ],
    'HK' => // (154) ISO 3166-1 alpha-2
    [
        'name'         => 'Hong Kong SAR China',
        'rusName'      => 'Гонконг (САР)',
        'languages'    => ['en', 'zh'],
        'iso3166_1_a2' => 'HK',
        'iso3166_1_a3' => 'HKG',
        'iso3166_1_n'  => '344',
        'iso3166_2'    => 'ISO 3166-2:HK',
        'gost7_67'     => ['cyrillic' => 'ГОО', 'numeric' => '344'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Flag_of_Hong_Kong.svg/22px-Flag_of_Hong_Kong.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Flag_of_Hong_Kong.svg/320px-Flag_of_Hong_Kong.svg.png'
        ] 
    ],
    'GY' => // (155) ISO 3166-1 alpha-2
    [
        'name'         => 'Guyana',
        'rusName'      => 'Гайана',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GY',
        'iso3166_1_a3' => 'GUY',
        'iso3166_1_n'  => '328',
        'iso3166_2'    => 'ISO 3166-2:GY',
        'gost7_67'     => ['cyrillic' => 'ГАЙ', 'numeric' => '328'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/99/Flag_of_Guyana.svg/22px-Flag_of_Guyana.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/99/Flag_of_Guyana.svg/320px-Flag_of_Guyana.svg.png'
        ] 
    ],
    'GW' => // (156) ISO 3166-1 alpha-2
    [
        'name'         => 'Guinea-Bissau',
        'rusName'      => 'Гвинея-Бисау',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'GW',
        'iso3166_1_a3' => 'GNB',
        'iso3166_1_n'  => '624',
        'iso3166_2'    => 'ISO 3166-2:GW',
        'gost7_67'     => ['cyrillic' => 'ГВЯ', 'numeric' => '624'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Guinea-Bissau.svg/22px-Flag_of_Guinea-Bissau.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/01/Flag_of_Guinea-Bissau.svg/320px-Flag_of_Guinea-Bissau.svg.png'
        ] 
    ],
    'GU' => // (157) ISO 3166-1 alpha-2
    [
        'name'         => 'Guam',
        'rusName'      => 'Гуам',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GU',
        'iso3166_1_a3' => 'GUM',
        'iso3166_1_n'  => '316',
        'iso3166_2'    => 'ISO 3166-2:GU',
        'gost7_67'     => ['cyrillic' => 'ГУА', 'numeric' => '316'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/07/Flag_of_Guam.svg/22px-Flag_of_Guam.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/07/Flag_of_Guam.svg/320px-Flag_of_Guam.svg.png'
        ] 
    ],
    'GT' => // (158) ISO 3166-1 alpha-2
    [
        'name'         => 'Guatemala',
        'rusName'      => 'Гватемала',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'GT',
        'iso3166_1_a3' => 'GTM',
        'iso3166_1_n'  => '320',
        'iso3166_2'    => 'ISO 3166-2:GT',
        'gost7_67'     => ['cyrillic' => 'ГВЕ', 'numeric' => '320'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Flag_of_Guatemala.svg/22px-Flag_of_Guatemala.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Flag_of_Guatemala.svg/320px-Flag_of_Guatemala.svg.png'
        ] 
    ],
    'GS' => // (159) ISO 3166-1 alpha-2
    [
        'name'         => 'South Georgia & South Sandwich Islands',
        'rusName'      => 'Южная Георгия и Южные Сандвичевы о-ва',
        'languages'    => [],
        'iso3166_1_a2' => 'GS',
        'iso3166_1_a3' => 'SGS',
        'iso3166_1_n'  => '239',
        'iso3166_2'    => 'ISO 3166-2:GS',
        'gost7_67'     => ['cyrillic' => 'ЮЖГ', 'numeric' => '239'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Flag_of_South_Georgia_and_the_South_Sandwich_Islands.svg/22px-Flag_of_South_Georgia_and_the_South_Sandwich_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Flag_of_South_Georgia_and_the_South_Sandwich_Islands.svg/320px-Flag_of_South_Georgia_and_the_South_Sandwich_Islands.svg.png'
        ] 
    ],
    'GR' => // (160) ISO 3166-1 alpha-2
    [
        'name'         => 'Greece',
        'rusName'      => 'Греция',
        'languages'    => ['el'],
        'iso3166_1_a2' => 'GR',
        'iso3166_1_a3' => 'GRC',
        'iso3166_1_n'  => '300',
        'iso3166_2'    => 'ISO 3166-2:GR',
        'gost7_67'     => ['cyrillic' => 'ГРИ', 'numeric' => '300'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Greece.svg/22px-Flag_of_Greece.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Flag_of_Greece.svg/320px-Flag_of_Greece.svg.png'
        ] 
    ],
    'GQ' => // (161) ISO 3166-1 alpha-2
    [
        'name'         => 'Equatorial Guinea',
        'rusName'      => 'Экваториальная Гвинея',
        'languages'    => ['fr', 'es'],
        'iso3166_1_a2' => 'GQ',
        'iso3166_1_a3' => 'GNQ',
        'iso3166_1_n'  => '226',
        'iso3166_2'    => 'ISO 3166-2:GQ',
        'gost7_67'     => ['cyrillic' => 'ЭКВ', 'numeric' => '226'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Equatorial_Guinea.svg/22px-Flag_of_Equatorial_Guinea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Equatorial_Guinea.svg/320px-Flag_of_Equatorial_Guinea.svg.png'
        ] 
    ],
    'GP' => // (162) ISO 3166-1 alpha-2
    [
        'name'         => 'Guadeloupe',
        'rusName'      => 'Гваделупа',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'GP',
        'iso3166_1_a3' => 'GLP',
        'iso3166_1_n'  => '312',
        'iso3166_2'    => 'ISO 3166-2:GP',
        'gost7_67'     => ['cyrillic' => 'ГВА', 'numeric' => '312'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Unofficial_flag_of_Guadeloupe_%28local%29.svg/22px-Unofficial_flag_of_Guadeloupe_%28local%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Unofficial_flag_of_Guadeloupe_%28local%29.svg/320px-Unofficial_flag_of_Guadeloupe_%28local%29.svg.png'
        ] 
    ],
    'GN' => // (163) ISO 3166-1 alpha-2
    [
        'name'         => 'Guinea',
        'rusName'      => 'Гвинея',
        'languages'    => ['fr', 'ff'],
        'iso3166_1_a2' => 'GN',
        'iso3166_1_a3' => 'GIN',
        'iso3166_1_n'  => '324',
        'iso3166_2'    => 'ISO 3166-2:GN',
        'gost7_67'     => ['cyrillic' => 'ГВН', 'numeric' => '324'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Flag_of_Guinea.svg/22px-Flag_of_Guinea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ed/Flag_of_Guinea.svg/320px-Flag_of_Guinea.svg.png'
        ] 
    ],
    'GM' => // (164) ISO 3166-1 alpha-2
    [
        'name'         => 'Gambia',
        'rusName'      => 'Гамбия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GM',
        'iso3166_1_a3' => 'GMB',
        'iso3166_1_n'  => '270',
        'iso3166_2'    => 'ISO 3166-2:GM',
        'gost7_67'     => ['cyrillic' => 'ГАМ', 'numeric' => '270'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_The_Gambia.svg/22px-Flag_of_The_Gambia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_The_Gambia.svg/320px-Flag_of_The_Gambia.svg.png'
        ] 
    ],
    'GL' => // (165) ISO 3166-1 alpha-2
    [
        'name'         => 'Greenland',
        'rusName'      => 'Гренландия',
        'languages'    => ['da', 'kl'],
        'iso3166_1_a2' => 'GL',
        'iso3166_1_a3' => 'GRL',
        'iso3166_1_n'  => '304',
        'iso3166_2'    => 'ISO 3166-2:GL',
        'gost7_67'     => ['cyrillic' => 'ГРЕ', 'numeric' => '304'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/09/Flag_of_Greenland.svg/22px-Flag_of_Greenland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/09/Flag_of_Greenland.svg/320px-Flag_of_Greenland.svg.png'
        ] 
    ],
    'GI' => // (166) ISO 3166-1 alpha-2
    [
        'name'         => 'Gibraltar',
        'rusName'      => 'Гибралтар',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GI',
        'iso3166_1_a3' => 'GIB',
        'iso3166_1_n'  => '292',
        'iso3166_2'    => 'ISO 3166-2:GI',
        'gost7_67'     => ['cyrillic' => 'ГИБ', 'numeric' => '292'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/02/Flag_of_Gibraltar.svg/22px-Flag_of_Gibraltar.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/02/Flag_of_Gibraltar.svg/320px-Flag_of_Gibraltar.svg.png'
        ] 
    ],
    'GH' => // (167) ISO 3166-1 alpha-2
    [
        'name'         => 'Ghana',
        'rusName'      => 'Гана',
        'languages'    => ['ak', 'en', 'ee', 'ha'],
        'iso3166_1_a2' => 'GH',
        'iso3166_1_a3' => 'GHA',
        'iso3166_1_n'  => '288',
        'iso3166_2'    => 'ISO 3166-2:GH',
        'gost7_67'     => ['cyrillic' => 'ГАН', 'numeric' => '288'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Ghana.svg/22px-Flag_of_Ghana.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Ghana.svg/320px-Flag_of_Ghana.svg.png'
        ] 
    ],
    'GG' => // (168) ISO 3166-1 alpha-2
    [
        'name'         => 'Guernsey',
        'rusName'      => 'Гернси',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GG',
        'iso3166_1_a3' => 'GGY',
        'iso3166_1_n'  => '831',
        'iso3166_2'    => 'ISO 3166-2:GG',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '831'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_Guernsey.svg/22px-Flag_of_Guernsey.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_Guernsey.svg/320px-Flag_of_Guernsey.svg.png'
        ] 
    ],
    'GF' => // (169) ISO 3166-1 alpha-2
    [
        'name'         => 'French Guiana',
        'rusName'      => 'Французская Гвиана',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'GF',
        'iso3166_1_a3' => 'GUF',
        'iso3166_1_n'  => '254',
        'iso3166_2'    => 'ISO 3166-2:GF',
        'gost7_67'     => ['cyrillic' => 'ГВИ', 'numeric' => '254'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/22px-Flag_of_France.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/320px-Flag_of_France.svg.png'
        ] 
    ],
    'GE' => // (170) ISO 3166-1 alpha-2
    [
        'name'         => 'Georgia',
        'rusName'      => 'Грузия',
        'languages'    => ['ka', 'os'],
        'iso3166_1_a2' => 'GE',
        'iso3166_1_a3' => 'GEO',
        'iso3166_1_n'  => '268',
        'iso3166_2'    => 'ISO 3166-2:GE',
        'gost7_67'     => ['cyrillic' => 'ГРУ', 'numeric' => '268'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_Georgia.svg/22px-Flag_of_Georgia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Flag_of_Georgia.svg/320px-Flag_of_Georgia.svg.png'
        ] 
    ],
    'GD' => // (171) ISO 3166-1 alpha-2
    [
        'name'         => 'Grenada',
        'rusName'      => 'Гренада',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'GD',
        'iso3166_1_a3' => 'GRD',
        'iso3166_1_n'  => '308',
        'iso3166_2'    => 'ISO 3166-2:GD',
        'gost7_67'     => ['cyrillic' => 'ГРА', 'numeric' => '308'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Grenada.svg/22px-Flag_of_Grenada.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Grenada.svg/320px-Flag_of_Grenada.svg.png'
        ] 
    ],
    'GB' => // (172) ISO 3166-1 alpha-2
    [
        'name'         => 'United Kingdom',
        'rusName'      => 'Великобритания',
        'languages'    => ['en', 'kw', 'gd', 'cy'],
        'iso3166_1_a2' => 'GB',
        'iso3166_1_a3' => 'GBR',
        'iso3166_1_n'  => '826',
        'iso3166_2'    => 'ISO 3166-2:GB',
        'gost7_67'     => ['cyrillic' => 'ВЕЛ', 'numeric' => '826'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_the_United_Kingdom.svg/22px-Flag_of_the_United_Kingdom.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Flag_of_the_United_Kingdom.svg/320px-Flag_of_the_United_Kingdom.svg.png'
        ] 
    ],
    'GA' => // (173) ISO 3166-1 alpha-2
    [
        'name'         => 'Gabon',
        'rusName'      => 'Габон',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'GA',
        'iso3166_1_a3' => 'GAB',
        'iso3166_1_n'  => '266',
        'iso3166_2'    => 'ISO 3166-2:GA',
        'gost7_67'     => ['cyrillic' => 'ГАБ', 'numeric' => '266'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/04/Flag_of_Gabon.svg/22px-Flag_of_Gabon.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/04/Flag_of_Gabon.svg/320px-Flag_of_Gabon.svg.png'
        ] 
    ],
    'FR' => // (174) ISO 3166-1 alpha-2
    [
        'name'         => 'France',
        'rusName'      => 'Франция',
        'languages'    => ['br', 'fr', 'ca'],
        'iso3166_1_a2' => 'FR',
        'iso3166_1_a3' => 'FRA',
        'iso3166_1_n'  => '250',
        'iso3166_2'    => 'ISO 3166-2:FR',
        'gost7_67'     => ['cyrillic' => 'ФРА', 'numeric' => '250'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/22px-Flag_of_France.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c3/Flag_of_France.svg/320px-Flag_of_France.svg.png'
        ] 
    ],
    'FO' => // (175) ISO 3166-1 alpha-2
    [
        'name'         => 'Faroe Islands',
        'rusName'      => 'Фарерские о-ва',
        'languages'    => ['fo'],
        'iso3166_1_a2' => 'FO',
        'iso3166_1_a3' => 'FRO',
        'iso3166_1_n'  => '234',
        'iso3166_2'    => 'ISO 3166-2:FO',
        'gost7_67'     => ['cyrillic' => 'ФАР', 'numeric' => '234'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Flag_of_the_Faroe_Islands.svg/22px-Flag_of_the_Faroe_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Flag_of_the_Faroe_Islands.svg/320px-Flag_of_the_Faroe_Islands.svg.png'
        ] 
    ],
    'FM' => // (176) ISO 3166-1 alpha-2
    [
        'name'         => 'Micronesia',
        'rusName'      => 'Федеративные Штаты Микронезии',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'FM',
        'iso3166_1_a3' => 'FSM',
        'iso3166_1_n'  => '583',
        'iso3166_2'    => 'ISO 3166-2:FM',
        'gost7_67'     => ['cyrillic' => 'МИК', 'numeric' => '583'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e4/Flag_of_the_Federated_States_of_Micronesia.svg/22px-Flag_of_the_Federated_States_of_Micronesia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e4/Flag_of_the_Federated_States_of_Micronesia.svg/320px-Flag_of_the_Federated_States_of_Micronesia.svg.png'
        ] 
    ],
    'FK' => // (177) ISO 3166-1 alpha-2
    [
        'name'         => 'Falkland Islands',
        'rusName'      => 'Фолклендские о-ва',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'FK',
        'iso3166_1_a3' => 'FLK',
        'iso3166_1_n'  => '238',
        'iso3166_2'    => 'ISO 3166-2:FK',
        'gost7_67'     => ['cyrillic' => 'ФОЛ', 'numeric' => '238'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/83/Flag_of_the_Falkland_Islands.svg/22px-Flag_of_the_Falkland_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/83/Flag_of_the_Falkland_Islands.svg/320px-Flag_of_the_Falkland_Islands.svg.png'
        ] 
    ],
    'FJ' => // (178) ISO 3166-1 alpha-2
    [
        'name'         => 'Fiji',
        'rusName'      => 'Фиджи',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'FJ',
        'iso3166_1_a3' => 'FJI',
        'iso3166_1_n'  => '242',
        'iso3166_2'    => 'ISO 3166-2:FJ',
        'gost7_67'     => ['cyrillic' => 'ФИД', 'numeric' => '242'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Flag_of_Fiji.svg/22px-Flag_of_Fiji.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Flag_of_Fiji.svg/320px-Flag_of_Fiji.svg.png'
        ] 
    ],
    'FI' => // (179) ISO 3166-1 alpha-2
    [
        'name'         => 'Finland',
        'rusName'      => 'Финляндия',
        'languages'    => ['fi', 'se', 'sv'],
        'iso3166_1_a2' => 'FI',
        'iso3166_1_a3' => 'FIN',
        'iso3166_1_n'  => '246',
        'iso3166_2'    => 'ISO 3166-2:FI',
        'gost7_67'     => ['cyrillic' => 'ФИН', 'numeric' => '246'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Finland.svg/22px-Flag_of_Finland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Finland.svg/320px-Flag_of_Finland.svg.png'
        ] 
    ],
    'EU' => // (180) ISO 3166-1 alpha-2
    [
        'name'         => 'Europe Union',
        'rusName'      => 'Европейский союз',
        'languages'    => [],
        'iso3166_1_a2' => 'EU',
        'iso3166_1_a3' => '',
        'iso3166_1_n'  => '',
        'iso3166_2'    => 'ISO 3166-2:EU',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => ''],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b7/Flag_of_Europe.svg/22px-Flag_of_Europe.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b7/Flag_of_Europe.svg/320px-Flag_of_Europe.svg.png'
        ] 
    ],
    'ET' => // (181) ISO 3166-1 alpha-2
    [
        'name'         => 'Ethiopia',
        'rusName'      => 'Эфиопия',
        'languages'    => ['am', 'om', 'so', 'ti'],
        'iso3166_1_a2' => 'ET',
        'iso3166_1_a3' => 'ETH',
        'iso3166_1_n'  => '231',
        'iso3166_2'    => 'ISO 3166-2:ET',
        'gost7_67'     => ['cyrillic' => 'ЭФИ', 'numeric' => '231'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/71/Flag_of_Ethiopia.svg/22px-Flag_of_Ethiopia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/71/Flag_of_Ethiopia.svg/320px-Flag_of_Ethiopia.svg.png'
        ] 
    ],
    'ES' => // (182) ISO 3166-1 alpha-2
    [
        'name'         => 'Spain',
        'rusName'      => 'Испания',
        'languages'    => ['eu', 'gl', 'ca', 'es'],
        'iso3166_1_a2' => 'ES',
        'iso3166_1_a3' => 'ESP',
        'iso3166_1_n'  => '724',
        'iso3166_2'    => 'ISO 3166-2:ES',
        'gost7_67'     => ['cyrillic' => 'ИСП', 'numeric' => '724'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Spain.svg/22px-Flag_of_Spain.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Spain.svg/320px-Flag_of_Spain.svg.png'
        ] 
    ],
    'ER' => // (183) ISO 3166-1 alpha-2
    [
        'name'         => 'Eritrea',
        'rusName'      => 'Эритрея',
        'languages'    => ['ar', 'en', 'ti'],
        'iso3166_1_a2' => 'ER',
        'iso3166_1_a3' => 'ERI',
        'iso3166_1_n'  => '232',
        'iso3166_2'    => 'ISO 3166-2:ER',
        'gost7_67'     => ['cyrillic' => 'ЭРИ', 'numeric' => '232'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/29/Flag_of_Eritrea.svg/22px-Flag_of_Eritrea.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/29/Flag_of_Eritrea.svg/320px-Flag_of_Eritrea.svg.png'
        ] 
    ],
    'EH' => // (184) ISO 3166-1 alpha-2
    [
        'name'         => 'Western Sahara',
        'rusName'      => 'Западная Сахара',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'EH',
        'iso3166_1_a3' => 'ESH',
        'iso3166_1_n'  => '732',
        'iso3166_2'    => 'ISO 3166-2:EH',
        'gost7_67'     => ['cyrillic' => 'ЗАП', 'numeric' => '732'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/26/Flag_of_the_Sahrawi_Arab_Democratic_Republic.svg/22px-Flag_of_the_Sahrawi_Arab_Democratic_Republic.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/26/Flag_of_the_Sahrawi_Arab_Democratic_Republic.svg/320px-Flag_of_the_Sahrawi_Arab_Democratic_Republic.svg.png'
        ] 
    ],
    'EG' => // (185) ISO 3166-1 alpha-2
    [
        'name'         => 'Egypt',
        'rusName'      => 'Египет',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'EG',
        'iso3166_1_a3' => 'EGY',
        'iso3166_1_n'  => '818',
        'iso3166_2'    => 'ISO 3166-2:EG',
        'gost7_67'     => ['cyrillic' => 'ЕГИ', 'numeric' => '818'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Egypt.svg/22px-Flag_of_Egypt.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_Egypt.svg/320px-Flag_of_Egypt.svg.png'
        ] 
    ],
    'EE' => // (186) ISO 3166-1 alpha-2
    [
        'name'         => 'Estonia',
        'rusName'      => 'Эстония',
        'languages'    => ['et'],
        'iso3166_1_a2' => 'EE',
        'iso3166_1_a3' => 'EST',
        'iso3166_1_n'  => '233',
        'iso3166_2'    => 'ISO 3166-2:EE',
        'gost7_67'     => ['cyrillic' => 'ЭСТ', 'numeric' => '233'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8f/Flag_of_Estonia.svg/22px-Flag_of_Estonia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/8f/Flag_of_Estonia.svg/320px-Flag_of_Estonia.svg.png'
        ] 
    ],
    'EC' => // (187) ISO 3166-1 alpha-2
    [
        'name'         => 'Ecuador',
        'rusName'      => 'Эквадор',
        'languages'    => ['qu', 'es'],
        'iso3166_1_a2' => 'EC',
        'iso3166_1_a3' => 'ECU',
        'iso3166_1_n'  => '218',
        'iso3166_2'    => 'ISO 3166-2:EC',
        'gost7_67'     => ['cyrillic' => 'ЭКА', 'numeric' => '218'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Flag_of_Ecuador.svg/22px-Flag_of_Ecuador.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Flag_of_Ecuador.svg/320px-Flag_of_Ecuador.svg.png'
        ] 
    ],
    'DZ' => // (188) ISO 3166-1 alpha-2
    [
        'name'         => 'Algeria',
        'rusName'      => 'Алжир',
        'languages'    => ['ar', 'fr'],
        'iso3166_1_a2' => 'DZ',
        'iso3166_1_a3' => 'DZA',
        'iso3166_1_n'  => '012',
        'iso3166_2'    => 'ISO 3166-2:DZ',
        'gost7_67'     => ['cyrillic' => 'АЛЖ', 'numeric' => '012'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_Algeria.svg/22px-Flag_of_Algeria.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Flag_of_Algeria.svg/320px-Flag_of_Algeria.svg.png'
        ] 
    ],
    'DO' => // (189) ISO 3166-1 alpha-2
    [
        'name'         => 'Dominican Republic',
        'rusName'      => 'Доминиканская Республика',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'DO',
        'iso3166_1_a3' => 'DOM',
        'iso3166_1_n'  => '214',
        'iso3166_2'    => 'ISO 3166-2:DO',
        'gost7_67'     => ['cyrillic' => 'ДОН', 'numeric' => '214'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_the_Dominican_Republic.svg/22px-Flag_of_the_Dominican_Republic.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Flag_of_the_Dominican_Republic.svg/320px-Flag_of_the_Dominican_Republic.svg.png'
        ] 
    ],
    'DM' => // (190) ISO 3166-1 alpha-2
    [
        'name'         => 'Dominica',
        'rusName'      => 'Доминика',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'DM',
        'iso3166_1_a3' => 'DMA',
        'iso3166_1_n'  => '212',
        'iso3166_2'    => 'ISO 3166-2:DM',
        'gost7_67'     => ['cyrillic' => 'ДОМ', 'numeric' => '212'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Flag_of_Dominica.svg/22px-Flag_of_Dominica.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Flag_of_Dominica.svg/320px-Flag_of_Dominica.svg.png'
        ] 
    ],
    'DK' => // (191) ISO 3166-1 alpha-2
    [
        'name'         => 'Denmark',
        'rusName'      => 'Дания',
        'languages'    => ['da'],
        'iso3166_1_a2' => 'DK',
        'iso3166_1_a3' => 'DNK',
        'iso3166_1_n'  => '208',
        'iso3166_2'    => 'ISO 3166-2:DK',
        'gost7_67'     => ['cyrillic' => 'ДАН', 'numeric' => '208'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Flag_of_Denmark.svg/22px-Flag_of_Denmark.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Flag_of_Denmark.svg/320px-Flag_of_Denmark.svg.png'
        ] 
    ],
    'DJ' => // (192) ISO 3166-1 alpha-2
    [
        'name'         => 'Djibouti',
        'rusName'      => 'Джибути',
        'languages'    => ['ar', 'fr', 'so'],
        'iso3166_1_a2' => 'DJ',
        'iso3166_1_a3' => 'DJI',
        'iso3166_1_n'  => '262',
        'iso3166_2'    => 'ISO 3166-2:DJ',
        'gost7_67'     => ['cyrillic' => 'ДЖИ', 'numeric' => '262'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/34/Flag_of_Djibouti.svg/22px-Flag_of_Djibouti.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/34/Flag_of_Djibouti.svg/320px-Flag_of_Djibouti.svg.png'
        ] 
    ],
    'DE' => // (193) ISO 3166-1 alpha-2
    [
        'name'         => 'Germany',
        'rusName'      => 'Германия',
        'languages'    => ['de'],
        'iso3166_1_a2' => 'DE',
        'iso3166_1_a3' => 'DEU',
        'iso3166_1_n'  => '276',
        'iso3166_2'    => 'ISO 3166-2:DE',
        'gost7_67'     => ['cyrillic' => 'ГЕР', 'numeric' => '276'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Flag_of_Germany.svg/22px-Flag_of_Germany.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Flag_of_Germany.svg/320px-Flag_of_Germany.svg.png'
        ] 
    ],
    'CZ' => // (194) ISO 3166-1 alpha-2
    [
        'name'         => 'Czechia',
        'rusName'      => 'Чехия',
        'languages'    => ['cs'],
        'iso3166_1_a2' => 'CZ',
        'iso3166_1_a3' => 'CZE',
        'iso3166_1_n'  => '203',
        'iso3166_2'    => 'ISO 3166-2:CZ',
        'gost7_67'     => ['cyrillic' => 'ЧЕШ', 'numeric' => '203'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Flag_of_the_Czech_Republic.svg/22px-Flag_of_the_Czech_Republic.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Flag_of_the_Czech_Republic.svg/320px-Flag_of_the_Czech_Republic.svg.png'
        ] 
    ],
    'CY' => // (195) ISO 3166-1 alpha-2
    [
        'name'         => 'Cyprus',
        'rusName'      => 'Кипр',
        'languages'    => ['el', 'tr'],
        'iso3166_1_a2' => 'CY',
        'iso3166_1_a3' => 'CYP',
        'iso3166_1_n'  => '196',
        'iso3166_2'    => 'ISO 3166-2:CY',
        'gost7_67'     => ['cyrillic' => 'КИП', 'numeric' => '196'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Flag_of_Cyprus.svg/22px-Flag_of_Cyprus.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Flag_of_Cyprus.svg/320px-Flag_of_Cyprus.svg.png'
        ] 
    ],
    'CX' => // (196) ISO 3166-1 alpha-2
    [
        'name'         => 'Christmas Island',
        'rusName'      => 'о-в Рождества',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'CX',
        'iso3166_1_a3' => 'CXR',
        'iso3166_1_n'  => '162',
        'iso3166_2'    => 'ISO 3166-2:CX',
        'gost7_67'     => ['cyrillic' => 'РОЖ', 'numeric' => '162'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/67/Flag_of_Christmas_Island.svg/22px-Flag_of_Christmas_Island.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/67/Flag_of_Christmas_Island.svg/320px-Flag_of_Christmas_Island.svg.png'
        ] 
    ],
    'CW' => // (197) ISO 3166-1 alpha-2
    [
        'name'         => 'Curaçao',
        'rusName'      => 'Кюрасао',
        'languages'    => ['nl'],
        'iso3166_1_a2' => 'CW',
        'iso3166_1_a3' => 'CUW',
        'iso3166_1_n'  => '531',
        'iso3166_2'    => 'ISO 3166-2:CW',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '531'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Flag_of_Cura%C3%A7ao.svg/22px-Flag_of_Cura%C3%A7ao.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Flag_of_Cura%C3%A7ao.svg/320px-Flag_of_Cura%C3%A7ao.svg.png'
        ] 
    ],
    'CV' => // (198) ISO 3166-1 alpha-2
    [
        'name'         => 'Cape Verde',
        'rusName'      => 'Кабо-Верде',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'CV',
        'iso3166_1_a3' => 'CPV',
        'iso3166_1_n'  => '132',
        'iso3166_2'    => 'ISO 3166-2:CV',
        'gost7_67'     => ['cyrillic' => 'КАБ', 'numeric' => '132'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Cape_Verde.svg/22px-Flag_of_Cape_Verde.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/38/Flag_of_Cape_Verde.svg/320px-Flag_of_Cape_Verde.svg.png'
        ] 
    ],
    'CU' => // (199) ISO 3166-1 alpha-2
    [
        'name'         => 'Cuba',
        'rusName'      => 'Куба',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'CU',
        'iso3166_1_a3' => 'CUB',
        'iso3166_1_n'  => '192',
        'iso3166_2'    => 'ISO 3166-2:CU',
        'gost7_67'     => ['cyrillic' => 'КУБ', 'numeric' => '192'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Flag_of_Cuba.svg/22px-Flag_of_Cuba.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Flag_of_Cuba.svg/320px-Flag_of_Cuba.svg.png'
        ] 
    ],
    'CR' => // (200) ISO 3166-1 alpha-2
    [
        'name'         => 'Costa Rica',
        'rusName'      => 'Коста-Рика',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'CR',
        'iso3166_1_a3' => 'CRI',
        'iso3166_1_n'  => '188',
        'iso3166_2'    => 'ISO 3166-2:CR',
        'gost7_67'     => ['cyrillic' => 'КОС', 'numeric' => '188'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Costa_Rica_%28state%29.svg/22px-Flag_of_Costa_Rica_%28state%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Flag_of_Costa_Rica_%28state%29.svg/320px-Flag_of_Costa_Rica_%28state%29.svg.png'
        ] 
    ],
    'CO' => // (201) ISO 3166-1 alpha-2
    [
        'name'         => 'Colombia',
        'rusName'      => 'Колумбия',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'CO',
        'iso3166_1_a3' => 'COL',
        'iso3166_1_n'  => '170',
        'iso3166_2'    => 'ISO 3166-2:CO',
        'gost7_67'     => ['cyrillic' => 'КОЛ', 'numeric' => '170'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Colombia.svg/22px-Flag_of_Colombia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Colombia.svg/320px-Flag_of_Colombia.svg.png'
        ] 
    ],
    'CN' => // (202) ISO 3166-1 alpha-2
    [
        'name'         => 'China',
        'rusName'      => 'Китай',
        'languages'    => ['ii', 'zh', 'bo', 'ug'],
        'iso3166_1_a2' => 'CN',
        'iso3166_1_a3' => 'CHN',
        'iso3166_1_n'  => '156',
        'iso3166_2'    => 'ISO 3166-2:CN',
        'gost7_67'     => ['cyrillic' => 'КИТ', 'numeric' => '156'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_the_People%27s_Republic_of_China.svg/22px-Flag_of_the_People%27s_Republic_of_China.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_the_People%27s_Republic_of_China.svg/320px-Flag_of_the_People%27s_Republic_of_China.svg.png'
        ] 
    ],
    'CM' => // (203) ISO 3166-1 alpha-2
    [
        'name'         => 'Cameroon',
        'rusName'      => 'Камерун',
        'languages'    => ['en', 'fr', 'ff'],
        'iso3166_1_a2' => 'CM',
        'iso3166_1_a3' => 'CMR',
        'iso3166_1_n'  => '120',
        'iso3166_2'    => 'ISO 3166-2:CM',
        'gost7_67'     => ['cyrillic' => 'КАМ', 'numeric' => '120'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Flag_of_Cameroon.svg/22px-Flag_of_Cameroon.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Flag_of_Cameroon.svg/320px-Flag_of_Cameroon.svg.png'
        ] 
    ],
    'CL' => // (204) ISO 3166-1 alpha-2
    [
        'name'         => 'Chile',
        'rusName'      => 'Чили',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'CL',
        'iso3166_1_a3' => 'CHL',
        'iso3166_1_n'  => '152',
        'iso3166_2'    => 'ISO 3166-2:CL',
        'gost7_67'     => ['cyrillic' => 'ЧИЛ', 'numeric' => '152'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/78/Flag_of_Chile.svg/22px-Flag_of_Chile.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/78/Flag_of_Chile.svg/320px-Flag_of_Chile.svg.png'
        ] 
    ],
    'CK' => // (205) ISO 3166-1 alpha-2
    [
        'name'         => 'Cook Islands',
        'rusName'      => 'Острова Кука',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'CK',
        'iso3166_1_a3' => 'COK',
        'iso3166_1_n'  => '184',
        'iso3166_2'    => 'ISO 3166-2:CK',
        'gost7_67'     => ['cyrillic' => 'КУК', 'numeric' => '184'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/35/Flag_of_the_Cook_Islands.svg/22px-Flag_of_the_Cook_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/35/Flag_of_the_Cook_Islands.svg/320px-Flag_of_the_Cook_Islands.svg.png'
        ] 
    ],
    'CI' => // (206) ISO 3166-1 alpha-2
    [
        'name'         => 'Côte d’Ivoire',
        'rusName'      => 'Кот-д’Ивуар',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'CI',
        'iso3166_1_a3' => 'CIV',
        'iso3166_1_n'  => '384',
        'iso3166_2'    => 'ISO 3166-2:CI',
        'gost7_67'     => ['cyrillic' => 'КОТ', 'numeric' => '384'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_C%C3%B4te_d%27Ivoire.svg/22px-Flag_of_C%C3%B4te_d%27Ivoire.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_C%C3%B4te_d%27Ivoire.svg/320px-Flag_of_C%C3%B4te_d%27Ivoire.svg.png'
        ] 
    ],
    'CH' => // (207) ISO 3166-1 alpha-2
    [
        'name'         => 'Switzerland',
        'rusName'      => 'Швейцария',
        'languages'    => ['de', 'fr', 'it', 'rm'],
        'iso3166_1_a2' => 'CH',
        'iso3166_1_a3' => 'CHE',
        'iso3166_1_n'  => '756',
        'iso3166_2'    => 'ISO 3166-2:CH',
        'gost7_67'     => ['cyrillic' => 'ШВА', 'numeric' => '756'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f3/Flag_of_Switzerland.svg/20px-Flag_of_Switzerland.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f3/Flag_of_Switzerland.svg/20px-Flag_of_Switzerland.svg.png'
        ] 
    ],
    'CG' => // (208) ISO 3166-1 alpha-2
    [
        'name'         => 'Congo - Brazzaville',
        'rusName'      => 'Конго - Браззавиль',
        'languages'    => ['fr', 'ln'],
        'iso3166_1_a2' => 'CG',
        'iso3166_1_a3' => 'COG',
        'iso3166_1_n'  => '178',
        'iso3166_2'    => 'ISO 3166-2:CG',
        'gost7_67'     => ['cyrillic' => 'КОН', 'numeric' => '178'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_the_Republic_of_the_Congo.svg/22px-Flag_of_the_Republic_of_the_Congo.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_the_Republic_of_the_Congo.svg/320px-Flag_of_the_Republic_of_the_Congo.svg.png'
        ] 
    ],
    'CF' => // (209) ISO 3166-1 alpha-2
    [
        'name'         => 'Central African Republic',
        'rusName'      => 'Центрально-Африканская Республика',
        'languages'    => ['fr', 'ln', 'sg'],
        'iso3166_1_a2' => 'CF',
        'iso3166_1_a3' => 'CAF',
        'iso3166_1_n'  => '140',
        'iso3166_2'    => 'ISO 3166-2:CF',
        'gost7_67'     => ['cyrillic' => 'ЦЕН', 'numeric' => '140'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Flag_of_the_Central_African_Republic.svg/22px-Flag_of_the_Central_African_Republic.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Flag_of_the_Central_African_Republic.svg/320px-Flag_of_the_Central_African_Republic.svg.png'
        ] 
    ],
    'CD' => // (210) ISO 3166-1 alpha-2
    [
        'name'         => 'Congo - Kinshasa',
        'rusName'      => 'Конго - Киншаса',
        'languages'    => ['fr', 'ln', 'lu'],
        'iso3166_1_a2' => 'CD',
        'iso3166_1_a3' => 'COD',
        'iso3166_1_n'  => '180',
        'iso3166_2'    => 'ISO 3166-2:CD',
        'gost7_67'     => ['cyrillic' => 'КОО', 'numeric' => '180'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Flag_of_the_Democratic_Republic_of_the_Congo.svg/22px-Flag_of_the_Democratic_Republic_of_the_Congo.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Flag_of_the_Democratic_Republic_of_the_Congo.svg/320px-Flag_of_the_Democratic_Republic_of_the_Congo.svg.png'
        ] 
    ],
    'CC' => // (211) ISO 3166-1 alpha-2
    [
        'name'         => 'Cocos (Keeling) Islands',
        'rusName'      => 'Кокосовые о-ва',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'CC',
        'iso3166_1_a3' => 'CCK',
        'iso3166_1_n'  => '166',
        'iso3166_2'    => 'ISO 3166-2:CC',
        'gost7_67'     => ['cyrillic' => 'КОК', 'numeric' => '166'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_the_Cocos_%28Keeling%29_Islands.svg/22px-Flag_of_the_Cocos_%28Keeling%29_Islands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/74/Flag_of_the_Cocos_%28Keeling%29_Islands.svg/320px-Flag_of_the_Cocos_%28Keeling%29_Islands.svg.png'
        ] 
    ],
    'CA' => // (212) ISO 3166-1 alpha-2
    [
        'name'         => 'Canada',
        'rusName'      => 'Канада',
        'languages'    => ['en', 'fr'],
        'iso3166_1_a2' => 'CA',
        'iso3166_1_a3' => 'CAN',
        'iso3166_1_n'  => '124',
        'iso3166_2'    => 'ISO 3166-2:CA',
        'gost7_67'     => ['cyrillic' => 'КАН', 'numeric' => '124'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Canada_%28Pantone%29.svg/22px-Flag_of_Canada_%28Pantone%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Canada_%28Pantone%29.svg/320px-Flag_of_Canada_%28Pantone%29.svg.png'
        ] 
    ],
    'BZ' => // (213) ISO 3166-1 alpha-2
    [
        'name'         => 'Belize',
        'rusName'      => 'Белиз',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'BZ',
        'iso3166_1_a3' => 'BLZ',
        'iso3166_1_n'  => '084',
        'iso3166_2'    => 'ISO 3166-2:BZ',
        'gost7_67'     => ['cyrillic' => 'БЕЗ', 'numeric' => '084'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Flag_of_Belize.svg/22px-Flag_of_Belize.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Flag_of_Belize.svg/320px-Flag_of_Belize.svg.png'
        ] 
    ],
    'BY' => // (214) ISO 3166-1 alpha-2
    [
        'name'         => 'Belarus',
        'rusName'      => 'Беларусь',
        'languages'    => ['ru', 'be'],
        'iso3166_1_a2' => 'BY',
        'iso3166_1_a3' => 'BLR',
        'iso3166_1_n'  => '112',
        'iso3166_2'    => 'ISO 3166-2:BY',
        'gost7_67'     => ['cyrillic' => 'БЕИ', 'numeric' => '112'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/85/Flag_of_Belarus.svg/22px-Flag_of_Belarus.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/85/Flag_of_Belarus.svg/320px-Flag_of_Belarus.svg.png'
        ] 
    ],
    'BW' => // (215) ISO 3166-1 alpha-2
    [
        'name'         => 'Botswana',
        'rusName'      => 'Ботсвана',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'BW',
        'iso3166_1_a3' => 'BWA',
        'iso3166_1_n'  => '072',
        'iso3166_2'    => 'ISO 3166-2:BW',
        'gost7_67'     => ['cyrillic' => 'БОТ', 'numeric' => '072'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_Botswana.svg/22px-Flag_of_Botswana.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Flag_of_Botswana.svg/320px-Flag_of_Botswana.svg.png'
        ] 
    ],
    'BV' => // (216) ISO 3166-1 alpha-2
    [
        'name'         => 'Bouvet Island',
        'rusName'      => 'о-в Буве',
        'languages'    => [],
        'iso3166_1_a2' => 'BV',
        'iso3166_1_a3' => 'BVT',
        'iso3166_1_n'  => '074',
        'iso3166_2'    => 'ISO 3166-2:BV',
        'gost7_67'     => ['cyrillic' => 'БУВ', 'numeric' => '074'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/22px-Flag_of_Norway.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Flag_of_Norway.svg/320px-Flag_of_Norway.svg.png'
        ] 
    ],
    'BT' => // (217) ISO 3166-1 alpha-2
    [
        'name'         => 'Bhutan',
        'rusName'      => 'Бутан',
        'languages'    => ['dz'],
        'iso3166_1_a2' => 'BT',
        'iso3166_1_a3' => 'BTN',
        'iso3166_1_n'  => '064',
        'iso3166_2'    => 'ISO 3166-2:BT',
        'gost7_67'     => ['cyrillic' => 'БУТ', 'numeric' => '064'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/91/Flag_of_Bhutan.svg/22px-Flag_of_Bhutan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/91/Flag_of_Bhutan.svg/320px-Flag_of_Bhutan.svg.png'
        ] 
    ],
    'BS' => // (218) ISO 3166-1 alpha-2
    [
        'name'         => 'Bahamas',
        'rusName'      => 'Багамы',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'BS',
        'iso3166_1_a3' => 'BHS',
        'iso3166_1_n'  => '044',
        'iso3166_2'    => 'ISO 3166-2:BS',
        'gost7_67'     => ['cyrillic' => 'БАГ', 'numeric' => '044'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Bahamas_Flag.svg/22px-Bahamas_Flag.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/7/77/Bahamas_Flag.svg/320px-Bahamas_Flag.svg.png'
        ] 
    ],
    'BR' => // (219) ISO 3166-1 alpha-2
    [
        'name'         => 'Brazil',
        'rusName'      => 'Бразилия',
        'languages'    => ['pt'],
        'iso3166_1_a2' => 'BR',
        'iso3166_1_a3' => 'BRA',
        'iso3166_1_n'  => '076',
        'iso3166_2'    => 'ISO 3166-2:BR',
        'gost7_67'     => ['cyrillic' => 'БРА', 'numeric' => '076'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/22px-Flag_of_Brazil.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/320px-Flag_of_Brazil.svg.png'
        ] 
    ],
    'BQ' => // (220) ISO 3166-1 alpha-2
    [
        'name'         => 'Caribbean Netherlands',
        'rusName'      => 'Бонэйр, Синт-Эстатиус и Саба',
        'languages'    => ['nl'],
        'iso3166_1_a2' => 'BQ',
        'iso3166_1_a3' => 'BES',
        'iso3166_1_n'  => '535',
        'iso3166_2'    => 'ISO 3166-2:BQ',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '535'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/20/Flag_of_the_Netherlands.svg/22px-Flag_of_the_Netherlands.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/20/Flag_of_the_Netherlands.svg/320px-Flag_of_the_Netherlands.svg.png'
        ] 
    ],
    'BO' => // (221) ISO 3166-1 alpha-2
    [
        'name'         => 'Bolivia',
        'rusName'      => 'Боливия',
        'languages'    => ['qu', 'es'],
        'iso3166_1_a2' => 'BO',
        'iso3166_1_a3' => 'BOL',
        'iso3166_1_n'  => '068',
        'iso3166_2'    => 'ISO 3166-2:BO',
        'gost7_67'     => ['cyrillic' => 'БОЛ', 'numeric' => '068'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/de/Flag_of_Bolivia_%28state%29.svg/22px-Flag_of_Bolivia_%28state%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/de/Flag_of_Bolivia_%28state%29.svg/320px-Flag_of_Bolivia_%28state%29.svg.png'
        ] 
    ],
    'BN' => // (222) ISO 3166-1 alpha-2
    [
        'name'         => 'Brunei',
        'rusName'      => 'Бруней-Даруссалам',
        'languages'    => ['ms'],
        'iso3166_1_a2' => 'BN',
        'iso3166_1_a3' => 'BRN',
        'iso3166_1_n'  => '096',
        'iso3166_2'    => 'ISO 3166-2:BN',
        'gost7_67'     => ['cyrillic' => 'БРУ', 'numeric' => '096'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Flag_of_Brunei.svg/22px-Flag_of_Brunei.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Flag_of_Brunei.svg/320px-Flag_of_Brunei.svg.png'
        ] 
    ],
    'BM' => // (223) ISO 3166-1 alpha-2
    [
        'name'         => 'Bermuda',
        'rusName'      => 'Бермудские о-ва',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'BM',
        'iso3166_1_a3' => 'BMU',
        'iso3166_1_n'  => '060',
        'iso3166_2'    => 'ISO 3166-2:BM',
        'gost7_67'     => ['cyrillic' => 'БЕР', 'numeric' => '060'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Flag_of_Bermuda.svg/22px-Flag_of_Bermuda.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Flag_of_Bermuda.svg/320px-Flag_of_Bermuda.svg.png'
        ] 
    ],
    'BL' => // (224) ISO 3166-1 alpha-2
    [
        'name'         => 'St. Barthélemy',
        'rusName'      => 'Сен-Бартелеми',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'BL',
        'iso3166_1_a3' => 'BLM',
        'iso3166_1_n'  => '652',
        'iso3166_2'    => 'ISO 3166-2:BL',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '652'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Saint_Barth%C3%A9lemy_%28local%29.svg/22px-Flag_of_Saint_Barth%C3%A9lemy_%28local%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Saint_Barth%C3%A9lemy_%28local%29.svg/320px-Flag_of_Saint_Barth%C3%A9lemy_%28local%29.svg.png'
        ] 
    ],
    'BJ' => // (225) ISO 3166-1 alpha-2
    [
        'name'         => 'Benin',
        'rusName'      => 'Бенин',
        'languages'    => ['fr', 'yo'],
        'iso3166_1_a2' => 'BJ',
        'iso3166_1_a3' => 'BEN',
        'iso3166_1_n'  => '204',
        'iso3166_2'    => 'ISO 3166-2:BJ',
        'gost7_67'     => ['cyrillic' => 'БЕН', 'numeric' => '204'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Flag_of_Benin.svg/22px-Flag_of_Benin.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Flag_of_Benin.svg/320px-Flag_of_Benin.svg.png'
        ] 
    ],
    'BI' => // (226) ISO 3166-1 alpha-2
    [
        'name'         => 'Burundi',
        'rusName'      => 'Бурунди',
        'languages'    => ['fr', 'rn'],
        'iso3166_1_a2' => 'BI',
        'iso3166_1_a3' => 'BDI',
        'iso3166_1_n'  => '108',
        'iso3166_2'    => 'ISO 3166-2:BI',
        'gost7_67'     => ['cyrillic' => 'БУР', 'numeric' => '108'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/50/Flag_of_Burundi.svg/22px-Flag_of_Burundi.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/50/Flag_of_Burundi.svg/320px-Flag_of_Burundi.svg.png'
        ] 
    ],
    'BH' => // (227) ISO 3166-1 alpha-2
    [
        'name'         => 'Bahrain',
        'rusName'      => 'Бахрейн',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'BH',
        'iso3166_1_a3' => 'BHR',
        'iso3166_1_n'  => '048',
        'iso3166_2'    => 'ISO 3166-2:BH',
        'gost7_67'     => ['cyrillic' => 'БАХ', 'numeric' => '048'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Bahrain.svg/22px-Flag_of_Bahrain.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2c/Flag_of_Bahrain.svg/320px-Flag_of_Bahrain.svg.png'
        ] 
    ],
    'BG' => // (228) ISO 3166-1 alpha-2
    [
        'name'         => 'Bulgaria',
        'rusName'      => 'Болгария',
        'languages'    => ['bg'],
        'iso3166_1_a2' => 'BG',
        'iso3166_1_a3' => 'BGR',
        'iso3166_1_n'  => '100',
        'iso3166_2'    => 'ISO 3166-2:BG',
        'gost7_67'     => ['cyrillic' => 'БОГ', 'numeric' => '100'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Bulgaria.svg/22px-Flag_of_Bulgaria.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Bulgaria.svg/320px-Flag_of_Bulgaria.svg.png'
        ] 
    ],
    'BF' => // (229) ISO 3166-1 alpha-2
    [
        'name'         => 'Burkina Faso',
        'rusName'      => 'Буркина-Фасо',
        'languages'    => ['fr'],
        'iso3166_1_a2' => 'BF',
        'iso3166_1_a3' => 'BFA',
        'iso3166_1_n'  => '854',
        'iso3166_2'    => 'ISO 3166-2:BF',
        'gost7_67'     => ['cyrillic' => 'БУК', 'numeric' => '854'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Burkina_Faso.svg/22px-Flag_of_Burkina_Faso.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/31/Flag_of_Burkina_Faso.svg/320px-Flag_of_Burkina_Faso.svg.png'
        ] 
    ],
    'BE' => // (230) ISO 3166-1 alpha-2
    [
        'name'         => 'Belgium',
        'rusName'      => 'Бельгия',
        'languages'    => ['de', 'en', 'fr', 'nl'],
        'iso3166_1_a2' => 'BE',
        'iso3166_1_a3' => 'BEL',
        'iso3166_1_n'  => '056',
        'iso3166_2'    => 'ISO 3166-2:BE',
        'gost7_67'     => ['cyrillic' => 'БЕЛ', 'numeric' => '056'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_Belgium_%28civil%29.svg/22px-Flag_of_Belgium_%28civil%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/92/Flag_of_Belgium_%28civil%29.svg/320px-Flag_of_Belgium_%28civil%29.svg.png'
        ] 
    ],
    'BD' => // (231) ISO 3166-1 alpha-2
    [
        'name'         => 'Bangladesh',
        'rusName'      => 'Бангладеш',
        'languages'    => ['bn'],
        'iso3166_1_a2' => 'BD',
        'iso3166_1_a3' => 'BGD',
        'iso3166_1_n'  => '050',
        'iso3166_2'    => 'ISO 3166-2:BD',
        'gost7_67'     => ['cyrillic' => 'БАН', 'numeric' => '050'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Flag_of_Bangladesh.svg/22px-Flag_of_Bangladesh.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Flag_of_Bangladesh.svg/320px-Flag_of_Bangladesh.svg.png'
        ] 
    ],
    'BB' => // (232) ISO 3166-1 alpha-2
    [
        'name'         => 'Barbados',
        'rusName'      => 'Барбадос',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'BB',
        'iso3166_1_a3' => 'BRB',
        'iso3166_1_n'  => '052',
        'iso3166_2'    => 'ISO 3166-2:BB',
        'gost7_67'     => ['cyrillic' => 'БАР', 'numeric' => '052'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ef/Flag_of_Barbados.svg/22px-Flag_of_Barbados.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/e/ef/Flag_of_Barbados.svg/320px-Flag_of_Barbados.svg.png'
        ] 
    ],
    'BA' => // (233) ISO 3166-1 alpha-2
    [
        'name'         => 'Bosnia & Herzegovina',
        'rusName'      => 'Босния и Герцеговина',
        'languages'    => ['bs', 'hr', 'sh', 'sr'],
        'iso3166_1_a2' => 'BA',
        'iso3166_1_a3' => 'BIH',
        'iso3166_1_n'  => '070',
        'iso3166_2'    => 'ISO 3166-2:BA',
        'gost7_67'     => ['cyrillic' => 'БОС', 'numeric' => '070'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Flag_of_Bosnia_and_Herzegovina.svg/22px-Flag_of_Bosnia_and_Herzegovina.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Flag_of_Bosnia_and_Herzegovina.svg/320px-Flag_of_Bosnia_and_Herzegovina.svg.png'
        ] 
    ],
    'AZ' => // (234) ISO 3166-1 alpha-2
    [
        'name'         => 'Azerbaijan',
        'rusName'      => 'Азербайджан',
        'languages'    => ['az'],
        'iso3166_1_a2' => 'AZ',
        'iso3166_1_a3' => 'AZE',
        'iso3166_1_n'  => '031',
        'iso3166_2'    => 'ISO 3166-2:AZ',
        'gost7_67'     => ['cyrillic' => 'АЗЕ', 'numeric' => '031'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Azerbaijan.svg/22px-Flag_of_Azerbaijan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Flag_of_Azerbaijan.svg/320px-Flag_of_Azerbaijan.svg.png'
        ] 
    ],
    'AX' => // (235) ISO 3166-1 alpha-2
    [
        'name'         => 'Åland Islands',
        'rusName'      => 'Аландские о-ва',
        'languages'    => ['sv'],
        'iso3166_1_a2' => 'AX',
        'iso3166_1_a3' => 'ALA',
        'iso3166_1_n'  => '248',
        'iso3166_2'    => 'ISO 3166-2:AX',
        'gost7_67'     => ['cyrillic' => '', 'numeric' => '248'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/5/52/Flag_of_%C3%85land.svg/22px-Flag_of_%C3%85land.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/5/52/Flag_of_%C3%85land.svg/320px-Flag_of_%C3%85land.svg.png'
        ] 
    ],
    'AW' => // (236) ISO 3166-1 alpha-2
    [
        'name'         => 'Aruba',
        'rusName'      => 'Аруба',
        'languages'    => ['nl'],
        'iso3166_1_a2' => 'AW',
        'iso3166_1_a3' => 'ABW',
        'iso3166_1_n'  => '533',
        'iso3166_2'    => 'ISO 3166-2:AW',
        'gost7_67'     => ['cyrillic' => 'АРУ', 'numeric' => '533'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Flag_of_Aruba.svg/22px-Flag_of_Aruba.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Flag_of_Aruba.svg/320px-Flag_of_Aruba.svg.png'
        ] 
    ],
    'AU' => // (237) ISO 3166-1 alpha-2
    [
        'name'         => 'Australia',
        'rusName'      => 'Австралия',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'AU',
        'iso3166_1_a3' => 'AUS',
        'iso3166_1_n'  => '036',
        'iso3166_2'    => 'ISO 3166-2:AU',
        'gost7_67'     => ['cyrillic' => 'АВС', 'numeric' => '036'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Flag_of_Australia.svg/22px-Flag_of_Australia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b9/Flag_of_Australia.svg/320px-Flag_of_Australia.svg.png'
        ] 
    ],
    'AT' => // (238) ISO 3166-1 alpha-2
    [
        'name'         => 'Austria',
        'rusName'      => 'Австрия',
        'languages'    => ['de'],
        'iso3166_1_a2' => 'AT',
        'iso3166_1_a3' => 'AUT',
        'iso3166_1_n'  => '040',
        'iso3166_2'    => 'ISO 3166-2:AT',
        'gost7_67'     => ['cyrillic' => 'АВТ', 'numeric' => '040'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Austria.svg/22px-Flag_of_Austria.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Austria.svg/320px-Flag_of_Austria.svg.png'
        ] 
    ],
    'AS' => // (239) ISO 3166-1 alpha-2
    [
        'name'         => 'American Samoa',
        'rusName'      => 'Американское Самоа',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'AS',
        'iso3166_1_a3' => 'ASM',
        'iso3166_1_n'  => '016',
        'iso3166_2'    => 'ISO 3166-2:AS',
        'gost7_67'     => ['cyrillic' => 'ВОС', 'numeric' => '016'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/87/Flag_of_American_Samoa.svg/22px-Flag_of_American_Samoa.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/87/Flag_of_American_Samoa.svg/320px-Flag_of_American_Samoa.svg.png'
        ] 
    ],
    'AR' => // (240) ISO 3166-1 alpha-2
    [
        'name'         => 'Argentina',
        'rusName'      => 'Аргентина',
        'languages'    => ['es'],
        'iso3166_1_a2' => 'AR',
        'iso3166_1_a3' => 'ARG',
        'iso3166_1_n'  => '032',
        'iso3166_2'    => 'ISO 3166-2:AR',
        'gost7_67'     => ['cyrillic' => 'АРГ', 'numeric' => '032'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Flag_of_Argentina.svg/22px-Flag_of_Argentina.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Flag_of_Argentina.svg/320px-Flag_of_Argentina.svg.png'
        ] 
    ],
    'AQ' => // (241) ISO 3166-1 alpha-2
    [
        'name'         => 'Antarctica',
        'rusName'      => 'Антарктида',
        'languages'    => [],
        'iso3166_1_a2' => 'AQ',
        'iso3166_1_a3' => 'ATA',
        'iso3166_1_n'  => '010',
        'iso3166_2'    => 'ISO 3166-2:AQ',
        'gost7_67'     => ['cyrillic' => 'АНК', 'numeric' => '010'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bb/Proposed_flag_of_Antarctica_%28Graham_Bartram%29.svg/22px-Proposed_flag_of_Antarctica_%28Graham_Bartram%29.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/bb/Proposed_flag_of_Antarctica_%28Graham_Bartram%29.svg/320px-Proposed_flag_of_Antarctica_%28Graham_Bartram%29.svg.png'
        ] 
    ],
    'AO' => // (242) ISO 3166-1 alpha-2
    [
        'name'         => 'Angola',
        'rusName'      => 'Ангола',
        'languages'    => ['ln', 'pt'],
        'iso3166_1_a2' => 'AO',
        'iso3166_1_a3' => 'AGO',
        'iso3166_1_n'  => '024',
        'iso3166_2'    => 'ISO 3166-2:AO',
        'gost7_67'     => ['cyrillic' => 'АНГ', 'numeric' => '024'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Flag_of_Angola.svg/22px-Flag_of_Angola.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Flag_of_Angola.svg/320px-Flag_of_Angola.svg.png'
        ] 
    ],
    'AM' => // (243) ISO 3166-1 alpha-2
    [
        'name'         => 'Armenia',
        'rusName'      => 'Армения',
        'languages'    => ['hy'],
        'iso3166_1_a2' => 'AM',
        'iso3166_1_a3' => 'ARM',
        'iso3166_1_n'  => '051',
        'iso3166_2'    => 'ISO 3166-2:AM',
        'gost7_67'     => ['cyrillic' => 'АРМ', 'numeric' => '051'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Flag_of_Armenia.svg/22px-Flag_of_Armenia.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Flag_of_Armenia.svg/320px-Flag_of_Armenia.svg.png'
        ] 
    ],
    'AL' => // (244) ISO 3166-1 alpha-2
    [
        'name'         => 'Albania',
        'rusName'      => 'Албания',
        'languages'    => ['sq'],
        'iso3166_1_a2' => 'AL',
        'iso3166_1_a3' => 'ALB',
        'iso3166_1_n'  => '008',
        'iso3166_2'    => 'ISO 3166-2:AL',
        'gost7_67'     => ['cyrillic' => 'АЛБ', 'numeric' => '008'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/3/36/Flag_of_Albania.svg/22px-Flag_of_Albania.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/3/36/Flag_of_Albania.svg/320px-Flag_of_Albania.svg.png'
        ] 
    ],
    'AI' => // (245) ISO 3166-1 alpha-2
    [
        'name'         => 'Anguilla',
        'rusName'      => 'Ангилья',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'AI',
        'iso3166_1_a3' => 'AIA',
        'iso3166_1_n'  => '660',
        'iso3166_2'    => 'ISO 3166-2:AI',
        'gost7_67'     => ['cyrillic' => 'АНА', 'numeric' => '660'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Anguilla.svg/22px-Flag_of_Anguilla.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Flag_of_Anguilla.svg/320px-Flag_of_Anguilla.svg.png'
        ] 
    ],
    'AG' => // (246) ISO 3166-1 alpha-2
    [
        'name'         => 'Antigua & Barbuda',
        'rusName'      => 'Антигуа и Барбуда',
        'languages'    => ['en'],
        'iso3166_1_a2' => 'AG',
        'iso3166_1_a3' => 'ATG',
        'iso3166_1_n'  => '028',
        'iso3166_2'    => 'ISO 3166-2:AG',
        'gost7_67'     => ['cyrillic' => 'АНР', 'numeric' => '028'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/8/89/Flag_of_Antigua_and_Barbuda.svg/22px-Flag_of_Antigua_and_Barbuda.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/8/89/Flag_of_Antigua_and_Barbuda.svg/320px-Flag_of_Antigua_and_Barbuda.svg.png'
        ] 
    ],
    'AF' => // (247) ISO 3166-1 alpha-2
    [
        'name'         => 'Afghanistan',
        'rusName'      => 'Афганистан',
        'languages'    => ['uz', 'ps', 'fa'],
        'iso3166_1_a2' => 'AF',
        'iso3166_1_a3' => 'AFG',
        'iso3166_1_n'  => '004',
        'iso3166_2'    => 'ISO 3166-2:AF',
        'gost7_67'     => ['cyrillic' => 'АФГ', 'numeric' => '004'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Afghanistan.svg/22px-Flag_of_Afghanistan.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Flag_of_Afghanistan.svg/320px-Flag_of_Afghanistan.svg.png'
        ] 
    ],
    'AE' => // (248) ISO 3166-1 alpha-2
    [
        'name'         => 'United Arab Emirates',
        'rusName'      => 'ОАЭ',
        'languages'    => ['ar'],
        'iso3166_1_a2' => 'AE',
        'iso3166_1_a3' => 'ARE',
        'iso3166_1_n'  => '784',
        'iso3166_2'    => 'ISO 3166-2:AE',
        'gost7_67'     => ['cyrillic' => 'ОБЭ', 'numeric' => '784'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Flag_of_the_United_Arab_Emirates.svg/22px-Flag_of_the_United_Arab_Emirates.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Flag_of_the_United_Arab_Emirates.svg/320px-Flag_of_the_United_Arab_Emirates.svg.png'
        ] 
    ],
    'AD' => // (249) ISO 3166-1 alpha-2
    [
        'name'         => 'Andorra',
        'rusName'      => 'Андорра',
        'languages'    => ['ca'],
        'iso3166_1_a2' => 'AD',
        'iso3166_1_a3' => 'AND',
        'iso3166_1_n'  => '020',
        'iso3166_2'    => 'ISO 3166-2:AD',
        'gost7_67'     => ['cyrillic' => 'АНД', 'numeric' => '020'],
        'flag'           => [
            'thumb' => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Andorra.svg/22px-Flag_of_Andorra.svg.png',
            'img'   => '//upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Andorra.svg/320px-Flag_of_Andorra.svg.png'
        ] 
    ]
];