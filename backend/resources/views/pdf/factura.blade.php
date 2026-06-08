<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1a1916; background:#fff; }
.page { padding:28px 32px; max-width:794px; }

/* Header */
.header { display:table; width:100%; margin-bottom:20px; }
.header-left { display:table-cell; width:55%; vertical-align:top; }
.header-right { display:table-cell; width:45%; vertical-align:top; text-align:right; }
.company-name { font-size:18px; font-weight:700; color:#1a1916; letter-spacing:-0.3px; }
.company-sub { font-size:10px; color:#6b6860; margin-top:2px; line-height:1.5; }
.doc-tipo { font-size:11px; font-weight:700; color:#6b6860; text-transform:uppercase; letter-spacing:1px; }
.doc-numero { font-size:22px; font-weight:700; color:#2d6a4f; font-family:DejaVu Sans Mono, monospace; }
.doc-meta { font-size:10px; color:#6b6860; margin-top:4px; line-height:1.6; }

/* Parties */
.parties { display:table; width:100%; margin-bottom:18px; background:#f7f6f3; border-radius:6px; }
.party { display:table-cell; width:50%; padding:12px 14px; vertical-align:top; }
.party-label { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#6b6860; margin-bottom:5px; }
.party-name { font-size:13px; font-weight:700; color:#1a1916; }
.party-sub { font-size:10px; color:#3a3834; line-height:1.6; margin-top:2px; }
.party-divider { display:table-cell; width:1px; background:#e5e3dc; }
.badge-rec { display:inline-block; background:#fff0e5; color:#7d3500; border:1px solid #ffd8b0; border-radius:3px; font-size:9px; font-weight:700; padding:1px 6px; margin-top:4px; }

/* Líneas */
table.lineas { width:100%; border-collapse:collapse; margin-bottom:16px; }
table.lineas thead tr { background:#2d6a4f; }
table.lineas thead th { color:#fff; padding:7px 9px; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; text-align:left; }
table.lineas thead th.r { text-align:right; }
table.lineas tbody tr:nth-child(even) td { background:#f7f6f3; }
table.lineas tbody td { padding:8px 9px; border-bottom:1px solid #e5e3dc; font-size:10px; color:#1a1916; }
table.lineas tbody td.r { text-align:right; }
table.lineas tbody td.mono { font-family:DejaVu Sans Mono, monospace; font-size:9px; }
table.lineas tbody td.bold { font-weight:700; }

/* Totales */
.totales-wrap { display:table; width:100%; margin-bottom:18px; }
.totales-spacer { display:table-cell; width:55%; }
.totales-block { display:table-cell; width:45%; }
table.totales { width:100%; border-collapse:collapse; }
table.totales td { padding:4px 9px; font-size:10px; }
table.totales td.label { color:#6b6860; }
table.totales td.value { text-align:right; font-family:DejaVu Sans Mono, monospace; }
table.totales tr.total-final td { font-size:13px; font-weight:700; border-top:2px solid #1a1916; padding-top:7px; }
table.totales tr.subtotal td { border-top:1px solid #e5e3dc; }

/* IVA detalle */
table.iva { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:9px; }
table.iva thead th { background:#f0efe9; padding:5px 8px; text-align:right; font-weight:700; color:#6b6860; text-transform:uppercase; }
table.iva thead th:first-child { text-align:left; }
table.iva tbody td { padding:4px 8px; border-bottom:1px solid #e5e3dc; text-align:right; }
table.iva tbody td:first-child { text-align:left; }

/* Notas */
.notas { background:#fffbf0; border-left:3px solid #f59e0b; padding:8px 12px; font-size:10px; color:#664d03; margin-bottom:14px; }

/* Footer */
.footer { border-top:1px solid #e5e3dc; padding-top:10px; font-size:9px; color:#6b6860; line-height:1.6; }
.footer-grid { display:table; width:100%; }
.footer-col { display:table-cell; width:50%; vertical-align:top; }

/* Separador */
.sep { height:1px; background:#e5e3dc; margin:14px 0; }
</style>
</head>
<body>
<div class="page">

  {{-- CABECERA --}}
  <div class="header">
    <div class="header-left">
      @if($empresa->logo_path && file_exists(storage_path('app/public/'.$empresa->logo_path)))
        <img src="{{ storage_path('app/public/'.$empresa->logo_path) }}" style="height:48px;margin-bottom:6px;display:block;">
      @endif
      <div class="company-name">{{ $empresa->nombre }}</div>
      <div class="company-sub">
        {{ $empresa->nif }}<br>
        {{ $empresa->direccion }}<br>
        {{ $empresa->codigo_postal }} {{ $empresa->localidad }}, {{ $empresa->provincia }}<br>
        @if($empresa->telefono) {{ $empresa->telefono }} · @endif{{ $empresa->email }}
      </div>
    </div>
    <div class="header-right">
      <div class="doc-tipo">
        @switch($doc->tipo)
          @case('factura') FACTURA @break
          @case('rectificativa') FACTURA RECTIFICATIVA @break
          @case('albaran') ALBARÁN @break
          @case('presupuesto') PRESUPUESTO @break
          @case('pedido') PEDIDO @break
        @endswitch
      </div>
      <div class="doc-numero">{{ $doc->numero }}</div>
      <div class="doc-meta">
        Fecha: <strong>{{ \Carbon\Carbon::parse($doc->fecha)->format('d/m/Y') }}</strong><br>
        @if($doc->fecha_vencimiento)
          Vencimiento: <strong>{{ \Carbon\Carbon::parse($doc->fecha_vencimiento)->format('d/m/Y') }}</strong><br>
        @endif
        @if($doc->referencia_cliente)
          Su referencia: <strong>{{ $doc->referencia_cliente }}</strong><br>
        @endif
        @if($doc->vendedor)
          Comercial: {{ $doc->vendedor->nombre }}<br>
        @endif
        Forma de pago: {{ $doc->formaPago?->nombre ?? '—' }}
      </div>
    </div>
  </div>

  {{-- PARTES --}}
  <div class="parties">
    <div class="party">
      <div class="party-label">Emisor</div>
      <div class="party-name">{{ $empresa->nombre }}</div>
      <div class="party-sub">
        {{ $empresa->nif }}<br>
        {{ $empresa->direccion }}<br>
        {{ $empresa->codigo_postal }} {{ $empresa->localidad }}
      </div>
    </div>
    <div class="party-divider"></div>
    <div class="party">
      <div class="party-label">Cliente</div>
      <div class="party-name">{{ $doc->cliente_nombre }}</div>
      <div class="party-sub">
        {{ $doc->cliente_nif }}<br>
        {!! nl2br(e($doc->cliente_direccion)) !!}
      </div>
      @if($doc->tipo_cliente === 'R')
        <div class="badge-rec">✓ RECARGO DE EQUIVALENCIA</div>
      @endif
    </div>
  </div>

  {{-- LÍNEAS --}}
  <table class="lineas">
    <thead>
      <tr>
        <th style="width:12%">Referencia</th>
        <th style="width:38%">Descripción</th>
        <th class="r" style="width:9%">Cant.</th>
        <th class="r" style="width:13%">Precio</th>
        <th class="r" style="width:8%">Dto.</th>
        <th class="r" style="width:11%">IVA</th>
        <th class="r" style="width:9%">Importe</th>
      </tr>
    </thead>
    <tbody>
      @foreach($doc->lineas as $linea)
        @if($linea->tipo_linea === 'C')
          <tr><td colspan="7" style="font-style:italic;color:#6b6860;padding:5px 9px;">{{ $linea->descripcion }}</td></tr>
        @else
          <tr>
            <td class="mono">{{ $linea->articulo?->referencia ?? '—' }}</td>
            <td class="bold">{{ $linea->descripcion }}</td>
            <td class="r">{{ number_format($linea->cantidad, $linea->cantidad == floor($linea->cantidad) ? 0 : 3, ',', '.') }}</td>
            <td class="r mono">{{ number_format($linea->precio_unitario, 4, ',', '.') }} €</td>
            <td class="r">{{ $linea->descuento_pct > 0 ? number_format($linea->descuento_pct,1,',','.').'%' : '—' }}</td>
            <td class="r">{{ number_format($linea->tipoIva?->porcentaje_iva ?? 0,0,',','.').'%' }}</td>
            <td class="r bold">{{ number_format($linea->importe_neto,2,',','.') }} €</td>
          </tr>
        @endif
      @endforeach
    </tbody>
  </table>

  {{-- DESGLOSE IVA + TOTALES --}}
  <div class="totales-wrap">
    <div class="totales-spacer">
      @if($doc->ivaDetalle->count())
      <div style="font-size:9px;font-weight:700;color:#6b6860;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Desglose impositivo</div>
      <table class="iva">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Base</th>
            <th>% IVA</th>
            <th>Cuota IVA</th>
            @if($doc->tipo_cliente==='R')<th>% Rec.</th><th>Cuota Rec.</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach($doc->ivaDetalle as $d)
          <tr>
            <td>{{ $d->tipoIva?->descripcion }}</td>
            <td>{{ number_format($d->base_imponible,2,',','.') }} €</td>
            <td>{{ number_format($d->porcentaje_iva,0,',','.').'%' }}</td>
            <td>{{ number_format($d->cuota_iva,2,',','.') }} €</td>
            @if($doc->tipo_cliente==='R')
              <td>{{ number_format($d->porcentaje_rec,1,',','.').'%' }}</td>
              <td>{{ number_format($d->cuota_recargo,2,',','.') }} €</td>
            @endif
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
    <div class="totales-block">
      <table class="totales">
        <tr>
          <td class="label">Base imponible</td>
          <td class="value">{{ number_format($doc->base_imponible,2,',','.') }} €</td>
        </tr>
        <tr>
          <td class="label">Cuota IVA</td>
          <td class="value">{{ number_format($doc->cuota_iva,2,',','.') }} €</td>
        </tr>
        @if($doc->cuota_recargo > 0)
        <tr>
          <td class="label">Recargo equivalencia</td>
          <td class="value">{{ number_format($doc->cuota_recargo,2,',','.') }} €</td>
        </tr>
        @endif
        @if($doc->total_descuento > 0)
        <tr class="subtotal">
          <td class="label">Descuentos aplicados</td>
          <td class="value" style="color:#c0392b;">-{{ number_format($doc->total_descuento,2,',','.') }} €</td>
        </tr>
        @endif
        <tr class="total-final">
          <td class="label">TOTAL FACTURA</td>
          <td class="value">{{ number_format($doc->total_factura,2,',','.') }} €</td>
        </tr>
      </table>
    </div>
  </div>

  {{-- NOTAS --}}
  @if($doc->notas)
  <div class="notas"><strong>Observaciones:</strong> {{ $doc->notas }}</div>
  @endif

  {{-- DATOS PAGO --}}
  @if($doc->tipo === 'factura' && $empresa->iban)
  <div style="background:#f0faf4;border:1px solid #b7e4c7;border-radius:5px;padding:8px 12px;font-size:10px;margin-bottom:14px;">
    <strong>Datos para el pago:</strong> {{ $empresa->iban }}
    @if($empresa->swift) · SWIFT/BIC: {{ $empresa->swift }}@endif
    — Concepto: {{ $doc->numero }}
  </div>
  @endif

  {{-- PIE --}}
  <div class="footer">
    <div class="footer-grid">
      <div class="footer-col">
        {{ $empresa->nombre }} · CIF {{ $empresa->nif }}<br>
        {{ $empresa->direccion }} · {{ $empresa->codigo_postal }} {{ $empresa->localidad }}
      </div>
      <div class="footer-col" style="text-align:right;">
        @if($empresa->web) {{ $empresa->web }}<br> @endif
        {{ $empresa->email }}
        @if($empresa->telefono) · {{ $empresa->telefono }} @endif
      </div>
    </div>
    @if($empresa->pie_factura)
    <div style="margin-top:6px;border-top:1px solid #e5e3dc;padding-top:6px;font-size:8.5px;color:#8a8880;">
      {{ $empresa->pie_factura }}
    </div>
    @endif
  </div>

</div>
</body>
</html>
