<?php
require 'dotenv.php';
if (isset($_GET['prompt'])) {
	ignore_user_abort(true);
	set_time_limit(0);
	ob_start();
	// Initial processing
	echo isset($_GET['processing']) ? $_GET['processing'] : 'Processing...';
	header('Connection: close');
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
	if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
	$ch =  curl_init('https://api.openai.com/v1/chat/completions');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'User-Agent: Twitch Chat Proxy',
		'Content-Type: application/json;charset=UTF-8',
		"Authorization: Bearer {$OpenAI_API_Key}"
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
		'model' => 'gpt-3.5-turbo',
		'max_tokens' => $MAX_TOKENS,
		'messages' => [
			[
				'role' => 'user',
				'content' => $_GET['prompt']
			]
		]
	]));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($ch), true);
	curl_close($ch);
	$ch = curl_init("https://api.streamelements.com/kappa/v2/bot/" . $_SERVER['HTTP_X_STREAMELEMENTS_CHANNEL'] . "/say");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: application/json; charset=utf-8',
		'Authorization: Bearer ' . $SE_JWT_TOKEN,
		'Content-Type: application/json'
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
		'message' => (isset($_GET['prefix']) ? $_GET['prefix'] : '🤖') . substr($response['choices'][0]['message']['content'], 0, 440) // StreamElements seems to prevent messages of more than 500 chars. It seems like the limit is around 450
	]));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($ch), true);
	curl_close($ch);
} else {
	http_response_code(400);
	echo '400 Bad Request - Missing required parameter prompt';
}
?>
