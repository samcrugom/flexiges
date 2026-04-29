<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── FÓRMULAS (BOM — Bill of Materials) ────────────────────────────────
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 15)->unique();
            $table->string('nombre', 120);
            $table->foreignId('articulo_id')
                ->comment('Artículo que se fabrica')
                ->constrained('articulos');
            $table->decimal('cantidad_producida', 12, 4)->default(1)
                ->comment('Unidades producidas por lote de fabricación');
            $table->foreignId('almacen_salida_id')
                ->nullable()
                ->comment('Almacén del que se consumen los componentes')
                ->constrained('almacenes')->nullOnDelete();
            $table->foreignId('almacen_entrada_id')
                ->nullable()
                ->comment('Almacén donde va el producto fabricado')
                ->constrained('almacenes')->nullOnDelete();
            $table->smallInteger('tiempo_fabricacion')->nullable()
                ->comment('Minutos estimados por lote');
            $table->boolean('activa')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('articulo_id');
            $table->index('activa');
        });

        // ── COMPONENTES DE FÓRMULA ────────────────────────────────────────────
        Schema::create('formula_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')
                ->constrained('formulas')->cascadeOnDelete();
            $table->foreignId('articulo_id')
                ->comment('Materia prima / componente')
                ->constrained('articulos');
            $table->decimal('cantidad', 12, 4)
                ->comment('Cantidad necesaria por lote de fabricación');
            $table->string('unidad', 5)->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('es_opcional')->default(false);
            $table->decimal('merma_pct', 5, 2)->default(0)
                ->comment('Porcentaje de merma adicional a la cantidad nominal');
            $table->string('notas', 200)->nullable();
            $table->timestamps();

            $table->unique(['formula_id', 'articulo_id']);
            $table->index('formula_id');
            $table->index('articulo_id');
        });

        // ── ÓRDENES DE FABRICACIÓN ────────────────────────────────────────────
        Schema::create('ordenes_fabricacion', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('formula_id')
                ->constrained('formulas');
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_prevista')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('cantidad_pedida', 12, 4)
                ->comment('Número de lotes a fabricar');
            $table->decimal('cantidad_fabricada', 12, 4)->default(0)
                ->comment('Lotes fabricados realmente');
            $table->string('estado', 20)->default('borrador');
            // borrador → confirmada → en_proceso → completada | anulada
            $table->foreignId('almacen_salida_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('almacen_entrada_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('coste_estimado', 14, 2)->default(0);
            $table->decimal('coste_real', 14, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('numero');
            $table->index('formula_id');
            $table->index('estado');
            $table->index('fecha');
        });

        // ── DETALLE DE ORDEN DE FABRICACIÓN ──────────────────────────────────
        Schema::create('ordenes_fabricacion_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes_fabricacion')->cascadeOnDelete();
            $table->foreignId('articulo_id')
                ->constrained('articulos');
            $table->char('tipo', 1)->default('C');
            // C = Componente consumido, P = Producto fabricado
            $table->decimal('cantidad_teorica', 12, 4)
                ->comment('Cantidad calculada según fórmula');
            $table->decimal('cantidad_real', 12, 4)->default(0)
                ->comment('Cantidad realmente consumida/producida');
            $table->decimal('precio_coste', 12, 4)->default(0);
            $table->foreignId('movimiento_id')
                ->nullable()
                ->comment('Movimiento de almacén generado')
                ->constrained('movimientos_almacen')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('orden_id');
            $table->index('articulo_id');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_fabricacion_detalle');
        Schema::dropIfExists('ordenes_fabricacion');
        Schema::dropIfExists('formula_componentes');
        Schema::dropIfExists('formulas');
    }
};
