<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationCaptcha
{
    // Render a raster image: neither HTML nor image metadata contains the answer.
    private const DIGITS = [
        ['01110', '10001', '10011', '10101', '11001', '10001', '01110'],
        ['00100', '01100', '00100', '00100', '00100', '00100', '01110'],
        ['01110', '10001', '00001', '00010', '00100', '01000', '11111'],
        ['11110', '00001', '00001', '01110', '00001', '00001', '11110'],
        ['00010', '00110', '01010', '10010', '11111', '00010', '00010'],
        ['11111', '10000', '10000', '11110', '00001', '00001', '11110'],
        ['01110', '10000', '10000', '11110', '10001', '10001', '01110'],
        ['11111', '00001', '00010', '00100', '01000', '01000', '01000'],
        ['01110', '10001', '10001', '01110', '10001', '10001', '01110'],
        ['01110', '10001', '10001', '01111', '00001', '00001', '01110'],
    ];

    public function issue(Request $request): array
    {
        $answer = '';
        for ($i = 0; $i < 6; $i++) {
            $answer .= random_int(0, 9);
        }
        $id = Str::random(40);
        $challenges = array_filter($request->session()->get('registration_captchas', []),
            fn (array $challenge) => $challenge['expires_at'] > now()->timestamp);
        $challenges = array_slice($challenges, -4, null, true);
        $challenges[$id] = ['hash' => hash('sha256', $answer), 'expires_at' => now()->addMinutes(10)->timestamp];
        $request->session()->put('registration_captchas', $challenges);

        return ['id' => $id, 'image' => 'data:image/png;base64,'.base64_encode($this->image($answer))];
    }

    public function consume(Request $request): bool
    {
        $id = $request->input('captcha_id');
        $answer = $request->input('captcha');
        if (! is_string($id) || ! preg_match('/^[a-zA-Z0-9]{40}$/D', $id)) {
            return false;
        }
        $challenge = $request->session()->pull('registration_captchas.'.$id);

        return is_array($challenge) && $challenge['expires_at'] > now()->timestamp
            && is_string($answer) && preg_match('/^[0-9]{6}$/D', $answer)
            && hash_equals($challenge['hash'], hash('sha256', $answer));
    }

    private function image(string $answer): string
    {
        // A small RGB PNG built with zlib, so XAMPP does not need GD or an image package.
        $width = 235;
        $height = 72;
        $rows = array_fill(0, $height, str_repeat("\xf1\xf5\xf9", $width));
        $pixel = static function (int $x, int $y, string $color) use (&$rows, $width, $height): void {
            if ($x >= 0 && $x < $width && $y >= 0 && $y < $height) {
                $rows[$y] = substr_replace($rows[$y], $color, $x * 3, 3);
            }
        };
        for ($i = 0; $i < 600; $i++) {
            $pixel(random_int(0, $width - 1), random_int(0, $height - 1), "\x94\xa3\xb8");
        }
        foreach (str_split($answer) as $index => $digit) {
            $angle = deg2rad(random_int(-10, 10));
            $baseX = 14 + $index * 35;
            $baseY = random_int(15, 22);
            $color = chr(random_int(15, 40)).chr(random_int(23, 50)).chr(random_int(42, 70));
            foreach (self::DIGITS[(int) $digit] as $row => $bits) {
                for ($column = 0; $column < 5; $column++) {
                    if ($bits[$column] !== '1') {
                        continue;
                    }
                    for ($dy = 0; $dy < 5; $dy++) {
                        for ($dx = 0; $dx < 5; $dx++) {
                            $x = $column * 5 + $dx - 12;
                            $y = $row * 5 + $dy - 17;
                            $pixel((int) round($baseX + 12 + $x * cos($angle) - $y * sin($angle)),
                                (int) round($baseY + 17 + $x * sin($angle) + $y * cos($angle)), $color);
                        }
                    }
                }
            }
        }
        for ($i = 0; $i < 5; $i++) {
            $start = random_int(0, $height - 1);
            $end = random_int(0, $height - 1);
            for ($x = 0; $x < $width; $x++) {
                $pixel($x, (int) round($start + ($end - $start) * $x / ($width - 1)), "\x94\xa3\xb8");
            }
        }
        $chunk = static fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        return "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress("\x00".implode("\x00", $rows)))
            .$chunk('IEND', '');
    }
}
