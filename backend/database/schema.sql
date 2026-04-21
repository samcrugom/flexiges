-- =============================================================================
-- FLEXIGES NEXT — Esquema PostgreSQL completo
-- Version: 1.0  |  Laravel 12 + PHP 8.4  |  2026
-- =============================================================================

-- Habilitar extensiones
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- =============================================================================
-- 0. EMPRESA / CONFIGURACIÓN
-- =============================================================================
CREATE TABLE empresa (
    id              BIGSERIAL PRIMARY KEY,
    nombre          VARCHAR(120)    NOT NULL,
    nombre_comercial VARCHAR(80),
    nif             VARCHAR(15)     NOT NULL UNIQUE,
    direccion       VARCHAR(100),
    codigo_postal   VARCHAR(10),
    localidad       VARCHAR(60),
    provincia       VARCHAR(60),
    pais            VARCHAR(40)     DEFAULT 'España',
    telefono        VARCHAR(20),
    fax             VARCHAR(20),
    email           VARCHAR(100),
    web             VARCHAR(100),
    iban            VARCHAR(34),
    swift           VARCHAR(11),
    logo_path       VARCHAR(255),
    serie_factura   VARCHAR(10)     DEFAULT 'F-',
    serie_albaran   VARCHAR(10)     DEFAULT 'A-',
    serie_presup    VARCHAR(10)     DEFAULT 'P-',
    serie_pedido    VARCHAR(10)     DEFAULT 'PED-',
    num_factura     INTEGER         DEFAULT 1,
    num_albaran     INTEGER         DEFAULT 1,
    num_presup      INTEGER         DEFAULT 1,
    num_pedido      INTEGER         DEFAULT 1,
    num_pedido_compra INTEGER       DEFAULT 1,
    pie_factura     TEXT,
    texto_legal     TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

-- =============================================================================
-- 1. AUTENTICACIÓN Y SEGURIDAD
-- =============================================================================
CREATE TABLE users (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    email_verified_at TIMESTAMPTZ,
    password        VARCHAR(255)    NOT NULL,
    remember_token  VARCHAR(100),
    avatar_path     VARCHAR(255),
    activo          BOOLEAN         DEFAULT TRUE,
    ultimo_acceso   TIMESTAMPTZ,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE personal_access_tokens (
    id              BIGSERIAL PRIMARY KEY,
    tokenable_type  VARCHAR(255)    NOT NULL,
    tokenable_id    BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    token           VARCHAR(64)     NOT NULL UNIQUE,
    abilities       TEXT,
    last_used_at    TIMESTAMPTZ,
    expires_at      TIMESTAMPTZ,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

-- Spatie permissions
CREATE TABLE roles (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(125)    NOT NULL,
    guard_name      VARCHAR(125)    NOT NULL DEFAULT 'sanctum',
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (name, guard_name)
);

CREATE TABLE permissions (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(125)    NOT NULL,
    guard_name      VARCHAR(125)    NOT NULL DEFAULT 'sanctum',
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (name, guard_name)
);

CREATE TABLE model_has_roles (
    role_id         BIGINT          NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    model_type      VARCHAR(255)    NOT NULL,
    model_id        BIGINT          NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);

CREATE TABLE model_has_permissions (
    permission_id   BIGINT          NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    model_type      VARCHAR(255)    NOT NULL,
    model_id        BIGINT          NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);

CREATE TABLE role_has_permissions (
    permission_id   BIGINT          NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    role_id         BIGINT          NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- Auditoría
CREATE TABLE audit_logs (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    event           VARCHAR(50)     NOT NULL, -- created, updated, deleted, login, etc.
    auditable_type  VARCHAR(255),
    auditable_id    BIGINT,
    old_values      JSONB,
    new_values      JSONB,
    url             TEXT,
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_audit_auditable ON audit_logs(auditable_type, auditable_id);
CREATE INDEX idx_audit_user ON audit_logs(user_id);
CREATE INDEX idx_audit_created ON audit_logs(created_at);

-- =============================================================================
-- 2. MAESTROS / TABLAS DE REFERENCIA
-- =============================================================================

CREATE TABLE provincias (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    pais            VARCHAR(40)     DEFAULT 'España'
);

CREATE TABLE formas_pago (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(10)     NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    tipo            VARCHAR(30)     NOT NULL DEFAULT 'contado',
    -- contado, transferencia, recibo, pagare, confirming, etc.
    dias_vencimiento SMALLINT       DEFAULT 0,
    num_vencimientos SMALLINT       DEFAULT 1,
    activo          BOOLEAN         DEFAULT TRUE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE tipos_iva (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    descripcion     VARCHAR(40),
    porcentaje_iva  DECIMAL(5,2)    NOT NULL,
    porcentaje_rec  DECIMAL(5,2)    DEFAULT 0.00,
    -- Recargo de equivalencia
    activo          BOOLEAN         DEFAULT TRUE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE zonas (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE sectores (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE delegaciones (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    direccion       VARCHAR(100),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE rutas (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    activo          BOOLEAN         DEFAULT TRUE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE familias_articulo (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(10)     NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    parent_id       BIGINT          REFERENCES familias_articulo(id) ON DELETE SET NULL,
    -- Para subfamilias: parent_id apunta a la familia padre
    orden           SMALLINT        DEFAULT 0,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE almacenes (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(60)     NOT NULL,
    direccion       VARCHAR(100),
    es_principal    BOOLEAN         DEFAULT FALSE,
    activo          BOOLEAN         DEFAULT TRUE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

-- =============================================================================
-- 3. VENDEDORES
-- =============================================================================
CREATE TABLE vendedores (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(5)      NOT NULL UNIQUE,
    nombre          VARCHAR(80)     NOT NULL,
    nif             VARCHAR(15),
    email           VARCHAR(100),
    telefono        VARCHAR(20),
    zona_id         BIGINT          REFERENCES zonas(id) ON DELETE SET NULL,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    comision_pct    DECIMAL(5,2)    DEFAULT 0.00,
    activo          BOOLEAN         DEFAULT TRUE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

-- =============================================================================
-- 4. CLIENTES
-- =============================================================================
CREATE TABLE clientes (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(8)      NOT NULL UNIQUE,
    nombre          VARCHAR(120)    NOT NULL,
    nombre_comercial VARCHAR(80),
    nif             VARCHAR(20),
    direccion       VARCHAR(100),
    codigo_postal   VARCHAR(10),
    localidad       VARCHAR(60),
    provincia       VARCHAR(60),
    pais            VARCHAR(40)     DEFAULT 'España',
    telefono        VARCHAR(20),
    telefono2       VARCHAR(20),
    fax             VARCHAR(20),
    email           VARCHAR(100),
    email2          VARCHAR(100),
    web             VARCHAR(100),
    persona_contacto VARCHAR(80),
    -- Condiciones comerciales
    forma_pago_id   BIGINT          REFERENCES formas_pago(id) ON DELETE SET NULL,
    tipo_cliente    CHAR(1)         NOT NULL DEFAULT 'N',
    -- N=Normal, R=Recargo de Equivalencia, E=Exento IVA
    tarifa          SMALLINT        NOT NULL DEFAULT 1, -- 1-4
    descuento_pct   DECIMAL(5,2)    DEFAULT 0.00,
    descuento2_pct  DECIMAL(5,2)    DEFAULT 0.00,
    vendedor_id     BIGINT          REFERENCES vendedores(id) ON DELETE SET NULL,
    zona_id         BIGINT          REFERENCES zonas(id) ON DELETE SET NULL,
    sector_id       BIGINT          REFERENCES sectores(id) ON DELETE SET NULL,
    ruta_id         BIGINT          REFERENCES rutas(id) ON DELETE SET NULL,
    delegacion_id   BIGINT          REFERENCES delegaciones(id) ON DELETE SET NULL,
    -- Control crédito
    credito_maximo  DECIMAL(12,2)   DEFAULT 0.00,
    riesgo_actual   DECIMAL(12,2)   DEFAULT 0.00,
    -- Estadísticas (calculadas)
    saldo_pendiente DECIMAL(12,2)   DEFAULT 0.00,
    -- Configuración
    dias_pago       SMALLINT,
    iban            VARCHAR(34),
    swift           VARCHAR(11),
    -- Estado
    activo          BOOLEAN         DEFAULT TRUE,
    observaciones   TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_clientes_codigo ON clientes(codigo);
CREATE INDEX idx_clientes_nombre ON clientes USING gin(to_tsvector('spanish', unaccent(nombre)));
CREATE INDEX idx_clientes_nif ON clientes(nif);
CREATE INDEX idx_clientes_vendedor ON clientes(vendedor_id);
CREATE INDEX idx_clientes_zona ON clientes(zona_id);

-- Direcciones adicionales de entrega
CREATE TABLE clientes_direcciones (
    id              BIGSERIAL PRIMARY KEY,
    cliente_id      BIGINT          NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
    nombre          VARCHAR(80),
    direccion       VARCHAR(100),
    codigo_postal   VARCHAR(10),
    localidad       VARCHAR(60),
    provincia       VARCHAR(60),
    telefono        VARCHAR(20),
    es_principal    BOOLEAN         DEFAULT FALSE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

-- =============================================================================
-- 5. PROVEEDORES
-- =============================================================================
CREATE TABLE proveedores (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(8)      NOT NULL UNIQUE,
    nombre          VARCHAR(120)    NOT NULL,
    nif             VARCHAR(20),
    direccion       VARCHAR(100),
    codigo_postal   VARCHAR(10),
    localidad       VARCHAR(60),
    provincia       VARCHAR(60),
    pais            VARCHAR(40)     DEFAULT 'España',
    telefono        VARCHAR(20),
    fax             VARCHAR(20),
    email           VARCHAR(100),
    persona_contacto VARCHAR(80),
    forma_pago_id   BIGINT          REFERENCES formas_pago(id) ON DELETE SET NULL,
    descuento_pct   DECIMAL(5,2)    DEFAULT 0.00,
    plazo_entrega   SMALLINT        DEFAULT 0,
    iban            VARCHAR(34),
    tipo_iva_id     BIGINT          REFERENCES tipos_iva(id) ON DELETE SET NULL,
    activo          BOOLEAN         DEFAULT TRUE,
    observaciones   TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_proveedores_codigo ON proveedores(codigo);
CREATE INDEX idx_proveedores_nombre ON proveedores(nombre);

-- =============================================================================
-- 6. ARTÍCULOS
-- =============================================================================
CREATE TABLE articulos (
    id              BIGSERIAL PRIMARY KEY,
    referencia      VARCHAR(20)     NOT NULL UNIQUE,
    descripcion     VARCHAR(120)    NOT NULL,
    descripcion2    VARCHAR(120),
    familia_id      BIGINT          REFERENCES familias_articulo(id) ON DELETE SET NULL,
    proveedor_id    BIGINT          REFERENCES proveedores(id) ON DELETE SET NULL,
    tipo_iva_id     BIGINT          NOT NULL REFERENCES tipos_iva(id),
    unidad_medida   VARCHAR(5)      NOT NULL DEFAULT 'UD',
    -- UD, KG, MT, LT, CJ, etc.
    ean13           VARCHAR(13),
    ean14           VARCHAR(14),
    peso            DECIMAL(10,4),
    volumen         DECIMAL(10,4),
    -- Precios (4 tarifas)
    precio_coste    DECIMAL(12,4)   DEFAULT 0.0000,
    ult_precio_coste DECIMAL(12,4)  DEFAULT 0.0000,
    tarifa1         DECIMAL(12,4)   DEFAULT 0.0000,
    tarifa2         DECIMAL(12,4)   DEFAULT 0.0000,
    tarifa3         DECIMAL(12,4)   DEFAULT 0.0000,
    tarifa4         DECIMAL(12,4)   DEFAULT 0.0000,
    -- Stock global (calculado a partir de stock_almacen)
    stock_total     DECIMAL(14,4)   DEFAULT 0.0000,
    stock_minimo    DECIMAL(14,4)   DEFAULT 0.0000,
    stock_maximo    DECIMAL(14,4),
    stock_reposicion DECIMAL(14,4),
    -- Fabricación
    es_fabricado    BOOLEAN         DEFAULT FALSE,
    -- Compras
    ref_proveedor   VARCHAR(30),
    -- Estado
    activo          BOOLEAN         DEFAULT TRUE,
    observaciones   TEXT,
    imagen_path     VARCHAR(255),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_articulos_ref ON articulos(referencia);
CREATE INDEX idx_articulos_desc ON articulos USING gin(to_tsvector('spanish', unaccent(descripcion)));
CREATE INDEX idx_articulos_familia ON articulos(familia_id);
CREATE INDEX idx_articulos_ean ON articulos(ean13);

-- Stock por almacén
CREATE TABLE stock_almacen (
    id              BIGSERIAL PRIMARY KEY,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id) ON DELETE CASCADE,
    almacen_id      BIGINT          NOT NULL REFERENCES almacenes(id) ON DELETE CASCADE,
    stock           DECIMAL(14,4)   NOT NULL DEFAULT 0.0000,
    stock_reservado DECIMAL(14,4)   NOT NULL DEFAULT 0.0000,
    -- Stock pendiente de recibir (pedidos compra)
    stock_pedido    DECIMAL(14,4)   NOT NULL DEFAULT 0.0000,
    ubicacion       VARCHAR(30),
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (articulo_id, almacen_id)
);

CREATE INDEX idx_stock_articulo ON stock_almacen(articulo_id);
CREATE INDEX idx_stock_almacen ON stock_almacen(almacen_id);

-- Tarifas especiales por cliente
CREATE TABLE articulos_tarifas_cliente (
    id              BIGSERIAL PRIMARY KEY,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id) ON DELETE CASCADE,
    cliente_id      BIGINT          NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
    precio          DECIMAL(12,4)   NOT NULL,
    descuento_pct   DECIMAL(5,2)    DEFAULT 0.00,
    fecha_desde     DATE,
    fecha_hasta     DATE,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (articulo_id, cliente_id)
);

-- =============================================================================
-- 7. MOVIMIENTOS DE ALMACÉN
-- =============================================================================
CREATE TABLE movimientos_almacen (
    id              BIGSERIAL PRIMARY KEY,
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    almacen_id      BIGINT          NOT NULL REFERENCES almacenes(id),
    tipo_movimiento VARCHAR(20)     NOT NULL,
    -- ENTRADA, SALIDA, AJUSTE_POS, AJUSTE_NEG, FABRICACION_IN,
    -- FABRICACION_OUT, DEVOLUCION_CLI, DEVOLUCION_PROV, TRASPASO_IN, TRASPASO_OUT
    cantidad        DECIMAL(14,4)   NOT NULL,
    -- positivo = entrada, negativo = salida
    stock_anterior  DECIMAL(14,4)   NOT NULL DEFAULT 0,
    stock_posterior DECIMAL(14,4)   NOT NULL DEFAULT 0,
    precio_coste    DECIMAL(12,4)   DEFAULT 0,
    documento_tipo  VARCHAR(20),
    -- FACTURA_VTA, ALBARAN_VTA, PEDIDO_COMPRA, ALBARAN_COMPRA,
    -- ORDEN_FAB, AJUSTE, INVENTARIO, TRASPASO
    documento_id    BIGINT,
    documento_ref   VARCHAR(20),
    lote            VARCHAR(30),
    observaciones   VARCHAR(200),
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_mov_articulo ON movimientos_almacen(articulo_id);
CREATE INDEX idx_mov_almacen ON movimientos_almacen(almacen_id);
CREATE INDEX idx_mov_fecha ON movimientos_almacen(fecha);
CREATE INDEX idx_mov_tipo ON movimientos_almacen(tipo_movimiento);
CREATE INDEX idx_mov_documento ON movimientos_almacen(documento_tipo, documento_id);

-- =============================================================================
-- 8. FABRICACIÓN — BOM (Bill of Materials)
-- =============================================================================
CREATE TABLE formulas (
    id              BIGSERIAL PRIMARY KEY,
    codigo          VARCHAR(15)     NOT NULL UNIQUE,
    nombre          VARCHAR(120)    NOT NULL,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    -- Artículo que se fabrica
    cantidad_producida DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
    -- Cuántas unidades se producen por lote
    almacen_salida_id  BIGINT       REFERENCES almacenes(id) ON DELETE SET NULL,
    -- Almacén desde donde se consumen los componentes
    almacen_entrada_id BIGINT       REFERENCES almacenes(id) ON DELETE SET NULL,
    -- Almacén donde va el producto fabricado
    tiempo_fabricacion SMALLINT,
    -- Minutos estimados por lote
    activa          BOOLEAN         DEFAULT TRUE,
    notas           TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE formula_componentes (
    id              BIGSERIAL PRIMARY KEY,
    formula_id      BIGINT          NOT NULL REFERENCES formulas(id) ON DELETE CASCADE,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    cantidad        DECIMAL(12,4)   NOT NULL,
    -- Cantidad necesaria por lote de fabricación
    unidad          VARCHAR(5),
    orden           SMALLINT        DEFAULT 0,
    es_opcional     BOOLEAN         DEFAULT FALSE,
    merma_pct       DECIMAL(5,2)    DEFAULT 0.00,
    -- % de merma adicional a aplicar
    notas           VARCHAR(200),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (formula_id, articulo_id)
);

CREATE INDEX idx_formula_comp_formula ON formula_componentes(formula_id);
CREATE INDEX idx_formula_comp_articulo ON formula_componentes(articulo_id);

CREATE TABLE ordenes_fabricacion (
    id              BIGSERIAL PRIMARY KEY,
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    formula_id      BIGINT          NOT NULL REFERENCES formulas(id),
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_prevista  DATE,
    fecha_fin       DATE,
    cantidad_pedida DECIMAL(12,4)   NOT NULL,
    -- Lotes a fabricar
    cantidad_fabricada DECIMAL(12,4) DEFAULT 0,
    estado          VARCHAR(20)     NOT NULL DEFAULT 'borrador',
    -- borrador, confirmada, en_proceso, completada, anulada
    almacen_salida_id  BIGINT       REFERENCES almacenes(id),
    almacen_entrada_id BIGINT       REFERENCES almacenes(id),
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    coste_estimado  DECIMAL(14,2)   DEFAULT 0,
    coste_real      DECIMAL(14,2)   DEFAULT 0,
    notas           TEXT,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_of_numero ON ordenes_fabricacion(numero);
CREATE INDEX idx_of_formula ON ordenes_fabricacion(formula_id);
CREATE INDEX idx_of_estado ON ordenes_fabricacion(estado);
CREATE INDEX idx_of_fecha ON ordenes_fabricacion(fecha);

CREATE TABLE ordenes_fabricacion_detalle (
    id              BIGSERIAL PRIMARY KEY,
    orden_id        BIGINT          NOT NULL REFERENCES ordenes_fabricacion(id) ON DELETE CASCADE,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    tipo            CHAR(1)         NOT NULL DEFAULT 'C',
    -- C=Componente consumido, P=Producto fabricado
    cantidad_teorica   DECIMAL(12,4) NOT NULL,
    cantidad_real      DECIMAL(12,4) DEFAULT 0,
    precio_coste    DECIMAL(12,4)   DEFAULT 0,
    movimiento_id   BIGINT          REFERENCES movimientos_almacen(id) ON DELETE SET NULL,
    -- Referencia al movimiento de stock generado
    created_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_ofd_orden ON ordenes_fabricacion_detalle(orden_id);
CREATE INDEX idx_ofd_articulo ON ordenes_fabricacion_detalle(articulo_id);

-- =============================================================================
-- 9. COMPRAS
-- =============================================================================
CREATE TABLE pedidos_compra (
    id              BIGSERIAL PRIMARY KEY,
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    proveedor_id    BIGINT          NOT NULL REFERENCES proveedores(id),
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_prevista  DATE,
    forma_pago_id   BIGINT          REFERENCES formas_pago(id),
    almacen_id      BIGINT          REFERENCES almacenes(id),
    estado          VARCHAR(20)     NOT NULL DEFAULT 'borrador',
    -- borrador, confirmado, parcial, recibido, anulado
    base_imponible  DECIMAL(14,2)   DEFAULT 0,
    cuota_iva       DECIMAL(14,2)   DEFAULT 0,
    total           DECIMAL(14,2)   DEFAULT 0,
    notas           TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE pedidos_compra_lineas (
    id              BIGSERIAL PRIMARY KEY,
    pedido_id       BIGINT          NOT NULL REFERENCES pedidos_compra(id) ON DELETE CASCADE,
    linea           SMALLINT        NOT NULL,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    descripcion     VARCHAR(120),
    cantidad        DECIMAL(12,4)   NOT NULL,
    cantidad_recibida DECIMAL(12,4) DEFAULT 0,
    precio          DECIMAL(12,4)   NOT NULL DEFAULT 0,
    descuento_pct   DECIMAL(5,2)    DEFAULT 0,
    importe_neto    DECIMAL(14,2)   DEFAULT 0,
    tipo_iva_id     BIGINT          REFERENCES tipos_iva(id),
    almacen_id      BIGINT          REFERENCES almacenes(id),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE albaranes_compra (
    id              BIGSERIAL PRIMARY KEY,
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    proveedor_id    BIGINT          NOT NULL REFERENCES proveedores(id),
    pedido_id       BIGINT          REFERENCES pedidos_compra(id),
    factura_compra_id BIGINT,       -- FK a facturas_compra (post-creación)
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    ref_proveedor   VARCHAR(30),
    almacen_id      BIGINT          REFERENCES almacenes(id),
    estado          VARCHAR(20)     NOT NULL DEFAULT 'pendiente',
    -- pendiente, facturado
    base_imponible  DECIMAL(14,2)   DEFAULT 0,
    cuota_iva       DECIMAL(14,2)   DEFAULT 0,
    total           DECIMAL(14,2)   DEFAULT 0,
    notas           TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE albaranes_compra_lineas (
    id              BIGSERIAL PRIMARY KEY,
    albaran_id      BIGINT          NOT NULL REFERENCES albaranes_compra(id) ON DELETE CASCADE,
    linea           SMALLINT        NOT NULL,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id),
    descripcion     VARCHAR(120),
    cantidad        DECIMAL(12,4)   NOT NULL,
    precio          DECIMAL(12,4)   NOT NULL DEFAULT 0,
    descuento_pct   DECIMAL(5,2)    DEFAULT 0,
    importe_neto    DECIMAL(14,2)   DEFAULT 0,
    tipo_iva_id     BIGINT          REFERENCES tipos_iva(id),
    almacen_id      BIGINT          REFERENCES almacenes(id),
    movimiento_id   BIGINT          REFERENCES movimientos_almacen(id),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE facturas_compra (
    id              BIGSERIAL PRIMARY KEY,
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    proveedor_id    BIGINT          NOT NULL REFERENCES proveedores(id),
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_contable  DATE,
    ref_proveedor   VARCHAR(30),
    forma_pago_id   BIGINT          REFERENCES formas_pago(id),
    estado          VARCHAR(20)     NOT NULL DEFAULT 'pendiente',
    -- pendiente, pagada, vencida, anulada
    base_imponible1 DECIMAL(14,2)   DEFAULT 0,
    tipo_iva1       DECIMAL(5,2)    DEFAULT 21,
    cuota_iva1      DECIMAL(14,2)   DEFAULT 0,
    base_imponible2 DECIMAL(14,2)   DEFAULT 0,
    tipo_iva2       DECIMAL(5,2)    DEFAULT 0,
    cuota_iva2      DECIMAL(14,2)   DEFAULT 0,
    total           DECIMAL(14,2)   DEFAULT 0,
    notas           TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

ALTER TABLE albaranes_compra
    ADD CONSTRAINT fk_albcomp_factura
    FOREIGN KEY (factura_compra_id) REFERENCES facturas_compra(id);

-- =============================================================================
-- 10. VENTAS
-- =============================================================================

-- Tabla unificada de documentos de venta (presupuesto, pedido, albarán, factura)
CREATE TABLE ventas_documentos (
    id              BIGSERIAL PRIMARY KEY,
    tipo            VARCHAR(15)     NOT NULL,
    -- presupuesto, pedido, albaran, factura, rectificativa
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    serie           VARCHAR(10),
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_entrega   DATE,
    cliente_id      BIGINT          NOT NULL REFERENCES clientes(id),
    -- Datos del cliente en el momento de la emisión (desnormalizado)
    cliente_nombre  VARCHAR(120),
    cliente_nif     VARCHAR(20),
    cliente_direccion VARCHAR(200),
    vendedor_id     BIGINT          REFERENCES vendedores(id) ON DELETE SET NULL,
    forma_pago_id   BIGINT          REFERENCES formas_pago(id) ON DELETE SET NULL,
    almacen_id      BIGINT          REFERENCES almacenes(id) ON DELETE SET NULL,
    ruta_id         BIGINT          REFERENCES rutas(id) ON DELETE SET NULL,
    delegacion_id   BIGINT          REFERENCES delegaciones(id) ON DELETE SET NULL,
    -- Documento origen (albarán viene de pedido, factura viene de albarán)
    doc_origen_id   BIGINT          REFERENCES ventas_documentos(id) ON DELETE SET NULL,
    -- Para rectificativas: factura original
    doc_rectifica_id BIGINT         REFERENCES ventas_documentos(id) ON DELETE SET NULL,
    -- Totales
    base_imponible  DECIMAL(14,2)   DEFAULT 0,
    cuota_iva       DECIMAL(14,2)   DEFAULT 0,
    cuota_recargo   DECIMAL(14,2)   DEFAULT 0,
    total_descuento DECIMAL(14,2)   DEFAULT 0,
    total_factura   DECIMAL(14,2)   DEFAULT 0,
    -- Tipo cliente en el momento: N o R (recargo equivalencia)
    tipo_cliente    CHAR(1)         DEFAULT 'N',
    -- Estado
    estado          VARCHAR(20)     NOT NULL DEFAULT 'borrador',
    -- borrador, confirmado, enviado, facturado, cobrado, anulado, parcial
    -- Para facturas
    fecha_vencimiento DATE,
    -- PDF generado
    pdf_path        VARCHAR(255),
    -- Control
    referencia_cliente VARCHAR(30),
    -- Nº de pedido del cliente
    notas           TEXT,
    notas_internas  TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_vdoc_tipo ON ventas_documentos(tipo);
CREATE INDEX idx_vdoc_numero ON ventas_documentos(numero);
CREATE INDEX idx_vdoc_cliente ON ventas_documentos(cliente_id);
CREATE INDEX idx_vdoc_fecha ON ventas_documentos(fecha);
CREATE INDEX idx_vdoc_estado ON ventas_documentos(estado);
CREATE INDEX idx_vdoc_vendedor ON ventas_documentos(vendedor_id);

CREATE TABLE ventas_lineas (
    id              BIGSERIAL PRIMARY KEY,
    documento_id    BIGINT          NOT NULL REFERENCES ventas_documentos(id) ON DELETE CASCADE,
    linea           SMALLINT        NOT NULL,
    tipo_linea      CHAR(1)         DEFAULT 'A',
    -- A=Artículo, C=Comentario, S=Subtotal
    articulo_id     BIGINT          REFERENCES articulos(id) ON DELETE RESTRICT,
    descripcion     VARCHAR(120)    NOT NULL,
    cantidad        DECIMAL(12,4)   NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(12,4)   NOT NULL DEFAULT 0,
    precio_con_iva  DECIMAL(12,4)   DEFAULT 0,
    descuento_pct   DECIMAL(5,2)    DEFAULT 0,
    importe_neto    DECIMAL(14,2)   DEFAULT 0,
    tipo_iva_id     BIGINT          REFERENCES tipos_iva(id),
    cuota_iva       DECIMAL(14,2)   DEFAULT 0,
    cuota_recargo   DECIMAL(14,2)   DEFAULT 0,
    almacen_id      BIGINT          REFERENCES almacenes(id),
    movimiento_id   BIGINT          REFERENCES movimientos_almacen(id),
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_vlin_documento ON ventas_lineas(documento_id);
CREATE INDEX idx_vlin_articulo ON ventas_lineas(articulo_id);

-- Desglose de IVA por tipo en el documento
CREATE TABLE ventas_iva_detalle (
    id              BIGSERIAL PRIMARY KEY,
    documento_id    BIGINT          NOT NULL REFERENCES ventas_documentos(id) ON DELETE CASCADE,
    tipo_iva_id     BIGINT          NOT NULL REFERENCES tipos_iva(id),
    base_imponible  DECIMAL(14,2)   NOT NULL DEFAULT 0,
    porcentaje_iva  DECIMAL(5,2)    NOT NULL,
    cuota_iva       DECIMAL(14,2)   NOT NULL DEFAULT 0,
    porcentaje_rec  DECIMAL(5,2)    DEFAULT 0,
    cuota_recargo   DECIMAL(14,2)   DEFAULT 0,
    UNIQUE (documento_id, tipo_iva_id)
);

-- =============================================================================
-- 11. COBROS
-- =============================================================================
CREATE TABLE cobros (
    id              BIGSERIAL PRIMARY KEY,
    factura_id      BIGINT          NOT NULL REFERENCES ventas_documentos(id),
    cliente_id      BIGINT          NOT NULL REFERENCES clientes(id),
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    fecha_emision   DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_vencimiento DATE          NOT NULL,
    importe_total   DECIMAL(14,2)   NOT NULL,
    importe_cobrado DECIMAL(14,2)   DEFAULT 0,
    importe_pendiente DECIMAL(14,2) GENERATED ALWAYS AS (importe_total - importe_cobrado) STORED,
    tipo_cobro      VARCHAR(30)     DEFAULT 'recibo',
    -- recibo, transferencia, efectivo, cheque, pagare, tarjeta
    estado          VARCHAR(20)     NOT NULL DEFAULT 'pendiente',
    -- pendiente, cobrado, parcial, impagado, anulado
    entidad_bancaria VARCHAR(80),
    cuenta_bancaria VARCHAR(34),
    remesa_id       BIGINT,
    -- FK a remesas (se añade tras crear tabla)
    notas           TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE INDEX idx_cobros_factura ON cobros(factura_id);
CREATE INDEX idx_cobros_cliente ON cobros(cliente_id);
CREATE INDEX idx_cobros_vencimiento ON cobros(fecha_vencimiento);
CREATE INDEX idx_cobros_estado ON cobros(estado);

CREATE TABLE cobros_movimientos (
    id              BIGSERIAL PRIMARY KEY,
    cobro_id        BIGINT          NOT NULL REFERENCES cobros(id) ON DELETE CASCADE,
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    importe         DECIMAL(14,2)   NOT NULL,
    tipo            VARCHAR(30),
    concepto        VARCHAR(200),
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW()
);

CREATE TABLE remesas (
    id              BIGSERIAL PRIMARY KEY,
    numero          VARCHAR(20)     NOT NULL UNIQUE,
    fecha           DATE            NOT NULL DEFAULT CURRENT_DATE,
    fecha_presentacion DATE,
    banco           VARCHAR(80),
    cuenta          VARCHAR(34),
    num_efectos     INTEGER         DEFAULT 0,
    importe_total   DECIMAL(14,2)   DEFAULT 0,
    estado          VARCHAR(20)     DEFAULT 'preparada',
    -- preparada, presentada, cobrada, devuelta
    notas           TEXT,
    user_id         BIGINT          REFERENCES users(id) ON DELETE SET NULL,
    created_at      TIMESTAMPTZ     DEFAULT NOW(),
    updated_at      TIMESTAMPTZ     DEFAULT NOW()
);

ALTER TABLE cobros ADD CONSTRAINT fk_cobro_remesa
    FOREIGN KEY (remesa_id) REFERENCES remesas(id) ON DELETE SET NULL;

-- =============================================================================
-- 12. ESTADÍSTICAS (tablas de resumen para performance)
-- =============================================================================
CREATE TABLE estadisticas_ventas_cliente (
    id              BIGSERIAL PRIMARY KEY,
    cliente_id      BIGINT          NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
    ejercicio       SMALLINT        NOT NULL,
    mes             SMALLINT        NOT NULL, -- 1-12
    num_facturas    INTEGER         DEFAULT 0,
    base_imponible  DECIMAL(14,2)   DEFAULT 0,
    cuota_iva       DECIMAL(14,2)   DEFAULT 0,
    total           DECIMAL(14,2)   DEFAULT 0,
    coste_total     DECIMAL(14,2)   DEFAULT 0,
    margen          DECIMAL(14,2)   DEFAULT 0,
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (cliente_id, ejercicio, mes)
);

CREATE TABLE estadisticas_ventas_articulo (
    id              BIGSERIAL PRIMARY KEY,
    articulo_id     BIGINT          NOT NULL REFERENCES articulos(id) ON DELETE CASCADE,
    ejercicio       SMALLINT        NOT NULL,
    mes             SMALLINT        NOT NULL,
    cantidad        DECIMAL(14,4)   DEFAULT 0,
    importe_neto    DECIMAL(14,2)   DEFAULT 0,
    coste_total     DECIMAL(14,2)   DEFAULT 0,
    margen          DECIMAL(14,2)   DEFAULT 0,
    updated_at      TIMESTAMPTZ     DEFAULT NOW(),
    UNIQUE (articulo_id, ejercicio, mes)
);

-- =============================================================================
-- 13. SISTEMA / LARAVEL FRAMEWORK
-- =============================================================================
CREATE TABLE migrations (
    id              SERIAL PRIMARY KEY,
    migration       VARCHAR(255)    NOT NULL,
    batch           INTEGER         NOT NULL
);

CREATE TABLE cache (
    key             VARCHAR(255)    PRIMARY KEY,
    value           TEXT            NOT NULL,
    expiration      INTEGER         NOT NULL
);

CREATE TABLE cache_locks (
    key             VARCHAR(255)    PRIMARY KEY,
    owner           VARCHAR(255)    NOT NULL,
    expiration      INTEGER         NOT NULL
);

CREATE TABLE jobs (
    id              BIGSERIAL PRIMARY KEY,
    queue           VARCHAR(255)    NOT NULL,
    payload         TEXT            NOT NULL,
    attempts        SMALLINT        NOT NULL DEFAULT 0,
    reserved_at     INTEGER,
    available_at    INTEGER         NOT NULL,
    created_at      INTEGER         NOT NULL
);

CREATE TABLE failed_jobs (
    id              BIGSERIAL PRIMARY KEY,
    uuid            VARCHAR(255)    NOT NULL UNIQUE,
    connection      TEXT            NOT NULL,
    queue           TEXT            NOT NULL,
    payload         TEXT            NOT NULL,
    exception       TEXT            NOT NULL,
    failed_at       TIMESTAMPTZ     DEFAULT NOW()
);

-- =============================================================================
-- FUNCIONES Y TRIGGERS
-- =============================================================================

-- Trigger: actualizar stock_total en articulos cuando cambia stock_almacen
CREATE OR REPLACE FUNCTION update_stock_total()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE articulos
    SET stock_total = (
        SELECT COALESCE(SUM(stock), 0)
        FROM stock_almacen
        WHERE articulo_id = NEW.articulo_id
    ),
    updated_at = NOW()
    WHERE id = NEW.articulo_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_stock_total
AFTER INSERT OR UPDATE ON stock_almacen
FOR EACH ROW EXECUTE FUNCTION update_stock_total();

-- Trigger: actualizar saldo_pendiente en clientes cuando cambia cobros
CREATE OR REPLACE FUNCTION update_saldo_cliente()
RETURNS TRIGGER AS $$
DECLARE v_cliente_id BIGINT;
BEGIN
    v_cliente_id := COALESCE(NEW.cliente_id, OLD.cliente_id);
    UPDATE clientes
    SET saldo_pendiente = (
        SELECT COALESCE(SUM(importe_pendiente), 0)
        FROM cobros
        WHERE cliente_id = v_cliente_id
        AND estado IN ('pendiente', 'parcial')
    ),
    updated_at = NOW()
    WHERE id = v_cliente_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_saldo_cliente
AFTER INSERT OR UPDATE OR DELETE ON cobros
FOR EACH ROW EXECUTE FUNCTION update_saldo_cliente();

-- =============================================================================
-- VISTAS ÚTILES
-- =============================================================================

CREATE VIEW v_stock_bajo_minimo AS
SELECT
    a.id,
    a.referencia,
    a.descripcion,
    a.stock_total,
    a.stock_minimo,
    a.stock_total - a.stock_minimo AS diferencia,
    f.nombre AS familia
FROM articulos a
LEFT JOIN familias_articulo f ON f.id = a.familia_id
WHERE a.activo = TRUE
AND a.stock_total < a.stock_minimo
ORDER BY diferencia ASC;

CREATE VIEW v_cobros_pendientes AS
SELECT
    c.id,
    c.numero,
    c.fecha_vencimiento,
    c.importe_total,
    c.importe_cobrado,
    c.importe_pendiente,
    c.estado,
    c.tipo_cobro,
    cl.id AS cliente_id,
    cl.codigo AS cliente_codigo,
    cl.nombre AS cliente_nombre,
    vd.numero AS factura_numero,
    CASE
        WHEN c.fecha_vencimiento < CURRENT_DATE AND c.estado = 'pendiente' THEN TRUE
        ELSE FALSE
    END AS vencido,
    CURRENT_DATE - c.fecha_vencimiento AS dias_vencido
FROM cobros c
JOIN clientes cl ON cl.id = c.cliente_id
JOIN ventas_documentos vd ON vd.id = c.factura_id
WHERE c.estado IN ('pendiente', 'parcial');

CREATE VIEW v_dashboard_kpis AS
SELECT
    -- Ventas mes actual
    (SELECT COALESCE(SUM(total_factura), 0)
     FROM ventas_documentos
     WHERE tipo = 'factura'
     AND estado NOT IN ('anulado', 'borrador')
     AND DATE_TRUNC('month', fecha) = DATE_TRUNC('month', CURRENT_DATE)
    ) AS ventas_mes,
    -- Cobros pendientes
    (SELECT COALESCE(SUM(importe_pendiente), 0)
     FROM cobros WHERE estado IN ('pendiente', 'parcial')
    ) AS cobros_pendientes,
    -- Artículos bajo mínimo
    (SELECT COUNT(*) FROM v_stock_bajo_minimo) AS articulos_bajo_minimo,
    -- Órdenes fabricación activas
    (SELECT COUNT(*)
     FROM ordenes_fabricacion
     WHERE estado IN ('confirmada', 'en_proceso')
    ) AS ordenes_fab_activas,
    -- Facturas pendientes cobro
    (SELECT COUNT(*)
     FROM cobros WHERE estado IN ('pendiente', 'parcial')
    ) AS num_cobros_pendientes;
