import { useState } from 'react'
import { useStockAlmacen, useMovimientos, useRegistrarMovimiento, useInventario } from '@/hooks/queries'
import { useMaestros } from '@/hooks/queries'
import { useArticulos } from '@/hooks/queries'
import { PageHeader, Card, DataTable, EstadoBadge, Btn, Field, inputCls, selectCls, Tabs, SearchInput, Pagination } from '@/components/shared'
import { fmtEur, fmtNum, fmtDate } from '@/utils'
import { Plus, X, AlertTriangle } from 'lucide-react'

const TIPOS_MOV = ['ENTRADA','SALIDA','AJUSTE_POS','AJUSTE_NEG','DEVOLUCION_CLI','DEVOLUCION_PROV','TRASPASO_IN','TRASPASO_OUT']

export default function AlmacenPage() {
  const [tab, setTab] = useState('stock')
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [almacenId, setAlmacenId] = useState<number|''>('')
  const [modal, setModal] = useState(false)
  const [form, setForm] = useState<any>({ tipo_movimiento:'ENTRADA', cantidad:1, precio_coste:0, observaciones:'' })

  const { data: stock, isLoading: loadS }  = useStockAlmacen({ almacen_id:almacenId||undefined, search, page })
  const { data: movs, isLoading: loadM }   = useMovimientos({ almacen_id:almacenId||undefined, page })
  const { data: inv }                      = useInventario({ almacen_id:almacenId||undefined })
  const { almacenes, articulos: _ } = useMaestros()
  const regMut = useRegistrarMovimiento()

  // articulos inline query for modal
  const [artSearch, setArtSearch] = useState('')
  const { data: artData } = useArticulos({ search: artSearch, per_page:8 })

  const set = (k:string,v:any) => setForm((f:any)=>({...f,[k]:v}))
  const guardar = () => regMut.mutate(form, { onSuccess:()=>{ setModal(false); setForm({ tipo_movimiento:'ENTRADA', cantidad:1, precio_coste:0, observaciones:'' }) }})

  const valorTotal = (inv??[]).reduce((s:number,r:any)=>s+Number(r.valor_coste),0)

  const stockCols = [
    { key:'ref',      label:'Ref.',    render:(r:any)=><span className="font-mono text-[12px]">{r.articulo?.referencia??r.referencia}</span> },
    { key:'desc',     label:'Artículo',render:(r:any)=><span className="font-medium">{r.articulo?.descripcion??r.descripcion}</span> },
    { key:'familia',  label:'Familia', render:(r:any)=>r.articulo?.familia?.nombre??r.familia??'—' },
    { key:'almacen',  label:'Almacén', render:(r:any)=>r.almacen?.nombre??'—' },
    { key:'stock',    label:'Stock', align:'right' as const, render:(r:any)=>{
      const art=r.articulo??r; const bajo=Number(r.stock??r.stock_total)<Number(art.stock_minimo??0)
      return <span className={`font-semibold ${bajo?'text-red-600':Number(r.stock??r.stock_total)===0?'text-[#6B6860]':'text-[#2D6A4F]'}`}>
        {bajo&&<AlertTriangle size={11} className="inline mr-1"/>}{fmtNum(r.stock??r.stock_total,3)}
      </span>
    }},
    { key:'unidad',   label:'Ud.', render:(r:any)=>r.articulo?.unidad_medida??r.unidad_medida??'UD' },
    { key:'valor',    label:'Valor coste', align:'right' as const, render:(r:any)=>fmtEur(Number(r.stock??r.stock_total)*Number(r.articulo?.precio_coste??r.precio_coste??0)) },
  ]

  const movCols = [
    { key:'fecha',     label:'Fecha',    render:(r:any)=>fmtDate(r.fecha) },
    { key:'articulo',  label:'Artículo', render:(r:any)=><span className="font-medium">{r.articulo?.descripcion}</span> },
    { key:'tipo',      label:'Tipo',     render:(r:any)=>{
      const isIn=['ENTRADA','FABRICACION_IN','DEVOLUCION_CLI','AJUSTE_POS','TRASPASO_IN'].includes(r.tipo_movimiento)
      return <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border ${isIn?'bg-green-100 text-green-800 border-green-300':'bg-red-100 text-red-800 border-red-300'}`}>{r.tipo_movimiento.replace('_',' ')}</span>
    }},
    { key:'cantidad',  label:'Cantidad', align:'right' as const, render:(r:any)=>{
      const isIn=r.cantidad>0
      return <span className={`font-semibold font-mono ${isIn?'text-[#2D6A4F]':'text-red-600'}`}>{isIn?'+':''}{fmtNum(r.cantidad,3)}</span>
    }},
    { key:'stock_posterior', label:'Stock tras', align:'right' as const, render:(r:any)=><span className="font-mono text-[12px]">{fmtNum(r.stock_posterior,3)}</span> },
    { key:'almacen',   label:'Almacén',  render:(r:any)=>r.almacen?.nombre??'—' },
    { key:'documento_ref', label:'Ref.',render:(r:any)=><span className="font-mono text-[11px]">{r.documento_ref??'—'}</span> },
    { key:'obs',       label:'Nota',     render:(r:any)=>r.observaciones??'—' },
  ]

  return (
    <div>
      <PageHeader title="Almacén" sub="Control de stock e inventario" actions={
        <div className="flex gap-2">
          <select className={`${selectCls} max-w-[180px]`} value={almacenId} onChange={e=>setAlmacenId(e.target.value?Number(e.target.value):'')}>
            <option value="">Todos los almacenes</option>
            {almacenes.data?.map((a:any)=><option key={a.id} value={a.id}>{a.nombre}</option>)}
          </select>
          <SearchInput value={search} onChange={v=>{setSearch(v);setPage(1)}}/>
          <Btn onClick={()=>setModal(true)}><Plus size={14}/>Nuevo movimiento</Btn>
        </div>
      }/>

      <Tabs tabs={[{id:'stock',label:'Stock actual'},{id:'movimientos',label:'Movimientos'},{id:'inventario',label:'Inventario valorado'}]} active={tab} onChange={setTab}/>

      {tab==='stock' && (
        <Card noPad>
          <DataTable cols={stockCols} rows={stock?.data??[]} loading={loadS} keyFn={r=>r.id}/>
        </Card>
      )}

      {tab==='movimientos' && (
        <Card noPad>
          <DataTable cols={movCols} rows={movs?.data??[]} loading={loadM} keyFn={r=>r.id}/>
        </Card>
      )}

      {tab==='inventario' && (
        <div>
          <div className="grid grid-cols-3 gap-3 mb-4">
            <div className="bg-white border border-[#E5E3DC] rounded-[10px] p-4">
              <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider">Valor total stock (coste)</div>
              <div className="text-[24px] font-semibold mt-1 text-[#2D6A4F]">{fmtEur(valorTotal)}</div>
            </div>
            <div className="bg-white border border-[#E5E3DC] rounded-[10px] p-4">
              <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider">Referencias con stock</div>
              <div className="text-[24px] font-semibold mt-1">{(inv??[]).filter((r:any)=>Number(r.stock)>0).length}</div>
            </div>
            <div className="bg-white border border-[#E5E3DC] rounded-[10px] p-4">
              <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider">Referencias sin stock</div>
              <div className="text-[24px] font-semibold mt-1 text-amber-600">{(inv??[]).filter((r:any)=>Number(r.stock)<=0).length}</div>
            </div>
          </div>
          <Card noPad>
            <table className="w-full border-collapse">
              <thead><tr>
                {['Ref.','Descripción','Familia','Ud.','Stock','Mín.','Valor coste','Valor PVP'].map(h=>(
                  <th key={h} className="text-left text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider px-3 py-2.5 border-b-2 border-[#E5E3DC]">{h}</th>
                ))}
              </tr></thead>
              <tbody>
                {(inv??[]).map((r:any,i:number)=>(
                  <tr key={i} className="border-b border-[#E5E3DC] last:border-0 hover:bg-[#F0EFE9]">
                    <td className="px-3 py-2 font-mono text-[12px]">{r.referencia}</td>
                    <td className="px-3 py-2 font-medium text-[13px]">{r.descripcion}</td>
                    <td className="px-3 py-2 text-[12px] text-[#6B6860]">{r.familia??'—'}</td>
                    <td className="px-3 py-2 text-[12px]">{r.unidad_medida}</td>
                    <td className="px-3 py-2 text-right font-semibold font-mono text-[12px]">{fmtNum(r.stock,3)}</td>
                    <td className="px-3 py-2 text-right text-[12px] text-[#6B6860] font-mono">{fmtNum(r.stock_minimo,0)}</td>
                    <td className="px-3 py-2 text-right font-mono text-[12px]">{fmtEur(r.valor_coste)}</td>
                    <td className="px-3 py-2 text-right font-mono text-[12px] text-[#6B6860]">{fmtEur(r.valor_pvp)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Card>
        </div>
      )}

      {/* Modal movimiento */}
      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&setModal(false)}>
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-sm">
            <div className="flex items-center justify-between px-5 py-4 border-b border-[#E5E3DC]">
              <h3 className="text-[16px] font-semibold">Nuevo movimiento</h3>
              <button onClick={()=>setModal(false)} className="p-1.5 rounded hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-5 space-y-3">
              <Field label="Artículo *">
                <div className="relative">
                  <input className={inputCls} placeholder="Buscar artículo..." value={artSearch} onChange={e=>setArtSearch(e.target.value)}/>
                  {artSearch && artData?.data?.length>0 && (
                    <div className="absolute top-full left-0 right-0 bg-white border border-[#E5E3DC] rounded-[8px] shadow-lg z-10 max-h-[150px] overflow-y-auto">
                      {artData.data.map((a:any)=>(
                        <div key={a.id} onClick={()=>{set('articulo_id',a.id);setArtSearch(a.descripcion)}} className="px-3 py-2 text-[13px] cursor-pointer hover:bg-[#F0EFE9] border-b border-[#E5E3DC] last:border-0">{a.referencia} — {a.descripcion}</div>
                      ))}
                    </div>
                  )}
                </div>
              </Field>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Almacén *">
                  <select className={selectCls} value={form.almacen_id||''} onChange={e=>set('almacen_id',Number(e.target.value))}>
                    <option value="">Seleccionar...</option>
                    {almacenes.data?.map((a:any)=><option key={a.id} value={a.id}>{a.nombre}</option>)}
                  </select>
                </Field>
                <Field label="Tipo *">
                  <select className={selectCls} value={form.tipo_movimiento} onChange={e=>set('tipo_movimiento',e.target.value)}>
                    {TIPOS_MOV.map(t=><option key={t} value={t}>{t.replace('_',' ')}</option>)}
                  </select>
                </Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Cantidad *"><input type="number" className={inputCls} min="0.001" step="any" value={form.cantidad} onChange={e=>set('cantidad',Number(e.target.value))}/></Field>
                <Field label="Precio coste"><input type="number" className={inputCls} min="0" step="0.0001" value={form.precio_coste} onChange={e=>set('precio_coste',Number(e.target.value))}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Referencia documento"><input className={inputCls} value={form.documento_ref||''} onChange={e=>set('documento_ref',e.target.value)} placeholder="Nº factura, albarán..."/></Field>
                <Field label="Lote"><input className={inputCls} value={form.lote||''} onChange={e=>set('lote',e.target.value)}/></Field>
              </div>
              <Field label="Observaciones"><input className={inputCls} value={form.observaciones||''} onChange={e=>set('observaciones',e.target.value)}/></Field>
            </div>
            <div className="flex gap-2 justify-end px-5 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(false)}>Cancelar</Btn>
              <Btn onClick={guardar} loading={regMut.isPending} disabled={!form.articulo_id||!form.almacen_id}>Guardar</Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
