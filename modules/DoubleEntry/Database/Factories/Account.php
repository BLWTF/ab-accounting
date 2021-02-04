<?php

namespace Modules\DoubleEntry\Database\Factories;

use App\Abstracts\Factory;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Models\Account as Model;

class Account extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company_id' => $this->company->id,
            'code' => 999,
            'name' => $this->faker->text(15),
            'description' => $this->faker->text(15),
        ];
    }

    /**
     * Indicate that the model is enabled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function enabled()
    {
        return $this->state([
            'enabled' => 1,
        ]);
    }

    /**
     * Indicate that the model is disabled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function disabled()
    {
        return $this->state([
            'enabled' => 0,
        ]);
    }

    /**
     * Indicate that the type of model is bank&cash.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function bank_cash()
    {
        return $this->state([
            'type_id' => 6,
        ]);
    }

    /**
     * Indicate that the type of model is tax.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function tax()
    {
        return $this->state([
            'type_id' => 17,
        ]);
    }

    /**
     * Indicate that the type of model is fixed asset.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function fixed_asset()
    {
        return $this->state([
            'type_id' => 2,
        ]);
    }

    /**
     * Indicate that the type of model is current asset.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function current_asset()
    {
        return $this->state([
            'type_id' => 1,
        ]);
    }
    
    /**
     * Indicate that the type of model is random.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function random()
    {
        return $this->state([
            'type_id' => Type::find($this->faker->numberBetween(1, 17))->id,
        ]);
    }
}
