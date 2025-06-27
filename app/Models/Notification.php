<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel',
        'type',
        'content',
        'status',
        'recipient',
        'send_date',
        'recipient'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'send_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Insert a new notification with default status of 3.
     *
     * @param array $attributes
     * @return \App\Models\Notification
     */
    public static function insertNotification(array $attributes)
    {
        $attributes['status'] = $attributes['status'] ?? '3';

        return self::create($attributes);
    }
}
