import { useState, useMemo } from 'react'
import { useVentas, useCreateVenta, useAnularVenta } from '@/hooks/queries'
import { useMaestros, useClientes, useArticulos } from '@/hooks/queries'
import { PageHeader, SearchInput, Card, DataTable, EstadoBadge, Btn, Field, inputCls, selectCls, Tabs, ConfirmDialog, Pagination } from '@/components/shared'
import { fmtEur, fmtNum, fmtDate, tipoDocLabel } from '@/utils'
import { Plus, X, Trash2, Search, FileText, Eye } from 'lucide-react'
import { ventasApi } from '@/api'
import type { VentaDocumento, VentaLinea } from '@/types'

type Tipo = 'factura'|'albaran'|'pedido'|'presupuesto'

// ── Modal nueva venta ─────────────────────────────────────────────────────
function NuevaVentaModal({ tipo, onClose }: { tipo:Tipo; onClose:()=>void }) {
  const [clienteId, setClienteId] = useState<number|''>('')
  const [fecha, setFecha] = useState(new Date().toISOString().split('T')[0])
  const [formaPagoId, setFormaPagoId] = useState<number|''>('')
  const [vendedorId, setVendedorId] = useState<number|''>('')
  const [lineas, setLineas] = useState<any[]>([])
  const [artSearch, setArtSearch] = useState('')
  const [notas, setNotas] = useState('')
  const [refCli, setRefCli] = useState('')

  const { data: clientes } = useClientes({ per_page:100 })
  const { tiposIva, formasPago, vendedores, almacenes } = useMaestros()
  const { data: artData } = useArticulos({ search: artSearch, per_page:8 })
  const createMut = useCreateVenta()

  const cliente = clientes?.data?.find((c:any)=>c.id===clienteId)

  const addLinea = (art:any) => {
    const tarifa = cliente ? `tarifa${cliente.tarifa}` : 'tarifa1'
    const precio = art[tarifa] ?? art.tarifa1 ?? 0
    setLineas(ls=>[...ls,{ articulo_id:art.id, descripcion:art.descripcion, cantidad:1, precio_unitario:precio, descuento_pct:cliente?.descuento_pct??0, tipo_iva_id:art.tipo_iva_id, _art:art }])
    setArtSearch('')
  }
  const updLinea = (i:number,k:string,v:any) => setLineas(ls=>{const n=[...ls];n[i]={...n[i],[k]:v};return n})
  const remLinea = (i:number) => setLineas(ls=>ls.filter((_,j)=>j!==i))

  // Calcular totales
  const totales = useMemo(()=>{
    let base=0,iva=0,rec=0,dto=0
    for(const l of lineas){
      const bruto=l.cantidad*l.precio_unitario
      const d=bruto*(l.descuento_pct??0)/100
      const neto=bruto-d
      const tiva=tiposIva.data?.find((t:any)=>t.id===l.tipo_iva_id)
      const pIva=tiva?.porcentaje_iva??21
      const pRec=cliente?.tipo_cliente==='R'?(tiva?.porcentaje_rec??0):0
      base+=neto; iva+=neto*pIva/100; rec+=neto*pRec/100; dto+=d
    }
    return { base:Math.round(base*100)/100, iva:Math.round(iva*100)/100, rec:Math.round(rec*100)/100, dto:Math.round(dto*100)/100, total:Math.round((base+iva+rec)*100)/100 }
  },[lineas,tiposIva.data,cliente])

  const emit = () => {
    createMut.mutate({
      tipo, cliente_id:clienteId, fecha, forma_pago_id:formaPagoId||undefined,
      vendedor_id:vendedorId||undefined, notas, referencia_cliente:refCli,
      lineas: lineas.map(({_art,...l})=>l),
    },{ onSuccess:onClose })
  }

  return (
    <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&onClose()}>
      <div className="bg-white rounded-[14px] shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC] sticky top-0 bg-white z-10">
          <h2 className="text-[16px] font-semibold">Nueva {tipoDocLabel[tipo]}</h2>
          <button onClick={onClose} className="p-1.5 rounded-[6px] hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
        </div>
        <div className="p-6">
          {/* Cabecera */}
          <div className="grid grid-cols-3 gap-3 mb-4">
            <Field label="Cliente *">
              <select className={selectCls} value={clienteId} onChange={e=>setClienteId(Number(e.target.value))}>
                <option value="">Seleccionar...</option>
                {clientes?.data?.map((c:any)=><option key={c.id} value={c.id}>{c.nombre}</option>)}
              </select>
            </Field>
            <Field label="Fecha"><input type="date" className={inputCls} value={fecha} onChange={e=>setFecha(e.target.value)}/></Field>
            <Field label="Forma de pago">
              <select className={selectCls} value={formaPagoId} onChange={e=>setFormaPagoId(Number(e.target.value))}>
                <option value="">Sin especificar</option>
                {formasPago.data?.map((fp:any)=><option key={fp.id} value={fp.id}>{fp.nombre}</option>)}
              </select>
            </Field>
          </div>
          <div className="grid grid-cols-3 gap-3 mb-4">
            <Field label="Vendedor">
              <select className={selectCls} value={vendedorId} onChange={e=>setVendedorId(Number(e.target.value))}>
                <option value="">Sin asignar</option>
                {vendedores.data?.map((v:any)=><option key={v.id} value={v.id}>{v.nombre}</option>)}
              </select>
            </Field>
            <Field label="Ref. cliente"><input className={inputCls} value={refCli} onChange={e=>setRefCli(e.target.value)} placeholder="Nº pedido cliente"/></Field>
            <div/>
          </div>

          {cliente?.tipo_cliente==='R' && (
            <div className="mb-3 px-3 py-2 bg-orange-50 border border-orange-200 rounded-[7px] text-[12px] text-orange-800 font-medium">
              ⚠ Cliente sujeto a Recargo de Equivalencia (5.2%) — se calculará automáticamente
            </div>
          )}

          {/* Buscador artículos */}
          <div className="relative mb-3">
            <Search size={13} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#6B6860]"/>
            <input className={`${inputCls} pl-8`} placeholder="Añadir artículo — busca por referencia o descripción..." value={artSearch} onChange={e=>setArtSearch(e.target.value)} disabled={!clienteId}/>
            {artSearch && artData?.data?.length>0 && (
              <div className="absolute top-full left-0 right-0 bg-white border border-[#E5E3DC] rounded-[8px] shadow-lg z-10 max-h-[200px] overflow-y-auto">
                {artData.data.map((a:any)=>(
                  <div key={a.id} onClick={()=>addLinea(a)} className="px-3 py-2.5 cursor-pointer hover:bg-[#F0EFE9] border-b border-[#E5E3DC] last:border-0 flex justify-between text-[13px]">
                    <span><span className="font-mono text-[11px] mr-2">{a.referencia}</span>{a.descripcion}</span>
                    <span className="text-[#6B6860]">{fmtEur(a[`tarifa${cliente?.tarifa??1}`]??a.tarifa1)} · Stock: {fmtNum(a.stock_total,0)}</span>
                  </div>
                ))}
              </div>
            )}
            {!clienteId && <p className="text-[11px] text-[#6B6860] mt-1">Selecciona un cliente primero</p>}
          </div>

          {/* Líneas */}
          {lineas.length===0
            ? <div className="py-10 text-center bg-[#F7F6F3] rounded-[8px] text-[#6B6860] text-[13px]"><FileText size={28} className="mx-auto mb-2 opacity-30"/>Añade artículos buscando arriba</div>
            : <table className="w-full border-collapse mb-4">
              <thead><tr>
                <th className="text-left text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC]">Artículo</th>
                <th className="text-left text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC]">Descripción</th>
                <th className="text-right text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC] w-24">Cant.</th>
                <th className="text-right text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC] w-28">Precio</th>
                <th className="text-right text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC] w-20">Dto %</th>
                <th className="text-right text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-2 py-2 border-b-2 border-[#E5E3DC] w-28">Importe</th>
                <th className="w-8"/>
              </tr></thead>
              <tbody>
                {lineas.map((l,i)=>{
                  const bruto=l.cantidad*l.precio_unitario
                  const neto=bruto-bruto*(l.descuento_pct??0)/100
                  return (
                    <tr key={i} className="border-b border-[#E5E3DC] last:border-0">
                      <td className="px-2 py-1.5"><span className="font-mono text-[11px]">{l._art?.referencia??l.articulo_id}</span></td>
                      <td className="px-2 py-1.5"><input value={l.descripcion} onChange={e=>updLinea(i,'descripcion',e.target.value)} className="border-0 bg-transparent text-[13px] w-full outline-none"/></td>
                      <td className="px-2 py-1.5"><input type="number" min="0.001" step="any" value={l.cantidad} onChange={e=>updLinea(i,'cantidad',Number(e.target.value))} className={`${inputCls} text-right`}/></td>
                      <td className="px-2 py-1.5"><input type="number" min="0" step="0.0001" value={l.precio_unitario} onChange={e=>updLinea(i,'precio_unitario',Number(e.target.value))} className={`${inputCls} text-right font-mono text-[12px]`}/></td>
                      <td className="px-2 py-1.5"><input type="number" min="0" max="100" step="0.5" value={l.descuento_pct??0} onChange={e=>updLinea(i,'descuento_pct',Number(e.target.value))} className={`${inputCls} text-right`}/></td>
                      <td className="px-2 py-1.5 text-right font-semibold text-[13px]">{fmtEur(neto)}</td>
                      <td className="px-2 py-1.5"><button onClick={()=>remLinea(i)} className="p-1 text-[#6B6860] hover:text-red-600"><Trash2 size={13}/></button></td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          }

          <Field label="Notas"><textarea className={inputCls} rows={2} value={notas} onChange={e=>setNotas(e.target.value)} placeholder="Observaciones, instrucciones de entrega..."/></Field>
        </div>

        <div className="flex items-center justify-between px-6 pb-5 border-t border-[#E5E3DC] pt-4">
          <div className="font-mono text-[13px] text-[#6B6860]">
            Base: {fmtEur(totales.base)} · IVA: {fmtEur(totales.iva)}
            {totales.rec>0&&<> · RE: {fmtEur(totales.rec)}</>}
            {' '}<strong className="text-[#1A1916] text-[14px]">TOTAL: {fmtEur(totales.total)}</strong>
          </div>
          <div className="flex gap-2">
            <Btn variant="secondary" onClick={onClose}>Cancelar</Btn>
            <Btn disabled={!clienteId||lineas.length===0} loading={createMut.isPending} onClick={emit}>
              Emitir {tipoDocLabel[tipo]}
            </Btn>
          </div>
        </div>
      </div>
    </div>
  )
}

// ── Main ──────────────────────────────────────────────────────────────────
export default function VentasPage() {
  const [tab, setTab] = useState<Tipo>('factura')
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [modal, setModal] = useState(false)
  const [pdfId, setPdfId] = useState<number|null>(null)
  const [anularId, setAnularId] = useState<number|null>(null)

  const { data, isLoading } = useVentas({ tipo:tab, search, page, per_page:25 })
  const anularMut = useAnularVenta()

  const cols = [
    { key:'numero', label:'Número', render:(r:VentaDocumento)=><span className="font-mono text-[12px]">{r.numero}</span> },
    { key:'fecha', label:'Fecha', render:(r:VentaDocumento)=>fmtDate(r.fecha) },
    { key:'cliente_nombre', label:'Cliente', render:(r:VentaDocumento)=><span className="font-medium">{r.cliente_nombre}</span> },
    { key:'vendedor', label:'Vendedor', render:(r:VentaDocumento)=>r.vendedor?.nombre?.split(' ')[0]??'—' },
    { key:'base_imponible', label:'Base imp.', align:'right' as const, render:(r:VentaDocumento)=>fmtEur(r.base_imponible) },
    { key:'cuota_iva', label:'IVA', align:'right' as const, render:(r:VentaDocumento)=>fmtEur(r.cuota_iva) },
    { key:'total_factura', label:'Total', align:'right' as const, render:(r:VentaDocumento)=><span className="font-semibold text-[14px]">{fmtEur(r.total_factura)}</span> },
    { key:'estado', label:'Estado', render:(r:VentaDocumento)=><EstadoBadge estado={r.estado}/> },
    { key:'_', label:'', render:(r:VentaDocumento)=>(
      <div className="flex gap-1">
        {r.tipo==='factura'&&<button onClick={e=>{e.stopPropagation();window.open(ventasApi.pdfUrl(r.id),'_blank')}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860]" title="Ver PDF"><Eye size={13}/></button>}
        {r.estado!=='anulado'&&<button onClick={e=>{e.stopPropagation();setAnularId(r.id)}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860]" title="Anular"><X size={13}/></button>}
      </div>
    )},
  ]

  const TABS = [
    {id:'factura',label:'Facturas'},{id:'albaran',label:'Albaranes'},
    {id:'pedido',label:'Pedidos'},{id:'presupuesto',label:'Presupuestos'},
  ]

  return (
    <div>
      <PageHeader title="Ventas y Facturación" sub={`${data?.total??0} documentos`} actions={
        <div className="flex gap-2">
          <SearchInput value={search} onChange={v=>{setSearch(v);setPage(1)}} placeholder="Nº, cliente, referencia..."/>
          <Btn onClick={()=>setModal(true)}><Plus size={14}/>Nueva {tipoDocLabel[tab]}</Btn>
        </div>
      }/>

      <Tabs tabs={TABS} active={tab} onChange={v=>{setTab(v as Tipo);setPage(1)}}/>

      <Card noPad>
        <DataTable cols={cols} rows={data?.data??[]} loading={isLoading} keyFn={r=>r.id}/>
      </Card>
      <Pagination page={page} lastPage={data?.last_page??1} onChange={setPage}/>

      {modal && <NuevaVentaModal tipo={tab} onClose={()=>setModal(false)}/>}
      <ConfirmDialog open={!!anularId} title="Anular documento" message="¿Anular este documento? El stock será revertido y los cobros asociados anulados."
        onConfirm={()=>anularMut.mutate(anularId!,{onSuccess:()=>setAnularId(null)})}
        onClose={()=>setAnularId(null)}/>
    </div>
  )
}
