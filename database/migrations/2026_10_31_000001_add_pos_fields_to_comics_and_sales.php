<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('other_fee', 12, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
        });

        DB::table('sales')->orderBy('id')->chunkById(100, function ($sales) {
            foreach ($sales as $sale) {
                $subtotal = (float) DB::table('sale_items')
                    ->where('sale_id', $sale->id)
                    ->sum('subtotal');

                DB::table('sales')->where('id', $sale->id)->update([
                    'subtotal' => $subtotal > 0 ? $subtotal : $sale->total,
                ]);
            }
        });

        Schema::create('held_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->json('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('held_transactions');

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_percent',
                'discount_amount',
                'tax',
                'other_fee',
                'payment_method',
                'customer_name',
                'customer_contact',
            ]);
        });

        Schema::table('comics', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
            $table->dropColumn('barcode');
        });
    }
};
