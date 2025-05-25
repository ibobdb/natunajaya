<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First make sure orders.invoice_id has an index
        Schema::table('orders', function (Blueprint $table) {
            // Only add the index if it doesn't already exist
            if (!Schema::hasIndex('orders', 'orders_invoice_id_index')) {
                $table->index('invoice_id', 'orders_invoice_id_index');
            }
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->string('invoice_id')->nullable()->after('id');
            $table->foreign('invoice_id')->references('invoice_id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
