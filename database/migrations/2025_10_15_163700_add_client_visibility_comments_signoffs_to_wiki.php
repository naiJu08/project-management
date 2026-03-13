<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wiki_pages', function (Blueprint $table) {
            $table->boolean('client_visible')->default(false)->after('content');
            $table->timestamp('client_visible_at')->nullable()->after('client_visible');
        });

        Schema::create('wiki_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_page_id')->constrained('wiki_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_comment_id')->nullable()->constrained('wiki_comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wiki_signoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_page_id')->constrained('wiki_pages')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('signed_off_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('signed_off_at');
            $table->unsignedInteger('version_signed');
            $table->text('remarks')->nullable();
            $table->boolean('is_outdated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wiki_signoffs');
        Schema::dropIfExists('wiki_comments');

        Schema::table('wiki_pages', function (Blueprint $table) {
            $table->dropColumn(['client_visible', 'client_visible_at']);
        });
    }
};
