<?php

namespace Modules\DoubleEntry\Traits;

trait Accounts
{
    public function countRelationships($model, $relationships)
    {
        $counter = [];

        foreach ($relationships as $relationship => $text) {
            if (!$c = $model->$relationship()->count()) {
                continue;
            }

            $counter[] = $c . ' ' . strtolower(trans_choice('double-entry::general.' . $text, ($c > 1) ? 2 : 1));
        }

        return $counter;
    }
    
    public function countSettings($account)
    {
        $counter = [];
        $settings = [
            'double-entry.accounts_receivable',
            'double-entry.accounts_payable',
            'double-entry.accounts_sales',
            'double-entry.accounts_expenses',
            'double-entry.accounts_sales_discount',
            'double-entry.accounts_purchase_discount',
            'double-entry.accounts_owners_contribution',
        ];

        foreach ($settings as $setting) {
            if ($account->code != setting($setting)) {
                continue;
            }

            $counter[] = strtolower(trans_choice('general.settings', 2));
        }

        return $counter;
    }

    public function updateSettings($old_code, $new_code)
    {
        $settings = [
            'double-entry.accounts_receivable',
            'double-entry.accounts_payable',
            'double-entry.accounts_sales',
            'double-entry.accounts_expenses',
            'double-entry.accounts_sales_discount',
            'double-entry.accounts_purchase_discount',
            'double-entry.accounts_owners_contribution',
        ];

        foreach ($settings as $setting) {
            if ($old_code == setting($setting)) {
                setting()->set($setting, $new_code);
            }
        }
        
        setting()->save();
    }
}
