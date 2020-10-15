<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\forms;

use Eren5960\SkyBlock\island\DefaultIslandManager;
use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\island\IslandManager;
use Eren5960\SkyBlock\island\Member;
use Eren5960\SkyBlock\pass\PassManager;
use Eren5960\SkyBlock\SkyBlock;
use Eren5960\SkyBlock\SkyPlayer;
use Eren5960\SkyBlock\utils\SkyUtils;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use StormGames\SGCore\utils\Utils;

class Forms{
    public static function islandCreate(Player $player){
    	$islands = array_filter(DefaultIslandManager::getIslandsData(), function(Config $data)use($player){return !IslandManager::isHaveIsland($player, $data->getAll()['name']);});
        $form = new SimpleForm(
            function (Player $player, ?int $i) use($islands){
                if($i === null) return;
                $data = array_values($islands)[$i]->getAll();
                Forms::islandInfo($player, $data['name'], (int) $data["level_cost"], (int) $data["money_cost"], (int) $data["xp_cost"], $data['description']);
            }
        );
        $form->setTitle("§2Ada Oluştur");
        $form->setContent("§7Yeni bir maceraya atılmak için doğru yerdesin. Ada oluştur ve maceraya katıl!");
        foreach ($islands as $name => $data){
            $form->addButton("§8» {$name} §8«" . "\n§2Daha fazla bilgi al");
        }
        $form->sendToPlayer($player);
    }

    public static function islandInfo(Player $player, string $island, int $level_cost, int $money_cost, int $xp_cost, string $desc){
        $content = "§7Bu ada için gereken hiçbir şey yok!";
        if($island !== DEFAULT_ISLAND){
            $content = "§bAçıklama: §7" . $desc . "\n§aGerekenler:\n\n§cNormal Ada seviyesi: §f" . $level_cost . "\n\n" . "§cOyun Parası ($): §f" . $money_cost . "\n\n" . "§cXP: §f" . $xp_cost;
        }
        $form = new IslandCreateModalWindow();
        $form->setTitle("§2Ada Özellikleri");
        $form->setContent("§7" . $content);
        $form->setButton1("§2Adayı oluştur");
        $form->setButton2("< Geri Dön");
        $form->island = $island;
        $form->level_cost = $level_cost;
        $form->money_cost = $money_cost;
        $form->xp_cost = $xp_cost;
        $form->sendToPlayer($player);
    }

    public static function islandTeleport(Player $player){
        $available = array_values(array_filter(DefaultIslandManager::getIslands(), function($island)use($player){return IslandManager::isHaveIsland($player, $island);}));
        $form = new SimpleForm(function(Player $player, int $i = null)use($available){
            if($i === null) return;
            IslandManager::initIsland($player, $available[$i])->teleport($player);
        });
        $form->setTitle("§3Ada Işınlan");
        $form->setContent("§7Işınlanmak istediğin ada hangisi?");
        foreach ($available as $island){
            $form->addButton("§8» {$island} §8«" . "\n§2Tıkla & Işınlan");
        }
        $form->sendToPlayer($player);
    }

    /**
     * Islands Settings Main Page
     *
     * @param Player $player
     * @param IslandBase $island
     * @return bool
     */
    public static function islandSettings(Player $player, IslandBase $island): bool{
        $options = $island->options;
        $form = new SimpleForm(function(Player $player, int $i = null) use($island, $options) {
            if($i === null) return;
            switch ($i){
                case 0:
	                $form = new CustomForm(function(Player $player, array $data = null) use($island): bool{
		                if($data === null) return Forms::islandSettings($player, $island);
		                $name = $data['isim'];
		                $count = mb_strlen($name, 'UTF-8');
		                $err = '';
		                if($count < 3){
			                $err = "en az 3";
		                }elseif($count > 14){
			                $err = "en fazla 14";
		                }
		                if(empty($err)){
			                $island->options->name = $name;
			                (new InfoForm(
				                "Başarılı", "§aAda ismin §e{$name} §aolarak ayarlandı.",
				                "< Geri Dön", "Kapat",
				                function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
			                )->sendToPlayer($player);
		                }else{
			                (new InfoForm(
				                "Başarısız", "§cAda ismin " . $err . " harften oluşmalıdır.",
				                "< Geri Dön", "Kapat",
				                function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
			                )->sendToPlayer($player);
		                }
		                return true;
	                });
	                $form->setTitle("Ada İsmini Değiştir");
	                $form->addLabel("§7Adanın ismini değiştirmek için doğru yerdesin ama bazı kurallarımız var!" . "\n" .
		                "§7» §cKüfürlü ada ismi 30 gün uzaklaştırma cezasına tekabüldür.\n" .
		                "§7» §eAda ismi §d3 §eile §d14 §eharf arasında içermelidir");
	                $form->addInput("Yeni ada ismi", "Benim Adam", $options->name, "isim");
	                $form->sendToPlayer($player);
                    break;
                case 1:
                    $island->getWorld()->setSpawnLocation($player->getLocation());
                    (new InfoForm(
                        "Başarılı", "§aAda doğma noktası başarıyla ayarlandı.",
                        "< Geri Dön", "Kapat",
                        function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
                    )->sendToPlayer($player);
                    break;
                case 2:
                    $players = $island->getWorld()->getPlayers();
                    unset($players[$player->getId()]);
                    $players = array_merge(["Herkesi Tekmele"], array_map(function(Player $player){return $player->getName();}, $players));
                    if(count($players) === 0){
                        (new InfoForm(
                            "Kimsecikler gözükmüyor", "§cAdada senden başka kimse yok.",
                            "< Geri Dön", "Kapat",
                            function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
                        )->sendToPlayer($player);
                    }else{
                        $form = new CustomForm(function(Player $player, array $data = null) use($island, $players): bool{
                            if($data === null) return Forms::islandSettings($player, $island);
                            $oyuncu = $players[$data['oyuncu']];
                            $kick = function(Player $player, string $player_){
                                $player_ = Server::getInstance()->getPlayerExact($player_);
                                if($player_ instanceof Player && $player_->isOnline()) {
                                    $player_->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                                    $player_->sendMessage("§7» §e" . $player->getName() . " §csizi adasından tekmeledi.");
                                    $player->sendPopup("§e" . $player_->getName() . " §cadandan tekmelendi.");
                                }
                            };
                            if($oyuncu === "Herkesi Tekmele"){
                                foreach($players as $player_){
                                    $kick($player, $player_);
                                }
                            }else{
                                $kick($player, $oyuncu);
                            }
                            return true;
                        });
                        $form->setTitle("Oyuncuları Tekmele");
                        $form->addLabel("§7Burdan adanda istemediğin oyuncuyu tekmeleyebilirsin. \nEğer kimseyi istemiyorsanız bakın: \n§3Ada Ayarları §f> §eAda Kilitle");
                        $form->addDropdown("Tekmelenecek oyuncular", $players, 0, "oyuncu");
                        $form->sendToPlayer($player);
                    }
                    break;
                case 3:
                    $options->locked = !$options->locked;
                    (new InfoForm(
                        "Kilit değiştirildi", "§aAda" . ($options->locked ? " başarıyla kilitlendi. Artık kimsecikler gelemez." : "nın kiliti kaldırıldı. Artık oyuncular buraya gelebilir!"),
                        "< Geri Dön", "Kapat",
                        function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
                    )->sendToPlayer($player);
                    break;
                case 4:
                    Forms::islandMemberForm($player, $island);
                    break;
                case 5:
                    Forms::islandCreate($player);
                    break;
                case 6:
                    $count = SkyBlock::getPlayerIslandsCount($player);
                    if($count === 1){
	                    (new InfoForm(
		                    "HATA", "§7Sadece §a1 §7tane adan var. \n§3Ada oluştur menüsünden ada oluşturabilirsin.",
		                    "< Geri Dön", "Kapat",
		                    function(Player $player)use($island) {self::islandSettings($player, $island);}, null)
	                    )->sendToPlayer($player);
                    }else{
	                    Forms::islandTeleport($player);
                    }
                    break;
            }
        });
        $form->setTitle("§3Ada Ayarları");
        $form->setContent("§7Burdan adanı yönetebilirsin.\n§eAda bilgileri:" .
            "\n§7İsim: §f" . $options->name . "\n§7Seviye: §f" . $options->level . "  §7XP: §a" . number_format($options->xp, 2) . '§7/§c' . number_format($options->need_xp, 2));
	    $form->addButton("Ada İsmini Değiştir");
        $form->addButton("Doğma Noktasını Ayarla");
        $form->addButton("Oyuncuları Tekmele");
        $form->addButton($options->locked ? "Adanın kilidini aç" : "Adanı Kilitle");
	    $form->addButton("Ada Ortaklar & İzinler");
        $form->addButton("Yeni Ada Oluştur");
        $form->addButton("Diğer Adana Git");
        $form->sendToPlayer($player);
        return true;
    }

    public static function islandMemberForm(Player $player, IslandBase $island){
    	$members = $island->options->members;
    	$form = new SimpleForm(function(SkyPlayer $player, ?int $selected) use($island, $members){
    		if($selected === null){
    			Forms::islandSettings($player, $island);
    			return;
		    }
    		$count = count($members);
    		if($count === 0){
			    self::addMemberForm($player, $island);
		    }else{
    			if($selected === $count){
    				self::addMemberForm($player, $island);
			    }else{
    				$member = array_values($members)[$selected];
    				self::remoteMemberForm($player, $island, $member);
			    }
		    }
	    });

    	$form->setTitle("Ada Ortaklar & İzinler");
    	$form->setContent(count($members) === 0 ? "§7Alttaki butondan adaya ortak ekleyebilirsin." : "§7İstediğin ortağa tıkla. İzinleri yönet ve ya ortaklıktan çıkar.");
    	foreach($members as $name => $member){
    		$form->addButton($name . "\n» " . ($member->isOnline() ? "§2Aktif" : "§cÇevrim dışı") . "§8«", 1, Utils::getSkinHeadImageURL($player));
	    }
    	$form->addButton("Ortak Ekle", 1, "https://cdn1.iconfinder.com/data/icons/DarkGlass_Reworked/128x128/actions/edit_add.png");
    	$form->sendToPlayer($player);
    }

    public static function addMemberForm(SkyPlayer $player, IslandBase $island){
    	$back_func = function()use($player, $island){self::addMemberForm($player, $island);};
	    $players = SkyUtils::getOnlinePlayers($player, $back_func);
	    if($players === null) return;

	    $form = new CustomForm(function(SkyPlayer $sender, array $data = null)  use($players, $island, $back_func){
		    if($data === null){
		    	self::islandMemberForm($sender, $island);
		    	return;
		    }
		    $player = Server::getInstance()->getPlayerExact($players[$data[1]]);
		    if($player instanceof SkyPlayer && $player->isOnline()){
			    $member = $island->options->members[$player->getName()] = new Member($player->getName());
			    $sender->sendAlert("BAŞARILI", "§b" . $player->getName() . " §7adanıza ortakk olarak eklendi. Şuan sadece adanıza ışınlanabilir. Ada izinler kısmından diğer izinleri ayarlayınız.",
				    "İzinleri yönet", "X Kapat", function() use($sender, $island, $member){
			    	    self::remoteMemberForm($sender, $island, $member);
				    });
		    }else{
			    $player->sendAlert("HATA", "§cOyuncu aktif değil",
				    "< Geri dön", "X Kapat", $back_func);
		    }
	    });
	    $form->setTitle("Ortak ekle");
	    $form->addLabel("§7Aşağıdan yeni ortağını seçebilirsin");
	    $form->addDropdown("Oyuncu seç", $players);
	    $form->sendToPlayer($player);
    }

    public static function remoteMemberForm(SkyPlayer $owner, IslandBase $island, Member $member): void{
    	$form = new CustomForm(function(SkyPlayer $player, ?array $data) use($island, $member){
			if($data === null) return;
		    if($data["kaldır"] === "Kaldır"){
		    	$player->sendAlert("Kaldırıldı", "§b" . $member->getName() . " §aada ortaklarınızdan kaldırıldı.", "Tamam", "X Kapat");
			    if($member->isOnline()){
				    $member->getPlayer()->sendAlert("Kovuldun", "§b" . $player->getName() . " §csizi ortaklıkdan çıkardı.", "Tamam", "X Kapat");
			    }
			    unset($island->options->members[$member->getName()]);
		    	return;
		    }
		    unset($data["kaldır"]);
		    $text = "";
		    foreach($data as $label => $value){
		    	$pass = PassManager::$pass[$label];
		    	if($value){
		    		$member->addPass($pass);
		    		$text .= "§b" . $pass->getName() . " §aiznini verdin." . TextFormat::EOL;
			    }elseif($member->removePass($pass)){
		    		$text .= "§b" . $pass->getName() . " §ciznini geri aldın." . TextFormat::EOL;
			    }
		    }
		    if($text === ""){
			    $player->sendAlert("Hiçbir değişiklik yapılmadı.", "Hiç bir şey değiştirmedin!", "Düzenle", "X Kapat",
				    function()use($player, $island, $member){self::remoteMemberForm($player, $island, $member);});
		    }else{
			    $player->sendAlert("Başarılı", $text, "Tekrar bak", "X Kapat",
				    function()use($player, $island, $member){self::remoteMemberForm($player, $island, $member);});
			    if($member->isOnline()){
				    $player->sendAlert("İzinler", "§b" . $player->getName() . " §cadaasındaki izinleriniz değişti.", "İzinlere bak.", "X Kapat"); // TODO: İzinlere bak (Ortak Form)
			    }
		    }
	    });
    	$form->setTitle($member->getName() . " Ortağını Yönet");
    	foreach(PassManager::$pass as $perm => $pass){
    		$form->addToggle($pass->getName() . " İzini", $member->hasPass($pass), $perm);
	    }
    	$form->addInput("Ada ortağını adadan kaldırmak için buraya \"§cKaldır\" §ryazınız.", "", null, "kaldır");
    	$form->sendToPlayer($owner);
    }
}