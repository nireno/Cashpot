#!/usr/bin/php -q
<?php
//echo $argc;
//echo date('dmy');
$url = "http://www.nlcb.co.tt/search/cpq/cashQuery.php";
$fields = array('day'=> '01', 'month'=> 'Jan', 'year'=> '11');
$r = new HttpRequest($url, HttpRequest::METH_POST);
$r->addPostFields($fields);
try
{
	$r->send();
	if($r->getResponseCode() == 200)  $cashpot_html = $r->getResponseBody();
	else echo "Request Failed";
}
catch(HttpException $e)
{
	die($e);
}

$pattern = '/;\s*.*?</';
preg_match_all($pattern, $cashpot_html, $all_matches);
$matches = $all_matches[0];
$draw_number = clean_regex_match($matches[0]);
$date = clean_regex_match($matches[1]);
$numbers = clean_regex_match($matches[2]);
$multiplier = clean_regex_match($matches[3]);
$jackpot = clean_regex_match($matches[4]);
$winners = clean_regex_match($matches[5]);


print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners);

function clean_regex_match($match)
{
	return trim(substr($match, 1, strlen($match) - 2));
}

function print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners)
{
	echo <<<RESULT
Draw #:     $draw_number
Date:       $date
Numbers:    $numbers
Multiplier: $multiplier
Jackpot:    $jackpot
Winners:    $winners

RESULT;
}
?>
