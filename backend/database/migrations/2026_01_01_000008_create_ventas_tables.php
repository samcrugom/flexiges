<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── DOCUMENTOS DE VENTA (presupuesto / pedido / albarán / factura) ────
        Schema::create('ventas_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 15);
            // presupuesto, pedido, albaran, factura, rectificativa
            $table->string('numero', 20)->unique();
            $table->string('serie', 10)->nullable();
            $table->date('fecha')->default(DB::raw('CURRENT_DATE'));
            $table->date('fecha_entrega')->nullable();
            $table->foreignId('cliente_id')->constrained('clientes');
            // Datos desnormalizados del cliente en el momento de emisión
            $table->string('cliente_nombre', 120)->nullable();
            $table->string('cliente_nif', 20)->nullable();
            $table->string('cliente_direccion', 200)->nullable();
            // Relaciones
            $table->foreignId('vendedor_id')
                ->nullable()->constrained('vendedores')->nullOnDelete();
            $table->foreignId('forma_pago_id')
                ->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('ruta_id')
                ->nullable()->constrained('rutas')->nullOnDelete();
            $table->foreignId('delegacion_id')
                ->nullable()->constrained('delegaciones')->nullOnDelete();
            // Documento origen
            $table->foreignId('doc_origen_id')
                ->nullable()
                ->comment('Albarán → Pedido origen, Factura → Albarán origen')
                ->constrained('ventas_documentos')->nullOnDelete();
            $table->foreignId('doc_rectifica_id')
                ->nullable()
                ->comment('Para rectificativas: factura original que rectifica')
                ->constrained('ventas_documentos')->nullOnDelete();
            // Totales
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('cuota_recargo', 14, 2)->default(0);
            $table->decimal('total_descuento', 14, 2)->default(0);
            $table->decimal('total_factura', 14, 2)->default(0);
            // Tipo cliente en el momento: N o R
            $table->char('tipo_cliente', 1)->default('N');
            // Estado del documento
            $table->string('estado', 20)->default('borrador');
            // borrador, confirmado, enviado, facturado, cobrado, anulado, parcial
            $table->date('fecha_vencimiento')->nullable();
            $table->string('pdf_path', 255)->nullable();
            // Referencia del cliente (nº pedido cliente)
            $table->string('referencia_cliente', 30)->nullable();
            $table->text('notas')->nullable();
            $table->text('notas_internas')->nullable();
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tipo');
            $table->index('numero');
            $table->index('cliente_id');
            $table->index('fecha');
            $table->index('estado');
            $table->index('vendedor_id');
            $table->index(['tipo', 'estado']);
        });

        // ── LÍNEAS DE DOCUMENTO ───────────────────────────────────────────────
        Schema::create('ventas_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')
                ->constrained('ventas_documentos')->cascadeOnDelete();
            $table->smallInteger('linea');
            $table->char('tipo_linea', 1)->default('A');
            // A=Artículo, C=Comentario, S=Subtotal
            $table->foreignId('articulo_id')
                ->nullable()->constrained('articulos')->restrictOnDelete();
            $table->string('descripcion', 120);
            $table->decimal('cantidad', 12, 4)->default(1);
            $table->decimal('precio_unitario', 12, 4)->default(0);
            $table->decimal('precio_con_iva', 12, 4)->default(0);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->decimal('importe_neto', 14, 2)->default(0);
            $table->foreignId('tipo_iva_id')
                ->nullable()->constrained('tipos_iva')->nullOnDelete();
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('cuota_recargo', 14, 2)->default(0);
            $table->foreignId('almacen_id')
                ->nullable()->constrained('almacenes')->nullOnDelete();
            $table->foreignId('movimiento_id')
                ->nullable()->constrained('movimientos_almacen')->nullOnDelete();
            $table->timestamps();

            $table->index('documento_id');
            $table->index('articulo_id');
        });

        // ── DESGLOSE IVA POR DOCUMENTO ────────────────────────────────────────
        Schema::create('ventas_iva_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')
                ->constrained('ventas_documentos')->cascadeOnDelete();
            $table->foreignId('tipo_iva_id')
                ->constrained('tipos_iva');
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('porcentaje_iva', 5, 2);
            $table->decimal('cuota_iva', 14, 2)->default(0);
            $table->decimal('porcentaje_rec', 5, 2)->default(0);
            $table->decimal('cuota_recargo', 14, 2)->default(0);

            $table->unique(['documento_id', 'tipo_iva_id']);
            $table->index('documento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_iva_detalle');
        Schema::dropIfExists('ventas_lineas');
        Schema::dropIfExists('ventas_documentos');
    }
};
