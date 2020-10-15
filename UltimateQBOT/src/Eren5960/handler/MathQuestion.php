<?php

declare(strict_types=1);

namespace Eren5960\handler;

class MathQuestion extends BaseQuestion{

	public function getType() : string{
		return "Matematik yapabilir misin?";
	}

	public function prepare() : void{
		$array = ['+', '-', 'x'];
		$q = $array[array_rand($array)];

		if($q === 'x'){
			$i1 = rand(2, 100);
			if($i1 < 10){
				$i2 = rand(75, 100);
			}elseif($i1 < 25){
				$i2 = rand(60, 80);
			}elseif($i1 > 50){
				$i2 = rand(10, 30);
			}else{
				$i2 = rand(25, 50);
			}
			$this->answer = $i1 * $i2;
		}else{
			$i1 = rand(0, 2) <= 1 ? rand(0, 999) : rand(0, 5000);
			$i2 = rand(1234, 7000);
			if($q === '-'){
				$i1_old = $i1;
				$i1 = max($i1_old, $i2);
				$i2 = min($i1_old, $i2);
				$this->answer = $i1 - $i2;
			}else{
				$this->answer = $i1 + $i2;
			}
		}
		$this->question = '§b' . $i1 . ' §e' . $q . ' §b' . $i2 . ' §7= §e?';
	}
}