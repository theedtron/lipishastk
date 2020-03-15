<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $table = 'transaction_logs';

    protected $fillable = ['transaction_type','transaction_ref','phone','amount','invoice','reference_no'];
}
