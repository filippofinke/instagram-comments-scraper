<?php
require __DIR__.'/vendor/autoload.php';
use  \InstagramAPI\Instagram as Instagram;
$ig = new Instagram();


echo "Instagram Comments Scraper by @flippofinke".PHP_EOL;

$username = readline("Username without @: ");
$password = readline("Password: ");

echo "I'm trying to access to your account.".PHP_EOL;

define("MAX_PAGES", 10);

try
{
	$ig->login($username, $password);
}
catch(Exception $ex)
{
	echo "Error ".$ex->getMessage().PHP_EOL;
	exit;
}
$tags = [];
echo "All right, access granted. Write the tags from which get the comments (without #), once finished send an empty line".PHP_EOL;

while(($read = readline("Tag: ")) != "")
{
	$tags[] = $read;
}

foreach($tags as $tag)
{
	$rankToken = \InstagramAPI\Signatures::generateUUID();
	$maxId = null;
	$page = 0;
	do {
		echo "Searching for #$tag".PHP_EOL;
		$posts = $ig->hashtag->getFeed($tag, $rankToken, $maxId);
		foreach ($posts->getItems() as $item) {
			try
			{
				$mediaId = $item->getId();
				echo "#tag ".$mediaId." ".$item->getCode();
				$data = $ig->media->getComments($mediaId);
				$comments = $data->getComments();
				$count = $data->getCommentCount();
				echo " comments: $count".PHP_EOL;
				if($count != 0)
				{
					foreach($comments as $comment)
					{
						$text = $comment->getText();
						echo $text.PHP_EOL;
						file_put_contents($tag.".txt", $text."\n", FILE_APPEND);
					}
				}
			}
			catch(Exception $ex)
			{
				echo "Error ".$ex->getMessage().PHP_EOL;
			}
    	}
		$maxId = $posts->getNextMaxId();
		$page++;
	} while ($maxId !== null && $page < MAX_PAGES); 
}

?>