<?php

require __DIR__ . '/vendor/autoload.php';

Predis\Autoloader::register();

$default_settings = [
  [
    'key'   => 'suspend',
    'text'  => 'Suspend',
    'value' => 1,
    'items' => null,
  ],
  [
    'key'   => 'modules',
    'text'  => 'Modules',
    'value' => null,
    'items' => [
      [
        'key'   => 'print_module',
        'text'  => 'Print Module',
        'value' => 1,
        'items' => null,
      ],
      [
        'key'   => 'analytics_module',
        'text'  => 'Advanced Analytics Module',
        'value' => 1,
        'items' => null,
      ],
      [
        'key'   => 'survey_module',
        'text'  => 'Client Survey',
        'value' => 0,
        'items' => null,
      ],
      [
        'key'   => 'tours_module',
        'text'  => 'Tours Module',
        'value' => 1,
        'items' => null,
      ],
      [
        'key'   => 'audio_module',
        'text'  => 'Audio',
        'value' => 0,
        'items' => null,
      ],
    ],
  ],
];

function redisOptions($settings) {

  global $redis ;

  foreach($settings as $k=>$s) {

    if(is_null($s['value'])) {

      $redis->del($s['key']);

    } else if($redis->exists($s['key'])) {

      $settings[$k]['value'] = $redis->get($s['key']);

    } else {

      $redis->set($s['key'], $s['value']);

    }

    if(!is_null($s['items'])) {

      $settings[$k]['items'] = redisOptions($s['items']) ;

    }

  }

  return $settings ;

}

$data = [] ;

$status = 0 ;

try {

  $redis = new Predis\Client();

  if(
    isset($_REQUEST['key'])
    && !empty($_REQUEST['key'])
    && isset($_REQUEST['value'])
  ) {

    $redis->set($_REQUEST['key'], $_REQUEST['value']);

    $data = [
      'key'   => $_REQUEST['key'],
      'value' => $redis->get($_REQUEST['key'])
    ];

  } else {

    $data = redisOptions($default_settings) ;

  }

} catch (Exception $e) {

  $status = 1;

}

header('Content-Type: application/json');
if($_GET['callback']) {

  echo $_GET['callback'] . '(' . json_encode(['status' => $status, 'data' => $data]) . ')' ;

} else {

  echo json_encode(['status' => $status, 'data' => $data]) ;

}

?>
