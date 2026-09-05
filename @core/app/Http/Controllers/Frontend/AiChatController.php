<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class AiChatController extends Controller
{
    
    public function send(Request $request)
    {
        $data = $request->validate([
            'message'  => ['required', 'string', 'max:3000'],
            'history'  => ['nullable', 'array'],
            'language' => ['nullable', 'in:en,sw'],
        ]);

        $lang = $data['language'] ?? 'en';
        $userMessage = trim($data['message']);

        // Keep only last 8 messages for cost control
        $history = collect($data['history'] ?? [])
            ->take(-8)
            ->map(function ($m) {
                $role = ($m['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
                return [
                    'role' => $role,
                    'content' => (string) ($m['content'] ?? ''),
                ];
            })
            ->values()
            ->all();

        $systemEn = "You are HudumaPortal Bot, the official assistant for HudumaPortal (hudumaportal.co.tz).

                Business info:
                - Site name: HudumaPortal
                - Contact phone/WhatsApp: +255752118496, +255712146132
                - Email: Contact@hudumaportal.co.tz
                - Customer support hours: 08:00 AM to 08:00 PM, Monday–Sunday
                
                Your job:
                - Help users understand the website and book services.
                - If user asks “contact / help / support”, share the phone numbers, email, and support hours.
                - If user asks about booking, explain the booking steps and what details are needed (service type, location, date/time).
                Rules:
                - Be concise and friendly.
                - Never ask for passwords/OTP or sensitive info.
                - If user needs a human, direct them to WhatsApp numbers above during support hours.
                Always reply in English.";
        
                $systemSw = "Wewe ni HudumaPortal Bot, msaidizi rasmi wa HudumaPortal (hudumaportal.co.tz).

                Taarifa za biashara:
                - Jina la tovuti: HudumaPortal
                - Simu/WhatsApp: +255752118496, +255712146132
                - Barua pepe: Contact@hudumaportal.co.tz
                - Muda wa huduma kwa wateja: 08:00 AM hadi 08:00 PM, Jumatatu–Jumapili
                
                Kazi yako:
                - Kusaidia watumiaji kuelewa tovuti na ku-book huduma.
                - Mtumiaji akiuliza “mawasiliano / msaada / support”, toa namba za simu, barua pepe, na muda wa msaada.
                - Akiuliza jinsi ya ku-book, eleza hatua na taarifa zinazohitajika (aina ya huduma, eneo, tarehe/muda).
                Sheria:
                - Jibu kwa ufupi na kwa msaada.
                - Usiombe nenosiri/OTP au taarifa nyeti.
                - Akihitaji binadamu, mwelekeze WhatsApp namba hizo ndani ya muda wa msaada.
                Jibu kwa Kiswahili kila wakati.";

        $system = [
            'role' => 'system',
            'content' => ($lang === 'sw')
                ? ($systemSw . "\nAlways reply in Swahili (Kiswahili).")
                : ($systemEn . "\nAlways reply in English."),
        ];

        $messages = array_merge([$system], $history, [
            ['role' => 'user', 'content' => $userMessage],
        ]);

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Missing OPENAI_API_KEY in .env'], 500);
        }

        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => $messages,
            "temperature" => 0.4,
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return response()->json(["reply" => "cURL error: " . $err], 500);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $msg = $decoded['error']['message'] ?? 'OpenAI API error';
            return response()->json(["reply" => $msg], $httpCode);
        }

        $reply = $decoded['choices'][0]['message']['content'] ?? '';

        return response()->json(["reply" => $reply]);
    }
}