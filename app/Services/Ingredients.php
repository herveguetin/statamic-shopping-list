<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace App\Services;

use Illuminate\Support\Str;

class Ingredients
{
    const INGREDIENTS_FILE_STORAGE_PATH = '/app/ingredients/alim_2020_07_07.xml';

    const GROUPS_FILE_STORAGE_PATH = '/app/ingredients/alim_grp_2020_07_07.xml';

    public static function get(string $id): array
    {
        $ingredients = new static();

        return $ingredients->getById($id);
    }

    public function getById(string $id): array
    {
        foreach (static::loadIngredientsFile() as $ingredient) {
            if (strtolower($id) === trim(strtolower($ingredient['alim_code']))) {
                return $this->makeIngredient($ingredient);
            }
        }

        return [];
    }

    public static function loadIngredientsFile(): array
    {
        return static::loadFile(self::INGREDIENTS_FILE_STORAGE_PATH)['ALIM'];
    }

    public static function loadGroupsFile(): array
    {
        return static::loadFile(self::GROUPS_FILE_STORAGE_PATH)['ALIM_GRP'];
    }

    private static function loadFile(string $path): array
    {
        $xmlString = file_get_contents(storage_path().$path);
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);

        return json_decode($json, true);
    }

    private function makeIngredient(mixed $ingredient): array
    {
        return [
            'code' => trim($ingredient['alim_code']),
            'label' => trim($ingredient['alim_nom_fr']),
        ];
    }

    public static function search(string $q): array
    {
        $ingredients = new static();

        return $ingredients->doSearch($q);
    }

    public function doSearch(string $q): array
    {
        $response = [];
        foreach ($this->getSearchTerms($q) as $term) {
            $response = array_merge($response, $this->searchTerm($term));
        }

        return array_slice($this->score($response, $this->getSearchTerms($q)), 0, 15);
    }

    private function getSearchTerms($q): array
    {
        return array_filter(explode(' ', $q), fn (string $term) => strlen($term) > 3);
    }

    private function searchTerm(string $term): array
    {
        $matches = array_filter(static::loadIngredientsFile(), function (array $item) use ($term) {
            return Str::contains($item['alim_nom_fr'], $term, true);
        });

        return array_values(array_map(fn ($match) => $this->makeIngredient($match), $matches));
    }

    private function score(array $response, array $searchTerms): array
    {
        $response = array_map(function (array $item) use ($searchTerms) {
            $score = 0;
            foreach ($searchTerms as $term) {
                if (Str::contains($item['label'], $term, true)) {
                    $score = $score + 1;
                }
            }
            $item['score'] = $score;

            return $item;
        }, $response);

        usort($response, fn ($a, $b) => $a['score'] <= $b['score']);

        return $response;
    }
}
