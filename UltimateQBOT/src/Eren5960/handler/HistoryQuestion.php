<?php

declare(strict_types=1);

namespace Eren5960\handler;

class HistoryQuestion extends BaseQuestion{

	/**
	 * Question type
	 * @return string
	 */
	public function getType() : string{
		return "Tarihini bilmen lazım...";
	}

	/**
	 * Prepares the variables (answer and question)
	 */
	public function prepare() : void{
		$data = self::$data;
		$index = array_rand($data);
		$this->question = $index . '?';
		$this->answer = $data[$index];
	}

	public static $data = [
		"Osmanlı kaç yılında kuruldu" => 1299,
		"Osmanlı devleti kurucusu kimdir" => "Osman Bey",
		"Çocuk hakları günü hangi tarihte kutlanmaktadır" => "20 Kasım",
		"İstiklal Marşı şairi kimdir" => "Mehmet Akif Ersoy",
		"Türklerin kullandığı ilk alfabe nedir" => "Göktürk",
		"Erkekler günü ne zamandır" => "19 Kasım",
		"Emek ve Dayanışma günü ne zamandır" => "1 Mayıs",
		"Cumhuriyet Bayramı ne zamandır" => "29 Ekim",
		"1. İnönü Zaferi tarihi" => "10 Ocak 1921",
		"2. İnönü Zaferi tarihi" => "31 Mart 1921",
		"Hz. Muhammed'in Mekke'yi fethinin tarihi" => "11 Ocak 630",
		"Sırp Sındığı zaferi tarihi" => "25 Ocak 1364",
		"İlk uzay gemisinin aya iniş tarihi" => "3 Şubat 1966",
		"Türk Kadınlar Birliği hangi yılda kuruldu" => "1924",
		"Telefon hangi yıl icat edildi" => "1876",
		"Türkiye'nin NATO'ya giriş tarihi" => "18 Şubat 1952",
		"Ankara Radyosu yayına hangi yıl başladı" => "1934",
		"Türkiye Yeşilay Cemiyeti'nin kuruluş tarihi" => "5 Mart 1920",
		"Antep'e, TBMM'ce, \"Gazi\" ünvanı hangi yıl verildi" => "1921",
		"Çanakkale zaferinin tarihi" => "18 Mart 1915",
		"Adolf Hitler hangi yıl intihar etti" => "1945",
		"2. Dünya Savaşı hangi yıl sona erdi" => "1945",
		"Kızılay'ın kuruluş yılı" => "1868",
		"Erzurum kongresi hangi yıl duyuruldu" => "1919"
	];
}