<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 31 Mart 2020
 */
declare(strict_types=1);
 
namespace eren5960\OtoCS;
 
use Eren5960\SkyBlock\SkyPlayer;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\block\utils\SignText;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityFactory;
use StormGames\SGCore\entity\FloatingText;

class CSCommand extends Command{
	/** @var string[] */
	public static $sessions = [];
	/** @var null|Main */
	public $main = null;
	public function __construct(Main $main){
		parent::__construct("otocs", "Otomatik satış yapmak için sandık oluştur.");
		$this->main = $main;
	}

	/**
	 * @param CommandSender|SkyPlayer $player
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @return mixed|void
	 */
	public function execute(CommandSender $player, string $commandLabel, array $args){
		$config = $this->main->getConfig();
		if($config->get($player->getName(), 0) < $player->getMaxCSCont()){
			$config->set($player->getName(), $config->get($player->getName()) + 1);
			$form = new CustomForm(function(SkyPlayer $player, ?array $data){
				if($data === null) return;
				$name = $data['name'];
				if(mb_strlen($name) > 8){
					$player->sendAlert("HATA", "§bCS §7ismi 8 harften fazla olamaz.",
						"< Geri Dön", "X Kapat", function()use($player){$player->getServer()->dispatchCommand($player, $this->getName());});
				}else{
					self::$sessions[$player->getName()] = $name;
					$player->sendAlert("Başarılı", "§aŞimdi oluşturmak istediğin yerdeki §bEnder Chest§a'e dokunmalısın.",
						"Anladım", "X Kapat");
				}
			});
			$form->setTitle("Oto CS");
			$form->addLabel("§7Merhaba. Otomatik bir şekilde eşyalarını satmak için yalnızca §a1 §7adımın kaldı. Şimdi aşağıya §bCS §7için bir isim gir. §cKüfürlü ya da reklam içeren isimler sınırsız ban sebebidir.");
			$form->addInput("CS İsmi", "", "Otomatör", "name");
			$form->sendToPlayer($player);
		}else{
			$player->sendAlert("HATA", "§7Daha fazla CS oluşturamazsın. Dilersen paketini yükseltip daha fazla SC oluşturabilirsin.\n§8» §bstormgames.net",
				"Anladım", "X Kapat");
		}
	}
}