<?php
/**
 * @author Hervé Guétin <www.linkedin.com/in/herveguetin>
 */

namespace App\Services;

use Illuminate\Support\Collection;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Facades\Entry;

class ShoppingList
{
    private Collection $allIngredients;

    private Collection $shoppingList;

    public function __construct()
    {
        $this->allIngredients = collect();
        $this->shoppingList = collect();
    }

    public static function make(): array
    {
        $shoppingList = new static();

        return $shoppingList->buildShoppingList();
    }

    private function buildShoppingList(): array
    {
        $this->gatherIngredients();
        $this->enrichIngredients();
        $this->shoppingListByGroup();

        return $this->shoppingList->toArray();
    }

    private function gatherIngredients(): void
    {
        $meals = Entry::whereCollection('meals');
        $meals->each(function (StatamicEntry $meal) {
            $meal->dishes->each(function (StatamicEntry $dish) use ($meal) {
                foreach ($dish->ingredients as $ingredient) {
                    $ingredientKey = $this->allIngredients->search(fn (array $item) => $item['name'] == $ingredient->name);
                    $quantity = $meal->number_of_guests * $ingredient->quantity;
                    if ($ingredientKey !== false) {
                        $existingIngredient = $this->allIngredients->get($ingredientKey);
                        $existingIngredient['quantity'] += $quantity;
                        $this->allIngredients->put($ingredientKey, $existingIngredient);
                    } else {
                        $this->allIngredients->add([
                            'name' => $ingredient->name,
                            'quantity' => $quantity,
                        ]);
                    }
                }
            });
        });
    }

    private function enrichIngredients(): void
    {
        $ingredientsDB = collect(Ingredients::loadIngredientsFile());
        $groupsDB = collect(Ingredients::loadGroupsFile());
        $this->allIngredients = $this->allIngredients->map(function (array $ingredient) use ($groupsDB, $ingredientsDB) {
            $ingredientData = $ingredientsDB->first(fn (array $item) => trim($item['alim_code']) === $ingredient['name']);
            $group = $groupsDB->first(fn (array $group) => trim($group['alim_ssgrp_code']) === trim($ingredientData['alim_ssgrp_code']));
            $ingredient['code'] = trim($ingredientData['alim_code']);
            $ingredient['name'] = trim($ingredientData['alim_nom_fr']);
            $ingredient['group_name'] = ucfirst(trim($group['alim_ssgrp_nom_fr']));
            $ingredient['group_code'] = trim($group['alim_grp_code']);

            return $ingredient;
        });
    }

    private function shoppingListByGroup(): void
    {
        $this->allIngredients->each(function (array $ingredient) {
            $existingGroupKey = $this->shoppingList->search(fn ($group) => $group['name'] === $ingredient['group_name']);
            if ($existingGroupKey !== false) {
                $existingGroup = $this->shoppingList->get($existingGroupKey);
                $existingGroup['ingredients']->add($ingredient);
                $this->shoppingList->put($existingGroupKey, $existingGroup);
            } else {
                $this->shoppingList->add([
                    'name' => $ingredient['group_name'],
                    'ingredients' => collect()->add($ingredient),
                ]);
            }
        });
    }
}
