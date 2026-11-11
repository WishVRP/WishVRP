<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buchungskalender (ein Kurs pro Tag), Stripe-Zahlung, Gutscheine, Shop-Produkte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_blocks', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('reason', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 160)->unique();
            $table->string('category', 20)->index();          // damast | kueche | jagd | einzel | ringe
            $table->string('status', 12)->default('entwurf'); // entwurf | aktiv | verkauft
            $table->string('short', 255)->nullable();
            $table->text('description')->nullable();           // Markdown
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('stock')->default(1);
            $table->json('images')->nullable();                // Pfade auf der public-Disk
            $table->json('specs')->nullable();                 // [{label, value}] – nur echte Angaben
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('persons')->default(1);
            $table->decimal('value', 10, 2);                   // Wert in € (Kurspreis × Personen)
            $table->string('status', 12)->default('offen');    // offen | bezahlt | eingeloest | storniert
            $table->string('recipient', 120)->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();           // Kauf
            $table->foreignId('redeemed_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->date('valid_until');
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->date('course_start')->nullable()->after('course_date_id')->index();
            $table->date('course_end')->nullable()->after('course_start');
            $table->foreignId('product_id')->nullable()->after('course_end')->constrained()->nullOnDelete();
            $table->string('shipping_method', 12)->nullable()->after('country');   // versand | abholung
            $table->decimal('shipping_amount', 10, 2)->nullable()->after('shipping_method');
            $table->foreignId('voucher_id')->nullable()->after('discount_percent')->constrained()->nullOnDelete();
            $table->decimal('voucher_amount', 10, 2)->nullable()->after('voucher_id');
            $table->string('payment_method', 12)->nullable()->after('voucher_amount');  // stripe | ueberweisung | gutschein | vorort
            $table->string('stripe_session_id', 120)->nullable()->after('payment_method')->index();
            $table->timestamp('hold_until')->nullable()->after('stripe_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['voucher_id', 'product_id'] as $fk) {
                $table->dropConstrainedForeignId($fk);
            }
            $table->dropColumn(['course_start', 'course_end', 'shipping_method', 'shipping_amount', 'voucher_amount', 'payment_method', 'stripe_session_id', 'hold_until']);
        });
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('calendar_blocks');
    }
};
