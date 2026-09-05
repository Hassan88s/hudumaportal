<?php

namespace App\PageBuilder\Traits;

use App\Helpers\LanguageHelper;
use Illuminate\Support\Collection;

trait LanguageFallbackForPageBuilder
{
    /**
     * Keep old addon frontend code working:
     * If a repeater saves title_en_GB/title_sw etc, auto-generate title_/description_/icon_ for the current locale.
     */
    public function get_settings()
    {
        $settings = parent::get_settings();

        $locale = app()->getLocale(); // example: en_GB or sw
        $fallback = config('app.fallback_locale', 'en_GB');

        // walk through settings and patch repeater-style arrays
        foreach ($settings as $key => $val) {
            if (is_array($val)) {
                $settings[$key] = $this->pbApplyRepeaterCompat($val, $locale, $fallback);
            }
        }

        return $settings;
    }

    public function setting_item($item){
        $settings = $this->get_settings();
        return $settings[$item] ?? null;
    }

    private function pbApplyRepeaterCompat(array $rep, string $locale, string $fallback): array
    {
        // Find fields like title_en_GB, title_sw, description_en_GB ...
        // For each base ("title", "description", "icon") create "title_" etc based on current locale.

        $langs = $this->pbLanguages();
        $chosenLang = $this->pbChooseLang($rep, $locale, $fallback, $langs);

        foreach ($rep as $k => $v) {
            // only care about arrays (because repeater values are arrays)
            if (!is_array($v)) continue;

            foreach ($langs as $lang) {
                $suffix = '_' . $lang;
                if (str_ends_with($k, $suffix)) {
                    $base = substr($k, 0, -strlen($suffix)); // e.g. title from title_en_GB

                    // Set base_ (old format) only once, using chosen language
                    $chosenKey = $base . '_' . $chosenLang;
                    if (!isset($rep[$base . '_']) && isset($rep[$chosenKey]) && is_array($rep[$chosenKey])) {
                        $rep[$base . '_'] = $rep[$chosenKey];
                    }

                    break;
                }
            }
        }

        return $rep;
    }

    private function pbLanguages(): array
    {
        if (class_exists(LanguageHelper::class)) {
            $all = LanguageHelper::all_languages();

            if ($all instanceof Collection) {
                $langs = $all->pluck('slug')->filter()->values()->toArray();
                return !empty($langs) ? $langs : ['en_GB', 'sw'];
            }

            if (is_array($all)) {
                $langs = array_values(array_filter(array_map(fn($l) => $l->slug ?? null, $all)));
                return !empty($langs) ? $langs : ['en_GB', 'sw'];
            }
        }

        return ['en_GB', 'sw'];
    }

    private function pbChooseLang(array $rep, string $locale, string $fallback, array $langs): string
    {
        // Try exact locale first, then normalized, then base language, then fallback, then first available.
        $candidates = $this->pbLocaleCandidates($locale);

        // If repeater has any key for that lang, accept it
        foreach ($candidates as $cand) {
            if (in_array($cand, $langs, true) && $this->pbRepeaterHasLang($rep, $cand)) {
                return $cand;
            }
        }

        if (in_array($fallback, $langs, true) && $this->pbRepeaterHasLang($rep, $fallback)) {
            return $fallback;
        }

        // pick first lang that exists in repeater
        foreach ($langs as $lang) {
            if ($this->pbRepeaterHasLang($rep, $lang)) return $lang;
        }

        return $fallback;
    }

    private function pbRepeaterHasLang(array $rep, string $lang): bool
    {
        foreach ($rep as $k => $v) {
            if (is_array($v) && str_ends_with($k, '_' . $lang)) {
                return true;
            }
        }
        return false;
    }

    private function pbLocaleCandidates(string $locale): array
    {
        $list = [];

        $list[] = $locale;
        $list[] = str_replace('-', '_', $locale);
        $list[] = str_replace('_', '-', $locale);

        if (str_contains($locale, '_')) $list[] = explode('_', $locale)[0];
        if (str_contains($locale, '-')) $list[] = explode('-', $locale)[0];

        return array_values(array_unique(array_filter($list)));
    }
}
