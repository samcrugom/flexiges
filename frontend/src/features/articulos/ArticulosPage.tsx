import { useState } from 'react'
import { useArticulos, useCreateArticulo, useUpdateArticulo } from '@/hooks/queries'
import { useMaestros } from '@/hooks/queries'
import { PageHeader, SearchInput, Card, DataTable, EstadoBadge, Btn, Field, inputCls, selectCls, Pagination } from '@/components/shared'
import { fmtEur, fmtNum } from '@/utils'
import { Plus, Edit2, X, AlertTriangle } from 'lucide-react'
import type { Articulo } from '@/types'

const UNIDADES = ['UD','KG','MT','LT','CJ','BL','ML','GR']
const emptyArt = { tipo_iva_id:1, unidad_medida:'UD', activo:true, es_fabricado:false, precio_coste:0, tarifa1:0, tarifa2:0, tarifa3:0, tarifa4:0, stock_minimo:0 }

export default function ArticulosPage() {
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [familiaId, setFamiliaId] = useState<number|''>('')
  const [modal, setModal] = useState(false)
  const [editing, setEditing] = useState<Articulo|null>(null)
  const [form, setForm] = useState<any>(emptyArt)

  const { data, isLoading } = useArticulos({ search, page, per_page:30, familia_id:familiaId||undefined })
  const { tiposIva, familias, proveedores } = useMaestros()
  const createMut = useCreateArticulo()
  const updateMut = useUpdateArticulo(editing?.id ?? 0)

  const openNew  = () => { setForm(emptyArt); setEditing(null); setModal(true) }
  const openEdit = (a:Articulo) => { setForm({...a}); setEditing(a); setModal(true) }
  const set = (k:string,v:any) => setForm((f:any)=>({...f,[k]:v}))
  const save = () => {
    const mut = editing ? updateMut : createMut
    ;(mut as any).mutate(form, { onSuccess: ()=>setModal(false) })
  }

  const cols = [
    { key:'referencia', label:'Ref.', render:(r:Articulo)=><span className="font-mono text-[12px]">{r.referencia}</span> },
    { key:'descripcion', label:'Descripción', render:(r:Articulo)=><span className="font-medium">{r.descripcion}</span> },
    { key:'familia', label:'Familia', render:(r:Articulo)=>r.familia ? <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 border border-blue-300">{r.familia.nombre}</span> : '—' },
    { key:'unidad_medida', label:'Ud.' },
    { key:'stock_total', label:'Stock', align:'right' as const, render:(r:Articulo)=>{
      const bajo = Number(r.stock_total) < Number(r.stock_minimo)
      return <span className={`font-semibold ${bajo?'text-red-600':Number(r.stock_total)===0?'text-[#6B6860]':'text-[#2D6A4F]'}`}>
        {bajo && <AlertTriangle size={11} className="inline mr-1"/>}{fmtNum(r.stock_total, r.unidad_medida==='KG'?3:0)}
      </span>
    }},
    { key:'tarifa1', label:'T1', align:'right' as const, render:(r:Articulo)=><span className="font-mono text-[12px]">{fmtEur(r.tarifa1)}</span> },
    { key:'tarifa2', label:'T2', align:'right' as const, render:(r:Articulo)=><span className="font-mono text-[12px]">{fmtEur(r.tarifa2)}</span> },
    { key:'precio_coste', label:'Coste', align:'right' as const, render:(r:Articulo)=><span className="font-mono text-[12px] text-[#6B6860]">{fmtEur(r.precio_coste)}</span> },
    { key:'es_fabricado', label:'', render:(r:Articulo)=>r.es_fabricado?<span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700 border border-purple-300">FAB</span>:null },
    { key:'_', label:'', render:(r:Articulo)=>(
      <button onClick={e=>{e.stopPropagation();openEdit(r)}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860]"><Edit2 size={13}/></button>
    )},
  ]

  // flatten familias for select
  const famFlat: any[] = []
  const flatten = (items:any[], level=0) => items?.forEach(f=>{ famFlat.push({...f,level}); if(f.children) flatten(f.children,level+1) })
  flatten(familias.data??[])

  return (
    <div>
      <PageHeader title="Artículos" sub={`${data?.total??0} referencias`} actions={
        <div className="flex gap-2 flex-wrap">
          <select className={`${selectCls} max-w-[180px]`} value={familiaId} onChange={e=>setFamiliaId(e.target.value?Number(e.target.value):'')}>
            <option value="">Todas las familias</option>
            {famFlat.map((f:any)=><option key={f.id} value={f.id}>{'\u00A0'.repeat(f.level*2)}{f.nombre}</option>)}
          </select>
          <SearchInput value={search} onChange={v=>{setSearch(v);setPage(1)}} placeholder="Ref, descripción, EAN..."/>
          <Btn onClick={openNew}><Plus size={14}/>Nuevo artículo</Btn>
        </div>
      }/>

      <Card noPad>
        <DataTable cols={cols} rows={data?.data??[]} loading={isLoading} keyFn={r=>r.id}/>
      </Card>
      <Pagination page={page} lastPage={data?.last_page??1} onChange={setPage}/>

      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&setModal(false)}>
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC] sticky top-0 bg-white z-10">
              <h2 className="text-[16px] font-semibold">{editing?'Editar artículo':'Nuevo artículo'}</h2>
              <button onClick={()=>setModal(false)} className="p-1.5 rounded-[6px] hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-2 gap-3">
                <Field label="Referencia *"><input className={inputCls} value={form.referencia||''} onChange={e=>set('referencia',e.target.value)} disabled={!!editing}/></Field>
                <Field label="Descripción *"><input className={inputCls} value={form.descripcion||''} onChange={e=>set('descripcion',e.target.value)}/></Field>
              </div>
              <div className="grid grid-cols-3 gap-3">
                <Field label="Familia">
                  <select className={selectCls} value={form.familia_id||''} onChange={e=>set('familia_id',Number(e.target.value)||null)}>
                    <option value="">Sin familia</option>
                    {famFlat.map((f:any)=><option key={f.id} value={f.id}>{'\u00A0'.repeat(f.level*2)}{f.nombre}</option>)}
                  </select>
                </Field>
                <Field label="Unidad">
                  <select className={selectCls} value={form.unidad_medida} onChange={e=>set('unidad_medida',e.target.value)}>
                    {UNIDADES.map(u=><option key={u} value={u}>{u}</option>)}
                  </select>
                </Field>
                <Field label="IVA">
                  <select className={selectCls} value={form.tipo_iva_id} onChange={e=>set('tipo_iva_id',Number(e.target.value))}>
                    {tiposIva.data?.map((t:any)=><option key={t.id} value={t.id}>{t.descripcion}</option>)}
                  </select>
                </Field>
              </div>
              <div className="bg-[#F7F6F3] rounded-[8px] p-4 mb-3">
                <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider mb-3">Tarifas de precio (€)</div>
                <div className="grid grid-cols-5 gap-2">
                  {['tarifa1','tarifa2','tarifa3','tarifa4','precio_coste'].map((k,i)=>(
                    <Field key={k} label={i<4?`Tarifa ${i+1}`:'Coste'}>
                      <input type="number" className={inputCls} min="0" step="0.0001" value={form[k]||''} onChange={e=>set(k,Number(e.target.value))}/>
                    </Field>
                  ))}
                </div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Stock mínimo (alerta)"><input type="number" className={inputCls} min="0" step="0.001" value={form.stock_minimo||0} onChange={e=>set('stock_minimo',Number(e.target.value))}/></Field>
                <Field label="EAN-13"><input className={inputCls} value={form.ean13||''} onChange={e=>set('ean13',e.target.value)} maxLength={13}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Proveedor habitual">
                  <select className={selectCls} value={form.proveedor_id||''} onChange={e=>set('proveedor_id',Number(e.target.value)||null)}>
                    <option value="">Sin proveedor</option>
                    {proveedores.data?.data?.map((p:any)=><option key={p.id} value={p.id}>{p.nombre}</option>)}
                  </select>
                </Field>
                <Field label="Artículo fabricado">
                  <select className={selectCls} value={form.es_fabricado?'1':'0'} onChange={e=>set('es_fabricado',e.target.value==='1')}>
                    <option value="0">No (comprado)</option>
                    <option value="1">Sí (fabricado)</option>
                  </select>
                </Field>
              </div>
              <Field label="Observaciones"><textarea className={inputCls} rows={2} value={form.observaciones||''} onChange={e=>set('observaciones',e.target.value)}/></Field>
            </div>
            <div className="flex gap-2 justify-end px-6 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(false)}>Cancelar</Btn>
              <Btn onClick={save} loading={createMut.isPending||updateMut.isPending}>Guardar artículo</Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
