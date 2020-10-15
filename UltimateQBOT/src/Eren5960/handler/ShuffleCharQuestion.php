<?php

declare(strict_types=1);

namespace Eren5960\handler;

class ShuffleCharQuestion extends BaseQuestion{

	public function getType() : string{
		return "Karışık haldeki harfleri düzelt";
	}

	/**
	 * Prepares the variables (answer and question)
	 */
	public function prepare() : void{
		$char = self::$data[array_rand(self::$data)];
		$this->answer = $char;
		do{
			$array = self::str_split_unicode($char);
			shuffle($array);
		}while(implode("", $array) === $char);
		$this->question = implode(" ", $array);
	}

	public static $data = [
		"araba", "elma", "uçak", "doktor", "hemşire", "öğretmen", "asker", "departman", "uzaylı", "kalem", "kader", "ölüm", "melek",
		"telefon", "kalemlik", "parlak", "lamba", "masa", "türk", "Atatürk", "elbise", "mısır", "amerika", "bilim", "edebiyat", "sanat",
		"kültür", "alfabe", "tabak", "destan", "kitabe", "sayı", "olgun", "genç", "yaşlı", "Eren", "pluton", "zeus", "güneş", "müzik", "yaş",
		"gereksiz", "merak", "kadın", "süper", "problem", "hat", "müze", "gölge", "narin", "kalp", "kimse", "yabancı", "tavan", "kiler",
		"dert", "kelebek", "moda", "kimya", "ten", "mutlu", "helak", "dev", "gezegen", "eksen", "oyun", "şiir", "hayat", "isyan", "şehir",
		"aşk", "felç", "ilişki", "cins", "mekik", "paralel", "yalnız", "benzer", "yörünge", "gece", "sabah", "dudak", "hayal", "mars", "tekirdağ",
		"istanbul", "ankara", "saç", "sarılmak", "duman", "kafa", "kanka", "araf", "ayak", "yalan", "yükümlülük", "rezil", "yenilmek", "sorun",
		"şehinşah", "ceza", "anıl", "dolunay", "paranoya", "rap", "akıl", "milyon", "anne", "etkilenmek", "karma", "garez", "lanet", "armağan",
		"zaman", "adalet", "merhamet", "acele", "yarım", "odaklanmak", "dosdoğru", "mantık", "mosmor", "kovalamak", "borç", "paradoks", "hidra",
		"kesin", "nasıl", "gülünç", "sürü", "müzisyen", "tura", "izmir", "kudurmak", "huzur", "kartal", "mermi", "makina", "kaygı", "şarkı", "kavram"
	];

	/**
	 * @param string $str
	 * @param int    $l
	 *
	 * @return array
	 */
	public static function str_split_unicode(string $str, int $l = 0) : array{
		if ($l > 0) {
			$ret = array();
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $l) {
				$ret[] = mb_substr($str, $i, $l, "UTF-8");
			}
			return $ret;
		}

		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}
}