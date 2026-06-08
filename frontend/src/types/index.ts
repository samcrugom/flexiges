// ── Maestros ───────────────────────────────────────────────────────────────
export interface FormaPago  { id:number; codigo:string; nombre:string; tipo:string; dias_vencimiento:number }
export interface TipoIva    { id:number; codigo:string; descripcion:string; porcentaje_iva:number; porcentaje_rec:number }
export interface Almacen    { id:number; codigo:string; nombre:string; es_principal:boolean }
export interface Vendedor   { id:number; codigo:string; nombre:string; comision_pct:number; zona?:Zona }
export interface Zona       { id:number; codigo:string; nombre:string }
export interface Sector     { id:number; codigo:string; nombre:string }
export interface FamiliaArticulo { id:number; codigo:string; nombre:string; children?:FamiliaArticulo[] }
export interface Empresa    { id:number; nombre:string; nif:string; direccion:string; codigo_postal:string; localidad:string; provincia:string; telefono:string; email:string; iban:string; serie_factura:string; serie_albaran:string; num_factura:number; pie_factura:string }

// ── Clientes ───────────────────────────────────────────────────────────────
export interface Cliente {
  id:number; codigo:string; nombre:string; nombre_comercial?:string; nif?:string
  direccion?:string; codigo_postal?:string; localidad?:string; provincia?:string
  telefono?:string; email?:string; persona_contacto?:string
  tipo_cliente:'N'|'R'|'E'; tarifa:1|2|3|4
  descuento_pct:number; descuento2_pct?:number
  forma_pago_id?:number; vendedor_id?:number; zona_id?:number; sector_id?:number
  credito_maximo:number; saldo_pendiente:number; riesgo_actual:number
  iban?:string; activo:boolean; observaciones?:string
  forma_pago?:FormaPago; vendedor?:Vendedor; zona?:Zona; sector?:Sector
}

// ── Proveedores ────────────────────────────────────────────────────────────
export interface Proveedor {
  id:number; codigo:string; nombre:string; nif?:string
  direccion?:string; codigo_postal?:string; localidad?:string; provincia?:string
  telefono?:string; email?:string; persona_contacto?:string
  forma_pago_id?:number; descuento_pct:number; plazo_entrega:number
  iban?:string; activo:boolean; observaciones?:string
  forma_pago?:FormaPago
}

// ── Artículos ──────────────────────────────────────────────────────────────
export interface Articulo {
  id:number; referencia:string; descripcion:string; descripcion2?:string
  familia_id?:number; proveedor_id?:number; tipo_iva_id:number
  unidad_medida:string; ean13?:string
  precio_coste:number; tarifa1:number; tarifa2:number; tarifa3:number; tarifa4:number
  stock_total:number; stock_minimo:number
  es_fabricado:boolean; activo:boolean; observaciones?:string
  familia?:FamiliaArticulo; tipo_iva?:TipoIva; proveedor?:Proveedor
  stock_almacen?:StockAlmacen[]
}

export interface StockAlmacen {
  id:number; articulo_id:number; almacen_id:number
  stock:number; stock_reservado:number; stock_pedido:number
  stock_disponible:number; almacen?:Almacen
}

export interface MovimientoAlmacen {
  id:number; fecha:string; articulo_id:number; almacen_id:number
  tipo_movimiento:string; cantidad:number
  stock_anterior:number; stock_posterior:number; precio_coste:number
  documento_tipo?:string; documento_ref?:string
  observaciones?:string; created_at:string
  articulo?:Articulo; almacen?:Almacen
}

// ── Fabricación ────────────────────────────────────────────────────────────
export interface FormulaComponente {
  id:number; formula_id:number; articulo_id:number
  cantidad:number; unidad?:string; orden:number
  es_opcional:boolean; merma_pct:number; notas?:string
  articulo?:Articulo
}

export interface Formula {
  id:number; codigo:string; nombre:string; articulo_id:number
  cantidad_producida:number
  almacen_salida_id?:number; almacen_entrada_id?:number
  tiempo_fabricacion?:number; activa:boolean; notas?:string
  articulo?:Articulo; componentes?:FormulaComponente[]
  almacen_salida?:Almacen; almacen_entrada?:Almacen
}

export interface CheckStockItem {
  articulo_id:number; referencia:string; descripcion:string
  unidad:string; necesario:number; disponible:number
  suficiente:boolean; diferencia:number
}

export interface OrdenFabricacionDetalle {
  id:number; orden_id:number; articulo_id:number
  tipo:'C'|'P'; cantidad_teorica:number; cantidad_real:number; precio_coste:number
  articulo?:Articulo
}

export interface OrdenFabricacion {
  id:number; numero:string; formula_id:number
  fecha:string; fecha_prevista?:string; fecha_fin?:string
  cantidad_pedida:number; cantidad_fabricada:number
  estado:'borrador'|'confirmada'|'en_proceso'|'completada'|'anulada'
  coste_estimado:number; coste_real:number; notas?:string
  formula?:Formula; detalle?:OrdenFabricacionDetalle[]
  almacen_salida?:Almacen; almacen_entrada?:Almacen
}

// ── Ventas ─────────────────────────────────────────────────────────────────
export type TipoDocumento = 'presupuesto'|'pedido'|'albaran'|'factura'|'rectificativa'
export type EstadoDocumento = 'borrador'|'confirmado'|'enviado'|'facturado'|'cobrado'|'anulado'|'parcial'

export interface VentaLinea {
  id:number; documento_id:number; linea:number; tipo_linea:'A'|'C'|'S'
  articulo_id?:number; descripcion:string
  cantidad:number; precio_unitario:number; precio_con_iva:number
  descuento_pct:number; importe_neto:number
  tipo_iva_id:number; cuota_iva:number; cuota_recargo:number
  almacen_id?:number; articulo?:Articulo; tipo_iva?:TipoIva
}

export interface VentaIvaDetalle {
  id:number; documento_id:number; tipo_iva_id:number
  base_imponible:number; porcentaje_iva:number; cuota_iva:number
  porcentaje_rec:number; cuota_recargo:number; tipo_iva?:TipoIva
}

export interface VentaDocumento {
  id:number; tipo:TipoDocumento; numero:string; serie?:string
  fecha:string; fecha_entrega?:string; fecha_vencimiento?:string
  cliente_id:number; cliente_nombre:string; cliente_nif?:string; cliente_direccion?:string
  vendedor_id?:number; forma_pago_id?:number; almacen_id?:number
  base_imponible:number; cuota_iva:number; cuota_recargo:number
  total_descuento:number; total_factura:number; tipo_cliente:'N'|'R'
  estado:EstadoDocumento; referencia_cliente?:string; notas?:string; pdf_path?:string
  cliente?:Cliente; vendedor?:Vendedor; forma_pago?:FormaPago; almacen?:Almacen
  lineas?:VentaLinea[]; iva_detalle?:VentaIvaDetalle[]
}

// ── Cobros ─────────────────────────────────────────────────────────────────
export interface Cobro {
  id:number; factura_id:number; cliente_id:number; numero:string
  fecha_emision:string; fecha_vencimiento:string
  importe_total:number; importe_cobrado:number; importe_pendiente:number
  tipo_cobro:string; estado:string; vencido?:boolean
  cliente?:Cliente; factura?:VentaDocumento
}

// ── Paginación ─────────────────────────────────────────────────────────────
export interface Paginated<T> {
  data:T[]; current_page:number; last_page:number
  per_page:number; total:number; from:number; to:number
}
