<?php

/*
 * BetterNormal from BetterGen
 * Copyright (C) Ad5001 2017
 * Licensed under the BoxOfDevs Public General LICENSE which can be found in the file LICENSE in the root directory
 * @author ad5001
 */
namespace Ad5001\BetterGen\generator;

use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\level\Level;
use Ad5001\BetterGen\biome\BetterForest;
use Ad5001\BetterGen\biome\BetterDesert;
use Ad5001\BetterGen\biome\BetterIcePlains;
use Ad5001\BetterGen\biome\BetterMesa;
use Ad5001\BetterGen\biome\BetterMesaPlains;
use Ad5001\BetterGen\biome\BetterRiver;
use Ad5001\BetterGen\biome\Mountainable;
use Ad5001\BetterGen\populator\CavePopulator;
use Ad5001\BetterGen\populator\RavinePopulator;
use Ad5001\BetterGen\populator\LakePopulator;
use Ad5001\BetterGen\populator\MineshaftPopulator;
use Ad5001\BetterGen\populator\FloatingIslandPopulator;

class BetterNormal extends Generator {
	const NOT_OVERWRITABLE = [ 
			Block::STONE,
			Block::GRAVEL,
			Block::BEDROCK,
			Block::DIAMOND_ORE,
			Block::GOLD_ORE,
			Block::LAPIS_ORE,
			Block::REDSTONE_ORE,
			Block::IRON_ORE,
			Block::COAL_ORE,
			Block::WATER,
			Block::STILL_WATER 
	];
	protected $selector;
	protected $level;
	protected $random;
	protected $populators = [ ];
	protected $generationPopulators = [ ];
	public static $biomes = [ ];
	public static $biomeById = [ ];
	public static $levels = [ ];
	protected static $GAUSSIAN_KERNEL = null; // From main class
	protected static $SMOOTH_SIZE = 2;
	protected $waterHeight = 63;
	
	/*
	 * Picks a biome by X and Z
	 * @param	$x	int
	 * @param	$z 	int
	 * @return Biome
	 */
	public function pickBiome($x, $z) {
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed ();
		$hash *= $hash + 223;
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if ($xNoise == 3) {
			$xNoise = 1;
		}
		if ($zNoise == 3) {
			$zNoise = 1;
		}
		
		$b = $this->selector->pickBiome ( $x + $xNoise - 1, $z + $zNoise - 1 );
		if ($b instanceof Mountainable && $this->random->nextBoundedInt ( 1000 ) < 3) {
			$b = clone $b;
			$b->setElevation ( $b->getMinElevation () + (50 * $b->getMinElevation () / 100), $b->getMaxElevation () + (50 * $b->getMinElevation () / 100) );
		}
		return $b;
	}
	public function init(ChunkManager $level, Random $random) {
		$this->level = $level;
		$this->random = $random;
		
		self::$levels [] = $level;
		
		$this->random->setSeed ( $this->level->getSeed () );
		$this->noiseBase = new Simplex ( $this->random, 4, 1 / 4, 1 / 32 );
		$this->random->setSeed ( $this->level->getSeed () );
		
		$this->registerBiome ( Biome::getBiome ( Biome::OCEAN ) );
		$this->registerBiome ( Biome::getBiome ( Biome::PLAINS ) );
		$this->registerBiome ( new BetterDesert () );
		$this->registerBiome ( new BetterMesa () );
		$this->registerBiome ( new BetterMesaPlains () );
		$this->registerBiome ( Biome::getBiome ( Biome::TAIGA ) );
		$this->registerBiome ( Biome::getBiome ( Biome::SWAMP ) );
		$this->registerBiome ( new BetterRiver () );
		$this->registerBiome ( new BetterIcePlains () );
		$this->registerBiome ( new BetterForest ( 0, [ 
				0.6,
				0.5 
		] ) );
		$this->registerBiome ( new BetterForest ( 1, [ 
				0.7,
				0.8 
		] ) );
		$this->registerBiome ( new BetterForest ( 2, [ 
				0.6,
				0.4 
		] ) );
		
		$this->selector = new BetterBiomeSelector ( $random, [ 
				self::class,
				"getBiome" 
		], self::getBiome ( 0, 0 ) );
		
		foreach ( self::$biomes as $rain ) {
			foreach ( $rain as $biome ) {
				$this->selector->addBiome ( $biome );
			}
		}
		
		$this->selector->recalculate ();
		
		$cover = new GroundCover ();
		$this->generationPopulators [] = $cover;
		
		$cave = new CavePopulator ();
		$cave->setBaseAmount ( 0 );
		$cave->setRandomAmount ( 2 );
		$this->populators [] = $cave;
		
		$ravine = new RavinePopulator ();
		$ravine->setBaseAmount ( 0 );
		$ravine->setRandomAmount ( 51 );
		$this->populators [] = $ravine;
		
		$mineshaft = new MineshaftPopulator ();
		$mineshaft->setBaseAmount ( 0 );
		$mineshaft->setRandomAmount ( 102 );
		$this->populators [] = $mineshaft;
		
		$lake = new LakePopulator ();
		$lake->setBaseAmount ( 0 );
		$lake->setRandomAmount ( 1 );
		$this->generationPopulators [] = $lake;
		
		$fisl = new FloatingIslandPopulator();
		$fisl->setBaseAmount ( 0 );
		$fisl->setRandomAmount ( 132 );
		$this->populators [] = $fisl;
		
		$ores = new Ore ();
		$ores->setOreTypes ( [ 
				new OreType ( new CoalOre (), 20, 16, 0, 128 ),
				new OreType ( new IronOre (), 20, 8, 0, 64 ),
				new OreType ( new RedstoneOre (), 8, 7, 0, 16 ),
				new OreType ( new LapisOre (), 1, 6, 0, 32 ),
				new OreType ( new GoldOre (), 2, 8, 0, 32 ),
				new OreType ( new DiamondOre (), 1, 7, 0, 16 ),
				new OreType ( new Dirt (), 20, 32, 0, 128 ),
				new OreType ( new Gravel (), 10, 16, 0, 128 ) 
		] );
		$this->populators [] = $ores;
	}
	
	/*
	 * Adds a biome to the selector. Do not use this method directly use Main::registerBiome which registers it properly
	 * @param $biome Biome
	 * @return bool
	 */
	public static function registerBiome(Biome $biome): bool {
		foreach ( self::$levels as $lvl )
			if (isset ( $lvl->selector ))
				$lvl->selector->addBiome ( $biome ); // If no selector created, it would cause errors. These will be added when selectoes
		if (! isset ( self::$biomes [( string ) $biome->getRainfall ()] ))
			self::$biomes [( string ) $biome->getRainfall ()] = [ ];
		self::$biomes [( string ) $biome->getRainfall ()] [( string ) $biome->getTemperature ()] = $biome;
		ksort ( self::$biomes [( string ) $biome->getRainfall ()] );
		ksort ( self::$biomes );
		self::$biomeById[$biome->getId()] = $biome;
		return true;
	}
	
	/*
	 * Returns a biome by temperature
	 * @param $temperature float
	 * @param $rainfall float
	 */
	public static function getBiome($temperature, $rainfall) {
		if (! isset ( self::$biomes [( string ) round ( $rainfall, 1 )] )) {
			while ( ! isset ( self::$biomes [( string ) round ( $rainfall, 1 )] ) ) {
				if (abs ( $rainfall - round ( $rainfall, 1 ) ) >= 0.05)
					$rainfall += 0.1;
				if (abs ( $rainfall - round ( $rainfall, 1 ) ) < 0.05)
					$rainfall -= 0.1;
				if (round ( $rainfall, 1 ) < 0)
					$rainfall = 0;
				if (round ( $rainfall, 1 ) >= 0.9)
					$rainfall = 0.9;
			}
		}
		$b = self::$biomes [( string ) round ( $rainfall, 1 )];
		foreach ( $b as $t => $biome ) {
			if ($temperature <= ( float ) $t) {
				$ret = $biome;
				break;
			}
		}
		if (is_string ( $ret )) {
			$ret = new $ret ();
		} else {
			return $ret;
		}
	}
	
	/*
	 * Returns a biome by its id
	 * @param 	$id 	int
	 * @return	Biome
	 */
	public function getBiomeById(int $id): Biome {
		return self::$biomeById[$id] ?? self::$biomeById(Biome::OCEAN);
	}
	
	/*
	 * Generates a chunk.
	 * Cloning method to make it work with new methods.
	 * @param $chunkX int
	 * @param $chunkZ int
	 */
	public function generateChunk($chunkX, $chunkZ) {
		$this->reRegisterBiomes ();
		
		$this->random->setSeed ( 0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed () );
		
		$noise = Generator::getFastNoise3D ( $this->noiseBase, 16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16 );
		
		$chunk = $this->level->getChunk ( $chunkX, $chunkZ );
		
		$biomeCache = [ ];
		
		for($x = 0; $x < 16; ++ $x) {
			for($z = 0; $z < 16; ++ $z) {
				$minSum = 0;
				$maxSum = 0;
				$weightSum = 0;
				
				$biome = $this->pickBiome ( $chunkX * 16 + $x, $chunkZ * 16 + $z );
				$chunk->setBiomeId ( $x, $z, $biome->getId () );
				
				for($sx = - self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++ $sx) {
					for($sz = - self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++ $sz) {
						
						$weight = self::$GAUSSIAN_KERNEL [$sx + self::$SMOOTH_SIZE] [$sz + self::$SMOOTH_SIZE];
						
						if ($sx === 0 and $sz === 0) {
							$adjacent = $biome;
						} else {
							$index = Level::chunkHash ( $chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz );
							if (isset ( $biomeCache [$index] )) {
								$adjacent = $biomeCache [$index];
							} else {
								$biomeCache [$index] = $adjacent = $this->pickBiome ( $chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz );
							}
						}
						$minSum += ($adjacent->getMinElevation () - 1) * $weight;
						$maxSum += $adjacent->getMaxElevation () * $weight;
						
						$weightSum += $weight;
					}
				}
				
				$minSum /= $weightSum;
				$maxSum /= $weightSum;
				
				$smoothHeight = ($maxSum - $minSum) / 2;
				
				for($y = 0; $y < 128; ++ $y) {
					if ($y < 3 || ($y < 5 && $this->random->nextBoolean ())) {
						$chunk->setBlockId ( $x, $y, $z, Block::BEDROCK );
						continue;
					}
					$noiseValue = $noise [$x] [$z] [$y] - 1 / $smoothHeight * ($y - $smoothHeight - $minSum);
					
					if ($noiseValue > 0) {
						$chunk->setBlockId ( $x, $y, $z, Block::STONE );
					} elseif ($y <= $this->waterHeight) {
						$chunk->setBlockId ( $x, $y, $z, Block::STILL_WATER );
					}
				}
			}
		}
		
		foreach ( $this->generationPopulators as $populator ) {
			$populator->populate ( $this->level, $chunkX, $chunkZ, $this->random );
		}
	}
	
	/*
	 * Populates a chunk.
	 * @param $chunkX int
	 * @param $chunk2 int
	 */
	public function populateChunk($chunkX, $chunkZ) {
		$this->random->setSeed ( 0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed () );
		foreach ( $this->populators as $populator ) {
			$populator->populate ( $this->level, $chunkX, $chunkZ, $this->random );
		}
		
		// Filling lava (lakes & rivers underground)...
		for($x = $chunkX; $x < $chunkX + 16; $x ++)
			for($z = $chunkZ; $z < $chunkZ + 16; $z ++)
				for($y = 1; $y < 11; $y ++) {
					if (! in_array ( $this->level->getBlockIdAt ( $x, $y, $z ), self::NOT_OVERWRITABLE ))
						$this->level->setBlockIdAt ( $x, $y, $z, Block::LAVA );
				}
		
		$chunk = $this->level->getChunk ( $chunkX, $chunkZ );
		$biome = Biome::getBiome ( $chunk->getBiomeId ( 7, 7 ) );
		$biome->populateChunk ( $this->level, $chunkX, $chunkZ, $this->random );
	}
	
	/*
	 * Constructs the class
	 * @param $options array
	 */
	public function __construct(array $options = []) {
		if (self::$GAUSSIAN_KERNEL === null) {
			self::generateKernel ();
		}
	}
	
	/*
	 * Generates the genration kernel based on smooth size (here 2)
	 */
	private static function generateKernel() {
		self::$GAUSSIAN_KERNEL = [ ];
		
		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;
		
		for($sx = - self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++ $sx) {
			self::$GAUSSIAN_KERNEL [$sx + self::$SMOOTH_SIZE] = [ ];
			
			for($sz = - self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++ $sz) {
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				self::$GAUSSIAN_KERNEL [$sx + self::$SMOOTH_SIZE] [$sz + self::$SMOOTH_SIZE] = $bellHeight * exp ( - ($bx * $bx + $bz * $bz) / 2 );
			}
		}
	}
	
	//  Returns the name of the generator
	public function getName() {
		return "betternormal";
	}
	
	/*
	 * Gives the generators settings.
	 * @return array
	 */
	public function getSettings(): array {
		return [ ];
	}
	public function getSpawn() {
		return new Vector3 ( 127.5, 128, 127.5 );
	}
	
	/*
	 * Returns a safe spawn location
	 */
	public function getSafeSpawn() {
		return new Vector3 ( 127.5, $this->getHighestWorkableBlock ( 127, 127 ), 127.5 );
	}
	
	/*
	 * Gets the top block (y) on an x and z axes
	 * @param $x int
	 * @param $z int
	 */
	protected function getHighestWorkableBlock($x, $z) {
		for($y = 127; $y > 0; -- $y) {
			$b = $this->level->getBlockIdAt ( $x, $y, $z );
			if ($b === Block::DIRT or $b === Block::GRASS or $b === Block::PODZOL) {
				break;
			} elseif ($b !== 0 and $b !== Block::SNOW_LAYER) {
				return - 1;
			}
		}
		
		return ++ $y;
	}
	
	/*
	 * Re registers all biomes for async
	 */
	public function reRegisterBiomes() {
		$reflection = new \ReflectionClass ( 'pocketmine\\level\\generator\\biome\\Biome' );
		$register = $reflection->getMethod ( 'register' );
		$register->setAccessible ( true );
		foreach ( self::$biomes as $rainfall => $arr ) {
			foreach ( $arr as $tmp => $biome ) {
				$register->invoke ( null, $biome->getId (), $biome );
			}
		}
	}
}