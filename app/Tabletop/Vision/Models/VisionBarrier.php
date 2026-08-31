<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Vision\Models;
use InvalidArgumentException;
defined('ABSPATH') || exit;
final class VisionBarrier
{
    public const WALL='wall'; public const DOOR='door';
    public function __construct(private string $id,private string $sceneId,private string $type,private float $x1,private float $y1,private float $x2,private float $y2,private bool $open=false)
    {
        if(trim($id)===''||trim($sceneId)===''||!in_array($type,[self::WALL,self::DOOR],true)||($x1===$x2&&$y1===$y2)){throw new InvalidArgumentException('A vision barrier requires a valid identity, type and two distinct grid intersections.');}
        if($type===self::WALL){$this->open=false;}
    }
    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record):self{return new self((string)($record['id']??''),(string)($record['scene_id']??''),(string)($record['type']??''),(float)($record['x1']??0),(float)($record['y1']??0),(float)($record['x2']??0),(float)($record['y2']??0),(bool)($record['open']??false));}
    public function id():string{return $this->id;} public function sceneId():string{return $this->sceneId;} public function type():string{return $this->type;}
    public function x1():float{return $this->x1;} public function y1():float{return $this->y1;} public function x2():float{return $this->x2;} public function y2():float{return $this->y2;}
    public function isOpen():bool{return $this->open;} public function blocksSight():bool{return $this->type===self::WALL||!$this->open;}
    public function toggleDoor():void{if($this->type!==self::DOOR){throw new InvalidArgumentException('Only doors may be opened or closed.');}$this->open=!$this->open;}
    /** @return array<string,mixed> */
    public function toArray():array{return ['id'=>$this->id,'scene_id'=>$this->sceneId,'type'=>$this->type,'x1'=>$this->x1,'y1'=>$this->y1,'x2'=>$this->x2,'y2'=>$this->y2,'open'=>$this->open];}
}
