<?php

namespace Modules\DoubleEntry\Database\Factories;

use App\Abstracts\Factory;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Journal as Model;

class Journal extends Factory
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
            'paid_at' => $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d H:i:s'),
            'reference' => $this->faker->text(15),
            'description' => $this->faker->text(15),
            'amount' => 0,
        ];
    }

    /**
     * Indicate that the model has income items.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function items_income()
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1, 1000);
            $doc_date = $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d H:i:s');

            $items = [
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountBank::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('400')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount,
                ],
            ];

            return [
                'items' => $items,
            ];
        });
    }

    /**
     * Indicate that the model has multiple income items.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function items_income_multiple()
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1, 1000);
            $amount_tax = $this->faker->randomFloat(2, 1, 1000);
            $amount_extra = $this->faker->randomFloat(2, 1, 1000);
            $doc_date = $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d H:i:s');

            $items = [
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountBank::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount + $amount_tax + $amount_extra,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('400')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountTax::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount_tax,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('460')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount_extra,
                ],
            ];

            return [
                'items' => $items,
            ];
        });
    }

    /**
     * Indicate that the model has expense items.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function items_expense()
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1, 1000);
            $doc_date = $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d H:i:s');

            $items = [
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('628')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountBank::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount,
                ],
            ];

            return [
                'items' => $items,
            ];
        });
    }

    /**
     * Indicate that the model has multiple expense items.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function items_expense_multiple()
    {
        return $this->state(function (array $attributes) {
            $amount = $this->faker->randomFloat(2, 1, 1000);
            $amount_tax = $this->faker->randomFloat(2, 1, 1000);
            $amount_extra = $this->faker->randomFloat(2, 1, 1000);
            $doc_date = $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d H:i:s');

            $items = [
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('500')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountTax::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount_tax,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => Account::code('644')->first()->id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'debit' => $amount_extra,
                ],
                [
                    'company_id' => $this->company->id,
                    'account_id' => AccountBank::companyId($this->company->id)->first()->account_id,
                    'issued_at' => $doc_date,
                    'entry_type' => 'item',
                    'credit' => $amount + $amount_tax + $amount_extra,
                ],
            ];

            return [
                'items' => $items,
            ];
        });
    }
}
