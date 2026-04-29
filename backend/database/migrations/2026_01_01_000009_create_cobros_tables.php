<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── REMESAS ───────────────────────────────────────────────────────────
        // Se crea antes que cobros para poder referenciarla
        Schema::create('remesas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_presentacion')->nullable();
            $table->string('banco', 80)->nullable();
            $table->string('cuenta', 34)->nullable();
            $table->integer('num_efectos')->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('estado', 20)->default('preparada');
            // preparada, presentada, cobrada, devuelta
            $table->text('notas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha');
        });

        // ── COBROS ────────────────────────────────────────────────────────────
        Schema::create('cobros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')
                ->comment('Referencia a ventas_documentos (factura)')
                ->constrained('ventas_documentos');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('numero', 20)->unique();
            $table->date('fecha_emision')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_vencimiento');
            $table->decimal('importe_total', 14, 2);
            $table->decimal('importe_cobrado', 14, 2)->default(0);
            // importe_pendiente se calcula como columna virtual en PostgreSQL
            // En Laravel se calcula en el modelo
            $table->string('tipo_cobro', 30)->default('recibo');
            // recibo, transferencia, efectivo, cheque, pagare, tarjeta
            $table->string('estado', 20)->default('pendiente');
            // pendiente, cobrado, parcial, impagado, anulado
            $table->string('entidad_bancaria', 80)->nullable();
            $table->string('cuenta_bancaria', 34)->nullable();
            $table->foreignId('remesa_id')
                ->nullable()->constrained('remesas')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('factura_id');
            $table->index('cliente_id');
            $table->index('fecha_vencimiento');
            $table->index('estado');
            $table->index('remesa_id');
        });

        // ── MOVIMIENTOS DE COBROS ─────────────────────────────────────────────
        Schema::create('cobros_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobro_id')
                ->constrained('cobros')->cascadeOnDelete();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->decimal('importe', 14, 2);
            $table->string('tipo', 30)->nullable();
            $table->string('concepto', 200)->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('cobro_id');
        });

        // ── ESTADÍSTICAS DE VENTAS (tablas agregadas) ─────────────────────────
        Schema::create('estadisticas_ventas_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('clientes')->cascadeOnDelete();
            $table->smallInteger('ejercicio');
            $table->smallInteger('mes');
            $table->integer('num_facturas')->default(0);
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('coste_total', 14, 2)->default(0);
            $table->decimal('margen', 14, 2)->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['cliente_id', 'ejercicio', 'mes']);
        });

        Schema::create('estadisticas_ventas_articulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')
                ->constrained('articulos')->cascadeOnDelete();
            $table->smallInteger('ejercicio');
            $table->smallInteger('mes');
            $table->decimal('cantidad', 14, 4)->default(0);
            $table->decimal('importe_neto', 14, 2)->default(0);
            $table->decimal('coste_total', 14, 2)->default(0);
            $table->decimal('margen', 14, 2)->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['articulo_id', 'ejercicio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estadisticas_ventas_articulo');
        Schema::dropIfExists('estadisticas_ventas_cliente');
        Schema::dropIfExists('cobros_movimientos');
        Schema::dropIfExists('cobros');
        Schema::dropIfExists('remesas');
    }
};
