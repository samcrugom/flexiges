// ─── ProveedoresPage ──────────────────────────────────────────────────────────
import { useState } from 'react'
import { useProveedores, useCreateProveedor, useUpdateProveedor } from '@/hooks/queries'
import { useMaestros } from '@/hooks/queries'
import { PageHeader, SearchInput, Card, DataTable, Btn, Field, inputCls, selectCls, Pagination } from '@/components/shared'
import { Plus, Edit2, X } from 'lucide-react'

export function ProveedoresPage() {
  const [search, setSearch] = useState('')
  const [page, setPage]   = useState(1)
  const [modal, setModal] = useState(false)
  const [editing, setEditing] = useState<any>(null)
  const [form, setForm]   = useState<any>({ descuento_pct:0, plazo_entrega:0, activo:true })

  const { data, isLoading } = useProveedores({ search, page, per_page:25 })
  const { formasPago } = useMaestros()
  const createMut = useCreateProveedor()
  const updateMut = useUpdateProveedor(editing?.id??0)

  const open  = (p?:any) => { setForm(p??{ descuento_pct:0, plazo_entrega:0, activo:true }); setEditing(p??null); setModal(true) }
  const set   = (k:string,v:any) => setForm((f:any)=>({...f,[k]:v}))
  const save  = () => { const m=editing?updateMut:createMut; (m as any).mutate(form,{onSuccess:()=>setModal(false)}) }

  const cols = [
    { key:'codigo',  label:'Código',  render:(r:any)=><span className="font-mono text-[12px]">{r.codigo}</span> },
    { key:'nombre',  label:'Nombre',  render:(r:any)=><span className="font-medium">{r.nombre}</span> },
    { key:'nif',     label:'NIF',     render:(r:any)=><span className="font-mono text-[12px]">{r.nif??'—'}</span> },
    { key:'localidad',label:'Localidad' },
    { key:'telefono',label:'Teléfono' },
    { key:'descuento_pct', label:'Dto.', align:'right' as const, render:(r:any)=><span className="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-800 border border-green-300">{r.descuento_pct}%</span> },
    { key:'_', label:'', render:(r:any)=><button onClick={e=>{e.stopPropagation();open(r)}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860]"><Edit2 size={13}/></button> },
  ]

  return (
    <div>
      <PageHeader title="Proveedores" sub={`${data?.total??0} proveedores`} actions={
        <div className="flex gap-2">
          <SearchInput value={search} onChange={v=>{setSearch(v);setPage(1)}}/>
          <Btn onClick={()=>open()}><Plus size={14}/>Nuevo proveedor</Btn>
        </div>
      }/>
      <Card noPad><DataTable cols={cols} rows={data?.data??[]} loading={isLoading} keyFn={r=>r.id}/></Card>
      <Pagination page={page} lastPage={data?.last_page??1} onChange={setPage}/>

      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&setModal(false)}>
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC] sticky top-0 bg-white">
              <h2 className="text-[16px] font-semibold">{editing?'Editar proveedor':'Nuevo proveedor'}</h2>
              <button onClick={()=>setModal(false)} className="p-1.5 rounded hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-2 gap-3">
                <Field label="Nombre *"><input className={inputCls} value={form.nombre||''} onChange={e=>set('nombre',e.target.value)}/></Field>
                <Field label="NIF / CIF"><input className={inputCls} value={form.nif||''} onChange={e=>set('nif',e.target.value)}/></Field>
              </div>
              <Field label="Dirección"><input className={inputCls} value={form.direccion||''} onChange={e=>set('direccion',e.target.value)}/></Field>
              <div className="grid grid-cols-3 gap-3">
                <Field label="CP"><input className={inputCls} value={form.codigo_postal||''} onChange={e=>set('codigo_postal',e.target.value)}/></Field>
                <Field label="Localidad"><input className={inputCls} value={form.localidad||''} onChange={e=>set('localidad',e.target.value)}/></Field>
                <Field label="Provincia"><input className={inputCls} value={form.provincia||''} onChange={e=>set('provincia',e.target.value)}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Teléfono"><input className={inputCls} value={form.telefono||''} onChange={e=>set('telefono',e.target.value)}/></Field>
                <Field label="Email"><input type="email" className={inputCls} value={form.email||''} onChange={e=>set('email',e.target.value)}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Forma de pago">
                  <select className={selectCls} value={form.forma_pago_id||''} onChange={e=>set('forma_pago_id',Number(e.target.value)||null)}>
                    <option value="">Sin asignar</option>
                    {formasPago.data?.map((fp:any)=><option key={fp.id} value={fp.id}>{fp.nombre}</option>)}
                  </select>
                </Field>
                <Field label="Dto. habitual (%)"><input type="number" className={inputCls} min="0" max="100" step="0.5" value={form.descuento_pct||0} onChange={e=>set('descuento_pct',Number(e.target.value))}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Plazo entrega (días)"><input type="number" className={inputCls} min="0" value={form.plazo_entrega||0} onChange={e=>set('plazo_entrega',Number(e.target.value))}/></Field>
                <Field label="IBAN"><input className={inputCls} value={form.iban||''} onChange={e=>set('iban',e.target.value)}/></Field>
              </div>
              <Field label="Observaciones"><textarea className={inputCls} rows={2} value={form.observaciones||''} onChange={e=>set('observaciones',e.target.value)}/></Field>
            </div>
            <div className="flex gap-2 justify-end px-6 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(false)}>Cancelar</Btn>
              <Btn onClick={save} loading={createMut.isPending||updateMut.isPending}>Guardar</Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default ProveedoresPage
