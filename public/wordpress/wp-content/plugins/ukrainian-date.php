<?php
/*
Plugin Name: Українські дати для Wordpress
Plugin URI: http://lilumi.org.ua/
Description: Формує правильні українські дати
Version: 1.0
Author: lilumi
Author URI: http://lilumi.org.ua
*/

function ua_time($tdate = '') {
	$treplace = array (
	"Січень" => "січня",
	"Лютий" => "лютого",
	"Березень" => "березня",
	"Квітень" => "квітня",
	"Травень" => "травня",
	"Червень" => "червня",
	"Липень" => "липня",
	"Серпень" => "серпня",
	"Вересень" => "вересня",
	"Жовтень" => "жовтня",
	"Листопад" => "листопада",
	"Грудень" => "грудня",

  "January" => "січня",
	"February" => "лютого",
	"March" => "березня",
	"April" => "квітня",
	"May" => "травня",
	"June" => "червня",
	"July" => "липня",
	"August" => "серпня",
	"September" => "вересня",
	"October" => "жовтня",
	"November" => "листопада",
	"December" => "грудня",

	"Sunday" => "неділя",
	"Monday" => "понеділок",
	"Tuesday" => "вівторок",
	"Wednesday" => "середа",
	"Thursday" => "четвер",
	"Friday" => "п'ятниця",
	"Saturday" => "субота",

	"Sun" => "неділя",
	"Mon" => "понеділок",
	"Tue" => "вівторок",
	"Wed" => "середа",
	"Thu" => "четвер",
	"Fri" => "п'ятниця",
	"Sat" => "субота",

	"th" => "",
	"st" => "",
	"nd" => "",
	"rd" => ""

	);
   	return strtr($tdate, $treplace);
}

add_filter('the_time', 'ua_time');
add_filter('the_date', 'ua_time');
add_filter('get_the_date', 'ua_time');
add_filter('the_time', 'ua_time');
add_filter('get_comment_date', 'ua_time');
add_filter('the_modified_time', 'ua_time');
