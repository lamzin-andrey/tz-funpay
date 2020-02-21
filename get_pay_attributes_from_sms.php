<?php

//тестировалось на PHP 7.3.12 (cli) (built: Nov 27 2019 09:04:30) ( NTS )


/**
 * @description Функция предусматривает, что текст сообщения может измениться и длина пароля также может измениться, 
 *  стать более чем 4 цифры, например шесть цифр, но не более десяти цифр, потому что такие пароли сложно вводить
 * @param string $s текст  SMS
 * @return StdClass {code: string, number: string, sum: string}
*/
function getPayAttributesFromSMS(string $s) : StdClass
{
	$oResult = new StdClass();
	
	//20 здесь - чтобы номер Я-кошелька был получен целиком, а не первые 10 цифр (чтобы отсечь его если код вдруг начнется с 41001)
	$sPattern = "#[0-9,.]{4,20}#mis";
	//Находим все числа из текста длинее 4 и менее 21 символа длиной
	preg_match_all($sPattern, $s, $aData);
	
	//Отсекаем номер Я-кошелька и сумму (её определяем по символу ','
	// (или '.' если что-то изменится и мы начнём отделять десятичную часть на аглицкий манер:) )
	
	/** @var array $aBuf - сюда собираем все числа начинающиеся с '41001' (может не повезти и код совпадёт с префиксон номеров кошельков) */
	$aBuf = [];
	foreach ($aData[0] as $str) {
		$bDecimalFound = preg_match("#[,.]#", $str);
		if (strpos($str, '41001') === false && !$bDecimalFound) {
			$oResult->code = $str;
		} else if ($bDecimalFound) {
			$oResult->sum = $str;
		} elseif (strpos($str, '41001') !== false) {
			$oResult->number = $str;
			//На случай, если код возьмёт и совпадёт с префиксом Я-кошелька
			$aBuf[] = $str;
		}
	}
	
	//Вариант, когда пароль (код) взял и совпал с префиксом Я-кошелька
	//Тогда берем минимальный по длине
	if (count($aBuf) > 1) {
		foreach ($aBuf as $str) {
			$nSz = strlen($str);
			if ($nSz > 10) {
				$oResult->number = $str;
			} else {
				$oResult->code = $str;
			}
		}
		return $oResult;
	}
	return $oResult;
}
