<?php
$I = new WebGuy($scenario);
$I->wantTo('sign in');
$I->amOnPage('/');
$I->click('#login');
$I->see('userName');

?>
