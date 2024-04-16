<?php

namespace Tests\Folder;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewTest extends TestCase
{
    /**
     * A test folder view list.
     *
     * @return void
     */
    public function test_FolderViewList()
    {
        $user = User::find(env("TEST_USER", 1));

        $folders =   $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("api/folders");

        $folders->assertStatus(200);
        $folders->assertJson([
            "status" => 200,
            "success" => true,
            "data" => [],
            "pagination" => []
        ]);
    }


    public function test_FolderViewListMany()
    {
        $user = User::find(env("TEST_USER", 1));

        $folders =   $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("api/folders?items=100");

        $folders->assertStatus(200);
        $folders->assertJson([
            "status" => 200,
            "success" => true,
            "data" => [],
            "pagination" => []
        ]);
    }

    public function test_FolderViewShow()
    {
        $user = User::find(env("TEST_USER", 1));

        $folders =   $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("api/folders");

        $folders->assertStatus(200);
        $folders->assertJson([
            "status" => 200,
            "success" => true,
            "data" => [],
            "pagination" => []
        ]);

        $data = $folders->json("data");

        if (isset($data['id'])) {
            $folder =   $this->actingAs($user)->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get("api/folders/" . $data['id']);

            $folder->assertStatus(200);
            $folder->assertJson([
                "status" => 200,
                "success" => true,
                "data" =>            [
                    "id" => 444,
                    "project_list_id" => null,
                    "folder_id" => null,
                    "folder_name" => "1707732007",
                    "created_at" => "2024-02-12T10:00:07.000000Z",
                    "updated_at" => "2024-02-12T10:00:07.000000Z",
                    "permission" => [],
                    "files" => [],
                    "folders" => []
                ]
            ]);
        }
    }

    public function test_FolderViewFiles()
    {
        $user = User::find(env("TEST_USER", 1));

        $files =   $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("api/files");

        $files->assertStatus(200);

        $files->assertJson([
            "status" => 200,
            "success" => true,
            "data" => [],
            "pagination" => []
        ]);

        $data = $files->json("data");

        if (isset($data['id'])) {
            $folder =   $this->actingAs($user)->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get("api/files/" . $data['id']);

            $folder->assertStatus(200);
            $folder->assertJson([
                "status" => 200,
                "success" => true,
                "data" =>            [
                    "id" => 444,
                    "folder_id" => null,
                    "name" => "1707732007",
                    "updated_at" => "2024-02-12T10:00:07.000000Z",
                ]
            ]);
        }
    }

    public function test_FolderViewFilesShow()
    {
        $user = User::find(env("TEST_USER", 1));

        $files =   $this->actingAs($user)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("api/files");

        $files->assertStatus(200);

        $files->assertJson([
            "status" => 200,
            "success" => true,
            "data" => [],
            "pagination" => []
        ]);
    }
}
