<?php
session_start();

require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
if(!isset($_SESSION['oauth_token']) && !isset($_SESSION['oauth_token_secret'])){
	$CONSUMER_KEY = CONSUMER_KEY;
	$CONSUMER_SECRET = CONSUMER_SECRET;
	$OAUTH_CALLBACK = OAUTH_CALLBACK;
	
	$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
	$request_token = $connection->getRequestToken($OAUTH_CALLBACK);
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	
	//$token = $request_token['oauth_token'];
	//setcookie("oauth_token",$token,time()+60*60*24);
	//setcookie("oauth_token_secret",$request_token['oauth_token_secret'],time()+60*60*24);
	switch ($connection->http_code) {
		case 200:
			/* Build authorize URL and redirect user to Twitter. */
			$url = $connection->getAuthorizeURL($token);
			$_SESSION['status'] = 'verified';
			header('Location: ' . $url);
			exit;
		  break;
		default:
			/* Show notification if something went wrong. */
			die('error');
			exit;
	}
}
if(isset($_REQUEST['oauth_token']) && isset($_SESSION['oauth_token']) && isset($_SESSION['oauth_token_secret'])){
	$CONSUMER_KEY = CONSUMER_KEY;
	$CONSUMER_SECRET = CONSUMER_SECRET;
	$rpp = '';
	$rpp = ($_GET['rpp'] * 1)?$_GET['rpp']:10;
	
	$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	$_SESSION['access_token'] = $access_token;
	$content =$connection->get('https://api.twitter.com/1.1/search/tweets.json?q=%23jsconfasia&include_entities=1&rpp=');
	print_r($content);
	die();
	//print_r($connection);
	//echo $content->name;
	if (200 == $connection->http_code) {
		//var_dump($connection->get('/search/tweets.json?q=%23susnavgala')  );
		$_SESSION['status'] = 'verified';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <title>SUS/NAV GALA</title>
	<link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="js/superslides/stylesheets/superslides.css">
</head>
<body>
    <div id="slides">
    <ul class="slides-container">
	</ul>
	<nav class="slides-navigation">
      <a href="#" class="next">Next</a>
      <a href="#" class="prev">Previous</a>
    </nav>
	</div>
	<div id="tweetLoader">
	<a id="nextTweets" href="#" rel="">Load Next Tweets</a>
	<img id="loader" src="images/loading.gif" width="16" height="16" />
	</div>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/superslides/jquery.superslides.min.js"></script>
    <script type="text/javascript">
        $().ready(function () {
             var rpp = 10;
            //retreiveTweets(rpp);
			$('#nextTweets').attr('rel',"?q=%23susnavgala&rpp=" + rpp + "&include_entities=1");
			$('#nextTweets').click(function(){
				retreiveTweets(rpp);
				return false;
			});
			$('#nextTweets').trigger('click');
			setInterval(function(){
				$('#nextTweets').trigger('click');
			},5000);
            function retreiveTweets(rpp) {
				$('#loader').show();
                var nextPage = $('#nextTweets').attr('rel');
               if ($.trim(nextPage) == '') {
					$('#nextTweets').attr('rel',"");
					$('#loader').hide();
                    return false
                }else{
					$.ajax({
						url: ' http://search.twitter.com/search.json' + nextPage + '&callback=?',
						//url:'test.json',
						dataType: 'json',
						crossDomain: true,
						success: function (data) {
							if(data.next_page==undefined){
								$('#nextTweets').attr('rel', "");
								$('#loader').hide();
							}else{
								$('#nextTweets').attr('rel', data.next_page);
							}
							var r = data.results;
							var page = parseInt(data.page) - 1;
							var multiplier = page * rpp;
							$.each(r, function (index, rObj) {
								//$('ul').append("<li><div class='container'>@" + rObj.from_user + " - " + rObj.text + "</div></li>");
								$li = $("<li><div class='container'>@" + rObj.from_user + " - " + rObj.text + "</div><a href='#' class='btnremove'>remove</a> | <a href='#' class='togglecontent'>toggle</a></li>");
								var ent = rObj.entities;
								var urls = ent.urls[0];
								var img = "";
								if (typeof urls != 'undefined') {
									//fetch instagram image
									var eUrl = urls.expanded_url;
									index = parseInt(index) + parseInt(multiplier);
									if (eUrl.indexOf("instagr.am") != -1) {
										$.getJSON("http://api.instagram.com/oembed?url=" + eUrl + "&callback=?", function (i) {
											var slideDiv = document.getElementById('slider');
											//$($('ul li')[index]).prepend("<img src='" + i.url + "' width='" + (i.width * 0.5) + "' height='" + (i.height * 0.5) + "' />");
											$($('ul li')[index]).prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "'  />");
											//$li.prepend("<img src='" + i.url + "' width='" + (i.width ) + "' height='" + (i.height) + "'  />");
										});
									}
								}else{ //twitter photo
									if(typeof ent.media !='undefined'){
										var media = ent.media[0];
										if(typeof media['media_url']!='undefined'){
											index = parseInt(index) + parseInt(multiplier);
											var slideDiv = document.getElementById('slider');
											var sizes = media.sizes;
											var chosen_size = sizes.medium;
											$li.prepend("<img src='" +media['media_url'] + "' width='" + (chosen_size.w ) + "' height='" + (chosen_size.h) + "'  />");
										}
									}
									
								}
								if(page==0){
									$('ul').append($li);
								}else{
									$('#slides').superslides('append', $li);
								}
							});
							$('#nextTweets').unbind('click').bind('click',function () {
								retreiveTweets(rpp);
								return false;
							});
							$('.btnremove').unbind('click').bind('click',function () {
								$('#slides').superslides('next');
								$(this).parent().remove();
								return false;
							});
							$('.togglecontent').unbind('click').bind('click',function () {
								$(this).parent().children('.container').toggle('slow');
							});
							if(page==0){
								$('#slides').superslides({
									play: true,
									slide_easing: 'easeInOutCubic',
									slide_speed: 800,
									delay: 8000,
									pagination: false
								  });
							}
							$('#loader').hide();
						}
					});
				}
			}
        });
		
    </script>
	<script type="text/javascript">
    window.onload = setupRefresh;

    function setupRefresh() {
      setTimeout("refreshPage();", 60000); // milliseconds
    }
    function refreshPage() {
       window.location = location.href;
    }
  </script>
</body>
</html>
