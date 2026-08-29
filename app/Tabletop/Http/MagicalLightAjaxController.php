<?php

declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Integration\Companion\CompanionCharacterGateway;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenType;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\MagicalLightRepository;
use GreatMarketrealmTabletop\Tabletop\Light\Models\MagicalLight;
use RuntimeException;
use Throwable;

final class MagicalLightAjaxController
{
    public function __construct(private CompanionCharacterGateway $companion,private TableMembershipRepository $members,private TableSceneRepository $scenes,private TableTokenRepository $tokens,private MagicalLightRepository $lights){}
    public function toggle(): void
    {
        if(!is_user_logged_in()){wp_send_json_error(['message'=>'Authentication required.'],401);}
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try{
            $tableId=sanitize_text_field((string)($_POST['table_id']??''));
            $spellId=sanitize_key((string)($_POST['spell_id']??''));
            $userId=get_current_user_id();
            $member=$this->members->find($tableId,$userId);
            if($member===null||$member->status()!==TableMemberStatus::ACTIVE) throw new RuntimeException('Only an active Table member may weave magical light.');
            $characterId=(string)($member->companionCharacterId()??'');
            if($characterId==='') throw new RuntimeException('Bring a Companion Character to the Table before invoking Shelfshine.');
            $character=$this->companion->characterForUser($userId,$characterId);
            if(!is_array($character)) throw new RuntimeException('Your seated Companion Character could not be verified.');
            $spell=$this->spell($character,$spellId);
            $illum=is_array($spell['illumination']??null)?$spell['illumination']:[];
            if(($illum['source']??'')!=='magical') throw new RuntimeException('That spell does not carry Companion-certified illumination.');
            $scene=null; foreach($this->scenes->forTable($tableId) as $candidate){if($candidate->isActive()){$scene=$candidate;break;}}
            if($scene===null) throw new RuntimeException('No active Chamber is ready to receive magical light.');
            $token=null; foreach($this->tokens->forScene($tableId,$scene->id()) as $candidate){if($candidate->type()===TableTokenType::CHARACTER&&$candidate->controllerUserId()===$userId&&(string)($candidate->sourceReference()??'')===$characterId){$token=$candidate;break;}}
            if($token===null) throw new RuntimeException('Place your Companion Character in this Chamber before invoking Shelfshine.');
            $existing=$this->lights->forToken($tableId,$scene->id(),$token->id());
            if($existing!==null){$this->lights->deleteForToken($tableId,$scene->id(),$token->id());wp_send_json_success(['active'=>false,'message'=>$existing->label().' fades from the Chamber.']);return;}
            $bright=max(0,(int)($illum['bright_feet']??0)); $dim=max(0,(int)($illum['dim_feet']??0));
            if($bright+$dim<1) throw new RuntimeException('That spell has no usable illumination radius.');
            $duration=max(1,(int)($illum['duration_seconds']??3600));
            $label=(string)($spell['label']??'Magical Light');
            $light=new MagicalLight('magic-'.wp_generate_uuid4(),$tableId,$scene->id(),$token->id(),$userId,$spellId,$label,$bright,$dim,time()+$duration);
            $this->lights->save($light);
            wp_send_json_success(['active'=>true,'message'=>$label.' blooms across the shelves: '.$bright.' ft bright + '.$dim.' ft dim light.']);
        }catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],400);}
    }
    /** @param array<string,mixed> $character @return array<string,mixed> */
    private function spell(array $character,string $spellId): array
    {
        $spells=is_array($character['play']['spellcasting']['spells']??null)?$character['play']['spellcasting']['spells']:[];
        foreach($spells as $spell)if(is_array($spell)&&(string)($spell['id']??'')===$spellId)return $spell;
        throw new RuntimeException('That spell is not present in this Companion Character\'s Spell Pouch.');
    }
}
