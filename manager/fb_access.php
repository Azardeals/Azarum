<?php

define('FB_APIKEY', '340481566014881');
define('FB_SECRET', 'be8087763ca558194d504b754330a35f');
define('FB_SESSION', '4.0.b9d7b900cba4a524a208d6f2.0-100000742659020');
require_once './facebook.php';
echo "post on wall";
try {
    $facebook = new Facebook(FB_APIKEY, FB_SECRET);
    $facebook->api_client->session_key = FB_SESSION;
    $fetch = ['friends' => ['pattern' => '.*', 'query' => "select uid2 from friend where uid1={$user}"]];
    echo $facebook->api_client->admin_setAppProperties(['preload_fql' => json_encode($fetch)]);
    $message = 'Check out great deals!';
    $attachment = ['name' => 'Glitz Hair Studio & Spa',
        'href' => 'http://bitfatdeals.fatbit.com/caracas/deal/187/glitz-hair-studio--spa',
        'caption' => 'Aroma/ Pollution shield facial',
        'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
        'properties' => ['Price' => '$50'],
        'media' => [['type' => 'image', 'src' => 'http://bitfatdeals.fatbit.com/deal-image.php?id=187&type=main', 'href' => 'http://bitfatdeals.fatbit.com/caracas/deal/187/glitz-hair-studio--spa']]];
    $facebook->api_client->stream_publish($message, $attachment, '', '376400939077819', '376400939077819'); //166050673515718
    echo "Added on FB Wall";
} catch (Exception $e) {
    echo $e . "<br />";
}
