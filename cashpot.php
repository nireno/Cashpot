#!/usr/bin/php -q
<?php
$options = getopt('d:');
if(!$options)
{
	die("Error: A date (YYYY-MM-DD) must be specified.\nExample: ./cashpot.php -d 2011-01-22\n");
}

try {
	$date = tokenize_date($options['d']);
} catch(Exception $e) {
	die($e->getMessage());
}

try {
	$raw_html = query_draw_history($date['day'], $date['month'], $date['year']);
} catch(HTTPException $e) {
	die($e->getMessage());
} catch(Exception $e) {
	die($e->getMessage());
}

$draw_data = extract_draw_data($raw_html);
if(!$draw_data)
{
	echo "I found no draw data for the given date.\n";
}
else
{
	$draw_number = $draw_data[0];
	$date = $draw_data[1];
	$numbers = $draw_data[2];
	$multiplier = $draw_data[3];
	$jackpot = $draw_data[4];
	$winners = $draw_data[5];


	print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners);
}

function tokenize_date($date_string)
{
	//validate date
	if(!$date = DateTime::CreateFromFormat('Y-m-d', $date_string))
	{
		throw new Exception("Error: Invalid date. Use a valid date with the format yyyy-mm-dd\n");
	}
	if(!checkdate($date->format('n'), $date->format('j'), $date->format('Y')))
	{
		throw new Exception("Error: The date supplied is not a valid Gregorian date");
	}

	return array("day" => $date->format('d'), "month" => $date->format('M'), "year" => $date->format('y'));
}

/* This function will either return the html from a cashpot query or it will
 * throw an exception.
 * The query expects two digits for the day and year but first three letters
 * of the month name.
 */
function query_draw_history($day, $month, $year)
{
	$url = "http://www.nlcb.co.tt/search/cpq/cashQuery.php";
	$fields = array('day'=> $day, 'month'=> $month, 'year'=> $year);
	$request = new HttpRequest($url, HttpRequest::METH_POST);
	$request->addPostFields($fields);
	try
	{
		$request->send();
		if($request->getResponseCode() == 200)  $response = $request->getResponseBody();
		else
		{
			throw new Exception("Request for $url was unsuccessful. A " . 
				$request->getResponseCode() . " response code was returned.\n");
		}
	}
	catch(HttpException $e)
	{
		echo $e->getMessage();
		throw $e;
	}
	return $response;
}

function extract_draw_data($html)
{
	$pattern = '/;\s*(.*?)</';
	preg_match_all($pattern, $html, $all_matches);
	$matches = $all_matches[1]; //Get the matches for the subpattern (.*?) only
	/*
	If nothing was matched then the html did not contain any data about a draw 
	and the empty array is returned. Otherwise, trim whitespace from the 
	extracted data.
	*/
	if(count($matches) > 0)
	{
		for($i = 0; $i < 6; $i++)
		{
			$matches[$i] = trim($matches[$i]);
		}
	}
	return $matches;
}

function print_draw_result($draw_number, $date, $numbers, $multiplier, $jackpot, $winners)
{
	echo <<<DRAW
Draw #:     $draw_number
Date:       $date
Numbers:    $numbers
Multiplier: $multiplier
Jackpot:    $jackpot
Winners:    $winners

DRAW;
}
?>
