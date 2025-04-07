<?php

class AttackPokemon {
    public $attackMinimal;
    public $attackMaximal;
    public $specialAttack;
    public $probabilitySpecialAttack;

    public function __construct($attackMinimal, $attackMaximal, $specialAttack, $probabilitySpecialAttack) {
        $this->attackMinimal = $attackMinimal;
        $this->attackMaximal = $attackMaximal;
        $this->specialAttack = $specialAttack;
        $this->probabilitySpecialAttack = $probabilitySpecialAttack;
    }
}

class Pokemon {
    private $name;
    private $url;
    private $hp;
    public $attackPokemon;

    public function __construct($name, $url, $hp, $attackPokemon) {
        $this->name = $name;
        $this->url = $url;
        $this->hp = $hp;
        $this->attackPokemon = $attackPokemon;
    }

    public function getName() {
        return $this->name;
    }

    public function getHp() {
        return $this->hp;
    }

    public function getUrl() {
        return $this->url;
    }

    public function isDead() {
        return $this->hp <= 0;
    }

    public function setHp($hp) {
        $this->hp = $hp;
    }

    public function getStatus() {
        return $this->hp;
    }

    public function attack($target, $effectiveness = 1) {
        $damage = rand($this->attackPokemon->attackMinimal, $this->attackPokemon->attackMaximal);
        $usedSpecial = false;

        if (rand(1, 100) <= $this->attackPokemon->probabilitySpecialAttack) {
            $damage *= $this->attackPokemon->specialAttack;
            $usedSpecial = true;
        }

        $damage *= $effectiveness;
        $damage = round($damage);

        $target->setHp(max(0, $target->getHp() - $damage));

        return [
            "damage" => $damage,
            "special" => $usedSpecial
        ];
    }
}

class PokemonFeu extends Pokemon {
    public function getType() {
        return 'Feu';
    }

    public function getEffectiveness($type) {
        return match($type) {
            'Plante' => 2,
            'Eau', 'Feu' => 0.5,
            default => 1
        };
    }
}

class PokemonEau extends Pokemon {
    public function getType() {
        return 'Eau';
    }

    public function getEffectiveness($type) {
        return match($type) {
            'Feu' => 2,
            'Eau', 'Plante' => 0.5,
            default => 1
        };
    }
}

class PokemonPlante extends Pokemon {
    public function getType() {
        return 'Plante';
    }

    public function getEffectiveness($type) {
        return match($type) {
            'Eau' => 2,
            'Feu', 'Plante' => 0.5,
            default => 1
        };
    }
}
class Fight {
    private $pokemon1;
    private $pokemon2;
    private $effectiveness1;
    private $effectiveness2;

    public function __construct($pokemon1, $pokemon2) {
        $this->pokemon1 = $pokemon1;
        $this->pokemon2 = $pokemon2;
    }

    public function startBattle() {
        $log = [];
        $round = 0;
        $this->effectiveness1 = $this->pokemon1->getEffectiveness($this->pokemon2->getType());
        $this->effectiveness2 = $this->pokemon2->getEffectiveness($this->pokemon1->getType());

        while (!$this->pokemon1->isDead() && !$this->pokemon2->isDead()) {
            $atk1 = $this->pokemon1->attack($this->pokemon2, $this->effectiveness1);
            $atk2 = $this->pokemon2->attack($this->pokemon1, $this->effectiveness2);

            $round++;

            $log[] = [
                "round" => $round,
                "attacker1" => [
                    "name" => $this->pokemon1->getName(),
                    "img" => $this->pokemon1->getUrl(),
                    "points" => $this->pokemon1->getStatus(),
                    "minAttack" => $this->pokemon1->attackPokemon->attackMinimal,
                    "maxAttack" => $this->pokemon1->attackPokemon->attackMaximal,
                    "specialAttack" => $this->pokemon1->attackPokemon->specialAttack,
                    "probabilitySpecial" => $this->pokemon1->attackPokemon->probabilitySpecialAttack,
                    "damage" => $atk1["damage"],
                    "usedSpecial" => $atk1["special"]
                ],
                "attacker2" => [
                    "name" => $this->pokemon2->getName(),
                    "img" => $this->pokemon2->getUrl(),
                    "points" => $this->pokemon2->getStatus(),
                    "minAttack" => $this->pokemon2->attackPokemon->attackMinimal,
                    "maxAttack" => $this->pokemon2->attackPokemon->attackMaximal,
                    "specialAttack" => $this->pokemon2->attackPokemon->specialAttack,
                    "probabilitySpecial" => $this->pokemon2->attackPokemon->probabilitySpecialAttack,
                    "damage" => $atk2["damage"],
                    "usedSpecial" => $atk2["special"]
                ]
            ];
        }

        $winner = $this->pokemon1->isDead() ? $this->pokemon2->getName() : $this->pokemon1->getName();

        return [
            "rounds" => $round,
            "winner" => $winner,
            "log" => $log
        ];
    }
}

// INITIALISATION
$attackPikachu = new AttackPokemon(10, 20, 2, 30);
$attackBulbizarre = new AttackPokemon(8, 15, 1.5, 40);

$pikachu = new PokemonEau("Pikachu", "https://assets.pokemon.com/assets/cms2/img/pokedex/full/025.png", 100, $attackPikachu);
$bulbizarre = new PokemonPlante("Bulbizarre", "https://assets.pokemon.com/assets/cms2/img/pokedex/full/001.png", 100, $attackBulbizarre);

// SIMULATION
$fight = new Fight($pikachu, $bulbizarre);
$results = $fight->startBattle();

//  OUTPUT  
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
