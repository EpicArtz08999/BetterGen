<?php

/*
 * BetterMesaPlains from BetterGen
 * Copyright (C) Ad5001 2017
 * Licensed under the BoxOfDevs Public General LICENSE which can be found in the file LICENSE in the root directory
 * @author ad5001
 */
namespace Ad5001\BetterGen\biome;

use pocketmine\level\generator\normal\biome\SandyBiome;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\biome\Biome;
use pocketmine\block\Block;
use pocketmine\block\GoldOre;
use Ad5001\BetterGen\populator\CactusPopulator;
use Ad5001\BetterGen\populator\DeadbushPopulator;
use Ad5001\BetterGen\populator\SugarCanePopulator;

class BetterMesaPlains extends SandyBiome {
	public function __construct() {
		$deadBush = new DeadbushPopulator ();
		$deadBush->setBaseAmount ( 1 );
		$deadBush->setRandomAmount ( 2 );
		
		$cactus = new CactusPopulator ();
		$cactus->setBaseAmount ( 1 );
		$cactus->setRandomAmount ( 2 );
		
		$sugarCane = new SugarCanePopulator ();
		$sugarCane->setRandomAmount ( 20 );
		$sugarCane->setBaseAmount ( 3 );
		
		$ores = new Ore ();
		$ores->setOreTypes ( [ 
				new OreType ( new GoldOre (), 20, 8, 0, 32 ) 
		] );
		
		$this->addPopulator ( $cactus );
		$this->addPopulator ( $deadBush );
		$this->addPopulator ( $sugarCane );
		$this->addPopulator ( $ores );
		
		$this->setElevation ( 62, 67 );
		// $this->setElevation(66, 70);
		
		$this->temperature = 0.6;
		$this->rainfall = 0;
		$this->setGroundCover ( [ 
				Block::get ( Block::SAND, 1 ),
				Block::get ( Block::SAND, 1 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 7 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 12 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 12 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 12 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 14 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 14 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 14 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 4 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 7 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 0 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 7 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::HARDENED_CLAY, 0 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::STAINED_HARDENED_CLAY, 1 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ),
				Block::get ( Block::RED_SANDSTONE, 0 ) 
		] );
	}
	public function getName(): string {
		return "Better Mesa Plains";
	}
	
	/*
	 * Returns biome id
	 */
	public function getId() {
		return 40;
	}
}