<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushNotificationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    /*@Table Fields:          push-notification-tokens
    "device-id":        
    "device-platform":  [ios-7 - android-7 - android-8 ]
    "fcm-token":
    "ip":               
    "agent":            $_SERVER['HTTP_USER_AGENT']
    "user-id":          [nullable]
    "status":           [boolean]*/
        Schema::create('push-notification-tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fcm-token', 500)->default('');
            $table->string('device-id', 500)->default('');
            $table->string('device-platform', 20)->default('');
            $table->char('ip', 15)->default('');
            $table->string('agent', 500)->default('');
            $table->boolean('status')->default(0);
            $table->integer('user-id')->nullable();
            $table->timestamp('created-at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push-notification-tokens');
    }
}
