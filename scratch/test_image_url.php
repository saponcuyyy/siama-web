<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Slider;
use Illuminate\Support\Facades\Http;

$slider = Slider::first();
if (!$slider) {
    echo "No sliders found\n";
    exit;
}

$url = $slider->image_url;
echo "Testing URL: $url\n";

try {
    $response = Http::get($url);
    echo "Status: " . $response->status() . "\n";
    if ($response->failed()) {
        echo "Body: " . substr($response->body(), 0, 200) . "...\n";
    } else {
        echo "Success! Image is accessible.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
