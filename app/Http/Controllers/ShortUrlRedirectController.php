<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;

class ShortUrlRedirectController extends Controller
{
    public function redirect($code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();

        $shortUrl->incrementClicks();

        return redirect($shortUrl->original_url);
    }
}
