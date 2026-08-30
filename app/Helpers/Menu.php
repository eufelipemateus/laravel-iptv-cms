<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Menu
{
    private $menusitens = [];

    public function add($menu)
    {
        if (is_array($menu) && isset($menu[0]) && is_array($menu[0])) {
            // Array de menus
            foreach ($menu as $item) {
                array_push($this->menusitens, $item);
            }
        } else {
            array_push($this->menusitens, $menu);
        }
    }

    public function view()
    {
        $menusList = array_values(array_filter($this->menusitens, function (array $item): bool {
            if (! isset($item['enabled_when'])) {
                return true;
            }

            $conditions = is_array($item['enabled_when']) ? $item['enabled_when'] : [$item['enabled_when']];

            return collect($conditions)->every(function ($condition): bool {
                $condition = (string) $condition;
                if (Str::startsWith($condition, 'auth.user.')) {
                    $attribute = Str::after($condition, 'auth.user.');

                    return (bool) data_get(auth()->user(), $attribute, false);
                }

                return (bool) config($condition, true);
            });
        }));

        return view('menu', ['menusList' => $menusList]);
    }
}
