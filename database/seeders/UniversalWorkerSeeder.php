<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\UniversalWorker;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;

class UniversalWorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $workers = [
            ['firstname' => 'Jane', 'lastname' => 'Kalumba', 'email' => 'jkalumba@isekit.com'],
            ['firstname' => 'Olayiwola', 'lastname' => 'Obasa', 'email' => 'oobasa@isekit.com'],
            ['firstname' => 'Damilola', 'lastname' => 'Williams', 'email' => 'dwilliams@isekit.com'],
            ['firstname' => 'David', 'lastname' => 'Agbor Okwu', 'email' => 'd.okwu@isekit.com'],
            ['firstname' => 'Loveth', 'lastname' => 'Igbo', 'email' => 'l.igbo@isekit.com'],
            ['firstname' => 'Pauline', 'lastname' => 'Dafei Yakubu', 'email' => 'p.yakubu@isekit.com'],
            ['firstname' => 'Taiwo', 'lastname' => 'Odebiyi', 'email' => 't.odebiyi@isekit.com'],
            ['firstname' => 'Anthonia', 'lastname' => 'Esomeonu', 'email' => 'a.esomeonu@isekit.com'],
            ['firstname' => 'Nonso', 'lastname' => 'Esenwa', 'email' => 'nesenwa@isekit.com'],
        ];

        foreach ($workers as $worker) {
            UniversalWorker::create([
                'worker_id' => Str::uuid(),
                'firstname' => $worker['firstname'],
                'lastname' => $worker['lastname'],
                'email' => $worker['email'],
                'country' => 'Nigeria',
                'status' => 'active',
                'registration_date' => Carbon::now()->toDateString(),
                'worker_qr' => Str::random(10),
            ]);
        }

        // Optionally, generate additional random workers using the factory
        UniversalWorker::factory()->count(10)->create();
    }
}
