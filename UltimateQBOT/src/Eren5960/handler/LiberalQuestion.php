<?php

declare(strict_types=1);

namespace Eren5960\handler;

class LiberalQuestion extends BaseQuestion{

	/**
	 * Question type
	 * @return string
	 */
	public function getType() : string{
		return "Genel kültür lazım tabi";
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
		"Cam neyden yapılır" => "kum",
		"Kim kepenek giyer" => "çoban",
		"Köylülerin el birliği ile köyün işini birlikte yapmalarına ne ad verilir" => "imece",
		"Türkiye'nin başkentinin adı nedir" => "ankara",
		"Türkiye'nin en fazla yağış alan ilinin adı nedir" => "rize",
		"Pirinç hangi ürünün kabuğunun soyulması ile elde edilir" => "çeltik",
		"Yön bulmak için hangi yıldız kullanılır" => "kutup yıldızı",
		"Türk tarihinin en ünlü mimari kimdir" => "mimar sinan",
		"20TL üzerindeki kişinin ünvanı ve adı nedir" => "mimar kemalettin",
		"Dünya'nın kaç tane uydusu vardır" => "1",
		"Rumeli hisarını hangi padişah yapmıştır" => "Fatih Sultan Mehmet",
		"Keçinin erkeğine ne ad verilir" => "Teke",
		"Yerden fışkırarak çıkan su kanaklarna ne ad verilir" => "gayzer",
		"İçerisinde yüksek oranda demir minerali bululunduran sebzenin adı nedir" => "Ispanak",
		"Depremin büyüklüğünü ve süresini ölçen alete ne ad verilir" => "sismograf",
		"Yazın kırlarda ve ekin tarlalarında yetişen kırmızı narin çiçeğin adı nedir" => "gelincik",
		"Kızınca tüküren hayvanın adı nedir" => "lama",
		"Gezilerini 'Seyehatname' adlı eserinde toplayan Türk gezgin kimdir" => "evliya çelebi",
		"Ses en hızlı hangi ortamda yayılır" => "katı",
		"Bozkırın tezenesi lakaplı rahmetli halk ozanı kimdir" => "Neşet Ertaş",
		"Mercekler ışığın hangi özelliği kullanılarak yapılmıştır" => "kırılma",
		"Duvara asılı bir haritanın sağı her zaman hangi yönü gösterir" => "doğu",
		"İlk evcilleştirilen hayvanın adı nedir" => "köpek",
		"Uçaklarda pilot kabinine ne ad verilir" => "kokpit",
		"Türkiyede kaç tane coğrafi bölge bulunmaktadır" => "7"
	];
}