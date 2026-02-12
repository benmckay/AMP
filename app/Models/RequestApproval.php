<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestApproval extends Model
{
    protected $fillable = ['request_id', 'approver_id', 'status', 'comments'];

    public function request()
    {
        return $this->belongsTo(AccessRequest::class, 'request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
