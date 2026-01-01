<?php
$functions = [
    'local_topico_ai_proxy_generate_program' => [
        'classname'   => 'local_topico_ai_proxy\external\generate_program',
        'methodname'  => 'execute',
        'classpath'   => 'local/topico_ai_proxy/classes/external/generate_program.php',
        'description' => 'Generates course program via AI Proxy',
        'type'        => 'write',
        'ajax'        => true, // Essential for Frontend JS
        'capabilities'=> 'moodle/course:update',
    ],
];

$services = [
    'topico_ai_proxy' => [
        'functions' => ['local_topico_ai_proxy_generate_program'],
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'topico_ai_proxy'
    ]
];
