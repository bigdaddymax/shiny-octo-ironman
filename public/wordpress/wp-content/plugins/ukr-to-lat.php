<?php
/*
Plugin Name: UkrToLat
Plugin URI: http://blog.net.ua/
Description: Цей плаґін формує адресу публікацій і сторінок латинськими літерами з його заголовків .
Author: LaSet
Author URI: http://www.laset.info/
Version: 0.1.3
*/ 
$gost = array(
   "Ґ"=>"G","Ё"=>"JO","Є"=>"JE","Ы"=>"Y","І"=>"I",
   "і"=>"i","ґ"=>"g","ё"=>"jo","№"=>"#","є"=>"je",
   "ы"=>"y","А"=>"A","Б"=>"B","В"=>"V","Г"=>"H",
   "Д"=>"D","Е"=>"E","Ж"=>"ZH","З"=>"Z","И"=>"Y",
   "Й"=>"J","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
   "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
   "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"CH",
   "Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'","Ы"=>"Y","Ь"=>"",
   "Э"=>"EH","Ю"=>"JU","Я"=>"JA","а"=>"a","б"=>"b",
   "в"=>"v","г"=>"h","д"=>"d","е"=>"e","ж"=>"zh",
   "з"=>"z","и"=>"y","й"=>"j","к"=>"k","л"=>"l",
   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
   "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"",
   "ы"=>"y","ь"=>"","э"=>"eh","ю"=>"ju","я"=>"ja",
   "ї"=>"ji","“"=>"","”"=>"","«"=>"","»"=>"","„"=>"",
   "‘"=>"","’"=>"","`"=>"","´"=>""
  );
  
$original = array(
   "Ґ"=>"G","Ё"=>"Yo","Є"=>"E","Ї"=>"Ji","І"=>"I",
   "і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"je",
   "ї"=>"ji","А"=>"A","Б"=>"B","В"=>"V","Г"=>"H",
   "Д"=>"D","Е"=>"E","Ж"=>"Zh","З"=>"Z","И"=>"Y",
   "Й"=>"J","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
   "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
   "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"Ts","Ч"=>"Ch",
   "Ш"=>"Sh","Щ"=>"Sch","Ъ"=>"'","Ы"=>"Yi","Ь"=>"",
   "Э"=>"E","Ю"=>"Yu","Я"=>"Ya","а"=>"a","б"=>"b",
   "в"=>"v","г"=>"h","д"=>"d","е"=>"e","ж"=>"zh",
   "з"=>"z","и"=>"y","й"=>"j","к"=>"k","л"=>"l",
   "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
   "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
   "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"",
   "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
   "“"=>"","”"=>"","«"=>"","»"=>"","„"=>"","‘"=>"",
   "’"=>"","`"=>"","´"=>""
  );
 
function sanitize_title_with_translit($title) {
	global $gost, $original;
	$rtl_standard = get_option('rtl_standard');
	switch ($rtl_standard) {
		case 'off':
		    return $title;
		case 'gost':
		    return strtr($title, $gost);
		default:
		    return strtr($title, $original);
	}
}

function rtl_options_page() {
?>
<div class="wrap">
	<h2>Налаштунки UkrToLat</h2>
	<p>Ви можете обрати стандарт, за яким буде проводитись транлітерація заголовків.</p>
	<?php
	if($_POST['rtl_standard']) {
		// set the post formatting options
		update_option('rtl_standard', $_POST['rtl_standard']);
		echo '<div class="updated"><p>Налаштунки оновлено.</p></div>';
	}
	?>

	<form method="post">
	<fieldset class="options">
		<legend>Проводити транслітерацію за стандартом:</legend>
		<?php
		$rtl_standard = get_option('rtl_standard');
		?>
			<select name="rtl_standard">
				<option value="off"<?php if($rtl_standard == 'off'){ echo(' 

selected="selected"');}?>>Вимкнена</option>
				<option value="original"<?php if($rtl_standard == 'original' OR $rtl_standard == ''){ 

echo(' selected="selected"');}?>>За замовчуванням</option>
				<option value="gost"<?php if($rtl_standard == 'gost'){ echo(' 

selected="selected"');}?>>Стандарт 1</option>
			</select>

			<input type="submit" value="Змінити стандарт" />

	</fieldset>
	</form>
</div>
<?
}

function rtl_add_menu() {
		add_options_page('UkrToLat', 'UkrToLat', 8, __FILE__, 'rtl_options_page');
}

add_action('admin_menu', 'rtl_add_menu');
add_action('sanitize_title', 'sanitize_title_with_translit', 0);
?>
