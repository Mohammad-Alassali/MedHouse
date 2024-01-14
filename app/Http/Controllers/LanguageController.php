<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Change favorite language
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $lang = 'en';
        if (auth()->user()->lang == 'en') {
            $lang = 'ar';
        }
        auth()->user()->update([
            'lang' => $lang
        ]);
        return $this->success(null);
    }
}
