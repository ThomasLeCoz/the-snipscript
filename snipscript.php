<?php
/****************************************************
*													*
*	SnipScript v 1.0 by Thomas Le Coz 				*
*		http://thomaslecoz.com 						*
*													*
*	Article with full explanations below 			*
*	http://thomaslecoz.com/get-leads-reading		*
*													*
*****************************************************/

/* #0 Params and stuff */
require_once 'class.bufferapp.php';

$your_security_key = '';
$your_sniply_campaign_id = '';
$your_buffer_token = '1/';
$your_buffer_profiles_ids = array('');

$auth_token_sniply = '';
$auth_token_buffer = '';

/* Most likely not to change soon, but still didn't
 * feel right to hardcode
 */
$sniply_linksapi_uri = 'https://snip.ly/api/v2/links/';
$buffer_updatesapi_uri = 'https://api.bufferapp.com/1/updates/create.json';


/* #0 Cheap security auth with no shame */
$sec_key = $_GET['key'];
if ($your_security_key != $sec_key)
	exit("Access Denied");

/* #1 Get Pocket Data from Zapier */
$article_url = $_POST['URL'];
$article_title = $_POST['Title'];
$article_excerpt = utf8_decode($_POST['Excerpt']);
$article_image = $_POST['Image'];

// Taking care of the smart quotes
$article_title = preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $article_title);
$article_excerpt = preg_replace('/\\\u([0-9a-z]{4})/', '&#x$1;', $article_excerpt);

if (empty($article_url) || empty($article_title))
	exit("Need at least a title and a URL");

/* #2 Send link to Snip.ly */

// The data
$postData = array(
	'url' => $article_url,
	'campaign' => $your_sniply_campaign_id);


// Using Curl
$ch = curl_init($sniply_linksapi_uri);
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Authorization: Bearer ' . $auth_token_sniply, 'Content-Type: application/json'
		),
	CURLOPT_POSTFIELDS => json_encode($postData)
	));

// Sending and checking for error
$response = curl_exec($ch);

if($response === FALSE){
    die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

// Get the link from snip.ly
$sniply_link = $responseData['href'];

/* #3 Send Snip.ly link to Buffer */
$buffer = new BufferPHP($your_buffer_token);

$buffer_data = array('profile_ids' => array());
for ($i = 0; $i <= count($your_buffer_profiles_ids); $i++) {
	$buffer_data['profile_ids'][] = $your_buffer_profiles_ids[$i];
}
$buffer_data['text'] = $article_title;
$buffer_data['media'] = array(
	'link' => $sniply_link,
	'description' => $article_excerpt,
	'title' => $article_title,
	'picture' => $article_image);

$ret = $buffer->post('updates/create', $buffer_data);

var_dump($ret);



?>