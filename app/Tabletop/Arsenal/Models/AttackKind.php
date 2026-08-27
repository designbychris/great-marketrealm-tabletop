<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Arsenal\Models;
use InvalidArgumentException;
defined('ABSPATH') || exit;
final class AttackKind
{
    public const MELEE_WEAPON='melee-weapon';
    public const RANGED_WEAPON='ranged-weapon';
    public const NATURAL='natural';
    public const SPELL='spell';
    public const IMPROVISED='improvised';
    public static function all():array{return [self::MELEE_WEAPON,self::RANGED_WEAPON,self::NATURAL,self::SPELL,self::IMPROVISED];}
    public static function assert(string $kind):string
    {
        if(!in_array($kind,self::all(),true)){throw new InvalidArgumentException('Unsupported arsenal attack kind.');}
        return $kind;
    }
}
