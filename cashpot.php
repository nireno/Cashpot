#!/usr/bin/php -q
<?php

/***** Example Usage *****/
$options = getopt('d:');
if(!$options)
{
	die("Error: A date (YYYY-MM-DD) must be specified.\nExample: ./cashpot.php -d 2011-01-22\n");
}

try {
	$date = tokenize_date($options['d']);
} catch(Exception $e) {
	die($e->getMessage() . "\n");
}

try {
	$raw_html = query_draw_history($date['day'], $date['month'], $date['year']);
} catch(HTTPException $e) {
	die($e->getMessage() . "\n");
} catch(Exception $e) {
	die($e->getMessage() . "\n");
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

/******************************************************************************/

/**
 * Tokenize a date
 * @param a date formatted as YYYY-MM-DD
 * @return an array containing the date components in a cashpot query format
 */
function tokenize_date($date_string)
{
	/*
	Note that CreateFromFormat will calculate an input (invalid) date of 2011-07-33 
	to 2011-08-02 so a further checkdate() is performed. CreateFromFormat is run
	first to ensure the format is correct.
	*/
	if(!$date = DateTime::CreateFromFormat('Y-m-d', $date_string))
	{
		throw new Exception("Error: Invalid date. Use a valid date with the format yyyy-mm-dd");
	}

	$year = strtok($date_string, '-');
	$month = strtok('-');
	$day = strtok('-');
	if(!checkdate($day, $month, $year))
	{
		throw new Exception("Error: The date supplied is not a valid calender date.");
	}

	return array("day" => $date->format('d'), "month" => $date->format('M'), "year" => $date->format('y'));
}

/**
 * Query past cashpot draws by date.
 * @param day a two digit representation of the day eg. 09
 * @param month a three letter representation of the month eg. Jan
 * @param year a two digit representation of the year eg. 99
 * @return the raw html from the page returned by querying a past cashpot draw.
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
				$request->getResponseCode() . " response code was returned.");
		}
	}
	catch(HttpException $e)
	{
		echo $e->getMessage();
		throw $e;
	}
	return $response;
}

/**
 * Extract the draw details from raw html (returned by query_draw_history function)
 * @param html the raw html returned from a query of a past cashpot draw.
 * @return an empty array if no draw data is found
 * @return an array containing the draw information extracted from the raw input html
 */
function extract_draw_data($html)
{
	$pattern = '/;\s*(.*?)</'; 
	/* this is a hacky regex search. It searches for strings between the ; and > characters */
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
