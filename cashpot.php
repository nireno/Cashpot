#!/usr/bin/php -q
<?php
//echo $argc;
//echo date('dmy');
$url = "http://www.nlcb.co.tt/search/cpq/cashQuery.php";
$fields = array('day'=> '01', 'month'=> 'Jan', 'year'=> '11');
$request = new HttpRequest($url, HttpRequest::METH_POST);
$request->addPostFields($fields);
try
{
	$request->send();
	if($request->getResponseCode() == 200)  $response = $request->getResponseBody();
	else echo "Request Failed";
}
catch(HttpException $e)
{
	die($e);
}

$draw_data = extract_draw_data($response);
$draw_number = $draw_data[0];
$date = $draw_data[1];
$numbers = $draw_data[2];
$multiplier = $draw_data[3];
$jackpot = $draw_data[4];
$winners = $draw_data[5];


print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners);

function extract_draw_data($html)
{
	$pattern = '/;\s*(.*?)</';
	preg_match_all($pattern, $html, $all_matches);
	$matches = $all_matches[1]; //Get the matches for the subpattern (.*?) only
	for($i = 0; $i < 6; $i++)
	{
		$matches[$i] = trim($matches[$i]);
	}
	return $matches;
}

function print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners)
{
	echo <<<RESULT
Draw #:     $draw_number$
Date:       $date$
Numbers:    $numbers$
Multiplier: $multiplier$
Jackpot:    $jackpot$
Winners:    $winners$

RESULT;
}
?>
