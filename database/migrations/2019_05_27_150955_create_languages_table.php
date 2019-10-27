<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * CreateLanguagesTable Class create languages table
 * @package Dev\Domain\Hydrator\LanguageHydrator
 * @author Mohamad El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('id');
                $table->char('code', 2)->unique();
                $table->char('locale' , 5)->default('');
                $table->string('name', 50);
                $table->string('native-name', 50);
                $table->boolean('status')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
