<?php
/*
///swordfish
define('OAUTH2_CLIENT_ID', 'd77aa75e0b260c50bbf4');
define('OAUTH2_CLIENT_SECRET', '97b631080b24174e7732db04c4ea5b002ef6f092');





///000webhost -> devtest swordfish -> github
define('OAUTH2_CLIENT_ID', '32b1ab6c7581851b6336');
define('OAUTH2_CLIENT_SECRET', '51bf0d9f0705a9b11a3802ce71341dd986b2806c');


///mine personal
define('OAUTH2_CLIENT_ID', 'c90d3d59f99f00bf39ad');
define('OAUTH2_CLIENT_SECRET', '420695c6b16706d1e7304ef6a7cffb2d315dcd34');





///000webhost hosting project
swordfishdevtest
T&q5zmUvZKZ)FgSCcGpR
*/




# add github credentials
define('OAUTH2_CLIENT_ID', '32b1ab6c7581851b6336'); //add client id here
define('OAUTH2_CLIENT_SECRET', '51bf0d9f0705a9b11a3802ce71341dd986b2806c'); //add client secret here

# URL of github api
$authorizeURL = 'https://github.com/login/oauth/authorize';
$tokenURL = 'https://github.com/login/oauth/access_token';
$apiURLBase = 'https://api.github.com/';

# start sessions
session_start();

// Start the login process by sending the user to Github's authorization page
if(get('action') == 'login') {
  // Generate a random hash and store in the session for security
  $_SESSION['state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
  unset($_SESSION['access_token']);

  $params = array(
    'client_id' => OAUTH2_CLIENT_ID,
    'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
    'scope' => 'user',
    'state' => $_SESSION['state']
  );

  // Redirect the user to Github's authorization page
  header('Location: ' . $authorizeURL . '?' . http_build_query($params));
  die();
}

// to kill all Sessions and reset code base 
if(get('action') == 'exit') {
    unset($_SESSION['state']);
    unset($_SESSION['access_token']);
    session_destroy();
    exit();
}

// When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
if(get('code')) {
  // Verify the state matches our stored state
  if(!get('state') || $_SESSION['state'] != get('state')) {
    header('Location: ' . $_SERVER['PHP_SELF']);
    die();
  }

  // Exchange the auth code for a token
  $token = apiRequest($tokenURL, array(
    'client_id' => OAUTH2_CLIENT_ID,
    'client_secret' => OAUTH2_CLIENT_SECRET,
    'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
    'state' => $_SESSION['state'],
    'code' => get('code')
  ));
  $_SESSION['access_token'] = $token->access_token;

  header('Location: ' . $_SERVER['PHP_SELF']);
}

# if successful show results
if(session('access_token')) {



/*
$user = apiRequest($apiURLBase.'user'); //get the repo url for this user
$repoissues = apiRequest($user->repos_url); //pull all repos
foreach ($repoissues as $repodetails){

//https://api.github.com/repos/DeanDevel/nginx-proxy-manager/issues{/number}
echo $repodetails->issues_url.'<br />';
echo $repodetails->open_issues_count.'<br />';

echo '<br /><br />';
print_r($repodetails);
echo '<br /><br />';

}
*/











    print '<br /><br />';
    $user = apiRequest($apiURLBase.'user');
    echo '<h3>Logged In</h3>';
    echo '<h4>' . $user->login . '</h4>';
    echo '<pre>';
    print_r($user);
    echo '</pre>';

    print '<br /><br />';
    print '<h3>Full List of Urls on Github</h3>';
    $full = apiRequest($apiURLBase);
    foreach ($full as $key=>$value)
    {
        print $key .'=>'. $value.'<br />';
    }
    print '<br /><br />';













  
} else {

# fail result if no session token
  echo '<h3>Not logged in</h3>';
  echo '<p><a href="?action=login">Log In</a></p>';
}

# main function for curl requests
function apiRequest($url, $post=FALSE, $headers=array()) {
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Linux useragent'); //change agent string

  if($post)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

  $headers[] = 'Accept: application/json';
  //$headers[] = 'Accept: application/vnd.github+json';
  

  # add access token to header 
  if(session('access_token'))
    $headers[] = 'Authorization: Bearer ' . session('access_token');

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $response = curl_exec($ch);
  return json_decode($response); //decode response
}

# array key existence
function get($key, $default=NULL) {
  return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

# array key existence
function session($key, $default=NULL) {
  return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}
?>
