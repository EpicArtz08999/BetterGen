<?php

/*
 * DeadbushPopulator from BetterGen
 * Copyright (C) Ad5001 2017
 * Licensed under the BoxOfDevs Public General LICENSE which can be found in the file LICENSE in the root directory
 * @author ad5001
 */

namespace Ad5001\BetterGen\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use Ad5001\BetterGen\populator\AmountPopulator;

class DeadbushPopulator extends AmountPopulator {
	protected $level;
	
	/*
	 * Populate the chunk
	 * @param $level pocketmine\level\ChunkManager
	 * @param $chunkX int
	 * @param $chunkZ int
	 * @param $random pocketmine\utils\Random
	 */
	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random) {
		$this->level = $level;
		$amount = $this->getAmount ( $random );
		for($i = 0; $i < $amount; ++ $i) {
			$x = $random->nextRange ( $chunkX * 16, $chunkX * 16 + 15 );
			$z = $random->nextRange ( $chunkZ * 16, $chunkZ * 16 + 15 );
			$y = $this->getHighestWorkableBlock ( $x, $z );
			if ($y !== - 1 && $level->getBlockIdAt ( $x, $y - 1, $z ) == Block::SAND) {
				$level->setBlockIdAt ( $x, $y, $z, Block::DEAD_BUSH );
				$level->setBlockDataAt ( $x, $y, $z, 1 );
			}
		}
	}
	
	/*
	 * Gets the top block (y) on an x and z axes
	 * @param $x int
	 * @param $z int
	 */
	protected function getHighestWorkableBlock($x, $z) {
		for($y = 127; $y >= 0; -- $y) {
			$b = $this->level->getBlockIdAt ( $x, $y, $z );
			if ($b !== Block::AIR and $b !== Block::LEAVES and $b !== Block::LEAVES2) {
				break;
			}
		}
		return $y === 0 ? - 1 : ++ $y;
	}
}