<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Http;
use GreatMarketrealmTabletop\Tabletop\Testing\TestTableProvisioner;
use Throwable;
defined('ABSPATH') || exit;
final class TestTableAjaxController
{
    public function __construct(private TestTableProvisioner $provisioner){}
    public function prepare(): void
    {
        if(!is_user_logged_in()){wp_send_json_error(['message'=>'Authentication required.'],401);}
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION,'nonce');
        try{
            $tableId=$this->provisioner->prepare(get_current_user_id());
            wp_send_json_success(['table_id'=>$tableId]);
        }catch(Throwable $exception){
            wp_send_json_error(['message'=>$exception->getMessage()],400);
        }
    }
}
