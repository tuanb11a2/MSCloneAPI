<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Manager;
use App\Models\User;
use App\Models\Group;
use App\Models\Exercise;
use Faker\Generator;
use App\Models\Friend;
use App\Models\Message;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Container\Container;

class DatabaseSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;
    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::query()->create([
            'name' => 'Tuan',
            'email' => 'anhtuanle.ka@gmail.com',
            'username' => 'letuan1601',
            'phone_number' => '0375910493',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'avatar' => 'https://natashaskitchen.com/wp-content/uploads/2020/05/Vanilla-Cupcakes-3.jpg',
            'remember_token' => Str::random(10),
        ]);
        Manager::query()->create([
            'name' => 'Tuan', 
            'email' => 'anhtuanle.ka@gmail.com',
            'username' => 'letuan1601',
            'phone_number' => '0375910493',
            'password' => bcrypt('12345678'),
        ]);

        User::factory(20)->create();
        $users = User::all();

        for ($i = 0; $i < 20; $i++) {
            Group::query()->create([
                'name' => $this->faker->name(),
                'description' => $this->faker->text(200),
                'privacy' => ['public', 'private'][rand(0, 1)],
                'slug' => Str::uuid()->toString(),
                'avatar' => $this->faker->imageUrl(),
                'creator_id' => $users->random()->id,
                'join_code' => Str::random(6),
            ]);
        }

        $friendStatus = ['accepted', 'pending'];
        foreach ($users as $user) {
            for ($i = 0; $i < 20; $i++) {
                $friendId = $users->random()->id;
                $content = $this->faker->sentence;
                $user->sentMessages()->create([
                    'content' => $content,
                    'receiver_id' => $friendId,
                ]);
                Message::query()->create([
                    'content' => $content,
                    'sender_id' => $user->id,
                    'receiver_id' => $friendId,
                ]);

                $tempStatus = $friendStatus[rand(0, 1)];
                $user->userFriends()->create([
                    'friend_id' => $friendId,
                    'status' => $tempStatus
                ]);

                Friend::query()->create([
                    'user_id' => $friendId,
                    'friend_id' => $user->id,
                    'status' => $tempStatus
                ]);
            }
        }

        $groups = Group::all();
        foreach ($groups as $group) {
            $group->channels()->create([
                'name' => 'Chung',
                'slug' => 'general',
            ]);

            $group->users()->attach($users->pluck('id'));

            $types = ['backlog', 'progress', 'review', 'finished'];
            $priority = ['high', 'low', 'medium'];
            $group->todos()->create([
                'name' => $this->faker->text(255),
                'user_id' => $users->random()->id,
                'type' => $types[rand(0, 3)],
                'priority' => $priority[rand(0, 2)],
                'deadline' => Carbon::tomorrow()
            ]);
        }

        $channels = Channel::all();
        foreach ($channels as $channel) {
            for ($i = 0; $i < 20; $i++) {

                $channel->posts()->create([
                    'content' => $this->faker->paragraph(),
                    'user_id' => $users->random()->id,
                ]);
                $channel->exercises()->create([
                    'description' => $this->faker->text(),
                    'title' => $this->faker->text(200),
                    'file_path' => $this->faker->imageUrl(),
                    'deadline' => Carbon::tomorrow()
                ]);
            }
        }

        $posts = Post::all();
        foreach ($posts as $post) {
            for ($i = 0; $i < 10; $i++) {
                $post->comments()->create([
                    'content' => $this->faker->paragraph(),
                    'user_id' => $users->random()->id,
                ]);
            }
        }

        $exercises = Exercise::all();
        foreach ($exercises as $exercise) {
            $exercise->comments()->create([
                'content' => $this->faker->paragraph(),
                'user_id' => 1,
            ]);
            $exercise->submissions()->create([
                'content' => $this->faker->paragraph(),
                'file_path' => $this->faker->imageUrl(),
                'user_id' => 1,
                'status' => 'assigned',
            ]);

            for ($i = 0; $i < 2; $i++) {
                $exercise->comments()->create([
                    'content' => $this->faker->paragraph(),
                    'user_id' => $users->random()->id,
                ]);
                $exercise->users()->attach($users->pluck('id'));
                $exercise->submissions()->create([
                    'content' => $this->faker->paragraph(),
                    'file_path' => $this->faker->imageUrl(),
                    'user_id' => $users->random()->id,
                    'status' => 'assigned',
                ]);
            }
        }
    }
}
