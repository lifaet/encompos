<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the 'profit' column if it doesn't exist
        if (!Schema::hasColumn('orders', 'profit')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->double('profit')->default(0)->after('total')->comment('Total profit of the order');
            });
        }

        // Populate existing orders with profit
        $orders = Order::all();
        foreach ($orders as $order) {
            $order->profit = $order->total - $order->sub_total; // adjust logic if needed
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('profit');
        });
    }
};
