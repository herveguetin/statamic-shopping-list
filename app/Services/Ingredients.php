<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace App\Services;

use Illuminate\Support\Str;

class Ingredients
{
    const INGREDIENTS_FILE_STORAGE_PATH = '/app/ingredients/alim_2020_07_07.xml';

    public static function get(string $id): array
    {
        $ingredients = new static();

        return $ingredients->getById($id);
    }

    public function getById(string $id): array
    {
        foreach ($this->loadIngredientsFile() as $ingredient) {
            if (strtolower($id) === trim(strtolower($ingredient['alim_code']))) {
                return $this->makeIngredient($ingredient);
            }
        }

        return [];
    }

    private function loadIngredientsFile(): array
    {
        $xmlString = file_get_contents(storage_path().self::INGREDIENTS_FILE_STORAGE_PATH);
        $xmlObject = simplexml_load_string($xmlString);
        $json = json_encode($xmlObject);

        return json_decode($json, true)['ALIM'];
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
        return array_filter(explode(' ', $q), function (string $term) {
            return strlen($term) > 3;
        });
    }

    private function searchTerm(string $term): array
    {
        $matches = array_filter($this->loadIngredientsFile(), function (array $item) use ($term) {
            return Str::contains($item['alim_nom_fr'], $term, true);
        });

        return array_values(array_map(function ($match) {
            return $this->makeIngredient($match);
        }, $matches));
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

        usort($response, function ($a, $b) {
            return $a['score'] <= $b['score'];
        });

        return $response;
    }
}
