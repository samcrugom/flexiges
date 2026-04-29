<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── ARTÍCULOS ─────────────────────────────────────────────────────────
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 20)->unique();
            $table->string('descripcion', 120);
            $table->string('descripcion2', 120)->nullable();
            $table->foreignId('familia_id')
                ->nullable()->constrained('familias_articulo')->nullOnDelete();
            $table->foreignId('proveedor_id')
                ->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('tipo_iva_id')
                ->constrained('tipos_iva');
            $table->string('unidad_medida', 5)->default('UD');
            // EAN
            $table->string('ean13', 13)->nullable()->unique();
            $table->string('ean14', 14)->nullable();
            // Dimensiones
            $table->decimal('peso', 10, 4)->nullable();
            $table->decimal('volumen', 10, 4)->nullable();
            // Precios — 4 tarifas + coste
            $table->decimal('precio_coste', 12, 4)->default(0);
            $table->decimal('ult_precio_coste', 12, 4)->default(0);
            $table->decimal('tarifa1', 12, 4)->default(0);
            $table->decimal('tarifa2', 12, 4)->default(0);
            $table->decimal('tarifa3', 12, 4)->default(0);
            $table->decimal('tarifa4', 12, 4)->default(0);
            // Stock (calculado via trigger desde stock_almacen)
            $table->decimal('stock_total', 14, 4)->default(0);
            $table->decimal('stock_minimo', 14, 4)->default(0);
            $table->decimal('stock_maximo', 14, 4)->nullable();
            $table->decimal('stock_reposicion', 14, 4)->nullable();
            // Fabricación
            $table->boolean('es_fabricado')->default(false);
            // Compras
            $table->string('ref_proveedor', 30)->nullable();
            // Estado
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->string('imagen_path', 255)->nullable();
            $table->timestamps();

            $table->index('descripcion');
            $table->index('familia_id');
            $table->index('proveedor_id');
            $table->index('activo');
        });

        // ── STOCK POR ALMACÉN ─────────────────────────────────────────────────
        Schema::create('stock_almacen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')
                ->constrained('articulos')->cascadeOnDelete();
            $table->foreignId('almacen_id')
                ->constrained('almacenes')->cascadeOnDelete();
            $table->decimal('stock', 14, 4)->default(0);
            $table->decimal('stock_reservado', 14, 4)->default(0);
            $table->decimal('stock_pedido', 14, 4)->default(0);
            // Stock físico disponible = stock - stock_reservado
            $table->string('ubicacion', 30)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['articulo_id', 'almacen_id']);
            $table->index('articulo_id');
            $table->index('almacen_id');
        });

        // ── TARIFAS ESPECIALES POR CLIENTE ────────────────────────────────────
        Schema::create('articulos_tarifas_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')
                ->constrained('articulos')->cascadeOnDelete();
            $table->foreignId('cliente_id')
                ->constrained('clientes')->cascadeOnDelete();
            $table->decimal('precio', 12, 4);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->timestamps();

            $table->unique(['articulo_id', 'cliente_id']);
        });

        // ── MOVIMIENTOS DE ALMACÉN ────────────────────────────────────────────
        Schema::create('movimientos_almacen', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->string('tipo_movimiento', 25);
            // ENTRADA, SALIDA, AJUSTE_POS, AJUSTE_NEG,
            // FABRICACION_IN, FABRICACION_OUT,
            // DEVOLUCION_CLI, DEVOLUCION_PROV,
            // TRASPASO_IN, TRASPASO_OUT
            $table->decimal('cantidad', 14, 4);
            // + entrada, - salida
            $table->decimal('stock_anterior', 14, 4)->default(0);
            $table->decimal('stock_posterior', 14, 4)->default(0);
            $table->decimal('precio_coste', 12, 4)->default(0);
            $table->string('documento_tipo', 25)->nullable();
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->string('documento_ref', 25)->nullable();
            $table->string('lote', 30)->nullable();
            $table->string('observaciones', 200)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('articulo_id');
            $table->index('almacen_id');
            $table->index('fecha');
            $table->index('tipo_movimiento');
            $table->index(['documento_tipo', 'documento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_almacen');
        Schema::dropIfExists('articulos_tarifas_cliente');
        Schema::dropIfExists('stock_almacen');
        Schema::dropIfExists('articulos');
    }
};
