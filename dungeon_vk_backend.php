<?php
class Dungeon{
	protected array $grid = array();
	protected int $startRow;
	protected int $startCol;
	protected int $finishRow;
	protected int $finishCol;


	public function __construct(int $startRow, int $startCol, int $finishRow, int $finishCol){
	    $this->startRow = $startRow;
        $this->startCol = $startCol;
        $this->finishRow = $finishRow;
        $this->finishCol = $finishCol;
		$this->grid[$startRow][$startCol] = new EmptyRoom();
		$this->grid[$finishRow][$finishCol] = new EmptyRoom();
	}

	public function createEmptyRoom(int $x, int $y){
		$this->grid[$x][$y] = new EmptyRoom();
	}

	public function createTreasureRoom(int $x, int $y, $rarity){
		$this->grid[$x][$y] = new TreasureRoom($rarity);
	}

	public function createMonsterRoom(int $x, int $y, int $power, int $scale, int $worth){
		$this->grid[$x][$y] = new MonsterRoom($power, $scale, $worth);
	}

	public function getStartPos(): array
    {
		return array($this->startRow, $this->startCol);
	}

	public function getFinishPos(): array
    {
		return array($this->finishRow, $this->finishCol);
	}

	public function getGrid(): array
    {
	    return $this->grid;
    }


}


class EmptyRoom{
	protected string $type;

	public function __construct(){
		$this->type = 'empty';
	}

	public function setEmpty(){
		$this->type = 'empty';
	}
	public function getType(): string
    {
	    return $this->type;
    }

}

class TreasureRoom extends EmptyRoom{
	protected int $rarity;
	public function __construct(int $rarity){
		$this->type = 'treasure';
		$this->rarity = $rarity;
	}

	public function GivePoint(){
		if ($this->type == 'treasure'){
            $this->setEmpty();
			switch ($this->rarity) {
				case 0:
					return rand(1, 10);
				case 1:
					return rand(11, 20);
				case 2:
					return rand(21, 30);
			}

		}
		return null;
	}
}

class MonsterRoom extends EmptyRoom{
	protected int $power;
	protected int $scale;
	protected int $worth;

	public function __construct(int $power, int $scale, int $worth){
		$this->type = 'monster';
		$this->power = $power;
		$this->scale = $scale;
		$this->worth = $worth;
	}

	public function getPower(){

        return $this->power;

	}

	public function reducePower(){
		$this->power -= $this->scale;
	}

	public function GivePoints(): int
    {
		$this->setEmpty();
        return $this->worth;
	}

}

class Player{
	protected int $points;
	protected $pos_x;
	protected $pos_y;
	protected bool $currently_in_dungeon;
    protected $grid;
    protected $finish_x;
    protected $finish_y;

	public function __construct(){
	    $this->points = 0;
	    $this->currently_in_dungeon = False;
    }

    public function gotoDungeon(Dungeon $dungeon){
        $this->currently_in_dungeon = True;
        $this->pos_x = $dungeon->getStartPos()[0];
        $this->pos_y = $dungeon->getStartPos()[1];
        $this->grid = $dungeon->getGrid();
        $this->finish_x = $dungeon->getFinishPos()[0];
        $this->finish_y = $dungeon->getFinishPos()[1];

        echo 'PLAYER ENTERED THE DUNGEON', PHP_EOL;
        $this->checkRoom($this->pos_x, $this->pos_y);
    }

    public function checkRoom(int $x, int $y){
	    if($x>= 0 && $y>=0 && isset($this->grid[$x][$y]) && $this->grid[$x][$y] instanceof EmptyRoom){
	        $this->pos_x = $x;
	        $this->pos_y = $y;
	        if ($x == $this->finish_x && $y == $this->finish_y){
	            $this->currently_in_dungeon = False;
	            echo 'PLAYER EXITED THE DUNGEON!', ' PLAYER HAS ', $this->points, ' POINTS', PHP_EOL;
	            echo  PHP_EOL;
	            return;
            }

	        switch($this->grid[$x][$y]->getType()) {
                case 'treasure':
                    echo 'TREASURE ROOM', PHP_EOL;
                    $this->TreasureAction($this->grid[$x][$y]);
                    break;
                case 'monster':
                    echo 'MONSTER ROOM', PHP_EOL;
                    $this->MonsterAction($this->grid[$x][$y]);
                    break;
                case 'empty':
                    echo 'EMPTY ROOM', PHP_EOL;
                    break;
            }
        }
	    else{
	        echo 'DEAD END', PHP_EOL;
        }
    }
    public function TreasureAction(TreasureRoom $treasureRoom)
    {
        $temp = $this->points;
        $this->points += $treasureRoom->GivePoint();
        echo 'PLAYER EARNED ', $this->points - $temp, ' PLAYER NOW HAS ', $this->points, PHP_EOL;
    }

    public function MonsterAction(MonsterRoom $monsterRoom)
    {
        $temp = rand(1, 20);
        echo 'POWER OF PLAYER ', $temp, ' vs POWER OF MONSTER ', $monsterRoom->getPower(). PHP_EOL;
        if ($temp>$monsterRoom->getPower()){
            $temp = $this->points;
            $this->points += $monsterRoom->GivePoints();
            echo 'PLAYER EARNED ', $this->points - $temp, ' PLAYER NOW HAS ', $this->points, PHP_EOL;
        }
        else{
            $monsterRoom->reducePower();
            $this->MonsterAction($monsterRoom);
        }
    }

    public function ifPlayerInsideDungeon(): bool
    {
        if ($this->currently_in_dungeon){
            return True;
        }
        else{
            echo 'PLAYER CANNOT MOVE OUTSIDE OF DUNGEON!', PHP_EOL;
            return False;
        }
    }

    public function goRight()
    {
        if ($this->ifPlayerInsideDungeon()) {
            $this->checkRoom($this->pos_x,$this->pos_y+1);
        }
    }

    public function goLeft()
    {
        if ($this->ifPlayerInsideDungeon())  {
            $this->checkRoom($this->pos_x,$this->pos_y-1);
        }
    }

    public function goUp()
    {
        if ($this->ifPlayerInsideDungeon())  {
            $this->checkRoom($this->pos_x-1, $this->pos_y);
        }
    }

    public function goDown()
    {
        if ($this->ifPlayerInsideDungeon())  {
            $this->checkRoom($this->pos_x+1,$this->pos_y);
        }
    }

    public function checkPoints(){
	    echo $this->points, PHP_EOL;;
    }




}
$dung1 = new Dungeon(0,0,2,2);  // created sample dungeon.
$dung1->createMonsterRoom(0,1,50,10,10);
$dung1->createTreasureRoom(1,0,2);   // 3 rarities: 0, 1, 2.
$dung1->createEmptyRoom(1,1);
$dung1->createEmptyRoom(2,1);

$test = $dung1->getStartPos();

$player = new Player();

$player->gotoDungeon($dung1);
$player->goDown();
$player->goUp();
$player->goRight();
$player->goRight();
$player->goDown();
$player->goDown();
$player->goRight(); // Finished the sample dungeon dung1.

$player->goDown();


?>