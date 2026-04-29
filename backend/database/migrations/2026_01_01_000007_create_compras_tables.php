<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── PEDIDOS DE COMPRA ─────────────────────────────────────────────────
        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_prevista')->nullable();
            $table->foreignId('forma_pago_id')
                ->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->string('estado', 20)->default('borrador');
            // borrador, confirmado, parcial, recibido, anulado
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('referencia_proveedor', 30)->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('estado');
            $table->index('fecha');
        });

        Schema::create('pedidos_compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                ->constrained('pedidos_compra')->cascadeOnDelete();
            $table->smallInteger('linea');
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->string('descripcion', 120)->nullable();
            $table->decimal('cantidad', 12, 4);
            $table->decimal('cantidad_recibida', 12, 4)->default(0);
            $table->decimal('precio', 12, 4)->default(0);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->decimal('importe_neto', 14, 2)->default(0);
            $table->foreignId('tipo_iva_id')
                ->nullable()->constrained('tipos_iva')->nullOnDelete();
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->timestamps();

            $table->index('pedido_id');
        });

        // ── ALBARANES DE COMPRA ───────────────────────────────────────────────
        Schema::create('albaranes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('pedido_id')
                ->nullable()->constrained('pedidos_compra')->nullOnDelete();
            $table->unsignedBigInteger('factura_compra_id')->nullable();
            // FK se añade después de crear facturas_compra
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->string('ref_proveedor', 30)->nullable();
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->string('estado', 20)->default('pendiente');
            // pendiente, facturado
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('estado');
        });

        Schema::create('albaranes_compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('albaran_id')
                ->constrained('albaranes_compra')->cascadeOnDelete();
            $table->smallInteger('linea');
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->string('descripcion', 120)->nullable();
            $table->decimal('cantidad', 12, 4);
            $table->decimal('precio', 12, 4)->default(0);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->decimal('importe_neto', 14, 2)->default(0);
            $table->foreignId('tipo_iva_id')
                ->nullable()->constrained('tipos_iva')->nullOnDelete();
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('movimiento_id')
                ->nullable()->constrained('movimientos_almacen')->nullOnDelete();
            $table->timestamps();

            $table->index('albaran_id');
        });

        // ── FACTURAS DE COMPRA ────────────────────────────────────────────────
        Schema::create('facturas_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_contable')->nullable();
            $table->string('ref_proveedor', 30)->nullable();
            $table->foreignId('forma_pago_id')
                ->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->string('estado', 20)->default('pendiente');
            // pendiente, pagada, vencida, anulada
            $table->decimal('base_imponible1', 14, 2)->default(0);
            $table->decimal('tipo_iva1', 5, 2)->default(21);
            $table->decimal('cuota_iva1', 14, 2)->default(0);
            $table->decimal('base_imponible2', 14, 2)->default(0);
            $table->decimal('tipo_iva2', 5, 2)->default(0);
            $table->decimal('cuota_iva2', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('proveedor_id');
            $table->index('estado');
        });

        // FK albaranes_compra → facturas_compra (ahora que existe la tabla)
        Schema::table('albaranes_compra', function (Blueprint $table) {
            $table->foreign('factura_compra_id')
                ->references('id')
                ->on('facturas_compra')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('albaranes_compra', function (Blueprint $table) {
            $table->dropForeign(['factura_compra_id']);
        });
        Schema::dropIfExists('facturas_compra');
        Schema::dropIfExists('albaranes_compra_lineas');
        Schema::dropIfExists('albaranes_compra');
        Schema::dropIfExists('pedidos_compra_lineas');
        Schema::dropIfExists('pedidos_compra');
    }
};
