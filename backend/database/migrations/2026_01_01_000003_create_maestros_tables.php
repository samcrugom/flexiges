<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── FORMAS DE PAGO ────────────────────────────────────────────────────
        Schema::create('formas_pago', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 60);
            $table->string('tipo', 30)->default('contado');
            // contado, transferencia, recibo, pagare, confirming, efectivo, tarjeta
            $table->smallInteger('dias_vencimiento')->default(0);
            $table->smallInteger('num_vencimientos')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── TIPOS DE IVA ──────────────────────────────────────────────────────
        Schema::create('tipos_iva', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('descripcion', 40)->nullable();
            $table->decimal('porcentaje_iva', 5, 2);
            $table->decimal('porcentaje_rec', 5, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── ZONAS ─────────────────────────────────────────────────────────────
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 60);
            $table->timestamps();
        });

        // ── SECTORES ──────────────────────────────────────────────────────────
        Schema::create('sectores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 60);
            $table->timestamps();
        });

        // ── DELEGACIONES ──────────────────────────────────────────────────────
        Schema::create('delegaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 60);
            $table->string('direccion', 100)->nullable();
            $table->timestamps();
        });

        // ── RUTAS ─────────────────────────────────────────────────────────────
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 60);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── FAMILIAS DE ARTÍCULO (árbol: familia/subfamilia) ──────────────────
        Schema::create('familias_articulo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 60);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('familias_articulo')
                ->nullOnDelete();
            $table->smallInteger('orden')->default(0);
            $table->timestamps();
        });

        // ── ALMACENES ─────────────────────────────────────────────────────────
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 60);
            $table->string('direccion', 100)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── VENDEDORES ────────────────────────────────────────────────────────
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 80);
            $table->string('nif', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('comision_pct', 5, 2)->default(0.00);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
        Schema::dropIfExists('almacenes');
        Schema::dropIfExists('familias_articulo');
        Schema::dropIfExists('rutas');
        Schema::dropIfExists('delegaciones');
        Schema::dropIfExists('sectores');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('tipos_iva');
        Schema::dropIfExists('formas_pago');
    }
};
