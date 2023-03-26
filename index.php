<?php
require 'dotenv.php';
if (isset($_GET['prompt'])) {
	$ch =  curl_init('https://api.openai.com/v1/chat/completions');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'User-Agent: Twitch Chat Proxy',
		'Content-Type: application/json;charset=UTF-8',
		"Authorization: Bearer {$OpenAI_API_Key}"
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
		'model' => 'gpt-3.5-turbo',
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
	echo $response['choices'][0]['message']['content'];
} else {
	http_response_code(400);
	echo '400 Bad Request - Missing required parameter prompt';
}
?>
