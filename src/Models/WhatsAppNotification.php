<?php

namespace Drivezy\LaravelRecordManager\Models;

use Drivezy\LaravelRecordManager\Observers\WhatsAppNotificationObserver;
use Drivezy\LaravelUtility\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class WhatsAppNotification
 * @package Drivezy\LaravelRecordManager\Models
 */
class WhatsAppNotification extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'dz_whatsapp_notifications';

    /**
     * Override the boot functionality to add up the observer
     */
    public static function boot ()
    {
        parent::boot();
        self::observe(new WhatsAppNotificationObserver());
    }

    /**
     * @return BelongsTo
     */
    public function template ()
    {
        return $this->belongsTo(WhatsAppTemplate::class);
    }

    /**
     * @return BelongsTo
     */
    public function notification ()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * @return HasMany
     */
    public function recipients ()
    {
        return $this->hasMany(NotificationRecipient::class, 'source_id')->where('source_type', md5(self::class));
    }

    /**
     * @return HasMany
     */
    public function active_recipients ()
    {
        return $this->hasMany(NotificationRecipient::class, 'source_id')->where('source_type', md5(self::class))->where('active', true);
    }

    /**
     * @return BelongsTo
     */
    public function run_condition ()
    {
        return $this->belongsTo(SystemScript::class);
    }
}
