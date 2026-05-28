import { useState } from 'react'
import { useFormulas, useOrdenesFabricacion, useCrearOrden, useEjecutarFabricacion, useAnularOrden, useCreateFormula, useUpdateFormula } from '@/hooks/queries'
import { fabricacionApi } from '@/api'
import { useMaestros } from '@/hooks/queries'
import { PageHeader, Card, DataTable, EstadoBadge, Btn, Field, inputCls, selectCls, Tabs, ConfirmDialog, SearchInput } from '@/components/shared'
import { fmtEur, fmtNum, fmtDate } from '@/utils'
import { Play, Plus, CheckCircle2, XCircle, X, Search, AlertTriangle, Factory } from 'lucide-react'
import { useQuery } from '@tanstack/react-query'
import { useArticulos } from '@/hooks/queries'
import type { Formula, OrdenFabricacion, CheckStockItem } from '@/types'

// ── Modal fabricar ────────────────────────────────────────────────────────
function FabricarModal({ formula, onClose, onDone }: { formula:Formula; onClose:()=>void; onDone:()=>void }) {
  const [lotes, setLotes] = useState(1)
  const ejecutar = useEjecutarFabricacion()
  const crearOrden = useCrearOrden()

  const { data: check, isLoading: checkLoading } = useQuery({
    queryKey: ['verificar-stock', formula.id, lotes],
    queryFn: () => fabricacionApi.verificarStock(formula.id, lotes),
    enabled: lotes > 0,
  })

  const canFab = Array.isArray(check) && check.length > 0 && (check as CheckStockItem[]).every(c => c.suficiente)
  const totalProducido = (formula.cantidad_producida ?? 1) * lotes

  const launch = async () => {
    const orden = await crearOrden.mutateAsync({ formula_id: formula.id, lotes })
    await ejecutar.mutateAsync(orden.id)
    onDone()
    onClose()
  }

  return (
    <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
      <div className="bg-white rounded-[14px] shadow-xl w-full max-w-lg">
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC]">
          <div>
            <h2 className="text-[16px] font-semibold">{formula.nombre}</h2>
            <p className="text-[12px] text-[#6B6860]">Produce: {formula.articulo?.descripcion}</p>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-[6px] hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
        </div>
        <div className="p-6">
          <Field label="Número de lotes a fabricar">
            <input type="number" className={`${inputCls} text-[18px] font-semibold h-12`} min="1" step="1" value={lotes} onChange={e=>setLotes(Math.max(1,Number(e.target.value)))}/>
            <p className="text-[12px] text-[#6B6860] mt-1">Se producirán: <strong>{fmtNum(totalProducido,0)} unidades</strong> de {formula.articulo?.descripcion}</p>
          </Field>

          <div className="bg-[#F7F6F3] rounded-[8px] p-4 mb-4">
            <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider mb-3">Componentes necesarios</div>
            {checkLoading && <div className="text-[13px] text-[#6B6860]">Verificando stock...</div>}
            {(check as CheckStockItem[] ?? []).map(c => (
              <div key={c.articulo_id} className="flex items-center justify-between mb-2 last:mb-0">
                <div>
                  <div className="text-[13px] font-medium">{c.descripcion}</div>
                  <div className="text-[11px] text-[#6B6860]">Necesario: {fmtNum(c.necesario,3)} {c.unidad}</div>
                </div>
                <div className="text-right">
                  <div className={`text-[13px] font-semibold flex items-center gap-1 ${c.suficiente?'text-[#2D6A4F]':'text-red-600'}`}>
                    {c.suficiente ? <CheckCircle2 size={13}/> : <XCircle size={13}/>}
                    {fmtNum(c.disponible,3)} {c.unidad}
                  </div>
                  <div className="text-[11px] text-[#6B6860]">Disponible</div>
                </div>
              </div>
            ))}
          </div>

          {!canFab && Array.isArray(check) && check.length > 0 && (
            <div className="flex items-start gap-2 bg-red-50 border border-red-200 rounded-[7px] p-3 mb-4 text-[13px] text-red-700">
              <AlertTriangle size={15} className="flex-shrink-0 mt-0.5"/>
              Stock insuficiente. Reduce la cantidad o repón los componentes.
            </div>
          )}
        </div>
        <div className="flex gap-2 justify-end px-6 pb-5 border-t border-[#E5E3DC] pt-4">
          <Btn variant="secondary" onClick={onClose}>Cancelar</Btn>
          <Btn disabled={!canFab} loading={crearOrden.isPending||ejecutar.isPending} onClick={launch}>
            <Play size={14}/>Ejecutar fabricación
          </Btn>
        </div>
      </div>
    </div>
  )
}

// ── Modal nueva fórmula ───────────────────────────────────────────────────
function FormulaModal({ editing, onClose }: { editing:Formula|null; onClose:()=>void }) {
  const [form, setForm] = useState<any>(editing ? { ...editing, componentes: editing.componentes?.map(c=>({...c})) ?? [] } : { nombre:'', cantidad_producida:1, activa:true, componentes:[] })
  const [artSearch, setArtSearch] = useState('')
  const { data: artData } = useArticulos({ search: artSearch, per_page:10 })
  const { almacenes } = useMaestros()
  const createMut = useCreateFormula()
  const updateMut = useUpdateFormula(editing?.id ?? 0)

  const set = (k:string,v:any) => setForm((f:any)=>({...f,[k]:v}))
  const addComp = (art:any) => {
    if (form.componentes.find((c:any)=>c.articulo_id===art.id)) return
    setForm((f:any)=>({...f, componentes:[...f.componentes,{articulo_id:art.id,cantidad:1,unidad:art.unidad_medida,orden:f.componentes.length+1,merma_pct:0,_art:art}]}))
    setArtSearch('')
  }
  const updComp = (i:number,k:string,v:any) => setForm((f:any)=>{const cs=[...f.componentes];cs[i]={...cs[i],[k]:v};return{...f,componentes:cs}})
  const remComp = (i:number) => setForm((f:any)=>({...f,componentes:f.componentes.filter((_:any,j:number)=>j!==i)}))

  const save = () => {
    const payload = { ...form, componentes: form.componentes.map(({_art,...c}:any)=>c) }
    const mut = editing ? updateMut : createMut
    ;(mut as any).mutate(payload, { onSuccess: onClose })
  }

  return (
    <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&onClose()}>
      <div className="bg-white rounded-[14px] shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC] sticky top-0 bg-white z-10">
          <h2 className="text-[16px] font-semibold">{editing?'Editar fórmula':'Nueva fórmula de fabricación'}</h2>
          <button onClick={onClose} className="p-1.5 rounded-[6px] hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
        </div>
        <div className="p-6">
          <div className="grid grid-cols-2 gap-3">
            <Field label="Código *"><input className={inputCls} value={form.codigo||''} onChange={e=>set('codigo',e.target.value)} disabled={!!editing}/></Field>
            <Field label="Nombre *"><input className={inputCls} value={form.nombre||''} onChange={e=>set('nombre',e.target.value)}/></Field>
          </div>
          <div className="grid grid-cols-3 gap-3">
            <Field label="Artículo que se fabrica *">
              <div className="relative">
                <input className={inputCls} placeholder="Buscar artículo..." value={form._artSearch||form.articulo?.descripcion||''} onChange={e=>{set('_artSearch',e.target.value);setArtSearch(e.target.value)}}/>
                {artSearch && artData?.data?.length > 0 && (
                  <div className="absolute top-full left-0 right-0 bg-white border border-[#E5E3DC] rounded-[8px] shadow-lg z-10 max-h-[150px] overflow-y-auto">
                    {artData.data.map((a:any)=>(
                      <div key={a.id} onClick={()=>{set('articulo_id',a.id);set('_artSearch',a.descripcion);set('articulo',a);setArtSearch('')}} className="px-3 py-2 text-[13px] cursor-pointer hover:bg-[#F0EFE9] border-b border-[#E5E3DC] last:border-0">
                        <span className="font-mono text-[11px] mr-2">{a.referencia}</span>{a.descripcion}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </Field>
            <Field label="Cantidad producida/lote"><input type="number" className={inputCls} min="0.001" step="any" value={form.cantidad_producida} onChange={e=>set('cantidad_producida',Number(e.target.value))}/></Field>
            <Field label="Tiempo fab. (min)"><input type="number" className={inputCls} min="1" value={form.tiempo_fabricacion||''} onChange={e=>set('tiempo_fabricacion',Number(e.target.value))}/></Field>
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Field label="Almacén consumo (componentes)">
              <select className={selectCls} value={form.almacen_salida_id||''} onChange={e=>set('almacen_salida_id',Number(e.target.value)||null)}>
                <option value="">Por defecto</option>
                {almacenes.data?.map((a:any)=><option key={a.id} value={a.id}>{a.nombre}</option>)}
              </select>
            </Field>
            <Field label="Almacén destino (producto fabricado)">
              <select className={selectCls} value={form.almacen_entrada_id||''} onChange={e=>set('almacen_entrada_id',Number(e.target.value)||null)}>
                <option value="">Por defecto</option>
                {almacenes.data?.map((a:any)=><option key={a.id} value={a.id}>{a.nombre}</option>)}
              </select>
            </Field>
          </div>

          {/* Componentes */}
          <div className="mb-3">
            <div className="text-[13px] font-semibold mb-2">Componentes / Materias primas</div>
            {form.componentes.map((c:any,i:number)=>(
              <div key={i} className="flex items-center gap-2 p-3 bg-[#F7F6F3] rounded-[7px] mb-2 border border-[#E5E3DC]">
                <div className="w-8 h-8 rounded-[6px] bg-[#2D6A4F] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">{i+1}</div>
                <div className="flex-1">
                  <div className="text-[13px] font-medium">{c._art?.descripcion || c.articulo?.descripcion || `Artículo ${c.articulo_id}`}</div>
                  <div className="text-[11px] text-[#6B6860]">Stock: {fmtNum(c._art?.stock_total??c.articulo?.stock_total??0,0)} {c.unidad}</div>
                </div>
                <input type="number" min="0.001" step="any" value={c.cantidad} onChange={e=>updComp(i,'cantidad',Number(e.target.value))} className={`${inputCls} w-24 text-right`}/>
                <span className="text-[12px] text-[#6B6860] w-8">{c.unidad}</span>
                <input type="number" min="0" max="100" step="0.1" value={c.merma_pct||0} onChange={e=>updComp(i,'merma_pct',Number(e.target.value))} className={`${inputCls} w-16 text-right`} title="Merma %"/>
                <span className="text-[11px] text-[#6B6860]">%</span>
                <button onClick={()=>remComp(i)} className="p-1 text-[#6B6860] hover:text-red-600"><X size={13}/></button>
              </div>
            ))}

            <div className="relative mt-2">
              <Search size={13} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#6B6860]"/>
              <input className={`${inputCls} pl-8`} placeholder="Añadir componente — busca artículo..." value={artSearch} onChange={e=>setArtSearch(e.target.value)}/>
              {artSearch && artData?.data?.filter((a:any)=>!form.componentes.find((c:any)=>c.articulo_id===a.id)).length>0 && (
                <div className="absolute top-full left-0 right-0 bg-white border border-[#E5E3DC] rounded-[8px] shadow-lg z-10 max-h-[200px] overflow-y-auto">
                  {artData.data.filter((a:any)=>!form.componentes.find((c:any)=>c.articulo_id===a.id)).map((a:any)=>(
                    <div key={a.id} onClick={()=>addComp(a)} className="px-3 py-2.5 text-[13px] cursor-pointer hover:bg-[#F0EFE9] border-b border-[#E5E3DC] last:border-0 flex justify-between">
                      <span><span className="font-mono text-[11px] mr-2">{a.referencia}</span>{a.descripcion}</span>
                      <span className="text-[#6B6860] text-[12px]">Stock: {fmtNum(a.stock_total,0)} {a.unidad_medida}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
          <Field label="Notas"><textarea className={inputCls} rows={2} value={form.notas||''} onChange={e=>set('notas',e.target.value)}/></Field>
        </div>
        <div className="flex gap-2 justify-end px-6 pb-5 border-t border-[#E5E3DC] pt-4">
          <Btn variant="secondary" onClick={onClose}>Cancelar</Btn>
          <Btn onClick={save} loading={createMut.isPending||updateMut.isPending}>Guardar fórmula</Btn>
        </div>
      </div>
    </div>
  )
}

// ── Main Page ─────────────────────────────────────────────────────────────
export default function FabricacionPage() {
  const [tab, setTab] = useState('fabricar')
  const [fabModal, setFabModal] = useState<Formula|null>(null)
  const [formulaModal, setFormulaModal] = useState<Formula|null|'new'>(null)
  const [confirmAnular, setConfirmAnular] = useState<number|null>(null)

  const { data: formulas, isLoading: loadF } = useFormulas()
  const { data: ordenes, isLoading: loadO } = useOrdenesFabricacion()
  const anularMut = useAnularOrden()

  const ordenCols = [
    { key:'numero', label:'Orden', render:(r:OrdenFabricacion)=><span className="font-mono text-[12px]">{r.numero}</span> },
    { key:'formula', label:'Fórmula', render:(r:OrdenFabricacion)=>r.formula?.nombre },
    { key:'fecha', label:'Fecha', render:(r:OrdenFabricacion)=>fmtDate(r.fecha) },
    { key:'cantidad_pedida', label:'Lotes', align:'right' as const },
    { key:'cantidad_fabricada', label:'Producido', align:'right' as const, render:(r:OrdenFabricacion)=>r.estado==='completada'?<span className="font-semibold text-[#2D6A4F]">{fmtNum(r.cantidad_fabricada,0)} ud</span>:'—' },
    { key:'coste_real', label:'Coste', align:'right' as const, render:(r:OrdenFabricacion)=>fmtEur(r.coste_real) },
    { key:'estado', label:'Estado', render:(r:OrdenFabricacion)=><EstadoBadge estado={r.estado}/> },
    { key:'_', label:'', render:(r:OrdenFabricacion)=>r.estado!=='completada'&&r.estado!=='anulada'?(
      <Btn variant="ghost" size="sm" onClick={()=>setConfirmAnular(r.id)}>Anular</Btn>
    ):null },
  ]

  return (
    <div>
      <PageHeader title="Fabricación" sub="Fórmulas BOM y órdenes de producción" actions={
        <Btn onClick={()=>setFormulaModal('new')}><Plus size={14}/>Nueva fórmula</Btn>
      }/>

      <div className="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-[8px] text-[13px] text-amber-800 flex items-start gap-2">
        <AlertTriangle size={15} className="flex-shrink-0 mt-0.5"/>
        <span><strong>¿Cómo funciona?</strong> Selecciona una fórmula, indica cuántos lotes fabricar. El sistema descuenta los componentes del almacén y suma el producto terminado automáticamente.</span>
      </div>

      <Tabs tabs={[{id:'fabricar',label:'Fabricar ahora'},{id:'formulas',label:'Fórmulas'},{id:'historial',label:'Historial órdenes'}]} active={tab} onChange={setTab}/>

      {/* TAB: Fabricar */}
      {tab==='fabricar' && (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
          {loadF && <div className="col-span-3 py-12 text-center text-[#6B6860] text-[13px]">Cargando fórmulas...</div>}
          {(formulas?.data ?? []).map((f:Formula) => {
            const art = f.articulo
            return (
              <div key={f.id} className="bg-white border border-[#E5E3DC] rounded-[10px] p-4 shadow-sm hover:border-[#2D6A4F] transition-colors cursor-pointer" onClick={()=>setFabModal(f)}>
                <div className="flex items-start justify-between mb-2">
                  <div>
                    <div className="text-[14px] font-semibold">{f.nombre}</div>
                    <div className="text-[12px] text-[#6B6860] mt-0.5">Produce: <strong>{f.cantidad_producida}</strong> × {art?.descripcion}</div>
                  </div>
                  <div className="w-9 h-9 rounded-[8px] bg-[#2D6A4F] text-white flex items-center justify-center flex-shrink-0"><Factory size={16}/></div>
                </div>
                <div className="flex flex-wrap gap-1.5 mt-3">
                  {f.componentes?.map(c=>(
                    <span key={c.id} className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#F0EFE9] text-[#6B6860] border border-[#E5E3DC]">
                      {fmtNum(c.cantidad,3)} {c.unidad} {c.articulo?.descripcion?.split(' ').slice(0,3).join(' ')}
                    </span>
                  ))}
                </div>
                <div className="mt-3 pt-3 border-t border-[#E5E3DC] flex items-center justify-between">
                  <span className="text-[11px] text-[#6B6860]">{f.componentes?.length ?? 0} componentes</span>
                  <Btn size="sm" onClick={e=>{e.stopPropagation();setFabModal(f)}}><Play size={12}/>Fabricar</Btn>
                </div>
              </div>
            )
          })}
        </div>
      )}

      {/* TAB: Fórmulas */}
      {tab==='formulas' && (
        <div>
          {(formulas?.data ?? []).map((f:Formula) => (
            <Card key={f.id} className="mb-3">
              <div className="flex items-start justify-between mb-3">
                <div>
                  <div className="text-[15px] font-semibold">{f.nombre} <span className="font-mono text-[12px] text-[#6B6860]">{f.codigo}</span></div>
                  <div className="text-[12px] text-[#6B6860]">Produce <strong>{f.cantidad_producida}</strong> ud de <strong>{f.articulo?.descripcion}</strong></div>
                </div>
                <Btn variant="secondary" size="sm" onClick={()=>setFormulaModal(f)}><Plus size={12}/>Editar</Btn>
              </div>
              <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2">
                {f.componentes?.map(c=>(
                  <div key={c.id} className="flex items-center gap-2 p-2.5 bg-[#F7F6F3] rounded-[7px] border border-[#E5E3DC]">
                    <div className="w-8 h-8 rounded-[6px] bg-[#2D6A4F] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">{fmtNum(c.cantidad,c.cantidad<1?3:0)}</div>
                    <div>
                      <div className="text-[12px] font-medium leading-tight">{c.articulo?.descripcion?.split(' ').slice(0,4).join(' ')}</div>
                      <div className="text-[10px] text-[#6B6860]">{c.unidad}{c.merma_pct>0?` · merma ${c.merma_pct}%`:''}</div>
                    </div>
                  </div>
                ))}
              </div>
            </Card>
          ))}
        </div>
      )}

      {/* TAB: Historial */}
      {tab==='historial' && (
        <Card noPad>
          <DataTable cols={ordenCols} rows={ordenes?.data??[]} loading={loadO} keyFn={r=>r.id}/>
        </Card>
      )}

      {fabModal && <FabricarModal formula={fabModal} onClose={()=>setFabModal(null)} onDone={()=>setTab('historial')}/>}
      {formulaModal && <FormulaModal editing={formulaModal==='new'?null:formulaModal as Formula} onClose={()=>setFormulaModal(null)}/>}
      <ConfirmDialog open={!!confirmAnular} title="Anular orden" message="¿Anular esta orden de fabricación?" onConfirm={()=>anularMut.mutate({id:confirmAnular!},{onSuccess:()=>setConfirmAnular(null)})} onClose={()=>setConfirmAnular(null)}/>
    </div>
  )
}
