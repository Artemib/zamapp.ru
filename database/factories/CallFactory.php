<?php

namespace Database\Factories;

use App\Models\Call;
use App\Enums\CallConstants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CallFactory extends Factory
{
    protected $model = Call::class;

    public function definition(): array
    {
        return [
            'callid'          => Str::uuid()->toString(),
            'datetime'        => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type'            => $this->faker->randomElement(CallConstants::typeKeys()),
            'status'          => $this->faker->randomElement(CallConstants::statusKeys()),
            'client_phone'    => $this->faker->phoneNumber(),
            'user_pbx'        => $this->faker->userName(),
            'diversion_phone' => $this->faker->phoneNumber(),
            'duration'        => $this->faker->numberBetween(10, 3600),
            'wait'            => $this->faker->numberBetween(0, 300),
            'link_record_pbx' => $this->faker->optional()->url(),
            'link_record_crm' => $this->faker->optional()->url(),
            'transcribation'  => $this->faker->optional()->paragraph(),
            'from_source_name'=> $this->faker->company(),
        ];
    }
}