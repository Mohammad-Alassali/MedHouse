<?php

namespace App\ScheduleObjects;

use App\Models\Product;

class DeleteExpiredProducts
{


    public function __invoke()
    {
        $products = Product::query()
            ->whereDate('expiration_date', '<=', date('y-m-d', strtotime('now')))
            ->get();
        if (count($products)) {
            foreach ($products as $product) {
                require_once(base_path('vendor/autoload.php'));
//        $ultramsg_token=env('WHATSAPP_TOKEN'); // Ultramsg.com token
//        $instance_id=env('WHATSAPP_ID'); // Ultramsg.com instance id
//        $client = new WhatsAppApi($ultramsg_token,$instance_id);
//        $number = "+963".substr($request['phone_number'], 1, 9);
//        $to=$number;
//        $body=trans('messages.delete',['name'=>$product['commercial_name']]);
//        $client->sendChatMessage($to,$body);
//        return $this->success(null, 'we send the code');
                $product->delete();
            }
        }
    }


}
