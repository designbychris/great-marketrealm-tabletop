<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Tabletop\Vision\Services\VisionBarrierManager;
use Throwable;
defined('ABSPATH') || exit;
final class VisionBarrierAjaxController
{
    public function __construct(private VisionBarrierManager $vision){}
    public function add():void{$this->respond(fn()=>['barrier'=>$this->vision->add($this->tableId(),get_current_user_id(),sanitize_key((string)($_POST['type']??'')),(int)($_POST['x1']??0),(int)($_POST['y1']??0),(int)($_POST['x2']??0),(int)($_POST['y2']??0))->toArray()]);}
    public function toggle():void{$this->respond(fn()=>['barrier'=>$this->vision->toggleDoor($this->tableId(),get_current_user_id(),sanitize_text_field((string)($_POST['barrier_id']??'')))->toArray()]);}
    public function remove():void{$this->respond(function():array{$this->vision->remove($this->tableId(),get_current_user_id(),sanitize_text_field((string)($_POST['barrier_id']??'')));return ['removed'=>true];});}
    private function respond(callable $action):void{if(!is_user_logged_in()){wp_send_json_error(['message'=>'Authentication required.'],401);}check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');try{wp_send_json_success($action());}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()],403);}}
    private function tableId():string{return sanitize_text_field((string)($_POST['table_id']??''));}
}
