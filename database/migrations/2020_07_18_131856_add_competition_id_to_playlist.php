<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompetitionIdToPlaylist extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('playlists', function (Blueprint $table) {
			$table->bigInteger('competition_id')->after('is_competition')->unsigned()->nullable();
			$table->foreign('competition_id')->references('id')->on('competitions')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('playlists', function (Blueprint $table) {
			$table->dropColumn('competition_id');
		});
	}
}
