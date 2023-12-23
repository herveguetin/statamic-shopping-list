<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace App\Services;

class Ingredients
{
    const INGREDIENTS_FILE_STORAGE_PATH = '/app/ingredients/alim_2020_07_07.xml';

    public static function search(string $q): array
    {
        $matches = array_filter(self::loadIngredientsFile(), function (array $item) use ($q) {
            return str_contains(trim(strtolower($item['alim_nom_fr'])), strtolower($q));
        });
        return array_values(array_map(function ($match) {
            return [
                'id' => trim($match['alim_code']),
                'name' => trim($match['alim_nom_fr'])
            ];
        }, $matches));
    }

    private static function loadIngredientsFile(): array
    {
        $xmlString = file_get_contents(storage_path() . self::INGREDIENTS_FILE_STORAGE_PATH);
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);
        return json_decode($json, true)['ALIM'];
    }
}
