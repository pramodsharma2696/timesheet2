<?php

namespace App\Traits\Helpers;

use Spatie\Permission\Models\Permission;

trait CrudPermissionTrait
{
    public function back_allow($entity, $action)
    {

        if (backpack_user()->hasPermissionTo("{$this->crud->entity_name}.$action")) {
            return  $this->crud->allowAccess($action);
        }
        $this->crud->denyAccess($action);
    }


    public function transformArray(array $inputArray)
    {
        $output = [];
        $all_permissions =   Permission::where("usage", "front_end")->get()->pluck("name")->toArray();

        foreach ($all_permissions as $string) {
            // Split the string by dots
            $parts = explode('.', $string);

            if (count($parts) == 4) {

                $state = in_array($string, $inputArray);

                // Extract page and process names
                $page = ($parts[1]);
                $process = $parts[3];

                // Create page entry if it doesn't exist
                if (!isset($output[$page])) {
                    $output[$page] = [
                        'page' => $page,
                        'pageView' => false,
                        'processes' => []
                    ];
                }

                // Mark pageView as true
                $output[$page]['pageView'] = $output[$page]['pageView'] == true ? true : $state;

                // Add process to the processes list
                $output[$page]['processes'][$process] = $state;
            }
        }
        // Convert the associative array to a sequential array
        $output = array_values($output);

        return $output;
    }

    public function reverseTransformArray(array $inputArray)
{
    $output = [];

    foreach ($inputArray as $pageData) {
        $page = $pageData['page'];
        $pageView = $pageData['pageView'];

        foreach ($pageData['processes'] as $process => $state) {
            // If the process state is true, add it to the output array
            if ($state) {
                $output[] = "front_end.$page.any.$process";
            }
        }

        // If pageView is true, add the page entry to the output array
        if ($pageView) {
            $output[] = "front_end.$page.any.view";
        }
    }

    return $output;
}
}
