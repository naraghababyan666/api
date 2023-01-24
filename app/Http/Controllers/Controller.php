<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
/**
 * @OA\Info(
 *    title="Upstart API",
 *    version="1.0.0",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $language_id = 1;
    public function __construct(Request $request)
    {
        $headers = apache_request_headers();
        if(isset($headers['X-Language'])){
            $this->language_id = $headers['X-Language'];
        }

        return true;

    }

}
