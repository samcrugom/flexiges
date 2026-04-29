<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── CLIENTES ──────────────────────────────────────────────────────────
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 8)->unique();
            $table->string('nombre', 120);
            $table->string('nombre_comercial', 80)->nullable();
            $table->string('nif', 20)->nullable();
            $table->string('direccion', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidad', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('pais', 40)->default('España');
            $table->string('telefono', 20)->nullable();
            $table->string('telefono2', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('email2', 100)->nullable();
            $table->string('web', 100)->nullable();
            $table->string('persona_contacto', 80)->nullable();
            // Condiciones comerciales
            $table->foreignId('forma_pago_id')
                ->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->char('tipo_cliente', 1)->default('N');
            // N=Normal, R=Recargo Equivalencia, E=Exento IVA
            $table->smallInteger('tarifa')->default(1);
            $table->decimal('descuento_pct', 5, 2)->default(0.00);
            $table->decimal('descuento2_pct', 5, 2)->default(0.00);
            $table->foreignId('vendedor_id')
                ->nullable()->constrained('vendedores')->nullOnDelete();
            $table->foreignId('zona_id')
                ->nullable()->constrained('zonas')->nullOnDelete();
            $table->foreignId('sector_id')
                ->nullable()->constrained('sectores')->nullOnDelete();
            $table->foreignId('ruta_id')
                ->nullable()->constrained('rutas')->nullOnDelete();
            $table->foreignId('delegacion_id')
                ->nullable()->constrained('delegaciones')->nullOnDelete();
            // Control crédito
            $table->decimal('credito_maximo', 12, 2)->default(0.00);
            $table->decimal('riesgo_actual', 12, 2)->default(0.00);
            $table->decimal('saldo_pendiente', 12, 2)->default(0.00);
            // Datos bancarios para cobros domiciliados
            $table->smallInteger('dias_pago')->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('swift', 11)->nullable();
            // Estado
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('nombre');
            $table->index('nif');
            $table->index('vendedor_id');
            $table->index('zona_id');
            $table->index('activo');
        });

        // ── CLIENTES DIRECCIONES ADICIONALES ─────────────────────────────────
        Schema::create('clientes_direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre', 80)->nullable();
            $table->string('direccion', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidad', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
        });

        // ── PROVEEDORES ───────────────────────────────────────────────────────
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 8)->unique();
            $table->string('nombre', 120);
            $table->string('nif', 20)->nullable();
            $table->string('direccion', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidad', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('pais', 40)->default('España');
            $table->string('telefono', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('persona_contacto', 80)->nullable();
            $table->foreignId('forma_pago_id')
                ->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->foreignId('tipo_iva_id')
                ->nullable()->constrained('tipos_iva')->nullOnDelete();
            $table->decimal('descuento_pct', 5, 2)->default(0.00);
            $table->smallInteger('plazo_entrega')->default(0);
            $table->string('iban', 34)->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('nombre');
            $table->index('nif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('clientes_direcciones');
        Schema::dropIfExists('clientes');
    }
};
